<?php
/**
 * Theme setup: supports, menus, image sizes.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Auto-flush delle rewrite rules quando la versione del tema cambia.
 *
 * Le rewrite rules custom (/ordine/{id}/, /traccia-ordine/, /marchi/{slug}/, ecc.)
 * sono registrate via `add_rewrite_rule()` su `init`, ma WordPress le serve dalla
 * option `rewrite_rules`. Senza flush, una rule appena aggiunta non finisce nella
 * cache e l'URL ritorna 404 (cadendo poi sul template di fallback).
 *
 * Confrontiamo PN_THEME_VERSION con l'option `pn_rewrite_rules_version`. Se sono
 * diverse, flush + bump option. Una sola scrittura DB per deploy.
 *
 * Bump PN_THEME_VERSION ogni volta che aggiungi o modifichi una rewrite rule.
 */
add_action(
	'wp_loaded',
	function () {
		if ( ! defined( 'PN_THEME_VERSION' ) ) {
			return;
		}
		$current = PN_THEME_VERSION;
		$stored  = get_option( 'pn_rewrite_rules_version' );
		if ( $stored === $current ) {
			return;
		}
		flush_rewrite_rules( false );
		update_option( 'pn_rewrite_rules_version', $current, false );
	},
	99
);

add_action(
	'after_setup_theme',
	function () {
		// Core supports.
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'custom-logo' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support(
			'html5',
			array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
		);

		// WooCommerce.
		add_theme_support( 'woocommerce' );
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );

		// Menu locations.
		register_nav_menus(
			array(
				'primary'   => __( 'Menu principale (mega menu)', 'pharmanow' ),
				'secondary' => __( 'Link secondari header', 'pharmanow' ),
				'footer-1'  => __( 'Footer · Azienda', 'pharmanow' ),
				'footer-2'  => __( 'Footer · Aiuto', 'pharmanow' ),
				'footer-3'  => __( 'Footer · Legali', 'pharmanow' ),
				'footer-4'  => __( 'Footer · Social', 'pharmanow' ),
			)
		);

		// Image sizes (allineati ai breakpoint Tailwind del Next).
		add_image_size( 'pn-product-thumb', 400, 400, true );
		add_image_size( 'pn-product-card', 600, 600, true );
		add_image_size( 'pn-hero-banner', 1920, 800, true );
		add_image_size( 'pn-category-tile', 600, 400, true );

		// Carica file di traduzione.
		load_theme_textdomain( 'pharmanow', PN_THEME_DIR . '/languages' );
	}
);

// Disabilita gli stylesheet di default di WooCommerce — tutto passa da Tailwind.
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

// Renderizza il mini-cart drawer a fine body (Alpine + wc-cart-fragments live update).
add_action(
	'wp_footer',
	function () {
		if ( ! function_exists( 'WC' ) ) {
			return;
		}
		get_template_part( 'template-parts/header/mini-cart-sidebar' );
	}
);

// Inizializza Alpine.store('pnCart') con count + subtotal correnti.
// Lo store è il source-of-truth per il badge nell'header e per la free-shipping bar.
add_action(
	'wp_footer',
	function () {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return;
		}
		$count    = (int) WC()->cart->get_cart_contents_count();
		$subtotal = (float) WC()->cart->get_subtotal();
		?>
		<script>
		document.addEventListener('alpine:init', () => {
			window.Alpine.store('pnCart', {
				isOpen: false,
				count: <?php echo (int) $count; ?>,
				subtotal: <?php echo (float) $subtotal; ?>,
				open() { this.isOpen = true; document.body.style.overflow = 'hidden'; },
				close() { this.isOpen = false; document.body.style.overflow = ''; },
				toggle() { this.isOpen ? this.close() : this.open(); },
			});
		});
		</script>
		<?php
	},
	5
);
