<?php
/**
 * Mega menu data layer.
 *
 * Costruisce un albero di product_cat per il mega menu, con cache transient.
 * Whitelist e mappa icone allineate a apps/frontend/lib/category-icons.ts del Next.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const PN_MEGA_MENU_TRANSIENT = 'pn_mega_menu_tree_v1';

const PN_ROOT_WHITELIST = array(
	'set-di-carte',
	'bundle',
	'edizioni-speciali',
	'temi',
	'interazioni',
	'ambienti',
	'adattamenti',
);

/**
 * Mappa handle → meta icona.
 */
function pn_root_icon( string $handle ): array {
	$map = array(
		'set-di-carte'      => array( 'icon' => 'package',   'color' => 'text-blue-600' ),
		'bundle'            => array( 'icon' => 'box',       'color' => 'text-emerald-600' ),
		'edizioni-speciali' => array( 'icon' => 'star',      'color' => 'text-amber-600' ),
		'temi'              => array( 'icon' => 'sparkles',  'color' => 'text-indigo-600' ),
		'interazioni'       => array( 'icon' => 'heart',     'color' => 'text-rose-600' ),
		'ambienti'          => array( 'icon' => 'leaf',      'color' => 'text-green-600' ),
		'adattamenti'       => array( 'icon' => 'zap',       'color' => 'text-cyan-600' ),
	);
	return $map[ $handle ] ?? array( 'icon' => '', 'color' => 'text-gray-400' );
}

/**
 * Ritorna l'albero del mega menu (root + figli + nipoti) con cache transient 1h.
 *
 * @return array<int, array{id:int, handle:string, name:string, count:int, children:array, link:string}>
 */
function pn_get_mega_menu_tree(): array {
	$cached = get_transient( PN_MEGA_MENU_TRANSIENT );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false, // Mostriamo anche categorie vuote — il filtro hasProducts() rimuove a runtime.
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}

	// Indicizza per ID e raggruppa per parent.
	$by_id     = array();
	$by_parent = array();
	foreach ( $terms as $t ) {
		$by_id[ $t->term_id ] = $t;
		$by_parent[ $t->parent ][] = $t;
	}

	// Costruisce ricorsivamente.
	$build = function ( $term ) use ( &$build, &$by_parent ) {
		$kids = array();
		if ( ! empty( $by_parent[ $term->term_id ] ) ) {
			foreach ( $by_parent[ $term->term_id ] as $child ) {
				$kids[] = $build( $child );
			}
		}
		return array(
			'id'       => (int) $term->term_id,
			'handle'   => $term->slug,
			'name'     => $term->name,
			'count'    => (int) $term->count,
			'link'     => get_term_link( $term, 'product_cat' ),
			'children' => $kids,
		);
	};

	$tree = array();
	if ( ! empty( $by_parent[0] ) ) {
		foreach ( $by_parent[0] as $root ) {
			$tree[] = $build( $root );
		}
	}

	set_transient( PN_MEGA_MENU_TRANSIENT, $tree, HOUR_IN_SECONDS );
	return $tree;
}

/**
 * Filtra l'albero applicando la whitelist root.
 *
 * @return array
 */
function pn_get_mega_menu_roots(): array {
	$tree = pn_get_mega_menu_tree();
	$by_handle = array();
	foreach ( $tree as $node ) {
		$by_handle[ $node['handle'] ] = $node;
	}
	$roots = array();
	foreach ( PN_ROOT_WHITELIST as $h ) {
		if ( isset( $by_handle[ $h ] ) ) {
			$roots[] = $by_handle[ $h ];
		}
	}
	return $roots;
}

/**
 * Ritorna true se il nodo (o un suo discendente) ha prodotti.
 */
function pn_node_has_products( array $node ): bool {
	if ( ( $node['count'] ?? 0 ) > 0 ) {
		return true;
	}
	foreach ( $node['children'] ?? array() as $child ) {
		if ( pn_node_has_products( $child ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Invalida la cache quando una categoria viene creata/modificata/cancellata.
 */
add_action( 'created_product_cat', 'pn_flush_mega_menu_cache' );
add_action( 'edited_product_cat', 'pn_flush_mega_menu_cache' );
add_action( 'delete_product_cat', 'pn_flush_mega_menu_cache' );
function pn_flush_mega_menu_cache(): void {
	delete_transient( PN_MEGA_MENU_TRANSIENT );
}
