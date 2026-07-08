<?php
/**
 * CTA "Parla con un farmacista" — replica del live pharmanow.com.
 *
 * Doppio bottone: WhatsApp (primario) + telefono (secondario).
 * Numero whatsapp da pn_shop_settings()['whatsapp'] (Customizer override).
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn_about_url = home_url( '/chi-siamo' );
?>

<section class="container max-w-7xl mx-auto px-4 py-10">
	<div class="relative overflow-hidden rounded-2xl border border-cyan-100 bg-gradient-to-br from-cyan-50 via-white to-sky-50 p-6 md:p-10">
		<span aria-hidden="true" class="absolute -right-16 -bottom-16 h-56 w-56 rounded-full bg-cyan-100/60 blur-3xl"></span>
		<span aria-hidden="true" class="absolute -left-10 -top-10 h-32 w-32 rounded-full bg-sky-100/60 blur-2xl"></span>

		<div class="relative grid grid-cols-1 md:grid-cols-[1fr_auto] gap-6 items-center">
			<div class="flex items-start gap-4">
				<div class="hidden sm:flex shrink-0 h-14 w-14 items-center justify-center rounded-2xl bg-pharma-teal text-white shadow-lg shadow-cyan-500/30">
					<?php pn_icon( 'flask-conical', array( 'class' => 'h-7 w-7' ) ); ?>
				</div>
				<div>
					<p class="text-xs font-semibold uppercase tracking-wide text-pharma-accent">
						<?php esc_html_e( 'Dietro le quinte', 'pharmanow' ); ?>
					</p>
					<h2 class="mt-1 text-xl md:text-2xl font-bold tracking-tight">
						<?php esc_html_e( 'Manuel e Giuliano, i biologi marini dietro The SIP', 'pharmanow' ); ?>
					</h2>
					<p class="mt-2 text-sm md:text-base text-muted-foreground max-w-xl">
						<?php esc_html_e( 'Due biologi marini, 25 illustratori e un amore per il mare: scopri come è nato il progetto a Napoli e chi c\'è dietro ogni carta.', 'pharmanow' ); ?>
					</p>
				</div>
			</div>

			<div class="flex flex-wrap gap-3 md:justify-end">
				<a
					href="<?php echo esc_url( $pn_about_url ); ?>"
					data-slot="button"
					class="inline-flex items-center justify-center gap-2 h-12 px-6 rounded-xl font-semibold btn-gradient text-white transition-colors shadow-sm"
				>
					<?php pn_icon( 'users', array( 'class' => 'h-5 w-5' ) ); ?>
					<?php esc_html_e( 'Scopri il progetto', 'pharmanow' ); ?>
				</a>
			</div>
		</div>
	</div>
</section>
