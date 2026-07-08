<?php
/**
 * Wishlist: DB table + REST endpoints + endpoint MyAccount.
 *
 * Tabella: wp_pharmanow_wishlist (id, user_id, product_id, created_at, UNIQUE(user_id,product_id))
 * REST:
 *   GET  /wp-json/pharmanow/v1/wishlist           → array IDs (loggato) o cookie (guest)
 *   POST /wp-json/pharmanow/v1/wishlist/toggle    → { product_id } toggle add/remove
 * Endpoint MyAccount: /account/wishlist/
 *
 * Strategia guest: cookie pn_wishlist (JSON IDs, 1 anno). Merge alla login.
 *
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

const PN_WISHLIST_TABLE  = 'pharmanow_wishlist';
const PN_WISHLIST_COOKIE = 'pn_wishlist';

/* ============================================================
   DB SCHEMA
   ============================================================ */
function pn_wishlist_install(): void {
	global $wpdb;
	$table   = $wpdb->prefix . PN_WISHLIST_TABLE;
	$charset = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE {$table} (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		user_id BIGINT UNSIGNED NOT NULL,
		product_id BIGINT UNSIGNED NOT NULL,
		created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		UNIQUE KEY user_product (user_id, product_id),
		KEY user_idx (user_id),
		KEY product_idx (product_id)
	) {$charset};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}
add_action(
	'after_switch_theme',
	function () {
		pn_wishlist_install();
		update_option( 'pn_wishlist_installed', '1' );
	}
);
// Lazy-install: se la tabella non esiste, la crea al boot.
add_action(
	'init',
	function () {
		if ( '1' === get_option( 'pn_wishlist_installed' ) ) {
			return;
		}
		pn_wishlist_install();
		update_option( 'pn_wishlist_installed', '1' );
	},
	5
);

/* ============================================================
   DB ACCESSORS
   ============================================================ */
function pn_wishlist_get_ids( int $user_id ): array {
	if ( ! $user_id ) {
		return pn_wishlist_get_cookie_ids();
	}
	global $wpdb;
	$table = $wpdb->prefix . PN_WISHLIST_TABLE;
	$rows  = $wpdb->get_col( $wpdb->prepare( "SELECT product_id FROM {$table} WHERE user_id = %d ORDER BY created_at DESC", $user_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	return array_map( 'intval', $rows ?: array() );
}

function pn_wishlist_has( int $user_id, int $product_id ): bool {
	if ( ! $user_id ) {
		return in_array( $product_id, pn_wishlist_get_cookie_ids(), true );
	}
	global $wpdb;
	$table = $wpdb->prefix . PN_WISHLIST_TABLE;
	return (bool) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE user_id = %d AND product_id = %d LIMIT 1", $user_id, $product_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
}

function pn_wishlist_add( int $user_id, int $product_id ): bool {
	if ( ! $user_id ) {
		return pn_wishlist_cookie_add( $product_id );
	}
	global $wpdb;
	$table = $wpdb->prefix . PN_WISHLIST_TABLE;
	// INSERT IGNORE su UNIQUE.
	$wpdb->query( $wpdb->prepare( "INSERT IGNORE INTO {$table} (user_id, product_id) VALUES (%d, %d)", $user_id, $product_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	return true;
}

function pn_wishlist_remove( int $user_id, int $product_id ): bool {
	if ( ! $user_id ) {
		return pn_wishlist_cookie_remove( $product_id );
	}
	global $wpdb;
	$table = $wpdb->prefix . PN_WISHLIST_TABLE;
	$wpdb->delete( $table, array( 'user_id' => $user_id, 'product_id' => $product_id ), array( '%d', '%d' ) );
	return true;
}

/* ============================================================
   COOKIE FALLBACK (guest)
   ============================================================ */
function pn_wishlist_get_cookie_ids(): array {
	$raw = isset( $_COOKIE[ PN_WISHLIST_COOKIE ] ) ? wp_unslash( $_COOKIE[ PN_WISHLIST_COOKIE ] ) : '';
	if ( '' === $raw ) {
		return array();
	}
	$decoded = json_decode( $raw, true );
	if ( ! is_array( $decoded ) ) {
		return array();
	}
	return array_values( array_unique( array_map( 'intval', $decoded ) ) );
}

function pn_wishlist_set_cookie_ids( array $ids ): void {
	$ids   = array_values( array_unique( array_map( 'intval', array_filter( $ids ) ) ) );
	$value = wp_json_encode( $ids );
	setcookie( PN_WISHLIST_COOKIE, $value, time() + YEAR_IN_SECONDS, COOKIEPATH ?: '/', COOKIE_DOMAIN ?: '', is_ssl(), false );
	$_COOKIE[ PN_WISHLIST_COOKIE ] = $value;
}

function pn_wishlist_cookie_add( int $product_id ): bool {
	$ids = pn_wishlist_get_cookie_ids();
	if ( ! in_array( $product_id, $ids, true ) ) {
		$ids[] = $product_id;
		pn_wishlist_set_cookie_ids( $ids );
	}
	return true;
}

function pn_wishlist_cookie_remove( int $product_id ): bool {
	$ids = array_values( array_filter( pn_wishlist_get_cookie_ids(), fn( $i ) => $i !== $product_id ) );
	pn_wishlist_set_cookie_ids( $ids );
	return true;
}

/**
 * Merge cookie wishlist al login (one-shot).
 */
add_action(
	'wp_login',
	function ( $user_login, $user ) {
		$cookie_ids = pn_wishlist_get_cookie_ids();
		if ( empty( $cookie_ids ) ) {
			return;
		}
		foreach ( $cookie_ids as $pid ) {
			if ( $pid > 0 ) {
				pn_wishlist_add( (int) $user->ID, (int) $pid );
			}
		}
		// Svuota il cookie.
		setcookie( PN_WISHLIST_COOKIE, '', time() - 3600, COOKIEPATH ?: '/', COOKIE_DOMAIN ?: '', is_ssl(), false );
	},
	10,
	2
);

/* ============================================================
   REST ENDPOINTS
   ============================================================ */
add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'pharmanow/v1',
			'/wishlist',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => 'pn_rest_wishlist_list',
			)
		);
		register_rest_route(
			'pharmanow/v1',
			'/wishlist/toggle',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => 'pn_rest_wishlist_toggle',
				'args'                => array(
					'product_id' => array( 'required' => true, 'sanitize_callback' => 'absint' ),
				),
			)
		);
	}
);

function pn_rest_wishlist_list( WP_REST_Request $req ): WP_REST_Response {
	$ids = pn_wishlist_get_ids( get_current_user_id() );
	return new WP_REST_Response( array( 'ids' => array_map( 'intval', $ids ) ) );
}

function pn_rest_wishlist_toggle( WP_REST_Request $req ): WP_REST_Response {
	$product_id = absint( $req->get_param( 'product_id' ) );
	if ( ! $product_id || ! ( wc_get_product( $product_id ) ) ) {
		return new WP_REST_Response( array( 'error' => 'invalid_product' ), 400 );
	}
	$user_id = get_current_user_id();
	$has     = pn_wishlist_has( $user_id, $product_id );
	if ( $has ) {
		pn_wishlist_remove( $user_id, $product_id );
		$state = 'removed';
	} else {
		pn_wishlist_add( $user_id, $product_id );
		$state = 'added';
	}
	return new WP_REST_Response(
		array(
			'state'      => $state,
			'in_wishlist' => ! $has,
			'count'      => count( pn_wishlist_get_ids( $user_id ) ),
		)
	);
}

/* ============================================================
   ENDPOINT MyAccount /account/wishlist/
   ============================================================ */
add_action(
	'init',
	function () {
		add_rewrite_endpoint( 'wishlist', EP_PAGES );
	}
);
add_filter(
	'woocommerce_get_query_vars',
	function ( $vars ) {
		$vars['wishlist'] = 'wishlist';
		return $vars;
	}
);
add_filter(
	'woocommerce_account_menu_items',
	function ( $items ) {
		// Inserisci "Wishlist" prima di "Profilo".
		$new = array();
		foreach ( $items as $key => $label ) {
			if ( 'edit-account' === $key ) {
				$new['wishlist'] = __( 'Wishlist', 'pharmanow' );
			}
			$new[ $key ] = $label;
		}
		return $new;
	}
);
add_action(
	'woocommerce_account_wishlist_endpoint',
	function () {
		wc_get_template( 'myaccount/wishlist.php' );
	}
);
add_filter(
	'the_title',
	function ( $title, $post_id = 0 ) {
		if ( ! is_admin() && function_exists( 'is_account_page' ) && is_account_page() && in_the_loop() && function_exists( 'WC' ) && 'wishlist' === WC()->query->get_current_endpoint() ) {
			return __( 'Wishlist', 'pharmanow' );
		}
		return $title;
	},
	10,
	2
);
