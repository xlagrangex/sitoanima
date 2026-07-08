<?php
/**
 * Pagina risultati ricerca — replica components/shop/search/SearchPage.tsx.
 *
 * Caricato per /ricerca/?q=X via rewrite virtuale (vedi inc/search/search.php).
 * Anche fallback per WP search nativo /?s=X.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header( 'shop' );

$pn_q = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : ( get_search_query() ?: '' );

// Se siamo via /ricerca/ il query var 's' non è valorizzato — eseguiamo
// noi una WP_Query manuale così i risultati siano sempre prodotti pubblicati.
$pn_total    = 0;
$pn_products = array();

if ( '' !== $pn_q && function_exists( 'pn_search_products' ) ) {
	$res         = pn_search_products( $pn_q, 48 );
	$pn_total    = (int) $res['total'];
	// pn_search_products limita a 48 ma serve la lista per la grid: rifacciamo
	// il fetch oggetti completi via WP_Query con post__in degli ID.
	$ids = array_map( function ( $p ) { return (int) $p['id']; }, $res['products'] );
	if ( $ids ) {
		$pn_query = new WP_Query(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => count( $ids ),
				'post__in'       => $ids,
				'orderby'        => 'post__in',
				'no_found_rows'  => true,
			)
		);
		$pn_products = $pn_query->posts;
	}
}
?>

<div class="bg-white">
	<div class="container mx-auto max-w-7xl px-4 py-6 md:py-10">

		<nav class="mb-3 text-sm text-gray-500" aria-label="<?php esc_attr_e( 'Breadcrumb', 'pharmanow' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-pharma-teal transition-colors">
				<?php esc_html_e( 'Home', 'pharmanow' ); ?>
			</a>
			<span class="mx-2 text-gray-300">/</span>
			<span class="text-gray-900 font-medium"><?php esc_html_e( 'Ricerca', 'pharmanow' ); ?></span>
		</nav>

		<header class="mb-8 space-y-1">
			<h1 class="text-2xl md:text-3xl font-bold text-gray-900 tracking-tight">
				<?php
				if ( $pn_total > 0 ) {
					printf(
						/* translators: 1: numero risultati, 2: query */
						esc_html__( '%1$s risultati per %2$s', 'pharmanow' ),
						'<span>' . esc_html( number_format_i18n( $pn_total ) ) . '</span>',
						'<span class="text-pharma-teal">&ldquo;' . esc_html( $pn_q ) . '&rdquo;</span>'
					);
				} else {
					printf(
						/* translators: %s query */
						esc_html__( 'Nessun risultato per %s', 'pharmanow' ),
						'<span class="text-pharma-teal">&ldquo;' . esc_html( $pn_q ) . '&rdquo;</span>'
					);
				}
				?>
			</h1>
			<?php if ( 0 === $pn_total ) : ?>
				<p class="text-sm text-gray-500">
					<?php
					printf(
						/* translators: %s catalog link */
						esc_html__( 'Prova con un termine diverso, controlla l\'ortografia o esplora il %s.', 'pharmanow' ),
						'<a href="' . esc_url( wc_get_page_permalink( 'shop' ) ?: home_url( '/catalogo/' ) ) . '" class="text-pharma-teal underline hover:text-pharma-teal-dark">' . esc_html__( 'catalogo completo', 'pharmanow' ) . '</a>'
					);
					?>
				</p>
			<?php endif; ?>
		</header>

		<?php if ( 0 === $pn_total ) : ?>

			<div class="flex flex-col items-center justify-center gap-4 rounded-lg border border-dashed border-gray-300 py-16 text-center">
				<span class="text-gray-400">
					<?php pn_icon( 'package-open', array( 'class' => 'h-12 w-12' ) ); ?>
				</span>
				<p class="text-sm text-gray-500"><?php esc_html_e( 'Nessun prodotto corrisponde alla ricerca.', 'pharmanow' ); ?></p>
			</div>

		<?php else : ?>

			<div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4">
				<?php
				foreach ( $pn_products as $pn_post ) :
					$pn_wcp = function_exists( 'wc_get_product' ) ? wc_get_product( $pn_post->ID ) : null;
					if ( ! $pn_wcp instanceof WC_Product ) {
						continue;
					}
					get_template_part( 'template-parts/shop/product-card', null, array( 'product' => $pn_wcp ) );
				endforeach;
				?>
			</div>

		<?php endif; ?>
	</div>
</div>

<?php
get_footer( 'shop' );
