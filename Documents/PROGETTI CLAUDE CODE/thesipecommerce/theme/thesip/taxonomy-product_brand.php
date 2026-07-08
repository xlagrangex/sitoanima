<?php
/**
 * Pagina marchio singolo — replica components/shop/brands/BrandPage.tsx.
 *
 * Servita su /marchi/<slug>/.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header( 'shop' );

$pn_term = get_queried_object();
if ( ! $pn_term || is_wp_error( $pn_term ) ) {
	get_footer( 'shop' );
	return;
}

$pn_brand_display = function_exists( 'pn_strip_company_suffix' )
	? pn_strip_company_suffix( (string) $pn_term->name )
	: (string) $pn_term->name;
$pn_count = (int) $pn_term->count;

global $wp_query;
$pn_total = isset( $wp_query->found_posts ) ? (int) $wp_query->found_posts : $pn_count;
?>

<div class="bg-white">
	<div class="container mx-auto max-w-7xl px-4 py-6 md:py-10">

		<nav class="mb-3 text-sm text-gray-500" aria-label="<?php esc_attr_e( 'Breadcrumb', 'pharmanow' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-pharma-teal transition-colors">
				<?php esc_html_e( 'Home', 'pharmanow' ); ?>
			</a>
			<span class="mx-2 text-gray-300">/</span>
			<a href="<?php echo esc_url( home_url( '/marchi/' ) ); ?>" class="hover:text-pharma-teal transition-colors">
				<?php esc_html_e( 'Marchi', 'pharmanow' ); ?>
			</a>
			<span class="mx-2 text-gray-300">/</span>
			<span class="text-gray-900 font-medium"><?php echo esc_html( $pn_brand_display ); ?></span>
		</nav>

		<header class="mb-8 space-y-2 rounded-lg border border-pharma-teal/15 bg-gradient-to-br from-pharma-teal/5 to-transparent p-6 md:p-8">
			<p class="text-xs font-semibold uppercase tracking-wider text-gray-500"><?php esc_html_e( 'Marchio', 'pharmanow' ); ?></p>
			<h1 class="text-3xl md:text-4xl font-bold text-gray-900 tracking-tight"><?php echo esc_html( $pn_brand_display ); ?></h1>
			<p class="text-sm text-gray-500">
				<?php
				printf(
					/* translators: %s numero prodotti */
					esc_html( _n( '%s prodotto disponibile', '%s prodotti disponibili', $pn_total, 'pharmanow' ) ),
					'<strong class="text-gray-700">' . esc_html( number_format_i18n( $pn_total ) ) . '</strong>'
				);
				?>
			</p>
		</header>

		<?php if ( woocommerce_product_loop() ) : ?>

			<?php woocommerce_product_loop_start(); ?>
				<?php
				while ( have_posts() ) :
					the_post();
					do_action( 'woocommerce_shop_loop' );
					wc_get_template_part( 'content', 'product' );
				endwhile;
				?>
			<?php woocommerce_product_loop_end(); ?>

			<?php do_action( 'woocommerce_after_shop_loop' ); ?>

		<?php else : ?>

			<p class="rounded-lg border border-dashed border-gray-300 py-12 text-center text-sm text-gray-500">
				<?php esc_html_e( 'Nessun prodotto disponibile per questo marchio al momento.', 'pharmanow' ); ?>
			</p>

		<?php endif; ?>
	</div>
</div>

<?php
get_footer( 'shop' );
