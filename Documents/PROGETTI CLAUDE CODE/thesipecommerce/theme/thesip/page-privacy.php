<?php
/**
 * Template Name: The SIP · Privacy Policy
 * Template Post Type: page
 *
 * Porting da components/shop/legal/PrivacyPage.tsx (Next).
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$pn_toc = array(
	array( 'id' => 'informazioni-generali', 'label' => __( 'Informazioni Generali', 'pharmanow' ) ),
	array( 'id' => 'raccolta-dati',         'label' => __( 'Raccolta Dati', 'pharmanow' ) ),
	array( 'id' => 'finalita-primarie',     'label' => __( 'Finalità Primarie', 'pharmanow' ) ),
	array( 'id' => 'marketing',             'label' => __( 'Marketing', 'pharmanow' ) ),
	array( 'id' => 'profilazione',          'label' => __( 'Profilazione', 'pharmanow' ) ),
	array( 'id' => 'comunicazione-dati',    'label' => __( 'Comunicazione Dati', 'pharmanow' ) ),
	array( 'id' => 'conservazione',         'label' => __( 'Conservazione', 'pharmanow' ) ),
	array( 'id' => 'diritti',               'label' => __( 'I Tuoi Diritti', 'pharmanow' ) ),
	array( 'id' => 'contatti',              'label' => __( 'Contatti', 'pharmanow' ) ),
);

$pn_rights = array(
	array( 'icon' => 'eye',     'title' => __( 'Diritto di Accesso', 'pharmanow' ),     'desc' => __( "Ottenere conferma dell'esistenza dei tuoi dati e la loro comunicazione", 'pharmanow' ) ),
	array( 'icon' => 'edit-2',  'title' => __( 'Diritto di Rettifica', 'pharmanow' ),   'desc' => __( 'Aggiornamento, rettifica o integrazione dei dati', 'pharmanow' ) ),
	array( 'icon' => 'trash-2', 'title' => __( 'Diritto di Cancellazione', 'pharmanow' ), 'desc' => __( 'Cancellazione o trasformazione in forma anonima dei dati', 'pharmanow' ) ),
	array( 'icon' => 'ban',     'title' => __( 'Diritto di Opposizione', 'pharmanow' ),  'desc' => __( 'Opporti al trattamento per finalità di marketing diretto', 'pharmanow' ) ),
);
?>

<div class="bg-gray-50 min-h-screen">

	<?php
	get_template_part(
		'template-parts/legal/page-hero',
		null,
		array(
			'title'            => __( 'Privacy Policy', 'pharmanow' ),
			'subtitle'         => __( 'Informativa sul trattamento dei dati personali ai sensi del GDPR', 'pharmanow' ),
			'breadcrumb_label' => __( 'Privacy Policy', 'pharmanow' ),
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
							<a href="#<?php echo esc_attr( $pn_item['id'] ); ?>" class="block text-sm text-gray-600 hover:text-[#0B8894] transition-colors">
								<?php echo esc_html( $pn_item['label'] ); ?>
							</a>
						<?php endforeach; ?>
					</nav>
					<div class="mt-6 pt-4 border-t border-gray-100">
						<div class="flex items-center gap-2 text-sm text-gray-500">
							<?php pn_icon( 'shield-check', array( 'class' => 'w-5 h-5 text-green-500' ) ); ?>
							<span><?php esc_html_e( 'Conforme GDPR', 'pharmanow' ); ?></span>
						</div>
					</div>
				</div>
			</aside>

			<div class="lg:col-span-3">
				<div class="bg-white rounded-2xl shadow-sm p-6 sm:p-8 space-y-10">

					<div class="bg-[#0B8894]/5 border border-[#0B8894]/20 rounded-xl p-5">
						<h2 class="text-lg font-bold text-gray-900 mb-2">
							<?php esc_html_e( 'Informativa per il trattamento dei dati personali', 'pharmanow' ); ?>
						</h2>
						<p class="text-gray-600 text-sm">
							<?php esc_html_e( "Ai sensi dell'Art. 13 del Regolamento Generale UE sulla Protezione dei Dati Personali N. 679/2016 (GDPR)", 'pharmanow' ); ?>
						</p>
					</div>

					<section id="informazioni-generali" class="scroll-mt-24">
						<h2 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100"><?php esc_html_e( 'Informazioni Generali', 'pharmanow' ); ?></h2>
						<div class="text-gray-600 space-y-4 leading-relaxed">
							<p>
								<?php
								echo wp_kses_post( __( "Gentile utente, ai sensi dell'art. 13 del Regolamento UE 679/16 (in seguito, &quot;GDPR&quot;) ti informiamo che la registrazione da parte tua al sito web www.thesip.it (in seguito, &quot;Sito&quot;) implica il trattamento dei tuoi Dati personali da parte di <strong>The SIP</strong>, con sede a Napoli, Titolare del trattamento.", 'pharmanow' ) );
								?>
							</p>
							<p>
								<?php esc_html_e( 'Lo scopo della presente informativa per la protezione dei tuoi dati personali è di fornirti le dovute informazioni sul trattamento dei tuoi dati personali quando utilizzi il nostro sito web.', 'pharmanow' ); ?>
							</p>
						</div>
					</section>

					<section id="raccolta-dati" class="scroll-mt-24">
						<h2 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100"><?php esc_html_e( 'Modalità di Raccolta e Trattamento dei Dati', 'pharmanow' ); ?></h2>
						<div class="text-gray-600 space-y-4 leading-relaxed">
							<p><?php echo wp_kses_post( __( "Il Titolare del trattamento tratta i tuoi <strong>Dati personali identificativi</strong> (nome, cognome, e-mail, indirizzo, dati bancari) in seguito alla registrazione nell'area riservata che ti consente di accedere agli acquisti ed ai servizi offerti nel sito www.thesip.it.", 'pharmanow' ) ); ?></p>
							<p><?php esc_html_e( 'Qualora tu decida di chiudere il tuo account, il Titolare conserverà i dati personali conferiti solo per finalità di carattere amministrativo, salve eventuali ulteriori esigenze per cui il loro ulteriore mantenimento sia concesso e/o richiesto da specifiche disposizioni di legge.', 'pharmanow' ); ?></p>

							<h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3"><?php esc_html_e( 'Dati di Navigazione', 'pharmanow' ); ?></h3>
							<p><?php esc_html_e( 'I dati di navigazione sono raccolti automaticamente durante il normale uso del Sito e, in associazione con i dati identificativi, possono identificarti. In questa categoria di dati rientrano:', 'pharmanow' ); ?></p>
							<ul class="list-disc list-inside space-y-1 text-gray-600 mt-3">
								<li><?php esc_html_e( 'Indirizzi IP o nomi a dominio dei computer e dei terminali utilizzati', 'pharmanow' ); ?></li>
								<li><?php esc_html_e( 'Orario e provenienza delle visite', 'pharmanow' ); ?></li>
								<li><?php esc_html_e( "Altri parametri relativi al sistema operativo e all'ambiente informatico", 'pharmanow' ); ?></li>
							</ul>
							<div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mt-4">
								<p class="text-sm text-blue-800">
									<?php echo wp_kses_post( __( "<strong>Nota:</strong> I dati di navigazione non persistono per più di sette giorni, fatte salve eventuali necessità di accertamento di reati da parte dell'Autorità giudiziaria.", 'pharmanow' ) ); ?>
								</p>
							</div>
						</div>
					</section>

					<section id="finalita-primarie" class="scroll-mt-24">
						<h2 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100"><?php esc_html_e( 'Finalità Primarie del Trattamento', 'pharmanow' ); ?></h2>
						<div class="text-gray-600 space-y-4 leading-relaxed">
							<p><?php esc_html_e( 'Il conferimento dei tuoi dati personali è obbligatorio se desideri registrarti sul sito web www.thesip.it ed accedere ai nostri servizi. Le finalità primarie del trattamento sono:', 'pharmanow' ); ?></p>

							<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
								<div class="bg-gray-50 rounded-xl p-4">
									<h4 class="font-semibold text-gray-900 mb-1"><?php esc_html_e( 'Vendita Prodotti', 'pharmanow' ); ?></h4>
									<p class="text-sm text-gray-600"><?php esc_html_e( 'Flashcard illustrate di biologia marina, stickers e set collezione', 'pharmanow' ); ?></p>
								</div>
								<div class="bg-gray-50 rounded-xl p-4">
									<h4 class="font-semibold text-gray-900 mb-1"><?php esc_html_e( 'Servizi Informativi', 'pharmanow' ); ?></h4>
									<p class="text-sm text-gray-600"><?php esc_html_e( 'Informazioni relative ai prodotti e servizi', 'pharmanow' ); ?></p>
								</div>
							</div>

							<h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3"><?php esc_html_e( 'I tuoi dati saranno trattati per:', 'pharmanow' ); ?></h3>
							<ul class="space-y-2">
								<?php
								$pn_purposes = array(
									__( 'Adempiere agli obblighi precontrattuali e contrattuali derivanti dalla registrazione', 'pharmanow' ),
									__( "Evadere gli ordini di acquisto e gestire l'esecuzione del contratto", 'pharmanow' ),
									__( 'Gestione degli incassi e dei pagamenti', 'pharmanow' ),
									__( 'Adempiere agli obblighi previsti dalla legge e dalla normativa comunitaria', 'pharmanow' ),
									__( 'Adempiere agli obblighi fiscali derivanti dal rapporto in essere', 'pharmanow' ),
									__( 'Esercitare i diritti del Titolare (es. diritto di difesa in giudizio)', 'pharmanow' ),
								);
								foreach ( $pn_purposes as $pn_i => $pn_p ) :
									?>
									<li class="flex items-start gap-2 text-gray-600">
										<span class="text-[#0B8894] font-bold"><?php echo esc_html( chr( 97 + $pn_i ) ); ?>)</span>
										<span><?php echo esc_html( $pn_p ); ?></span>
									</li>
								<?php endforeach; ?>
							</ul>

							<div class="mt-6 bg-green-50 border border-green-100 rounded-xl p-4">
								<h4 class="font-semibold text-green-800 mb-2"><?php esc_html_e( 'Base Giuridica', 'pharmanow' ); ?></h4>
								<p class="text-sm text-green-700">
									<?php esc_html_e( "L'esecuzione di un contratto di cui l'interessato è parte o all'esecuzione di misure precontrattuali adottate su richiesta dello stesso (Art. 6, comma 1, lett. b, GDPR).", 'pharmanow' ); ?>
								</p>
							</div>
						</div>
					</section>

					<section id="marketing" class="scroll-mt-24">
						<h2 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100"><?php esc_html_e( 'Finalità di Marketing', 'pharmanow' ); ?></h2>
						<div class="text-gray-600 space-y-4 leading-relaxed">
							<p><?php echo wp_kses_post( __( 'I dati personali raccolti saranno trattati — <strong>previo tuo consenso</strong> — anche per le seguenti finalità secondarie:', 'pharmanow' ) ); ?></p>
							<ul class="list-disc list-inside space-y-1 text-gray-600 mt-3">
								<li><?php esc_html_e( 'Promozione commerciale', 'pharmanow' ); ?></li>
								<li><?php esc_html_e( 'Comunicazione pubblicitaria', 'pharmanow' ); ?></li>
								<li><?php esc_html_e( 'Ricerche di mercato', 'pharmanow' ); ?></li>
								<li><?php esc_html_e( 'Sondaggi sulla qualità e soddisfazione dei servizi', 'pharmanow' ); ?></li>
								<li><?php esc_html_e( 'Elaborazioni statistiche', 'pharmanow' ); ?></li>
							</ul>
							<div class="bg-amber-50 border border-amber-100 rounded-xl p-4 mt-4">
								<h4 class="font-semibold text-amber-800 mb-2"><?php esc_html_e( 'Modalità di contatto', 'pharmanow' ); ?></h4>
								<p class="text-sm text-amber-700">
									<?php esc_html_e( 'Il tuo consenso ci autorizzerà ad inviarti comunicazioni commerciali mediante: posta elettronica, chiamate telefoniche automatizzate, newsletter, SMS, messaggi su applicazioni web e posta cartacea.', 'pharmanow' ); ?>
								</p>
							</div>
							<p class="mt-4">
								<?php echo wp_kses_post( __( '<strong>Importante:</strong> Il consenso è del tutto facoltativo. Laddove decidi di non prestare il consenso, nessuna conseguenza vi sarà per i tuoi acquisti online.', 'pharmanow' ) ); ?>
							</p>
						</div>
					</section>

					<section id="profilazione" class="scroll-mt-24">
						<h2 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100"><?php esc_html_e( 'Trattamenti per Finalità di Profilazione', 'pharmanow' ); ?></h2>
						<div class="text-gray-600 space-y-4 leading-relaxed">
							<p><?php esc_html_e( 'Per le finalità di marketing e miglioramento dei servizi, il Titolare può procedere a trattamenti di "profilazione" per valutare determinati aspetti e analizzare le preferenze, gli interessi, etc.', 'pharmanow' ); ?></p>
							<div class="bg-gray-50 rounded-xl p-5 mt-4">
								<h4 class="font-semibold text-gray-900 mb-2"><?php esc_html_e( "Cos'è la profilazione? (Art. 4 par. 4 GDPR)", 'pharmanow' ); ?></h4>
								<p class="text-sm text-gray-600">
									<?php esc_html_e( "Qualsiasi forma di trattamento automatizzato di dati personali consistente nell'utilizzo di tali dati per valutare determinati aspetti personali relativi a una persona fisica, in particolare per analizzare o prevedere aspetti riguardanti le preferenze personali, gli interessi, l'affidabilità, il comportamento, l'ubicazione o gli spostamenti.", 'pharmanow' ); ?>
								</p>
							</div>
							<p class="mt-4">
								<?php echo wp_kses_post( __( 'Il conferimento del consenso al Trattamento di Profilazione è assolutamente <strong>facoltativo ed opzionale</strong> (ed è comunque revocabile senza formalità in qualsiasi momento).', 'pharmanow' ) ); ?>
							</p>
						</div>
					</section>

					<section id="comunicazione-dati" class="scroll-mt-24">
						<h2 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100"><?php esc_html_e( 'Comunicazione e Diffusione dei Dati', 'pharmanow' ); ?></h2>
						<div class="text-gray-600 space-y-4 leading-relaxed">
							<p><?php esc_html_e( 'I tuoi dati potranno essere resi accessibili e portati a conoscenza di:', 'pharmanow' ); ?></p>
							<ul class="space-y-3 mt-3">
								<?php
								$pn_recipients = array(
									__( 'Dipendenti e collaboratori del Titolare, autorizzati al trattamento dei dati', 'pharmanow' ),
									__( 'Società terze o collaboratori esterni che svolgono attività per nostro conto (responsabili esterni del trattamento)', 'pharmanow' ),
									__( 'Autorità giudiziaria e di polizia o altre amministrazioni pubbliche per obblighi normativi', 'pharmanow' ),
								);
								foreach ( $pn_recipients as $pn_r ) :
									?>
									<li class="flex items-start gap-3 text-gray-600">
										<div class="w-6 h-6 bg-[#0B8894]/10 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
											<?php pn_icon( 'check', array( 'class' => 'w-3 h-3 text-[#0B8894]' ) ); ?>
										</div>
										<span><?php echo esc_html( $pn_r ); ?></span>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					</section>

					<section id="conservazione" class="scroll-mt-24">
						<h2 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100"><?php esc_html_e( 'Modalità e Tempi di Conservazione', 'pharmanow' ); ?></h2>
						<div class="text-gray-600 space-y-4 leading-relaxed">
							<p><?php esc_html_e( "I dati personali saranno trattati in forma prevalentemente automatizzata. Non è previsto il trasferimento dei dati al di fuori dell'Unione Europea.", 'pharmanow' ); ?></p>
							<div class="overflow-x-auto mt-4">
								<table class="w-full border border-gray-200 rounded-lg overflow-hidden">
									<thead class="bg-[#0B8894] text-white">
										<tr>
											<th class="px-4 py-3 text-left text-sm font-semibold"><?php esc_html_e( 'Tipologia Dati', 'pharmanow' ); ?></th>
											<th class="px-4 py-3 text-left text-sm font-semibold"><?php esc_html_e( 'Periodo di Conservazione', 'pharmanow' ); ?></th>
										</tr>
									</thead>
									<tbody class="bg-white">
										<tr class="border-b border-gray-100">
											<td class="px-4 py-3 text-gray-700 font-medium"><?php esc_html_e( 'Dati contrattuali', 'pharmanow' ); ?></td>
											<td class="px-4 py-3 text-gray-600"><?php esc_html_e( "10 anni dall'acquisto", 'pharmanow' ); ?></td>
										</tr>
										<tr class="border-b border-gray-100">
											<td class="px-4 py-3 text-gray-700 font-medium"><?php esc_html_e( 'Dati per marketing', 'pharmanow' ); ?></td>
											<td class="px-4 py-3 text-gray-600"><?php esc_html_e( '24 mesi dalla raccolta del consenso', 'pharmanow' ); ?></td>
										</tr>
										<tr>
											<td class="px-4 py-3 text-gray-700 font-medium"><?php esc_html_e( 'Dati per profilazione', 'pharmanow' ); ?></td>
											<td class="px-4 py-3 text-gray-600"><?php esc_html_e( '12 mesi', 'pharmanow' ); ?></td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</section>

					<section id="diritti" class="scroll-mt-24">
						<h2 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100"><?php esc_html_e( 'I Tuoi Diritti (Art. 15-22 GDPR)', 'pharmanow' ); ?></h2>
						<div class="text-gray-600 space-y-4 leading-relaxed">
							<p><?php esc_html_e( 'Ai sensi degli articoli 15-22 del GDPR, hai i seguenti diritti:', 'pharmanow' ); ?></p>
							<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
								<?php foreach ( $pn_rights as $pn_right ) : ?>
									<div class="border border-gray-200 rounded-xl p-4">
										<h4 class="font-semibold text-gray-900 mb-2 flex items-center gap-2">
											<?php pn_icon( $pn_right['icon'], array( 'class' => 'w-5 h-5 text-[#0B8894]' ) ); ?>
											<?php echo esc_html( $pn_right['title'] ); ?>
										</h4>
										<p class="text-sm text-gray-600"><?php echo esc_html( $pn_right['desc'] ); ?></p>
									</div>
								<?php endforeach; ?>
							</div>
							<div class="bg-red-50 border border-red-100 rounded-xl p-4 mt-4">
								<h4 class="font-semibold text-red-800 mb-2"><?php esc_html_e( 'Reclamo al Garante', 'pharmanow' ); ?></h4>
								<p class="text-sm text-red-700">
									<?php
									printf(
										/* translators: %s = link garanteprivacy */
										wp_kses(
											__( 'Hai il diritto di proporre un reclamo al Garante per la protezione dei dati personali seguendo le procedure su %s.', 'pharmanow' ),
											array( 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array(), 'class' => array() ) )
										),
										'<a href="https://www.garanteprivacy.it" target="_blank" rel="noopener noreferrer" class="underline font-medium">www.garanteprivacy.it</a>'
									);
									?>
								</p>
							</div>
						</div>
					</section>

					<section id="contatti" class="bg-gradient-to-r from-[#0B8894]/10 to-[#43CCB1]/10 rounded-xl p-6 scroll-mt-24">
						<h2 class="text-xl font-bold text-gray-900 mb-4"><?php esc_html_e( 'Contatti per Richieste Privacy', 'pharmanow' ); ?></h2>
						<p class="text-gray-600 mb-4"><?php esc_html_e( 'Per esercitare i tuoi diritti, segnalare problemi o chiedere chiarimenti sul trattamento dei dati personali:', 'pharmanow' ); ?></p>
						<div class="space-y-3 mb-6">
							<div class="flex items-center gap-3">
								<div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center shadow-sm">
									<?php pn_icon( 'mail', array( 'class' => 'w-5 h-5 text-[#0B8894]' ) ); ?>
								</div>
								<div>
									<p class="text-sm text-gray-500"><?php esc_html_e( 'Email Privacy', 'pharmanow' ); ?></p>
									<a href="mailto:info@thesip.it" class="text-[#0B8894] font-semibold hover:underline">info@thesip.it</a>
								</div>
							</div>
							<div class="flex items-center gap-3">
								<div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center shadow-sm">
									<?php pn_icon( 'map-pin', array( 'class' => 'w-5 h-5 text-[#0B8894]' ) ); ?>
								</div>
								<div>
									<p class="text-sm text-gray-500"><?php esc_html_e( 'Indirizzo', 'pharmanow' ); ?></p>
									<p class="text-gray-700 font-medium">The SIP — Napoli</p>
								</div>
							</div>
						</div>
						<div class="flex flex-wrap gap-4">
							<a href="mailto:info@thesip.it" class="inline-flex items-center gap-2 bg-[#0B8894] text-white font-semibold py-2.5 px-5 rounded-xl hover:bg-[#0B8894]/90 transition-colors">
								<?php pn_icon( 'mail', array( 'class' => 'w-5 h-5' ) ); ?>
								<?php esc_html_e( 'Contatta Privacy', 'pharmanow' ); ?>
							</a>
							<a href="<?php echo esc_url( home_url( '/legale/termini' ) ); ?>" class="inline-flex items-center gap-2 bg-white text-gray-700 font-semibold py-2.5 px-5 rounded-xl border border-gray-200 hover:bg-gray-50 transition-colors">
								<?php esc_html_e( 'Termini e Condizioni', 'pharmanow' ); ?>
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
