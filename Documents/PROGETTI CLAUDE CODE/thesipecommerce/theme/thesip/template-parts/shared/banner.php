<?php
/**
 * Render singolo banner. Riceve $banner via set_query_var('pn_banner', ...).
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn_banner = get_query_var( 'pn_banner' );
if ( ! $pn_banner instanceof WP_Post ) {
	return;
}

$pn_id     = (int) $pn_banner->ID;
$pn_img_d  = (int) get_post_meta( $pn_id, '_pn_banner_image_desktop_id', true );
$pn_img_m  = (int) get_post_meta( $pn_id, '_pn_banner_image_mobile_id', true );

if ( $pn_img_d <= 0 ) {
	return;
}

$pn_alt        = (string) get_post_meta( $pn_id, '_pn_banner_alt', true );
if ( '' === $pn_alt ) {
	$pn_alt = get_the_title( $pn_id );
}
$pn_link       = pn_banner_resolve_link( $pn_id );
$pn_new_tab    = '1' === get_post_meta( $pn_id, '_pn_banner_link_new_tab', true );
$pn_full_width = '1' === get_post_meta( $pn_id, '_pn_banner_full_width', true );

$pn_url_d = wp_get_attachment_image_url( $pn_img_d, 'full' );
if ( ! $pn_url_d ) {
	return;
}
$pn_url_m = $pn_img_m ? wp_get_attachment_image_url( $pn_img_m, 'large' ) : '';

$pn_meta_d  = wp_get_attachment_metadata( $pn_img_d );
$pn_w       = isset( $pn_meta_d['width'] ) ? (int) $pn_meta_d['width'] : 1920;
$pn_h       = isset( $pn_meta_d['height'] ) ? (int) $pn_meta_d['height'] : 540;

$pn_wrapper_class = $pn_full_width
	? 'pn-banner pn-banner--full w-full'
	: 'pn-banner container max-w-7xl mx-auto px-4';

$pn_inner_class = 'block overflow-hidden rounded-lg';
if ( $pn_full_width ) {
	$pn_inner_class = 'block overflow-hidden';
}

$pn_img_html = sprintf(
	'<img src="%1$s" alt="%2$s" width="%3$d" height="%4$d" loading="lazy" decoding="async" class="block w-full h-auto">',
	esc_url( $pn_url_d ),
	esc_attr( $pn_alt ),
	$pn_w,
	$pn_h
);

if ( $pn_url_m ) {
	$pn_img_html = sprintf(
		'<picture>
			<source media="(max-width: 767px)" srcset="%1$s">
			<source media="(min-width: 768px)" srcset="%2$s">
			%3$s
		</picture>',
		esc_url( $pn_url_m ),
		esc_url( $pn_url_d ),
		$pn_img_html
	);
}
?>

<div class="<?php echo esc_attr( $pn_wrapper_class ); ?> my-6 md:my-8">
	<?php if ( $pn_link ) : ?>
		<a
			href="<?php echo esc_url( $pn_link ); ?>"
			class="<?php echo esc_attr( $pn_inner_class ); ?>"
			<?php if ( $pn_new_tab ) : ?>target="_blank" rel="noopener"<?php endif; ?>
			data-pn-banner-id="<?php echo (int) $pn_id; ?>"
		>
			<?php echo $pn_img_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — escaped above ?>
		</a>
	<?php else : ?>
		<div class="<?php echo esc_attr( $pn_inner_class ); ?>" data-pn-banner-id="<?php echo (int) $pn_id; ?>">
			<?php echo $pn_img_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	<?php endif; ?>
</div>
