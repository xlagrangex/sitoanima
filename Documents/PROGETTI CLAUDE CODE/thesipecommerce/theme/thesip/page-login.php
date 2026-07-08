<?php
/**
 * Template Name: Login
 *
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

get_header();

$pn_redirect = isset( $_GET['redirect'] ) ? esc_url_raw( wp_unslash( $_GET['redirect'] ) ) : wc_get_page_permalink( 'myaccount' );
$pn_error    = isset( $_GET['login'] ) ? sanitize_text_field( wp_unslash( $_GET['login'] ) ) : '';
?>
<main class="container mx-auto max-w-md px-4 py-10 md:py-16">
	<div class="text-center mb-8">
		<h1 class="text-3xl font-bold uppercase mb-2"><?php esc_html_e( 'Accedi a Pharmanow', 'pharmanow' ); ?></h1>
		<p class="text-sm text-muted-foreground"><?php esc_html_e( 'Inserisci email e password per entrare nel tuo account.', 'pharmanow' ); ?></p>
	</div>

	<?php if ( '' !== $pn_error ) : ?>
		<div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">
			<?php esc_html_e( "Si è verificato un errore. Riprova o usa un'altra email.", 'pharmanow' ); ?>
		</div>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/account/login-form', null, array( 'redirect' => $pn_redirect ) ); ?>

	<div class="mt-6 space-y-3 text-center text-sm">
		<p class="text-muted-foreground">
			<?php esc_html_e( 'Non hai un account?', 'pharmanow' ); ?>
			<a href="<?php echo esc_url( add_query_arg( 'redirect', $pn_redirect, home_url( '/registrati/' ) ) ); ?>" class="font-semibold text-pharma-teal hover:underline"><?php esc_html_e( 'Registrati', 'pharmanow' ); ?></a>
		</p>
		<p class="text-xs text-muted-foreground">
			<a href="<?php echo esc_url( home_url( '/password-dimenticata/' ) ); ?>" class="hover:text-pharma-teal hover:underline"><?php esc_html_e( 'Password dimenticata?', 'pharmanow' ); ?></a>
		</p>
	</div>
</main>
<?php
get_footer();
