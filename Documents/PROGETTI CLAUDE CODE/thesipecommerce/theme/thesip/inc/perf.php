<?php
/**
 * Performance: dequeue di asset non necessari sul frontend.
 *
 * Lighthouse audit (mobile, Slow 4G):
 *   - block-library/style.min.css   117 KiB CSS Gutenberg, 100% inutile sul tema
 *   - wc-blocks.css                  14 KiB
 *   - styles.css (Stripe)            22 KiB
 *   - 30+ script WC Blocks (react,react-dom,lodash,wp-data,...) 250+ KiB
 *
 * Strategia: il tema NON usa Gutenberg block frontend né WC Blocks (cart e
 * checkout sono Block Gutenberg ma solo su /carrello/ e /pagamento/). Sulle
 * altre route togliamo gli stili e gli script.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * True se la pagina corrente NON richiede WooCommerce Blocks (cart/checkout).
 */
function pn_perf_needs_wc_blocks(): bool {
	if ( ! function_exists( 'is_cart' ) ) {
		return false;
	}
	return is_cart() || is_checkout() || is_account_page();
}

/**
 * Dequeue Gutenberg core CSS frontend ovunque (il tema non lo usa).
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		// Gutenberg block-library: 117 KiB di CSS quasi sempre inutile.
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );

		// Su pagine che NON sono cart/checkout/account, dequeue anche WC Blocks.
		if ( ! pn_perf_needs_wc_blocks() ) {
			wp_dequeue_style( 'wc-blocks-style' );
			wp_dequeue_style( 'wc-block-style' );
			wp_dequeue_style( 'wc-blocks-vendors-style' );
			// Stripe UPE blocks CSS solo sul checkout.
			wp_dequeue_style( 'wc-stripe-blocks-checkout-style' );
		}
	},
	100
);

/**
 * Rimuove jQuery Migrate (solo per WP < 5.5 compat, non ci serve).
 */
add_action(
	'wp_default_scripts',
	function ( $scripts ) {
		if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
			$deps = $scripts->registered['jquery']->deps;
			$scripts->registered['jquery']->deps = array_values( array_diff( $deps, array( 'jquery-migrate' ) ) );
		}
	}
);

/**
 * Defer SOLO degli script "foglia" sicuri.
 *
 * NON deferire mai: jQuery, le librerie core WP/JS (wp-*, lodash, underscore,
 * react…) e qualunque script con codice inline (-after/-before/-translations)
 * o da cui dipendono altri script. Motivo: i blocchi inline che WordPress stampa
 * (es. `wp.i18n.setLocaleData(...)`, `window.lodash = _.noConflict()`) NON sono
 * deferibili e girano durante il parsing, PRIMA della libreria deferita → errori
 * "wp is not defined" / "_ is not defined" che rompevano il checkout e il
 * rendering del bottone PayPal (vendite perse). Vedi indagine 2026-05-29.
 */
add_filter(
	'script_loader_tag',
	function ( $tag, $handle ) {
		if ( is_admin() ) {
			return $tag;
		}
		// jQuery: blocking (altri inline WP lo usano sync).
		if ( in_array( $handle, array( 'jquery-core', 'jquery-migrate', 'jquery' ), true ) ) {
			return $tag;
		}
		// Librerie core WP/JS: hanno inline (-after/-translations) che dipende da
		// loro → mai deferire.
		if ( 0 === strpos( $handle, 'wp-' )
			|| in_array( $handle, array( 'lodash', 'underscore', 'backbone', 'react', 'react-dom', 'react-jsx-runtime', 'moment' ), true )
		) {
			return $tag;
		}

		$scripts = wp_scripts();
		if ( $scripts && isset( $scripts->registered[ $handle ] ) ) {
			$obj = $scripts->registered[ $handle ];
			// Script con codice inline o traduzioni: il blocco inline non è
			// deferibile e dipende dallo script → deve restare blocking.
			if ( ! empty( $obj->extra['after'] ) || ! empty( $obj->extra['before'] ) || ! empty( $obj->textdomain ) ) {
				return $tag;
			}
			// Se altri script dipendono da questo, deve caricarsi prima: niente defer.
			foreach ( $scripts->registered as $other ) {
				if ( in_array( $handle, $other->deps, true ) ) {
					return $tag;
				}
			}
		}

		// Script foglia senza inline/dipendenti: defer sicuro.
		if ( false === strpos( $tag, ' defer' ) && false === strpos( $tag, ' async' ) ) {
			$tag = str_replace( ' src=', ' defer src=', $tag );
		}
		return $tag;
	},
	10,
	2
);

/**
 * Disabilita emoji core WP (mai usati, 8 KiB di JS inline + script extra).
 */
add_action(
	'init',
	function () {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	}
);

/**
 * Preconnect/dns-prefetch per origini critiche (riduce setup TLS).
 */
add_filter(
	'wp_resource_hints',
	function ( $hints, $relation ) {
		if ( 'preconnect' === $relation ) {
			// Self-host Geist font: fonts.gstatic.com solo se usiamo Google Fonts.
			$hints[] = array(
				'href'        => 'https://fonts.gstatic.com',
				'crossorigin' => 'anonymous',
			);
		}
		return $hints;
	},
	10,
	2
);
