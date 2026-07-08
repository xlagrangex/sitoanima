<?php
/**
 * Theme header — entry point.
 * Renderizza template-parts/header/main.php.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<link rel="icon" type="image/png" href="<?php echo esc_url( get_template_directory_uri() . '/assets/images/favicon.png' ); ?>">
	<link rel="apple-touch-icon" href="<?php echo esc_url( get_template_directory_uri() . '/assets/images/favicon.png' ); ?>">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'font-sans antialiased text-foreground bg-background' ); ?>>
<?php wp_body_open(); ?>

<?php
// Sul checkout (e thank-you) un header minimal: solo logo + "Pagamento sicuro".
// Sul resto del sito, header completo.
if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_wc_endpoint_url( 'order-received' ) ) :
	?>
	<header class="pn-co-header">
		<div class="pn-co-header__inner">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="pn-co-header__logo" aria-label="<?php esc_attr_e( 'Home The SIP', 'pharmanow' ); ?>">
				<img src="<?php echo esc_url( pn_asset( 'images/logo-thesip-header.png' ) ); ?>" alt="The SIP">
			</a>
			<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="pn-co-header__back">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
				<span><?php esc_html_e( 'Torna al carrello', 'pharmanow' ); ?></span>
			</a>
			<span class="pn-co-header__secure">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
				<span><?php esc_html_e( 'Pagamento sicuro', 'pharmanow' ); ?></span>
			</span>
		</div>
	</header>
	<div class="pn-co-header__spacer"></div>
<?php else : ?>
	<?php get_template_part( 'template-parts/header/main' ); ?>
<?php endif; ?>

<main class="min-h-[60vh]">
