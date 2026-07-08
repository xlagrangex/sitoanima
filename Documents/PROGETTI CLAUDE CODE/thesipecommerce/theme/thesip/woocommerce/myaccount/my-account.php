<?php
/**
 * My-Account wrapper.
 *
 * Layout standard: 260px sidebar + main (replica del Next).
 * Eccezione Shopify-style: su `view-order` la pagina è full-width senza sidebar
 * né titolo "Il mio account" — l'order page deve sentirsi standalone.
 *
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

// Distingue view-order (/account/ordini/{id}) da orders list (/account/ordini).
// Il theme override `inc/account/setup.php` mappa entrambi gli endpoint sullo slug `ordini`,
// quindi is_wc_endpoint_url('view-order') torna true anche sulla lista. Filtriamo per ID > 0.
$pn_is_view_order = is_wc_endpoint_url( 'view-order' ) && (int) get_query_var( 'view-order' ) > 0;

do_action( 'woocommerce_before_account_navigation' );

if ( $pn_is_view_order ) :
	?>
	<?php do_action( 'woocommerce_account_content' ); ?>
<?php else : ?>
	<div class="container mx-auto max-w-7xl px-4 py-8 md:py-12">
		<h1 class="mb-6 text-3xl font-bold uppercase md:mb-8 md:text-4xl"><?php esc_html_e( 'Il mio account', 'pharmanow' ); ?></h1>

		<div class="grid gap-6 md:grid-cols-[260px_1fr] md:gap-8">
			<?php wc_get_template( 'myaccount/navigation.php' ); ?>

			<div class="woocommerce-MyAccount-content min-w-0">
				<?php do_action( 'woocommerce_account_content' ); ?>
			</div>
		</div>
	</div>
<?php endif; ?>
