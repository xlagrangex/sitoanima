<?php
/**
 * Sezione FAQ in home — replica del live pharmanow.com.
 *
 * Mostra 5 domande "top" estratte dal dataset principale (pn_get_faq_data),
 * con accordion Alpine.js. Link finale alla pagina FAQ completa.
 * Include JSON-LD FAQPage per SEO.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// FAQ home — 5 domande hardcoded per The SIP.
// Filtrabile via `pn_home_faq_items` se serve override altrove.
$pn_items = apply_filters(
	'pn_home_faq_items',
	array(
		array(
			'q' => __( 'Quanto costa la spedizione?', 'pharmanow' ),
			'a' => __( 'La spedizione è gratuita per ordini sopra €30. Sotto questa soglia si applica un piccolo contributo. Consegna in 24/48h.', 'pharmanow' ),
		),
		array(
			'q' => __( 'Le carte sono in italiano o in inglese?', 'pharmanow' ),
			'a' => __( 'Le carte sono disponibili sia in italiano sia in inglese. Puoi indicare la lingua che preferisci nelle note dell\'ordine al momento del checkout.', 'pharmanow' ),
		),
		array(
			'q' => __( 'Cosa contiene il set?', 'pharmanow' ),
			'a' => __( 'Il set completo comprende 31 carte: 30 flashcard di biologia marina illustrate da 25 artisti diversi, più 1 carta preistorica speciale illustrata da Willy Guasti "Zoosparkle". Le carte sono divise in 3 temi: Interazioni, Ambienti e Adattamenti.', 'pharmanow' ),
		),
		array(
			'q' => __( 'Posso ritirare le carte a mano a Napoli?', 'pharmanow' ),
			'a' => __( 'Sì! Se sei a Napoli puoi ricevere il tuo set con consegna a mano gratuita. Indicalo nelle note dell\'ordine e ti contatteremo per organizzare la consegna.', 'pharmanow' ),
		),
		array(
			'q' => __( 'Come funziona la carta personalizzata del Bundle Deluxe?', 'pharmanow' ),
			'a' => __( 'Il Bundle Deluxe include una carta personalizzata: dopo l\'acquisto ci metteremo in contatto con te per creare insieme una carta unica, illustrata su misura per te.', 'pharmanow' ),
		),
	)
);

if ( empty( $pn_items ) ) {
	return;
}

// JSON-LD per SEO (solo le domande mostrate in home).
$pn_jsonld = array(
	'@context'   => 'https://schema.org',
	'@type'      => 'FAQPage',
	'mainEntity' => array_map(
		static function ( $q ) {
			return array(
				'@type'          => 'Question',
				'name'           => $q['q'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $q['a'],
				),
			);
		},
		$pn_items
	),
);
?>

<section class="thesip-pattern-bg py-12 md:py-16">
	<script type="application/ld+json"><?php echo wp_json_encode( $pn_jsonld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>

	<div class="container max-w-4xl mx-auto px-4">
		<div class="text-center mb-10">
			<h2 class="text-2xl md:text-3xl font-bold tracking-tight">
				<?php esc_html_e( 'Domande Frequenti', 'pharmanow' ); ?>
			</h2>
			<p class="mt-2 text-sm md:text-base text-muted-foreground">
				<?php esc_html_e( 'Le risposte più richieste. Se non trovi quello che cerchi, dai un\'occhiata alla pagina FAQ completa.', 'pharmanow' ); ?>
			</p>
		</div>

		<div class="rounded-2xl bg-white shadow-sm divide-y divide-gray-100 overflow-hidden">
			<?php foreach ( $pn_items as $idx => $q ) : ?>
				<div x-data="{ open: <?php echo 0 === $idx ? 'true' : 'false'; ?> }">
					<button
						type="button"
						@click="open = !open"
						:aria-expanded="open"
						class="w-full flex items-center justify-between gap-4 px-5 md:px-6 py-4 md:py-5 text-left hover:bg-gray-50 transition-colors"
					>
						<span class="font-medium text-gray-900 text-sm md:text-base">
							<?php echo esc_html( $q['q'] ); ?>
						</span>
						<span :class="open ? 'rotate-180 text-pharma-teal' : 'text-gray-400'" class="flex-shrink-0 transition-transform">
							<?php pn_icon( 'chevron-down', array( 'class' => 'h-5 w-5' ) ); ?>
						</span>
					</button>
					<div x-show="open" x-cloak x-transition class="px-5 md:px-6 pb-5 pt-0 text-sm text-gray-600 leading-relaxed whitespace-pre-line">
						<?php echo esc_html( $q['a'] ); ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="mt-8 text-center">
			<a
				href="<?php echo esc_url( home_url( '/faq' ) ); ?>"
				data-slot="button"
				class="inline-flex items-center gap-2 h-11 px-6 rounded-xl bg-white border border-gray-200 text-pharma-teal font-semibold hover:bg-gray-50 transition-colors"
			>
				<?php
				echo pn_slide_text(
					'<span>' . esc_html__( 'Vedi tutte le FAQ', 'pharmanow' ) . '</span>' .
					pn_icon_string( 'arrow-right', array( 'class' => 'h-4 w-4' ) )
				);
				?>
			</a>
		</div>
	</div>
</section>
