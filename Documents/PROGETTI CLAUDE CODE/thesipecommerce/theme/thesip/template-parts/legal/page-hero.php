<?php
/**
 * Hero condiviso per pagine statiche/legali — porting da componenti Next che usano
 * il pattern bg-gradient + breadcrumb + titolo + sottotitolo opzionale.
 *
 * Variabili attese (passabili via set_query_var oppure get_template_part('...', $args)):
 *   - $args['title']            string  Titolo H1
 *   - $args['subtitle']         string  (opzionale) sottotitolo sotto H1
 *   - $args['breadcrumb_label'] string  Etichetta corrente nel breadcrumb
 *   - $args['size']             'sm'|'md'|'lg' — default 'md'
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn_title    = isset( $args['title'] ) ? (string) $args['title'] : get_the_title();
$pn_subtitle = isset( $args['subtitle'] ) ? (string) $args['subtitle'] : '';
$pn_crumb    = isset( $args['breadcrumb_label'] ) ? (string) $args['breadcrumb_label'] : $pn_title;
$pn_size     = isset( $args['size'] ) ? (string) $args['size'] : 'md';

$pn_pad = array(
	'sm' => 'py-8 sm:py-10',
	'md' => 'py-10 sm:py-12',
	'lg' => 'py-12 sm:py-16 lg:py-20',
);
$pn_pad_class = $pn_pad[ $pn_size ] ?? $pn_pad['md'];

$pn_h1 = ( 'lg' === $pn_size )
	? 'text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-4'
	: 'text-2xl sm:text-3xl font-bold text-white';
?>

<div class="bg-gradient-to-r from-[#0891B2] to-[#67E8F9] <?php echo esc_attr( $pn_pad_class ); ?>">
	<div class="max-w-[1280px] mx-auto px-4">
		<nav class="flex items-center gap-2 text-sm mb-3" aria-label="<?php esc_attr_e( 'Breadcrumb', 'pharmanow' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="text-white/70 hover:text-white transition-colors">
				<?php esc_html_e( 'Home', 'pharmanow' ); ?>
			</a>
			<?php pn_icon( 'chevron-right', array( 'class' => 'w-4 h-4 text-white/50' ) ); ?>
			<span class="text-white"><?php echo esc_html( $pn_crumb ); ?></span>
		</nav>
		<h1 class="<?php echo esc_attr( $pn_h1 ); ?>"><?php echo esc_html( $pn_title ); ?></h1>
		<?php if ( $pn_subtitle ) : ?>
			<p class="text-white/80 mt-2"><?php echo esc_html( $pn_subtitle ); ?></p>
		<?php endif; ?>
	</div>
</div>
