<?php
/**
 * Search: instant search REST endpoint + route /ricerca/?q=X.
 *
 * Replica components/shop/search/InstantSearch.tsx + app/(shop)/ricerca/.
 *
 * Performance:
 *  - Cache transient pn_search_<md5(q)>_<limit> per 5 minuti.
 *  - Query SQL diretta su title/SKU/brand-meta con UNION + LIMIT.
 *  - Niente WP_Query orchestrato per evitare l'overhead di hook + cap-checks.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Query var virtuale.
 */
add_filter(
	'query_vars',
	function ( $vars ) {
		$vars[] = 'pn_search';
		return $vars;
	}
);

/**
 * Rewrite /ricerca/ → query var pn_search=1, "q" passato come query string.
 */
add_action(
	'init',
	function () {
		add_rewrite_rule( '^ricerca/?$', 'index.php?pn_search=1', 'top' );
	},
	11
);

/**
 * Template loader: search.php quando pn_search attivo.
 */
add_filter(
	'template_include',
	function ( $template ) {
		if ( '1' === (string) get_query_var( 'pn_search' ) ) {
			$candidate = locate_template( array( 'search.php' ) );
			if ( $candidate ) {
				return $candidate;
			}
		}
		return $template;
	}
);

/**
 * <title> per la route virtuale.
 */
add_filter(
	'document_title_parts',
	function ( $parts ) {
		if ( '1' === (string) get_query_var( 'pn_search' ) ) {
			$q = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
			$parts['title'] = $q
				? sprintf( __( 'Risultati per "%s"', 'pharmanow' ), $q )
				: __( 'Ricerca', 'pharmanow' );
		}
		return $parts;
	}
);

/**
 * REST endpoint instant search.
 *
 *   GET /wp-json/pharmanow/v1/search?q=X&limit=5
 *   → { products: [...], total: int, query: str }
 */
add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'pharmanow/v1',
			'/search',
			array(
				'methods'             => 'GET',
				'callback'            => 'pn_search_rest',
				'permission_callback' => '__return_true',
				'args'                => array(
					'q'     => array( 'required' => true, 'type' => 'string' ),
					'limit' => array( 'required' => false, 'type' => 'integer', 'default' => 5 ),
				),
			)
		);
	}
);

/**
 * Decodifica entità HTML ed elimina nbsp da output wc_price().
 *
 * wc_price() ritorna "<span>7,50&nbsp;&euro;</span>" → wp_strip_all_tags
 * lascia "7,50&nbsp;&euro;" → questa funzione produce "7,50 €".
 */
function pn_search_clean_price( float $price ): string {
	if ( ! function_exists( 'wc_price' ) ) {
		return number_format_i18n( $price, 2 ) . ' €';
	}
	$raw = wp_strip_all_tags( wc_price( $price ) );
	return html_entity_decode( $raw, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
}

/**
 * Esegue la ricerca prodotti.
 *
 * Strategia performance (5868 prodotti su VPS Hostinger):
 *  - Niente JOIN su {wp_postmeta}: la hot path è solo {wp_posts}.
 *  - FULLTEXT index pn_ft_title su post_title → MATCH AGAINST con
 *    BOOLEAN MODE prefix '<q>*' per query ≥ 3 chars (sfrutta l'indice).
 *  - Per query 2-char fallback LIKE '<q>%' (prefix, può usare anche
 *    composite index post_type+status); evita LIKE %q% in hot path.
 *  - Brand/SKU non più nella query principale: erano costose. In
 *    pratica title cattura già brand (sono in post_title).
 *  - Total in unica query con SQL_CALC_FOUND_ROWS rimosso: usiamo
 *    LIMIT * 4 e contiamo i match raw, sufficiente per dropdown.
 *
 * @return array{products:array<int,array<string,mixed>>, total:int}
 */
function pn_search_products( string $q, int $limit = 5 ): array {
	$q     = trim( $q );
	$limit = max( 1, min( 48, (int) $limit ) );

	if ( mb_strlen( $q ) < 2 ) {
		return array( 'products' => array(), 'total' => 0 );
	}

	$cache_key = 'pn_search_v2_' . md5( strtolower( $q ) ) . '_' . $limit;
	$cached    = get_transient( $cache_key );
	if ( false !== $cached && is_array( $cached ) ) {
		return $cached;
	}

	global $wpdb;
	$ids   = array();
	$total = 0;

	// Strategia A: FULLTEXT (query ≥ 3 chars, normale per InnoDB innodb_ft_min_token_size=3).
	if ( mb_strlen( $q ) >= 3 ) {
		// Boolean mode: prefisso wildcard *. Sanitizziamo da chars di sintassi FT.
		$q_ft = preg_replace( '/[+\-><()~*"@]/', ' ', $q );
		$q_ft = preg_replace( '/\s+/', ' ', trim( (string) $q_ft ) );
		if ( $q_ft ) {
			$ft_param = '+' . str_replace( ' ', '* +', $q_ft ) . '*';
			$sql = $wpdb->prepare(
				"SELECT ID, MATCH(post_title) AGAINST(%s IN BOOLEAN MODE) AS score
				 FROM {$wpdb->posts}
				 WHERE post_type='product' AND post_status='publish'
				   AND MATCH(post_title) AGAINST(%s IN BOOLEAN MODE)
				 ORDER BY score DESC, menu_order ASC, post_title ASC
				 LIMIT %d",
				$ft_param,
				$ft_param,
				$limit * 4
			);
			$rows  = $wpdb->get_results( $sql, ARRAY_A );
			$total = count( (array) $rows );
			$ids   = array_map( fn( $r ) => (int) $r['ID'], array_slice( (array) $rows, 0, $limit ) );
		}
	}

	// Strategia B: LIKE prefix (fallback per 2-char o se FT non ha trovato).
	if ( empty( $ids ) ) {
		$like_prefix = $wpdb->esc_like( $q ) . '%';
		$like_any    = '%' . $wpdb->esc_like( $q ) . '%';
		$sql = $wpdb->prepare(
			"SELECT ID,
				CASE WHEN post_title LIKE %s THEN 100 ELSE 50 END AS score
			 FROM {$wpdb->posts}
			 WHERE post_type='product' AND post_status='publish'
			   AND post_title LIKE %s
			 ORDER BY score DESC, menu_order ASC, post_title ASC
			 LIMIT %d",
			$like_prefix,
			$like_any,
			$limit * 4
		);
		$rows  = $wpdb->get_results( $sql, ARRAY_A );
		$total = count( (array) $rows );
		$ids   = array_map( fn( $r ) => (int) $r['ID'], array_slice( (array) $rows, 0, $limit ) );
	}

	// Total esatto: count rows matched (cap a $limit*4 per non far esplodere il count).
	// Per il dropdown va bene "approx total"; per la pagina /ricerca/ limit=48
	// cattura comunque i risultati significativi.

	$products = array();
	foreach ( $ids as $id ) {
		$wcp = function_exists( 'wc_get_product' ) ? wc_get_product( $id ) : null;
		if ( ! $wcp instanceof WC_Product ) {
			continue;
		}

		$brand = (string) ( $wcp->get_meta( '_brand_name' )
			?: $wcp->get_meta( '_linea_name' )
			?: $wcp->get_meta( '_nome_azienda' ) );
		if ( $brand && function_exists( 'pn_strip_company_suffix' ) ) {
			$brand = pn_strip_company_suffix( $brand );
		}

		$thumb_id  = (int) get_post_thumbnail_id( $id );
		$thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'pn-product-thumb' ) : '';
		if ( ! $thumb_url && function_exists( 'wc_placeholder_img_src' ) ) {
			$thumb_url = wc_placeholder_img_src( 'pn-product-thumb' );
		}

		$regular = (float) $wcp->get_regular_price();
		$sale    = $wcp->is_on_sale() ? (float) $wcp->get_sale_price() : 0;
		$price   = $sale > 0 ? $sale : $regular;
		$pct     = ( $sale > 0 && $regular > 0 ) ? (int) round( ( ( $regular - $sale ) / $regular ) * 100 ) : 0;

		$products[] = array(
			'id'                => $id,
			'title'             => $wcp->get_name(),
			'url'               => get_permalink( $id ),
			'thumbnail'         => $thumb_url,
			'brand'             => $brand,
			'regular_price'     => $regular,
			'sale_price'        => $sale,
			'price'             => $price,
			'price_formatted'   => pn_search_clean_price( $price ),
			'regular_formatted' => $sale > 0 ? pn_search_clean_price( $regular ) : '',
			'discount_pct'      => $pct,
		);
	}

	$result = array(
		'products' => $products,
		'total'    => $total,
		'query'    => $q,
	);

	set_transient( $cache_key, $result, 5 * MINUTE_IN_SECONDS );
	return $result;
}

/**
 * REST handler.
 */
function pn_search_rest( WP_REST_Request $req ) {
	$q     = (string) $req->get_param( 'q' );
	$limit = (int) $req->get_param( 'limit' );
	$out   = pn_search_products( $q, $limit > 0 ? $limit : 5 );
	$resp  = rest_ensure_response( $out );
	// Browser cache 60s · Cloudflare edge cache 5 min · stale-while-revalidate
	// per servire istantaneo dall'edge mentre rivalida in background.
	$resp->header(
		'Cache-Control',
		'public, max-age=60, s-maxage=300, stale-while-revalidate=600'
	);
	return $resp;
}

/**
 * Invalida cache search quando un prodotto viene salvato/cancellato.
 * Strategia naïve: chiamiamo wp_cache_flush sui transient con prefix
 * pn_search_ via SQL (transient su DB).
 */
function pn_search_flush_cache(): void {
	global $wpdb;
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_pn_search_%' OR option_name LIKE '_transient_timeout_pn_search_%'" );
}
add_action( 'save_post_product', 'pn_search_flush_cache' );
add_action( 'deleted_post', 'pn_search_flush_cache' );
