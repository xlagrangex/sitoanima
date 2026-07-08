<?php
/**
 * Tabs Descrizione/Bugiardino/Composizione/Avvertenze.
 * Desktop: tabs orizzontali. Mobile: accordion.
 *
 * Replica `ProductTabs.tsx` + `BugiardinoRenderer.tsx`.
 *
 * @var array $args { product: WC_Product, meta: array }
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

$pn_p    = $args['product'] ?? null;
$pn_meta = $args['meta'] ?? null;
if ( ! $pn_p instanceof WC_Product || ! is_array( $pn_meta ) ) {
	return;
}

$pn_short = (string) $pn_p->get_short_description();
$pn_long  = (string) $pn_p->get_description();

$pn_sections = array_values(
	array_filter(
		array(
			array(
				'key'     => 'descrizione',
				'label'   => __( 'Descrizione', 'pharmanow' ),
				'content' => '' !== $pn_meta['descrizione_estesa'] && null !== $pn_meta['descrizione_estesa'] ? $pn_meta['descrizione_estesa'] : ( '' !== $pn_short ? $pn_short : $pn_long ),
			),
			array(
				'key'     => 'bugiardino',
				'label'   => __( 'Bugiardino', 'pharmanow' ),
				'content' => $pn_meta['bugiardino'],
			),
			array(
				'key'     => 'composizione',
				'label'   => __( 'Composizione', 'pharmanow' ),
				'content' => $pn_meta['composizione'],
			),
			array(
				'key'     => 'avvertenze',
				'label'   => __( 'Avvertenze', 'pharmanow' ),
				'content' => $pn_meta['avvertenze'],
			),
		),
		function ( $s ) {
			return is_string( $s['content'] ) && '' !== trim( $s['content'] );
		}
	)
);

if ( empty( $pn_sections ) ) {
	return;
}
$pn_first = $pn_sections[0]['key'];
?>
<div class="pn-product-tabs">
	<!-- Desktop: tabs -->
	<div class="hidden md:block" x-data="{ tab: '<?php echo esc_attr( $pn_first ); ?>' }">
		<div class="flex w-full items-center gap-1 border-b" role="tablist">
			<?php foreach ( $pn_sections as $pn_s ) : ?>
				<button
					type="button"
					role="tab"
					@click="tab = '<?php echo esc_attr( $pn_s['key'] ); ?>'"
					:aria-selected="tab === '<?php echo esc_attr( $pn_s['key'] ); ?>'"
					:class="tab === '<?php echo esc_attr( $pn_s['key'] ); ?>' ? 'border-pharma-teal text-foreground' : 'border-transparent text-muted-foreground hover:text-foreground'"
					class="px-4 py-3 text-sm font-medium border-b-2 -mb-px transition-colors"
				>
					<?php echo esc_html( $pn_s['label'] ); ?>
				</button>
			<?php endforeach; ?>
		</div>
		<?php foreach ( $pn_sections as $pn_s ) : ?>
			<div x-show="tab === '<?php echo esc_attr( $pn_s['key'] ); ?>'" x-cloak class="pt-6">
				<div class="prose prose-sm max-w-none [&_table]:block [&_table]:overflow-x-auto [&_th]:bg-muted [&_th]:px-3 [&_th]:py-2 [&_th]:text-left [&_th]:font-semibold [&_td]:px-3 [&_td]:py-2 [&_td]:border-t">
					<?php echo pn_render_bugiardino( (string) $pn_s['content'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<!-- Mobile: accordion -->
	<div class="md:hidden" x-data="{ open: '<?php echo esc_attr( $pn_first ); ?>' }">
		<?php foreach ( $pn_sections as $pn_s ) : ?>
			<div class="border-b">
				<button
					type="button"
					@click="open = (open === '<?php echo esc_attr( $pn_s['key'] ); ?>' ? '' : '<?php echo esc_attr( $pn_s['key'] ); ?>')"
					class="flex w-full items-center justify-between py-4 text-left text-base font-medium"
				>
					<?php echo esc_html( $pn_s['label'] ); ?>
					<span :class="open === '<?php echo esc_attr( $pn_s['key'] ); ?>' ? 'rotate-180' : ''" class="transition-transform">
						<?php pn_icon( 'chevron-down', array( 'class' => 'h-4 w-4' ) ); ?>
					</span>
				</button>
				<div x-show="open === '<?php echo esc_attr( $pn_s['key'] ); ?>'" x-cloak class="pb-4">
					<div class="prose prose-sm max-w-none [&_table]:block [&_table]:overflow-x-auto [&_th]:bg-muted [&_th]:px-3 [&_th]:py-2 [&_th]:text-left [&_th]:font-semibold [&_td]:px-3 [&_td]:py-2 [&_td]:border-t">
						<?php echo pn_render_bugiardino( (string) $pn_s['content'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
