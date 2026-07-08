<?php
/**
 * Template Name: Registrati
 *
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

get_header();

$pn_redirect = isset( $_GET['redirect'] ) ? esc_url_raw( wp_unslash( $_GET['redirect'] ) ) : wc_get_page_permalink( 'myaccount' );
?>
<main class="container mx-auto max-w-md px-4 py-10 md:py-16">
	<div class="text-center mb-8">
		<h1 class="text-3xl font-bold uppercase mb-2"><?php esc_html_e( 'Crea un account', 'pharmanow' ); ?></h1>
		<p class="text-sm text-muted-foreground"><?php esc_html_e( "È gratis. Crea l'account in pochi secondi.", 'pharmanow' ); ?></p>
	</div>

	<?php get_template_part( 'template-parts/account/register-form', null, array( 'redirect' => $pn_redirect ) ); ?>

	<p class="mt-6 text-center text-sm text-muted-foreground">
		<?php esc_html_e( 'Hai già un account?', 'pharmanow' ); ?>
		<a href="<?php echo esc_url( add_query_arg( 'redirect', $pn_redirect, home_url( '/login/' ) ) ); ?>" class="font-semibold text-pharma-teal hover:underline"><?php esc_html_e( 'Accedi', 'pharmanow' ); ?></a>
	</p>
</main>
<?php
get_footer();
