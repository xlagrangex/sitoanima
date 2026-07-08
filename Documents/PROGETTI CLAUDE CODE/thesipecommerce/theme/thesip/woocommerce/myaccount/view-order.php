<?php
/**
 * View Order (account) — Shopify-style: stessa pagina di thank-you, hero "info".
 *
 * Variabile Woo disponibile in scope: $order_id (int).
 *
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

$pn_order = wc_get_order( $order_id );
if ( ! $pn_order || (int) $pn_order->get_user_id() !== (int) get_current_user_id() ) {
	?>
	<section class="max-w-3xl mx-auto py-16 px-4 text-center">
		<h1 class="text-2xl font-semibold text-gray-900"><?php esc_html_e( 'Ordine non disponibile', 'pharmanow' ); ?></h1>
		<p class="mt-3 text-gray-600">
			<?php esc_html_e( "Non è stato possibile trovare l'ordine richiesto, oppure non hai i permessi per visualizzarlo.", 'pharmanow' ); ?>
		</p>
		<a href="<?php echo esc_url( wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) ) ); ?>" class="inline-flex items-center gap-2 mt-6 bg-[#0B8894] text-white font-semibold py-2.5 px-5 rounded-xl hover:bg-[#0B8894]/90 transition-colors">
			<?php esc_html_e( 'Torna ai miei ordini', 'pharmanow' ); ?>
		</a>
	</section>
	<?php
	return;
}

// Variant 'auto' → celebration se ordine appena creato (< 10 min), altrimenti info.
pn_render_order_page( $pn_order, 'auto' );
