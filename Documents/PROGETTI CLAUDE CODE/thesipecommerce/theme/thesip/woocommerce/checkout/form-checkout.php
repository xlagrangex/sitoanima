<?php
/**
 * Checkout form — replica fedele del checkout Next (3 sezioni accordion + sidebar).
 * Layout 2-col [680px / 1fr], stato accordion gestito da Alpine.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form_cart_notices' );

if ( ! is_user_logged_in() && WC()->checkout()->is_registration_required() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'Devi essere loggato per completare l\'ordine.', 'pharmanow' ) ) );
	return;
}

// Lista province italiane (subset principale).
$pn_provinces = array(
	'AG' => 'Agrigento','AL' => 'Alessandria','AN' => 'Ancona','AO' => 'Aosta','AR' => 'Arezzo','AP' => 'Ascoli Piceno','AT' => 'Asti','AV' => 'Avellino',
	'BA' => 'Bari','BT' => 'Barletta-Andria-Trani','BL' => 'Belluno','BN' => 'Benevento','BG' => 'Bergamo','BI' => 'Biella','BO' => 'Bologna','BZ' => 'Bolzano','BS' => 'Brescia','BR' => 'Brindisi',
	'CA' => 'Cagliari','CL' => 'Caltanissetta','CB' => 'Campobasso','CI' => 'Carbonia-Iglesias','CE' => 'Caserta','CT' => 'Catania','CZ' => 'Catanzaro','CH' => 'Chieti','CO' => 'Como','CS' => 'Cosenza','CR' => 'Cremona','KR' => 'Crotone','CN' => 'Cuneo',
	'EN' => 'Enna','FM' => 'Fermo','FE' => 'Ferrara','FI' => 'Firenze','FG' => 'Foggia','FC' => 'Forlì-Cesena','FR' => 'Frosinone',
	'GE' => 'Genova','GO' => 'Gorizia','GR' => 'Grosseto',
	'IM' => 'Imperia','IS' => 'Isernia','SP' => 'La Spezia','AQ' => "L'Aquila",'LT' => 'Latina','LE' => 'Lecce','LC' => 'Lecco','LI' => 'Livorno','LO' => 'Lodi','LU' => 'Lucca',
	'MC' => 'Macerata','MN' => 'Mantova','MS' => 'Massa-Carrara','MT' => 'Matera','ME' => 'Messina','MI' => 'Milano','MO' => 'Modena','MB' => 'Monza e Brianza',
	'NA' => 'Napoli','NO' => 'Novara','NU' => 'Nuoro','OR' => 'Oristano',
	'PD' => 'Padova','PA' => 'Palermo','PR' => 'Parma','PV' => 'Pavia','PG' => 'Perugia','PU' => 'Pesaro e Urbino','PE' => 'Pescara','PC' => 'Piacenza','PI' => 'Pisa','PT' => 'Pistoia','PN' => 'Pordenone','PZ' => 'Potenza','PO' => 'Prato',
	'RG' => 'Ragusa','RA' => 'Ravenna','RC' => 'Reggio Calabria','RE' => 'Reggio Emilia','RI' => 'Rieti','RN' => 'Rimini','RM' => 'Roma','RO' => 'Rovigo',
	'SA' => 'Salerno','SS' => 'Sassari','SV' => 'Savona','SI' => 'Siena','SR' => 'Siracusa','SO' => 'Sondrio','SU' => 'Sud Sardegna',
	'TA' => 'Taranto','TE' => 'Teramo','TR' => 'Terni','TO' => 'Torino','TP' => 'Trapani','TN' => 'Trento','TV' => 'Treviso','TS' => 'Trieste',
	'UD' => 'Udine','VA' => 'Varese','VE' => 'Venezia','VB' => 'Verbano-Cusio-Ossola','VC' => 'Vercelli','VR' => 'Verona','VV' => 'Vibo Valentia','VI' => 'Vicenza','VT' => 'Viterbo',
);
?>

<form name="checkout" method="post" class="checkout woocommerce-checkout pn-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php esc_attr_e( 'Modulo di pagamento', 'pharmanow' ); ?>"
	x-data="pnCheckout()"
	novalidate
>

	<div class="pn-checkout__container">

		<?php
		// Pay-for-order Shopify-style: se siamo qui via /order-pay/ link, mostra
		// una headline personalizzata "Ciao {Nome}, completa l'ordine".
		$pn_guardian_order = function_exists( 'pn_pay_for_order_get_guardian' ) ? pn_pay_for_order_get_guardian() : null;
		if ( $pn_guardian_order instanceof WC_Order ) :
			$pn_pay_first_name = trim( (string) $pn_guardian_order->get_billing_first_name() );
			?>
			<div class="pn-pay-headline mb-6 md:mb-8 text-center">
				<h1 class="text-2xl md:text-3xl font-bold tracking-tight text-gray-900">
					<?php
					if ( $pn_pay_first_name ) {
						printf(
							/* translators: %s = nome cliente */
							esc_html__( 'Ciao %s, completa l\'ordine', 'pharmanow' ),
							esc_html( $pn_pay_first_name )
						);
					} else {
						esc_html_e( 'Completa l\'ordine', 'pharmanow' );
					}
					?>
				</h1>
				<p class="mt-2 text-sm text-gray-500">
					<?php
					printf(
						/* translators: %s = numero ordine */
						esc_html__( 'Ordine #%s · prodotti e indirizzo già pre-compilati. Modifica se necessario, poi paga.', 'pharmanow' ),
						esc_html( $pn_guardian_order->get_order_number() )
					);
					?>
				</p>
			</div>
		<?php endif; ?>

		<?php
		// Hook ufficiale del plugin WooCommerce Stripe Gateway v10.3:
		// `woocommerce_checkout_before_customer_details` → display_express_checkout_button_html()
		// Genera Apple Pay, Google Pay, Link. Renderizziamo il markup ora,
		// lo iniettiamo nella colonna SINISTRA del grid (sopra le 3 sezioni)
		// così la sidebar destra (Order Summary) resta visibile da subito.
		ob_start();
		do_action( 'woocommerce_checkout_before_customer_details' );

		// Force-render PayPal Express buttons del plugin Payment Plugins for PayPal:
		// l'hook `woocommerce_checkout_before_customer_details` a volte non triggera
		// nel template custom, chiamiamo direttamente il PaymentButtonController.
		if ( class_exists( '\PaymentPlugins\WooCommerce\PPCP\Main' )
			&& class_exists( '\PaymentPlugins\WooCommerce\PPCP\PaymentButtonController' )
		) {
			try {
				$container = \PaymentPlugins\WooCommerce\PPCP\Main::container();
				if ( $container ) {
					$ppcp_btn_ctrl = $container->get( \PaymentPlugins\WooCommerce\PPCP\PaymentButtonController::class );
					if ( $ppcp_btn_ctrl && method_exists( $ppcp_btn_ctrl, 'render_express_buttons' ) ) {
						$ppcp_btn_ctrl->render_express_buttons();
					}
				}
			} catch ( \Throwable $e ) {
				// Plugin non pronto: ignora, lascia solo Stripe.
			}
		}

		$pn_express_top_html = ob_get_clean();
		$pn_has_express      = ! empty( trim( $pn_express_top_html ) );
		?>

		<div class="pn-checkout__grid">

			<!-- ============ COLONNA SX (Express + Form) ============ -->
			<div class="pn-checkout__left">

				<?php if ( $pn_has_express && ! empty( trim( $pn_express_top_html ) ) ) : ?>
					<!-- BOX 1: Express Checkout -->
					<div class="pn-checkout__express-box">
						<p class="pn-checkout__express-title"><?php esc_html_e( 'Checkout rapido', 'pharmanow' ); ?></p>
						<?php echo $pn_express_top_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>

					<!-- Divisore tra i due box -->
					<div class="pn-checkout__between-divider">
						<span><?php esc_html_e( 'oppure compila qui sotto', 'pharmanow' ); ?></span>
					</div>
				<?php endif; ?>

				<!-- BOX 2: Form 3 sezioni -->
				<div class="pn-checkout__main">

				<!-- ===== SEZIONE 1: Contatto & Indirizzo ===== -->
				<section class="pn-co-sec" :class="{ 'is-active': step === 1, 'is-completed': completed.includes(1) }">
					<button type="button" class="pn-co-sec__head" @click="toggleSection(1)" :aria-expanded="step === 1">
						<span class="pn-co-sec__num" :class="{ 'is-active': step === 1, 'is-completed': completed.includes(1) }">
							<template x-if="completed.includes(1) && step !== 1">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
							</template>
							<template x-if="!completed.includes(1) || step === 1">
								<span>1</span>
							</template>
						</span>
						<div class="pn-co-sec__title">
							<span><?php esc_html_e( 'Contatto & Indirizzo', 'pharmanow' ); ?></span>
							<p class="pn-co-sec__summary" x-show="completed.includes(1) && step !== 1" x-text="contactSummary"></p>
						</div>
						<span class="pn-co-sec__edit" x-show="completed.includes(1) && step !== 1">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
							<?php esc_html_e( 'Modifica', 'pharmanow' ); ?>
						</span>
					</button>

					<div class="pn-co-sec__body" x-show="step === 1" x-collapse>

						<!-- Email -->
						<div class="pn-co-field">
							<label for="billing_email"><?php esc_html_e( 'Email', 'pharmanow' ); ?> <span class="pn-required">*</span></label>
							<input id="billing_email" name="billing_email" type="email" inputmode="email" autocomplete="email" required value="<?php echo esc_attr( WC()->checkout->get_value( 'billing_email' ) ); ?>" class="pn-input">
						</div>

						<!-- Nome / Cognome -->
						<div class="pn-co-row">
							<div class="pn-co-field">
								<label for="billing_first_name"><?php esc_html_e( 'Nome', 'pharmanow' ); ?> <span class="pn-required">*</span></label>
								<input id="billing_first_name" name="billing_first_name" type="text" autocomplete="given-name" required value="<?php echo esc_attr( WC()->checkout->get_value( 'billing_first_name' ) ); ?>" class="pn-input">
							</div>
							<div class="pn-co-field">
								<label for="billing_last_name"><?php esc_html_e( 'Cognome', 'pharmanow' ); ?> <span class="pn-required">*</span></label>
								<input id="billing_last_name" name="billing_last_name" type="text" autocomplete="family-name" required value="<?php echo esc_attr( WC()->checkout->get_value( 'billing_last_name' ) ); ?>" class="pn-input">
							</div>
						</div>

						<!-- Telefono -->
						<div class="pn-co-field">
							<label for="billing_phone"><?php esc_html_e( 'Telefono', 'pharmanow' ); ?> <span class="pn-required">*</span></label>
							<input id="billing_phone" name="billing_phone" type="tel" inputmode="tel" autocomplete="tel" required value="<?php echo esc_attr( WC()->checkout->get_value( 'billing_phone' ) ); ?>" class="pn-input">
						</div>

						<!-- Indirizzo -->
						<div class="pn-co-row">
							<div class="pn-co-field">
								<label for="billing_address_1"><?php esc_html_e( 'Via e numero', 'pharmanow' ); ?> <span class="pn-required">*</span></label>
								<input id="billing_address_1" name="billing_address_1" type="text" autocomplete="address-line1" required value="<?php echo esc_attr( WC()->checkout->get_value( 'billing_address_1' ) ); ?>" class="pn-input">
							</div>
							<div class="pn-co-field">
								<label for="billing_address_2"><?php esc_html_e( 'Interno / Scala', 'pharmanow' ); ?></label>
								<input id="billing_address_2" name="billing_address_2" type="text" autocomplete="address-line2" value="<?php echo esc_attr( WC()->checkout->get_value( 'billing_address_2' ) ); ?>" class="pn-input">
							</div>
						</div>

						<div class="pn-co-row pn-co-row--3">
							<div class="pn-co-field">
								<label for="billing_city"><?php esc_html_e( 'Città', 'pharmanow' ); ?> <span class="pn-required">*</span></label>
								<input id="billing_city" name="billing_city" type="text" autocomplete="address-level2" required value="<?php echo esc_attr( WC()->checkout->get_value( 'billing_city' ) ); ?>" class="pn-input">
							</div>
							<div class="pn-co-field">
								<label for="billing_state"><?php esc_html_e( 'Provincia', 'pharmanow' ); ?> <span class="pn-required">*</span></label>
								<select id="billing_state" name="billing_state" required class="pn-input">
									<option value="">--</option>
									<?php
									$pn_current_state = WC()->checkout->get_value( 'billing_state' );
									foreach ( $pn_provinces as $code => $name ) {
										printf( '<option value="%s" %s>%s — %s</option>', esc_attr( $code ), selected( $pn_current_state, $code, false ), esc_html( $code ), esc_html( $name ) );
									}
									?>
								</select>
							</div>
							<div class="pn-co-field">
								<label for="billing_postcode"><?php esc_html_e( 'CAP', 'pharmanow' ); ?> <span class="pn-required">*</span></label>
								<input id="billing_postcode" name="billing_postcode" type="text" inputmode="numeric" autocomplete="postal-code" maxlength="5" required value="<?php echo esc_attr( WC()->checkout->get_value( 'billing_postcode' ) ); ?>" class="pn-input pn-input--mono">
							</div>
						</div>

						<!-- Country fisso IT — id+class richiesti dal plugin Stripe per leggere il
						     billing_details.address.country quando crea il PaymentMethod (sennò
						     errore "did not pass params.billing_details.address.country"). -->
						<input type="hidden" id="billing_country" name="billing_country" value="IT" class="country_to_state country_select">

						<!-- Spedisci a indirizzo diverso -->
						<label class="pn-co-checkbox">
							<input type="checkbox" name="ship_to_different_address" value="1" x-model="shipToDifferent">
							<span><?php esc_html_e( 'Spedisci a un indirizzo diverso', 'pharmanow' ); ?></span>
						</label>

						<div class="pn-co-shipto" x-show="shipToDifferent" x-collapse>
							<p class="pn-co-shipto__title"><?php esc_html_e( 'Indirizzo di spedizione', 'pharmanow' ); ?></p>
							<!-- Nome / Cognome destinatario — obbligatori lato WC quando "spedisci a
							     indirizzo diverso" è attivo. Senza questi campi il checkout viene
							     bocciato con un errore su campi che il cliente non vede mai. -->
							<div class="pn-co-row">
								<div class="pn-co-field">
									<label for="shipping_first_name"><?php esc_html_e( 'Nome', 'pharmanow' ); ?></label>
									<input id="shipping_first_name" name="shipping_first_name" type="text" autocomplete="shipping given-name" value="<?php echo esc_attr( WC()->checkout->get_value( 'shipping_first_name' ) ); ?>" class="pn-input">
								</div>
								<div class="pn-co-field">
									<label for="shipping_last_name"><?php esc_html_e( 'Cognome', 'pharmanow' ); ?></label>
									<input id="shipping_last_name" name="shipping_last_name" type="text" autocomplete="shipping family-name" value="<?php echo esc_attr( WC()->checkout->get_value( 'shipping_last_name' ) ); ?>" class="pn-input">
								</div>
							</div>
							<div class="pn-co-field">
								<label for="shipping_address_1"><?php esc_html_e( 'Via e numero', 'pharmanow' ); ?></label>
								<input id="shipping_address_1" name="shipping_address_1" type="text" value="<?php echo esc_attr( WC()->checkout->get_value( 'shipping_address_1' ) ); ?>" class="pn-input">
							</div>
							<div class="pn-co-row pn-co-row--3">
								<div class="pn-co-field">
									<label for="shipping_city"><?php esc_html_e( 'Città', 'pharmanow' ); ?></label>
									<input id="shipping_city" name="shipping_city" type="text" value="<?php echo esc_attr( WC()->checkout->get_value( 'shipping_city' ) ); ?>" class="pn-input">
								</div>
								<div class="pn-co-field">
									<label for="shipping_state"><?php esc_html_e( 'Provincia', 'pharmanow' ); ?></label>
									<select id="shipping_state" name="shipping_state" class="pn-input">
										<option value="">--</option>
										<?php
										$pn_current_ship_state = WC()->checkout->get_value( 'shipping_state' );
										foreach ( $pn_provinces as $code => $name ) {
											printf( '<option value="%s" %s>%s</option>', esc_attr( $code ), selected( $pn_current_ship_state, $code, false ), esc_html( $code ) );
										}
										?>
									</select>
								</div>
								<div class="pn-co-field">
									<label for="shipping_postcode"><?php esc_html_e( 'CAP', 'pharmanow' ); ?></label>
									<input id="shipping_postcode" name="shipping_postcode" type="text" inputmode="numeric" maxlength="5" value="<?php echo esc_attr( WC()->checkout->get_value( 'shipping_postcode' ) ); ?>" class="pn-input pn-input--mono">
								</div>
							</div>
							<input type="hidden" name="shipping_country" value="IT">
						</div>

						<!-- Note ordine -->
						<div class="pn-co-field">
							<label for="order_comments"><?php esc_html_e( 'Note per l\'ordine (facoltativo)', 'pharmanow' ); ?></label>
							<textarea id="order_comments" name="order_comments" rows="3" placeholder="<?php esc_attr_e( 'Indica qui la lingua delle carte (IT o EN) e, se sei a Napoli, se preferisci la consegna a mano.', 'pharmanow' ); ?>" class="pn-input pn-textarea"><?php echo esc_textarea( WC()->checkout->get_value( 'order_comments' ) ); ?></textarea>
							<p class="pn-co-field__hint" style="margin-top:6px;font-size:12px;color:#64748b;"><?php esc_html_e( 'Le carte sono disponibili in italiano (IT) o inglese (EN): scrivi la tua scelta qui sopra.', 'pharmanow' ); ?></p>
						</div>

						<!-- Documento fiscale -->
						<div class="pn-co-invoice">
							<h3 class="pn-co-invoice__title"><?php esc_html_e( 'Documento fiscale', 'pharmanow' ); ?></h3>
							<div class="pn-co-invoice__list">

								<label class="pn-co-invoice__item" :class="{ 'is-active': invoiceType === 'scontrino' }">
									<input type="radio" name="pn_invoice_type" value="scontrino" x-model="invoiceType">
									<div class="pn-co-invoice__body">
										<span class="pn-co-invoice__label"><?php esc_html_e( 'Scontrino fiscale', 'pharmanow' ); ?></span>
									</div>
								</label>

								<label class="pn-co-invoice__item" :class="{ 'is-active': invoiceType === 'scontrino_cf' }">
									<input type="radio" name="pn_invoice_type" value="scontrino_cf" x-model="invoiceType">
									<div class="pn-co-invoice__body">
										<span class="pn-co-invoice__label"><?php esc_html_e( 'Scontrino con codice fiscale', 'pharmanow' ); ?></span>
										<p class="pn-co-invoice__desc"><?php esc_html_e( 'Per la detrazione fiscale', 'pharmanow' ); ?></p>
										<div class="pn-co-invoice__expand" x-show="invoiceType === 'scontrino_cf'" x-collapse>
											<input type="text" name="pn_codice_fiscale" placeholder="RSSMRA85M01H501Z" maxlength="16" class="pn-input pn-input--mono pn-input--upper" value="<?php echo esc_attr( WC()->checkout->get_value( 'pn_codice_fiscale' ) ); ?>">
										</div>
									</div>
								</label>

								<label class="pn-co-invoice__item" :class="{ 'is-active': invoiceType === 'fattura' }">
									<input type="radio" name="pn_invoice_type" value="fattura" x-model="invoiceType">
									<div class="pn-co-invoice__body">
										<span class="pn-co-invoice__label"><?php esc_html_e( 'Fattura', 'pharmanow' ); ?></span>
										<div class="pn-co-invoice__expand" x-show="invoiceType === 'fattura'" x-collapse>
											<div class="pn-co-field">
												<label><?php esc_html_e( 'Ragione sociale', 'pharmanow' ); ?> <span class="pn-required">*</span></label>
												<input type="text" name="pn_ragione_sociale" class="pn-input" value="<?php echo esc_attr( WC()->checkout->get_value( 'pn_ragione_sociale' ) ); ?>">
											</div>
											<div class="pn-co-row">
												<div class="pn-co-field">
													<label><?php esc_html_e( 'Partita IVA', 'pharmanow' ); ?> <span class="pn-required">*</span></label>
													<input type="text" name="pn_partita_iva" maxlength="11" inputmode="numeric" class="pn-input pn-input--mono" value="<?php echo esc_attr( WC()->checkout->get_value( 'pn_partita_iva' ) ); ?>">
												</div>
												<div class="pn-co-field">
													<label><?php esc_html_e( 'Codice fiscale', 'pharmanow' ); ?> <span class="pn-required">*</span></label>
													<input type="text" name="pn_cf_azienda" maxlength="16" class="pn-input pn-input--mono pn-input--upper" value="<?php echo esc_attr( WC()->checkout->get_value( 'pn_cf_azienda' ) ); ?>">
												</div>
											</div>
											<div class="pn-co-field">
												<label><?php esc_html_e( 'Codice univoco / PEC', 'pharmanow' ); ?> <span class="pn-required">*</span></label>
												<input type="text" name="pn_sdi_pec" placeholder="0000000 oppure pec@example.it" class="pn-input" value="<?php echo esc_attr( WC()->checkout->get_value( 'pn_sdi_pec' ) ); ?>">
											</div>
											<div class="pn-co-field">
												<label><?php esc_html_e( 'Email copia fattura (facoltativo)', 'pharmanow' ); ?></label>
												<input type="email" name="pn_email_fattura" placeholder="fattura@azienda.it" class="pn-input" value="<?php echo esc_attr( WC()->checkout->get_value( 'pn_email_fattura' ) ); ?>">
											</div>
										</div>
									</div>
								</label>

							</div>
						</div>

						<button type="button" class="pn-btn-gradient pn-co-cta" @click.prevent.stop="completeStep(1)">
							<span class="pn-btn-slide">
								<span class="pn-btn-slide__text"><?php esc_html_e( 'Continua alla spedizione', 'pharmanow' ); ?></span>
								<span class="pn-btn-slide__clone" aria-hidden="true"><?php esc_html_e( 'Continua alla spedizione', 'pharmanow' ); ?></span>
							</span>
						</button>
					</div>
				</section>

				<!-- ===== SEZIONE 2: Spedizione e Termini ===== -->
				<section class="pn-co-sec" :class="{ 'is-active': step === 2, 'is-completed': completed.includes(2), 'is-locked': !completed.includes(1) }">
					<button type="button" class="pn-co-sec__head" @click="toggleSection(2)" :aria-expanded="step === 2" :disabled="!completed.includes(1)">
						<span class="pn-co-sec__num" :class="{ 'is-active': step === 2, 'is-completed': completed.includes(2) }">
							<template x-if="completed.includes(2) && step !== 2">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
							</template>
							<template x-if="!completed.includes(2) || step === 2">
								<span>2</span>
							</template>
						</span>
						<div class="pn-co-sec__title">
							<span><?php esc_html_e( 'Spedizione e Termini', 'pharmanow' ); ?></span>
							<p class="pn-co-sec__summary" x-show="completed.includes(2) && step !== 2" x-text="shippingSummary"></p>
						</div>
					</button>

					<div class="pn-co-sec__body" x-show="step === 2" x-collapse>

						<!-- Shipping methods -->
						<?php get_template_part( 'template-parts/checkout/shipping-methods' ); ?>

						<!-- Carrier logos -->
						<div class="pn-co-carriers">
							<span class="pn-co-carriers__label"><?php esc_html_e( 'Spediamo con', 'pharmanow' ); ?></span>
							<img src="<?php echo esc_url( pn_asset( 'images/shipping/gls.png' ) ); ?>" alt="GLS" width="80" height="24" loading="lazy" decoding="async" class="pn-co-carriers__logo">
							<img src="<?php echo esc_url( pn_asset( 'images/shipping/fedex.jpg' ) ); ?>" alt="FedEx" width="80" height="24" loading="lazy" decoding="async" class="pn-co-carriers__logo">
						</div>

						<!-- Free shipping bar -->
						<?php
						$pn_threshold_co = (float) get_theme_mod( 'pn_free_shipping_threshold', 29.90 );
						$pn_subtotal_co  = (float) WC()->cart->get_displayed_subtotal();
						$pn_remaining_co = max( 0.0, $pn_threshold_co - $pn_subtotal_co );
						$pn_pct_co       = $pn_threshold_co > 0 ? min( 100.0, ( $pn_subtotal_co / $pn_threshold_co ) * 100.0 ) : 0.0;
						$pn_is_free_co   = $pn_subtotal_co >= $pn_threshold_co && $pn_subtotal_co > 0;
						?>
						<div class="pn-co-fsb">
							<div class="pn-co-fsb__row">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 18H3c-.6 0-1-.4-1-1V7c0-.6.4-1 1-1h10c.6 0 1 .4 1 1v11"/><path d="M14 9h4l4 4v4c0 .6-.4 1-1 1h-2"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/></svg>
								<p>
									<?php if ( $pn_is_free_co ) : ?>
										<?php esc_html_e( 'Hai la spedizione', 'pharmanow' ); ?> <strong style="color:#059669"><?php esc_html_e( 'GRATUITA!', 'pharmanow' ); ?></strong>
									<?php else : ?>
										<?php esc_html_e( 'Mancano', 'pharmanow' ); ?> <strong>€<?php echo esc_html( number_format( $pn_remaining_co, 2, ',', '.' ) ); ?></strong> <?php esc_html_e( 'per la spedizione GRATUITA', 'pharmanow' ); ?>
									<?php endif; ?>
								</p>
							</div>
							<div class="pn-co-fsb__track">
								<div class="pn-co-fsb__bar" style="width:<?php echo esc_attr( (string) $pn_pct_co ); ?>%"></div>
							</div>
						</div>

						<!-- Legal sections — UNIFICATE in 1 box scrollabile + 3 checkbox abilitati a fine scroll -->
						<?php
						$pn_terms_url   = home_url( '/legale/termini' );
						$pn_privacy_url = get_privacy_policy_url() ?: home_url( '/legale/privacy' );
						?>
						<div class="pn-co-legal-unified" data-pn-legal-root>

							<div class="pn-co-legal-unified__wrap" data-pn-legal-wrap>
								<!-- Header interno: toggle accordion + quickscroll -->
								<header class="pn-co-legal-unified__header">
									<button type="button" class="pn-co-legal-unified__toggle" data-pn-legal-toggle aria-expanded="true">
										<svg class="pn-co-legal-unified__toggle-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>
										<span class="pn-co-legal-unified__title-text">
											<span data-pn-legal-title-locked><?php esc_html_e( 'Documenti legali da leggere', 'pharmanow' ); ?></span>
											<span data-pn-legal-title-read style="display:none">✓ <?php esc_html_e( 'Documenti letti', 'pharmanow' ); ?></span>
										</span>
									</button>
									<button type="button" class="pn-co-legal-unified__quickscroll" data-pn-legal-quickscroll>
										<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
										<span><?php esc_html_e( 'Scorri velocemente', 'pharmanow' ); ?></span>
									</button>
								</header>

								<!-- Box scrollabile -->
								<div class="pn-co-legal-unified__collapse" data-pn-legal-collapse>
									<div class="pn-co-legal-unified__box" data-pn-legal-unified data-pn-legal-state="locked">
										<div class="pn-co-legal-unified__inner">
											<h4><?php esc_html_e( 'Termini e Condizioni di vendita', 'pharmanow' ); ?></h4>
											<p><?php esc_html_e( 'Acquistando su The SIP accetti le condizioni di vendita del progetto The Sea In your Pocket: flashcard illustrate di biologia marina realizzate dai nostri artisti e biologi.', 'pharmanow' ); ?></p>
											<p><?php esc_html_e( 'I prezzi sono comprensivi di IVA. La spedizione avviene entro 24-48h lavorative ed è gratuita sopra €30. Hai diritto al recesso entro 14 giorni. Le carte personalizzate del Bundle Deluxe, essendo realizzate su misura, non sono restituibili salvo difetto di prodotto.', 'pharmanow' ); ?></p>
											<p><?php esc_html_e( 'In caso di errore sull\'ordine, contattaci entro 48h. The SIP si riserva di rifiutare ordini sospetti o non conformi alle presenti condizioni.', 'pharmanow' ); ?> <a href="<?php echo esc_url( $pn_terms_url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Leggi tutto', 'pharmanow' ); ?></a>.</p>

											<h4><?php esc_html_e( 'Privacy Policy', 'pharmanow' ); ?></h4>
											<p><?php esc_html_e( 'I tuoi dati personali (nome, indirizzo, email, telefono, codice fiscale) sono trattati per finalità contrattuali, fatturazione, evasione dell\'ordine e adempimenti fiscali. Il titolare del trattamento è The SIP.', 'pharmanow' ); ?></p>
											<p><?php esc_html_e( 'I dati vengono conservati per il tempo richiesto dalla normativa fiscale italiana. Hai diritto di accesso, rettifica, cancellazione e portabilità dei tuoi dati. Per esercitarli scrivi a info@thesip.it.', 'pharmanow' ); ?></p>
											<p><?php esc_html_e( 'I dati possono essere condivisi con i corrieri per la spedizione e con il commercialista per la fatturazione.', 'pharmanow' ); ?> <a href="<?php echo esc_url( $pn_privacy_url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Privacy completa', 'pharmanow' ); ?></a>.</p>

											<h4><?php esc_html_e( 'Clausole vessatorie (artt. 1341 e 1342 c.c.)', 'pharmanow' ); ?></h4>
											<p><?php esc_html_e( 'Ai sensi degli artt. 1341-1342 c.c. dichiari di approvare specificamente:', 'pharmanow' ); ?></p>
											<p><?php esc_html_e( '— Limitazione di responsabilità: The SIP risponde dei danni diretti per fatto proprio, escluso il caso fortuito.', 'pharmanow' ); ?></p>
											<p><?php esc_html_e( '— Foro competente: per qualsiasi controversia è competente il Foro di Napoli, salvo foro del consumatore.', 'pharmanow' ); ?></p>
											<p><?php esc_html_e( '— Diritto di recesso: escluso per le carte personalizzate realizzate su misura (art. 59 Codice del Consumo).', 'pharmanow' ); ?></p>
											<p><?php esc_html_e( '— Modifiche unilaterali: The SIP può modificare le condizioni con preavviso di 30 giorni.', 'pharmanow' ); ?></p>
										</div>
									</div>
								</div>
							</div>

							<span class="pn-co-legal-unified__hint" data-pn-legal-hint>
								<?php esc_html_e( 'Scorri per attivare le caselle', 'pharmanow' ); ?>
							</span>

							<div class="pn-co-legal-unified__checkboxes" data-pn-legal-checkboxes>
								<label class="pn-co-checkbox pn-co-legal__item">
									<input type="checkbox" name="pn_terms_accepted" value="1" x-model="termsAccepted" data-pn-legal-cb disabled>
									<span><?php esc_html_e( 'Accetto i Termini e Condizioni', 'pharmanow' ); ?> <span class="pn-required">*</span></span>
								</label>
								<label class="pn-co-checkbox pn-co-legal__item">
									<input type="checkbox" name="pn_privacy_accepted" value="1" x-model="privacyAccepted" data-pn-legal-cb disabled>
									<span><?php esc_html_e( 'Acconsento alla Privacy Policy', 'pharmanow' ); ?> <span class="pn-required">*</span></span>
								</label>
								<label class="pn-co-checkbox pn-co-legal__item">
									<input type="checkbox" name="pn_vessatorie_accepted" value="1" x-model="vessatorieAccepted" data-pn-legal-cb disabled>
									<span><?php esc_html_e( 'Approvo le clausole vessatorie', 'pharmanow' ); ?> <span class="pn-required">*</span></span>
								</label>
								<label class="pn-co-checkbox pn-co-legal__item pn-co-legal__item--optional">
									<input type="checkbox" name="pn_marketing_consent" value="1" x-model="marketingAccepted">
									<span><?php esc_html_e( 'Acconsento a ricevere offerte via email (facoltativo)', 'pharmanow' ); ?></span>
								</label>
							</div>
						</div>

						<p class="pn-co-error" x-show="legalError" x-text="legalError"></p>

						<button type="button" class="pn-btn-gradient pn-co-cta" @click.prevent.stop="completeStep(2)">
							<span class="pn-btn-slide">
								<span class="pn-btn-slide__text"><?php esc_html_e( 'Continua al pagamento', 'pharmanow' ); ?></span>
								<span class="pn-btn-slide__clone" aria-hidden="true"><?php esc_html_e( 'Continua al pagamento', 'pharmanow' ); ?></span>
							</span>
						</button>
					</div>
				</section>

				<!-- ===== SEZIONE 3: Pagamento ===== -->
				<section class="pn-co-sec" :class="{ 'is-active': step === 3, 'is-locked': !completed.includes(2) }">
					<button type="button" class="pn-co-sec__head" @click="toggleSection(3)" :aria-expanded="step === 3" :disabled="!completed.includes(2)">
						<span class="pn-co-sec__num" :class="{ 'is-active': step === 3 }">
							<span>3</span>
						</span>
						<div class="pn-co-sec__title">
							<span><?php esc_html_e( 'Pagamento', 'pharmanow' ); ?></span>
						</div>
					</button>

					<div class="pn-co-sec__body" x-show="step === 3" x-collapse>
						<div id="payment" class="pn-co-payment woocommerce-checkout-payment">

							<p class="pn-co-payment__heading"><?php esc_html_e( 'Scegli il metodo di pagamento', 'pharmanow' ); ?></p>

							<?php if ( WC()->cart->needs_payment() ) : ?>
								<ul class="pn-co-payment__list wc_payment_methods payment_methods methods">
									<?php
									$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
									if ( $available_gateways ) {
										foreach ( $available_gateways as $gateway ) {
											?>
											<li class="pn-co-pm wc_payment_method payment_method_<?php echo esc_attr( $gateway->id ); ?>">
												<label class="pn-co-pm__head">
													<input
														id="payment_method_<?php echo esc_attr( $gateway->id ); ?>"
														type="radio"
														class="input-radio"
														name="payment_method"
														value="<?php echo esc_attr( $gateway->id ); ?>"
														<?php checked( $gateway->chosen, true ); ?>
														data-order_button_text="<?php echo esc_attr( $gateway->order_button_text ); ?>"
													>
													<span class="pn-co-pm__title"><?php echo wp_kses_post( $gateway->get_title() ); ?></span>
													<?php if ( $gateway->get_icon() ) : ?>
														<span class="pn-co-pm__icon"><?php echo $gateway->get_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
													<?php endif; ?>
												</label>
												<?php if ( $gateway->has_fields() || $gateway->get_description() ) : ?>
													<div class="pn-co-pm__box payment_box payment_method_<?php echo esc_attr( $gateway->id ); ?>" <?php if ( ! $gateway->chosen ) { echo 'style="display:none;"'; } ?>>
														<?php $gateway->payment_fields(); ?>
													</div>
												<?php endif; ?>
											</li>
											<?php
										}
									} else {
										echo '<li class="woocommerce-notice woocommerce-notice--info">' . esc_html__( 'Nessun metodo di pagamento disponibile.', 'pharmanow' ) . '</li>';
									}
									?>
								</ul>
							<?php endif; ?>

							<div class="form-row place-order">
								<noscript>
									<?php esc_html_e( 'Da quando il browser non supporta JavaScript, o è disabilitato, assicurati di cliccare sul pulsante <em>Aggiorna totali</em> prima di effettuare l\'ordine. Potrebbero applicarsi delle commissioni o costi non visibili al momento e che influenzeranno il totale.', 'pharmanow' ); ?>
									<br><button type="submit" class="button alt" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e( 'Aggiorna totali', 'pharmanow' ); ?>"><?php esc_html_e( 'Aggiorna totali', 'pharmanow' ); ?></button>
								</noscript>

								<?php wc_get_template( 'checkout/terms.php' ); ?>

								<?php do_action( 'woocommerce_review_order_before_submit' ); ?>

								<button type="submit" class="pn-btn-gradient pn-co-place-order" name="woocommerce_checkout_place_order" id="place_order" value="<?php esc_attr_e( 'Completa Ordine', 'pharmanow' ); ?>" data-value="<?php esc_attr_e( 'Completa Ordine', 'pharmanow' ); ?>">
									<span class="pn-btn-slide">
										<span class="pn-btn-slide__text">
											<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
											<?php esc_html_e( 'Completa Ordine', 'pharmanow' ); ?>
										</span>
										<span class="pn-btn-slide__clone" aria-hidden="true">
											<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
											<?php esc_html_e( 'Completa Ordine', 'pharmanow' ); ?>
										</span>
									</span>
								</button>

								<?php do_action( 'woocommerce_review_order_after_submit' ); ?>

								<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
							</div>
						</div>
					</div>
				</section>

				<!-- Trust signals -->
				<div class="pn-co-trust">
					<div class="pn-co-trust__item">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
						<span><?php esc_html_e( 'Pagamento sicuro e crittografato', 'pharmanow' ); ?></span>
					</div>
					<div class="pn-co-trust__item">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
						<span><?php esc_html_e( 'Arte + scienza, da Napoli', 'pharmanow' ); ?></span>
					</div>
					<div class="pn-co-trust__item">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16.5 9.4l-9-5.19M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
						<span><?php esc_html_e( 'Spedizione gratis sopra €30', 'pharmanow' ); ?></span>
					</div>
				</div>
			</div>
			<!-- /.pn-checkout__main -->
			</div>
			<!-- /.pn-checkout__left -->

			<!-- ============ SIDEBAR (dx) ============ -->
			<aside class="pn-checkout__sidebar">
				<?php get_template_part( 'template-parts/checkout/order-summary' ); ?>
			</aside>

		</div>

	</div>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
