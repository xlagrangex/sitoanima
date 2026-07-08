<?php
/**
 * Empty State — replica components/shop/catalog/EmptyState.tsx.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// URL senza filtri (mantiene path).
$pn_reset_url = strtok( esc_url_raw( ( is_ssl() ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ), '?' );
?>
<div class="flex flex-col items-center justify-center gap-4 rounded-lg border border-dashed border-gray-300 py-16 text-center">
	<span class="text-gray-400">
		<?php pn_icon( 'package-open', array( 'class' => 'h-12 w-12' ) ); ?>
	</span>
	<div>
		<h3 class="text-lg font-semibold text-gray-900"><?php esc_html_e( 'Nessun prodotto trovato', 'pharmanow' ); ?></h3>
		<p class="mt-1 text-sm text-gray-500"><?php esc_html_e( 'Prova a modificare o resettare i filtri attivi.', 'pharmanow' ); ?></p>
	</div>
	<a
		href="<?php echo esc_url( $pn_reset_url ); ?>"
		class="inline-flex items-center justify-center h-10 px-4 rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:border-pharma-teal/50 hover:text-pharma-teal-dark transition-colors"
	>
		<?php esc_html_e( 'Resetta filtri', 'pharmanow' ); ?>
	</a>
</div>
