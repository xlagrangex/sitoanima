<?php
/**
 * Sezione "La tua farmacia di fiducia, online" — replica fedele del live pharmanow.com.
 *
 * Titolo + sottotitolo + 4 USP card con icona, h3, descrizione.
 * NON include CTA WhatsApp (gestito da pharmacist-cta.php standalone).
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn_cards = array(
	array(
		'icon'   => 'users',
		'title'  => __( '25 illustratori', 'pharmanow' ),
		'desc'   => __( 'Ogni carta è illustrata da un artista diverso: 25 stili, una sola passione per il mare.', 'pharmanow' ),
		'accent' => 'bg-pink-50 text-pink-600',
	),
	array(
		'icon'   => 'flask-conical',
		'title'  => __( '2 biologi marini', 'pharmanow' ),
		'desc'   => __( 'Manuel e Giuliano curano i testi: rigore scientifico raccontato in modo curioso e semplice.', 'pharmanow' ),
		'accent' => 'bg-cyan-50 text-cyan-600',
	),
	array(
		'icon'   => 'leaf',
		'title'  => __( '3 temi da esplorare', 'pharmanow' ),
		'desc'   => __( 'Interazioni, Ambienti e Adattamenti: dal rapporto uomo-mare alle strategie di sopravvivenza.', 'pharmanow' ),
		'accent' => 'bg-emerald-50 text-emerald-600',
	),
	array(
		'icon'   => 'star',
		'title'  => __( 'Una carta speciale', 'pharmanow' ),
		'desc'   => __( 'Una carta preistorica illustrata da Willy Guasti "Zoosparkle" completa il set da 31.', 'pharmanow' ),
		'accent' => 'bg-amber-50 text-amber-600',
	),
);
?>

<section class="bg-white py-12 md:py-16">
	<div class="container max-w-7xl mx-auto px-4">
		<div class="text-center max-w-2xl mx-auto mb-10">
			<h2 class="text-2xl md:text-3xl font-bold tracking-tight text-gray-900">
				<?php esc_html_e( 'Un progetto di arte e scienza', 'pharmanow' ); ?>
			</h2>
			<p class="mt-2 text-sm md:text-base text-muted-foreground">
				<?php esc_html_e( 'The SIP nasce a Napoli dall\'incontro tra illustratori e biologi marini. Sul retro di ogni carta c\'è Andrea, una bambina curiosa che ti racconta le meraviglie del mare e ti indica la profondità.', 'pharmanow' ); ?>
			</p>
		</div>

		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
			<?php foreach ( $pn_cards as $pn_c ) : ?>
				<div class="group rounded-2xl border bg-card p-6 text-center transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
					<div class="mx-auto mb-4 inline-flex h-14 w-14 items-center justify-center rounded-2xl <?php echo esc_attr( $pn_c['accent'] ); ?> transition-transform duration-300 group-hover:scale-110">
						<?php pn_icon( $pn_c['icon'], array( 'class' => 'h-7 w-7' ) ); ?>
					</div>
					<h3 class="font-semibold text-gray-900 text-sm md:text-base mb-2">
						<?php echo esc_html( $pn_c['title'] ); ?>
					</h3>
					<p class="text-xs md:text-sm text-muted-foreground leading-relaxed">
						<?php echo esc_html( $pn_c['desc'] ); ?>
					</p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
