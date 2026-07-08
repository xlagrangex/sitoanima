<?php
/**
 * Empty cart state.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="pn-cart-empty max-w-2xl mx-auto px-4 py-16 text-center">

	<?php do_action( 'woocommerce_cart_is_empty' ); ?>

	<div class="mx-auto h-20 w-20 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-5">
		<?php pn_icon( 'shopping-bag', array( 'class' => 'h-10 w-10' ) ); ?>
	</div>

	<h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-gray-900 mb-2">
		<?php esc_html_e( 'Il tuo carrello è vuoto', 'pharmanow' ); ?>
	</h1>
	<p class="text-base text-gray-600 mb-8">
		<?php esc_html_e( 'Esplora il catalogo e trova i prodotti che cerchi.', 'pharmanow' ); ?>
	</p>

	<?php if ( wc_get_page_id( 'shop' ) > 0 ) : ?>
		<a href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>" class="pn-btn-gradient inline-flex">
			<?php echo esc_html( apply_filters( 'woocommerce_return_to_shop_text', __( 'Esplora il catalogo', 'pharmanow' ) ) ); ?>
		</a>
	<?php endif; ?>

</div>
