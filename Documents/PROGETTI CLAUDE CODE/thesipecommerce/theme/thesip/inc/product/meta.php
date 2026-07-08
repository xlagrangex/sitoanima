<?php
/**
 * Helper di accesso ai metadati pharma del prodotto.
 *
 * Mappa le meta keys reali del DB Pharmanow (sync Farmadati) ai dati
 * che il PDP del Next consumava da `product.metadata.*`.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Estrae una sezione delimitata da `<b>Label</b>` dentro l'HTML del bugiardino.
 * Le sezioni terminano al successivo `<b>...</b>` o alla fine.
 *
 * @param string $html  Bugiardino HTML.
 * @param array  $labels Array di label da cercare in priorità (es. ['Composizione', 'Ingredienti']).
 * @return string|null
 */
function pn_extract_bugiardino_section( string $html, array $labels ): ?string {
	if ( '' === $html ) {
		return null;
	}

	foreach ( $labels as $label ) {
		$pattern = '#<b>\s*' . preg_quote( $label, '#' ) . '\s*</b>(.*?)(?=<b>|$)#si';
		if ( preg_match( $pattern, $html, $m ) ) {
			$section = trim( $m[1] );
			$section = preg_replace( '#^\s*<br\s*/?>\s*#i', '', $section );
			if ( '' !== $section ) {
				return $section;
			}
		}
	}
	return null;
}

/**
 * Oggetto pharma normalizzato per un prodotto.
 *
 * @param WC_Product $product
 * @return array{
 *   minsan:string, ean:string, brand_name:string, brand_slug:string, iva:string,
 *   bugiardino:?string, descrizione_estesa:?string, composizione:?string, avvertenze:?string,
 *   conservazione:?string, has_glutine:bool, has_lattosio:bool,
 *   senza_glutine:bool, senza_lattosio:bool, integratore:bool, biologico:bool,
 *   dispositivo_medico:bool, sostituito_da:int, sostituito_at:string,
 *   tags:array<int,string>
 * }
 */
function pn_pharma_meta( WC_Product $product ): array {
	$id = $product->get_id();

	$bugiardino = (string) $product->get_meta( '_bugiardino_html' );
	$bugiardino = '' !== $bugiardino ? $bugiardino : (string) $product->get_meta( 'bugiardino' );

	// Sezioni parsate dal bugiardino HTML (alcune meta keys non esistono come campi separati).
	$composizione        = pn_extract_bugiardino_section( $bugiardino, array( 'Composizione', 'Ingredienti' ) );
	$avvertenze          = pn_extract_bugiardino_section( $bugiardino, array( 'Avvertenze' ) );
	$descrizione_estesa  = pn_extract_bugiardino_section( $bugiardino, array( 'Descrizione' ) );
	$conservazione       = pn_extract_bugiardino_section( $bugiardino, array( 'Conservazione', 'Modalit&agrave; di conservazione', 'Modalità di conservazione' ) );

	$senza_glutine  = $product->get_meta( '_senza_glutine' );
	$senza_lattosio = $product->get_meta( '_senza_lattosio' );

	// Brand: priorità _brand_name → _linea_name → _nome_azienda (ragione sociale,
	// stripata di " Srl/SpA/SRL/SPA"). Sul DB Pharmanow _brand_name è vuoto su
	// tutti i record; _nome_azienda è popolato (es. "ZUCCARI Srl").
	$brand_name = trim( (string) $product->get_meta( '_brand_name' ) );
	if ( '' === $brand_name ) {
		$brand_name = trim( (string) $product->get_meta( '_linea_name' ) );
	}
	if ( '' === $brand_name ) {
		$azienda = trim( (string) $product->get_meta( '_nome_azienda' ) );
		if ( '' !== $azienda ) {
			$azienda    = preg_replace( '/\s+(s\.?\s*r\.?\s*l\.?|s\.?\s*p\.?\s*a\.?|spa|srl|s\.a\.s\.|sas)\s*$/i', '', $azienda );
			$brand_name = trim( $azienda );
		}
	}
	// Brand slug: prima prova match diretto, poi cerca term assegnato al prodotto,
	// poi LIKE prefix (gestisce "ZUCCARI" → "ZUCCARI Srl").
	$brand_slug = '';
	if ( '' !== $brand_name && taxonomy_exists( 'product_brand' ) ) {
		// (1) match esatto sul nome strippato.
		$term = get_term_by( 'name', $brand_name, 'product_brand' );
		if ( $term && ! is_wp_error( $term ) ) {
			$brand_slug = $term->slug;
		}
		// (2) term effettivamente assegnato al prodotto (più affidabile).
		if ( '' === $brand_slug ) {
			$assigned = wp_get_post_terms( $id, 'product_brand', array( 'fields' => 'all' ) );
			if ( ! is_wp_error( $assigned ) && ! empty( $assigned ) ) {
				$brand_slug = $assigned[0]->slug;
				if ( '' === $brand_name || stripos( $assigned[0]->name, $brand_name ) === 0 ) {
					$brand_name = pn_strip_company_suffix( $assigned[0]->name );
				}
			}
		}
		// (3) LIKE prefix (es. "ZUCCARI" → "ZUCCARI Srl").
		if ( '' === $brand_slug ) {
			global $wpdb;
			$like = $wpdb->esc_like( $brand_name ) . '%';
			$slug = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT t.slug FROM {$wpdb->terms} t
					 INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
					 WHERE tt.taxonomy = 'product_brand' AND t.name LIKE %s
					 ORDER BY LENGTH(t.name) ASC LIMIT 1",
					$like
				)
			);
			if ( $slug ) {
				$brand_slug = $slug;
			}
		}
	}

	$tags = array();
	foreach ( wp_get_post_terms( $id, 'product_tag', array( 'fields' => 'names' ) ) as $tag_name ) {
		$tags[] = strtolower( $tag_name );
	}
	// Auto-tag derivati da meta pharma.
	if ( $product->is_on_sale() ) {
		$tags[] = 'promo';
	}
	if ( ( time() - get_post_time( 'U', false, $id ) ) < 30 * DAY_IN_SECONDS ) {
		$tags[] = 'novita';
	}
	$tags = array_values( array_unique( $tags ) );

	$sostituito_da = (int) $product->get_meta( '_sostituito_da' );
	if ( ! $sostituito_da ) {
		// `_farmadati_replaced_by` può essere un MINSAN: risolve a post_id.
		$replaced_minsan = (string) $product->get_meta( '_farmadati_replaced_by' );
		if ( '' !== $replaced_minsan ) {
			$lookup = get_posts(
				array(
					'post_type'      => 'product',
					'posts_per_page' => 1,
					'fields'         => 'ids',
					'meta_query'     => array(
						array(
							'key'   => '_codice_minsan',
							'value' => $replaced_minsan,
						),
					),
				)
			);
			if ( ! empty( $lookup ) ) {
				$sostituito_da = (int) $lookup[0];
			}
		}
	}

	$sostituito_at = (string) $product->get_meta( '_farmadati_substitution_date' );
	if ( '' === $sostituito_at ) {
		$sostituito_at = (string) $product->get_meta( '_farmadati_deactivated_at' );
	}

	return array(
		'minsan'              => (string) ( $product->get_meta( '_codice_minsan' ) ?: $product->get_sku() ),
		'ean'                 => (string) $product->get_meta( '_ean' ),
		'brand_name'          => $brand_name,
		'brand_slug'          => $brand_slug,
		'iva'                 => (string) $product->get_meta( '_aliquota_iva' ),
		'bugiardino'          => '' !== $bugiardino ? $bugiardino : null,
		'descrizione_estesa'  => $descrizione_estesa,
		'composizione'        => $composizione,
		'avvertenze'          => $avvertenze,
		'conservazione'       => $conservazione,
		'senza_glutine'       => '1' === (string) $senza_glutine,
		'senza_lattosio'      => '1' === (string) $senza_lattosio,
		'integratore'         => '1' === (string) $product->get_meta( '_integratore' ),
		'biologico'           => '1' === (string) $product->get_meta( '_biologico' ),
		'dispositivo_medico'  => '1' === (string) $product->get_meta( '_dispositivo_medico_ce' ),
		'sostituito_da'       => $sostituito_da,
		'sostituito_at'       => $sostituito_at,
		'tags'                => $tags,
	);
}

/**
 * Sanitizza l'HTML del bugiardino con la stessa whitelist di BugiardinoRenderer.tsx.
 *
 * @param string $html
 * @return string
 */
function pn_render_bugiardino( string $html ): string {
	$allowed = array(
		'p'          => array( 'class' => true ),
		'h1'         => array( 'class' => true ),
		'h2'         => array( 'class' => true ),
		'h3'         => array( 'class' => true ),
		'h4'         => array( 'class' => true ),
		'h5'         => array( 'class' => true ),
		'strong'     => array(),
		'em'         => array(),
		'u'          => array(),
		'b'          => array(),
		'i'          => array(),
		'ul'         => array(),
		'ol'         => array(),
		'li'         => array(),
		'table'      => array( 'border' => true, 'cellspacing' => true, 'cellpadding' => true ),
		'thead'      => array(),
		'tbody'      => array(),
		'tr'         => array( 'align' => true ),
		'td'         => array( 'align' => true, 'colspan' => true, 'rowspan' => true ),
		'th'         => array( 'align' => true, 'colspan' => true, 'rowspan' => true ),
		'br'         => array(),
		'hr'         => array(),
		'a'          => array( 'href' => true, 'title' => true, 'target' => true, 'rel' => true ),
		'span'       => array( 'class' => true ),
		'div'        => array( 'class' => true, 'align' => true ),
		'blockquote' => array(),
	);
	return wp_kses( $html, $allowed );
}
