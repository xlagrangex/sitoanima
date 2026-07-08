<?php
/**
 * Product section home — porting da components/shop/home/product-section.tsx (Next).
 *
 * Args richiesti:
 *   - title (string)
 *   - view_all (string url)
 *   E uno tra:
 *     - sort     ('popularity'|'newest'|'on_sale') → query globale
 *     - category (string handle, es. 'integratori') → query per categoria
 *     - slugs    (string[]) → curation manuale per slug
 *     - skus     (array)    → curation manuale per SKU/MINSAN (può essere ['sku'=>..,'name'=>..])
 * Args opzionali:
 *   - subtitle (string)
 *   - limit    (int, default 5)
 *   - cols     (int 2..6, default 5) — colonne grid desktop
 *   - icon     (string) — slug icona accanto al titolo (es. 'pill', 'leaf')
 *   - icon_color (string) — classe colore icona (es. 'text-emerald-600')
 *   - bg_color (string) — CSS color o classe Tailwind per sfondo sezione
 *   - cat_orderby (string 'popularity'|'date', default 'popularity') — ordinamento quando si usa category
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn_title       = isset( $args['title'] ) ? (string) $args['title'] : '';
$pn_subtitle    = isset( $args['subtitle'] ) ? (string) $args['subtitle'] : '';
$pn_sort        = isset( $args['sort'] ) ? (string) $args['sort'] : 'popularity';
$pn_category    = isset( $args['category'] ) ? (string) $args['category'] : '';
$pn_slugs       = isset( $args['slugs'] ) && is_array( $args['slugs'] ) ? $args['slugs'] : array();
$pn_skus        = isset( $args['skus'] ) && is_array( $args['skus'] ) ? $args['skus'] : array();
$pn_view_all    = isset( $args['view_all'] ) ? (string) $args['view_all'] : home_url( '/catalogo' );
$pn_limit       = isset( $args['limit'] ) ? (int) $args['limit'] : 5;
$pn_cols        = isset( $args['cols'] ) ? max( 2, min( 6, (int) $args['cols'] ) ) : 5;
$pn_icon        = isset( $args['icon'] ) ? (string) $args['icon'] : '';
$pn_icon_color  = isset( $args['icon_color'] ) ? (string) $args['icon_color'] : 'text-pharma-teal';
$pn_bg_color    = isset( $args['bg_color'] ) ? (string) $args['bg_color'] : '';
$pn_cat_orderby = isset( $args['cat_orderby'] ) && 'date' === $args['cat_orderby'] ? 'date' : 'popularity';

if ( ! $pn_title ) {
	return;
}

if ( ! empty( $pn_skus ) && function_exists( 'pn_query_products_by_skus' ) ) {
	$pn_products = pn_query_products_by_skus( $pn_skus );
} elseif ( ! empty( $pn_slugs ) && function_exists( 'pn_query_products_by_slugs' ) ) {
	$pn_products = pn_query_products_by_slugs( $pn_slugs );
} elseif ( $pn_category ) {
	$pn_products = pn_query_products_by_category( $pn_category, $pn_limit, $pn_cat_orderby );
} else {
	$pn_products = pn_query_products( $pn_sort, $pn_limit );
}

$pn_section_style = '';
$pn_section_class = '';
if ( $pn_bg_color ) {
	if ( '#' === substr( $pn_bg_color, 0, 1 ) || 0 === strpos( $pn_bg_color, 'rgb' ) || 0 === strpos( $pn_bg_color, 'hsl' ) ) {
		$pn_section_style = 'background-color:' . esc_attr( $pn_bg_color ) . ';';
	} else {
		$pn_section_class = ' ' . $pn_bg_color;
	}
}

$pn_grid_cols_map = array(
	2 => 'sm:grid-cols-2 lg:grid-cols-2',
	3 => 'sm:grid-cols-2 lg:grid-cols-3',
	4 => 'sm:grid-cols-2 lg:grid-cols-4',
	5 => 'sm:grid-cols-3 lg:grid-cols-5',
	6 => 'sm:grid-cols-3 lg:grid-cols-6',
);
$pn_grid_class = $pn_grid_cols_map[ $pn_cols ] ?? $pn_grid_cols_map[5];
?>

<section
	class="<?php echo $pn_bg_color ? 'py-10 md:py-14' : 'py-10'; ?><?php echo esc_attr( $pn_section_class ); ?>"
	<?php if ( $pn_section_style ) : ?>style="<?php echo $pn_section_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"<?php endif; ?>
>
<div class="container max-w-7xl mx-auto px-4">
	<div class="mb-6 flex items-end justify-between gap-4">
		<div class="flex items-start gap-3 min-w-0">
			<?php if ( $pn_icon ) : ?>
				<span class="shrink-0 inline-flex h-10 w-10 md:h-11 md:w-11 items-center justify-center rounded-xl bg-white shadow-sm <?php echo esc_attr( $pn_icon_color ); ?>">
					<?php pn_icon( $pn_icon, array( 'class' => 'h-5 w-5 md:h-6 md:w-6' ) ); ?>
				</span>
			<?php endif; ?>
			<div class="min-w-0">
				<h2 class="text-2xl font-bold leading-tight"><?php echo esc_html( $pn_title ); ?></h2>
				<?php if ( $pn_subtitle ) : ?>
					<p class="mt-1 text-sm text-muted-foreground"><?php echo esc_html( $pn_subtitle ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<a href="<?php echo esc_url( $pn_view_all ); ?>" data-slot="button" class="hidden sm:inline-flex items-center text-sm font-medium text-pharma-teal hover:underline">
			<?php
			echo pn_slide_text(
				'<span>' . esc_html__( 'Vedi tutti', 'pharmanow' ) . '</span>' .
				pn_icon_string( 'arrow-right', array( 'class' => 'h-4 w-4' ) )
			);
			?>
		</a>
	</div>

	<?php if ( empty( $pn_products ) ) : ?>
		<div class="py-12 text-center text-sm text-muted-foreground">
			<?php esc_html_e( 'Nessun prodotto trovato', 'pharmanow' ); ?>
		</div>
	<?php else : ?>
		<div class="grid grid-cols-2 gap-4 <?php echo esc_attr( $pn_grid_class ); ?>">
			<?php foreach ( $pn_products as $pn_product ) : ?>
				<?php get_template_part( 'template-parts/shop/product-card', null, array( 'product' => $pn_product ) ); ?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<div class="mt-4 text-center sm:hidden">
		<a href="<?php echo esc_url( $pn_view_all ); ?>" data-slot="button" class="inline-flex items-center h-9 px-4 rounded-md border border-pharma-teal text-pharma-teal text-sm font-medium hover:bg-pharma-teal/5">
			<?php
			echo pn_slide_text(
				'<span>' . esc_html__( 'Vedi tutti', 'pharmanow' ) . '</span>' .
				pn_icon_string( 'arrow-right', array( 'class' => 'h-4 w-4' ) )
			);
			?>
		</a>
	</div>
</div>
</section>
