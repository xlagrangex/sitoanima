<?php
/**
 * REST endpoint export ordini → Google Sheet customer care.
 * GET /wp-json/pharmanow/v1/orders-export?key=SECRET
 *
 * Ordini in lavorazione riga-per-prodotto per il sync del foglio
 * "ORDINI PHARMANOW NUOVO" (Apps Script pull ogni 5 min, dedup per numero).
 * Chiave segreta nell'option pn_orders_export_key, mai in query log leggibili
 * lato client: accettata anche via header X-PN-Export-Key.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'pharmanow/v1',
			'/orders-export',
			array(
				'methods'             => 'GET',
				'permission_callback' => 'pn_orders_export_check_key',
				'callback'            => 'pn_rest_orders_export',
			)
		);
	}
);

function pn_orders_export_check_key( WP_REST_Request $req ) {
	$secret = (string) get_option( 'pn_orders_export_key' );
	if ( '' === $secret ) {
		return false;
	}
	$given = (string) ( $req->get_header( 'x_pn_export_key' ) ?: $req->get_param( 'key' ) );
	return '' !== $given && hash_equals( $secret, $given );
}

/**
 * Partner del coupon: fonte primaria il meta _promo_partner sul coupon
 * stesso (popolato dall'import promozioni, copre ~99% dei codici);
 * fallback la mappa prefissi storica dai dati di Loreley.
 */
function pn_orders_export_partner( $coupon_code ) {
	$coupon_id = wc_get_coupon_id_by_code( $coupon_code );
	if ( $coupon_id ) {
		$partner = (string) get_post_meta( $coupon_id, '_promo_partner', true );
		if ( '' !== trim( $partner ) ) {
			return strtoupper( trim( $partner ) );
		}
	}

	$code = strtolower( $coupon_code );
	$map  = apply_filters(
		'pn_orders_export_partner_map',
		array(
			'nexi'      => 'JAKALA',
			'vodafone'  => 'JAKALA',
			'ali'       => 'JAKALA',
			'aon'       => 'AON',
			'epipoli'   => 'EPIPOLI',
			'pipoli'    => 'EPIPOLI',
			'doubleyou' => 'DOUBLEYOU',
			'dy'        => 'DOUBLEYOU',
			'jointly'   => 'JOINTLY',
			'wellhub'   => 'WELLHUB',
			'we'        => 'WELLHUB',
			'edenred'   => 'EDENRED',
		)
	);
	foreach ( $map as $prefix => $partner ) {
		if ( 0 === strpos( $code, $prefix ) ) {
			return $partner;
		}
	}
	return '';
}

/**
 * Il foglio usa i nomi che usa Loreley, non gli id dei gateway.
 */
function pn_orders_export_payment_label( $method ) {
	$map = array(
		'ppcp'                 => 'paypal',
		'ppcp-gateway'         => 'paypal',
		'woocommerce_payments' => 'woopayments',
	);
	return isset( $map[ $method ] ) ? $map[ $method ] : $method;
}

function pn_rest_orders_export( WP_REST_Request $req ) {
	$status = array_filter( array_map( 'sanitize_key', explode( ',', (string) $req->get_param( 'status' ) ) ) );
	$limit  = min( 200, max( 1, (int) ( $req->get_param( 'limit' ) ?: 100 ) ) );

	return rest_ensure_response( pn_orders_export_collect( $status ?: array( 'processing' ), $limit ) );
}

/**
 * Dati export riusati da endpoint REST e sync cron (inc/sync/sheet-sync.php).
 */
function pn_orders_export_collect( $status = array( 'processing' ), $limit = 100 ) {
	$orders = wc_get_orders(
		array(
			'status'  => $status,
			'limit'   => $limit,
			'orderby' => 'date',
			'order'   => 'ASC',
		)
	);

	// Email → numeri ordine, per il flag stesso-cliente calcolato server-side.
	$by_email = array();
	foreach ( $orders as $order ) {
		$em = strtolower( (string) $order->get_billing_email() );
		$by_email[ $em ][] = (string) $order->get_order_number();
	}

	$out = array();
	foreach ( $orders as $order ) {
		$email   = strtolower( (string) $order->get_billing_email() );
		$number  = (string) $order->get_order_number();
		$coupons = array();
		foreach ( $order->get_items( 'coupon' ) as $ci ) {
			$coupons[] = array(
				'code'   => $ci->get_code(),
				'amount' => round( (float) $ci->get_discount() + (float) $ci->get_discount_tax(), 2 ),
			);
		}

		$partner    = '';
		$labels     = array();
		$coupon_url = '';
		foreach ( $coupons as $cp ) {
			$partner  = $partner ?: pn_orders_export_partner( $cp['code'] );
			$labels[] = strtoupper( $cp['code'] ) . ' ' . wc_format_localized_price( (string) $cp['amount'] ) . '€';
			if ( '' === $coupon_url ) {
				$cid = wc_get_coupon_id_by_code( $cp['code'] );
				if ( $cid ) {
					$coupon_url = admin_url( 'post.php?post=' . $cid . '&action=edit' );
				}
			}
		}

		$discount = (float) $order->get_discount_total() + (float) $order->get_discount_tax();
		$total    = (float) $order->get_total();
		$gross    = $total + $discount;
		$totale   = $discount > 0
			? sprintf( 'tot: %s - %s= %s €', wc_format_localized_price( (string) $gross ), wc_format_localized_price( (string) $discount ), wc_format_localized_price( (string) $total ) )
			: '';

		$items = array();
		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();
			$qty     = max( 1, (int) $item->get_quantity() );
			$line    = (float) $item->get_subtotal() + (float) $item->get_subtotal_tax();
			$items[] = array(
				'minsan' => $product ? (string) $product->get_sku() : '',
				'name'   => $item->get_name(),
				'qty'    => (int) $item->get_quantity(),
				'price'  => round( $line / $qty, 2 ), // unitario, non totale riga
			);
		}

		$others = array_values( array_diff( $by_email[ $email ], array( $number ) ) );

		$out[] = array(
			'id'             => $order->get_id(),
			'order_status'   => $order->get_status(),
			'number'         => $number,
			'admin_url'      => admin_url( 'admin.php?page=wc-orders&action=edit&id=' . $order->get_id() ),
			'email'          => $email,
			'payment'        => pn_orders_export_payment_label( (string) $order->get_payment_method() ),
			'date'           => $order->get_date_created() ? $order->get_date_created()->date_i18n( 'd/m/Y' ) : '',
			'cf'             => (string) $order->get_meta( '_pn_codice_fiscale' ),
			'coupons'        => $coupons,
			'coupon_label'   => implode( ' + ', $labels ),
			'coupon_url'     => $coupon_url,
			'partner'        => $partner,
			'edenred'        => false !== strpos( (string) $order->get_payment_method(), 'edenred' ) ? $total : '',
			'subtotal'       => round( (float) $order->get_subtotal(), 2 ),
			'discount'       => round( $discount, 2 ),
			'shipping'       => round( (float) $order->get_shipping_total() + (float) $order->get_shipping_tax(), 2 ),
			'total'          => round( $total, 2 ),
			'totale_string'  => $totale,
			'same_customer'  => $others,
			'items'          => $items,
		);
	}

	return array(
		'generated_at' => gmdate( 'c' ),
		'status'       => $status,
		'count'        => count( $out ),
		'orders'       => $out,
	);
}
