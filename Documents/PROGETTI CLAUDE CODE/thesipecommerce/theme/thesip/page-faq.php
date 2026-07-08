<?php
/**
 * Template Name: The SIP · FAQ
 * Template Post Type: page
 *
 * Porting da components/shop/faq/FaqPage.tsx (Next).
 * Sidebar categorie + ricerca client-side + accordion (Alpine).
 * FAQ JSON-LD inline per SEO.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$pn_faq      = pn_get_faq_data();
$pn_initial  = '';
if ( isset( $_GET['cat'] ) ) {
	$pn_initial = sanitize_key( wp_unslash( (string) $_GET['cat'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}
$pn_initial_id = $pn_initial && wp_list_filter( $pn_faq, array( 'id' => $pn_initial ) ) ? $pn_initial : ( $pn_faq[0]['id'] ?? 'ordini' );

// Build JSON-LD FAQPage from all questions.
$pn_jsonld = array(
	'@context'   => 'https://schema.org',
	'@type'      => 'FAQPage',
	'mainEntity' => array(),
);
foreach ( $pn_faq as $cat ) {
	foreach ( $cat['questions'] as $q ) {
		$pn_jsonld['mainEntity'][] = array(
			'@type'          => 'Question',
			'name'           => $q['q'],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $q['a'],
			),
		);
	}
}
?>

<script type="application/ld+json"><?php echo wp_json_encode( $pn_jsonld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>

<div class="bg-gray-50 min-h-screen" x-data="pnFaqPage(<?php echo esc_attr( wp_json_encode( array( 'initial' => $pn_initial_id ) ) ); ?>)">

	<?php
	get_template_part(
		'template-parts/legal/page-hero',
		null,
		array(
			'title'            => __( 'Domande Frequenti', 'pharmanow' ),
			'subtitle'         => __( 'Trova rapidamente le risposte alle tue domande', 'pharmanow' ),
			'breadcrumb_label' => 'FAQ',
			'size'             => 'sm',
		)
	);
	?>

	<div class="max-w-[1280px] mx-auto px-4 py-8 sm:py-12">
		<div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

			<?php // Sidebar Desktop. ?>
			<aside class="lg:col-span-1 hidden lg:block">
				<div class="bg-white rounded-2xl shadow-sm p-5 lg:sticky lg:top-[120px]">
					<h2 class="font-bold text-gray-900 mb-4"><?php esc_html_e( 'Categorie FAQ', 'pharmanow' ); ?></h2>
					<nav class="space-y-1">
						<?php foreach ( $pn_faq as $pn_cat ) : ?>
							<button
								type="button"
								@click="setActive('<?php echo esc_js( $pn_cat['id'] ); ?>')"
								:class="active === '<?php echo esc_js( $pn_cat['id'] ); ?>' ? 'bg-[#0A6E9E] text-white' : 'text-gray-600 hover:bg-gray-50'"
								class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-left transition-colors"
							>
								<span :class="active === '<?php echo esc_js( $pn_cat['id'] ); ?>' ? 'text-white' : 'text-[#0A6E9E]'" class="inline-flex">
									<?php pn_icon( $pn_cat['icon'], array( 'class' => 'w-5 h-5' ) ); ?>
								</span>
								<span class="text-sm font-medium"><?php echo esc_html( $pn_cat['title'] ); ?></span>
							</button>
						<?php endforeach; ?>
					</nav>
				</div>
			</aside>

			<?php // Mobile category selector. ?>
			<div class="lg:hidden">
				<button type="button" @click="mobileMenu = !mobileMenu" class="w-full flex items-center justify-between bg-white rounded-xl shadow-sm p-4">
					<span class="flex items-center gap-3">
						<span class="text-[#0A6E9E] inline-flex" x-html="iconHtml(active)"></span>
						<span class="font-medium text-gray-900" x-text="catTitle(active)"></span>
					</span>
					<?php pn_icon( 'menu', array( 'class' => 'w-5 h-5 text-gray-400' ) ); ?>
				</button>
				<div x-show="mobileMenu" x-cloak class="mt-2 bg-white rounded-xl shadow-sm p-2">
					<?php foreach ( $pn_faq as $pn_cat ) : ?>
						<button
							type="button"
							@click="setActive('<?php echo esc_js( $pn_cat['id'] ); ?>'); mobileMenu = false"
							:class="active === '<?php echo esc_js( $pn_cat['id'] ); ?>' ? 'bg-[#0A6E9E] text-white' : 'text-gray-600'"
							class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-left"
						>
							<span :class="active === '<?php echo esc_js( $pn_cat['id'] ); ?>' ? 'text-white' : 'text-[#0A6E9E]'" class="inline-flex">
								<?php pn_icon( $pn_cat['icon'], array( 'class' => 'w-5 h-5' ) ); ?>
							</span>
							<span class="text-sm font-medium"><?php echo esc_html( $pn_cat['title'] ); ?></span>
						</button>
					<?php endforeach; ?>
				</div>
			</div>

			<?php // Main column. ?>
			<div class="lg:col-span-3">
				<?php // Search input. ?>
				<div class="relative mb-6">
					<span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
						<?php pn_icon( 'search', array( 'class' => 'w-5 h-5' ) ); ?>
					</span>
					<input
						type="search"
						x-model="query"
						:placeholder="searchPlaceholder()"
						class="w-full bg-white rounded-xl shadow-sm pl-12 pr-4 py-3 text-sm border border-transparent focus:border-[#0A6E9E] focus:outline-none transition-colors"
					>
				</div>

				<?php foreach ( $pn_faq as $pn_cat ) : ?>
					<div x-show="active === '<?php echo esc_js( $pn_cat['id'] ); ?>'" x-cloak class="bg-white rounded-2xl shadow-sm overflow-hidden">
						<div class="bg-gradient-to-r from-[#0A6E9E] to-[#38BDF8] px-6 py-5 text-white">
							<div class="flex items-center gap-3">
								<?php pn_icon( $pn_cat['icon'], array( 'class' => 'w-6 h-6' ) ); ?>
								<h2 class="text-lg sm:text-xl font-bold"><?php echo esc_html( $pn_cat['title'] ); ?></h2>
							</div>
							<p class="text-white/80 text-sm mt-1">
								<?php
								$pn_count = count( $pn_cat['questions'] );
								echo esc_html(
									sprintf(
										/* translators: %d = number */
										_n( '%d domanda', '%d domande', $pn_count, 'pharmanow' ),
										$pn_count
									)
								);
								?>
							</p>
						</div>

						<div class="divide-y divide-gray-100">
							<?php foreach ( $pn_cat['questions'] as $idx => $q ) : ?>
								<div
									x-show="match('<?php echo esc_js( $pn_cat['id'] ); ?>', <?php echo (int) $idx; ?>)"
									x-data="{ open: false }"
								>
									<button
										type="button"
										@click="open = !open"
										:aria-expanded="open"
										class="w-full flex items-center justify-between gap-4 px-6 py-5 text-left hover:bg-gray-50 transition-colors"
									>
										<span class="font-medium text-gray-900 text-sm sm:text-base">
											<?php echo esc_html( $q['q'] ); ?>
										</span>
										<span :class="open ? 'rotate-180' : ''" class="text-gray-400 flex-shrink-0 transition-transform">
											<?php pn_icon( 'chevron-down', array( 'class' => 'w-5 h-5' ) ); ?>
										</span>
									</button>
									<div x-show="open" x-cloak class="px-6 pb-5 pt-1 text-sm text-gray-600 leading-relaxed whitespace-pre-line">
										<?php echo esc_html( $q['a'] ); ?>
									</div>
								</div>
							<?php endforeach; ?>

							<p x-show="emptyForCat('<?php echo esc_js( $pn_cat['id'] ); ?>')" x-cloak class="p-6 text-sm text-gray-500">
								<?php
								printf(
									/* translators: %s = link contatti */
									wp_kses(
										__( 'Nessuna domanda trovata. Prova con altre parole chiave o %s.', 'pharmanow' ),
										array( 'a' => array( 'href' => array(), 'class' => array() ) )
									),
									'<a href="' . esc_url( home_url( '/contatti' ) ) . '" class="text-[#0A6E9E] hover:underline">' . esc_html__( 'contattaci', 'pharmanow' ) . '</a>'
								);
								?>
							</p>
						</div>
					</div>
				<?php endforeach; ?>

				<?php // Help footer. ?>
				<div class="mt-8 bg-gradient-to-r from-[#0A6E9E]/10 to-[#38BDF8]/10 rounded-2xl p-6 text-center">
					<h3 class="font-bold text-gray-900 mb-2"><?php esc_html_e( 'Non hai trovato la risposta?', 'pharmanow' ); ?></h3>
					<p class="text-gray-600 text-sm mb-4">
						<?php esc_html_e( 'Scrivici: ti rispondiamo entro 24 ore lavorative.', 'pharmanow' ); ?>
					</p>
					<div class="flex flex-wrap justify-center gap-3">
						<a href="<?php echo esc_url( home_url( '/contatti' ) ); ?>" class="inline-flex items-center gap-2 bg-[#0A6E9E] text-white font-semibold py-2.5 px-5 rounded-xl hover:bg-[#0A6E9E]/90 transition-colors">
							<?php esc_html_e( 'Scrivici', 'pharmanow' ); ?>
						</a>
						<a href="mailto:info@thesip.it" class="inline-flex items-center gap-2 bg-white text-gray-700 font-semibold py-2.5 px-5 rounded-xl border border-gray-200 hover:bg-gray-50 transition-colors">
							info@thesip.it
						</a>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>

<?php
// Build a JS-friendly map { id: { title, questions: [{q,a}, …] } } for client-side filtering / titles.
$pn_js_data = array();
foreach ( $pn_faq as $pn_cat ) {
	$pn_js_data[ $pn_cat['id'] ] = array(
		'title'     => $pn_cat['title'],
		'icon'      => $pn_cat['icon'],
		'iconSvg'   => pn_icon_string( $pn_cat['icon'], array( 'class' => 'w-5 h-5' ) ),
		'questions' => array_map(
			function ( $q ) {
				return array(
					'q' => $q['q'],
					'a' => $q['a'],
				);
			},
			$pn_cat['questions']
		),
	);
}
?>
<script>
window.pnFaqData = <?php echo wp_json_encode( $pn_js_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?>;
window.pnFaqPage = function (cfg) {
	return {
		active: cfg.initial,
		query: '',
		mobileMenu: false,
		setActive(id) { this.active = id; this.query = ''; },
		catTitle(id) { return (window.pnFaqData[id] || {}).title || ''; },
		iconHtml(id) {
			const cat = window.pnFaqData[id];
			return cat && cat.iconSvg ? cat.iconSvg : '';
		},
		searchPlaceholder() {
			const t = this.catTitle(this.active);
			return <?php echo wp_json_encode( __( 'Cerca in', 'pharmanow' ) . ' "' ); ?> + t + '"…';
		},
		match(catId, idx) {
			if (catId !== this.active) return false;
			const needle = this.query.trim().toLowerCase();
			if (!needle) return true;
			const cat = window.pnFaqData[catId];
			const item = cat && cat.questions[idx];
			if (!item) return false;
			return item.q.toLowerCase().includes(needle) || item.a.toLowerCase().includes(needle);
		},
		emptyForCat(catId) {
			if (catId !== this.active) return false;
			const needle = this.query.trim().toLowerCase();
			if (!needle) return false;
			const cat = window.pnFaqData[catId];
			if (!cat) return true;
			return !cat.questions.some((q) => q.q.toLowerCase().includes(needle) || q.a.toLowerCase().includes(needle));
		},
	};
};
</script>

<?php
get_footer();
