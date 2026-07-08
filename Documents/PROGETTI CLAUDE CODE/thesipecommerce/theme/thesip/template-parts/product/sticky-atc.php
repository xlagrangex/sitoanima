<?php
/**
 * Sticky bar fixed bottom con ATC, appare quando il bottone principale esce dal viewport.
 * Replica `StickyAddToCart.tsx`.
 *
 * @var array $args { product: WC_Product }
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

$pn_p = $args['product'] ?? null;
if ( ! $pn_p instanceof WC_Product ) {
	return;
}
$pn_id       = $pn_p->get_id();
$pn_title    = $pn_p->get_name();
$pn_in_stock = $pn_p->is_in_stock();
$pn_thumb    = get_the_post_thumbnail_url( $pn_id, 'pn-product-thumb' ) ?: wc_placeholder_img_src( 'pn-product-thumb' );
$pn_btn      = $pn_in_stock ? __( 'Aggiungi al carrello', 'pharmanow' ) : __( 'Esaurito', 'pharmanow' );
?>
<div
	x-data="pnStickyAtc()"
	x-init="init('[data-pdp-cta]')"
	x-show="visible"
	x-cloak
	x-transition.opacity
	class="fixed inset-x-0 bottom-0 z-40 border-t bg-background/95 shadow-[0_-4px_20px_-8px_rgba(0,0,0,0.15)] backdrop-blur"
	style="transition: transform .2s ease, opacity .2s ease;"
	x-bind:style="visible ? 'transform: translateY(0)' : 'transform: translateY(80px)'"
>
	<div class="container mx-auto flex max-w-7xl items-center gap-3 px-4 py-3 md:gap-4">
		<?php if ( $pn_thumb ) : ?>
			<div class="relative hidden h-12 w-12 shrink-0 overflow-hidden rounded-md bg-muted md:block">
				<img src="<?php echo esc_url( $pn_thumb ); ?>" alt="<?php echo esc_attr( $pn_title ); ?>" class="absolute inset-0 h-full w-full object-contain p-1">
			</div>
		<?php endif; ?>
		<div class="hidden min-w-0 flex-1 md:block">
			<p class="truncate text-sm font-semibold"><?php echo esc_html( $pn_title ); ?></p>
		</div>
		<div class="hidden md:block text-base font-bold text-pharma-teal tabular-nums"><?php echo wp_kses_post( $pn_p->get_price_html() ); ?></div>
		<form method="post" class="cart ml-auto flex-1 md:flex-none">
			<input type="hidden" name="quantity" value="1">
			<button
				type="submit"
				name="add-to-cart"
				value="<?php echo (int) $pn_id; ?>"
				<?php disabled( ! $pn_in_stock, true ); ?>
				class="pn-atc-btn w-full md:w-auto h-11 px-5 rounded-md bg-pharma-teal text-white text-sm font-semibold hover:bg-pharma-teal-dark disabled:opacity-60"
			>
				<span class="pn-atc-default">
					<?php pn_icon( 'shopping-cart', array( 'class' => 'h-4 w-4' ) ); ?>
					<?php echo esc_html( $pn_btn ); ?>
				</span>
				<span class="pn-atc-success">
					<?php pn_icon( 'check-circle-2', array( 'class' => 'h-4 w-4' ) ); ?>
					<?php esc_html_e( 'Aggiunto!', 'pharmanow' ); ?>
				</span>
			</button>
		</form>
	</div>
</div>
