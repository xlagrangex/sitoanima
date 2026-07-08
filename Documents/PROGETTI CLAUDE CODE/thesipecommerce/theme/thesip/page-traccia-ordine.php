<?php
/**
 * Template Name: Traccia ordine
 *
 * Replica app/(shop)/traccia-ordine + components/shop/account/TrackOrderForm
 * del Next. Form Alpine + REST endpoint pharmanow/v1/track-order.
 *
 * Caricato sia per il rewrite virtuale /traccia-ordine/ (vedi
 * inc/account/track-order.php) sia come Page Template selezionabile in admin.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header( 'shop' );

$pn_endpoint = esc_url_raw( rest_url( 'pharmanow/v1/track-order' ) );
$pn_nonce    = wp_create_nonce( 'wp_rest' );
?>
<div class="bg-white">
	<div class="container mx-auto max-w-xl px-4 py-10">

		<div class="mb-6 text-center">
			<h1 class="text-2xl md:text-3xl font-bold text-gray-900 tracking-tight">
				<?php esc_html_e( 'Traccia il tuo ordine', 'pharmanow' ); ?>
			</h1>
			<p class="mt-2 text-sm text-gray-500">
				<?php esc_html_e( 'Inserisci il numero ordine e l\'email che hai usato per ricevere lo stato attuale.', 'pharmanow' ); ?>
			</p>
		</div>

		<div
			class="space-y-6"
			x-data='{
				displayId: "",
				email: "",
				loading: false,
				error: null,
				result: null,
				errors: { displayId: null, email: null },
				validate() {
					this.errors.displayId = this.displayId.trim() ? null : "<?php echo esc_js( __( 'Numero ordine obbligatorio', 'pharmanow' ) ); ?>";
					const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
					this.errors.email = emailRe.test(this.email.trim()) ? null : "<?php echo esc_js( __( 'Email non valida', 'pharmanow' ) ); ?>";
					return !this.errors.displayId && !this.errors.email;
				},
				async submit() {
					if (this.loading) return;
					this.error = null; this.result = null;
					if (!this.validate()) return;
					this.loading = true;
					try {
						const u = new URL("<?php echo esc_url_raw( $pn_endpoint ); ?>");
						u.searchParams.set("display_id", this.displayId.trim());
						u.searchParams.set("email", this.email.trim());
						const res = await fetch(u.toString(), {
							headers: { "X-WP-Nonce": "<?php echo esc_js( $pn_nonce ); ?>", "Accept": "application/json" }
						});
						const json = await res.json().catch(() => ({}));
						if (!res.ok) {
							this.error = json.message || json.error || "<?php echo esc_js( __( 'Ordine non trovato', 'pharmanow' ) ); ?>";
						} else {
							this.result = json;
						}
					} catch (e) {
						this.error = "<?php echo esc_js( __( 'Errore di rete, riprova', 'pharmanow' ) ); ?>";
					} finally {
						this.loading = false;
					}
				},
				toneClass(tone) {
					const map = {
						amber: "bg-amber-100 text-amber-800",
						emerald: "bg-emerald-100 text-emerald-800",
						blue: "bg-blue-100 text-blue-800",
						rose: "bg-rose-100 text-rose-800",
						gray: "bg-gray-100 text-gray-700"
					};
					return map[tone] || map.gray;
				},
				formatDate(iso) {
					if (!iso) return "";
					const d = new Date(iso);
					return d.toLocaleDateString("it-IT", { day: "2-digit", month: "long", year: "numeric" });
				}
			}'
		>
			<form
				@submit.prevent="submit()"
				class="space-y-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm"
				novalidate
			>
				<div class="space-y-1.5">
					<label for="pn-track-order-id" class="block text-xs font-medium text-gray-600">
						<?php esc_html_e( 'Numero ordine', 'pharmanow' ); ?>
					</label>
					<input
						id="pn-track-order-id"
						type="text"
						x-model="displayId"
						placeholder="<?php esc_attr_e( 'es. 12345', 'pharmanow' ); ?>"
						autocomplete="off"
						class="w-full h-10 px-3 text-sm rounded-md border border-gray-200 bg-white focus:outline-none focus:ring-2 focus:ring-pharma-teal/30 focus:border-pharma-teal transition-colors"
					>
					<p x-show="errors.displayId" x-cloak x-text="errors.displayId" class="text-xs text-rose-600"></p>
				</div>

				<div class="space-y-1.5">
					<label for="pn-track-order-email" class="block text-xs font-medium text-gray-600">
						<?php esc_html_e( 'Email usata per l\'ordine', 'pharmanow' ); ?>
					</label>
					<input
						id="pn-track-order-email"
						type="email"
						x-model="email"
						autocomplete="email"
						class="w-full h-10 px-3 text-sm rounded-md border border-gray-200 bg-white focus:outline-none focus:ring-2 focus:ring-pharma-teal/30 focus:border-pharma-teal transition-colors"
					>
					<p x-show="errors.email" x-cloak x-text="errors.email" class="text-xs text-rose-600"></p>
				</div>

				<button
					type="submit"
					:disabled="loading"
					class="inline-flex items-center justify-center gap-2 w-full sm:w-auto h-10 px-5 rounded-md bg-pharma-teal text-white text-sm font-semibold transition-colors hover:bg-pharma-teal-dark disabled:opacity-60 disabled:cursor-not-allowed"
				>
					<span x-show="loading" x-cloak>
						<?php pn_icon( 'loader-2', array( 'class' => 'h-4 w-4 animate-spin' ) ); ?>
					</span>
					<?php esc_html_e( 'Cerca ordine', 'pharmanow' ); ?>
				</button>
			</form>

			<!-- Errore -->
			<div
				x-show="error"
				x-cloak
				x-transition.opacity
				class="rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700"
			>
				<span x-text="error"></span>
			</div>

			<!-- Risultato -->
			<div
				x-show="result"
				x-cloak
				x-transition
				class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm"
			>
				<div class="flex items-start gap-3">
					<div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-pharma-teal/10 text-pharma-teal">
						<?php pn_icon( 'box', array( 'class' => 'h-5 w-5' ) ); ?>
					</div>
					<div class="flex-1 min-w-0">
						<div class="flex flex-wrap items-center gap-2">
							<h3 class="font-semibold text-gray-900">
								<?php esc_html_e( 'Ordine #', 'pharmanow' ); ?><span x-text="result?.display_id"></span>
							</h3>
							<span
								class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium"
								:class="toneClass(result?.status_tone)"
								x-text="result?.status_label"
							></span>
						</div>
						<p class="mt-1 text-xs text-gray-500">
							<span x-text="formatDate(result?.created_at)"></span>
							<span> · </span>
							<span x-text="(result?.items_count || 0) + ' ' + ((result?.items_count === 1) ? '<?php echo esc_js( __( 'articolo', 'pharmanow' ) ); ?>' : '<?php echo esc_js( __( 'articoli', 'pharmanow' ) ); ?>')"></span>
							<span> · </span>
							<span x-text="result?.total_formatted"></span>
						</p>
					</div>
				</div>

				<!-- Tracking -->
				<div
					x-show="result?.tracking_number"
					x-cloak
					class="mt-4 rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm"
				>
					<p class="text-xs uppercase tracking-wider text-gray-500"><?php esc_html_e( 'Tracking', 'pharmanow' ); ?></p>
					<p class="mt-1 font-mono text-gray-900" x-text="result?.tracking_number"></p>
					<p x-show="result?.tracking_provider" x-cloak class="mt-0.5 text-xs text-gray-500">
						<?php esc_html_e( 'Corriere', 'pharmanow' ); ?>:
						<span class="capitalize" x-text="result?.tracking_provider"></span>
					</p>
					<a
						x-show="result?.tracking_url"
						x-cloak
						:href="result?.tracking_url"
						target="_blank"
						rel="noopener noreferrer"
						class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-pharma-teal hover:text-pharma-teal-dark transition-colors"
					>
						<?php esc_html_e( 'Vai al tracking corriere', 'pharmanow' ); ?>
						<?php pn_icon( 'external-link', array( 'class' => 'h-3 w-3' ) ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
get_footer( 'shop' );
