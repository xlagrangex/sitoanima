<?php
/**
 * AJAX endpoints custom per il mini-cart drawer.
 *
 * - pn_update_qty: setta la quantità di un cart_item_key, ritorna i fragments
 *   aggiornati così wc-cart-fragments può sostituire il drawer markup.
 *
 * Usage lato client:
 *   POST a /?wc-ajax=pn_update_qty
 *   body: key=<cart_item_key>&qty=<int>
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'wc_ajax_pn_update_qty',
	function () {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			wp_send_json_error( array( 'message' => 'WC not available' ), 500 );
		}

		$key = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
		$qty = isset( $_POST['qty'] ) ? max( 0, (int) $_POST['qty'] ) : 0;

		if ( empty( $key ) ) {
			wp_send_json_error( array( 'message' => 'Missing key' ), 400 );
		}

		$cart = WC()->cart->get_cart();
		if ( ! isset( $cart[ $key ] ) ) {
			wp_send_json_error( array( 'message' => 'Item not in cart' ), 404 );
		}

		// set_quantity con true → ricalcola totali immediatamente.
		WC()->cart->set_quantity( $key, $qty, true );

		// Ritorna fragments aggiornati (mini-cart, contents-count, ecc.).
		WC_AJAX::get_refreshed_fragments();
	}
);

// pn_get_refreshed_fragments — wrapper che il client può chiamare per forzare un sync
// (es. dopo un add-to-cart standard via querystring senza redirect).
add_action(
	'wc_ajax_pn_get_refreshed_fragments',
	function () {
		WC_AJAX::get_refreshed_fragments();
	}
);
