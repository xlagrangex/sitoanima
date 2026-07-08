<?php
/**
 * Sidebar filtri catalogo (replica FilterSidebar.tsx).
 *
 * Tutti i filtri sono GET-based: <form method="get"> include hidden con orderby corrente
 * e (se taxonomy) si appoggia all'URL della pagina. Submit on change via Alpine watcher
 * + bottone "Applica" per fallback no-JS.
 *
 * Query vars consumati da inc/catalog/filters.php:
 *   brand[], min_price, max_price, glutine, lattosio, instock, promo, iva[], orderby
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Stato corrente da $_GET.
$pn_min_price   = isset( $_GET['min_price'] ) ? (string) absint( $_GET['min_price'] ) : '';
$pn_max_price   = isset( $_GET['max_price'] ) ? (string) absint( $_GET['max_price'] ) : '';
$pn_glutine     = ! empty( $_GET['glutine'] );
$pn_lattosio    = ! empty( $_GET['lattosio'] );
$pn_instock     = ! empty( $_GET['instock'] );
$pn_promo       = ! empty( $_GET['promo'] );
$pn_iva_sel     = isset( $_GET['iva'] ) && is_array( $_GET['iva'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_GET['iva'] ) ) : array();
$pn_orderby     = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : '';

$pn_has_active  = '' !== $pn_min_price || '' !== $pn_max_price || $pn_glutine || $pn_lattosio || $pn_instock || $pn_promo || ! empty( $pn_iva_sel );

// URL base senza filtri (per "Resetta").
$pn_base_url = strtok( esc_url_raw( ( is_ssl() ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ), '?' );
?>
<aside class="space-y-6">
	<form method="get" action="" id="pn-catalog-filters" class="space-y-6">
		<?php if ( $pn_orderby ) : ?>
			<input type="hidden" name="orderby" value="<?php echo esc_attr( $pn_orderby ); ?>">
		<?php endif; ?>

		<div class="flex items-center justify-between">
			<h2 class="text-lg font-semibold text-gray-900"><?php esc_html_e( 'Filtri', 'pharmanow' ); ?></h2>
			<?php if ( $pn_has_active ) : ?>
				<a href="<?php echo esc_url( $pn_base_url ); ?>" class="h-8 inline-flex items-center px-2 text-xs font-medium text-gray-500 hover:text-pharma-teal-dark transition-colors">
					<?php esc_html_e( 'Resetta', 'pharmanow' ); ?>
				</a>
			<?php endif; ?>
		</div>

		<div class="h-px bg-gray-200"></div>

		<?php /* ─── Brand picker rimosso (filtro non funzionante, da reintegrare) ─── */ ?>

		<?php /* ─── Prezzo ─── */ ?>
		<div class="space-y-3">
			<h3 class="text-sm font-medium text-gray-900"><?php esc_html_e( 'Prezzo (€)', 'pharmanow' ); ?></h3>
			<div class="grid grid-cols-2 gap-2">
				<div>
					<label for="pn-price-min" class="block text-xs text-gray-500 mb-1"><?php esc_html_e( 'Min', 'pharmanow' ); ?></label>
					<input
						id="pn-price-min"
						type="number"
						min="0"
						name="min_price"
						placeholder="0"
						value="<?php echo esc_attr( $pn_min_price ); ?>"
						class="w-full h-9 px-2 text-sm rounded-md border border-gray-200 bg-white focus:outline-none focus:ring-2 focus:ring-pharma-teal/30 focus:border-pharma-teal transition-colors"
					>
				</div>
				<div>
					<label for="pn-price-max" class="block text-xs text-gray-500 mb-1"><?php esc_html_e( 'Max', 'pharmanow' ); ?></label>
					<input
						id="pn-price-max"
						type="number"
						min="0"
						name="max_price"
						placeholder="∞"
						value="<?php echo esc_attr( $pn_max_price ); ?>"
						class="w-full h-9 px-2 text-sm rounded-md border border-gray-200 bg-white focus:outline-none focus:ring-2 focus:ring-pharma-teal/30 focus:border-pharma-teal transition-colors"
					>
				</div>
			</div>
		</div>

		<div class="h-px bg-gray-200"></div>

		<?php /* ─── Caratteristiche ─── */ ?>
		<div class="space-y-3">
			<h3 class="text-sm font-medium text-gray-900"><?php esc_html_e( 'Caratteristiche', 'pharmanow' ); ?></h3>
			<div class="space-y-2">
				<?php
				$pn_toggles = array(
					array( 'name' => 'instock',  'label' => __( 'Solo disponibili', 'pharmanow' ), 'checked' => $pn_instock ),
					array( 'name' => 'promo',    'label' => __( 'In promozione', 'pharmanow' ), 'checked' => $pn_promo ),
				);
				foreach ( $pn_toggles as $pn_t ) : ?>
					<label class="flex cursor-pointer items-center gap-2 text-sm text-gray-700 select-none">
						<input
							type="checkbox"
							name="<?php echo esc_attr( $pn_t['name'] ); ?>"
							value="1"
							<?php checked( $pn_t['checked'] ); ?>
							onchange="this.form.requestSubmit()"
							class="h-4 w-4 rounded border-gray-300 text-pharma-teal focus:ring-pharma-teal/30"
						>
						<span><?php echo esc_html( $pn_t['label'] ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="h-px bg-gray-200"></div>

		<?php /* ─── IVA ─── */ ?>
		<div class="space-y-3">
			<h3 class="text-sm font-medium text-gray-900"><?php esc_html_e( 'IVA', 'pharmanow' ); ?></h3>
			<div class="space-y-2">
				<?php foreach ( array( '4', '10', '22' ) as $pn_iva ) : ?>
					<label class="flex cursor-pointer items-center gap-2 text-sm text-gray-700 select-none">
						<input
							type="checkbox"
							name="iva[]"
							value="<?php echo esc_attr( $pn_iva ); ?>"
							<?php checked( in_array( $pn_iva, $pn_iva_sel, true ) ); ?>
							onchange="this.form.requestSubmit()"
							class="h-4 w-4 rounded border-gray-300 text-pharma-teal focus:ring-pharma-teal/30"
						>
						<span><?php echo esc_html( $pn_iva ); ?>%</span>
					</label>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Fallback no-JS: bottone Applica -->
		<noscript>
			<button type="submit" class="w-full h-10 rounded-md bg-pharma-teal text-white text-sm font-semibold hover:bg-pharma-teal-dark transition-colors">
				<?php esc_html_e( 'Applica filtri', 'pharmanow' ); ?>
			</button>
		</noscript>
	</form>
</aside>
