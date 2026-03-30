<?php
/**
 * SEO: Schema.org Structured Data (JSON-LD) & Open Graph Meta Tags
 *
 * Tasks 6.19 (Schema.org) and 6.20 (Open Graph).
 * Outputs JSON-LD and OG/Twitter meta tags via wp_head.
 * Automatically skips output when a known SEO plugin is active.
 *
 * @package BizStudio
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/* =====================================================================
   Helper: detect third-party SEO plugins
   ===================================================================== */

/**
 * Check whether a popular SEO plugin is active.
 *
 * Supported: Yoast SEO, Rank Math, All in One SEO.
 *
 * @return bool
 */
function bizstudio_is_seo_plugin_active() {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$seo_plugins = [
		'wordpress-seo/wp-seo.php',                 // Yoast SEO
		'wordpress-seo-premium/wp-seo-premium.php', // Yoast SEO Premium
		'seo-by-rank-math/rank-math.php',           // Rank Math
		'all-in-one-seo-pack/all_in_one_seo_pack.php', // All in One SEO
		'all-in-one-seo-pack-pro/all_in_one_seo_pack.php', // AIOSEO Pro
	];

	foreach ( $seo_plugins as $plugin ) {
		if ( is_plugin_active( $plugin ) ) {
			return true;
		}
	}

	return false;
}

/* =====================================================================
   Schema.org builders — each returns a PHP array (not JSON)
   ===================================================================== */

/**
 * Organization schema (output on every page).
 *
 * @return array
 */
function bizstudio_schema_organization() {
	$schema = [
		'@type' => 'Organization',
		'name'  => get_bloginfo( 'name' ),
		'url'   => home_url( '/' ),
	];

	// Logo.
	$custom_logo_id = get_theme_mod( 'custom_logo' );
	if ( $custom_logo_id ) {
		$logo_url = wp_get_attachment_image_url( $custom_logo_id, 'full' );
		if ( $logo_url ) {
			$schema['logo'] = $logo_url;
		}
	}

	// Social links from Customizer (theme_mod).
	$social_keys = [
		'bizstudio_social_facebook',
		'bizstudio_social_instagram',
		'bizstudio_social_twitter',
		'bizstudio_social_linkedin',
		'bizstudio_social_youtube',
		'bizstudio_social_tiktok',
		'bizstudio_social_pinterest',
	];

	$same_as = [];
	foreach ( $social_keys as $key ) {
		$url = get_theme_mod( $key, '' );
		if ( $url ) {
			$same_as[] = esc_url( $url );
		}
	}

	if ( $same_as ) {
		$schema['sameAs'] = $same_as;
	}

	return $schema;
}

/**
 * WebSite schema (output on front page only).
 *
 * Includes a SearchAction pointing to the WooCommerce product search
 * when WC is active, or the default WP search otherwise.
 *
 * @return array
 */
function bizstudio_schema_website() {
	$schema = [
		'@type' => 'WebSite',
		'name'  => get_bloginfo( 'name' ),
		'url'   => home_url( '/' ),
	];

	// SearchAction — product search when WC is available.
	$search_url = class_exists( 'WooCommerce' )
		? home_url( '/?s={search_term_string}&post_type=product' )
		: home_url( '/?s={search_term_string}' );

	$schema['potentialAction'] = [
		'@type'       => 'SearchAction',
		'target'      => $search_url,
		'query-input' => 'required name=search_term_string',
	];

	return $schema;
}

/**
 * BreadcrumbList schema (shop, category, single product).
 *
 * Builds the list from WooCommerce's own breadcrumb data when available.
 *
 * @return array|null Null when breadcrumbs cannot be determined.
 */
function bizstudio_schema_breadcrumbs() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return null;
	}

	// Only on relevant WC pages.
	if ( ! is_shop() && ! is_product_category() && ! is_product_tag() && ! is_product() ) {
		return null;
	}

	if ( ! function_exists( 'wc_get_breadcrumb' ) ) {
		return null;
	}

	$raw = wc_get_breadcrumb();
	if ( empty( $raw ) || ! is_array( $raw ) ) {
		return null;
	}

	$items = [];
	foreach ( $raw as $position => $crumb ) {
		$item = [
			'@type'    => 'ListItem',
			'position' => $position + 1,
			'name'     => $crumb[0],
		];

		if ( ! empty( $crumb[1] ) ) {
			$item['item'] = esc_url( $crumb[1] );
		}

		$items[] = $item;
	}

	if ( empty( $items ) ) {
		return null;
	}

	return [
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $items,
	];
}

/**
 * Product schema (single product page only).
 *
 * @return array|null
 */
function bizstudio_schema_product() {
	if ( ! class_exists( 'WooCommerce' ) || ! is_product() ) {
		return null;
	}

	global $product;
	if ( ! $product instanceof WC_Product ) {
		$product = wc_get_product( get_the_ID() );
	}
	if ( ! $product ) {
		return null;
	}

	// Availability mapping.
	$availability_map = [
		'instock'     => 'https://schema.org/InStock',
		'outofstock'  => 'https://schema.org/OutOfStock',
		'onbackorder' => 'https://schema.org/PreOrder',
	];

	$stock_status = $product->get_stock_status();
	$availability = isset( $availability_map[ $stock_status ] )
		? $availability_map[ $stock_status ]
		: 'https://schema.org/InStock';

	$schema = [
		'@type'       => 'Product',
		'name'        => $product->get_name(),
		'description' => wp_strip_all_tags( $product->get_short_description() ?: $product->get_description() ),
		'url'         => get_permalink( $product->get_id() ),
	];

	// Image.
	$image_id = $product->get_image_id();
	if ( $image_id ) {
		$image_url = wp_get_attachment_image_url( $image_id, 'full' );
		if ( $image_url ) {
			$schema['image'] = $image_url;
		}
	}

	// SKU.
	$sku = $product->get_sku();
	if ( $sku ) {
		$schema['sku'] = $sku;
	}

	// Brand — try product attribute "brand", then first product category.
	$brand = '';
	$brand_attr = $product->get_attribute( 'brand' );
	if ( $brand_attr ) {
		$brand = $brand_attr;
	} else {
		$terms = get_the_terms( $product->get_id(), 'product_cat' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			$brand = $terms[0]->name;
		}
	}
	if ( $brand ) {
		$schema['brand'] = [
			'@type' => 'Brand',
			'name'  => $brand,
		];
	}

	// Offers.
	$price    = $product->get_price();
	$currency = function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'EUR';

	$schema['offers'] = [
		'@type'         => 'Offer',
		'price'         => $price,
		'priceCurrency' => $currency,
		'availability'  => $availability,
		'url'           => get_permalink( $product->get_id() ),
	];

	// AggregateRating (only when reviews exist).
	$review_count = $product->get_review_count();
	$average      = $product->get_average_rating();
	if ( $review_count > 0 && $average > 0 ) {
		$schema['aggregateRating'] = [
			'@type'       => 'AggregateRating',
			'ratingValue' => $average,
			'reviewCount' => $review_count,
		];
	}

	return $schema;
}

/* =====================================================================
   Hook: output JSON-LD in <head>  (priority 1)
   ===================================================================== */

add_action( 'wp_head', 'bizstudio_output_schema_jsonld', 1 );

/**
 * Print all applicable Schema.org JSON-LD blocks.
 */
function bizstudio_output_schema_jsonld() {
	if ( bizstudio_is_seo_plugin_active() ) {
		return;
	}

	$schemas = [];

	// Organization — every page.
	$schemas[] = bizstudio_schema_organization();

	// WebSite — front page only.
	if ( is_front_page() ) {
		$schemas[] = bizstudio_schema_website();
	}

	// BreadcrumbList — WC shop / category / product.
	$breadcrumbs = bizstudio_schema_breadcrumbs();
	if ( $breadcrumbs ) {
		$schemas[] = $breadcrumbs;
	}

	// Product — single product page.
	$product_schema = bizstudio_schema_product();
	if ( $product_schema ) {
		$schemas[] = $product_schema;
	}

	if ( empty( $schemas ) ) {
		return;
	}

	// Wrap in a @graph for a single <script> block.
	$output = [
		'@context' => 'https://schema.org',
		'@graph'   => $schemas,
	];

	echo '<script type="application/ld+json">' . "\n";
	echo wp_json_encode( $output, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
	echo "\n" . '</script>' . "\n";
}

/* =====================================================================
   Hook: output Open Graph & Twitter Card meta tags  (priority 2)
   ===================================================================== */

add_action( 'wp_head', 'bizstudio_output_open_graph', 2 );

/**
 * Print Open Graph and Twitter Card meta tags.
 */
function bizstudio_output_open_graph() {
	if ( bizstudio_is_seo_plugin_active() ) {
		return;
	}

	$og = [];

	// --- og:site_name ---
	$og['og:site_name'] = get_bloginfo( 'name' );

	// --- og:locale ---
	$og['og:locale'] = get_locale();

	// --- og:url ---
	if ( is_singular() ) {
		$og['og:url'] = get_permalink();
	} elseif ( is_front_page() ) {
		$og['og:url'] = home_url( '/' );
	} elseif ( class_exists( 'WooCommerce' ) && is_shop() ) {
		$og['og:url'] = get_permalink( wc_get_page_id( 'shop' ) );
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$og['og:url'] = get_term_link( get_queried_object() );
	} else {
		$og['og:url'] = home_url( $_SERVER['REQUEST_URI'] ?? '/' );
	}

	// --- og:type ---
	if ( is_front_page() ) {
		$og['og:type'] = 'website';
	} elseif ( class_exists( 'WooCommerce' ) && is_product() ) {
		$og['og:type'] = 'product';
	} else {
		$og['og:type'] = 'article';
	}

	// --- og:title ---
	if ( is_singular() ) {
		$og['og:title'] = get_the_title();
	} elseif ( is_front_page() ) {
		$og['og:title'] = get_bloginfo( 'name' );
	} elseif ( class_exists( 'WooCommerce' ) && is_shop() ) {
		$og['og:title'] = wc_get_page_id( 'shop' ) ? get_the_title( wc_get_page_id( 'shop' ) ) : __( 'Shop', 'bizstudio' );
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$og['og:title'] = single_term_title( '', false );
	} else {
		$og['og:title'] = wp_get_document_title();
	}

	// --- og:description ---
	if ( class_exists( 'WooCommerce' ) && is_product() ) {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			$product = wc_get_product( get_the_ID() );
		}
		if ( $product ) {
			$desc = wp_strip_all_tags( $product->get_short_description() ?: $product->get_description() );
		} else {
			$desc = get_bloginfo( 'description' );
		}
	} elseif ( is_singular() && has_excerpt() ) {
		$desc = wp_strip_all_tags( get_the_excerpt() );
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$desc = wp_strip_all_tags( term_description() );
	} else {
		$desc = get_bloginfo( 'description' );
	}
	// Trim to 200 chars for meta.
	if ( $desc ) {
		$desc = mb_substr( $desc, 0, 200 );
		$og['og:description'] = $desc;
	}

	// --- og:image ---
	$image = '';
	if ( is_singular() && has_post_thumbnail() ) {
		$image = get_the_post_thumbnail_url( get_the_ID(), 'large' );
	} elseif ( class_exists( 'WooCommerce' ) && is_product_category() ) {
		$term = get_queried_object();
		$thumb_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
		if ( $thumb_id ) {
			$image = wp_get_attachment_image_url( $thumb_id, 'large' );
		}
	}
	// Fallback to custom logo.
	if ( ! $image ) {
		$custom_logo_id = get_theme_mod( 'custom_logo' );
		if ( $custom_logo_id ) {
			$image = wp_get_attachment_image_url( $custom_logo_id, 'full' );
		}
	}
	if ( $image ) {
		$og['og:image'] = $image;
	}

	// --- Product-specific OG tags ---
	if ( class_exists( 'WooCommerce' ) && is_product() ) {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			$product = wc_get_product( get_the_ID() );
		}
		if ( $product ) {
			$og['product:price:amount']   = $product->get_price();
			$og['product:price:currency'] = get_woocommerce_currency();

			$stock_status = $product->get_stock_status();
			$og_avail_map = [
				'instock'     => 'instock',
				'outofstock'  => 'oos',
				'onbackorder' => 'pending',
			];
			$og['og:availability'] = isset( $og_avail_map[ $stock_status ] )
				? $og_avail_map[ $stock_status ]
				: 'instock';
		}
	}

	// --- Print OG tags ---
	echo "\n<!-- BizStudio Open Graph -->\n";
	foreach ( $og as $property => $content ) {
		printf(
			'<meta property="%s" content="%s">' . "\n",
			esc_attr( $property ),
			esc_attr( $content )
		);
	}

	// --- Twitter Card tags ---
	$twitter = [
		'twitter:card'        => $image ? 'summary_large_image' : 'summary',
		'twitter:title'       => $og['og:title'] ?? '',
		'twitter:description' => $og['og:description'] ?? '',
	];
	if ( $image ) {
		$twitter['twitter:image'] = $image;
	}

	echo "<!-- BizStudio Twitter Card -->\n";
	foreach ( $twitter as $name => $content ) {
		if ( ! $content ) {
			continue;
		}
		printf(
			'<meta name="%s" content="%s">' . "\n",
			esc_attr( $name ),
			esc_attr( $content )
		);
	}
}
