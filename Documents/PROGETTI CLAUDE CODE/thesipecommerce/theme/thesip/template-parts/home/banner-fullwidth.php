<?php
/**
 * Banner full-width brand-led — replica dei banner promo del live pharmanow.com.
 *
 * Args:
 *   - eyebrow       (string)  Testo piccolo sopra il titolo. Opzionale.
 *   - title         (string)  Brand o titolo banner. Required.
 *   - subtitle      (string)  Tagline sotto il titolo. Opzionale.
 *   - cta_text      (string)  Etichetta CTA (default "Scopri").
 *   - url           (string)  Link click area + CTA. Required.
 *   - image         (string)  URL immagine desktop. Opzionale (fallback gradient).
 *   - image_mobile  (string)  URL immagine mobile (≤ 767px). Opzionale (fallback a image).
 *   - hide_text     (bool)    Se true e c'è image, nasconde tutto il testo overlay. Utile per banner "puri" che hanno già il testo nell'immagine.
 *   - gradient      (string)  CSS background quando manca image. Default teal.
 *   - size          (string)  'md' (default) | 'lg' (banner hero come Bayer)
 *   - align         (string)  'left' (default) | 'center' | 'right' — posizione del blocco testo
 *
 * Tutto è filtrabile — vedi promo-banners.php per pattern alternativi.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn_eyebrow      = isset( $args['eyebrow'] ) ? (string) $args['eyebrow'] : '';
$pn_title        = isset( $args['title'] ) ? (string) $args['title'] : '';
$pn_subtitle     = isset( $args['subtitle'] ) ? (string) $args['subtitle'] : '';
$pn_cta          = isset( $args['cta_text'] ) ? (string) $args['cta_text'] : __( 'Scopri', 'pharmanow' );
$pn_url          = isset( $args['url'] ) ? (string) $args['url'] : '';
$pn_image        = isset( $args['image'] ) ? (string) $args['image'] : '';
$pn_image_mobile = isset( $args['image_mobile'] ) ? (string) $args['image_mobile'] : '';
$pn_hide_text    = ! empty( $args['hide_text'] );
$pn_grad         = isset( $args['gradient'] ) ? (string) $args['gradient'] : 'linear-gradient(135deg, #0b8894 0%, #065a62 100%)';
$pn_size         = isset( $args['size'] ) && in_array( $args['size'], array( 'md', 'lg' ), true ) ? $args['size'] : 'md';
$pn_align        = isset( $args['align'] ) && in_array( $args['align'], array( 'left', 'center', 'right' ), true ) ? $args['align'] : 'left';

if ( ! $pn_title || ! $pn_url ) {
	return;
}

// Altezze allineate al live pharmanow.com (md: 280/420, lg leggermente più ariosa).
$pn_height = 'lg' === $pn_size
	? 'min-h-[300px] md:min-h-[440px]'
	: 'min-h-[280px] md:min-h-[420px]';

$pn_text_size = 'lg' === $pn_size
	? 'text-3xl md:text-5xl'
	: 'text-2xl md:text-4xl';

$pn_text_align = 'center' === $pn_align ? 'text-center items-center' : ( 'right' === $pn_align ? 'text-right items-end' : 'text-left items-start' );
$pn_block_pos  = 'center' === $pn_align ? 'mx-auto' : ( 'right' === $pn_align ? 'ml-auto' : '' );
$pn_has_image  = '' !== $pn_image;
$pn_show_text  = ! ( $pn_has_image && $pn_hide_text );

$pn_bg_style = $pn_has_image
	? ''
	: 'background-image: ' . esc_attr( $pn_grad ) . ';';
?>

<section class="container max-w-7xl mx-auto px-4 py-6">
	<a
		href="<?php echo esc_url( $pn_url ); ?>"
		class="group relative block isolate overflow-hidden rounded-2xl text-white shadow-sm transition-transform duration-300 hover:-translate-y-0.5"
		<?php if ( $pn_has_image ) : ?>aria-label="<?php echo esc_attr( $pn_title ); ?>"<?php endif; ?>
	>
		<?php if ( $pn_has_image ) : ?>
			<picture class="absolute inset-0 -z-20 block">
				<?php if ( $pn_image_mobile ) : ?>
					<source media="(max-width: 767px)" srcset="<?php echo esc_url( $pn_image_mobile ); ?>">
				<?php endif; ?>
				<img
					src="<?php echo esc_url( $pn_image ); ?>"
					alt=""
					loading="lazy"
					decoding="async"
					class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.02]"
				>
			</picture>
			<?php if ( $pn_show_text ) : ?>
				<span aria-hidden="true" class="absolute inset-0 -z-10 bg-gradient-to-r from-black/55 via-black/25 to-transparent"></span>
			<?php endif; ?>
		<?php endif; ?>

		<div
			class="<?php echo esc_attr( $pn_height ); ?> w-full px-6 py-8 md:px-12 md:py-12 flex flex-col justify-center <?php echo esc_attr( $pn_text_align ); ?>"
			<?php if ( $pn_bg_style ) : ?>style="<?php echo $pn_bg_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"<?php endif; ?>
		>
			<?php if ( $pn_show_text ) : ?>
				<?php if ( ! $pn_has_image ) : ?>
					<span aria-hidden="true" class="absolute -right-10 -top-10 h-44 w-44 rounded-full bg-white/10 blur-3xl transition-opacity group-hover:opacity-80"></span>
				<?php endif; ?>

				<div class="relative max-w-xl <?php echo esc_attr( $pn_block_pos ); ?>">
					<?php if ( $pn_eyebrow ) : ?>
						<p class="text-xs md:text-sm font-semibold uppercase tracking-wide opacity-85 mb-2 drop-shadow-sm">
							<?php echo esc_html( $pn_eyebrow ); ?>
						</p>
					<?php endif; ?>

					<h2 class="<?php echo esc_attr( $pn_text_size ); ?> font-extrabold tracking-tight leading-tight drop-shadow-sm">
						<?php echo esc_html( $pn_title ); ?>
					</h2>

					<?php if ( $pn_subtitle ) : ?>
						<p class="mt-3 text-sm md:text-lg opacity-95 drop-shadow-sm">
							<?php echo esc_html( $pn_subtitle ); ?>
						</p>
					<?php endif; ?>

					<span class="mt-5 inline-flex items-center gap-2 text-sm font-semibold rounded-full bg-white/15 backdrop-blur-sm px-5 py-2.5 transition-colors group-hover:bg-white group-hover:text-gray-900">
						<?php echo esc_html( $pn_cta ); ?>
						<span class="transition-transform duration-300 group-hover:translate-x-1">
							<?php pn_icon( 'arrow-right', array( 'class' => 'h-4 w-4' ) ); ?>
						</span>
					</span>
				</div>
			<?php endif; ?>
		</div>
	</a>
</section>
