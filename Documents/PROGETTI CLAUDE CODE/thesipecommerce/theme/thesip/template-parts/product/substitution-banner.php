<?php
/**
 * Banner blu "Questo prodotto è stato sostituito".
 * Replica `SubstitutionBanner.tsx`. Mostrato solo se daysSince < 30.
 *
 * @var array $args { new_product_id: int, days_since: int }
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

$pn_new_id = (int) ( $args['new_product_id'] ?? 0 );
$pn_days   = (int) ( $args['days_since'] ?? 0 );
if ( ! $pn_new_id ) {
	return;
}
$pn_new = wc_get_product( $pn_new_id );
if ( ! $pn_new ) {
	return;
}
$pn_url   = get_permalink( $pn_new_id );
$pn_title = $pn_new->get_name();
?>
<a href="<?php echo esc_url( $pn_url ); ?>" class="mb-6 flex items-start gap-3 rounded-lg border border-blue-200 bg-blue-50 p-4 text-blue-900 transition-colors hover:bg-blue-100">
	<span class="mt-0.5 shrink-0 text-blue-700"><?php pn_icon( 'info', array( 'class' => 'h-5 w-5' ) ); ?></span>
	<div class="flex-1 text-sm">
		<p class="font-semibold"><?php esc_html_e( 'Questo prodotto è stato sostituito', 'pharmanow' ); ?></p>
		<p class="mt-0.5 text-blue-800">
			<?php esc_html_e( 'La nuova versione è', 'pharmanow' ); ?>
			<span class="font-medium underline"><?php echo esc_html( $pn_title ); ?></span>.
			<?php
			/* translators: %d: number of days */
			printf( esc_html__( 'Sostituzione effettuata %d giorni fa.', 'pharmanow' ), (int) $pn_days );
			?>
		</p>
	</div>
	<span class="mt-0.5 shrink-0 text-blue-700"><?php pn_icon( 'arrow-right', array( 'class' => 'h-5 w-5' ) ); ?></span>
</a>
