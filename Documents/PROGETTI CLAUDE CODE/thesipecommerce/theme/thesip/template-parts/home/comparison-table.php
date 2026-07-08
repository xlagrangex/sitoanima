<?php
/**
 * Tabella comparativa "Perché scegliere Pharmanow?" — replica del live pharmanow.com.
 *
 * 3 colonne: Pharmanow / Farmacia fisica / Altri e-commerce.
 * Mobile: ogni feature diventa una card con badge per ogni colonna.
 * Desktop: tabella tradizionale con icone check/x.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn_rows = array(
	array(
		'label'   => __( '25 illustratori diversi', 'pharmanow' ),
		'pn'      => array( 'icon' => 'check', 'text' => __( 'Arte originale', 'pharmanow' ),     'state' => 'yes' ),
		'fisica'  => array( 'icon' => 'x',     'text' => __( 'Grafica stock', 'pharmanow' ),      'state' => 'no' ),
		'altri'   => array( 'icon' => 'x',     'text' => __( 'Poche illustrazioni', 'pharmanow' ),'state' => 'partial' ),
	),
	array(
		'label'   => __( 'Testi curati da biologi marini', 'pharmanow' ),
		'pn'      => array( 'icon' => 'check', 'text' => __( 'Rigore scientifico', 'pharmanow' ), 'state' => 'yes' ),
		'fisica'  => array( 'icon' => 'x',     'text' => __( 'Non verificati', 'pharmanow' ),     'state' => 'no' ),
		'altri'   => array( 'icon' => 'x',     'text' => __( 'Generici', 'pharmanow' ),           'state' => 'no' ),
	),
	array(
		'label'   => __( 'Progetto indipendente napoletano', 'pharmanow' ),
		'pn'      => array( 'icon' => 'check', 'text' => __( 'Nato a Napoli', 'pharmanow' ),      'state' => 'yes' ),
		'fisica'  => array( 'icon' => 'x',     'text' => __( 'Produzione di massa', 'pharmanow' ),'state' => 'no' ),
		'altri'   => array( 'icon' => 'x',     'text' => __( 'Marchi anonimi', 'pharmanow' ),     'state' => 'no' ),
	),
	array(
		'label'   => __( 'Carta preistorica speciale', 'pharmanow' ),
		'pn'      => array( 'icon' => 'check', 'text' => __( 'Firmata Zoosparkle', 'pharmanow' ), 'state' => 'yes' ),
		'fisica'  => array( 'icon' => 'x',     'text' => __( 'Assente', 'pharmanow' ),            'state' => 'no' ),
		'altri'   => array( 'icon' => 'x',     'text' => __( 'Assente', 'pharmanow' ),            'state' => 'no' ),
	),
	array(
		'label'   => __( 'Disponibili in italiano e inglese', 'pharmanow' ),
		'pn'      => array( 'icon' => 'check', 'text' => __( 'Doppia lingua', 'pharmanow' ),      'state' => 'yes' ),
		'fisica'  => array( 'icon' => 'x',     'text' => __( 'Una sola lingua', 'pharmanow' ),    'state' => 'no' ),
		'altri'   => array( 'icon' => 'check', 'text' => __( 'Variabile', 'pharmanow' ),          'state' => 'partial' ),
	),
);

$pn_cell_classes = function( string $state ): string {
	if ( 'yes' === $state ) {
		return 'text-emerald-600 bg-emerald-50';
	}
	if ( 'partial' === $state ) {
		return 'text-amber-600 bg-amber-50';
	}
	return 'text-rose-500 bg-rose-50';
};
?>

<section class="container max-w-7xl mx-auto px-4 py-12 md:py-16">
	<div class="text-center max-w-2xl mx-auto mb-10">
		<h2 class="text-2xl md:text-3xl font-bold tracking-tight">
			<?php esc_html_e( 'Perché', 'pharmanow' ); ?>
			<span class="pharma-text-grad"><?php esc_html_e( 'The SIP', 'pharmanow' ); ?></span>?
		</h2>
		<p class="mt-2 text-sm md:text-base text-muted-foreground">
			<?php esc_html_e( 'Quello che rende The SIP diverso dalle carte generiche: arte originale, rigore scientifico e un progetto indipendente nato a Napoli.', 'pharmanow' ); ?>
		</p>
	</div>

	<?php /* Desktop table ≥ md */ ?>
	<div class="hidden md:block overflow-hidden rounded-2xl border bg-card">
		<table class="w-full text-sm">
			<thead>
				<tr class="border-b bg-muted/40">
					<th class="text-left font-semibold p-4 w-[36%]">&nbsp;</th>
					<th class="p-4 text-center">
						<div class="inline-flex flex-col items-center gap-1">
							<span class="inline-flex items-center gap-1.5 rounded-full pharma-primary-bg text-white px-3 py-1 text-xs font-semibold">
								<?php pn_icon( 'sparkles', array( 'class' => 'h-3.5 w-3.5' ) ); ?>
								The SIP
							</span>
						</div>
					</th>
					<th class="p-4 text-center text-muted-foreground font-medium">
						<?php esc_html_e( 'Carte generiche', 'pharmanow' ); ?>
					</th>
					<th class="p-4 text-center text-muted-foreground font-medium">
						<?php esc_html_e( 'Altri set', 'pharmanow' ); ?>
					</th>
				</tr>
			</thead>
			<tbody class="divide-y">
				<?php foreach ( $pn_rows as $pn_r ) : ?>
					<tr class="hover:bg-muted/20 transition-colors">
						<td class="p-4 font-medium"><?php echo esc_html( $pn_r['label'] ); ?></td>
						<?php foreach ( array( 'pn', 'fisica', 'altri' ) as $pn_col ) :
							$pn_cell = $pn_r[ $pn_col ];
							?>
							<td class="p-4 text-center align-middle">
								<span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-medium <?php echo esc_attr( $pn_cell_classes( $pn_cell['state'] ) ); ?>">
									<?php pn_icon( $pn_cell['icon'], array( 'class' => 'h-3.5 w-3.5' ) ); ?>
									<?php echo esc_html( $pn_cell['text'] ); ?>
								</span>
							</td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<?php /* Mobile stacked cards */ ?>
	<div class="md:hidden space-y-4">
		<?php foreach ( $pn_rows as $pn_r ) : ?>
			<div class="rounded-xl border bg-card p-4">
				<p class="text-sm font-semibold mb-3"><?php echo esc_html( $pn_r['label'] ); ?></p>
				<div class="grid grid-cols-3 gap-2 text-[11px]">
					<?php
					$pn_cols = array(
						'pn'     => 'The SIP',
						'fisica' => __( 'Generiche', 'pharmanow' ),
						'altri'  => __( 'Altri', 'pharmanow' ),
					);
					foreach ( $pn_cols as $pn_key => $pn_label ) :
						$pn_cell = $pn_r[ $pn_key ];
						?>
						<div class="flex flex-col items-center gap-1 text-center">
							<span class="text-[10px] uppercase tracking-wide text-muted-foreground"><?php echo esc_html( $pn_label ); ?></span>
							<span class="inline-flex items-center justify-center rounded-full p-1.5 <?php echo esc_attr( $pn_cell_classes( $pn_cell['state'] ) ); ?>">
								<?php pn_icon( $pn_cell['icon'], array( 'class' => 'h-3.5 w-3.5' ) ); ?>
							</span>
							<span class="leading-tight"><?php echo esc_html( $pn_cell['text'] ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
