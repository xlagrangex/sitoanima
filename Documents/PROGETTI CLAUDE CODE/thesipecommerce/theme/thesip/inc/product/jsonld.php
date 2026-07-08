<?php
/**
 * JSON-LD schema su single-product (Product + BreadcrumbList).
 *
 * Replica `lib/seo/jsonld.ts` del Next.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'wp_head',
	function () {
		if ( ! is_singular( 'product' ) ) {
			return;
		}
		global $product;
		if ( ! $product instanceof WC_Product ) {
			$product = wc_get_product( get_queried_object_id() );
		}
		if ( ! $product ) {
			return;
		}

		$meta    = pn_pharma_meta( $product );
		$url     = get_permalink( $product->get_id() );
		$thumb   = get_the_post_thumbnail_url( $product->get_id(), 'full' );
		$regular = (float) $product->get_regular_price();
		$sale    = $product->is_on_sale() ? (float) $product->get_sale_price() : 0;
		$final   = $sale > 0 ? $sale : $regular;

		$ld_product = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'Product',
			'name'        => $product->get_name(),
			'description' => wp_strip_all_tags( $product->get_short_description() ?: ( $meta['descrizione_estesa'] ?: '' ) ),
			'sku'         => $product->get_sku() ?: $meta['minsan'],
			'offers'      => array(
				'@type'         => 'Offer',
				'url'           => $url,
				'priceCurrency' => get_woocommerce_currency(),
				'price'         => number_format( (float) $final, 2, '.', '' ),
				'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
				'itemCondition' => 'https://schema.org/NewCondition',
			),
		);
		if ( $thumb ) {
			$ld_product['image'] = array( $thumb );
		}
		if ( '' !== $meta['ean'] ) {
			$ld_product['gtin13'] = $meta['ean'];
		}
		if ( '' !== $meta['brand_name'] ) {
			$ld_product['brand'] = array(
				'@type' => 'Brand',
				'name'  => $meta['brand_name'],
			);
		}
		// Description senza HTML, max 5000 char (limite Schema).
		$ld_product['description'] = mb_substr( $ld_product['description'], 0, 5000 );

		// Breadcrumb: Home > Catalogo > [Categoria primaria] > Prodotto.
		$cats     = wp_get_post_terms( $product->get_id(), 'product_cat' );
		$bc_items = array(
			array( 'name' => 'Home', 'url' => home_url( '/' ) ),
			array( 'name' => 'Catalogo', 'url' => home_url( '/catalogo/' ) ),
		);
		if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) {
			$primary    = $cats[0];
			$bc_items[] = array(
				'name' => $primary->name,
				'url'  => get_term_link( $primary ),
			);
		}
		$bc_items[] = array( 'name' => $product->get_name() );

		$ld_breadcrumb = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => array_map(
				function ( $item, $i ) {
					$el = array(
						'@type'    => 'ListItem',
						'position' => $i + 1,
						'name'     => $item['name'],
					);
					if ( ! empty( $item['url'] ) ) {
						$el['item'] = $item['url'];
					}
					return $el;
				},
				$bc_items,
				array_keys( $bc_items )
			),
		);

		echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $ld_product, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
		echo '<script type="application/ld+json">' . wp_json_encode( $ld_breadcrumb, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}
);
