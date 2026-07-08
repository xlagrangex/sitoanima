<?php
/**
 * Pay-for-order stile Shopify Draft Order.
 *
 * Quando il customer-care manda al cliente l'URL nativo WC:
 *   /pagamento/order-pay/{id}/?pay_for_order=true&key=wc_order_xxx
 *
 * Invece di mostrare la pagina WC pay-for-order (review-only), intercettiamo
 * la request e:
 *   1. Validiamo la `key` contro l'order_key
 *   2. Svuotiamo il cart corrente
 *   3. Aggiungiamo i line items dell'ordine al cart
 *   4. Pre-compiliamo billing/shipping in WC()->customer
 *   5. Salviamo l'ID ordine "guardian" in sessione
 *   6. Redirect al checkout normale `/pagamento/`
 *
 * Il cliente vede il checkout standard (3-step accordion) con tutto pre-popolato
 * e può modificare l'indirizzo. Headline custom "Ciao Nome, completa l'ordine".
 *
 * Quando paga, WC crea un NUOVO ordine. Hook su payment_complete trasha il vecchio
 * (guardian) e aggiunge nota "Sostituisce ordine #X" sul nuovo.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const PN_PAY_FOR_ORDER_SESSION_KEY = 'pn_pay_for_order_id';

/**
 * Intercetta l'URL pay-for-order nativo di WC e dirotta sul flusso draft-cart.
 */
add_action(
	'template_redirect',
	function () {
		// Solo se URL contiene il pattern WC pay-for-order.
		if ( empty( $_GET['pay_for_order'] ) || empty( $_GET['key'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		if ( ! function_exists( 'is_checkout_pay_page' ) || ! is_checkout_pay_page() ) {
			return;
		}

		global $wp;
		$order_id = isset( $wp->query_vars['order-pay'] ) ? absint( $wp->query_vars['order-pay'] ) : 0;
		if ( ! $order_id ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			wc_add_notice( __( 'Ordine non trovato.', 'pharmanow' ), 'error' );
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}

		$key_in = sanitize_text_field( wp_unslash( (string) $_GET['key'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! hash_equals( (string) $order->get_order_key(), $key_in ) ) {
			wc_add_notice( __( 'Link di pagamento non valido o scaduto. Contatta il customer care.', 'pharmanow' ), 'error' );
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}

		// Se l'ordine è già pagato, non fare niente — manda alla thank-you.
		if ( ! $order->needs_payment() ) {
			$thanks = $order->get_checkout_order_received_url();
			wp_safe_redirect( $thanks ?: home_url( '/' ) );
			exit;
		}

		// Anti-loop: se siamo già stati qui in questa sessione per lo stesso ordine,
		// non re-popolare (sennò perdiamo eventuali modifiche del cliente al cart).
		$already = (int) WC()->session->get( PN_PAY_FOR_ORDER_SESSION_KEY, 0 );
		if ( $already === $order_id ) {
			wp_safe_redirect( wc_get_checkout_url() );
			exit;
		}

		// Le fee custom non sono riproducibili in un cart ricostruito: il totale
		// non combacerebbe con quanto concordato dal customer care. In quel caso
		// lasciamo la pagina order-pay nativa di WC, che incassa il totale salvato.
		if ( count( $order->get_items( 'fee' ) ) > 0 ) {
			return;
		}

		// Auth temporanea per quella request: WC vuole un user loggato per editare il cart
		// se l'ordine ha customer_id. Equivalente al token-based access (key è il secret).
		if ( ! is_user_logged_in() ) {
			$customer_id = (int) $order->get_user_id();
			if ( $customer_id ) {
				wp_set_current_user( $customer_id );
			}
		}

		// Inizializza WC frontend (cart + customer + session) se non già fatto.
		if ( ! WC()->cart ) {
			wc_load_cart();
		}

		// 1. Svuota il cart corrente.
		WC()->cart->empty_cart( true );

		// 2. Aggiungi line items dell'ordine al cart. Per le variazioni il
		// dettaglio attributi viene ricostruito da WC a partire dalla
		// variation_id stessa; gli item non più acquistabili vanno segnalati,
		// non persi in silenzio (il cliente pagherebbe un ordine diverso).
		foreach ( $order->get_items( 'line_item' ) as $item ) {
			$product_id   = (int) $item->get_product_id();
			$variation_id = (int) $item->get_variation_id();
			$quantity     = (int) $item->get_quantity();
			if ( ! $product_id || $quantity < 1 ) {
				continue;
			}
			try {
				$added = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, array() );
			} catch ( Exception $e ) {
				$added = false;
			}
			if ( ! $added ) {
				wc_add_notice(
					sprintf(
						/* translators: %s = nome prodotto */
						__( '"%s" non è più disponibile e non è stato aggiunto al carrello: contatta il customer care.', 'pharmanow' ),
						$item->get_name()
					),
					'notice'
				);
			}
		}

		// 2b. Ri-applica i coupon dell'ordine originale: senza, il cliente
		// vedrebbe un totale più alto di quello concordato e abbandonerebbe.
		// Se un coupon non è ri-applicabile (es. usage limit già consumato
		// dal guardian stesso, ancora pending), il totale non combacerebbe:
		// si torna alla pagina order-pay nativa, che incassa il totale salvato.
		foreach ( $order->get_coupon_codes() as $pn_coupon_code ) {
			if ( ! WC()->cart->apply_coupon( $pn_coupon_code ) ) {
				WC()->cart->empty_cart( true );
				wc_clear_notices();
				return;
			}
		}

		// 3. Pre-compila customer billing/shipping da ordine.
		$customer = WC()->customer;
		if ( $customer ) {
			$customer->set_billing_first_name( $order->get_billing_first_name() );
			$customer->set_billing_last_name( $order->get_billing_last_name() );
			$customer->set_billing_company( $order->get_billing_company() );
			$customer->set_billing_email( $order->get_billing_email() );
			$customer->set_billing_phone( $order->get_billing_phone() );
			$customer->set_billing_address_1( $order->get_billing_address_1() );
			$customer->set_billing_address_2( $order->get_billing_address_2() );
			$customer->set_billing_city( $order->get_billing_city() );
			$customer->set_billing_state( $order->get_billing_state() );
			$customer->set_billing_postcode( $order->get_billing_postcode() );
			$customer->set_billing_country( $order->get_billing_country() ?: 'IT' );

			$customer->set_shipping_first_name( $order->get_shipping_first_name() );
			$customer->set_shipping_last_name( $order->get_shipping_last_name() );
			$customer->set_shipping_company( $order->get_shipping_company() );
			$customer->set_shipping_address_1( $order->get_shipping_address_1() );
			$customer->set_shipping_address_2( $order->get_shipping_address_2() );
			$customer->set_shipping_city( $order->get_shipping_city() );
			$customer->set_shipping_state( $order->get_shipping_state() );
			$customer->set_shipping_postcode( $order->get_shipping_postcode() );
			$customer->set_shipping_country( $order->get_shipping_country() ?: 'IT' );

			$customer->save();
		}

		// 4. Salva il "guardian order id" in sessione.
		WC()->session->set( PN_PAY_FOR_ORDER_SESSION_KEY, $order_id );

		// 5. Redirect al checkout normale.
		wp_safe_redirect( wc_get_checkout_url() );
		exit;
	},
	5
);

/**
 * Helper: ritorna l'ordine "guardian" in sessione, oppure null.
 *
 * @return WC_Order|null
 */
function pn_pay_for_order_get_guardian(): ?WC_Order {
	if ( ! function_exists( 'WC' ) || ! WC()->session ) {
		return null;
	}
	$guardian_id = (int) WC()->session->get( PN_PAY_FOR_ORDER_SESSION_KEY, 0 );
	if ( ! $guardian_id ) {
		return null;
	}
	$order = wc_get_order( $guardian_id );
	return $order ?: null;
}

/**
 * Quando il NUOVO ordine viene creato in checkout, salva il guardian id come post_meta
 * (così sopravvive alla sessione + è visibile al customer-care in admin).
 */
add_action(
	'woocommerce_checkout_create_order',
	function ( $new_order ) {
		$guardian_id = (int) WC()->session->get( PN_PAY_FOR_ORDER_SESSION_KEY, 0 );
		if ( $guardian_id ) {
			$new_order->update_meta_data( '_pn_replaces_order_id', $guardian_id );
			$new_order->add_order_note(
				sprintf(
					/* translators: %d = id ordine sostituito */
					__( 'Ordine generato da link "Customer payment URL" in sostituzione dell\'ordine #%d.', 'pharmanow' ),
					$guardian_id
				)
			);
		}
	},
	10,
	1
);

/**
 * Quando il NUOVO ordine raggiunge "processing" o "completed", trasha il vecchio
 * (guardian) — il customer-care vedrà solo l'ordine pagato. Nota nel guardian
 * con link al sostitutivo per audit trail.
 */
add_action( 'woocommerce_order_status_processing', 'pn_pay_for_order_trash_guardian', 20, 2 );
add_action( 'woocommerce_order_status_completed', 'pn_pay_for_order_trash_guardian', 20, 2 );

function pn_pay_for_order_trash_guardian( int $new_order_id, $new_order = null ): void {
	if ( ! $new_order ) {
		$new_order = wc_get_order( $new_order_id );
	}
	if ( ! $new_order ) {
		return;
	}
	$guardian_id = (int) $new_order->get_meta( '_pn_replaces_order_id' );
	if ( ! $guardian_id ) {
		return;
	}
	$guardian = wc_get_order( $guardian_id );
	// Solo guardian ancora in attesa di pagamento (pending) o con pagamento
	// fallito: mai on-hold (un pagamento può essere in volo/in verifica) né
	// status pagati/terminali.
	if ( ! $guardian || ! $guardian->has_status( array( 'pending', 'failed' ) ) ) {
		return;
	}

	// Note sul vecchio prima di trasharlo (audit).
	$guardian->add_order_note(
		sprintf(
			/* translators: %d = id nuovo ordine */
			__( 'Sostituito dall\'ordine #%d (pagato via link customer payment).', 'pharmanow' ),
			$new_order_id
		)
	);
	// Cancello → status, non delete fisico (così resta nel cestino in WP Admin).
	$guardian->update_status( 'cancelled', __( 'Sostituito da ordine ', 'pharmanow' ) . '#' . $new_order_id );

	// Pulisci la sessione.
	if ( WC()->session ) {
		WC()->session->set( PN_PAY_FOR_ORDER_SESSION_KEY, 0 );
	}
}

/**
 * Pulisci la sessione anche se il cliente abbandona o ricarica la pagina home.
 * Tieni il flag attivo solo per le pagine cart/checkout.
 */
add_action(
	'wp',
	function () {
		// Le richieste wc-ajax (update_order_review, checkout, fragments…)
		// attraversano 'wp' con is_checkout() ancora false: senza questo bail
		// il flag veniva azzerato al primo AJAX della pagina checkout, quindi
		// _pn_replaces_order_id non veniva MAI scritto sul sostituto e il
		// guardian non veniva mai chiuso a pagamento avvenuto.
		if ( wp_doing_ajax() || ! empty( $_GET['wc-ajax'] ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		if ( ! function_exists( 'WC' ) || ! WC()->session || ! WC()->session->has_session() ) {
			return;
		}
		if ( ! WC()->session->get( PN_PAY_FOR_ORDER_SESSION_KEY ) ) {
			return;
		}
		// Se non siamo né su cart né su checkout, ripuliamo.
		if ( ! is_cart() && ! is_checkout() ) {
			WC()->session->set( PN_PAY_FOR_ORDER_SESSION_KEY, 0 );
		}
	},
	999
);
