<?php
/**
 * Paginazione catalogo (replica visiva del CatalogPage Next:
 * "Pagina X di Y" + Precedente/Successiva).
 *
 * @package Pharmanow
 *
 * @var int $total
 * @var int $current
 * @var int $base
 * @var array $format
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $total ) ) {
	$total = wc_get_loop_prop( 'total_pages' );
}
if ( ! isset( $current ) ) {
	$current = wc_get_loop_prop( 'current_page' );
}

if ( $total <= 1 ) {
	return;
}

$pn_prev_url = $current > 1 ? get_pagenum_link( $current - 1 ) : '';
$pn_next_url = $current < $total ? get_pagenum_link( $current + 1 ) : '';
?>
<nav class="woocommerce-pagination flex items-center justify-between border-t border-gray-200 pt-4" aria-label="<?php esc_attr_e( 'Paginazione', 'pharmanow' ); ?>">
	<span class="text-sm text-gray-500">
		<?php
		printf(
			/* translators: 1: pagina corrente, 2: totale pagine */
			esc_html__( 'Pagina %1$s di %2$s', 'pharmanow' ),
			'<strong class="text-gray-700">' . esc_html( number_format_i18n( $current ) ) . '</strong>',
			'<strong class="text-gray-700">' . esc_html( number_format_i18n( $total ) ) . '</strong>'
		);
		?>
	</span>
	<div class="flex items-center gap-2">
		<?php if ( $pn_prev_url ) : ?>
			<a
				href="<?php echo esc_url( $pn_prev_url ); ?>"
				class="inline-flex items-center gap-1 h-9 px-3 rounded-md border border-gray-200 bg-white text-sm font-medium text-gray-700 hover:border-pharma-teal/50 hover:text-pharma-teal-dark transition-colors"
			>
				<?php pn_icon( 'chevron-left', array( 'class' => 'h-4 w-4' ) ); ?>
				<?php esc_html_e( 'Precedente', 'pharmanow' ); ?>
			</a>
		<?php else : ?>
			<span class="inline-flex items-center gap-1 h-9 px-3 rounded-md border border-gray-200 bg-gray-50 text-sm font-medium text-gray-400 cursor-not-allowed">
				<?php pn_icon( 'chevron-left', array( 'class' => 'h-4 w-4' ) ); ?>
				<?php esc_html_e( 'Precedente', 'pharmanow' ); ?>
			</span>
		<?php endif; ?>

		<?php if ( $pn_next_url ) : ?>
			<a
				href="<?php echo esc_url( $pn_next_url ); ?>"
				class="inline-flex items-center gap-1 h-9 px-3 rounded-md border border-gray-200 bg-white text-sm font-medium text-gray-700 hover:border-pharma-teal/50 hover:text-pharma-teal-dark transition-colors"
			>
				<?php esc_html_e( 'Successiva', 'pharmanow' ); ?>
				<?php pn_icon( 'chevron-right', array( 'class' => 'h-4 w-4' ) ); ?>
			</a>
		<?php else : ?>
			<span class="inline-flex items-center gap-1 h-9 px-3 rounded-md border border-gray-200 bg-gray-50 text-sm font-medium text-gray-400 cursor-not-allowed">
				<?php esc_html_e( 'Successiva', 'pharmanow' ); ?>
				<?php pn_icon( 'chevron-right', array( 'class' => 'h-4 w-4' ) ); ?>
			</span>
		<?php endif; ?>
	</div>
</nav>
