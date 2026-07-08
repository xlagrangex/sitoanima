<?php
/**
 * Template Name: Password dimenticata
 *
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main class="container mx-auto max-w-md px-4 py-10 md:py-16">
	<div class="text-center mb-8">
		<h1 class="text-3xl font-bold uppercase mb-2"><?php esc_html_e( "Recupera l'accesso", 'pharmanow' ); ?></h1>
		<p class="text-sm text-muted-foreground"><?php esc_html_e( 'Inserisci la tua email per ricevere il link di reset password.', 'pharmanow' ); ?></p>
	</div>

	<?php get_template_part( 'template-parts/account/lost-password-form' ); ?>

	<p class="mt-6 text-center text-sm">
		<a href="<?php echo esc_url( home_url( '/login/' ) ); ?>" class="text-pharma-teal hover:underline"><?php esc_html_e( '← Torna al login', 'pharmanow' ); ?></a>
	</p>
</main>
<?php
get_footer();
