<?php
/**
 * Single product template — replica fedele di app/(shop)/prodotto/[slug] del Next.
 *
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

while ( have_posts() ) :
	the_post();
	global $product;
	if ( ! $product instanceof WC_Product ) {
		$product = wc_get_product( get_the_ID() );
	}
	if ( ! $product ) {
		continue;
	}
	$pn_meta = pn_pharma_meta( $product );
	?>
	<div class="container mx-auto max-w-7xl px-4 py-6 md:py-10">

		<?php get_template_part( 'template-parts/product/breadcrumb', null, array( 'product' => $product ) ); ?>

		<?php if ( $pn_meta['sostituito_da'] && '' !== $pn_meta['sostituito_at'] ) : ?>
			<?php
			$pn_days = pn_substitution_days_since( $pn_meta['sostituito_at'] );
			if ( $pn_days < PN_SUBSTITUTION_DAYS ) :
				get_template_part(
					'template-parts/product/substitution-banner',
					null,
					array(
						'new_product_id' => $pn_meta['sostituito_da'],
						'days_since'     => $pn_days,
					)
				);
			endif;
			?>
		<?php endif; ?>

		<!-- Above the fold: gallery + info -->
		<div data-pdp-hero class="grid gap-8 md:grid-cols-2 lg:gap-12">
			<?php get_template_part( 'template-parts/product/gallery', null, array( 'product' => $product ) ); ?>
			<?php get_template_part( 'template-parts/product/info', null, array( 'product' => $product, 'meta' => $pn_meta ) ); ?>
		</div>

		<!-- Tabs -->
		<div class="mt-10 md:mt-16">
			<?php get_template_part( 'template-parts/product/tabs', null, array( 'product' => $product, 'meta' => $pn_meta ) ); ?>
		</div>

		<!-- Recommendations + recently viewed + reviews placeholder -->
		<div class="mt-12 space-y-12 pb-24 md:pb-0">
			<?php get_template_part( 'template-parts/product/recommendations', null, array( 'product_id' => $product->get_id() ) ); ?>
			<?php get_template_part( 'template-parts/product/recently-viewed', null, array( 'product_id' => $product->get_id() ) ); ?>
			<section class="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground">
				<p>⭐ Recensioni &amp; Q&amp;A — in arrivo</p>
			</section>
		</div>

	</div>

	<?php get_template_part( 'template-parts/product/sticky-atc', null, array( 'product' => $product ) ); ?>
	<?php
endwhile;

get_footer( 'shop' );
