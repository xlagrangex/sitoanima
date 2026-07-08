<?php
/**
 * Helper di query prodotti per le sezioni home.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ritorna prodotti Woo ordinati per criterio richiesto.
 *
 * @param string $sort  'popularity' | 'newest' | 'on_sale'
 * @param int    $limit Quanti prodotti.
 * @return WC_Product[]
 */
function pn_query_products( string $sort = 'popularity', int $limit = 8 ): array {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}

	$args = array(
		'limit'        => $limit,
		'status'       => 'publish',
		'visibility'   => 'catalog',
		'stock_status' => 'instock',
	);

	switch ( $sort ) {
		case 'newest':
			$args['orderby'] = 'date';
			$args['order']   = 'DESC';
			break;

		case 'on_sale':
			$ids = wc_get_product_ids_on_sale();
			if ( empty( $ids ) ) {
				return array();
			}
			$args['include'] = $ids;
			$args['orderby'] = 'date';
			$args['order']   = 'DESC';
			break;

		case 'popularity':
		default:
			$args['orderby']  = 'meta_value_num';
			$args['meta_key'] = 'total_sales'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$args['order']    = 'DESC';
			break;
	}

	$products = wc_get_products( $args );
	return is_array( $products ) ? $products : array();
}

/**
 * Ritorna prodotti per category handle.
 *
 * @param string $category_slug Slug del product_cat.
 * @param int    $limit         Quanti prodotti.
 * @param string $orderby       'date' | 'popularity'.
 * @return WC_Product[]
 */
function pn_query_products_by_category( string $category_slug, int $limit = 12, string $orderby = 'date' ): array {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}

	$args = array(
		'limit'        => $limit,
		'status'       => 'publish',
		'visibility'   => 'catalog',
		'stock_status' => 'instock',
		'category'     => array( $category_slug ),
	);

	if ( 'popularity' === $orderby ) {
		$args['orderby']  = 'meta_value_num';
		$args['meta_key'] = 'total_sales'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		$args['order']    = 'DESC';
	} else {
		$args['orderby'] = 'date';
		$args['order']   = 'DESC';
	}

	$products = wc_get_products( $args );
	return is_array( $products ) ? $products : array();
}

/**
 * Ritorna prodotti per SKU o nome (fallback), mantenendo l'ordine richiesto.
 *
 * Ogni item può essere:
 *   - una stringa → trattata come SKU
 *   - un array ['sku' => string, 'name' => string] → prova prima SKU, poi name LIKE
 *
 * Utile per curation editoriale stabile della home (codici MINSAN da Pharmanow live).
 *
 * @param array $items Lista di SKU/name nell'ordine di display.
 * @return WC_Product[]
 */
function pn_query_products_by_skus( array $items ): array {
	if ( ! function_exists( 'wc_get_product_id_by_sku' ) || empty( $items ) ) {
		return array();
	}

	global $wpdb;

	$ids = array();
	foreach ( $items as $it ) {
		$sku  = is_array( $it ) ? (string) ( $it['sku'] ?? '' ) : (string) $it;
		$name = is_array( $it ) ? (string) ( $it['name'] ?? '' ) : '';

		$id = 0;
		if ( $sku ) {
			$id = (int) wc_get_product_id_by_sku( $sku );
		}
		if ( ! $id && $name ) {
			$like = '%' . $wpdb->esc_like( $name ) . '%';
			$id   = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish' AND post_title LIKE %s LIMIT 1",
					$like
				)
			);
		}
		if ( $id ) {
			$ids[] = $id;
		}
	}

	if ( empty( $ids ) ) {
		return array();
	}

	// Curation esplicita: niente filtro visibility/stock per non escludere prodotti che il
	// cliente ha chiesto di mostrare. Status 'publish' è l'unico vincolo.
	$found = wc_get_products(
		array(
			'limit'   => count( $ids ),
			'include' => $ids,
			'status'  => 'publish',
		)
	);
	if ( ! is_array( $found ) ) {
		return array();
	}

	$by_id = array();
	foreach ( $found as $p ) {
		$by_id[ $p->get_id() ] = $p;
	}
	$ordered = array();
	foreach ( $ids as $id ) {
		if ( isset( $by_id[ $id ] ) ) {
			$ordered[] = $by_id[ $id ];
		}
	}
	return $ordered;
}

/**
 * Ritorna prodotti per slug specifici, mantenendo l'ordine richiesto.
 * Utile per curation manuale (es. selezione editoriale home).
 *
 * @param string[] $slugs Lista di slug prodotti (in ordine di display desiderato).
 * @return WC_Product[]
 */
function pn_query_products_by_slugs( array $slugs ): array {
	if ( ! function_exists( 'wc_get_products' ) || empty( $slugs ) ) {
		return array();
	}

	$args = array(
		'limit'        => count( $slugs ),
		'status'       => 'publish',
		'visibility'   => 'catalog',
		'stock_status' => 'instock',
		'slug'         => $slugs,
	);

	$products = wc_get_products( $args );
	if ( ! is_array( $products ) || empty( $products ) ) {
		return array();
	}

	// Ordina come richiesto dall'utente.
	$by_slug = array();
	foreach ( $products as $p ) {
		$by_slug[ $p->get_slug() ] = $p;
	}
	$ordered = array();
	foreach ( $slugs as $s ) {
		if ( isset( $by_slug[ $s ] ) ) {
			$ordered[] = $by_slug[ $s ];
		}
	}
	return $ordered;
}
