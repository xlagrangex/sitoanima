<?php
/**
 * Carousel "Spesso comprati insieme". Replica `Recommendations.tsx` + `ProductCarousel.tsx`.
 *
 * @var array $args { product_id: int }
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

$pn_pid     = (int) ( $args['product_id'] ?? 0 );
$pn_recs    = pn_get_recommendations( $pn_pid, 8 );
if ( empty( $pn_recs ) ) {
	return;
}
?>
<section class="space-y-4" x-data="pnCarousel()" x-init="init()">
	<div class="flex items-center justify-between">
		<h2 class="text-xl font-bold md:text-2xl"><?php esc_html_e( 'Spesso comprati insieme', 'pharmanow' ); ?></h2>
		<div class="hidden gap-2 md:flex">
			<button type="button" @click="scroll(-1)" aria-label="<?php esc_attr_e( 'Scorri a sinistra', 'pharmanow' ); ?>" class="flex h-9 w-9 items-center justify-center rounded-full border bg-background transition-colors hover:bg-muted">
				<?php pn_icon( 'chevron-left', array( 'class' => 'h-4 w-4' ) ); ?>
			</button>
			<button type="button" @click="scroll(1)" aria-label="<?php esc_attr_e( 'Scorri a destra', 'pharmanow' ); ?>" class="flex h-9 w-9 items-center justify-center rounded-full border bg-background transition-colors hover:bg-muted">
				<?php pn_icon( 'chevron-right', array( 'class' => 'h-4 w-4' ) ); ?>
			</button>
		</div>
	</div>
	<div x-ref="scroller" class="flex snap-x snap-mandatory gap-3 overflow-x-auto scroll-smooth pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
		<?php
		foreach ( $pn_recs as $pn_rec_product ) :
			?>
			<div class="w-[180px] shrink-0 snap-start sm:w-[200px]">
				<?php
				get_template_part(
					'template-parts/shop/product-card',
					null,
					array(
						'product' => $pn_rec_product,
						'variant' => 'compact',
					)
				);
				?>
			</div>
			<?php
		endforeach;
		?>
	</div>
</section>
