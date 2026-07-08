<?php
/**
 * Template Name: The SIP · Termini e Condizioni
 * Template Post Type: page
 *
 * Porting da components/shop/legal/TermsPage.tsx (Next).
 *
 * @package The SIP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$pn_toc = array(
	array( 'id' => 'condizioni-servizio', 'label' => __( 'Condizioni di Servizio', 'pharmanow' ), 'indent' => false ),
	array( 'id' => 'informazioni',        'label' => __( '1. Informazioni', 'pharmanow' ),       'indent' => true ),
	array( 'id' => 'finalita',            'label' => __( '2. Finalità', 'pharmanow' ),           'indent' => true ),
	array( 'id' => 'disponibilita',       'label' => __( '3. Disponibilità', 'pharmanow' ),      'indent' => true ),
	array( 'id' => 'ordini',              'label' => __( '4. Ordini', 'pharmanow' ),             'indent' => true ),
	array( 'id' => 'prodotti',            'label' => __( '5. Prodotti', 'pharmanow' ),           'indent' => true ),
	array( 'id' => 'spedizioni',          'label' => __( '7. Spedizioni', 'pharmanow' ),         'indent' => true ),
	array( 'id' => 'resi',                'label' => __( '8. Resi', 'pharmanow' ),               'indent' => true ),
	array( 'id' => 'pagamento',           'label' => __( '9. Pagamento', 'pharmanow' ),          'indent' => true ),
	array( 'id' => 'buoni',               'label' => __( '10. Buoni', 'pharmanow' ),             'indent' => true ),
	array( 'id' => 'condizioni-uso',      'label' => __( "Condizioni d'Uso", 'pharmanow' ),      'indent' => false, 'divider' => true ),
);
?>

<div class="bg-gray-50 min-h-screen">

	<?php
	get_template_part(
		'template-parts/legal/page-hero',
		null,
		array(
			'title'            => __( 'Termini e Condizioni', 'pharmanow' ),
			'subtitle'         => __( 'Ultimo aggiornamento: Gennaio 2026', 'pharmanow' ),
			'breadcrumb_label' => __( 'Termini e Condizioni', 'pharmanow' ),
			'size'             => 'sm',
		)
	);
	?>

	<div class="max-w-[1280px] mx-auto px-4 py-8 sm:py-12">
		<div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

			<aside class="lg:col-span-1">
				<div class="bg-white rounded-2xl shadow-sm p-5 lg:sticky lg:top-[120px]">
					<h2 class="font-bold text-gray-900 mb-4"><?php esc_html_e( 'Indice', 'pharmanow' ); ?></h2>
					<nav class="space-y-2">
						<?php foreach ( $pn_toc as $pn_item ) : ?>
							<?php
							$pn_classes = 'block text-sm text-gray-600 hover:text-[#0A6E9E] transition-colors';
							if ( ! empty( $pn_item['indent'] ) ) {
								$pn_classes .= ' pl-3';
							}
							if ( ! empty( $pn_item['divider'] ) ) {
								$pn_classes .= ' mt-4 pt-4 border-t border-gray-100';
							}
							?>
							<a href="#<?php echo esc_attr( $pn_item['id'] ); ?>" class="<?php echo esc_attr( $pn_classes ); ?>">
								<?php echo esc_html( $pn_item['label'] ); ?>
							</a>
						<?php endforeach; ?>
					</nav>
				</div>
			</aside>

			<div class="lg:col-span-3">
				<div class="bg-white rounded-2xl shadow-sm p-6 sm:p-8 space-y-8">

					<section id="condizioni-servizio" class="scroll-mt-24">
						<h2 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100"><?php esc_html_e( 'Termini e Condizioni Generali di Servizio', 'pharmanow' ); ?></h2>
						<div class="text-gray-600 space-y-4 leading-relaxed">
							<p><?php esc_html_e( 'Benvenuti nel sito thesip.it e nelle sue applicazioni (di seguito "Servizio").', 'pharmanow' ); ?></p>
							<p>
								<?php esc_html_e( "Questa pagina (unitamente ai documenti in essa richiamati) descrive le condizioni generali in base alle quali The SIP da la possibilità agli utenti di acquistare i prodotti (di seguito \"Prodotto/i\") specificati nel sito e/o nell'applicazione mobile. Si raccomanda di leggere attentamente le presenti condizioni generali prima di ordinare qualsiasi Prodotto. Visitare il Sito o ordinare un Prodotto costituisce accettazione delle presenti condizioni generali e della politica per l'utilizzo del sito.", 'pharmanow' ); ?>
							</p>
							<p><?php esc_html_e( 'In caso di domande relativamente alle presenti condizioni generali, si prega di rivolgersi a The SIP prima di effettuare qualsiasi ordine. In caso di disaccordo, anche solo parziale, con quanto previsto in tali condizioni generali, si raccomanda di non utilizzare il Servizio.', 'pharmanow' ); ?></p>
						</div>
					</section>

					<section id="informazioni" class="scroll-mt-24">
						<h3 class="text-lg font-bold text-gray-900 mb-3"><?php esc_html_e( "1. Informazioni relative all'app e al sito", 'pharmanow' ); ?></h3>
						<div class="text-gray-600 space-y-4 leading-relaxed">
							<p>
								<?php
								printf(
									/* translators: %s = email customer-care */
									wp_kses(
										__( 'Il Sito "www.thesip.it" e l\'applicazione "The SIP" sono gestiti da The SIP, con sede a Napoli. Per eventuale assistenza o reclami, il recapito di The SIP è il seguente: e-mail %s.', 'pharmanow' ),
										array( 'a' => array( 'href' => array(), 'class' => array() ) )
									),
									'<a href="mailto:info@thesip.it" class="text-[#0A6E9E] hover:underline">info@thesip.it</a>'
								);
								?>
							</p>
							<p><?php esc_html_e( 'The SIP è un progetto per la vendita on-line e la consegna di flashcard illustrate di biologia marina, stickers e set collezione, nato a Napoli dall\'incontro tra 25 artisti e due biologi marini.', 'pharmanow' ); ?></p>
						</div>
					</section>

					<section id="finalita" class="scroll-mt-24">
						<h3 class="text-lg font-bold text-gray-900 mb-3"><?php esc_html_e( '2. Finalità', 'pharmanow' ); ?></h3>
						<div class="text-gray-600 space-y-4 leading-relaxed">
							<p><?php esc_html_e( 'Il Servizio intende offrire un mezzo semplice e pratico grazie al quale gli utenti possono visualizzare, scegliere e acquistare i Prodotti. The SIP si occupa della produzione e commercializzazione dei Prodotti.', 'pharmanow' ); ?></p>
						</div>
					</section>

					<section id="disponibilita" class="scroll-mt-24">
						<h3 class="text-lg font-bold text-gray-900 mb-3"><?php esc_html_e( '3. Disponibilità del servizio', 'pharmanow' ); ?></h3>
						<div class="text-gray-600 space-y-4 leading-relaxed">
							<p><?php esc_html_e( "The SIP offre un servizio di ordinazione di Prodotti e la relativa consegna a domicilio nel territorio nazionale (Italia). Per visualizzare i Prodotti è necessario accedere al Servizio e selezionare i Prodotti desiderati al fine di collocare l'ordine.", 'pharmanow' ); ?></p>
						</div>
					</section>

					<section id="ordini" class="scroll-mt-24">
						<h3 class="text-lg font-bold text-gray-900 mb-3"><?php esc_html_e( '4. Ordini', 'pharmanow' ); ?></h3>
						<div class="text-gray-600 space-y-4 leading-relaxed">
							<p><?php esc_html_e( "A fronte di un ordine tramite il Servizio, The SIP invia all'utente una e-mail di ringraziamento nonché di conferma della ricezione e accettazione di tale ordine (di seguito \"E-mail di conferma\"). Il contratto per la fornitura dei Prodotti ordinati si conclude tra l'utente e The SIP e si perfeziona esclusivamente al momento dell'invio all'utente dell'E-mail di conferma sopra menzionata.", 'pharmanow' ); ?></p>
							<h4 class="font-semibold text-gray-900 mt-6 mb-3"><?php esc_html_e( "Come si effettua l'ordine?", 'pharmanow' ); ?></h4>
							<p><?php esc_html_e( 'Fare un ordine tramite il Servizio The SIP richiede alcuni semplici passaggi:', 'pharmanow' ); ?></p>
							<ol class="list-decimal list-inside space-y-2 mt-3">
								<li><?php esc_html_e( 'Ricercare i prodotti attraverso la barra di ricerca oppure attraverso il menù a categorie.', 'pharmanow' ); ?></li>
								<li><?php esc_html_e( 'Selezionare i prodotti che si vuole acquistare cliccando il pulsante "Aggiungi al carrello" nella scheda prodotto.', 'pharmanow' ); ?></li>
								<li><?php esc_html_e( "Quando si decide di ultimare l'acquisto basterà cliccare sul pulsante \"Vai alla cassa\" presente nel carrello.", 'pharmanow' ); ?></li>
							</ol>
						</div>
					</section>

					<section id="prodotti" class="scroll-mt-24">
						<h3 class="text-lg font-bold text-gray-900 mb-3"><?php esc_html_e( '5. Prodotti', 'pharmanow' ); ?></h3>
						<div class="text-gray-600 space-y-4 leading-relaxed">
							<p><?php esc_html_e( "I Prodotti vengono consegnati previa verifica della loro disponibilità. Il Partner si impegna ad assicurare che i Prodotti siano integri nella loro confezione e contenuto e che l'offerta e la vendita siano a norma di legge.", 'pharmanow' ); ?></p>
						</div>
					</section>

					<section id="spedizioni" class="scroll-mt-24">
						<h3 class="text-lg font-bold text-gray-900 mb-3"><?php esc_html_e( '7. Costi e modalità di spedizione', 'pharmanow' ); ?></h3>
						<div class="text-gray-600 space-y-4 leading-relaxed">
							<p><?php esc_html_e( 'The SIP si propone come obiettivo di fornire il miglior servizio di consegna possibile. Tutti i prodotti acquistati sul Sito e/o App vengono spediti da The SIP, con sede a Napoli.', 'pharmanow' ); ?></p>
							<p><?php esc_html_e( "Le spedizioni vengono effettuate con corriere espresso. Le spedizioni avvengono solo dal Lunedì al Venerdì, escluse le festività nazionali. I vostri ordini saranno evasi in 24/48h dal momento dell'ordine. Se sei a Napoli, è possibile concordare la consegna a mano.", 'pharmanow' ); ?></p>

							<div class="overflow-x-auto mt-4">
								<table class="w-full border border-gray-200 rounded-lg overflow-hidden">
									<thead class="bg-[#0A6E9E] text-white">
										<tr>
											<th class="px-4 py-3 text-left text-sm font-semibold"><?php esc_html_e( 'Destinazione', 'pharmanow' ); ?></th>
											<th class="px-4 py-3 text-center text-sm font-semibold"><?php esc_html_e( 'Fino a €29,90', 'pharmanow' ); ?></th>
											<th class="px-4 py-3 text-center text-sm font-semibold"><?php esc_html_e( 'Oltre €29,90', 'pharmanow' ); ?></th>
											<th class="px-4 py-3 text-center text-sm font-semibold"><?php esc_html_e( 'Tempi', 'pharmanow' ); ?></th>
										</tr>
									</thead>
									<tbody class="bg-white">
										<tr class="border-b border-gray-100">
											<td class="px-4 py-3 text-gray-700 font-medium"><?php esc_html_e( 'Italia', 'pharmanow' ); ?></td>
											<td class="px-4 py-3 text-center text-gray-600">€4,90</td>
											<td class="px-4 py-3 text-center text-green-600 font-semibold"><?php esc_html_e( 'Gratis', 'pharmanow' ); ?></td>
											<td class="px-4 py-3 text-center text-gray-600">24/48 ore</td>
										</tr>
										<tr>
											<td class="px-4 py-3 text-gray-700 font-medium"><?php esc_html_e( 'Sicilia, Sardegna, isole*', 'pharmanow' ); ?></td>
											<td class="px-4 py-3 text-center text-gray-600">€9,90</td>
											<td class="px-4 py-3 text-center text-gray-600">€4,90</td>
											<td class="px-4 py-3 text-center text-gray-600">48/72 ore</td>
										</tr>
									</tbody>
								</table>
							</div>
							<p class="text-xs text-gray-500 mt-2"><?php esc_html_e( '*Località disagiate e difficilmente raggiungibili', 'pharmanow' ); ?></p>

							<div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mt-4">
								<p class="text-sm text-blue-800">
									<?php echo wp_kses_post( __( "<strong>Importante:</strong> All'atto del ricevimento del prodotto il Cliente dovrà verificare che il collo sia perfettamente chiuso. Ogni pacco è chiuso dal nastro adesivo con il logo The SIP. Si invitano gli acquirenti a non ritirare la scatola se il pacco non risulta integro o il nastro adesivo manomesso.", 'pharmanow' ) ); ?>
								</p>
							</div>
						</div>
					</section>

					<section id="resi" class="scroll-mt-24">
						<h3 class="text-lg font-bold text-gray-900 mb-3"><?php esc_html_e( '8. Annullamento e resi', 'pharmanow' ); ?></h3>
						<div class="text-gray-600 space-y-4 leading-relaxed">
							<p><?php esc_html_e( "I prodotti acquistati possono sempre essere restituiti dall'acquirente a The SIP, previa comunicazione via e-mail, entro e non oltre quattordici (14) giorni dalla data di ricezione. Le carte personalizzate del Bundle Deluxe, realizzate su misura, non sono restituibili salvo difetto di prodotto.", 'pharmanow' ); ?></p>
							<p>
								<?php
								printf(
									/* translators: %s = email customer-care */
									wp_kses(
										__( "La comunicazione potrà essere anticipata dall'acquirente stesso al seguente indirizzo e-mail %s, purché confermata con raccomandata a/r entro 48 ore.", 'pharmanow' ),
										array( 'a' => array( 'href' => array(), 'class' => array() ) )
									),
									'<a href="mailto:info@thesip.it" class="text-[#0A6E9E] hover:underline">info@thesip.it</a>'
								);
								?>
							</p>

							<div class="bg-amber-50 border border-amber-100 rounded-xl p-4 mt-4">
								<h4 class="font-semibold text-amber-800 mb-2"><?php esc_html_e( 'Condizioni per il reso:', 'pharmanow' ); ?></h4>
								<ul class="list-disc list-inside space-y-1 text-sm text-amber-700">
									<li><?php esc_html_e( "Il prodotto deve essere integro e nell'imballaggio originale non danneggiato", 'pharmanow' ); ?></li>
									<li><?php esc_html_e( "Il trasporto del prodotto reso è a carico dell'utente", 'pharmanow' ); ?></li>
									<li><?php esc_html_e( 'Restituzione entro 14 giorni dalla ricezione', 'pharmanow' ); ?></li>
									<li><?php esc_html_e( 'Rimborso entro 30 giorni dalla ricezione dei prodotti', 'pharmanow' ); ?></li>
								</ul>
							</div>

							<h4 class="font-semibold text-gray-900 mt-4 mb-2"><?php esc_html_e( 'Il diritto di recesso decade nei seguenti casi:', 'pharmanow' ); ?></h4>
							<ul class="list-disc list-inside space-y-1">
								<li><?php esc_html_e( 'Utilizzo anche parziale del bene e di eventuali materiali di consumo', 'pharmanow' ); ?></li>
								<li><?php esc_html_e( "Mancanza della confezione esterna e/o dell'imballo interno originale", 'pharmanow' ); ?></li>
								<li><?php esc_html_e( 'Danneggiamento del prodotto per cause diverse dal trasporto', 'pharmanow' ); ?></li>
								<li><?php esc_html_e( 'Assenza di elementi integranti del prodotto', 'pharmanow' ); ?></li>
								<li><?php esc_html_e( 'Acquisti effettuati con Partita IVA', 'pharmanow' ); ?></li>
							</ul>
						</div>
					</section>

					<section id="pagamento" class="scroll-mt-24">
						<h3 class="text-lg font-bold text-gray-900 mb-3"><?php esc_html_e( '9. Prezzi e modalità di pagamento', 'pharmanow' ); ?></h3>
						<div class="text-gray-600 space-y-4 leading-relaxed">
							<p><?php esc_html_e( "I prezzi dei Prodotti sono elencati nel Servizio e sono comprensivi di IVA. A tali prezzi potranno essere aggiunte le spese di consegna se applicabili, secondo quanto indicato nel Servizio.", 'pharmanow' ); ?></p>
							<h4 class="font-semibold text-gray-900 mt-4 mb-2"><?php esc_html_e( 'Pagamento con Carte e PayPal', 'pharmanow' ); ?></h4>
							<p><?php esc_html_e( "L'acquirente può pagare con una qualsiasi carta di credito/prepagata appartenente ai circuiti Visa e Mastercard. Il limite minimo di spesa necessario a finalizzare la transazione è pari a €0,60. Sono accettati anche American Express e PayPal — pagamenti sicuri e criptati.", 'pharmanow' ); ?></p>
							<div class="flex flex-wrap items-center gap-3 p-4 bg-gray-50 rounded-xl mt-4">
								<?php foreach ( array( 'Visa', 'Mastercard', 'PayPal', 'Amex' ) as $pn_m ) : ?>
									<span class="bg-white rounded px-3 py-1.5 shadow-sm text-xs font-medium text-gray-700"><?php echo esc_html( $pn_m ); ?></span>
								<?php endforeach; ?>
								<span class="text-sm text-gray-500 ml-2"><?php esc_html_e( 'Pagamenti sicuri e criptati', 'pharmanow' ); ?></span>
							</div>
						</div>
					</section>

					<section id="buoni" class="scroll-mt-24">
						<h3 class="text-lg font-bold text-gray-900 mb-3"><?php esc_html_e( '10. Condizioni generali applicabili ai buoni', 'pharmanow' ); ?></h3>
						<div class="text-gray-600 space-y-4 leading-relaxed">
							<p><?php esc_html_e( 'Salvo che sia diversamente previsto, gli sconti, i buoni o i codici promozionali si applicano esclusivamente agli ordini effettuati dai clienti di The SIP.', 'pharmanow' ); ?></p>
							<ul class="list-disc list-inside space-y-2 mt-3">
								<li><?php esc_html_e( 'Può essere utilizzato un solo sconto, buono o codice promozionale per ciascun ordine', 'pharmanow' ); ?></li>
								<li><?php esc_html_e( 'Ciascun codice può essere utilizzato una sola volta per ciascun cliente', 'pharmanow' ); ?></li>
								<li><?php esc_html_e( 'I buoni o codici promozionali non sono cumulabili con altri buoni', 'pharmanow' ); ?></li>
								<li><?php esc_html_e( 'Gli sconti non possono essere convertiti in denaro', 'pharmanow' ); ?></li>
							</ul>
						</div>
					</section>

					<section class="scroll-mt-24">
						<h3 class="text-lg font-bold text-gray-900 mb-3"><?php esc_html_e( '11-17. Altre disposizioni', 'pharmanow' ); ?></h3>
						<div class="text-gray-600 space-y-3 leading-relaxed text-sm">
							<p><?php echo wp_kses_post( __( "<strong>Responsabilità:</strong> nella misura massima consentita dalla legge, l'App mobile e il Sito sono offerti \"così come sono\" senza alcuna garanzia.", 'pharmanow' ) ); ?></p>
							<p><?php echo wp_kses_post( __( '<strong>Eventi non controllabili:</strong> le parti non sono responsabili per ritardi dovuti a cause esterne (catastrofi naturali, pandemie, provvedimenti governativi, etc.).', 'pharmanow' ) ); ?></p>
							<p><?php echo wp_kses_post( __( '<strong>Legge applicabile:</strong> le controversie sono disciplinate dal diritto italiano e deferite ai tribunali del luogo di residenza del consumatore.', 'pharmanow' ) ); ?></p>
							<p><?php echo wp_kses_post( __( '<strong>Modifiche:</strong> The SIP può modificare le presenti condizioni in qualsiasi momento.', 'pharmanow' ) ); ?></p>
						</div>
					</section>

					<div class="border-t-4 border-[#0A6E9E]/20 my-4"></div>

					<section id="condizioni-uso" class="scroll-mt-24">
						<h2 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100"><?php esc_html_e( "Condizioni per l'Utilizzo del Sito e delle Applicazioni", 'pharmanow' ); ?></h2>
						<div class="text-gray-600 space-y-4 leading-relaxed">
							<p><?php esc_html_e( "Questa sezione descrive le condizioni generali per l'utilizzo del sito web www.thesip.it e dell'applicazione \"The SIP\".", 'pharmanow' ); ?></p>
							<h4 class="font-semibold text-gray-900 mt-4 mb-2"><?php esc_html_e( 'Accesso al Servizio', 'pharmanow' ); ?></h4>
							<p><?php esc_html_e( "L'utente può accedere in due modi: come Utente non registrato (anonimo) o come Utente registrato. Per registrarsi è necessario essere maggiorenni e avere piena capacità giuridica.", 'pharmanow' ); ?></p>
							<h4 class="font-semibold text-gray-900 mt-4 mb-2"><?php esc_html_e( 'Uso consentito', 'pharmanow' ); ?></h4>
							<p><?php esc_html_e( "Il Servizio può essere utilizzato esclusivamente per finalità conformi alla legge. È vietato utilizzare il sito per attività illegali o per diffondere contenuti inappropriati.", 'pharmanow' ); ?></p>
							<h4 class="font-semibold text-gray-900 mt-4 mb-2"><?php esc_html_e( 'Proprietà intellettuale', 'pharmanow' ); ?></h4>
							<p><?php esc_html_e( "Tutti i diritti di proprietà intellettuale relativi al Sito e al Servizio appartengono a The SIP. È vietato copiare, riprodurre o diffondere i contenuti senza autorizzazione.", 'pharmanow' ); ?></p>
							<h4 class="font-semibold text-gray-900 mt-4 mb-2"><?php esc_html_e( 'Privacy e sicurezza', 'pharmanow' ); ?></h4>
							<p>
								<?php
								printf(
									/* translators: %s = link informativa privacy */
									wp_kses(
										__( "The SIP ha adottato misure di sicurezza per proteggere i dati degli utenti, inclusa la tecnologia di criptaggio e protocolli HTTPS. Per maggiori informazioni, consulta la nostra %s.", 'pharmanow' ),
										array( 'a' => array( 'href' => array(), 'class' => array() ) )
									),
									'<a href="' . esc_url( home_url( '/legale/privacy' ) ) . '" class="text-[#0A6E9E] hover:underline">' . esc_html__( 'Informativa Privacy', 'pharmanow' ) . '</a>'
								);
								?>
							</p>
						</div>
					</section>

					<section class="bg-gradient-to-r from-[#0A6E9E]/10 to-[#38BDF8]/10 rounded-xl p-6">
						<h3 class="text-lg font-bold text-gray-900 mb-3"><?php esc_html_e( 'Hai domande?', 'pharmanow' ); ?></h3>
						<p class="text-gray-600 mb-4"><?php esc_html_e( 'Per qualsiasi dubbio o domanda relativamente ai contenuti e materiali del Servizio si prega di contattarci.', 'pharmanow' ); ?></p>
						<div class="flex flex-wrap gap-4">
							<a href="mailto:info@thesip.it" class="inline-flex items-center gap-2 bg-[#0A6E9E] text-white font-semibold py-2.5 px-5 rounded-xl hover:bg-[#0A6E9E]/90 transition-colors">
								<?php pn_icon( 'mail', array( 'class' => 'w-5 h-5' ) ); ?>
								<?php esc_html_e( 'Scrivici', 'pharmanow' ); ?>
							</a>
							<a href="<?php echo esc_url( home_url( '/faq' ) ); ?>" class="inline-flex items-center gap-2 bg-white text-gray-700 font-semibold py-2.5 px-5 rounded-xl border border-gray-200 hover:bg-gray-50 transition-colors">
								<?php pn_icon( 'help-circle', array( 'class' => 'w-5 h-5' ) ); ?>
								<?php esc_html_e( 'FAQ', 'pharmanow' ); ?>
							</a>
						</div>
					</section>

				</div>
			</div>

		</div>
	</div>
</div>

<?php
get_footer();
