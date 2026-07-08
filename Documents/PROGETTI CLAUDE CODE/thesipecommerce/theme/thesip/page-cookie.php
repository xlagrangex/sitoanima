<?php
/**
 * Template Name: The SIP · Cookie Policy
 * Template Post Type: page
 *
 * Porting da components/shop/legal/CookiePolicyPage.tsx (Next).
 * Pulsante "Apri impostazioni cookie" emette l'evento custom `pn:cookie-settings:open`
 * che il banner consent custom (sezione 5.2 PORTING-PLAN) potrà ascoltare.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$pn_cookie_types = array(
	array(
		'name'        => __( 'Cookie Tecnici/Necessari', 'pharmanow' ),
		'description' => __( 'Questi cookie sono essenziali per il corretto funzionamento del sito web. Senza questi cookie, il sito non potrebbe funzionare correttamente.', 'pharmanow' ),
		'can_disable' => false,
		'examples'    => array(
			array( 'name' => 'session_id',     'purpose' => __( 'Gestione della sessione utente', 'pharmanow' ),         'duration' => __( 'Sessione', 'pharmanow' ) ),
			array( 'name' => 'cart_token',     'purpose' => __( 'Mantenimento del carrello', 'pharmanow' ),               'duration' => '30 ' . __( 'giorni', 'pharmanow' ) ),
			array( 'name' => 'cookie_consent', 'purpose' => __( 'Memorizzazione preferenze cookie', 'pharmanow' ),         'duration' => __( '1 anno', 'pharmanow' ) ),
			array( 'name' => 'XSRF-TOKEN',     'purpose' => __( 'Protezione da attacchi CSRF', 'pharmanow' ),              'duration' => __( 'Sessione', 'pharmanow' ) ),
		),
	),
	array(
		'name'        => __( 'Cookie Analitici', 'pharmanow' ),
		'description' => __( 'Questi cookie ci permettono di raccogliere informazioni anonime su come i visitatori utilizzano il nostro sito web, aiutandoci a migliorarne le prestazioni e l\'usabilità.', 'pharmanow' ),
		'can_disable' => true,
		'examples'    => array(
			array( 'name' => '_ga',   'purpose' => __( 'Google Analytics — Identificazione utenti', 'pharmanow' ),  'duration' => __( '2 anni', 'pharmanow' ) ),
			array( 'name' => '_ga_*', 'purpose' => __( 'Google Analytics — Stato sessione', 'pharmanow' ),          'duration' => __( '2 anni', 'pharmanow' ) ),
			array( 'name' => '_gid',  'purpose' => __( 'Google Analytics — Identificazione utenti', 'pharmanow' ),  'duration' => __( '24 ore', 'pharmanow' ) ),
			array( 'name' => '_gat',  'purpose' => __( 'Google Analytics — Limitazione richieste', 'pharmanow' ),   'duration' => __( '1 minuto', 'pharmanow' ) ),
		),
	),
	array(
		'name'        => __( 'Cookie di Marketing/Pubblicitari', 'pharmanow' ),
		'description' => __( 'Questi cookie vengono utilizzati per tracciare i visitatori attraverso i siti web e mostrare annunci pubblicitari pertinenti e coinvolgenti.', 'pharmanow' ),
		'can_disable' => true,
		'examples'    => array(
			array( 'name' => '_fbp', 'purpose' => __( 'Facebook Pixel — Tracciamento conversioni', 'pharmanow' ), 'duration' => __( '3 mesi', 'pharmanow' ) ),
			array( 'name' => 'fr',   'purpose' => __( 'Facebook — Pubblicità mirata', 'pharmanow' ),               'duration' => __( '3 mesi', 'pharmanow' ) ),
			array( 'name' => 'IDE',  'purpose' => __( 'Google DoubleClick — Pubblicità', 'pharmanow' ),            'duration' => __( '1 anno', 'pharmanow' ) ),
			array( 'name' => 'NID',  'purpose' => __( 'Google — Preferenze pubblicitarie', 'pharmanow' ),          'duration' => __( '6 mesi', 'pharmanow' ) ),
		),
	),
	array(
		'name'        => __( 'Cookie di Preferenza/Funzionalità', 'pharmanow' ),
		'description' => __( "Questi cookie permettono al sito di ricordare le scelte effettuate dall'utente e fornire funzionalità personalizzate.", 'pharmanow' ),
		'can_disable' => true,
		'examples'    => array(
			array( 'name' => 'language',         'purpose' => __( 'Preferenza lingua', 'pharmanow' ),               'duration' => __( '1 anno', 'pharmanow' ) ),
			array( 'name' => 'recently_viewed',  'purpose' => __( 'Prodotti visualizzati di recente', 'pharmanow' ), 'duration' => '30 ' . __( 'giorni', 'pharmanow' ) ),
			array( 'name' => 'wishlist',         'purpose' => __( 'Lista dei desideri', 'pharmanow' ),               'duration' => '30 ' . __( 'giorni', 'pharmanow' ) ),
		),
	),
);

$pn_third_parties = array(
	array( 'name' => 'Google Analytics', 'url' => 'https://policies.google.com/privacy' ),
	array( 'name' => 'Facebook',         'url' => 'https://www.facebook.com/privacy/explanation' ),
	array( 'name' => 'Trustpilot',       'url' => 'https://legal.trustpilot.com/for-reviewers/end-user-privacy-terms' ),
);

$pn_browsers = array(
	array( 'name' => 'Google Chrome',   'url' => 'https://support.google.com/chrome/answer/95647' ),
	array( 'name' => 'Mozilla Firefox', 'url' => 'https://support.mozilla.org/it/kb/Gestione%20dei%20cookie' ),
	array( 'name' => 'Safari',          'url' => 'https://support.apple.com/it-it/guide/safari/sfri11471/mac' ),
	array( 'name' => 'Microsoft Edge',  'url' => 'https://support.microsoft.com/it-it/microsoft-edge/eliminare-i-cookie-in-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09' ),
);
?>

<div class="bg-gray-50 min-h-screen">

	<?php
	get_template_part(
		'template-parts/legal/page-hero',
		null,
		array(
			'title'            => __( 'Cookie Policy', 'pharmanow' ),
			'subtitle'         => __( 'Ultimo aggiornamento: Gennaio 2026', 'pharmanow' ),
			'breadcrumb_label' => __( 'Cookie Policy', 'pharmanow' ),
			'size'             => 'md',
		)
	);
	?>

	<div class="max-w-[900px] mx-auto px-4 py-10 sm:py-14">
		<div class="bg-white rounded-2xl shadow-sm p-6 sm:p-10 space-y-10">

			<section>
				<h2 class="text-xl font-bold text-gray-900 mb-4"><?php esc_html_e( 'Cosa sono i Cookie?', 'pharmanow' ); ?></h2>
				<p class="text-gray-600 leading-relaxed mb-4">
					<?php esc_html_e( 'I cookie sono piccoli file di testo che vengono memorizzati sul tuo dispositivo (computer, tablet o smartphone) quando visiti un sito web. I cookie sono ampiamente utilizzati per far funzionare i siti web in modo più efficiente, nonché per fornire informazioni ai proprietari del sito.', 'pharmanow' ); ?>
				</p>
				<p class="text-gray-600 leading-relaxed">
					<?php echo wp_kses_post( __( 'Questo sito web (<strong>thesip.it</strong>) utilizza cookie e tecnologie simili per garantirti la migliore esperienza possibile, in conformità con il Regolamento UE 2016/679 (GDPR) e la Direttiva ePrivacy 2002/58/CE.', 'pharmanow' ) ); ?>
				</p>
			</section>

			<div class="bg-gradient-to-r from-[#0A6E9E]/10 to-[#38BDF8]/10 rounded-xl p-5">
				<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
					<div>
						<h3 class="font-semibold text-gray-900 mb-1"><?php esc_html_e( 'Gestisci le tue preferenze', 'pharmanow' ); ?></h3>
						<p class="text-sm text-gray-600"><?php esc_html_e( 'Puoi modificare le tue preferenze sui cookie in qualsiasi momento.', 'pharmanow' ); ?></p>
					</div>
					<button
						type="button"
						class="bg-gradient-to-r from-[#0A6E9E] to-[#38BDF8] text-white font-semibold py-2.5 px-5 rounded-xl hover:opacity-90 transition-opacity whitespace-nowrap"
						onclick="window.dispatchEvent(new CustomEvent('pn:cookie-settings:open'));"
					>
						<?php esc_html_e( 'Apri impostazioni cookie', 'pharmanow' ); ?>
					</button>
				</div>
			</div>

			<section>
				<h2 class="text-xl font-bold text-gray-900 mb-6"><?php esc_html_e( 'Tipi di Cookie utilizzati', 'pharmanow' ); ?></h2>
				<div class="space-y-6">
					<?php foreach ( $pn_cookie_types as $pn_type ) : ?>
						<div class="border border-gray-200 rounded-xl overflow-hidden">
							<div class="bg-gray-50 px-5 py-4 flex items-center justify-between">
								<h3 class="font-semibold text-gray-900"><?php echo esc_html( $pn_type['name'] ); ?></h3>
								<span class="text-xs font-medium px-3 py-1 rounded-full <?php echo $pn_type['can_disable'] ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'; ?>">
									<?php echo $pn_type['can_disable'] ? esc_html__( 'Disattivabile', 'pharmanow' ) : esc_html__( 'Sempre attivo', 'pharmanow' ); ?>
								</span>
							</div>
							<div class="p-5">
								<p class="text-gray-600 text-sm mb-4"><?php echo esc_html( $pn_type['description'] ); ?></p>
								<div class="overflow-x-auto">
									<table class="w-full text-sm">
										<thead>
											<tr class="border-b border-gray-200">
												<th class="text-left py-2 pr-4 font-semibold text-gray-700"><?php esc_html_e( 'Nome', 'pharmanow' ); ?></th>
												<th class="text-left py-2 pr-4 font-semibold text-gray-700"><?php esc_html_e( 'Finalità', 'pharmanow' ); ?></th>
												<th class="text-left py-2 font-semibold text-gray-700"><?php esc_html_e( 'Durata', 'pharmanow' ); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ( $pn_type['examples'] as $pn_ex ) : ?>
												<tr class="border-b border-gray-100 last:border-0">
													<td class="py-2 pr-4 font-mono text-xs text-gray-800"><?php echo esc_html( $pn_ex['name'] ); ?></td>
													<td class="py-2 pr-4 text-gray-600"><?php echo esc_html( $pn_ex['purpose'] ); ?></td>
													<td class="py-2 text-gray-600 whitespace-nowrap"><?php echo esc_html( $pn_ex['duration'] ); ?></td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</section>

			<section>
				<h2 class="text-xl font-bold text-gray-900 mb-4"><?php esc_html_e( 'Cookie di terze parti', 'pharmanow' ); ?></h2>
				<p class="text-gray-600 leading-relaxed mb-4">
					<?php esc_html_e( 'Il nostro sito può contenere cookie impostati da terze parti. Non abbiamo controllo su questi cookie e ti invitiamo a consultare le rispettive informative sulla privacy:', 'pharmanow' ); ?>
				</p>
				<ul class="space-y-2 text-gray-600">
					<?php foreach ( $pn_third_parties as $pn_tp ) : ?>
						<li class="flex items-start gap-2">
							<?php pn_icon( 'chevron-right', array( 'class' => 'w-5 h-5 text-[#0A6E9E] flex-shrink-0 mt-0.5' ) ); ?>
							<span>
								<strong><?php echo esc_html( $pn_tp['name'] ); ?>:</strong>
								<a href="<?php echo esc_url( $pn_tp['url'] ); ?>" target="_blank" rel="noopener noreferrer" class="text-[#0A6E9E] hover:underline">
									<?php esc_html_e( 'Privacy Policy', 'pharmanow' ); ?>
								</a>
							</span>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>

			<section>
				<h2 class="text-xl font-bold text-gray-900 mb-4"><?php esc_html_e( 'Come gestire i cookie dal browser', 'pharmanow' ); ?></h2>
				<p class="text-gray-600 leading-relaxed mb-4">
					<?php esc_html_e( 'Puoi anche gestire i cookie attraverso le impostazioni del tuo browser. Ecco i link alle guide dei principali browser:', 'pharmanow' ); ?>
				</p>
				<div class="grid sm:grid-cols-2 gap-3">
					<?php foreach ( $pn_browsers as $pn_b ) : ?>
						<a href="<?php echo esc_url( $pn_b['url'] ); ?>" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:border-[#0A6E9E] hover:bg-[#0A6E9E]/5 transition-colors">
							<?php pn_icon( 'external-link', array( 'class' => 'w-5 h-5 text-gray-400' ) ); ?>
							<span class="text-gray-700 font-medium"><?php echo esc_html( $pn_b['name'] ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</section>

			<section class="bg-gray-50 rounded-xl p-5">
				<h2 class="font-bold text-gray-900 mb-2"><?php esc_html_e( 'Contatti', 'pharmanow' ); ?></h2>
				<p class="text-gray-600 text-sm leading-relaxed">
					<?php esc_html_e( 'Per qualsiasi domanda relativa a questa Cookie Policy o al trattamento dei tuoi dati, puoi contattarci:', 'pharmanow' ); ?>
				</p>
				<ul class="mt-3 space-y-1 text-sm text-gray-600">
					<li>
						<strong><?php esc_html_e( 'Email:', 'pharmanow' ); ?></strong>
						<a href="mailto:info@thesip.it" class="text-[#0A6E9E] hover:underline">info@thesip.it</a>
					</li>
				</ul>
			</section>

		</div>
	</div>
</div>

<?php
get_footer();
