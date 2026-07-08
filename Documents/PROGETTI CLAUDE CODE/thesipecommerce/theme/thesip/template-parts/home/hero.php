<?php
/**
 * Hero banner home — porting da components/shop/home/hero-banner.tsx (Next).
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn_settings  = pn_shop_settings();
$pn_threshold = number_format( (float) $pn_settings['free_shipping_threshold'], 2, ',', '.' );
?>

<section class="relative overflow-hidden pharma-hero text-white">
	<div
		class="absolute inset-0 bg-cover bg-center"
		style="background-image:url('<?php echo esc_url( pn_asset( 'images/thesip/hero-bg.jpg' ) ); ?>')"
		aria-hidden="true"
	></div>
	<div class="absolute inset-0 bg-gradient-to-r from-[#063a52]/90 via-[#0a6e9e]/55 to-transparent" aria-hidden="true"></div>
	<div class="relative container max-w-7xl mx-auto px-4 py-16 md:py-28">
		<div class="grid md:grid-cols-2 gap-10 items-center">
			<div class="max-w-2xl">
				<div class="inline-flex items-center gap-2 rounded-full bg-white/15 backdrop-blur-sm text-white px-3 py-1 text-xs font-medium mb-4">
					<?php pn_icon( 'sparkles', array( 'class' => 'h-3.5 w-3.5' ) ); ?>
					<?php esc_html_e( 'Finanziato su Kickstarter · 165 sostenitori', 'pharmanow' ); ?>
				</div>

				<h1 class="text-3xl sm:text-4xl md:text-5xl font-bold tracking-tight leading-tight">
					<?php esc_html_e( 'Il', 'pharmanow' ); ?>
					<span class="text-pharma-accent-light"><?php esc_html_e( 'mare', 'pharmanow' ); ?></span>,
					<?php esc_html_e( 'in tasca', 'pharmanow' ); ?>
				</h1>

				<p class="mt-4 text-base md:text-lg text-white/80 max-w-xl leading-relaxed">
					<?php esc_html_e( '31 flashcard illustrate di biologia marina da collezionare. 25 artisti diversi, curate da 2 biologi marini. Take a seat and take a SIP!', 'pharmanow' ); ?>
				</p>

				<div class="mt-6 flex flex-wrap gap-3">
					<a
						href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalogo' ) ); ?>"
						data-slot="button"
						class="inline-flex items-center justify-center h-12 px-6 rounded-md font-semibold bg-white text-pharma-teal hover:bg-white/90 transition-colors"
					>
						<?php
						echo pn_slide_text(
							'<span>' . esc_html__( 'Scopri il set', 'pharmanow' ) . '</span>' .
							pn_icon_string( 'arrow-right', array( 'class' => 'h-4 w-4' ) )
						);
						?>
					</a>
					<a
						href="<?php echo esc_url( home_url( '/chi-siamo' ) ); ?>"
						data-slot="button"
						class="inline-flex items-center justify-center h-12 px-6 rounded-md font-semibold bg-transparent border border-white/60 text-white hover:bg-white hover:text-pharma-teal hover:border-white transition-colors"
					>
						<?php echo pn_slide_text( '<span>' . esc_html__( 'Il progetto', 'pharmanow' ) . '</span>' ); ?>
					</a>
				</div>

				<!-- Trust highlights -->
				<div class="mt-8 flex flex-wrap gap-6">
					<?php
					$pn_hl = array(
						array(
							'icon' => 'truck',
							'text' => sprintf(
								/* translators: %s = soglia */
								__( 'Spedizione gratuita sopra €%s', 'pharmanow' ),
								$pn_threshold
							),
						),
						array(
							'icon' => 'clock',
							'text' => __( 'Consegna in 24/48h', 'pharmanow' ),
						),
						array(
							'icon' => 'shield-check',
							'text' => __( 'Pagamenti 100% sicuri', 'pharmanow' ),
						),
					);
					foreach ( $pn_hl as $pn_h ) :
						?>
						<div class="flex items-center gap-2 text-sm text-white/70">
							<?php pn_icon( $pn_h['icon'], array( 'class' => 'h-4 w-4 text-pharma-accent-light shrink-0' ) ); ?>
							<span><?php echo esc_html( $pn_h['text'] ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="hidden md:flex justify-end">
				<img
					src="<?php echo esc_url( pn_asset( 'images/thesip/hero-cards.jpg' ) ); ?>"
					alt="<?php esc_attr_e( 'The SIP — il mazzo di flashcard illustrate di biologia marina', 'pharmanow' ); ?>"
					class="w-full max-w-md h-auto rounded-2xl shadow-2xl ring-1 ring-white/20 rotate-2 hover:rotate-0 transition-transform duration-300"
					loading="eager"
					decoding="async"
				>
			</div>
		</div>
	</div>
</section>
