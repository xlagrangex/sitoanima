<?php
/**
 * Enqueue scripts and styles
 *
 * @package BizStudio
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', function () {
	$ver = BIZSTUDIO_VERSION;

	// Main CSS — for MVP we load from src directly (no Vite build needed yet)
	wp_enqueue_style(
		'bizstudio-main',
		BIZSTUDIO_URI . '/assets/src/css/main.css',
		[],
		$ver
	);

	// Universal form styling
	wp_enqueue_style(
		'bizstudio-forms',
		BIZSTUDIO_URI . '/assets/src/css/components/forms.css',
		[ 'bizstudio-main' ],
		$ver
	);

	// Animations (parallax, hover, transitions, skeleton, back-to-top)
	wp_enqueue_style(
		'bizstudio-animations',
		BIZSTUDIO_URI . '/assets/src/css/components/animations.css',
		[ 'bizstudio-main' ],
		$ver
	);

	// WooCommerce CSS
	if ( class_exists( 'WooCommerce' ) ) {
		wp_enqueue_style(
			'bizstudio-woocommerce',
			BIZSTUDIO_URI . '/assets/src/css/woocommerce/shop.css',
			[ 'bizstudio-main' ],
			$ver
		);

		// Cart & Checkout block CSS
		if ( is_cart() || is_checkout() ) {
			wp_enqueue_style(
				'bizstudio-cart-checkout',
				BIZSTUDIO_URI . '/assets/src/css/woocommerce/cart-checkout.css',
				[ 'bizstudio-main' ],
				$ver
			);
		}

		// Full-page checkout (minimal chrome — no header/footer)
		if ( is_checkout() && ! is_wc_endpoint_url() ) {
			wp_enqueue_style(
				'bizstudio-checkout-fullpage',
				BIZSTUDIO_URI . '/assets/src/css/woocommerce/checkout-fullpage.css',
				[ 'bizstudio-main', 'bizstudio-cart-checkout' ],
				$ver
			);
		}

		// My Account CSS
		if ( is_account_page() ) {
			wp_enqueue_style(
				'bizstudio-account',
				BIZSTUDIO_URI . '/assets/src/css/woocommerce/account.css',
				[ 'bizstudio-main' ],
				$ver
			);
		}
	}

	// Main JS
	wp_enqueue_script(
		'bizstudio-main',
		BIZSTUDIO_URI . '/assets/src/js/main.js',
		[],
		$ver,
		[ 'strategy' => 'defer', 'in_footer' => true ]
	);

	// Pass data to JS
	wp_localize_script( 'bizstudio-main', 'bizstudio', [
		'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'bizstudio_nonce' ),
		'cartUrl'  => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '',
	] );

	// Animations JS (parallax, page transitions, loading bar, smooth scroll, back-to-top)
	wp_enqueue_script(
		'bizstudio-animations',
		BIZSTUDIO_URI . '/assets/src/js/modules/animations.js',
		[ 'bizstudio-main' ],
		$ver,
		[ 'strategy' => 'defer', 'in_footer' => true ]
	);

	// WooCommerce JS modules
	if ( class_exists( 'WooCommerce' ) ) {
		// Quick view (on all pages with product cards)
		wp_enqueue_script(
			'bizstudio-quick-view',
			BIZSTUDIO_URI . '/assets/src/js/modules/quick-view.js',
			[ 'bizstudio-main' ],
			$ver,
			[ 'strategy' => 'defer', 'in_footer' => true ]
		);

		// Live search
		wp_enqueue_script(
			'bizstudio-search',
			BIZSTUDIO_URI . '/assets/src/js/modules/search.js',
			[ 'bizstudio-main' ],
			$ver,
			[ 'strategy' => 'defer', 'in_footer' => true ]
		);

		// Quantity +/- buttons
		wp_enqueue_script(
			'bizstudio-quantity',
			BIZSTUDIO_URI . '/assets/src/js/modules/quantity.js',
			[ 'bizstudio-main' ],
			$ver,
			[ 'strategy' => 'defer', 'in_footer' => true ]
		);

	// Mini-cart JS
		wp_enqueue_script(
			'bizstudio-mini-cart',
			BIZSTUDIO_URI . '/assets/src/js/modules/mini-cart.js',
			[ 'bizstudio-main' ],
			$ver,
			[ 'strategy' => 'defer', 'in_footer' => true ]
		);

		// Single product CSS + JS
		if ( is_product() ) {
			wp_enqueue_style(
				'bizstudio-product',
				BIZSTUDIO_URI . '/assets/src/css/woocommerce/product.css',
				[ 'bizstudio-main' ],
				$ver
			);
			wp_enqueue_script(
				'bizstudio-gallery',
				BIZSTUDIO_URI . '/assets/src/js/modules/gallery.js',
				[ 'bizstudio-main' ],
				$ver,
				[ 'strategy' => 'defer', 'in_footer' => true ]
			);
			wp_enqueue_script(
				'bizstudio-single-atc',
				BIZSTUDIO_URI . '/assets/src/js/modules/single-add-to-cart.js',
				[ 'bizstudio-main' ],
				$ver,
				[ 'strategy' => 'defer', 'in_footer' => true ]
			);
			wp_enqueue_script(
				'bizstudio-swatches',
				BIZSTUDIO_URI . '/assets/src/js/modules/swatches.js',
				[ 'bizstudio-main' ],
				$ver,
				[ 'strategy' => 'defer', 'in_footer' => true ]
			);
		}

		// Shop page JS
		if ( is_shop() || is_product_category() || is_product_tag() ) {
			wp_enqueue_script(
				'bizstudio-shop',
				BIZSTUDIO_URI . '/assets/src/js/modules/shop.js',
				[ 'bizstudio-main' ],
				$ver,
				[ 'strategy' => 'defer', 'in_footer' => true ]
			);
		}
	}
} );

// Dequeue default WooCommerce styles (we style everything ourselves)
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
