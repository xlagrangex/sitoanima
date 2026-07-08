<?php
/**
 * Account: rinomina endpoint My-Account in italiano + redirect /login + /registrati
 * + /password-dimenticata come pagine WP standalone.
 *
 * URL finali:
 *   /login                    → pagina WP "login" (template-parts/account/login)
 *   /registrati               → pagina WP "registrati"
 *   /password-dimenticata     → pagina WP "password-dimenticata"
 *   /account                  → My-Account (slug pagina WC = "account")
 *   /account/profilo          → endpoint edit-account
 *   /account/ordini           → endpoint orders
 *   /account/ordini/{id}      → endpoint view-order (stesso slug)
 *   /account/indirizzi        → endpoint edit-address
 *
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Override slug endpoint My-Account.
 */
add_filter(
	'woocommerce_get_query_vars',
	function ( $vars ) {
		$vars['edit-account']    = 'profilo';
		$vars['orders']          = 'ordini';
		$vars['view-order']      = 'ordini';
		$vars['edit-address']    = 'indirizzi';
		$vars['payment-methods'] = 'metodi-pagamento';
		$vars['lost-password']   = 'reset-password';
		$vars['customer-logout'] = 'logout';
		return $vars;
	}
);

/**
 * Override label tab sidebar My-Account (anche se renderizziamo custom,
 * resta valido per i breadcrumb e i link nativi).
 */
add_filter(
	'woocommerce_account_menu_items',
	function ( $items ) {
		$items = array(
			'dashboard'       => __( 'Panoramica', 'pharmanow' ),
			'orders'          => __( 'Ordini', 'pharmanow' ),
			'edit-address'    => __( 'Indirizzi', 'pharmanow' ),
			'edit-account'    => __( 'Profilo', 'pharmanow' ),
			'customer-logout' => __( 'Esci', 'pharmanow' ),
		);
		return $items;
	}
);

/**
 * Sync option WC `woocommerce_myaccount_*_endpoint` con i nuovi slug
 * (necessario perché alcune funzioni interne leggono direttamente dalla option,
 * non dal filtro `woocommerce_get_query_vars`).
 */
add_action(
	'init',
	function () {
		$pn_endpoints = array(
			'orders'          => 'ordini',
			'view-order'      => 'ordini',
			'edit-account'    => 'profilo',
			'edit-address'    => 'indirizzi',
			'lost-password'   => 'reset-password',
			'customer-logout' => 'logout',
			'payment-methods' => 'metodi-pagamento',
		);
		$pn_changed = false;
		foreach ( $pn_endpoints as $key => $slug ) {
			$opt = 'woocommerce_myaccount_' . $key . '_endpoint';
			if ( get_option( $opt ) !== $slug ) {
				update_option( $opt, $slug );
				$pn_changed = true;
			}
		}
		if ( $pn_changed && get_option( 'pn_endpoints_synced' ) !== '1' ) {
			update_option( 'pn_endpoints_synced', '1' );
			flush_rewrite_rules( false );
		}
	},
	20
);

/**
 * Auto-create pagine standalone al setup (idempotente).
 */
add_action(
	'after_switch_theme',
	function () {
		$pages = array(
			'login'                 => array( 'title' => __( 'Accedi', 'pharmanow' ), 'template' => 'page-login.php' ),
			'registrati'            => array( 'title' => __( 'Crea un account', 'pharmanow' ), 'template' => 'page-registrati.php' ),
			'password-dimenticata'  => array( 'title' => __( "Recupera l'accesso", 'pharmanow' ), 'template' => 'page-password-dimenticata.php' ),
		);
		foreach ( $pages as $slug => $info ) {
			if ( null === get_page_by_path( $slug ) ) {
				wp_insert_post(
					array(
						'post_type'      => 'page',
						'post_title'     => $info['title'],
						'post_name'      => $slug,
						'post_status'    => 'publish',
						'post_content'   => '',
						'page_template'  => $info['template'],
						'comment_status' => 'closed',
					)
				);
			}
		}
		flush_rewrite_rules();
	}
);

/**
 * Redirect intelligente:
 *  - utente loggato che visita /login, /registrati, /password-dimenticata → /account
 *  - utente non loggato che visita /account/* → /login?redirect=...
 */
add_action(
	'template_redirect',
	function () {
		$slug    = is_page() ? get_post_field( 'post_name', get_queried_object_id() ) : '';
		$auth_pages = array( 'login', 'registrati', 'password-dimenticata' );

		if ( is_user_logged_in() && in_array( $slug, $auth_pages, true ) ) {
			$redirect = isset( $_GET['redirect'] ) ? wp_validate_redirect( wp_unslash( $_GET['redirect'] ), wc_get_page_permalink( 'myaccount' ) ) : wc_get_page_permalink( 'myaccount' );
			wp_safe_redirect( $redirect );
			exit;
		}

		if ( ! is_user_logged_in() && function_exists( 'is_account_page' ) && is_account_page() ) {
			$redirect = home_url( '/login/' );
			$current  = ( isset( $_SERVER['REQUEST_URI'] ) ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			if ( $current ) {
				$redirect = add_query_arg( 'redirect', rawurlencode( $current ), $redirect );
			}
			wp_safe_redirect( $redirect );
			exit;
		}
	}
);

/**
 * Forza l'URL "lost password" verso la nostra pagina dedicata.
 */
add_filter(
	'lostpassword_url',
	function ( $url ) {
		return home_url( '/password-dimenticata/' );
	},
	10,
	2
);

/**
 * Forza l'URL di login (es. checkout) verso la nostra pagina.
 */
add_filter(
	'login_url',
	function ( $url, $redirect, $force_reauth ) {
		$out = home_url( '/login/' );
		if ( $redirect ) {
			$out = add_query_arg( 'redirect', rawurlencode( $redirect ), $out );
		}
		return $out;
	},
	10,
	3
);

/**
 * Mappa status WC ordine → tone+label per OrderStatusBadge.
 */
function pn_order_status_tone( string $status ): array {
	$map = array(
		'pending'    => array( 'tone' => 'amber',   'label' => __( 'In attesa', 'pharmanow' ) ),
		'on-hold'    => array( 'tone' => 'amber',   'label' => __( 'In attesa', 'pharmanow' ) ),
		'processing' => array( 'tone' => 'blue',    'label' => __( 'In elaborazione', 'pharmanow' ) ),
		'completed'  => array( 'tone' => 'emerald', 'label' => __( 'Completato', 'pharmanow' ) ),
		'cancelled'  => array( 'tone' => 'rose',    'label' => __( 'Annullato', 'pharmanow' ) ),
		'refunded'   => array( 'tone' => 'gray',    'label' => __( 'Rimborsato', 'pharmanow' ) ),
		'failed'     => array( 'tone' => 'rose',    'label' => __( 'Fallito', 'pharmanow' ) ),
	);
	return $map[ $status ] ?? array( 'tone' => 'gray', 'label' => ucfirst( $status ) );
}

function pn_order_status_classes( string $tone ): string {
	$tones = array(
		'amber'   => 'bg-amber-100 text-amber-800',
		'emerald' => 'bg-emerald-100 text-emerald-800',
		'blue'    => 'bg-blue-100 text-blue-800',
		'rose'    => 'bg-rose-100 text-rose-800',
		'gray'    => 'bg-gray-100 text-gray-800',
	);
	return $tones[ $tone ] ?? $tones['gray'];
}
