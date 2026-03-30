<?php
/**
 * Checkout Template — Full-page, minimal chrome.
 *
 * No site header / footer. Just logo + checkout blocks + back link.
 *
 * @package BizStudio
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'biz-checkout-page' ); ?>>
<?php wp_body_open(); ?>

<div class="biz-checkout">

	<!-- Mini header: logo only -->
	<header class="biz-checkout__header">
		<div class="biz-checkout__header-inner">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="biz-checkout__logo-text">
					<?php bloginfo( 'name' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</header>

	<!-- Checkout content (WC blocks handle their own layout) -->
	<main class="biz-checkout__main">
		<?php while ( have_posts() ) : the_post(); ?>
			<?php the_content(); ?>
		<?php endwhile; ?>
	</main>

	<!-- Mini footer: back to cart -->
	<footer class="biz-checkout__footer">
		<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="biz-checkout__back">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
			<?php esc_html_e( 'Torna al carrello', 'bizstudio' ); ?>
		</a>
		<span class="biz-checkout__copy">&copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?></span>
	</footer>

</div>

<?php wp_footer(); ?>
</body>
</html>
