<?php
/**
 * Cleanup ordini "pending" orfani al checkout.
 *
 * Problema: WooCommerce, dentro WC_Checkout::create_order(), riusa l'ordine
 * "order_awaiting_payment" della sessione solo se `cart_hash` combacia. Quando
 * il cliente applica/rimuove un coupon (o cambia shipping, qty, ecc.) il
 * cart_hash cambia → WC crea un NUOVO ordine pending invece di aggiornare il
 * precedente. Risultato: il customer-care vede 2 (o 3) ordini con stesso
 * cliente — uno senza coupon, uno con coupon, ecc.
 *
 * Fix: al momento in cui il nuovo ordine viene creato e salvato (hook
 * `woocommerce_checkout_order_processed`), cerca tutti gli ordini in stato
 * `pending` dello stesso billing_email creati nei minuti recenti e li cancella
 * con nota di audit "Sostituito da #N". Finestra stretta (30 min) per non
 * toccare pending genuinamente vecchi.
 *
 * Caso pay-for-order draft: il guardian NON viene toccato qui. Cancellarlo
 * alla creazione del sostituto (cioè PRIMA che il pagamento esista) perdeva
 * la vendita quando il cliente abbandonava al gateway: ordine CC cancellato
 * + sostituto pending destinato a morire. Il guardian viene cancellato solo
 * da `pn_pay_for_order_trash_guardian` quando il sostituto risulta pagato,
 * ed è escluso anche dallo sweep duplicati qui sotto.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Finestra in minuti per considerare un pending come "duplicato".
 */
const PN_ORPHAN_PENDING_WINDOW_MIN = 30;

add_action(
	'woocommerce_checkout_order_processed',
	function ( $new_order_id, $posted_data, $new_order ) {
		if ( ! $new_order instanceof WC_Order ) {
			$new_order = wc_get_order( $new_order_id );
		}
		if ( ! $new_order ) {
			return;
		}

		$email = $new_order->get_billing_email();
		if ( ! $email ) {
			return;
		}

		$cancelled_dup_ids = array();
		$guardian_id       = (int) $new_order->get_meta( '_pn_replaces_order_id' );

		// 1. Pending duplicati dello stesso email negli ultimi 30 minuti.
		//
		// NOTA HPOS: il filtro `date_created` di wc_get_orders viene IGNORATO
		// quando combinato con `billing_email` nello stesso array di args
		// (verificato su Woo 9.x + HPOS, May 2026). Applichiamo il filtro
		// temporale in PHP DOPO il fetch — limit alto + orderby DESC per
		// catturare comunque tutti i candidati recenti.
		$min_ts = time() - ( PN_ORPHAN_PENDING_WINDOW_MIN * MINUTE_IN_SECONDS );

		$duplicates = wc_get_orders(
			array(
				'status'        => array( 'pending' ),
				'billing_email' => $email,
				'exclude'       => array( $new_order_id ),
				'limit'         => 50,
				'orderby'       => 'date_created',
				'order'         => 'DESC',
				'return'        => 'objects',
			)
		);

		foreach ( $duplicates as $dup ) {
			if ( ! $dup instanceof WC_Order ) {
				continue;
			}
			// Safety: non toccare se per qualunque motivo lo status è cambiato tra query e load.
			if ( ! $dup->has_status( 'pending' ) ) {
				continue;
			}
			// Il guardian pay-for-order resta vivo finché il sostituto non è pagato.
			if ( $guardian_id && $dup->get_id() === $guardian_id ) {
				continue;
			}
			// Filtro temporale in PHP (HPOS-safe).
			$created    = $dup->get_date_created();
			$created_ts = $created ? $created->getTimestamp() : 0;
			if ( ! $created_ts || $created_ts < $min_ts ) {
				continue;
			}
			$dup_id = $dup->get_id();
			$dup->add_order_note(
				sprintf(
					/* translators: %d = id nuovo ordine */
					__( 'Cancellato automaticamente: sostituito dal nuovo ordine #%d (stesso cliente, pending duplicato in finestra %dmin).', 'pharmanow' ),
					$new_order_id,
					PN_ORPHAN_PENDING_WINDOW_MIN
				)
			);
			$dup->update_status( 'cancelled', __( 'Sostituito da ordine ', 'pharmanow' ) . '#' . $new_order_id . ' (cleanup pending duplicati)' );

			$new_order->add_order_note(
				sprintf(
					/* translators: %d = id ordine duplicato cancellato */
					__( 'Cleanup automatico: cancellato ordine pending duplicato #%d (stesso cliente).', 'pharmanow' ),
					$dup_id
				)
			);

			$cancelled_dup_ids[] = $dup_id;
		}

		// 2. Log persistente — solo se abbiamo davvero agito. Il guardian
		// pay-for-order non viene più cancellato qui (pre-pagamento): se ne
		// occupa pn_pay_for_order_trash_guardian a sostituto pagato.
		if ( ! empty( $cancelled_dup_ids ) ) {
			pn_dup_cleanup_log_append(
				array(
					'ts'           => time(),
					'new_order_id' => $new_order_id,
					'email'        => $email,
					'duplicates'   => $cancelled_dup_ids,
					'guardian'     => null,
				)
			);
		}
	},
	5,
	3
);

/**
 * Finestra estesa (in minuti) per il cleanup su "resurrection" di ordini
 * cancelled → processing. Più ampia della finestra base perché copre il caso
 * in cui un ordine cancellato dal cron WC viene poi pagato in ritardo e
 * resuscitato: nel frattempo il cliente può aver creato un secondo pending
 * fantasma anche ore dopo.
 */
const PN_ORPHAN_PENDING_WINDOW_RESURRECTION_MIN = 360; // 6 ore

/**
 * Gateway che possono LEGITTIMAMENTE avere pending di lunga durata (es. BACS
 * in attesa di bonifico bancario). Esclusi dal cleanup "resurrection".
 */
function pn_orphan_pending_protected_gateways(): array {
	return array( 'bacs', 'cheque', 'cod' );
}

/**
 * Cleanup su transizione ordine → processing.
 *
 * Caso d'uso principale: ordine #A cancellato dal cron WC ("hold stock"),
 * cliente lo paga in ritardo via link customer-payment-URL, Payment Plugins
 * lo riporta a processing. Nel frattempo il cliente ha creato #B (pending
 * fantasma) che resta orfano fino al prossimo cron. Risultato per il CS: 2
 * ordini in admin di cui uno fantasma confondente.
 *
 * Fix: quando un qualsiasi ordine va in processing, cancella eventuali
 * pending dello stesso email (finestra 6 ore, gateway online only).
 */
add_action(
	'woocommerce_order_status_processing',
	function ( $order_id, $order = null ) {
		if ( ! $order ) {
			$order = wc_get_order( $order_id );
		}
		if ( ! $order instanceof WC_Order ) {
			return;
		}

		$email = $order->get_billing_email();
		if ( ! $email ) {
			return;
		}

		$min_ts            = time() - ( PN_ORPHAN_PENDING_WINDOW_RESURRECTION_MIN * MINUTE_IN_SECONDS );
		$protected_gw      = pn_orphan_pending_protected_gateways();
		$cancelled_dup_ids = array();

		$candidates = wc_get_orders(
			array(
				'status'        => array( 'pending' ),
				'billing_email' => $email,
				'exclude'       => array( $order_id ),
				'limit'         => 50,
				'orderby'       => 'date_created',
				'order'         => 'DESC',
				'return'        => 'objects',
			)
		);

		foreach ( $candidates as $dup ) {
			if ( ! $dup instanceof WC_Order ) {
				continue;
			}
			if ( ! $dup->has_status( 'pending' ) ) {
				continue;
			}
			// Skip gateway "legittimamente lenti" (bonifico, contrassegno).
			if ( in_array( $dup->get_payment_method(), $protected_gw, true ) ) {
				continue;
			}
			$created    = $dup->get_date_created();
			$created_ts = $created ? $created->getTimestamp() : 0;
			if ( ! $created_ts || $created_ts < $min_ts ) {
				continue;
			}

			$dup_id = $dup->get_id();
			$dup->add_order_note(
				sprintf(
					/* translators: %d = id nuovo ordine pagato */
					__( 'Cancellato automaticamente: sostituito dall\'ordine #%d che è stato pagato (stesso cliente, pending fantasma post-resurrection).', 'pharmanow' ),
					$order_id
				)
			);
			$dup->update_status( 'cancelled', __( 'Sostituito da ordine ', 'pharmanow' ) . '#' . $order_id . ' (cleanup post-resurrection)' );

			$order->add_order_note(
				sprintf(
					/* translators: %d = id pending duplicato cancellato */
					__( 'Cleanup post-resurrection: cancellato ordine pending fantasma #%d (stesso cliente).', 'pharmanow' ),
					$dup_id
				)
			);

			$cancelled_dup_ids[] = $dup_id;
		}

		if ( ! empty( $cancelled_dup_ids ) ) {
			pn_dup_cleanup_log_append(
				array(
					'ts'           => time(),
					'new_order_id' => $order_id,
					'email'        => $email,
					'duplicates'   => $cancelled_dup_ids,
					'guardian'     => null,
					'trigger'      => 'resurrection', // distingue dal trigger checkout
				)
			);
		}
	},
	20,
	2
);

/**
 * Aggiunge una entry al log circolare (max 200 entries) usato dalla pagina
 * admin di monitoring. Storato come option non-autoload per non gravare sul
 * boot di WordPress.
 *
 * @param array $entry
 */
function pn_dup_cleanup_log_append( array $entry ): void {
	$log = get_option( 'pn_dup_cleanup_log', array() );
	if ( ! is_array( $log ) ) {
		$log = array();
	}
	array_unshift( $log, $entry );
	if ( count( $log ) > 200 ) {
		$log = array_slice( $log, 0, 200 );
	}
	update_option( 'pn_dup_cleanup_log', $log, false );
}
