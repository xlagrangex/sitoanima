<?php
/**
 * Archive Product — pagina catalogo Pharmanow.
 *
 * Replica components/shop/catalog/CatalogPage.tsx del frontend Next:
 *  - container max-w-7xl + breadcrumb
 *  - header (titolo + descrizione + InstantSearch)
 *  - subcategories chip strip (su tassonomie)
 *  - toolbar (filtro mobile sheet + sort dropdown)
 *  - layout grid [sidebar 260px | products]
 *  - paginazione
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header( 'shop' );

/**
 * Determina contesto pagina:
 *  - shop: /catalogo (page_for_shop oppure post_type_archive product)
 *  - taxonomy product_cat: /catalogo/<slug>
 */
$pn_is_tax_cat   = is_product_taxonomy() && is_tax( 'product_cat' );
$pn_current_term = $pn_is_tax_cat ? get_queried_object() : null;

// Titolo + descrizione.
if ( $pn_is_tax_cat && $pn_current_term ) {
	$pn_title = single_term_title( '', false );
	$pn_desc  = term_description( $pn_current_term );
} else {
	$pn_title = woocommerce_page_title( false );
	$pn_desc  = '';
}

// Conteggio prodotti pagina corrente (per intestazione tipo "X prodotti in catalogo").
global $wp_query;
$pn_total_products = isset( $wp_query->found_posts ) ? (int) $wp_query->found_posts : 0;
?>

<div class="bg-white">
	<div class="container mx-auto max-w-7xl px-4 py-6 md:py-10">

		<?php
		/**
		 * Breadcrumb.
		 * Hook into woocommerce_breadcrumb_defaults se il design lo richiede.
		 */
		?>
		<nav class="mb-3 text-sm text-gray-500" aria-label="<?php esc_attr_e( 'Breadcrumb', 'pharmanow' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-pharma-teal transition-colors">
				<?php esc_html_e( 'Home', 'pharmanow' ); ?>
			</a>
			<span class="mx-2 text-gray-300">/</span>
			<?php if ( $pn_is_tax_cat ) : ?>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="hover:text-pharma-teal transition-colors">
					<?php esc_html_e( 'Catalogo', 'pharmanow' ); ?>
				</a>
				<?php
				// Crumbs ancestors.
				if ( $pn_current_term && $pn_current_term->parent ) {
					$ancestors = array_reverse( get_ancestors( $pn_current_term->term_id, 'product_cat' ) );
					foreach ( $ancestors as $aid ) {
						$ancestor = get_term( $aid, 'product_cat' );
						if ( $ancestor && ! is_wp_error( $ancestor ) ) :
							?>
							<span class="mx-2 text-gray-300">/</span>
							<a href="<?php echo esc_url( get_term_link( $ancestor ) ); ?>" class="hover:text-pharma-teal transition-colors">
								<?php echo esc_html( $ancestor->name ); ?>
							</a>
							<?php
						endif;
					}
				}
				?>
				<span class="mx-2 text-gray-300">/</span>
				<span class="text-gray-900 font-medium"><?php echo esc_html( $pn_title ); ?></span>
			<?php else : ?>
				<span class="text-gray-900 font-medium"><?php esc_html_e( 'Catalogo', 'pharmanow' ); ?></span>
			<?php endif; ?>
		</nav>

		<?php get_template_part( 'template-parts/catalog/header', null, array(
			'title'         => $pn_is_tax_cat ? $pn_title : __( 'Tutti i prodotti', 'pharmanow' ),
			'description'   => $pn_desc ? wp_strip_all_tags( $pn_desc ) : sprintf(
				/* translators: %d: numero prodotti */
				_n( '%d prodotto in catalogo', '%d prodotti in catalogo', $pn_total_products, 'pharmanow' ),
				$pn_total_products
			),
		) ); ?>

		<?php if ( $pn_is_tax_cat && $pn_current_term ) : ?>
			<?php get_template_part( 'template-parts/catalog/subcategories', null, array( 'parent_id' => $pn_current_term->term_id ) ); ?>
		<?php elseif ( ! $pn_is_tax_cat ) : ?>
			<?php get_template_part( 'template-parts/catalog/subcategories', null, array( 'parent_id' => 0 ) ); ?>
		<?php endif; ?>

		<?php
		// Toolbar: filtro mobile (Alpine sheet) + sort dropdown.
		get_template_part( 'template-parts/catalog/toolbar' );
		?>

		<div class="grid gap-6 md:grid-cols-[260px_1fr] lg:gap-8">
			<aside class="hidden md:block">
				<?php get_template_part( 'template-parts/catalog/sidebar-filters' ); ?>
			</aside>

			<div class="space-y-6">
				<?php if ( woocommerce_product_loop() ) : ?>

					<p class="text-sm text-gray-500">
						<?php
						printf(
							/* translators: %s: numero prodotti */
							esc_html( _n( '%s prodotto', '%s prodotti', $pn_total_products, 'pharmanow' ) ),
							'<strong class="text-gray-700">' . esc_html( number_format_i18n( $pn_total_products ) ) . '</strong>'
						);
						?>
					</p>

					<?php woocommerce_product_loop_start(); ?>

					<?php if ( wc_get_loop_prop( 'total' ) ) : ?>
						<?php
						while ( have_posts() ) :
							the_post();
							/**
							 * Hook: woocommerce_shop_loop.
							 */
							do_action( 'woocommerce_shop_loop' );
							wc_get_template_part( 'content', 'product' );
						endwhile;
						?>
					<?php endif; ?>

					<?php woocommerce_product_loop_end(); ?>

					<?php
					/**
					 * Paginazione (override pagination.php sotto).
					 */
					do_action( 'woocommerce_after_shop_loop' );
					?>

				<?php else : ?>
					<?php
					/**
					 * Hook: woocommerce_no_products_found.
					 */
					do_action( 'woocommerce_no_products_found' );
					?>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<?php
	// Mobile filters drawer (Alpine sheet) — outside grid for full-screen overlay.
	get_template_part( 'template-parts/catalog/mobile-drawer' );
	?>
</div>

<?php
get_footer( 'shop' );
