<?php
/**
 * Indice marche — replica components/shop/brands/BrandIndex.tsx.
 *
 * Caricato per /marchi/ via rewrite virtuale (vedi inc/cpt/brand.php).
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header( 'shop' );

$pn_brands = pn_get_brand_summaries();
$pn_total  = count( $pn_brands );

// Group by initial.
$pn_grouped = array();
foreach ( $pn_brands as $b ) {
	$pn_grouped[ $b['first'] ][] = $b;
}
ksort( $pn_grouped );

// Popolari (top 12 per count prodotti — proxy salesScore del Next).
$pn_popular = $pn_brands;
usort( $pn_popular, function ( $a, $b ) { return $b['count'] - $a['count']; } );
$pn_popular = array_slice( $pn_popular, 0, 12 );

$pn_letters_all     = str_split( 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' );
$pn_letters_all[]   = '#';
$pn_letters_present = array_keys( $pn_grouped );
?>

<div class="bg-white">
	<div class="container mx-auto max-w-5xl px-4 py-6 md:py-10">

		<nav class="mb-3 text-sm text-gray-500" aria-label="<?php esc_attr_e( 'Breadcrumb', 'pharmanow' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-pharma-teal transition-colors">
				<?php esc_html_e( 'Home', 'pharmanow' ); ?>
			</a>
			<span class="mx-2 text-gray-300">/</span>
			<span class="text-gray-900 font-medium"><?php esc_html_e( 'Marchi', 'pharmanow' ); ?></span>
		</nav>

		<header class="mb-6 space-y-2">
			<h1 class="text-2xl md:text-3xl font-bold text-gray-900 tracking-tight">
				<?php esc_html_e( 'Tutti i marchi', 'pharmanow' ); ?>
			</h1>
			<p class="text-sm text-gray-500">
				<?php
				printf(
					/* translators: %s numero marche */
					esc_html( _n( '%s marchio in catalogo', '%s marchi in catalogo', $pn_total, 'pharmanow' ) ),
					'<strong class="text-gray-700">' . esc_html( number_format_i18n( $pn_total ) ) . '</strong>'
				);
				?>
			</p>
		</header>

		<?php if ( empty( $pn_brands ) ) : ?>

			<div class="rounded-lg border border-dashed border-gray-300 py-12 text-center text-sm text-gray-500">
				<?php esc_html_e( 'Nessun marchio disponibile al momento.', 'pharmanow' ); ?>
			</div>

		<?php else : ?>

			<div
				x-data="{
					q: '',
					match(name){ return !this.q.trim() || name.toLowerCase().includes(this.q.toLowerCase().trim()); }
				}"
			>
				<!-- Search box -->
				<div class="relative mb-8 max-w-md">
					<span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
						<?php pn_icon( 'search', array( 'class' => 'h-4 w-4' ) ); ?>
					</span>
					<input
						type="search"
						x-model="q"
						placeholder="<?php esc_attr_e( 'Cerca marca…', 'pharmanow' ); ?>"
						class="w-full h-10 pl-9 pr-3 text-sm rounded-md border border-gray-200 bg-white focus:outline-none focus:ring-2 focus:ring-pharma-teal/30 focus:border-pharma-teal transition-colors"
					>
				</div>

				<!-- Marchi popolari (nascosti durante ricerca) -->
				<?php if ( ! empty( $pn_popular ) ) : ?>
					<section class="mb-10" x-show="!q.trim()">
						<h2 class="mb-3 text-sm font-semibold uppercase tracking-wider text-gray-500">
							<?php esc_html_e( 'Marchi più popolari', 'pharmanow' ); ?>
						</h2>
						<div class="flex flex-wrap gap-2">
							<?php foreach ( $pn_popular as $b ) :
								$link = get_term_link( $b['slug'], 'product_brand' );
								if ( is_wp_error( $link ) ) {
									continue;
								}
								?>
								<a
									href="<?php echo esc_url( $link ); ?>"
									class="rounded-full border border-gray-200 bg-white px-4 py-1.5 text-sm font-medium text-gray-700 transition-colors hover:border-pharma-teal hover:bg-pharma-teal/5 hover:text-pharma-teal"
								>
									<?php echo esc_html( $b['name'] ); ?>
								</a>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endif; ?>

				<!-- Indice alfabetico sticky -->
				<div class="sticky top-0 z-10 -mx-4 mb-6 border-y border-gray-200 bg-white/95 px-4 py-3 backdrop-blur">
					<div class="flex flex-wrap gap-1.5">
						<?php foreach ( $pn_letters_all as $l ) :
							$present = in_array( $l, $pn_letters_present, true );
							?>
							<a
								<?php echo $present ? 'href="#letter-' . esc_attr( $l ) . '"' : 'aria-disabled="true"'; ?>
								class="flex h-8 w-8 items-center justify-center rounded text-sm font-semibold transition-colors <?php echo $present ? 'text-gray-900 hover:bg-pharma-teal hover:text-white' : 'text-gray-300 cursor-default'; ?>"
							>
								<?php echo esc_html( $l ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>

				<!-- Empty state ricerca -->
				<p
					x-show="q.trim() && [...document.querySelectorAll('[data-brand-name]')].every(el => !el.dataset.brandName.toLowerCase().includes(q.toLowerCase().trim()))"
					x-cloak
					class="text-center text-sm text-gray-500"
				>
					<?php esc_html_e( 'Nessun marchio corrisponde alla ricerca.', 'pharmanow' ); ?>
				</p>

				<!-- Lista raggruppata -->
				<div class="space-y-8">
					<?php foreach ( $pn_grouped as $letter => $list ) : ?>
						<section
							id="letter-<?php echo esc_attr( $letter ); ?>"
							class="scroll-mt-20"
							x-show="q.trim() === '' || [...$el.querySelectorAll('[data-brand-name]')].some(el => match(el.dataset.brandName))"
						>
							<h2 class="mb-3 flex items-center gap-3 text-xl font-bold text-gray-900">
								<span class="flex h-8 w-8 items-center justify-center rounded bg-pharma-teal text-white">
									<?php echo esc_html( $letter ); ?>
								</span>
								<span class="text-sm font-normal text-gray-500">
									<?php
									$n = count( $list );
									printf(
										/* translators: %d numero marche */
										esc_html( _n( '%d marca', '%d marche', $n, 'pharmanow' ) ),
										(int) $n
									);
									?>
								</span>
							</h2>
							<ul class="grid gap-2 sm:grid-cols-2 md:grid-cols-3">
								<?php foreach ( $list as $b ) :
									$link = get_term_link( $b['slug'], 'product_brand' );
									if ( is_wp_error( $link ) ) {
										continue;
									}
									?>
									<li
										data-brand-name="<?php echo esc_attr( $b['name'] ); ?>"
										x-show="match('<?php echo esc_js( $b['name'] ); ?>')"
									>
										<a
											href="<?php echo esc_url( $link ); ?>"
											class="flex items-center justify-between rounded-md border border-gray-200 bg-white px-3 py-2 text-sm transition-colors hover:border-pharma-teal hover:bg-pharma-teal/5"
										>
											<span class="font-medium text-gray-700"><?php echo esc_html( $b['name'] ); ?></span>
											<span class="text-xs text-gray-400"><?php echo (int) $b['count']; ?></span>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</section>
					<?php endforeach; ?>
				</div>
			</div>

		<?php endif; ?>
	</div>
</div>

<?php
get_footer( 'shop' );
