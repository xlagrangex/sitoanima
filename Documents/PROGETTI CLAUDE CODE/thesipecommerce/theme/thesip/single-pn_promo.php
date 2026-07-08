<?php
/**
 * Pagina promozione — replica app/promozione/[slug]/promo-page-client.tsx.
 *
 * Servita su /promozione/<slug>/.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header( 'shop' );

while ( have_posts() ) :
	the_post();
	$pn_id         = get_the_ID();
	$pn_title      = get_the_title();
	$pn_excerpt    = has_excerpt() ? get_the_excerpt() : '';
	$pn_content    = get_the_content();
	// Hero: usa helper che fa fallback attachment ID → URL legacy.
	$pn_hero_d     = function_exists( 'pn_promo_hero_url' ) ? pn_promo_hero_url( $pn_id, 'desktop' ) : (string) get_post_meta( $pn_id, '_pn_promo_hero_desktop', true );
	$pn_hero_m     = function_exists( 'pn_promo_hero_url' ) ? pn_promo_hero_url( $pn_id, 'mobile' ) : (string) get_post_meta( $pn_id, '_pn_promo_hero_mobile', true );
	$pn_hero_alt   = (string) get_post_meta( $pn_id, '_pn_promo_hero_alt', true );
	$pn_coupon     = (string) get_post_meta( $pn_id, '_pn_promo_coupon', true );
	$pn_end_date   = (string) get_post_meta( $pn_id, '_pn_promo_end_date', true );
	$pn_is_expired = $pn_end_date && strtotime( $pn_end_date ) < strtotime( 'today' );

	$pn_products = function_exists( 'pn_promo_get_products' ) ? pn_promo_get_products( $pn_id ) : array();

	$pn_hero_alt_safe = $pn_hero_alt ?: $pn_title;
	?>

	<div class="bg-gray-50 min-h-screen">
		<main class="container max-w-7xl mx-auto px-4 py-8">

			<nav class="mb-6 text-sm text-gray-500" aria-label="<?php esc_attr_e( 'Breadcrumb', 'pharmanow' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-gray-700 transition-colors">
					<?php esc_html_e( 'Home', 'pharmanow' ); ?>
				</a>
				<span class="mx-2 text-gray-300">/</span>
				<span class="text-gray-900 font-medium"><?php echo esc_html( $pn_title ); ?></span>
			</nav>

			<?php if ( $pn_hero_d ) : ?>
				<div class="relative mb-8 overflow-hidden rounded-2xl">
					<picture>
						<?php if ( $pn_hero_m ) : ?>
							<source media="(max-width: 768px)" srcset="<?php echo esc_url( $pn_hero_m ); ?>">
						<?php endif; ?>
						<img
							src="<?php echo esc_url( $pn_hero_d ); ?>"
							alt="<?php echo esc_attr( $pn_hero_alt_safe ); ?>"
							class="h-auto w-full object-cover"
							fetchpriority="high"
						>
					</picture>
				</div>
			<?php endif; ?>

			<div class="mb-8 text-center">
				<h1 class="mb-3 text-3xl md:text-4xl font-bold text-gray-900 tracking-tight">
					<?php echo esc_html( $pn_title ); ?>
				</h1>
				<?php if ( $pn_excerpt ) : ?>
					<p class="mx-auto max-w-2xl text-gray-600"><?php echo esc_html( $pn_excerpt ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( $pn_is_expired ) : ?>

				<div class="mb-8 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-800">
					<span class="shrink-0 mt-0.5">
						<?php pn_icon( 'alert-triangle', array( 'class' => 'h-5 w-5' ) ); ?>
					</span>
					<div>
						<p class="font-medium"><?php esc_html_e( 'Promozione terminata', 'pharmanow' ); ?></p>
						<p class="text-sm">
							<?php esc_html_e( 'Questa promozione non è più attiva.', 'pharmanow' ); ?>
							<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalogo/' ) ); ?>" class="underline hover:text-amber-900">
								<?php esc_html_e( 'Scopri il nostro catalogo', 'pharmanow' ); ?>
							</a>
						</p>
					</div>
				</div>

			<?php else : ?>

				<?php if ( $pn_coupon ) : ?>
					<div class="mb-8" x-data="{ copied: false, copy() {
						navigator.clipboard.writeText('<?php echo esc_js( $pn_coupon ); ?>');
						this.copied = true;
						setTimeout(() => this.copied = false, 2000);
					} }">
						<div class="mx-auto max-w-md rounded-xl border-2 border-dashed border-emerald-400 bg-emerald-50 p-6 text-center">
							<p class="mb-2 text-sm font-medium text-emerald-700">
								<?php esc_html_e( 'Usa il codice sconto', 'pharmanow' ); ?>
							</p>
							<div class="flex items-center justify-center gap-3">
								<span class="text-2xl font-bold tracking-wider text-emerald-800">
									<?php echo esc_html( $pn_coupon ); ?>
								</span>
								<button
									type="button"
									@click="copy()"
									class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white transition-colors hover:bg-emerald-700"
								>
									<span x-show="!copied">
										<?php pn_icon( 'copy', array( 'class' => 'h-4 w-4' ) ); ?>
									</span>
									<span x-show="copied" x-cloak>
										<?php pn_icon( 'check', array( 'class' => 'h-4 w-4' ) ); ?>
									</span>
									<span x-text="copied ? '<?php echo esc_js( __( 'Copiato!', 'pharmanow' ) ); ?>' : '<?php echo esc_js( __( 'Copia', 'pharmanow' ) ); ?>'"></span>
								</button>
							</div>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( $pn_content ) : ?>
					<div class="mx-auto max-w-3xl prose prose-sm md:prose-base mb-10">
						<?php echo wp_kses_post( apply_filters( 'the_content', $pn_content ) ); ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $pn_products ) ) : ?>
					<div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
						<?php foreach ( $pn_products as $pn_p ) :
							// Render via product-card partial coerente con il resto del tema.
							get_template_part( 'template-parts/shop/product-card', null, array( 'product' => $pn_p ) );
						endforeach; ?>
					</div>
				<?php else : ?>
					<div class="py-16 text-center text-gray-500">
						<p class="text-lg"><?php esc_html_e( 'Nessun prodotto disponibile al momento', 'pharmanow' ); ?></p>
					</div>
				<?php endif; ?>

			<?php endif; ?>
		</main>
	</div>

	<?php
endwhile;

get_footer( 'shop' );
