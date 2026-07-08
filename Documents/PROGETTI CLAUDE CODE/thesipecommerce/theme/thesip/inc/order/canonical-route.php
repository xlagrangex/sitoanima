<?php
/**
 * URL canonica unificata `/ordine/{id}/` (stile Shopify).
 *
 * Stessa pagina sia post-checkout (con ?key=) che da account loggato.
 * - Owner loggato → render diretto.
 * - Guest con ?key=<order_key> match → render diretto.
 * - Altrimenti → redirect a login con redirect-back.
 *
 * Hero variant scelta automaticamente: 'celebration' se ordine < 10 min, altrimenti 'info'.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'init',
	function () {
		add_rewrite_rule(
			'^ordine/([0-9]+)/?$',
			'index.php?pn_order_id=$matches[1]',
			'top'
		);
	}
);

add_filter(
	'query_vars',
	function ( $vars ) {
		$vars[] = 'pn_order_id';
		return $vars;
	}
);

/**
 * FALLBACK URI-based (super drastico).
 *
 * Object Cache Pro talvolta cachea malamente la option `rewrite_rules`,
 * facendo "scomparire" la rule `^ordine/([0-9]+)/?$` dalla cache anche se
 * regolarmente registrata su `init`. Risultato: URL /ordine/<id>/ cade in 404
 * → fallback template del tema → utente vede il placeholder "Fase 1.X".
 *
 * Soluzione: bypassiamo COMPLETAMENTE WP routing/template loader. Su
 * `wp_loaded` (DOPO che plugin/tema sono caricati, PRIMA del routing), se
 * REQUEST_URI matcha /ordine/<id>/, eseguiamo l'intero render direttamente e
 * `exit`. Non si dipende da rewrite_rules né da query vars.
 *
 * Questo handler scatta indipendentemente dallo stato della cache.
 */
add_action(
	'wp_loaded',
	function () {
		// Solo richieste GET front-end (no admin/ajax/cron/rest).
		if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}
		if ( ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) !== 'GET' ) {
			return;
		}

		$uri  = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '';
		$path = (string) parse_url( $uri, PHP_URL_PATH );

		if ( ! preg_match( '#^/ordine/(\d+)/?$#', $path, $m ) ) {
			return;
		}

		$order_id = (int) $m[1];
		$order    = function_exists( 'wc_get_order' ) ? wc_get_order( $order_id ) : null;
		if ( ! $order ) {
			global $wp_query;
			if ( $wp_query ) {
				$wp_query->set_404();
			}
			status_header( 404 );
			nocache_headers();
			return;
		}

		$key             = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['key'] ) ) : '';
		$current_user_id = (int) get_current_user_id();
		$is_owner        = $current_user_id && (int) $order->get_customer_id() === $current_user_id;
		$has_valid_key   = $key && hash_equals( (string) $order->get_order_key(), $key );

		if ( ! $is_owner && ! $has_valid_key ) {
			wp_safe_redirect( wp_login_url( home_url( '/ordine/' . $order_id . '/' ) ) );
			exit;
		}

		// Context Woo per filtri/hook downstream.
		$GLOBALS['post'] = get_post( wc_get_page_id( 'myaccount' ) );
		if ( $GLOBALS['post'] ) {
			setup_postdata( $GLOBALS['post'] );
		}

		nocache_headers();
		status_header( 200 );

		get_header();
		echo '<main class="min-h-[60vh]">';

		if ( ! function_exists( 'pn_render_order_page' ) ) {
			require_once PN_THEME_DIR . '/inc/order/view.php';
		}
		$pn_is_thankyou = ! empty( $_GET['ty'] );
		$force_variant  = $pn_is_thankyou ? 'celebration' : 'auto';
		pn_render_order_page( $order, $force_variant, $pn_is_thankyou );

		echo '</main>';
		get_footer();
		exit;
	},
	1
);

add_action(
	'template_redirect',
	function () {
		$order_id = (int) get_query_var( 'pn_order_id' );
		if ( ! $order_id ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			nocache_headers();
			return;
		}

		$key             = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['key'] ) ) : '';
		$current_user_id = (int) get_current_user_id();
		$is_owner        = $current_user_id && (int) $order->get_customer_id() === $current_user_id;
		$has_valid_key   = $key && hash_equals( (string) $order->get_order_key(), $key );

		if ( ! $is_owner && ! $has_valid_key ) {
			$redirect = home_url( '/ordine/' . $order_id . '/' );
			wp_safe_redirect( wp_login_url( $redirect ) );
			exit;
		}

		// Imposta context Woo per i filtri/hook downstream.
		$GLOBALS['post'] = get_post( wc_get_page_id( 'myaccount' ) );
		setup_postdata( $GLOBALS['post'] );

		nocache_headers();
		status_header( 200 );

		get_header();

		echo '<main class="min-h-[60vh]">';

		if ( ! function_exists( 'pn_render_order_page' ) ) {
			require_once PN_THEME_DIR . '/inc/order/view.php';
		}

		// Forza celebration se viene da checkout (?ty=1) — set dal redirect post-pagamento.
		$pn_is_thankyou = ! empty( $_GET['ty'] );
		$force_variant  = $pn_is_thankyou ? 'celebration' : 'auto';

		pn_render_order_page( $order, $force_variant, $pn_is_thankyou );

		echo '</main>';

		get_footer();
		exit;
	}
);

/**
 * Filtra l'URL "view-order" e thank-you per puntare alla forma canonica.
 * Mantiene il fallback Woo nativo per retro-compatibilità.
 */
add_filter(
	'woocommerce_get_view_order_url',
	function ( $url, $order ) {
		if ( $order && is_a( $order, 'WC_Order' ) ) {
			// Includiamo `?key=` anche qui: stessa shareability del link thank-you,
			// così copiare l'URL dall'account funziona pure in finestra incognito.
			return add_query_arg(
				'key',
				$order->get_order_key(),
				home_url( '/ordine/' . $order->get_id() . '/' )
			);
		}
		return $url;
	},
	10,
	2
);

add_filter(
	'woocommerce_get_checkout_order_received_url',
	function ( $url, $order ) {
		if ( $order && is_a( $order, 'WC_Order' ) ) {
			return add_query_arg(
				array(
					'key' => $order->get_order_key(),
					'ty'  => 1,
				),
				home_url( '/ordine/' . $order->get_id() . '/' )
			);
		}
		return $url;
	},
	10,
	2
);
