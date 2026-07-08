<?php
/**
 * Header search bar — InstantSearch Alpine.
 *
 * Replica components/shop/search/InstantSearch.tsx del Next:
 *  - Placeholder typing animato (vitamine, tachipirina, …)
 *  - Debounce 200ms su REST /wp-json/pharmanow/v1/search
 *  - Recent searches localStorage (chiave pn-recent-searches, max 5)
 *  - Dropdown: header "Ricerca per", lista prodotti suggeriti, footer
 *    "Vedi tutti i N risultati"
 *  - Empty state, loader inline, clear button
 *
 * Submit form → /ricerca/?q=...
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn_endpoint = esc_url_raw( rest_url( 'pharmanow/v1/search' ) );
$pn_search_url = esc_url( home_url( '/ricerca/' ) );
?>
<div
	class="relative w-full"
	x-data='{
		query: "",
		results: null,
		open: false,
		pending: false,
		recent: [],
		debounceId: null,
		typed: "<?php echo esc_js( __( 'Cerca carte, bundle, temi…', 'pharmanow' ) ); ?>",
		typingId: null,
		ti: { qi: 0, ci: 0, deleting: false },
		typingQueries: ["squalo bianco","polpo","medusa","tartaruga marina","set completo","bundle deluxe"],
		init() {
			try {
				const raw = localStorage.getItem("pn-recent-searches");
				if (raw) this.recent = JSON.parse(raw) || [];
			} catch(e) {}
			this.startTyping();
		},
		startTyping() {
			const tick = () => {
				if (this.query.length > 0 || this.open) {
					this.typingId = setTimeout(tick, 600);
					return;
				}
				const word = this.typingQueries[this.ti.qi];
				if (!this.ti.deleting) {
					this.ti.ci++;
					this.typed = word.slice(0, this.ti.ci);
					if (this.ti.ci >= word.length) {
						this.ti.deleting = true;
						this.typingId = setTimeout(tick, 1500);
						return;
					}
					this.typingId = setTimeout(tick, 70 + Math.random()*60);
				} else {
					this.ti.ci--;
					this.typed = word.slice(0, this.ti.ci);
					if (this.ti.ci <= 0) {
						this.ti.deleting = false;
						this.ti.qi = (this.ti.qi + 1) % this.typingQueries.length;
						this.typingId = setTimeout(tick, 250);
						return;
					}
					this.typingId = setTimeout(tick, 30 + Math.random()*30);
				}
			};
			this.typingId = setTimeout(tick, 800);
		},
		saveRecent(q) {
			q = (q||"").trim();
			if (!q) return;
			this.recent = [q, ...this.recent.filter(x => x !== q)].slice(0, 5);
			try { localStorage.setItem("pn-recent-searches", JSON.stringify(this.recent)); } catch(e) {}
		},
		clearQuery() {
			this.query = "";
			this.results = null;
			this.$refs.input.focus();
		},
		clearRecent() {
			this.recent = [];
			try { localStorage.removeItem("pn-recent-searches"); } catch(e) {}
		},
		onInput() {
			this.open = true;
			if (this.debounceId) clearTimeout(this.debounceId);
			const q = this.query.trim();
			if (q.length < 2) { this.results = null; this.pending = false; return; }
			this.pending = true;
			this.debounceId = setTimeout(async () => {
				try {
					const u = new URL("<?php echo esc_url_raw( $pn_endpoint ); ?>");
					u.searchParams.set("q", q);
					u.searchParams.set("limit", "5");
					const res = await fetch(u.toString(), { headers: { "Accept": "application/json" } });
					if (res.ok) this.results = await res.json();
					else this.results = { products: [], total: 0, query: q };
				} catch (e) {
					this.results = { products: [], total: 0, query: q };
				} finally {
					this.pending = false;
				}
			}, 200);
		},
		submitSearch() {
			const q = this.query.trim();
			if (!q) return;
			this.saveRecent(q);
			this.open = false;
			window.location.href = "<?php echo esc_url( $pn_search_url ); ?>?q=" + encodeURIComponent(q);
		},
		goRecent(q) {
			this.open = false;
			window.location.href = "<?php echo esc_url( $pn_search_url ); ?>?q=" + encodeURIComponent(q);
		},
		showDropdown() {
			return this.open && (this.query.trim().length >= 2 || (this.query.length === 0 && this.recent.length > 0));
		}
	}'
	@click.outside="open = false"
	@keydown.escape="open = false; $refs.input.blur()"
>
	<form @submit.prevent="submitSearch()" class="relative">
		<span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
			<?php pn_icon( 'search', array( 'class' => 'h-4 w-4' ) ); ?>
		</span>

		<input
			x-ref="input"
			type="search"
			x-model="query"
			:placeholder="query.length === 0 && !open ? typed : '<?php echo esc_js( __( 'Cerca carte, bundle, temi…', 'pharmanow' ) ); ?>'"
			autocomplete="off"
			@input="onInput()"
			@focus="open = true"
			class="w-full h-9 pl-9 pr-9 rounded-full border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-pharma-teal/30 focus:bg-white transition-colors"
		>

		<button
			type="button"
			x-show="query"
			x-cloak
			@click="clearQuery()"
			aria-label="<?php esc_attr_e( 'Pulisci ricerca', 'pharmanow' ); ?>"
			class="absolute right-2 top-1/2 -translate-y-1/2 rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition-colors"
		>
			<?php pn_icon( 'x', array( 'class' => 'h-3.5 w-3.5' ) ); ?>
		</button>

		<span
			x-show="pending"
			x-cloak
			class="absolute right-9 top-1/2 -translate-y-1/2 text-gray-400"
		>
			<?php pn_icon( 'loader-2', array( 'class' => 'h-4 w-4 animate-spin' ) ); ?>
		</span>
	</form>

	<!-- Dropdown -->
	<div
		x-show="showDropdown()"
		x-cloak
		x-transition.opacity.duration.150ms
		class="absolute left-0 right-0 top-full z-50 mt-2 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl"
	>
		<!-- Header "Ricerca per" -->
		<template x-if="query.trim().length >= 2">
			<div class="bg-gray-50/70 px-4 py-2.5">
				<p class="text-[10px] uppercase tracking-wider text-gray-500"><?php esc_html_e( 'Ricerca per', 'pharmanow' ); ?></p>
				<p class="text-sm font-bold text-gray-900">&ldquo;<span x-text="query"></span>&rdquo;</p>
			</div>
		</template>

		<!-- Recent searches -->
		<template x-if="query.length === 0 && recent.length > 0">
			<div class="p-3">
				<div class="mb-2 flex items-center justify-between">
					<p class="text-xs font-medium text-gray-500"><?php esc_html_e( 'Ricerche recenti', 'pharmanow' ); ?></p>
					<button type="button" @click="clearRecent()" class="text-[10px] text-gray-400 hover:text-gray-700 transition-colors">
						<?php esc_html_e( 'pulisci', 'pharmanow' ); ?>
					</button>
				</div>
				<div class="space-y-1">
					<template x-for="r in recent" :key="r">
						<button
							type="button"
							@mousedown.prevent="goRecent(r)"
							class="flex w-full items-center gap-2 rounded px-2 py-1.5 text-left text-sm text-gray-700 hover:bg-gray-50 transition-colors"
						>
							<span class="text-gray-400"><?php pn_icon( 'search', array( 'class' => 'h-3.5 w-3.5' ) ); ?></span>
							<span x-text="r"></span>
						</button>
					</template>
				</div>
			</div>
		</template>

		<!-- Empty state -->
		<template x-if="results && results.total === 0 && query.trim().length >= 2">
			<div class="border-t border-gray-100 p-8 text-center text-sm text-gray-500">
				<?php esc_html_e( 'Nessun risultato per', 'pharmanow' ); ?> &ldquo;<span x-text="query"></span>&rdquo;
			</div>
		</template>

		<!-- Risultati prodotti -->
		<template x-if="results && results.products && results.products.length > 0">
			<div class="max-h-[60vh] overflow-y-auto">
				<p class="border-t border-gray-100 px-4 pb-1.5 pt-3 text-[10px] font-bold uppercase tracking-widest text-gray-500">
					<?php esc_html_e( 'Prodotti suggeriti', 'pharmanow' ); ?>
				</p>
				<ul class="divide-y divide-gray-100">
					<template x-for="p in results.products" :key="p.id">
						<li>
							<a
								:href="p.url"
								@mousedown.prevent="window.location.href = p.url"
								class="group flex items-center gap-3 px-4 py-2 transition-colors hover:bg-gray-50"
							>
								<div class="relative h-12 w-12 shrink-0 overflow-hidden rounded-md border border-gray-100 bg-white p-1">
									<template x-if="p.thumbnail">
										<img :src="p.thumbnail" :alt="p.title" class="h-full w-full object-contain" loading="lazy">
									</template>
								</div>
								<div class="min-w-0 flex-1">
									<p class="truncate text-sm font-medium leading-tight text-gray-900" x-text="p.title"></p>
									<div class="mt-0.5 flex flex-wrap items-baseline gap-1.5">
										<template x-if="p.sale_price > 0 && p.regular_price > 0">
											<s class="text-[11px] text-gray-400" x-text="p.regular_formatted"></s>
										</template>
										<span
											class="text-sm font-semibold tabular-nums"
											:class="p.sale_price > 0 ? 'text-emerald-600' : 'text-gray-900'"
											x-text="p.price_formatted"
										></span>
										<template x-if="p.discount_pct > 0">
											<span class="rounded bg-emerald-100 px-1.5 py-px text-[10px] font-semibold text-emerald-700">
												-<span x-text="p.discount_pct"></span>%
											</span>
										</template>
									</div>
								</div>
								<span class="shrink-0 text-gray-400 transition-transform group-hover:translate-x-0.5 group-hover:text-gray-700">
									<?php pn_icon( 'arrow-right', array( 'class' => 'h-3.5 w-3.5' ) ); ?>
								</span>
							</a>
						</li>
					</template>
				</ul>

				<button
					type="button"
					@mousedown.prevent="saveRecent(query); window.location.href = '<?php echo esc_url( $pn_search_url ); ?>?q=' + encodeURIComponent(query)"
					class="flex w-full items-center justify-center gap-2 border-t border-gray-100 bg-gray-50/60 px-4 py-3 text-sm font-semibold text-pharma-teal hover:bg-gray-100 transition-colors"
				>
					<?php esc_html_e( 'Vedi tutti i', 'pharmanow' ); ?>
					<span x-text="results.total"></span>
					<?php esc_html_e( 'risultati', 'pharmanow' ); ?>
					<?php pn_icon( 'arrow-right', array( 'class' => 'h-3.5 w-3.5' ) ); ?>
				</button>
			</div>
		</template>
	</div>
</div>
