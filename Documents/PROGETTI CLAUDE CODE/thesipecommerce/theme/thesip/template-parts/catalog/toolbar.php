<?php
/**
 * Toolbar catalogo: trigger filtri mobile + sort dropdown.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="mb-4 flex items-center justify-between gap-3">
	<button
		type="button"
		x-data
		@click="$dispatch('open-filters')"
		class="md:hidden inline-flex items-center gap-2 h-9 px-3 rounded-md border border-pharma-teal/30 text-sm font-medium text-pharma-teal-dark hover:bg-pharma-teal/5 transition-colors"
	>
		<?php pn_icon( 'sliders-horizontal', array( 'class' => 'h-4 w-4' ) ); ?>
		<?php esc_html_e( 'Filtri', 'pharmanow' ); ?>
	</button>

	<div class="ml-auto">
		<?php woocommerce_catalog_ordering(); ?>
	</div>
</div>
