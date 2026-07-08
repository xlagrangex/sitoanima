<?php
/**
 * Trust bar home — porting da components/shop/home/trust-bar.tsx (Next).
 *
 * 5 features con icona in cerchio gradient + title + desc.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn_features = array(
	array( 'icon' => 'truck',        'title' => __( 'Spedizione Gratuita', 'pharmanow' ),   'desc' => __( 'Per ordini sopra €30', 'pharmanow' ) ),
	array( 'icon' => 'clock',        'title' => __( 'Consegna Rapida', 'pharmanow' ),       'desc' => __( 'In 24/48h', 'pharmanow' ) ),
	array( 'icon' => 'map-pin',      'title' => __( 'Consegna a Napoli', 'pharmanow' ),     'desc' => __( 'A mano e gratuita', 'pharmanow' ) ),
	array( 'icon' => 'sparkles',     'title' => __( 'Arte Originale', 'pharmanow' ),        'desc' => __( '25 illustratori', 'pharmanow' ) ),
	array( 'icon' => 'credit-card',  'title' => __( 'Pagamento Sicuro', 'pharmanow' ),      'desc' => __( 'Transazioni crittografate', 'pharmanow' ) ),
);
?>

<section class="bg-muted/30 border-y">
	<div class="container max-w-7xl mx-auto px-4 py-8">
		<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-6">
			<?php foreach ( $pn_features as $pn_f ) : ?>
				<div class="flex items-start gap-3">
					<div class="rounded-full pharma-primary-bg p-2.5 shrink-0">
						<?php pn_icon( $pn_f['icon'], array( 'class' => 'h-5 w-5 text-white' ) ); ?>
					</div>
					<div>
						<p class="text-sm font-semibold leading-tight"><?php echo esc_html( $pn_f['title'] ); ?></p>
						<p class="text-xs text-muted-foreground mt-0.5"><?php echo esc_html( $pn_f['desc'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
