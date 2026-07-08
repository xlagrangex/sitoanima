<?php
/**
 * Algoritmo recommendations PDP.
 *
 * Override esplicito: WC native cross-sells; in mancanza, scoring pesato:
 *   same_category +30, same_brand +25, prezzo ±30% +20, bestseller +15, recency 90gg +10.
 * Cache via transient 1h.
 *
 * Rimpiazza il MVP "stessa categoria" del Next (lib/queries/products.ts:99).
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ritorna fino a $limit prodotti raccomandati per il prodotto corrente.
 *
 * @param int $product_id
 * @param int $limit
 * @return WC_Product[]
 */
function pn_get_recommendations( int $product_id, int $limit = 8 ): array {
	$cache_key = 'pn_reco_' . $product_id . '_' . $limit;
	$cached    = get_transient( $cache_key );
	if ( false !== $cached ) {
		$objs = array_filter( array_map( 'wc_get_product', $cached ) );
		if ( count( $objs ) >= 1 ) {
			return $objs;
		}
	}

	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		return array();
	}

	// 1) Override esplicito: WC native cross-sells.
	$cross_sells = array_map( 'wc_get_product', $product->get_cross_sell_ids() );
	$cross_sells = array_filter(
		$cross_sells,
		function ( $p ) {
			return $p && $p->is_visible() && $p->is_in_stock();
		}
	);
	if ( count( $cross_sells ) >= max( 4, (int) ceil( $limit / 2 ) ) ) {
		$ids = array_map(
			function ( $p ) {
				return $p->get_id();
			},
			array_slice( $cross_sells, 0, $limit )
		);
		set_transient( $cache_key, $ids, HOUR_IN_SECONDS );
		return array_slice( array_values( $cross_sells ), 0, $limit );
	}

	// 2) Scoring pesato.
	$category_ids = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
	$brand        = (string) ( $product->get_meta( '_brand_name' ) ?: $product->get_meta( '_linea_name' ) ?: $product->get_meta( '_nome_azienda' ) );
	$base_price   = (float) ( $product->get_sale_price() ?: $product->get_regular_price() );

	$candidates_args = array(
		'post_type'      => 'product',
		'posts_per_page' => 60,
		'fields'         => 'ids',
		'post__not_in'   => array( $product_id ),
		'meta_query'     => array(
			array(
				'key'   => '_stock_status',
				'value' => 'instock',
			),
		),
		'orderby'        => 'rand',
	);
	if ( ! empty( $category_ids ) ) {
		$candidates_args['tax_query'] = array(
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'term_id',
				'terms'    => $category_ids,
			),
		);
	}
	$ids = get_posts( $candidates_args );

	// Se la categoria non basta, espandiamo per brand match.
	if ( count( $ids ) < $limit && '' !== $brand ) {
		$brand_ids = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => 30,
				'fields'         => 'ids',
				'post__not_in'   => array_merge( array( $product_id ), $ids ),
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'   => '_stock_status',
						'value' => 'instock',
					),
					array(
						'relation' => 'OR',
						array( 'key' => '_brand_name',   'value' => $brand ),
						array( 'key' => '_linea_name',   'value' => $brand ),
						array( 'key' => '_nome_azienda', 'value' => $brand, 'compare' => 'LIKE' ),
					),
				),
				'orderby'        => 'rand',
			)
		);
		$ids = array_unique( array_merge( $ids, $brand_ids ) );
	}

	if ( empty( $ids ) ) {
		return array();
	}

	$now      = time();
	$scored   = array();
	$cat_set  = array_flip( $category_ids );
	foreach ( $ids as $cid ) {
		$candidate = wc_get_product( $cid );
		if ( ! $candidate || ! $candidate->is_visible() ) {
			continue;
		}

		$score = 0;

		// Stessa categoria (+30).
		$cand_cats = wp_get_post_terms( $cid, 'product_cat', array( 'fields' => 'ids' ) );
		foreach ( $cand_cats as $cc ) {
			if ( isset( $cat_set[ $cc ] ) ) {
				$score += 30;
				break;
			}
		}

		// Stesso brand (+25).
		$cand_brand = (string) ( $candidate->get_meta( '_brand_name' ) ?: $candidate->get_meta( '_linea_name' ) ?: $candidate->get_meta( '_nome_azienda' ) );
		if ( '' !== $brand && $cand_brand === $brand ) {
			$score += 25;
		}

		// Prezzo ±30% (+20).
		$cand_price = (float) ( $candidate->get_sale_price() ?: $candidate->get_regular_price() );
		if ( $base_price > 0 && $cand_price > 0 ) {
			$ratio = $cand_price / $base_price;
			if ( $ratio >= 0.7 && $ratio <= 1.3 ) {
				$score += 20;
			}
		}

		// Bestseller boost (+15 max, scalato su total_sales).
		$sales = (int) get_post_meta( $cid, 'total_sales', true );
		if ( $sales > 0 ) {
			$score += min( 15, (int) round( log10( $sales + 1 ) * 5 ) );
		}

		// Recency 90gg (+10).
		$age_days = ( $now - get_post_time( 'U', false, $cid ) ) / DAY_IN_SECONDS;
		if ( $age_days <= 90 ) {
			$score += 10;
		}

		// Tie-breaker random per varietà.
		$score += wp_rand( 0, 5 );

		$scored[] = array( 'id' => $cid, 'score' => $score, 'product' => $candidate );
	}

	usort(
		$scored,
		function ( $a, $b ) {
			return $b['score'] <=> $a['score'];
		}
	);

	$picked     = array_slice( $scored, 0, $limit );
	$picked_ids = array_map(
		function ( $row ) {
			return $row['id'];
		},
		$picked
	);
	$products   = array_map(
		function ( $row ) {
			return $row['product'];
		},
		$picked
	);

	set_transient( $cache_key, $picked_ids, HOUR_IN_SECONDS );
	return $products;
}

// Invalida cache su update prodotto.
add_action(
	'woocommerce_update_product',
	function ( $product_id ) {
		foreach ( array( 4, 6, 8, 10, 12 ) as $limit ) {
			delete_transient( 'pn_reco_' . $product_id . '_' . $limit );
		}
	}
);
