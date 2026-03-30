<?php
/**
 * WooCommerce Integration
 *
 * @package BizStudio
 */

defined( 'ABSPATH' ) || exit;

// Declare WooCommerce support
add_action( 'after_setup_theme', function () {
	add_theme_support( 'woocommerce', [
		'thumbnail_image_width' => 400,
		'single_image_width'    => 800,
		'product_grid'          => [
			'default_rows'    => 4,
			'min_rows'        => 1,
			'default_columns' => 4,
			'min_columns'     => 2,
			'max_columns'     => 5,
		],
	] );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
} );

// Full-page checkout template (Shopify-style) — no header/footer
add_filter( 'template_include', function ( $template ) {
	if ( is_checkout() && ! is_wc_endpoint_url() ) {
		$checkout_tpl = get_template_directory() . '/woocommerce/checkout.php';
		if ( file_exists( $checkout_tpl ) ) {
			return $checkout_tpl;
		}
	}
	return $template;
} );

// HPOS compatibility
add_action( 'before_woocommerce_init', function () {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

// Products per page
add_filter( 'loop_shop_per_page', function () {
	return 12;
} );

// Products per row
add_filter( 'loop_shop_columns', function () {
	return 4;
} );

// Remove default WooCommerce wrappers
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

add_action( 'woocommerce_before_main_content', function () {
	echo '<main class="biz-main"><div class="biz-container">';
}, 10 );

add_action( 'woocommerce_after_main_content', function () {
	echo '</div></main>';
}, 10 );

// Custom product card — remove defaults, add our own
remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

// Use our custom product card template
add_action( 'woocommerce_before_shop_loop_item', function () {
	get_template_part( 'template-parts/product/card-product' );
}, 10 );

// Prevent WooCommerce from rendering its own content after our card
add_filter( 'woocommerce_before_shop_loop_item_title', '__return_empty_string', 99 );

// Cart fragments for mini-cart
add_filter( 'woocommerce_add_to_cart_fragments', function ( $fragments ) {
	ob_start();
	get_template_part( 'template-parts/global/mini-cart' );
	$fragments['.biz-mini-cart'] = ob_get_clean();

	// Cart count
	$count = WC()->cart->get_cart_contents_count();
	$fragments['.biz-cart-count'] = '<span class="biz-cart-count">' . $count . '</span>';

	return $fragments;
} );

// Translate WooCommerce button texts
add_filter( 'woocommerce_product_single_add_to_cart_text', function () {
	return __( 'Aggiungi al carrello', 'bizstudio' );
} );

add_filter( 'woocommerce_product_add_to_cart_text', function () {
	return __( 'Aggiungi al carrello', 'bizstudio' );
} );

// Enable AJAX add-to-cart on single product pages (WC disables it by default)
add_theme_support( 'wc-product-gallery-slider' );

add_filter( 'woocommerce_product_single_add_to_cart_text', function ( $text ) {
	return __( 'Aggiungi al carrello', 'bizstudio' );
} );

// Add data attributes to single product add-to-cart button for AJAX
add_filter( 'woocommerce_after_add_to_cart_button', function () {
	global $product;
	if ( ! $product ) return;
	?>
	<input type="hidden" name="bizstudio_ajax_add" value="1">
	<?php
} );

// Breadcrumb args
add_filter( 'woocommerce_breadcrumb_defaults', function () {
	return [
		'delimiter'   => ' <span class="biz-breadcrumb__sep">/</span> ',
		'wrap_before' => '<nav class="biz-breadcrumb" aria-label="Breadcrumb"><div class="biz-container">',
		'wrap_after'  => '</div></nav>',
		'before'      => '<span class="biz-breadcrumb__item">',
		'after'       => '</span>',
		'home'        => __( 'Home', 'bizstudio' ),
	];
} );
