<?php
/**
 * REST endpoint per "Visti di recente": ritorna prodotti per array di IDs.
 *
 * GET /wp-json/pharmanow/v1/products?ids=12,34,56
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
			'/products',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => 'pn_rest_products_by_ids',
				'args'                => array(
					'ids' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
	}
);

function pn_rest_products_by_ids( WP_REST_Request $req ): WP_REST_Response {
	$ids_raw = $req->get_param( 'ids' );
	$ids     = array_filter( array_map( 'absint', explode( ',', (string) $ids_raw ) ) );
	$ids     = array_slice( $ids, 0, 20 );
	if ( empty( $ids ) ) {
		return new WP_REST_Response( array( 'products' => array() ) );
	}

	$out = array();
	foreach ( $ids as $id ) {
		$p = wc_get_product( $id );
		if ( ! $p || ! $p->is_visible() ) {
			continue;
		}
		$brand   = (string) ( $p->get_meta( '_brand_name' ) ?: $p->get_meta( '_linea_name' ) ?: $p->get_meta( '_nome_azienda' ) );
		$thumb   = get_the_post_thumbnail_url( $id, 'pn-product-card' ) ?: wc_placeholder_img_src( 'pn-product-card' );
		$regular = (float) $p->get_regular_price();
		$sale    = $p->is_on_sale() ? (float) $p->get_sale_price() : 0;

		$out[] = array(
			'id'           => $id,
			'title'        => $p->get_name(),
			'url'          => get_permalink( $id ),
			'thumbnail'    => $thumb,
			'brand'        => $brand,
			'in_stock'     => $p->is_in_stock(),
			'price_html'   => $p->get_price_html(),
			'regular'      => $regular,
			'sale'         => $sale,
			'savings_pct'  => ( $sale > 0 && $regular > 0 ) ? (int) round( ( ( $regular - $sale ) / $regular ) * 100 ) : 0,
			'is_new'       => ( time() - get_post_time( 'U', false, $id ) ) < 30 * DAY_IN_SECONDS,
		);
	}

	return new WP_REST_Response( array( 'products' => $out ) );
}
