<?php
/**
 * Mini Cart drawer wrapper — renderizzato a fine body via wp_footer.
 * Il contenuto interno (lista, totals, free-shipping bar) è in
 * woocommerce/cart/mini-cart.php e viene aggiornato live da wc-cart-fragments.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
	return;
}
?>

<div
	x-data
	x-cloak
	x-show="$store.pnCart.isOpen"
	@keydown.escape.window="$store.pnCart.close()"
	class="pn-drawer-root"
	style="position:fixed; inset:0; z-index:100;"
>
	<!-- Backdrop -->
	<div
		x-show="$store.pnCart.isOpen"
		x-transition.opacity
		class="pn-drawer-backdrop"
		style="position:absolute; inset:0; background:rgba(0,0,0,0.4);"
		@click="$store.pnCart.close()"
	></div>

	<!-- Drawer -->
	<aside
		x-show="$store.pnCart.isOpen"
		x-transition:enter="transition transform ease-out duration-300"
		x-transition:enter-start="translate-x-full"
		x-transition:enter-end="translate-x-0"
		x-transition:leave="transition transform ease-in duration-200"
		x-transition:leave-start="translate-x-0"
		x-transition:leave-end="translate-x-full"
		class="pn-drawer"
		role="dialog"
		aria-modal="true"
		aria-label="<?php esc_attr_e( 'Mini carrello', 'pharmanow' ); ?>"
	>
		<!-- Header -->
		<header class="pn-drawer__head">
			<h2 class="pn-drawer__title">
				<?php pn_icon( 'shopping-bag', array( 'class' => 'pn-drawer__title-icon' ) ); ?>
				<span><?php esc_html_e( 'Carrello', 'pharmanow' ); ?></span>
				<span class="pn-drawer__count" x-show="$store.pnCart.count > 0">
					(<span x-text="$store.pnCart.count + ' ' + ($store.pnCart.count === 1 ? 'articolo' : 'articoli')"><?php
						$pn_count_init = (int) WC()->cart->get_cart_contents_count();
						echo esc_html( $pn_count_init . ' ' . ( 1 === $pn_count_init ? 'articolo' : 'articoli' ) );
					?></span>)
				</span>
			</h2>
			<button
				type="button"
				class="pn-drawer__close"
				@click="$store.pnCart.close()"
				aria-label="<?php esc_attr_e( 'Chiudi', 'pharmanow' ); ?>"
			>
				<?php pn_icon( 'x', array( 'class' => '' ) ); ?>
			</button>
		</header>

		<!-- Body wrapper persistente (NON sostituito da fragments) -->
		<div class="pn-drawer__body">
			<!-- Inner replaced by wc-cart-fragments at every cart mutation -->
			<div class="widget_shopping_cart_content">
				<?php woocommerce_mini_cart(); ?>
			</div>
		</div>
	</aside>
</div>
