<?php
/**
 * Frontend asset enqueue.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'wp_enqueue_scripts',
	function () {
		// Geist font (Google Fonts CDN per ora; self-host in fase successiva).
		wp_enqueue_style(
			'pn-fonts',
			'https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&display=swap',
			array(),
			null
		);

		// Main CSS compilato da Tailwind.
		$css_path = PN_THEME_DIR . '/assets/dist/main.css';
		$css_ver  = file_exists( $css_path ) ? filemtime( $css_path ) : PN_THEME_VERSION;
		wp_enqueue_style(
			'pn-main',
			PN_THEME_URI . '/assets/dist/main.css',
			array( 'pn-fonts' ),
			$css_ver
		);

		// Main JS bundle (Alpine + componenti).
		$js_path = PN_THEME_DIR . '/assets/dist/main.js';
		$js_ver  = file_exists( $js_path ) ? filemtime( $js_path ) : PN_THEME_VERSION;
		wp_enqueue_script(
			'pn-main',
			PN_THEME_URI . '/assets/dist/main.js',
			array( 'wp-data' ),
			$js_ver,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);

		// style.css (solo per header WP).
		wp_enqueue_style(
			'pn-theme',
			get_stylesheet_uri(),
			array( 'pn-main' ),
			PN_THEME_VERSION
		);

		// WooCommerce: cart fragments + ajax add-to-cart per i Block (popolano il Mini Cart Block)
		if ( function_exists( 'WC' ) ) {
			wp_enqueue_script( 'wc-cart-fragments' );
			wp_enqueue_script( 'wc-add-to-cart' );
		}
	}
);

// Rimuovi le 2 wp_localize_script residue: ora i Block hanno tutto loro.
// PN_DATA rimane per altre integrazioni (newsletter, search, ecc.)
add_action(
	'wp_enqueue_scripts',
	function () {
		// Cleanup: niente più PN_CART, lo gestisce il Block via Store API.
	},
	30
);

// Espone variabili PHP a JS via wp_localize_script (cart fragments threshold, ecc.).
add_action(
	'wp_enqueue_scripts',
	function () {
		wp_localize_script(
			'pn-main',
			'PN_DATA',
			array(
				'rest_url'                => esc_url_raw( rest_url( 'pharmanow/v1/' ) ),
				'nonce'                   => wp_create_nonce( 'wp_rest' ),
				'home_url'                => esc_url( home_url( '/' ) ),
				'free_shipping_threshold' => pn_get_free_shipping_threshold(),
				'standard_shipping_cost'  => (float) get_theme_mod( 'pn_standard_shipping_cost', 4.90 ),
				'currency_symbol'         => function_exists( 'get_woocommerce_currency_symbol' ) ? html_entity_decode( get_woocommerce_currency_symbol() ) : '€',
			)
		);
	},
	20
);
