<?php
/**
 * Footer principale — porting fedele da components/shop/layout/footer.tsx (Next).
 *
 * Sezioni:
 *   1. USP bar (gradient accent, 4 voci)
 *   2. Main footer dark navy (logo+badges, Pharmanow links, Informazioni links, Assistenza)
 *   3. Payment + Shipping + SSL strip
 *   4. Payment methods strip (icone svg)
 *   5. Bottom bar (copyright + dati legali)
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn_settings   = pn_shop_settings();
$pn_email      = 'info@thesip.it';
$pn_instagram  = '#';

$pn_usp_items = array(
	array(
		'icon'  => 'map-pin',
		'label' => __( 'Progetto nato a Napoli', 'pharmanow' ),
		'sub'   => __( 'Biologia marina illustrata', 'pharmanow' ),
	),
	array(
		'icon'  => 'zap',
		'label' => __( 'Spedizione 24/48h', 'pharmanow' ),
		'sub'   => __( 'In tutta Italia', 'pharmanow' ),
	),
	array(
		'icon'  => 'truck',
		'label' => sprintf(
			/* translators: %s = importo soglia */
			__( 'Gratis sopra €%s', 'pharmanow' ),
			number_format( (float) $pn_settings['free_shipping_threshold'], 2, ',', '.' )
		),
		'sub'   => __( 'Nessun minimo ordine', 'pharmanow' ),
	),
	array(
		'icon'  => 'credit-card',
		'label' => __( 'Pagamenti Sicuri', 'pharmanow' ),
		'sub'   => __( 'Visa, Mastercard, PayPal', 'pharmanow' ),
	),
);

$pn_pharmanow_links = array(
	array( 'label' => __( 'Chi siamo', 'pharmanow' ), 'href' => home_url( '/chi-siamo' ) ),
	array( 'label' => __( 'FAQ', 'pharmanow' ),       'href' => home_url( '/faq' ) ),
	array( 'label' => __( 'Contatti', 'pharmanow' ),  'href' => home_url( '/contatti' ) ),
);

$pn_info_links = array(
	array( 'label' => __( 'Spedizioni e Resi', 'pharmanow' ),    'href' => home_url( '/faq' ) ),
	array( 'label' => __( 'Traccia Ordine', 'pharmanow' ),       'href' => home_url( '/traccia-ordine' ) ),
	array( 'label' => __( 'Privacy Policy', 'pharmanow' ),       'href' => home_url( '/legale/privacy' ) ),
	array( 'label' => __( 'Termini e Condizioni', 'pharmanow' ), 'href' => home_url( '/legale/termini' ) ),
	array( 'label' => __( 'Cookie Policy', 'pharmanow' ),        'href' => home_url( '/legale/cookie' ) ),
	array( 'label' => __( 'Gestisci cookie', 'pharmanow' ),      'href' => '#', 'icon' => 'cookie', 'attrs' => 'onclick="event.preventDefault();window.dispatchEvent(new CustomEvent(\'pn:open-cookie-prefs\'))"' ),
);
?>

<footer class="mt-16">

	<!-- ── USP Bar ── -->
	<div class="pharma-accent-bg">
		<div class="container max-w-7xl mx-auto px-4 py-5">
			<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
				<?php foreach ( $pn_usp_items as $pn_usp ) : ?>
					<div class="flex items-center gap-3 text-white">
						<div class="rounded-full bg-white/15 p-2.5 shrink-0">
							<?php pn_icon( $pn_usp['icon'], array( 'class' => 'h-5 w-5' ) ); ?>
						</div>
						<div>
							<p class="text-sm font-semibold leading-tight"><?php echo esc_html( $pn_usp['label'] ); ?></p>
							<p class="text-xs text-white/70 mt-0.5"><?php echo esc_html( $pn_usp['sub'] ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<!-- ── Main footer · dark navy ── -->
	<div class="bg-[#0f172a] text-white">
		<div class="container max-w-7xl mx-auto px-4 py-12">
			<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-8 lg:gap-6">

				<!-- Col 1 — Logo + Description + Carriers (4 cols) -->
				<div class="lg:col-span-4">
					<img
						src="<?php echo esc_url( pn_asset( 'images/logo-thesip-white.svg' ) ); ?>"
						alt="The SIP"
						width="180"
						height="36"
						loading="lazy"
						decoding="async"
						class="h-9 w-auto mb-4"
					>
					<p class="text-sm text-gray-400 leading-relaxed mb-5">
						<?php esc_html_e( 'Il mare in tasca. 31 flashcard illustrate di biologia marina, nate a Napoli da due biologi marini e 25 illustratori. Take a seat and take a SIP!', 'pharmanow' ); ?>
					</p>

					<!-- Corrieri -->
					<div class="mt-4">
						<p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-gray-500"><?php esc_html_e( 'Corrieri', 'pharmanow' ); ?></p>
						<div class="flex items-center gap-2">
							<?php if ( in_array( 'FedEx', $pn_settings['carriers'], true ) ) : ?>
								<div class="flex h-8 w-14 items-center justify-center rounded border border-gray-700 bg-white p-1">
									<img src="<?php echo esc_url( pn_asset( 'images/shipping/fedex.jpg' ) ); ?>" alt="FedEx" width="80" height="32" class="h-auto max-h-full w-auto max-w-full object-contain" loading="lazy" decoding="async">
								</div>
							<?php endif; ?>
							<?php if ( in_array( 'GLS', $pn_settings['carriers'], true ) ) : ?>
								<div class="flex h-8 w-14 items-center justify-center rounded border border-gray-700 bg-white p-1">
									<img src="<?php echo esc_url( pn_asset( 'images/shipping/gls.png' ) ); ?>" alt="GLS" width="80" height="32" class="h-auto max-h-full w-auto max-w-full object-contain" loading="lazy" decoding="async">
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>

				<!-- Col 2 — Il progetto links (2 cols) -->
				<div class="lg:col-span-2">
					<h4 class="text-sm font-semibold uppercase tracking-wider mb-4 text-white"><?php esc_html_e( 'Il progetto', 'pharmanow' ); ?></h4>
					<ul class="space-y-2.5">
						<?php foreach ( $pn_pharmanow_links as $pn_lk ) : ?>
							<li>
								<a href="<?php echo esc_url( $pn_lk['href'] ); ?>" data-slot="button" class="inline-block text-sm text-gray-400 hover:text-pharma-accent-light transition-colors">
									<?php echo pn_slide_text( '<span>' . esc_html( $pn_lk['label'] ) . '</span>' ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

				<!-- Col 3 — Informazioni links (2 cols) -->
				<div class="lg:col-span-2">
					<h4 class="text-sm font-semibold uppercase tracking-wider mb-4 text-white"><?php esc_html_e( 'Assistenza', 'pharmanow' ); ?></h4>
					<ul class="space-y-2.5">
						<?php foreach ( $pn_info_links as $pn_lk ) : ?>
							<li>
								<a href="<?php echo esc_url( $pn_lk['href'] ); ?>" <?php if ( ! empty( $pn_lk['attrs'] ) ) { echo $pn_lk['attrs']; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ } ?> data-slot="button" class="inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-pharma-accent-light transition-colors">
									<?php
									$pn_inner = '';
									if ( ! empty( $pn_lk['icon'] ) ) {
										$pn_inner .= pn_icon_string( $pn_lk['icon'], array( 'class' => 'h-4 w-4' ) );
									}
									$pn_inner .= '<span>' . esc_html( $pn_lk['label'] ) . '</span>';
									echo pn_slide_text( $pn_inner ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

				<!-- Col 4 — Contatti (4 cols) -->
				<div class="lg:col-span-4">
					<h4 class="text-sm font-semibold uppercase tracking-wider mb-4 text-white"><?php esc_html_e( 'Contatti', 'pharmanow' ); ?></h4>

					<div class="space-y-4">
						<!-- Email -->
						<a href="mailto:<?php echo esc_attr( $pn_email ); ?>" class="flex items-start gap-3 group">
							<div class="rounded-full border border-gray-700 p-2.5 group-hover:border-pharma-accent-light group-hover:bg-pharma-accent-light/10 transition-colors shrink-0">
								<?php pn_icon( 'mail', array( 'class' => 'h-4 w-4 text-pharma-accent-light' ) ); ?>
							</div>
							<div>
								<p class="text-sm font-semibold text-white group-hover:text-pharma-accent-light transition-colors">
									<?php echo esc_html( $pn_email ); ?>
								</p>
								<p class="text-xs text-gray-500 mt-0.5"><?php esc_html_e( 'Ordini, info e assistenza', 'pharmanow' ); ?></p>
							</div>
						</a>

						<!-- Sede -->
						<div class="flex items-start gap-3">
							<div class="rounded-full border border-gray-700 p-2.5 shrink-0">
								<?php pn_icon( 'map-pin', array( 'class' => 'h-4 w-4 text-pharma-accent-light' ) ); ?>
							</div>
							<div>
								<p class="text-sm font-semibold text-white"><?php esc_html_e( 'Napoli', 'pharmanow' ); ?></p>
								<p class="text-xs text-gray-500 mt-0.5"><?php esc_html_e( 'Consegna a mano gratuita in città', 'pharmanow' ); ?></p>
							</div>
						</div>

						<!-- Social -->
						<div class="flex items-center gap-2 pt-1">
							<a
								href="<?php echo esc_url( $pn_instagram ); ?>"
								target="_blank"
								rel="noopener noreferrer"
								aria-label="<?php esc_attr_e( 'Instagram', 'pharmanow' ); ?>"
								class="rounded-lg border border-gray-700 p-2 hover:border-pharma-accent-light hover:bg-pharma-accent-light/10 transition-colors text-gray-400 hover:text-pharma-accent-light"
							>
								<?php pn_icon( 'social/instagram', array( 'class' => 'h-4 w-4' ) ); ?>
							</a>
						</div>
					</div>
				</div>
			</div>

			<!-- ── SSL trust strip ── -->
			<div class="mt-10 pt-6 border-t border-gray-800 flex items-center justify-end">
				<div class="flex items-center gap-1.5 text-xs text-gray-500">
					<?php pn_icon( 'shield-check', array( 'class' => 'h-3.5 w-3.5 text-green-500' ) ); ?>
					<?php esc_html_e( 'Connessione SSL crittografata', 'pharmanow' ); ?>
				</div>
			</div>
		</div>

		<!-- ── Payment methods strip (icone svg, no wrapper) ── -->
		<div class="border-t border-gray-800 bg-[#0a0f1a]">
			<div class="container mx-auto flex max-w-7xl flex-wrap items-center justify-center gap-3 px-4 py-4 sm:justify-start">
				<span class="text-[11px] uppercase tracking-wider text-gray-500"><?php esc_html_e( 'Metodi di pagamento accettati:', 'pharmanow' ); ?></span>
				<?php
				$pn_payment_assets = array(
					'visa.svg'             => 'Visa',
					'mastercard.svg'       => 'Mastercard',
					'maestro.svg'          => 'Maestro',
					'american-express.svg' => 'American Express',
					'paypal.svg'           => 'PayPal',
					'apple-pay.svg'        => 'Apple Pay',
					'google-pay.svg'       => 'Google Pay',
				);
				foreach ( $pn_payment_assets as $pn_file => $pn_alt ) :
					?>
					<img
						src="<?php echo esc_url( pn_asset( "images/payment/{$pn_file}" ) ); ?>"
						alt="<?php echo esc_attr( $pn_alt ); ?>"
						width="48"
						height="24"
						loading="lazy"
						decoding="async"
						class="h-6 w-auto"
					>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- ── Bottom bar ── -->
		<div class="bg-[#0a0f1a] border-t border-gray-800">
			<div class="container max-w-7xl mx-auto px-4 py-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500">
				<p>
					<?php
					/* translators: %s = anno corrente */
					printf( esc_html__( '© %s The SIP — The Sea In your Pocket', 'pharmanow' ), esc_html( gmdate( 'Y' ) ) );
					?>
				</p>
				<p class="text-center sm:text-right">
					<?php echo esc_html( 'The SIP · Napoli · ' . $pn_email ); ?>
				</p>
			</div>
		</div>
	</div>
</footer>
