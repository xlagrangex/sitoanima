<?php
/**
 * Newsletter CTA home — submit AJAX a /wp-json/pharmanow/v1/newsletter (single opt-in).
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="container max-w-7xl mx-auto px-4 py-10">
	<div
		x-data="pnNewsletterForm()"
		class="relative overflow-hidden rounded-2xl pharma-hero text-white px-6 py-10 md:px-12 md:py-14 text-center"
	>
		<div
			class="absolute inset-0 bg-cover bg-center"
			style="background-image:url('<?php echo esc_url( pn_asset( 'images/thesip/newsletter-bg.jpg' ) ); ?>')"
			aria-hidden="true"
		></div>
		<div class="absolute inset-0 bg-[#032a40]/55" aria-hidden="true"></div>
		<div class="relative mx-auto max-w-lg">
			<span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 backdrop-blur-sm px-3 py-1 text-xs font-semibold uppercase tracking-wide text-pharma-accent-light mb-4">
				<?php pn_icon( 'sparkles', array( 'class' => 'h-3.5 w-3.5' ) ); ?>
				<?php esc_html_e( 'Newsletter The SIP', 'pharmanow' ); ?>
			</span>
			<h2 class="text-2xl md:text-3xl font-bold tracking-tight">
				<?php esc_html_e( 'Resta a bordo', 'pharmanow' ); ?>
			</h2>
			<p class="mt-2 text-sm md:text-base text-white/80">
				<?php esc_html_e( 'Iscriviti per primo alle novità: nuove carte, nuovi artisti, ristampe e anteprime dal mondo di The SIP.', 'pharmanow' ); ?>
			</p>

			<form
				x-on:submit.prevent="submit()"
				class="mt-6 flex flex-col sm:flex-row gap-3 max-w-md mx-auto"
				novalidate
			>
				<input
					type="email"
					x-model="email"
					required
					:disabled="loading"
					placeholder="<?php esc_attr_e( 'Il tuo indirizzo email', 'pharmanow' ); ?>"
					class="flex-1 h-11 rounded-lg border-0 bg-white/15 backdrop-blur-sm text-white placeholder:text-white/50 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-white/30 disabled:opacity-60"
				>
				<input type="text" x-model="hp" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0;">
				<button
					type="submit"
					data-slot="button"
					:disabled="loading"
					class="inline-flex items-center justify-center h-11 px-6 rounded-md font-semibold bg-white text-pharma-teal hover:bg-white/90 transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
				>
					<span x-show="!loading">
						<?php
						echo pn_slide_text(
							'<span>' . esc_html__( 'Iscriviti', 'pharmanow' ) . '</span>' .
							pn_icon_string( 'arrow-right', array( 'class' => 'h-4 w-4' ) )
						);
						?>
					</span>
					<span x-show="loading" x-cloak><?php esc_html_e( 'Invio...', 'pharmanow' ); ?></span>
				</button>
			</form>

			<p x-show="success" x-cloak x-transition class="mt-4 text-sm text-pharma-accent-light font-medium">
				<?php esc_html_e( '✓ Iscrizione completata! Ti terremo aggiornato sulle novità di The SIP.', 'pharmanow' ); ?>
			</p>
			<p x-show="error" x-cloak x-transition class="mt-4 text-sm text-red-200 font-medium" x-text="error"></p>

			<p class="mt-4 text-xs text-white/60">
				<?php esc_html_e( 'Iscrivendoti accetti la nostra Privacy Policy. Puoi disiscriverti in qualsiasi momento.', 'pharmanow' ); ?>
			</p>
		</div>
	</div>
</section>

<script>
window.pnNewsletterForm = function () {
	return {
		email: '',
		hp: '',
		loading: false,
		success: false,
		error: '',
		async submit() {
			if (this.loading) return;
			this.error = '';
			this.success = false;
			const email = (this.email || '').trim();
			if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
				this.error = <?php echo wp_json_encode( __( 'Inserisci un indirizzo email valido.', 'pharmanow' ) ); ?>;
				return;
			}
			this.loading = true;
			try {
				const res = await fetch(window.PN_DATA.rest_url + 'newsletter', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.PN_DATA.nonce },
					body: JSON.stringify({ email: email, source: 'homepage', _hp: this.hp }),
				});
				const data = await res.json().catch(() => ({}));
				if (!res.ok) {
					this.error = (data && data.message) || <?php echo wp_json_encode( __( 'Iscrizione non riuscita. Riprova più tardi.', 'pharmanow' ) ); ?>;
					return;
				}
				this.success = true;
				this.email = '';
				setTimeout(() => { this.success = false; }, 6000);
			} catch (e) {
				this.error = <?php echo wp_json_encode( __( 'Errore di rete. Riprova più tardi.', 'pharmanow' ) ); ?>;
			} finally {
				this.loading = false;
			}
		},
	};
};
</script>
