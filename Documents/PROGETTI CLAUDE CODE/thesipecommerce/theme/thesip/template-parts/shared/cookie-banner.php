<?php
/**
 * Cookie consent banner — sticky bottom + modal preferenze.
 * Render unico (markup banner + modal in stesso wrapper).
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn_categories = pn_consent_categories();
?>

<div
	x-data="pnCookieConsent()"
	x-cloak
	class="pn-consent-root"
>
	<!-- Banner sticky bottom -->
	<div
		x-show="bannerOpen"
		x-transition:enter="transition transform ease-out duration-300"
		x-transition:enter-start="translate-y-full opacity-0"
		x-transition:enter-end="translate-y-0 opacity-100"
		x-transition:leave="transition transform ease-in duration-200"
		x-transition:leave-start="translate-y-0 opacity-100"
		x-transition:leave-end="translate-y-full opacity-0"
		class="pn-consent-banner"
		role="dialog"
		aria-labelledby="pn-consent-banner-title"
	>
		<div class="pn-consent-banner__inner">
			<div class="pn-consent-banner__brand">
				<img src="<?php echo esc_url( pn_asset( 'images/logo-pharmanow.svg' ) ); ?>" alt="Pharmanow" class="pn-consent-banner__logo">
			</div>
			<div class="pn-consent-banner__body">
				<h2 id="pn-consent-banner-title" class="pn-consent-banner__title">
					<?php pn_icon( 'cookie', array( 'class' => 'pn-consent-banner__title-icon' ) ); ?>
					<span><?php esc_html_e( 'Usiamo i cookie', 'pharmanow' ); ?></span>
				</h2>
				<p class="pn-consent-banner__text">
					<?php esc_html_e( 'Utilizziamo cookie tecnici necessari al funzionamento del sito. Con il tuo consenso, attiviamo anche cookie statistici e di marketing per migliorare la tua esperienza e mostrarti contenuti rilevanti.', 'pharmanow' ); ?>
					<a href="<?php echo esc_url( home_url( '/legale/cookie' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Leggi la cookie policy', 'pharmanow' ); ?></a>.
				</p>
			</div>
			<div class="pn-consent-banner__actions">
				<button type="button" class="pn-consent-btn pn-consent-btn--ghost" @click="openModal()">
					<?php esc_html_e( 'Personalizza', 'pharmanow' ); ?>
				</button>
				<button type="button" class="pn-consent-btn pn-consent-btn--outline" @click="acceptNecessary()">
					<?php esc_html_e( 'Solo necessari', 'pharmanow' ); ?>
				</button>
				<button type="button" class="pn-consent-btn pn-consent-btn--primary" @click="acceptAll()">
					<?php esc_html_e( 'Accetta tutti', 'pharmanow' ); ?>
				</button>
			</div>
		</div>
	</div>

	<!-- Modal Personalizza -->
	<div
		x-show="modalOpen"
		x-transition:enter="transition ease-out duration-200"
		x-transition:enter-start="opacity-0"
		x-transition:enter-end="opacity-100"
		x-transition:leave="transition ease-in duration-150"
		x-transition:leave-start="opacity-100"
		x-transition:leave-end="opacity-0"
		@keydown.escape.window="modalOpen = false"
		class="pn-consent-modal-backdrop"
		role="dialog"
		aria-modal="true"
		aria-labelledby="pn-consent-modal-title"
	>
		<div
			@click.outside="modalOpen = false"
			x-transition:enter="transition transform ease-out duration-200"
			x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
			x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
			class="pn-consent-modal"
		>
			<header class="pn-consent-modal__head">
				<h2 id="pn-consent-modal-title" class="pn-consent-modal__title">
					<?php esc_html_e( 'Preferenze cookie', 'pharmanow' ); ?>
				</h2>
				<button
					type="button"
					class="pn-consent-modal__close"
					@click="modalOpen = false"
					aria-label="<?php esc_attr_e( 'Chiudi', 'pharmanow' ); ?>"
				>×</button>
			</header>

			<div class="pn-consent-modal__body">
				<p class="pn-consent-modal__intro">
					<?php esc_html_e( 'Scegli quali categorie di cookie autorizzare. Puoi modificare le tue preferenze in qualsiasi momento dal link "Cookie preferences" nel footer.', 'pharmanow' ); ?>
				</p>

				<?php foreach ( $pn_categories as $cat ) : ?>
					<div class="pn-consent-cat" :class="{ 'is-active': prefs.<?php echo esc_attr( $cat['key'] ); ?> }">
						<div class="pn-consent-cat__head">
							<div class="pn-consent-cat__title">
								<span><?php echo esc_html( $cat['label'] ); ?></span>
								<?php if ( $cat['required'] ) : ?>
									<span class="pn-consent-cat__badge"><?php esc_html_e( 'Sempre attivi', 'pharmanow' ); ?></span>
								<?php endif; ?>
							</div>
							<label class="pn-consent-toggle" :class="{ 'is-disabled': <?php echo $cat['required'] ? 'true' : 'false'; ?> }">
								<input
									type="checkbox"
									x-model="prefs.<?php echo esc_attr( $cat['key'] ); ?>"
									<?php if ( $cat['required'] ) : ?>checked disabled<?php endif; ?>
									class="pn-consent-toggle__input"
								>
								<span class="pn-consent-toggle__track"></span>
							</label>
						</div>
						<p class="pn-consent-cat__desc"><?php echo esc_html( $cat['description'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>

			<footer class="pn-consent-modal__foot">
				<button type="button" class="pn-consent-btn pn-consent-btn--ghost" @click="acceptNecessary(); modalOpen = false">
					<?php esc_html_e( 'Solo necessari', 'pharmanow' ); ?>
				</button>
				<button type="button" class="pn-consent-btn pn-consent-btn--primary" @click="saveCustom()">
					<?php esc_html_e( 'Salva preferenze', 'pharmanow' ); ?>
				</button>
			</footer>
		</div>
	</div>
</div>
