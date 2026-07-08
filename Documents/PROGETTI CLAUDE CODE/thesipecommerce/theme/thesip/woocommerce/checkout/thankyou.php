<?php
/**
 * Thank-you (override Woo) — Shopify-style: stessa pagina di account view-order,
 * cambia solo l'hero (variant celebration). Single source of truth in
 * template-parts/order/body.php tramite pn_render_order_page().
 *
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

if ( ! $order || ! is_a( $order, 'WC_Order' ) ) :
	?>
	<section class="max-w-3xl mx-auto py-16 px-4 text-center">
		<h1 class="text-2xl font-semibold text-gray-900"><?php esc_html_e( 'Grazie', 'pharmanow' ); ?></h1>
		<p class="mt-3 text-gray-600">
			<?php esc_html_e( "Il tuo ordine è stato ricevuto. Se non vedi i dettagli, controlla l'email di conferma.", 'pharmanow' ); ?>
		</p>
	</section>
	<?php
	return;
endif;

pn_render_order_page( $order, 'celebration', true );
