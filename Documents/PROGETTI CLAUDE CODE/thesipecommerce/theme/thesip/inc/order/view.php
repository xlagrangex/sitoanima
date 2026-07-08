<?php
/**
 * Order view — data prep + render unificato per thank-you e account view-order.
 *
 * Pattern Shopify-style: stesso layout della pagina ordine, due varianti di hero
 * (celebration post-checkout, info dopo). Single source of truth in template-parts/order/body.php.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Calcola tutto ciò che serve ai partial di order/body + heroes.
 *
 * @param WC_Order $order
 * @return array
 */
function pn_get_order_view_data( WC_Order $order ): array {
	$settings = pn_shop_settings();

	$status = $order->get_status();

	// Mappa stato → step timeline (0..3).
	$step_idx = 0;
	if ( in_array( $status, array( 'on-hold', 'pending', 'processing' ), true ) ) {
		$step_idx = 1;
	}
	if ( 'processing' === $status ) {
		$step_idx = 1;
	} elseif ( in_array( $status, array( 'shipped', 'partial-shipped' ), true ) ) {
		$step_idx = 2;
	} elseif ( 'completed' === $status ) {
		$step_idx = 3;
	} elseif ( 'on-hold' === $status || 'pending' === $status ) {
		$step_idx = 0;
	}

	// ETA: penisola 1-2 gg lavorativi, isole 2-3.
	$ship_state = strtoupper( (string) $order->get_shipping_state() );
	$islands    = array( 'SI', 'SR', 'CT', 'PA', 'EN', 'AG', 'CL', 'TP', 'RG', 'ME', 'SS', 'CA', 'NU', 'OR', 'SU' );
	$eta_min    = in_array( $ship_state, $islands, true ) ? 2 : 1;
	$eta_max    = in_array( $ship_state, $islands, true ) ? 3 : 2;
	$eta_from   = strtotime( '+' . ( $eta_min + 1 ) . ' weekdays' );
	$eta_to     = strtotime( '+' . ( $eta_max + 1 ) . ' weekdays' );
	$eta_label  = sprintf(
		/* translators: 1: from date, 2: to date */
		__( '%1$s — %2$s', 'pharmanow' ),
		wp_date( 'j', $eta_from ),
		wp_date( 'j M', $eta_to )
	);

	$steps = array(
		array(
			'label' => __( 'Ricevuto', 'pharmanow' ),
			'sub'   => __( 'Ordine confermato', 'pharmanow' ),
			'icon'  => 'check',
		),
		array(
			'label' => __( 'In preparazione', 'pharmanow' ),
			'sub'   => __( 'Stiamo allestendo il pacco', 'pharmanow' ),
			'icon'  => 'package',
		),
		array(
			'label' => __( 'Spedito', 'pharmanow' ),
			'sub'   => __( 'In viaggio verso di te', 'pharmanow' ),
			'icon'  => 'truck',
		),
		array(
			'label' => __( 'Consegnato', 'pharmanow' ),
			'sub'   => __( 'Nelle tue mani', 'pharmanow' ),
			'icon'  => 'house',
		),
	);

	$status_label = wc_get_order_status_name( $status );

	$ship_addr = (string) $order->get_formatted_shipping_address();
	$bill_addr = (string) $order->get_formatted_billing_address();
	if ( '' === $ship_addr ) {
		$ship_addr = $bill_addr;
	}

	$date_obj = $order->get_date_created();

	return array(
		'order'         => $order,
		'first_name'    => trim( (string) $order->get_billing_first_name() ),
		'email'         => $order->get_billing_email(),
		'phone'         => $order->get_billing_phone(),
		'date_iso'      => $date_obj ? $date_obj->date( 'c' ) : '',
		'date_fmt'      => $date_obj ? wp_date( 'j F Y · H:i', $date_obj->getTimestamp() ) : '',
		'date_short'    => $date_obj ? wp_date( 'j M Y', $date_obj->getTimestamp() ) : '',
		'is_recent'     => $date_obj && ( time() - $date_obj->getTimestamp() ) < 600,
		'status'        => $status,
		'status_label'  => $status_label,
		'step_idx'      => $step_idx,
		'eta_label'     => $eta_label,
		'steps'         => $steps,
		'ship_addr'     => $ship_addr,
		'bill_addr'     => $bill_addr,
		'show_billing'  => $bill_addr && $bill_addr !== $ship_addr,
		'ship_method'   => $order->get_shipping_method(),
		'totals'        => array(
			'subtotal' => (float) $order->get_subtotal(),
			'shipping' => (float) $order->get_shipping_total(),
			'discount' => (float) $order->get_total_discount(),
			'tax'      => (float) $order->get_total_tax(),
			'total'    => (float) $order->get_total(),
		),
		'qty_total'     => (int) $order->get_item_count(),
		'settings'      => $settings,
		'shop_url'      => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalogo' ),
		'track_url'     => home_url( '/traccia-ordine/' ),
		'account_url'   => $order->get_view_order_url(),
		'payment_title' => $order->get_payment_method_title(),
		'payment_id'    => $order->get_payment_method(),
		'customer_note' => (string) $order->get_customer_note(),
	);
}

/**
 * Renderizza la pagina ordine completa (hero + body) per Woo thank-you o my-account view-order.
 *
 * @param WC_Order $order
 * @param string   $variant     'celebration' (post-checkout) | 'info' (account view) | 'auto' (basato su data)
 * @param bool     $is_thankyou true solo sul landing post-checkout: spara gli hook woocommerce_thankyou
 *                              (gateway che finalizzano sulla thank-you, tracking conversioni) che il
 *                              render custom altrimenti non eseguirebbe mai.
 */
function pn_render_order_page( WC_Order $order, string $variant = 'auto', bool $is_thankyou = false ): void {
	$data = pn_get_order_view_data( $order );

	if ( 'auto' === $variant ) {
		$variant = $data['is_recent'] ? 'celebration' : 'info';
	}

	// Niente confetti né "importo addebitato" per ordini non pagati: il cliente
	// può tornare dal gateway con pagamento rifiutato/in sospeso e deve vederlo
	// (e poter riprovare), non ricevere una conferma falsa.
	$payment_pending = $order->needs_payment() || $order->has_status( array( 'failed', 'cancelled' ) );
	if ( $payment_pending && 'celebration' === $variant ) {
		$variant = 'payment-pending';
	}

	$data['variant']         = $variant;
	$data['payment_pending'] = $payment_pending;
	$data['retry_url']       = $order->needs_payment() ? $order->get_checkout_payment_url() : '';

	if ( $is_thankyou ) {
		do_action( 'woocommerce_before_thankyou', $order->get_id() );
	}

	echo '<div class="bg-gray-50 min-h-screen">';

	// Animations CSS — caricato sempre (poche regole, riusato in entrambe le varianti).
	get_template_part( 'template-parts/order/animations' );

	if ( 'payment-pending' === $variant ) {
		get_template_part( 'template-parts/order/hero-payment-pending', null, $data );
	} elseif ( 'celebration' === $variant ) {
		get_template_part( 'template-parts/order/hero-celebration', null, $data );
	} else {
		get_template_part( 'template-parts/order/hero-info', null, $data );
	}

	get_template_part( 'template-parts/order/body', null, $data );

	echo '</div>';

	if ( $is_thankyou ) {
		// Il dettaglio ordine è già renderizzato dal layout custom qui sopra:
		// senza questo remove, l'hook core appenderebbe la tabella stock
		// (non stilata) in fondo alla pagina.
		remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );
		do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() );
		do_action( 'woocommerce_thankyou', $order->get_id() );
	}
}
