<?php
/**
 * Sort Dropdown — replica components/shop/catalog/SortDropdown.tsx.
 *
 * @package Pharmanow
 *
 * @var array  $catalog_orderby_options Opzioni orderby.
 * @var string $orderby                 Valore corrente.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn_options = isset( $catalog_orderby_options ) && is_array( $catalog_orderby_options ) ? $catalog_orderby_options : array();
$pn_current = isset( $orderby ) ? (string) $orderby : 'menu_order';

// Etichette IT allineate al Next.
$pn_label_map = array(
	'menu_order'  => __( 'Rilevanza', 'pharmanow' ),
	'popularity'  => __( 'Più venduti', 'pharmanow' ),
	'price'       => __( 'Prezzo crescente', 'pharmanow' ),
	'price-desc'  => __( 'Prezzo decrescente', 'pharmanow' ),
	'newest'      => __( 'Novità', 'pharmanow' ),
	'discount'    => __( 'Sconto %', 'pharmanow' ),
);

// Override label.
foreach ( $pn_options as $pn_k => $pn_v ) {
	if ( isset( $pn_label_map[ $pn_k ] ) ) {
		$pn_options[ $pn_k ] = $pn_label_map[ $pn_k ];
	}
}

$pn_current_label = isset( $pn_options[ $pn_current ] ) ? $pn_options[ $pn_current ] : ( $pn_options['menu_order'] ?? __( 'Ordina', 'pharmanow' ) );
?>
<form
	class="woocommerce-ordering"
	method="get"
	x-data="{ open: false }"
	@click.outside="open = false"
	@keydown.escape="open = false"
>
	<?php
	// Preserva i parametri di filtro durante il submit.
	foreach ( (array) $_GET as $pn_k => $pn_v ) {
		if ( 'orderby' === $pn_k || 'submit' === $pn_k || 'paged' === $pn_k ) {
			continue;
		}
		if ( is_array( $pn_v ) ) {
			foreach ( $pn_v as $pn_vi ) {
				printf( '<input type="hidden" name="%s" value="%s">', esc_attr( $pn_k . '[]' ), esc_attr( wp_unslash( $pn_vi ) ) );
			}
		} else {
			printf( '<input type="hidden" name="%s" value="%s">', esc_attr( $pn_k ), esc_attr( wp_unslash( $pn_v ) ) );
		}
	}
	?>

	<div class="relative">
		<button
			type="button"
			@click="open = !open"
			class="flex w-[200px] items-center justify-between gap-2 h-9 px-3 rounded-md border border-gray-200 bg-white text-sm text-gray-700 hover:border-pharma-teal/40 transition-colors focus:outline-none focus:ring-2 focus:ring-pharma-teal/30"
			:aria-expanded="open"
		>
			<span class="truncate"><?php echo esc_html( $pn_current_label ); ?></span>
			<span class="text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''">
				<?php pn_icon( 'chevron-down', array( 'class' => 'h-4 w-4' ) ); ?>
			</span>
		</button>

		<div
			x-show="open"
			x-cloak
			x-transition.opacity.duration.150ms
			class="absolute right-0 z-30 mt-1 w-[220px] overflow-hidden rounded-md border border-gray-200 bg-white shadow-lg"
		>
			<ul class="py-1">
				<?php foreach ( $pn_options as $pn_id => $pn_name ) :
					$pn_is_current = ( $pn_id === $pn_current );
					?>
					<li>
						<button
							type="submit"
							name="orderby"
							value="<?php echo esc_attr( $pn_id ); ?>"
							class="flex w-full items-center justify-between gap-2 px-3 py-2 text-left text-sm transition-colors <?php echo $pn_is_current ? 'bg-pharma-teal/10 text-pharma-teal font-medium' : 'text-gray-700 hover:bg-gray-50'; ?>"
						>
							<span><?php echo esc_html( $pn_name ); ?></span>
							<?php if ( $pn_is_current ) : ?>
								<?php pn_icon( 'check', array( 'class' => 'h-4 w-4 text-pharma-teal' ) ); ?>
							<?php endif; ?>
						</button>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</form>
