<?php
/**
 * Page template — used for Cart, Checkout, My Account and all standard pages.
 *
 * @package BizStudio
 */

get_header();
?>

<main class="biz-main">
	<div class="biz-container">
		<?php while ( have_posts() ) : the_post(); ?>
			<?php if ( ! is_cart() && ! is_checkout() && ! is_account_page() ) : ?>
				<header class="biz-page-header">
					<h1 class="biz-page-header__title"><?php the_title(); ?></h1>
				</header>
			<?php endif; ?>

			<div class="biz-page-content entry-content">
				<?php the_content(); ?>
			</div>
		<?php endwhile; ?>
	</div>
</main>

<?php
get_footer();
