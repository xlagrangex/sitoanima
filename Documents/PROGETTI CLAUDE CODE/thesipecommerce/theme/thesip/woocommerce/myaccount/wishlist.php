<?php
/**
 * Wishlist account page. Replica `account/wishlist/page.tsx` + `WishlistGrid.tsx` del Next.
 *
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

$pn_ids      = pn_wishlist_get_ids( get_current_user_id() );
$pn_products = array();
foreach ( $pn_ids as $pid ) {
	$p = wc_get_product( $pid );
	if ( $p && $p->is_visible() ) {
		$pn_products[] = $p;
	}
}
?>
<div class="space-y-6" x-data x-init="window.dispatchEvent(new CustomEvent('pn-wishlist-sync'))">
	<div class="flex flex-wrap items-end justify-between gap-2">
		<div>
			<h2 class="text-2xl font-bold"><?php esc_html_e( 'Wishlist', 'pharmanow' ); ?></h2>
			<p class="mt-1 text-sm text-muted-foreground">
				<?php
				$pn_count = count( $pn_products );
				/* translators: %d: count */
				echo esc_html( $pn_count > 0 ? sprintf( _n( '%d prodotto salvato', '%d prodotti salvati', $pn_count, 'pharmanow' ), $pn_count ) : __( 'I prodotti che hai salvato', 'pharmanow' ) );
				?>
			</p>
		</div>
	</div>

	<?php if ( empty( $pn_products ) ) : ?>
		<div class="flex flex-col items-center justify-center gap-3 rounded-xl border border-dashed bg-card py-16 text-center">
			<span class="text-muted-foreground/40"><?php pn_icon( 'heart', array( 'class' => 'h-9 w-9' ) ); ?></span>
			<p class="text-sm text-muted-foreground"><?php esc_html_e( 'La tua wishlist è vuota', 'pharmanow' ); ?></p>
			<a href="<?php echo esc_url( home_url( '/catalogo/' ) ); ?>" class="inline-flex h-10 items-center gap-2 rounded-md bg-pharma-teal px-5 text-sm font-semibold text-white hover:bg-pharma-teal-dark">
				<?php esc_html_e( 'Esplora il catalogo', 'pharmanow' ); ?>
			</a>
		</div>
	<?php else : ?>
		<div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4">
			<?php
			foreach ( $pn_products as $pn_p ) {
				get_template_part( 'template-parts/shop/product-card', null, array( 'product' => $pn_p ) );
			}
			?>
		</div>
	<?php endif; ?>
</div>
