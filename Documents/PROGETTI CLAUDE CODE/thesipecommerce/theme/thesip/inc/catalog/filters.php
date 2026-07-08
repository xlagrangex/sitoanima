<?php
/**
 * Catalogo: filtri server-side.
 *
 * Replica feature parity di components/shop/catalog/* del Next:
 *  - brand[]            checkbox marche (chain _brand_name → _linea_name → _nome_azienda)
 *  - min_price/max_price input numerici (Woo nativo)
 *  - glutine=1          solo prodotti meta _senza_glutine = '1'
 *  - lattosio=1         solo prodotti meta _senza_lattosio = '1'
 *  - instock=1          solo prodotti _stock_status = 'instock'
 *  - promo=1            solo prodotti in offerta
 *  - iva[]              meta _aliquota_iva ∈ ['4','10','22']
 *  - orderby            relevance|price|price-desc|popularity|newest|discount
 *
 * Brand fallback chain (allineata a inc/product/meta.php, recently-viewed,
 * recommendations, product-card): sul DB Pharmanow _brand_name è vuoto su
 * tutti i record, _nome_azienda è popolato (5868 prodotti) come ragione
 * sociale ("ZUCCARI Srl") che strippiamo del suffisso societario.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Strip suffisso societario "Srl/SpA/SAS" da una ragione sociale.
 * Allineato a inc/product/meta.php.
 */
function pn_strip_company_suffix( string $name ): string {
	// Suffisso preceduto da spazio O da punto (es. "RIUN.Srl"). Soluzione
	// più tollerante di inc/product/meta.php che richiedeva \s+.
	$cleaned = preg_replace(
		'/[\s.]+(s\.?\s*r\.?\s*l\.?|s\.?\s*p\.?\s*a\.?|spa|srl|s\.a\.s\.|sas)\s*$/i',
		'',
		$name
	);
	return trim( (string) $cleaned );
}

/**
 * Lista marche disponibili (display).
 *
 * Strategia:
 *  1) tassonomia product_brand (se esiste) → tutti i term name
 *  2) fallback: union distinct di {_brand_name, _linea_name, _nome_azienda}
 *     con strip suffisso societario, dedupe, sort naturale, cache 1h.
 *
 * @return string[]
 */
function pn_get_available_brands(): array {
	$cache_key = 'pn_catalog_brands_v2';
	$cached    = get_transient( $cache_key );
	if ( false !== $cached && is_array( $cached ) ) {
		return $cached;
	}

	$brands = array();

	// Sorgente A: tassonomia product_brand. I term spesso contengono ragione
	// sociale grezza ("NAMEDSPORT Srl"). Strippiamo per coerenza UI.
	if ( taxonomy_exists( 'product_brand' ) ) {
		$terms = get_terms(
			array(
				'taxonomy'   => 'product_brand',
				'hide_empty' => true,
				'fields'     => 'names',
			)
		);
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term_name ) {
				$brands[] = pn_strip_company_suffix( (string) $term_name );
			}
		}
	}

	// Sorgente B: meta diretta sui prodotti. Solo se nessun term valido.
	if ( empty( $brands ) ) {
		global $wpdb;
		$rows = $wpdb->get_results(
			"SELECT pm.meta_key, pm.meta_value
			 FROM {$wpdb->postmeta} pm
			 INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			 WHERE pm.meta_key IN ('_brand_name','_linea_name','_nome_azienda')
			   AND pm.meta_value <> ''
			   AND p.post_type = 'product'
			   AND p.post_status = 'publish'",
			ARRAY_A
		);
		foreach ( (array) $rows as $row ) {
			$value = trim( (string) $row['meta_value'] );
			if ( '' === $value ) {
				continue;
			}
			if ( '_nome_azienda' === $row['meta_key'] ) {
				$value = pn_strip_company_suffix( $value );
			}
			if ( '' !== $value ) {
				$brands[] = $value;
			}
		}
	}

	$brands = array_values( array_unique( array_map( 'trim', $brands ) ) );
	$brands = array_values( array_filter( $brands ) );
	sort( $brands, SORT_NATURAL | SORT_FLAG_CASE );

	set_transient( $cache_key, $brands, HOUR_IN_SECONDS );
	return $brands;
}

/**
 * Risolve un nome brand (display) → tutti i term IDs della tassonomia
 * product_brand il cui nome matcha esattamente o ha quel prefisso seguito
 * da " Srl/SpA/SAS".
 *
 * @param string[] $brands Nomi display selezionati.
 * @return int[] Term IDs.
 */
function pn_resolve_brand_term_ids( array $brands ): array {
	if ( ! taxonomy_exists( 'product_brand' ) || empty( $brands ) ) {
		return array();
	}
	global $wpdb;

	$ids = array();
	foreach ( $brands as $b ) {
		$b = trim( (string) $b );
		if ( '' === $b ) {
			continue;
		}
		$like = $wpdb->esc_like( $b ) . '%';
		$term_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT t.term_id
				 FROM {$wpdb->terms} t
				 INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
				 WHERE tt.taxonomy = 'product_brand'
				   AND ( t.name = %s OR t.name LIKE %s )",
				$b,
				$like
			)
		);
		foreach ( $term_ids as $tid ) {
			$ids[] = (int) $tid;
		}
	}
	return array_values( array_unique( $ids ) );
}

/**
 * Hook GET → meta_query / tax_query / post_in.
 */
add_action(
	'woocommerce_product_query',
	function ( $q ) {
		if ( is_admin() || ! $q->is_main_query() ) {
			return;
		}

		$meta_query = (array) $q->get( 'meta_query' );
		$tax_query  = (array) $q->get( 'tax_query' );
		$post__in   = $q->get( 'post__in' );

		// Brand[]: il valore è già strippato (es. "NAMEDSPORT"). Risolviamo
		// in term IDs raw ("NAMEDSPORT Srl") se la tassonomia esiste,
		// altrimenti OR sui meta con LIKE per coprire i suffissi.
		if ( ! empty( $_GET['brand'] ) && is_array( $_GET['brand'] ) ) {
			$brands = array_values( array_filter( array_map( 'sanitize_text_field', wp_unslash( $_GET['brand'] ) ) ) );
			if ( $brands ) {
				if ( taxonomy_exists( 'product_brand' ) ) {
					$term_ids = pn_resolve_brand_term_ids( $brands );
					if ( $term_ids ) {
						$tax_query[] = array(
							'taxonomy' => 'product_brand',
							'field'    => 'term_id',
							'terms'    => $term_ids,
							'operator' => 'IN',
						);
					} else {
						// Brand selezionato ma nessun term match → 0 risultati.
						$post__in = array( 0 );
					}
				} else {
					$brand_or = array( 'relation' => 'OR' );
					foreach ( $brands as $b ) {
						$brand_or[] = array(
							'key'     => '_brand_name',
							'value'   => $b,
							'compare' => 'LIKE',
						);
						$brand_or[] = array(
							'key'     => '_linea_name',
							'value'   => $b,
							'compare' => 'LIKE',
						);
						$brand_or[] = array(
							'key'     => '_nome_azienda',
							'value'   => $b,
							'compare' => 'LIKE',
						);
					}
					$meta_query[] = $brand_or;
				}
			}
		}

		// IVA[]: aliquota iva.
		if ( ! empty( $_GET['iva'] ) && is_array( $_GET['iva'] ) ) {
			$iva_vals = array_values(
				array_filter(
					array_map( 'sanitize_text_field', wp_unslash( $_GET['iva'] ) ),
					function ( $v ) {
						return in_array( $v, array( '4', '10', '22' ), true );
					}
				)
			);
			if ( $iva_vals ) {
				$meta_query[] = array(
					'key'     => '_aliquota_iva',
					'value'   => $iva_vals,
					'compare' => 'IN',
				);
			}
		}

		// Senza glutine.
		if ( ! empty( $_GET['glutine'] ) ) {
			$meta_query[] = array(
				'key'     => '_senza_glutine',
				'value'   => '1',
				'compare' => '=',
			);
		}

		// Senza lattosio.
		if ( ! empty( $_GET['lattosio'] ) ) {
			$meta_query[] = array(
				'key'     => '_senza_lattosio',
				'value'   => '1',
				'compare' => '=',
			);
		}

		// Solo disponibili.
		if ( ! empty( $_GET['instock'] ) ) {
			$meta_query[] = array(
				'key'     => '_stock_status',
				'value'   => 'instock',
				'compare' => '=',
			);
		}

		// In promozione.
		if ( ! empty( $_GET['promo'] ) && function_exists( 'wc_get_product_ids_on_sale' ) ) {
			$on_sale = wc_get_product_ids_on_sale();
			if ( empty( $on_sale ) ) {
				$post__in = array( 0 );
			} else {
				$post__in = is_array( $post__in ) && $post__in
					? array_values( array_intersect( $post__in, $on_sale ) )
					: $on_sale;
				if ( empty( $post__in ) ) {
					$post__in = array( 0 );
				}
			}
		}

		if ( $meta_query ) {
			$q->set( 'meta_query', $meta_query );
		}
		if ( $tax_query ) {
			$q->set( 'tax_query', $tax_query );
		}
		if ( ! empty( $post__in ) ) {
			$q->set( 'post__in', $post__in );
		}
	}
);

/**
 * Sort options: aggiunge "newest" e "discount" agli orderby Woo nativi.
 *
 * Mapping verso label SortDropdown del Next:
 *   relevance   → menu_order (default Woo)
 *   price       → price asc
 *   price-desc  → price desc
 *   popularity  → best_sellers
 *   newest      → date desc
 *   discount    → custom (% sconto desc)
 */
add_filter(
	'woocommerce_default_catalog_orderby_options',
	function ( $opts ) {
		$opts['newest']   = __( 'Novità', 'pharmanow' );
		$opts['discount'] = __( 'Sconto %', 'pharmanow' );
		// Rimuove rating per allinearci al Next.
		unset( $opts['rating'] );
		return $opts;
	}
);

add_filter(
	'woocommerce_catalog_orderby',
	function ( $opts ) {
		$opts['newest']   = __( 'Novità', 'pharmanow' );
		$opts['discount'] = __( 'Sconto %', 'pharmanow' );
		unset( $opts['rating'] );
		return $opts;
	}
);

add_filter(
	'woocommerce_get_catalog_ordering_args',
	function ( $args ) {
		$orderby = isset( $_GET['orderby'] ) ? wc_clean( wp_unslash( $_GET['orderby'] ) ) : '';

		if ( 'newest' === $orderby ) {
			$args['orderby']  = 'date';
			$args['order']    = 'DESC';
			$args['meta_key'] = '';
		}

		if ( 'discount' === $orderby ) {
			// Prodotti in saldo prima, ordinati per % sconto decrescente.
			$on_sale = function_exists( 'wc_get_product_ids_on_sale' ) ? wc_get_product_ids_on_sale() : array();
			if ( ! empty( $on_sale ) ) {
				add_filter(
					'posts_clauses',
					function ( $clauses ) use ( $on_sale ) {
						global $wpdb;
						$ids = implode( ',', array_map( 'absint', $on_sale ) );
						$clauses['join']   .= " LEFT JOIN {$wpdb->postmeta} pn_reg ON ({$wpdb->posts}.ID = pn_reg.post_id AND pn_reg.meta_key = '_regular_price')";
						$clauses['join']   .= " LEFT JOIN {$wpdb->postmeta} pn_sale ON ({$wpdb->posts}.ID = pn_sale.post_id AND pn_sale.meta_key = '_sale_price')";
						$clauses['where']  .= " AND {$wpdb->posts}.ID IN ({$ids})";
						$clauses['orderby'] = "((CAST(pn_reg.meta_value AS DECIMAL(10,2)) - CAST(pn_sale.meta_value AS DECIMAL(10,2))) / NULLIF(CAST(pn_reg.meta_value AS DECIMAL(10,2)),0)) DESC";
						return $clauses;
					}
				);
			}
		}

		return $args;
	}
);

/**
 * Invalida cache marche quando un prodotto viene salvato.
 */
add_action(
	'save_post_product',
	function () {
		delete_transient( 'pn_catalog_brands_v1' );
	}
);
