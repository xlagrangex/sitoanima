<?php
/**
 * Template Name: The SIP · Contatti
 * Template Post Type: page
 *
 * Porting da app/(shop)/contatti/page.tsx + components/shop/contact/ContactForm.tsx + ContactMap.tsx.
 * Form custom server-rendered, submit AJAX a /wp-json/pharmanow/v1/contact, Mapbox lazy-init via Alpine
 * (token in customizer/constant), fallback senza JS.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$pn_settings = pn_shop_settings();
$pn_email    = 'info@thesip.it';
$pn_full     = 'Napoli';

$pn_lat        = (float) get_theme_mod( 'pn_address_lat', 40.8518 );
$pn_lng        = (float) get_theme_mod( 'pn_address_lng', 14.2681 );
$pn_mapbox_tok = (string) ( defined( 'PN_MAPBOX_TOKEN' ) ? PN_MAPBOX_TOKEN : get_theme_mod( 'pn_mapbox_token', '' ) );
?>

<div class="bg-gray-50 min-h-screen">

	<?php
	get_template_part(
		'template-parts/legal/page-hero',
		null,
		array(
			'title'            => __( 'Contattaci', 'pharmanow' ),
			'subtitle'         => __( 'Siamo a tua disposizione per qualsiasi domanda su prodotti, ordini o spedizioni. Rispondiamo entro 24 ore lavorative.', 'pharmanow' ),
			'breadcrumb_label' => __( 'Contatti', 'pharmanow' ),
			'size'             => 'md',
		)
	);
	?>

	<div class="max-w-6xl mx-auto px-4 py-12">
		<div class="grid lg:grid-cols-2 gap-10">

			<section>
				<h2 class="text-xl font-semibold mb-4"><?php esc_html_e( 'Scrivici', 'pharmanow' ); ?></h2>

				<form
					class="space-y-4"
					novalidate
					x-data="pnContactForm()"
					@submit.prevent="submit"
					aria-describedby="pn-contact-status"
				>
					<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
						<div class="space-y-1.5">
							<label for="pn-c-nome" class="text-sm font-medium leading-none"><?php esc_html_e( 'Nome e cognome', 'pharmanow' ); ?></label>
							<input id="pn-c-nome" name="nome" x-model="form.nome" type="text" autocomplete="name" required minlength="2" placeholder="Mario Rossi" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
							<p class="text-xs text-destructive" x-show="errors.nome" x-text="errors.nome"></p>
						</div>
						<div class="space-y-1.5">
							<label for="pn-c-email" class="text-sm font-medium leading-none"><?php esc_html_e( 'Email', 'pharmanow' ); ?></label>
							<input id="pn-c-email" name="email" x-model="form.email" type="email" autocomplete="email" required placeholder="mario@esempio.it" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
							<p class="text-xs text-destructive" x-show="errors.email" x-text="errors.email"></p>
						</div>
					</div>

					<div class="space-y-1.5">
						<label for="pn-c-tel" class="text-sm font-medium leading-none"><?php esc_html_e( 'Telefono (opzionale)', 'pharmanow' ); ?></label>
						<input id="pn-c-tel" name="telefono" x-model="form.telefono" type="tel" autocomplete="tel" placeholder="+39 333 1234567" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
					</div>

					<div class="space-y-1.5">
						<label for="pn-c-ogg" class="text-sm font-medium leading-none"><?php esc_html_e( 'Oggetto', 'pharmanow' ); ?></label>
						<input id="pn-c-ogg" name="oggetto" x-model="form.oggetto" type="text" required minlength="3" placeholder="<?php esc_attr_e( 'Es. Domanda sulla spedizione', 'pharmanow' ); ?>" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
						<p class="text-xs text-destructive" x-show="errors.oggetto" x-text="errors.oggetto"></p>
					</div>

					<div class="space-y-1.5">
						<label for="pn-c-msg" class="text-sm font-medium leading-none"><?php esc_html_e( 'Messaggio', 'pharmanow' ); ?></label>
						<textarea id="pn-c-msg" name="messaggio" x-model="form.messaggio" rows="6" required minlength="10" placeholder="<?php esc_attr_e( 'Descrivi qui la tua richiesta…', 'pharmanow' ); ?>" class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"></textarea>
						<p class="text-xs text-destructive" x-show="errors.messaggio" x-text="errors.messaggio"></p>
					</div>

					<input type="text" name="_hp" x-model="form._hp" tabindex="-1" autocomplete="off" aria-hidden="true" class="absolute left-[-9999px] h-0 w-0">

					<p class="text-xs text-muted-foreground">
						<?php
						printf(
							/* translators: %s = link Privacy Policy */
							wp_kses(
								__( 'Inviando il modulo accetti la nostra %s.', 'pharmanow' ),
								array( 'a' => array( 'href' => array(), 'class' => array() ) )
							),
							'<a href="' . esc_url( home_url( '/legale/privacy' ) ) . '" class="underline underline-offset-2">' . esc_html__( 'Privacy Policy', 'pharmanow' ) . '</a>'
						);
						?>
					</p>

					<button type="submit" :disabled="submitting" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium h-9 px-4 py-2 bg-primary text-primary-foreground shadow hover:bg-primary/90 disabled:opacity-50 disabled:pointer-events-none w-full sm:w-auto">
						<span x-show="!submitting"><?php esc_html_e( 'Invia messaggio', 'pharmanow' ); ?></span>
						<span x-show="submitting"><?php esc_html_e( 'Invio in corso…', 'pharmanow' ); ?></span>
					</button>

					<p
						id="pn-contact-status"
						role="status"
						class="text-sm"
						:class="status === 'ok' ? 'text-green-700' : (status === 'err' ? 'text-destructive' : 'text-muted-foreground')"
						x-show="message"
						x-text="message"
					></p>
				</form>
			</section>

			<section class="space-y-6">
				<h2 class="text-xl font-semibold"><?php esc_html_e( 'Altri canali', 'pharmanow' ); ?></h2>

				<ul class="space-y-4">
					<li class="flex items-start gap-3">
						<?php pn_icon( 'mail', array( 'class' => 'h-5 w-5 mt-0.5 text-primary' ) ); ?>
						<div>
							<p class="text-xs font-medium uppercase tracking-wider text-muted-foreground"><?php esc_html_e( 'Email', 'pharmanow' ); ?></p>
							<p class="text-sm">
								<a href="mailto:<?php echo esc_attr( $pn_email ); ?>" class="hover:underline">
									<?php echo esc_html( $pn_email ); ?>
								</a>
							</p>
						</div>
					</li>
					<li class="flex items-start gap-3">
						<?php pn_icon( 'map-pin', array( 'class' => 'h-5 w-5 mt-0.5 text-primary' ) ); ?>
						<div>
							<p class="text-xs font-medium uppercase tracking-wider text-muted-foreground"><?php esc_html_e( 'Dove siamo', 'pharmanow' ); ?></p>
							<p class="text-sm"><?php esc_html_e( 'Napoli — consegna a mano gratuita in città', 'pharmanow' ); ?></p>
						</div>
					</li>
					<li class="flex items-start gap-3">
						<?php pn_icon( 'clock', array( 'class' => 'h-5 w-5 mt-0.5 text-primary' ) ); ?>
						<div>
							<p class="text-xs font-medium uppercase tracking-wider text-muted-foreground"><?php esc_html_e( 'Tempi di risposta', 'pharmanow' ); ?></p>
							<p class="text-sm"><?php esc_html_e( 'Rispondiamo entro 24 ore lavorative', 'pharmanow' ); ?></p>
						</div>
					</li>
				</ul>

				<?php if ( $pn_mapbox_tok ) : ?>
					<div
						class="aspect-video w-full rounded-lg overflow-hidden border"
						x-data="pnContactMap(<?php echo esc_attr( wp_json_encode( array( 'lat' => $pn_lat, 'lng' => $pn_lng, 'token' => $pn_mapbox_tok, 'label' => 'The SIP' ) ) ); ?>)"
						x-init="init()"
					></div>
				<?php else : ?>
					<div class="aspect-video w-full rounded-lg border bg-muted flex items-center justify-center text-sm text-muted-foreground">
						<?php
						$pn_q = rawurlencode( $pn_full );
						?>
						<a href="https://www.google.com/maps/search/?api=1&query=<?php echo esc_attr( $pn_q ); ?>" target="_blank" rel="noopener" class="underline">
							<?php esc_html_e( 'Apri mappa su Google Maps', 'pharmanow' ); ?>
						</a>
					</div>
				<?php endif; ?>
			</section>

		</div>
	</div>
</div>

<?php
// Component Alpine inline (verrà tipicamente spostato in assets/src/js/components/contact.js).
?>
<script>
window.pnContactForm = function () {
	return {
		form: { nome: '', email: '', telefono: '', oggetto: '', messaggio: '', _hp: '' },
		errors: {},
		submitting: false,
		message: '',
		status: '',
		validate() {
			const e = {};
			if (!this.form.nome || this.form.nome.trim().length < 2) e.nome = <?php echo wp_json_encode( __( 'Inserisci nome e cognome.', 'pharmanow' ) ); ?>;
			if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) e.email = <?php echo wp_json_encode( __( 'Email non valida.', 'pharmanow' ) ); ?>;
			if (!this.form.oggetto || this.form.oggetto.trim().length < 3) e.oggetto = <?php echo wp_json_encode( __( 'Oggetto troppo corto.', 'pharmanow' ) ); ?>;
			if (!this.form.messaggio || this.form.messaggio.trim().length < 10) e.messaggio = <?php echo wp_json_encode( __( 'Messaggio troppo corto.', 'pharmanow' ) ); ?>;
			this.errors = e;
			return Object.keys(e).length === 0;
		},
		async submit() {
			this.message = ''; this.status = '';
			if (!this.validate()) return;
			this.submitting = true;
			try {
				const res = await fetch(window.PN_DATA.rest_url + 'contact', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.PN_DATA.nonce },
					body: JSON.stringify(this.form),
				});
				const data = await res.json().catch(() => ({}));
				if (!res.ok) throw new Error(data && data.message ? data.message : <?php echo wp_json_encode( __( 'Invio fallito.', 'pharmanow' ) ); ?>);
				this.status = 'ok';
				this.message = <?php echo wp_json_encode( __( 'Messaggio inviato. Ti rispondiamo entro 24 ore.', 'pharmanow' ) ); ?>;
				this.form = { nome: '', email: '', telefono: '', oggetto: '', messaggio: '', _hp: '' };
			} catch (err) {
				this.status = 'err';
				this.message = err && err.message ? err.message : <?php echo wp_json_encode( __( "Errore durante l'invio.", 'pharmanow' ) ); ?>;
			} finally {
				this.submitting = false;
			}
		},
	};
};

window.pnContactMap = function (cfg) {
	return {
		init() {
			if (!cfg.token) return;
			const ensureCss = (href) => new Promise((res) => {
				if (document.querySelector('link[data-pn-mapbox]')) return res();
				const l = document.createElement('link'); l.rel = 'stylesheet'; l.href = href; l.dataset.pnMapbox = '1';
				l.onload = res; document.head.appendChild(l);
			});
			const ensureJs = (src) => new Promise((res, rej) => {
				if (window.mapboxgl) return res();
				const s = document.createElement('script'); s.src = src; s.onload = res; s.onerror = rej;
				document.head.appendChild(s);
			});
			Promise.all([
				ensureCss('https://api.mapbox.com/mapbox-gl-js/v3.0.1/mapbox-gl.css'),
				ensureJs('https://api.mapbox.com/mapbox-gl-js/v3.0.1/mapbox-gl.js'),
			]).then(() => {
				window.mapboxgl.accessToken = cfg.token;
				const map = new window.mapboxgl.Map({
					container: this.$el,
					style: 'mapbox://styles/mapbox/streets-v12',
					center: [cfg.lng, cfg.lat],
					zoom: 14,
				});
				new window.mapboxgl.Marker({ color: '#0ea5a4' })
					.setLngLat([cfg.lng, cfg.lat])
					.setPopup(new window.mapboxgl.Popup({ offset: 24 }).setText(cfg.label || ''))
					.addTo(map);
			}).catch(() => {});
		}
	};
};
</script>

<?php
get_footer();
