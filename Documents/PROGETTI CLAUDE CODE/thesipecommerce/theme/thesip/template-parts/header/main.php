<?php
/**
 * Header principale — porting fedele da components/shop/layout/header.tsx (Next).
 *
 * Struttura:
 *   1. Top trust bar (sm+, gradient accent, animate height on scroll)
 *   2. Main row h-16 (logo + search + account + wishlist + cart trigger)
 *   3. Nav row desktop (lg+, mega menu)
 *   4. Mobile drawer (lg-, x-show)
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn_threshold = (float) get_theme_mod( 'pn_free_shipping_threshold', 29.90 );
?>

<header
	x-data="header()"
	x-bind:class="scrolled ? 'shadow-md' : 'shadow-sm'"
	class="sticky top-0 z-50 bg-white border-b transition-shadow"
>
	<!-- ── Top trust bar (animate height to 0 on scroll) ── -->
	<div
		class="hidden sm:block overflow-hidden transition-[height,opacity] duration-300 ease-out"
		x-bind:style="scrolled ? 'height:0; opacity:0' : 'height:36px; opacity:1'"
	>
		<div class="pharma-accent-bg text-white h-[36px]">
			<div class="container max-w-7xl mx-auto px-4 h-full flex items-center justify-between text-xs">
				<div class="flex items-center gap-4">
					<span class="flex items-center gap-1.5">
						<?php pn_icon( 'map-pin', array( 'class' => 'h-3.5 w-3.5' ) ); ?>
						<?php esc_html_e( 'Progetto nato a Napoli', 'pharmanow' ); ?>
					</span>
					<span class="flex items-center gap-1.5">
						<?php pn_icon( 'zap', array( 'class' => 'h-3.5 w-3.5' ) ); ?>
						<?php esc_html_e( 'Spedizione 24/48h', 'pharmanow' ); ?>
					</span>
				</div>
				<div class="flex items-center gap-4">
					<span class="flex items-center gap-1.5">
						<?php pn_icon( 'truck', array( 'class' => 'h-3.5 w-3.5' ) ); ?>
						<?php
						printf(
							/* translators: %s = importo soglia spedizione gratuita */
							esc_html__( 'Gratis sopra €%s', 'pharmanow' ),
							esc_html( number_format( $pn_threshold, 2, ',', '.' ) )
						);
						?>
					</span>
					<span class="flex items-center gap-1.5">
						<?php pn_icon( 'credit-card', array( 'class' => 'h-3.5 w-3.5' ) ); ?>
						<?php esc_html_e( 'Pagamenti Sicuri', 'pharmanow' ); ?>
					</span>
				</div>
			</div>
		</div>
	</div>

	<!-- ── Main row — sempre h-16, mai cambia ── -->
	<div class="container max-w-7xl mx-auto px-4">
		<div class="flex items-center gap-4 h-16">
			<!-- Hamburger mobile -->
			<button
				type="button"
				class="lg:hidden shrink-0 inline-flex items-center justify-center h-9 w-9 rounded-md text-gray-600 hover:bg-gray-100"
				x-on:click="mobileOpen = !mobileOpen"
				x-bind:aria-expanded="mobileOpen"
				aria-label="<?php esc_attr_e( 'Apri/chiudi menu', 'pharmanow' ); ?>"
			>
				<span x-show="!mobileOpen" x-cloak><?php pn_icon( 'menu', array( 'class' => 'h-5 w-5' ) ); ?></span>
				<span x-show="mobileOpen" x-cloak><?php pn_icon( 'x', array( 'class' => 'h-5 w-5' ) ); ?></span>
			</button>

			<!-- Logo -->
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="shrink-0" aria-label="<?php esc_attr_e( 'Home The SIP', 'pharmanow' ); ?>">
				<img
					src="<?php echo esc_url( pn_asset( 'images/logo-thesip-header.png' ) ); ?>"
					alt="The SIP"
					width="160"
					height="32"
					fetchpriority="high"
					decoding="async"
					class="h-10 w-auto transition-transform duration-300 origin-left"
					x-bind:style="scrolled ? 'transform:scale(0.8)' : 'transform:scale(1)'"
				>
			</a>

			<!-- Search bar (desktop inline; su mobile sta nella riga dedicata sotto) -->
			<div class="hidden lg:block w-full max-w-2xl">
				<?php get_template_part( 'template-parts/header/search-bar' ); ?>
			</div>

			<!-- Actions: account + wishlist + cart -->
			<div class="flex items-center gap-0.5 ml-auto">
				<?php get_template_part( 'template-parts/header/account-menu' ); ?>

				<a
					href="<?php echo esc_url( home_url( '/account/wishlist/' ) ); ?>"
					class="relative hidden sm:inline-flex items-center justify-center h-9 w-9 rounded-md text-gray-600 hover:bg-gray-100 hover:text-pharma-teal transition-opacity duration-300"
					x-bind:style="scrolled ? 'opacity:0; pointer-events:none' : 'opacity:1; pointer-events:auto'"
					aria-label="<?php esc_attr_e( 'Wishlist', 'pharmanow' ); ?>"
				>
					<?php pn_icon( 'heart', array( 'class' => 'h-5 w-5' ) ); ?>
					<span
						x-show="$store.pnWishlist?.count > 0"
						x-text="$store.pnWishlist?.count"
						x-cloak
						class="absolute -top-1 -right-1 inline-flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white tabular-nums"
					></span>
				</a>

				<!-- Mini Cart custom (trigger + drawer Alpine) -->
				<?php get_template_part( 'template-parts/header/mini-cart-trigger' ); ?>
			</div>
		</div>

		<!-- Mobile search row (persistente, full-width) — su lg+ la search è inline nella riga sopra -->
		<div class="lg:hidden pb-3">
			<?php get_template_part( 'template-parts/header/search-bar' ); ?>
		</div>
	</div>

	<!-- ── Nav row desktop — animate height to 0 on scroll ── -->
	<div
		class="hidden lg:block transition-[height,opacity] duration-300 ease-out"
		x-bind:class="scrolled ? 'overflow-hidden' : ''"
		x-bind:style="scrolled ? 'height:0; opacity:0' : 'height:40px; opacity:1'"
	>
		<?php get_template_part( 'template-parts/header/mega-menu' ); ?>
	</div>

	<!-- ── Mobile drawer ── -->
	<div
		x-show="mobileOpen"
		x-cloak
		x-transition.opacity
		class="lg:hidden border-t bg-white"
	>
		<!-- Mobile trust strip -->
		<div class="flex items-center justify-around py-2.5 px-4 border-b bg-gray-50 text-[10px] text-gray-500">
			<span class="flex items-center gap-1">
				<?php pn_icon( 'map-pin', array( 'class' => 'h-3 w-3 text-pharma-teal' ) ); ?>
				<?php esc_html_e( 'Progetto nato a Napoli', 'pharmanow' ); ?>
			</span>
			<span class="flex items-center gap-1">
				<?php pn_icon( 'truck', array( 'class' => 'h-3 w-3 text-pharma-teal' ) ); ?>
				<?php
				printf(
					/* translators: %s = soglia */
					esc_html__( 'Gratis >€%s', 'pharmanow' ),
					esc_html( number_format( $pn_threshold, 2, ',', '.' ) )
				);
				?>
			</span>
			<span class="flex items-center gap-1">
				<?php pn_icon( 'credit-card', array( 'class' => 'h-3 w-3 text-pharma-teal' ) ); ?>
				<?php esc_html_e( 'Pagamento Sicuro', 'pharmanow' ); ?>
			</span>
		</div>

		<?php get_template_part( 'template-parts/header/mobile-menu' ); ?>

		<!-- Mobile secondary links -->
		<nav class="flex flex-col p-2 border-t">
			<?php
			$pn_secondary = array(
				array(
					'label'     => __( 'Offerte', 'pharmanow' ),
					'href'      => home_url( '/?on_sale=1' ),
					'highlight' => true,
				),
				array(
					'label' => __( 'Chi Siamo', 'pharmanow' ),
					'href'  => home_url( '/chi-siamo' ),
				),
				array(
					'label' => __( 'Contattaci', 'pharmanow' ),
					'href'  => home_url( '/contatti' ),
				),
			);
			foreach ( $pn_secondary as $link ) :
				$is_highlight = ! empty( $link['highlight'] );
				?>
				<a
					href="<?php echo esc_url( $link['href'] ); ?>"
					class="px-4 py-3 text-sm font-medium hover:bg-gray-50 rounded-md transition-colors <?php echo $is_highlight ? 'text-pharma-teal' : 'text-gray-700'; ?>"
					x-on:click="mobileOpen = false"
				>
					<?php echo esc_html( $link['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</nav>
	</div>
</header>
