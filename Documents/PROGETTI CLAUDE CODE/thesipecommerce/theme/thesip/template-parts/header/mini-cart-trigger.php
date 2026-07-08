<?php
/**
 * Mini Cart trigger (icona carrello + badge count) + drawer wrapper.
 * Drawer è renderizzato a fine body via wp_footer (vedi mini-cart-sidebar.php).
 *
 * Count badge viene aggiornato live via wc-cart-fragments (Woo built-in).
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn_count = function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
?>

<button
	type="button"
	class="relative inline-flex items-center justify-center h-9 w-9 rounded-md text-gray-600 hover:bg-gray-100 hover:text-pharma-teal transition-colors"
	x-on:click.stop="$store.pnCart.open()"
	aria-label="<?php esc_attr_e( 'Apri carrello', 'pharmanow' ); ?>"
>
	<?php pn_icon( 'shopping-cart', array( 'class' => 'h-5 w-5' ) ); ?>

	<span
		class="pn-cart-badge"
		x-bind:class="$store.pnCart.count > 0 ? '' : 'opacity-0'"
		x-text="$store.pnCart.count"
	><?php echo esc_html( (string) $pn_count ); ?></span>
</button>
