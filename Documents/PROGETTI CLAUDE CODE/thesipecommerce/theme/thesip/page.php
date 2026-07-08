<?php
/**
 * Default page template.
 *
 * Su pagine WooCommerce (cart, checkout, account, ecc.) il template Woo
 * renderizza il proprio layout completo — niente H1 wrapper.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$pn_is_woo_page = function_exists( 'is_cart' ) && (
	is_cart() ||
	is_checkout() ||
	( function_exists( 'is_account_page' ) && is_account_page() ) ||
	( function_exists( 'is_order_received_page' ) && is_order_received_page() )
);
?>

<?php if ( $pn_is_woo_page ) : ?>

	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>

<?php else : ?>

	<div class="container max-w-4xl mx-auto px-4 py-12">
		<?php while ( have_posts() ) : the_post(); ?>
			<article class="mb-8">
				<h1 class="text-3xl font-bold mb-6"><?php the_title(); ?></h1>
				<div class="prose"><?php the_content(); ?></div>
			</article>
		<?php endwhile; ?>
	</div>

<?php endif; ?>

<?php
get_footer();
