<?php
/**
 * Promo banners home — replica della sezione "carousel banner promo" del live pharmanow.com.
 *
 * 3 card brand-led (default: La Roche-Posay, Bioscalin, Bayer) con sfondo colorato,
 * eyebrow, titolo, sottotitolo e CTA. I dati sono filtrabili via `pn_home_promo_banners`
 * per permettere override (immagini reali, link a landing, riordino).
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn_shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalogo' );

$pn_default_banners = array(
	array(
		'eyebrow'    => __( 'Il rapporto uomo-mare', 'pharmanow' ),
		'title'      => __( 'Interazioni', 'pharmanow' ),
		'subtitle'   => __( 'Come le nostre vite si intrecciano con quelle del mare.', 'pharmanow' ),
		'cta'        => __( 'Scopri il tema', 'pharmanow' ),
		'url'        => $pn_shop_url,
		'image'      => pn_asset( 'images/thesip/tema-interazioni.jpg' ),
		'gradient'   => 'linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%)',
		'text_color' => 'text-white',
	),
	array(
		'eyebrow'    => __( 'Gli ecosistemi marini', 'pharmanow' ),
		'title'      => __( 'Ambienti', 'pharmanow' ),
		'subtitle'   => __( 'Un viaggio tra gli habitat che compongono il mare.', 'pharmanow' ),
		'cta'        => __( 'Scopri il tema', 'pharmanow' ),
		'url'        => $pn_shop_url,
		'image'      => pn_asset( 'images/thesip/tema-ambienti.jpg' ),
		'gradient'   => 'linear-gradient(135deg, #14b8a6 0%, #0f766e 100%)',
		'text_color' => 'text-white',
	),
	array(
		'eyebrow'    => __( 'Strategie di sopravvivenza', 'pharmanow' ),
		'title'      => __( 'Adattamenti', 'pharmanow' ),
		'subtitle'   => __( 'Le sorprendenti soluzioni delle creature marine.', 'pharmanow' ),
		'cta'        => __( 'Scopri il tema', 'pharmanow' ),
		'url'        => $pn_shop_url,
		'image'      => pn_asset( 'images/thesip/tema-adattamenti.jpg' ),
		'gradient'   => 'linear-gradient(135deg, #6366f1 0%, #3730a3 100%)',
		'text_color' => 'text-white',
	),
);

/**
 * Filtra i banner promo della home.
 * Ogni elemento deve avere: eyebrow, title, subtitle, cta, url, image (opt), gradient (opt), text_color (opt).
 *
 * @param array $banners
 */
$pn_banners = apply_filters( 'pn_home_promo_banners', $pn_default_banners );

if ( empty( $pn_banners ) ) {
	return;
}
?>

<section class="container max-w-7xl mx-auto px-4 py-10">
	<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
		<?php foreach ( $pn_banners as $pn_b ) :
			$pn_image    = isset( $pn_b['image'] ) ? (string) $pn_b['image'] : '';
			$pn_grad     = isset( $pn_b['gradient'] ) ? (string) $pn_b['gradient'] : 'linear-gradient(135deg, #0b8894 0%, #065a62 100%)';
			$pn_text     = isset( $pn_b['text_color'] ) ? (string) $pn_b['text_color'] : 'text-white';
			$pn_style    = $pn_image
				? 'background-image: linear-gradient(135deg, rgba(0,0,0,0.55), rgba(0,0,0,0.2)), url(' . esc_url( $pn_image ) . '); background-size: cover; background-position: center;'
				: 'background-image: ' . esc_attr( $pn_grad ) . ';';
			?>
			<a
				href="<?php echo esc_url( $pn_b['url'] ); ?>"
				class="group relative isolate overflow-hidden rounded-2xl p-6 md:p-8 min-h-[220px] flex flex-col justify-between <?php echo esc_attr( $pn_text ); ?> transition-transform duration-300 hover:-translate-y-1"
				style="<?php echo $pn_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
			>
				<span aria-hidden="true" class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-2xl transition-opacity group-hover:opacity-80"></span>

				<div class="relative">
					<p class="text-xs font-semibold uppercase tracking-wide opacity-80">
						<?php echo esc_html( $pn_b['eyebrow'] ); ?>
					</p>
					<h3 class="mt-2 text-2xl md:text-3xl font-bold leading-tight">
						<?php echo esc_html( $pn_b['title'] ); ?>
					</h3>
					<p class="mt-2 text-sm md:text-base opacity-90 max-w-xs">
						<?php echo esc_html( $pn_b['subtitle'] ); ?>
					</p>
				</div>

				<span class="relative inline-flex items-center gap-2 text-sm font-semibold mt-6">
					<?php echo esc_html( $pn_b['cta'] ); ?>
					<span class="transition-transform duration-300 group-hover:translate-x-1">
						<?php pn_icon( 'chevron-right', array( 'class' => 'h-4 w-4' ) ); ?>
					</span>
				</span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
