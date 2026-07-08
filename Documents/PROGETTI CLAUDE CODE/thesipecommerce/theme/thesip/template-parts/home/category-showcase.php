<?php
/**
 * Category showcase home — porting da components/shop/home/CategoryShowcase.tsx (Next).
 *
 * 4 sezioni "Novità in [Categoria]" con horizontal scroll snap di 12 prodotti recenti.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn_showcase_handles = array(
	'set-di-carte',
	'bundle',
	'edizioni-speciali',
);

$pn_tree     = pn_get_mega_menu_tree();
$pn_by_handle = array();
foreach ( $pn_tree as $pn_node ) {
	$pn_by_handle[ $pn_node['handle'] ] = $pn_node;
}

foreach ( $pn_showcase_handles as $pn_handle ) :
	if ( ! isset( $pn_by_handle[ $pn_handle ] ) ) {
		continue;
	}
	$pn_node     = $pn_by_handle[ $pn_handle ];
	$pn_products = pn_query_products_by_category( $pn_handle, 12, 'date' );
	if ( empty( $pn_products ) ) {
		continue;
	}
	?>
	<section class="container max-w-7xl mx-auto px-4 py-10">
		<div class="mb-6 flex items-end justify-between">
			<a href="<?php echo esc_url( $pn_node['link'] ); ?>" class="group inline-flex items-center gap-2">
				<h2 class="text-2xl font-bold transition-colors group-hover:text-pharma-teal">
					<?php
					printf(
						/* translators: %s = nome categoria */
						esc_html__( 'Novità in %s', 'pharmanow' ),
						esc_html( $pn_node['name'] )
					);
					?>
				</h2>
				<span class="text-pharma-teal transition-transform group-hover:translate-x-1">
					<?php pn_icon( 'arrow-right', array( 'class' => 'h-5 w-5' ) ); ?>
				</span>
			</a>
		</div>

		<div class="-mx-4 overflow-x-auto px-4 pb-2 snap-x snap-mandatory scroll-pl-4">
			<div class="flex gap-4">
				<?php foreach ( $pn_products as $pn_product ) : ?>
					<div class="snap-start shrink-0 w-[200px] sm:w-[220px] lg:w-[240px]">
						<?php get_template_part( 'template-parts/shop/product-card', null, array( 'product' => $pn_product ) ); ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
endforeach;
