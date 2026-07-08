<?php
/**
 * Recupero carrelli abbandonati + avviso Customer Care.
 *
 * Quando un ordine online (PayPal/Stripe) non pagato viene annullato (timeout
 * WC o cleanup duplicati) e NON esiste un ordine "fratello" pagato dello stesso
 * cliente, invia al cliente un'email di recupero con un link che ricostruisce
 * carrello + coupon, e annota/avvisa il Customer Care.
 *
 * Vedi docs/superpowers/specs/2026-05-31-abandoned-recovery-design.md
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Finestra per cercare ordini "fratelli" dello stesso cliente. */
const PN_RECOVERY_SIBLING_WINDOW = DAY_IN_SECONDS;

/**
 * True se il gateway è online (PayPal/Stripe) — gli unici per cui ha senso il
 * recupero "pagamento non completato".
 */
function pn_recovery_is_online_gateway( string $pm ): bool {
	return 'ppcp' === $pm || 0 === strpos( $pm, 'stripe' );
}

/**
 * Costruisce il link tokenizzato di recupero per un ordine.
 */
function pn_recovery_link( WC_Order $order ): string {
	return add_query_arg(
		array(
			'pn_recover' => $order->get_id(),
			'key'        => $order->get_order_key(),
		),
		home_url( '/' )
	);
}

/**
 * Decide se un ordine annullato è un abbandono recuperabile.
 *
 * Funzione PURA (nessun side-effect, nessun invio) per essere testabile.
 *
 * @param bool $ignore_dedup Salta i controlli anti-doppione (solo per i test).
 * @return true|string True se recuperabile, altrimenti il motivo dello skip.
 */
function pn_recovery_should_recover( WC_Order $order, bool $ignore_dedup = false ) {
	if ( ! pn_recovery_is_online_gateway( $order->get_payment_method() ) ) {
		return 'gateway-non-online';
	}
	$email = $order->get_billing_email();
	if ( ! is_email( $email ) ) {
		return 'email-assente';
	}
	if ( $order->get_date_paid() ) {
		return 'gia-pagato';
	}
	// Mai su cancellazione manuale da wp-admin (il CC ha annullato apposta).
	if ( is_admin() && ! wp_doing_cron() ) {
		return 'cancellazione-manuale-admin';
	}
	if ( ! $ignore_dedup ) {
		if ( $order->get_meta( '_pn_recovery_sent' ) ) {
			return 'gia-inviata-ordine';
		}
		if ( get_transient( 'pn_recov_' . md5( strtolower( $email ) ) ) ) {
			return 'gia-inviata-email-recente';
		}
	}

	$siblings = wc_get_orders(
		array(
			'type'          => 'shop_order',
			'billing_email' => $email,
			'date_created'  => '>' . ( time() - PN_RECOVERY_SIBLING_WINDOW ),
			'exclude'       => array( $order->get_id() ),
			'limit'         => 20,
		)
	);
	foreach ( $siblings as $sib ) {
		if ( ! $sib instanceof WC_Order ) {
			continue;
		}
		// Un fratello pagato → era un duplicato benigno.
		if ( $sib->get_date_paid() || in_array( $sib->get_status(), array( 'processing', 'completed', 'on-hold' ), true ) ) {
			return 'fratello-pagato';
		}
		// Un fratello ancora pending → si aspetta che muoia l'ultimo.
		if ( 'pending' === $sib->get_status() ) {
			return 'fratello-pending';
		}
	}

	return true;
}

/**
 * Trigger principale: ordine annullato → valuta se è un abbandono recuperabile.
 */
add_action( 'woocommerce_order_status_cancelled', 'pn_recovery_on_cancelled', 30, 2 );

function pn_recovery_on_cancelled( int $order_id, $order = null ): void {
	$order = $order instanceof WC_Order ? $order : wc_get_order( $order_id );
	if ( ! $order instanceof WC_Order ) {
		return;
	}
	if ( true !== pn_recovery_should_recover( $order ) ) {
		return;
	}

	$email       = $order->get_billing_email();
	$recover_url = pn_recovery_link( $order );
	$coupons     = $order->get_coupon_codes();
	$coupon_str  = $coupons ? strtoupper( implode( ', ', $coupons ) ) : '';

	$sent = pn_recovery_send_customer_email( $order, $recover_url, $coupons );

	// Nota Customer Care + alert.
	$order->add_order_note(
		sprintf(
			/* translators: 1 gateway, 2 email, 3 coupon, 4 esito invio, 5 url */
			__( '⚠️ Abbandono al pagamento (%1$s). Email: %2$s. Coupon: %3$s. Email di recupero al cliente: %4$s. Link recupero: %5$s', 'pharmanow' ),
			$order->get_payment_method_title() ?: $order->get_payment_method(),
			$email,
			$coupon_str ?: '—',
			$sent ? __( 'inviata', 'pharmanow' ) : __( 'NON inviata', 'pharmanow' ),
			$recover_url
		)
	);
	pn_recovery_send_cc_alert( $order, $recover_url );

	$order->update_meta_data( '_pn_recovery_sent', current_time( 'mysql' ) );
	$order->save();
	set_transient( 'pn_recov_' . md5( strtolower( $email ) ), $order_id, PN_RECOVERY_SIBLING_WINDOW );
}

/**
 * HTML dell'email cliente (puro, testabile senza invio).
 */
function pn_recovery_customer_email_html( WC_Order $order, string $recover_url, array $coupons ): string {
	$name = $order->get_billing_first_name();
	$h    = function_exists( 'pn_email_header' ) ? pn_email_header( __( 'Completa il tuo ordine', 'pharmanow' ) ) : '<!DOCTYPE html><html><body style="font-family:sans-serif;color:#1f2937">';
	$h .= '<p>' . sprintf( esc_html__( 'Ciao %s,', 'pharmanow' ), esc_html( $name ?: '' ) ) . '</p>';
	$h .= '<p>' . esc_html__( 'il tuo ordine non risulta completato: il pagamento non è andato a buon fine o si è interrotto. Niente paura — abbiamo tenuto da parte il tuo carrello.', 'pharmanow' ) . '</p>';
	if ( $coupons ) {
		$h .= '<p>' . sprintf( esc_html__( 'Il tuo sconto %s è ancora valido e verrà riapplicato.', 'pharmanow' ), '<strong>' . esc_html( strtoupper( $coupons[0] ) ) . '</strong>' ) . '</p>';
	}
	$h .= function_exists( 'pn_btn' )
		? pn_btn( __( 'Completa il tuo ordine', 'pharmanow' ), $recover_url )
		: '<p><a href="' . esc_url( $recover_url ) . '">' . esc_html__( 'Completa il tuo ordine', 'pharmanow' ) . '</a></p>';
	$h .= '<p style="font-size:13px;color:#6b7280">' . esc_html__( 'Il link ricostruisce il tuo carrello e ti porta direttamente al pagamento.', 'pharmanow' ) . '</p>';
	if ( function_exists( 'pn_help' ) ) {
		$h .= pn_help();
	}
	$h .= function_exists( 'pn_email_footer' ) ? pn_email_footer() : '</body></html>';
	return $h;
}

/**
 * Invia l'email di recupero al cliente (via Brevo se disponibile, fallback wp_mail).
 */
function pn_recovery_send_customer_email( WC_Order $order, string $recover_url, array $coupons ): bool {
	$subject = __( 'Hai lasciato qualcosa in sospeso — completa il tuo ordine', 'pharmanow' );
	$html    = pn_recovery_customer_email_html( $order, $recover_url, $coupons );

	if ( function_exists( 'pn_send_brevo' ) ) {
		return (bool) pn_send_brevo( $order->get_billing_email(), $subject, $html, false, 'customer_abandon_recovery', $order->get_id() );
	}
	return wp_mail( $order->get_billing_email(), $subject, $html, array( 'Content-Type: text/html; charset=UTF-8' ) );
}

/**
 * Alert al Customer Care.
 */
function pn_recovery_send_cc_alert( WC_Order $order, string $recover_url ): void {
	$to = defined( 'PN_ADMIN_EMAILS' ) ? PN_ADMIN_EMAILS : get_option( 'admin_email' );
	if ( ! $to ) {
		return;
	}
	$name    = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
	$total   = html_entity_decode( wp_strip_all_tags( $order->get_formatted_order_total() ) );
	$subject = sprintf( __( 'Carrello abbandonato — %1$s — %2$s', 'pharmanow' ), $name ?: $order->get_billing_email(), $total );

	$h  = function_exists( 'pn_email_header' ) ? pn_email_header( __( 'Carrello abbandonato', 'pharmanow' ) ) : '<!DOCTYPE html><html><body>';
	$h .= '<p><strong>' . esc_html__( 'Cliente da ricontattare per recupero.', 'pharmanow' ) . '</strong></p>';
	$h .= '<p>' . esc_html__( 'Cliente', 'pharmanow' ) . ': ' . esc_html( $name ) . '<br>';
	$h .= esc_html__( 'Email', 'pharmanow' ) . ': ' . esc_html( $order->get_billing_email() ) . '<br>';
	$h .= esc_html__( 'Telefono', 'pharmanow' ) . ': ' . esc_html( $order->get_billing_phone() ) . '<br>';
	$h .= esc_html__( 'Totale', 'pharmanow' ) . ': ' . esc_html( $total ) . '<br>';
	$h .= esc_html__( 'Ordine', 'pharmanow' ) . ': #' . esc_html( $order->get_order_number() ) . ' (' . esc_html( $order->get_payment_method_title() ) . ')</p>';
	$h .= function_exists( 'pn_btn' ) ? pn_btn( __( 'Link di recupero cliente', 'pharmanow' ), $recover_url ) : '<p><a href="' . esc_url( $recover_url ) . '">' . esc_url( $recover_url ) . '</a></p>';
	$h .= function_exists( 'pn_email_footer' ) ? pn_email_footer() : '</body></html>';

	if ( function_exists( 'pn_send_brevo' ) ) {
		pn_send_brevo( $to, $subject, $h, false, 'admin_abandon', $order->get_id() );
	} else {
		wp_mail( $to, $subject, $h, array( 'Content-Type: text/html; charset=UTF-8' ) );
	}
}

/**
 * Handler del link di recupero: ricostruisce carrello + coupon → checkout.
 */
add_action( 'template_redirect', 'pn_recovery_handle_link' );

function pn_recovery_handle_link(): void {
	if ( empty( $_GET['pn_recover'] ) || empty( $_GET['key'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	$order = wc_get_order( absint( wp_unslash( $_GET['pn_recover'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$key   = sanitize_text_field( wp_unslash( $_GET['key'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( ! $order instanceof WC_Order || ! hash_equals( (string) $order->get_order_key(), $key ) ) {
		wc_add_notice( __( 'Link di recupero non valido o scaduto.', 'pharmanow' ), 'error' );
		wp_safe_redirect( wc_get_cart_url() );
		exit;
	}

	// Il link email resta valido per giorni: lo stato va riverificato AL CLICK.
	// Un ordine cancellato dal cleanup e poi resuscitato da una capture tardiva
	// (PayPal/Stripe) risulta pagato — riproporre il checkout farebbe pagare
	// due volte (e svuoterebbe il carrello corrente). Si procede col rebuild
	// solo per ordini ancora pagabili (pending/failed) o cancellati mai pagati.
	$pn_already_paid = $order->is_paid() || $order->get_date_paid();
	if ( $pn_already_paid || ( ! $order->needs_payment() && ! $order->has_status( 'cancelled' ) ) ) {
		wp_safe_redirect( home_url( '/ordine/' . $order->get_id() . '/?key=' . rawurlencode( $key ) ) );
		exit;
	}

	if ( ! WC()->cart ) {
		return;
	}

	WC()->cart->empty_cart();
	$skipped = 0;
	foreach ( $order->get_items() as $item ) {
		if ( ! $item instanceof WC_Order_Item_Product ) {
			continue;
		}
		$product_id   = $item->get_product_id();
		$variation_id = $item->get_variation_id();
		$product      = wc_get_product( $variation_id ?: $product_id );
		if ( ! $product || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
			$skipped++;
			continue;
		}
		WC()->cart->add_to_cart( $product_id, $item->get_quantity(), $variation_id, $variation_id ? $item->get_variation() : array() );
	}

	foreach ( $order->get_coupon_codes() as $code ) {
		if ( ! WC()->cart->has_discount( $code ) ) {
			WC()->cart->apply_coupon( $code );
		}
	}

	if ( $skipped > 0 ) {
		wc_add_notice(
			sprintf(
				/* translators: %d = numero prodotti non più disponibili */
				_n( '%d prodotto non più disponibile è stato rimosso dal carrello.', '%d prodotti non più disponibili sono stati rimossi dal carrello.', $skipped, 'pharmanow' ),
				$skipped
			),
			'notice'
		);
	}

	wp_safe_redirect( wc_get_checkout_url() );
	exit;
}
