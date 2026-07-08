<?php
/**
 * Sezione "Esplora per categoria" — tabs interattive con 12 prodotti per categoria.
 *
 * Switching client-side via Alpine.js (no fetch).
 * Tutte le 8 categorie sono renderizzate SSR e mostrate via x-show.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn_explore_handles = array(
	'set-di-carte',
	'bundle',
	'edizioni-speciali',
);

$pn_tree      = pn_get_mega_menu_tree();
$pn_by_handle = array();
foreach ( $pn_tree as $pn_node ) {
	$pn_by_handle[ $pn_node['handle'] ] = $pn_node;
}

$pn_tabs = array();
foreach ( $pn_explore_handles as $pn_h ) {
	if ( ! isset( $pn_by_handle[ $pn_h ] ) ) {
		continue;
	}
	$pn_node     = $pn_by_handle[ $pn_h ];
	$pn_products = pn_query_products_by_category( $pn_h, 10, 'date' );
	if ( empty( $pn_products ) ) {
		continue;
	}
	$pn_tabs[] = array(
		'handle'   => $pn_h,
		'name'     => $pn_node['name'],
		'link'     => $pn_node['link'],
		'icon'     => pn_root_icon( $pn_h ),
		'products' => $pn_products,
	);
}

if ( empty( $pn_tabs ) ) {
	return;
}

$pn_active = $pn_tabs[0]['handle'];
?>

<section class="bg-gray-50 py-12 md:py-16" x-data="{ active: '<?php echo esc_js( $pn_active ); ?>' }">
	<div class="container max-w-7xl mx-auto px-4">
		<div class="mb-8 text-center max-w-2xl mx-auto">
			<h2 class="text-2xl md:text-3xl font-bold tracking-tight">
				<span class="pharma-text-grad"><?php esc_html_e( 'Esplora', 'pharmanow' ); ?></span>
				<?php esc_html_e( 'per categoria', 'pharmanow' ); ?>
			</h2>
			<p class="mt-2 text-sm md:text-base text-muted-foreground">
				<?php esc_html_e( 'Scegli una categoria per vedere i prodotti più recenti.', 'pharmanow' ); ?>
			</p>
		</div>

		<?php /* Tabs grid responsive: 2 righe su desktop (5×2), 5 righe mobile (2×5) */ ?>
		<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 mb-8" role="tablist">
			<?php foreach ( $pn_tabs as $pn_tab ) :
				$pn_color = $pn_tab['icon']['color'] ?? 'text-gray-500';
				?>
				<button
					type="button"
					role="tab"
					@click="active = '<?php echo esc_js( $pn_tab['handle'] ); ?>'"
					:aria-selected="active === '<?php echo esc_js( $pn_tab['handle'] ); ?>'"
					:class="active === '<?php echo esc_js( $pn_tab['handle'] ); ?>'
						? 'pharma-primary-bg text-white shadow-sm border-transparent'
						: 'bg-white text-gray-700 border-gray-200 hover:border-pharma-teal hover:text-pharma-teal'"
					class="inline-flex items-center justify-center gap-2 rounded-full border px-4 py-2.5 text-xs sm:text-sm font-medium transition-all duration-200"
				>
					<span :class="active === '<?php echo esc_js( $pn_tab['handle'] ); ?>' ? 'text-white' : '<?php echo esc_attr( $pn_color ); ?>'" class="inline-flex shrink-0">
						<?php if ( $pn_tab['icon']['icon'] ) : ?>
							<?php pn_icon( $pn_tab['icon']['icon'], array( 'class' => 'h-4 w-4' ) ); ?>
						<?php endif; ?>
					</span>
					<span class="text-center leading-tight"><?php echo esc_html( $pn_tab['name'] ); ?></span>
				</button>
			<?php endforeach; ?>
		</div>

		<?php /* Pannelli */ ?>
		<?php foreach ( $pn_tabs as $pn_tab ) : ?>
			<div
				role="tabpanel"
				x-show="active === '<?php echo esc_js( $pn_tab['handle'] ); ?>'"
				x-cloak
				x-transition.opacity.duration.200ms
			>
				<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
					<?php foreach ( $pn_tab['products'] as $pn_product ) : ?>
						<?php get_template_part( 'template-parts/shop/product-card', null, array( 'product' => $pn_product ) ); ?>
					<?php endforeach; ?>
				</div>

				<div class="mt-8 text-center">
					<a
						href="<?php echo esc_url( $pn_tab['link'] ); ?>"
						data-slot="button"
						class="inline-flex items-center gap-2 h-11 px-6 rounded-xl bg-white border border-gray-200 text-pharma-teal font-semibold hover:bg-gray-50 transition-colors"
					>
						<?php
						echo pn_slide_text(
							'<span>' . sprintf(
								/* translators: %s = nome categoria */
								esc_html__( 'Vedi tutto in %s', 'pharmanow' ),
								esc_html( $pn_tab['name'] )
							) . '</span>' .
							pn_icon_string( 'arrow-right', array( 'class' => 'h-4 w-4' ) )
						);
						?>
					</a>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
