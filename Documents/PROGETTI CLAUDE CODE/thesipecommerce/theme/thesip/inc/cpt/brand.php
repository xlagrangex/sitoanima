<?php
/**
 * Tassonomia product_brand: slug allineato al Next (/marchi/) + index page virtuale.
 *
 * La tassonomia è registrata da plugin esterno (Farmadati) con slug 'marchio'.
 * Cambiamo lo slug a 'marchi' via filter register_taxonomy_args, e aggiungiamo
 * una rewrite rule per /marchi/ (senza term) che renderizza l'indice marche.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cambia lo slug rewrite da 'marchio' → 'marchi' al momento della registrazione.
 */
add_filter(
	'register_taxonomy_args',
	function ( $args, $taxonomy ) {
		if ( 'product_brand' !== $taxonomy ) {
			return $args;
		}
		if ( ! is_array( $args ) ) {
			return $args;
		}
		if ( ! isset( $args['rewrite'] ) || ! is_array( $args['rewrite'] ) ) {
			$args['rewrite'] = array();
		}
		$args['rewrite']['slug']         = 'marchi';
		$args['rewrite']['with_front']   = false;
		$args['rewrite']['hierarchical'] = false;
		return $args;
	},
	20,
	2
);

/**
 * Query var virtuale per l'indice marche.
 */
add_filter(
	'query_vars',
	function ( $vars ) {
		$vars[] = 'pn_brand_index';
		return $vars;
	}
);

/**
 * Rewrite rule: /marchi/ → indice marche (prima delle regole tassonomia).
 */
add_action(
	'init',
	function () {
		add_rewrite_rule( '^marchi/?$', 'index.php?pn_brand_index=1', 'top' );
	},
	11
);

/**
 * Template loader per l'indice marche.
 *
 * Cerca prima archive-product_brand.php nel tema, altrimenti fallback inline.
 */
add_filter(
	'template_include',
	function ( $template ) {
		if ( '1' === (string) get_query_var( 'pn_brand_index' ) ) {
			$candidate = locate_template( array( 'archive-product_brand.php' ) );
			if ( $candidate ) {
				return $candidate;
			}
		}
		return $template;
	}
);

/**
 * Lista marche normalizzate per l'indice.
 *
 * Restituisce un array di oggetti con:
 *   - name       (string)  display, suffisso societario strippato
 *   - slug       (string)  term slug WP
 *   - count      (int)     numero prodotti pubblicati con quel term
 *   - first      (string)  iniziale per group-by alfabetico
 *
 * Cache transient 1h, invalidato su create/edit/delete term product_brand.
 *
 * @return array<int,array{name:string,slug:string,count:int,first:string}>
 */
function pn_get_brand_summaries(): array {
	$cache_key = 'pn_brand_summaries_v1';
	$cached    = get_transient( $cache_key );
	if ( false !== $cached && is_array( $cached ) ) {
		return $cached;
	}

	if ( ! taxonomy_exists( 'product_brand' ) ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_brand',
			'hide_empty' => true,
			'number'     => 0,
		)
	);
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		set_transient( $cache_key, array(), 5 * MINUTE_IN_SECONDS );
		return array();
	}

	$out = array();
	foreach ( $terms as $term ) {
		$display = function_exists( 'pn_strip_company_suffix' )
			? pn_strip_company_suffix( (string) $term->name )
			: trim( (string) $term->name );
		if ( '' === $display ) {
			continue;
		}
		$first = strtoupper( mb_substr( $display, 0, 1 ) );
		if ( ! preg_match( '/[A-Z]/', $first ) ) {
			$first = '#';
		}
		$out[] = array(
			'name'  => $display,
			'slug'  => $term->slug,
			'count' => (int) $term->count,
			'first' => $first,
		);
	}

	usort(
		$out,
		function ( $a, $b ) {
			return strnatcasecmp( $a['name'], $b['name'] );
		}
	);

	set_transient( $cache_key, $out, HOUR_IN_SECONDS );
	return $out;
}

/**
 * Invalida cache quando un term product_brand cambia.
 */
foreach ( array( 'created_product_brand', 'edited_product_brand', 'delete_product_brand' ) as $h ) {
	add_action(
		$h,
		function () {
			delete_transient( 'pn_brand_summaries_v1' );
		}
	);
}
