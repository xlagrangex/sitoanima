<?php
/**
 * Category grid home — porting da components/shop/home/category-grid.tsx (Next).
 *
 * 8 root in grid 2/4/8 con icona colorata in cerchio. Hover: gradient slide-up dal basso,
 * icona e testo diventano bianchi.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Categorie root The SIP.
$pn_grid_whitelist = array(
	'set-di-carte',
	'bundle',
	'edizioni-speciali',
);

$pn_tree     = pn_get_mega_menu_tree();
$pn_by_handle = array();
foreach ( $pn_tree as $pn_node ) {
	$pn_by_handle[ $pn_node['handle'] ] = $pn_node;
}

$pn_items = array();
foreach ( $pn_grid_whitelist as $pn_h ) {
	if ( isset( $pn_by_handle[ $pn_h ] ) ) {
		$pn_items[] = $pn_by_handle[ $pn_h ];
	}
}

if ( empty( $pn_items ) ) {
	return;
}
?>

<section class="container max-w-7xl mx-auto px-4 py-10">
	<h2 class="text-2xl font-bold mb-6">
		<span class="pharma-text-grad"><?php esc_html_e( 'Categorie', 'pharmanow' ); ?></span>
		<?php esc_html_e( 'principali', 'pharmanow' ); ?>
	</h2>
	<div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
		<?php
		foreach ( $pn_items as $pn_item ) :
			$pn_meta  = pn_root_icon( $pn_item['handle'] );
			// Mappiamo color → coppia bg/text per il cerchio (allineata a category-grid.tsx del Next).
			$pn_color_pairs = array(
				'text-blue-600'    => 'bg-blue-50 text-blue-600',
				'text-emerald-600' => 'bg-emerald-50 text-emerald-600',
				'text-pink-600'    => 'bg-pink-50 text-pink-600',
				'text-rose-600'    => 'bg-rose-50 text-rose-600',
				'text-yellow-600'  => 'bg-yellow-50 text-yellow-600',
				'text-red-600'     => 'bg-red-50 text-red-600',
				'text-green-600'   => 'bg-green-50 text-green-600',
				'text-orange-600'  => 'bg-orange-50 text-orange-600',
				'text-indigo-600'  => 'bg-indigo-50 text-indigo-600',
				'text-cyan-600'    => 'bg-cyan-50 text-cyan-600',
				'text-slate-600'   => 'bg-slate-50 text-slate-600',
				'text-amber-600'   => 'bg-amber-50 text-amber-600',
			);
			$pn_circle = $pn_color_pairs[ $pn_meta['color'] ] ?? 'bg-gray-50 text-gray-500';
			?>
			<a
				href="<?php echo esc_url( $pn_item['link'] ); ?>"
				class="group relative flex flex-col items-center gap-2.5 rounded-xl border bg-card p-4 text-center overflow-hidden isolate"
			>
				<span class="absolute inset-0 pharma-primary-bg translate-y-full transition-transform duration-300 ease-out group-hover:translate-y-0 -z-10"></span>

				<div class="rounded-full p-3 transition-colors duration-300 <?php echo esc_attr( $pn_circle ); ?> group-hover:bg-white/20 group-hover:text-white">
					<?php if ( $pn_meta['icon'] ) : ?>
						<?php pn_icon( $pn_meta['icon'], array( 'class' => 'h-5 w-5' ) ); ?>
					<?php endif; ?>
				</div>
				<span class="text-xs font-medium leading-tight transition-colors duration-300 group-hover:text-white">
					<?php echo esc_html( $pn_item['name'] ); ?>
				</span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
