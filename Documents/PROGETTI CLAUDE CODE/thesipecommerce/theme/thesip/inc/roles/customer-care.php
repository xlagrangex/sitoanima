<?php
/**
 * Ruolo "Customer Care": clone 1:1 delle capability di Administrator.
 *
 * Il ruolo viene creato/risincronizzato quando:
 *   - non esiste ancora;
 *   - cambia PN_THEME_VERSION (es. dopo update del tema);
 *   - cambiano le capability dell'administrator (es. dopo update di WooCommerce
 *     che aggiunge manage_woocommerce, ecc.).
 *
 * Lo slug interno è `customer_care`. Label visibile in admin: "Customer Care".
 */

defined( 'ABSPATH' ) || exit;

const PN_CUSTOMER_CARE_ROLE        = 'customer_care';
const PN_CUSTOMER_CARE_ROLE_OPTION = 'pn_customer_care_role_sync';

/**
 * Sincronizza il ruolo customer_care con le capability correnti dell'administrator.
 */
function pn_sync_customer_care_role(): void {
	$admin = get_role( 'administrator' );
	if ( ! $admin ) {
		return;
	}

	$admin_caps = $admin->capabilities;
	$signature  = PN_THEME_VERSION . '|' . md5( wp_json_encode( $admin_caps ) );

	if ( get_option( PN_CUSTOMER_CARE_ROLE_OPTION ) === $signature && get_role( PN_CUSTOMER_CARE_ROLE ) ) {
		return;
	}

	remove_role( PN_CUSTOMER_CARE_ROLE );
	add_role( PN_CUSTOMER_CARE_ROLE, __( 'Customer Care', 'pharmanow' ), $admin_caps );

	update_option( PN_CUSTOMER_CARE_ROLE_OPTION, $signature, false );
}
add_action( 'init', 'pn_sync_customer_care_role', 20 );

/**
 * Whitelist degli slug top-level del menu admin visibili al Customer Care.
 * Le capability restano quelle di admin: l'utente può comunque raggiungere
 * via URL diretto qualsiasi pagina. Qui filtriamo solo la SIDEBAR.
 */
const PN_CUSTOMER_CARE_MENU_WHITELIST = array(
	'pharmanow-email-monitor',
	'edit.php?post_type=pn_banner',
	'pn-promo-bar',
	'edit.php?post_type=pn_popup',
	'pn-coupon-manager',
	'pn-farmadati-import',
	'woocommerce',
	'edit.php?post_type=product',
	'users.php',
);

/**
 * Rimuove dalla sidebar admin tutte le voci non in whitelist quando l'utente
 * ha il ruolo customer_care e NON è anche administrator (così un admin con
 * dual-role mantiene il menu completo per debug).
 */
function pn_customer_care_filter_admin_menu(): void {
	$user = wp_get_current_user();
	if ( ! $user || ! $user->ID ) {
		return;
	}
	$roles = (array) $user->roles;
	if ( ! in_array( PN_CUSTOMER_CARE_ROLE, $roles, true ) || in_array( 'administrator', $roles, true ) ) {
		return;
	}

	global $menu;
	foreach ( (array) $menu as $idx => $item ) {
		$slug = $item[2] ?? '';
		if ( ! in_array( $slug, PN_CUSTOMER_CARE_MENU_WHITELIST, true ) ) {
			unset( $menu[ $idx ] );
		}
	}
}
add_action( 'admin_menu', 'pn_customer_care_filter_admin_menu', 9999 );
