<?php
/**
 * Checkout Template — Full-page, minimal chrome.
 *
 * Logo top-left, secure footer with privacy/terms links.
 * WC blocks handle their own form layout.
 *
 * @package BizStudio
 */

defined( 'ABSPATH' ) || exit;

$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy' );
$terms_page_id   = (int) get_option( 'woocommerce_terms_page_id' );
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

	<!-- Header: logo a sinistra -->
	<header class="biz-checkout__header">
		<div class="biz-checkout__header-inner">
			<div class="biz-checkout__logo">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="biz-checkout__logo-text">
						<?php bloginfo( 'name' ); ?>
					</a>
				<?php endif; ?>
			</div>
			<div class="biz-checkout__secure-badge">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
				<?php esc_html_e( 'Checkout sicuro', 'bizstudio' ); ?>
			</div>
		</div>
	</header>

	<!-- Checkout content (WC blocks handle their own layout) -->
	<main class="biz-checkout__main">
		<?php while ( have_posts() ) : the_post(); ?>
			<?php the_content(); ?>
		<?php endwhile; ?>
	</main>

	<!-- Footer: torna al carrello + checkout sicuro + link legali -->
	<footer class="biz-checkout__footer">
		<div class="biz-checkout__footer-inner">
			<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="biz-checkout__back">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
				<?php esc_html_e( 'Torna al carrello', 'bizstudio' ); ?>
			</a>

			<div class="biz-checkout__footer-center">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
				<span><?php esc_html_e( 'Checkout sicuro', 'bizstudio' ); ?></span>
			</div>

			<div class="biz-checkout__footer-links">
				<?php if ( $privacy_page_id ) : ?>
					<a href="<?php echo esc_url( get_permalink( $privacy_page_id ) ); ?>" target="_blank" rel="noopener">
						<?php esc_html_e( 'Privacy Policy', 'bizstudio' ); ?>
					</a>
				<?php endif; ?>
				<?php if ( $terms_page_id ) : ?>
					<a href="<?php echo esc_url( get_permalink( $terms_page_id ) ); ?>" target="_blank" rel="noopener">
						<?php esc_html_e( 'Termini e Condizioni', 'bizstudio' ); ?>
					</a>
				<?php endif; ?>
				<span class="biz-checkout__copy">&copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?></span>
			</div>
		</div>
	</footer>

</div>

<?php wp_footer(); ?>
</body>
</html>
