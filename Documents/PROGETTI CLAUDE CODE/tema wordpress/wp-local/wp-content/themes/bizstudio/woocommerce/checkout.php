<?php
/**
 * Checkout Full-Page Template (Shopify-style)
 *
 * Minimal page with logo, step breadcrumbs, and checkout blocks.
 * No header, no footer — clean checkout experience.
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
	<!-- Checkout Header: Logo + Steps -->
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

			<nav class="biz-checkout__steps" aria-label="<?php esc_attr_e( 'Passaggi checkout', 'bizstudio' ); ?>">
				<button class="biz-checkout__step biz-checkout__step--active" data-step="1" type="button">
					<span class="biz-checkout__step-num">1</span>
					<span class="biz-checkout__step-label"><?php esc_html_e( 'Informazioni', 'bizstudio' ); ?></span>
				</button>
				<span class="biz-checkout__step-sep" aria-hidden="true">
					<svg width="8" height="12" viewBox="0 0 8 12" fill="none"><path d="M1.5 1L6.5 6L1.5 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</span>
				<button class="biz-checkout__step" data-step="2" type="button" disabled>
					<span class="biz-checkout__step-num">2</span>
					<span class="biz-checkout__step-label"><?php esc_html_e( 'Spedizione', 'bizstudio' ); ?></span>
				</button>
				<span class="biz-checkout__step-sep" aria-hidden="true">
					<svg width="8" height="12" viewBox="0 0 8 12" fill="none"><path d="M1.5 1L6.5 6L1.5 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</span>
				<button class="biz-checkout__step" data-step="3" type="button" disabled>
					<span class="biz-checkout__step-num">3</span>
					<span class="biz-checkout__step-label"><?php esc_html_e( 'Pagamento', 'bizstudio' ); ?></span>
				</button>
			</nav>

			<div class="biz-checkout__secure">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
				<span><?php esc_html_e( 'Checkout sicuro', 'bizstudio' ); ?></span>
			</div>
		</div>
	</header>

	<!-- Mobile Order Summary Toggle -->
	<div class="biz-checkout__summary-toggle" id="biz-checkout-summary-toggle">
		<button type="button" class="biz-checkout__summary-btn">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
			<span><?php esc_html_e( 'Mostra riepilogo ordine', 'bizstudio' ); ?></span>
			<svg class="biz-checkout__summary-chevron" width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M3 4.5L6 7.5L9 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
			<?php if ( function_exists( 'WC' ) && WC()->cart ) : ?>
				<strong class="biz-checkout__summary-total"><?php echo WC()->cart->get_cart_total(); ?></strong>
			<?php endif; ?>
		</button>
	</div>

	<!-- Checkout Content -->
	<div class="biz-checkout__body">
		<div class="biz-checkout__form">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php the_content(); ?>
			<?php endwhile; ?>
		</div>
	</div>

	<!-- Back to cart -->
	<footer class="biz-checkout__footer">
		<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="biz-checkout__back">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
			<?php esc_html_e( 'Torna al carrello', 'bizstudio' ); ?>
		</a>
		<span class="biz-checkout__copyright">
			&copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>
		</span>
	</footer>
</div>

<?php wp_footer(); ?>
</body>
</html>
