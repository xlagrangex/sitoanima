<?php
/**
 * Template Name: Reset password
 *
 * Pagina che gestisce il reset password dopo che l'utente clicca il link
 * nell'email di reset. Verifica key+login e mostra form per nuova password.
 *
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

// Se l'utente è loggato, redirect a /account/profilo/.
if ( is_user_logged_in() ) {
	wp_safe_redirect( home_url( '/account/profilo/' ) );
	exit;
}

$pn_key   = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : '';
$pn_login = isset( $_GET['login'] ) ? sanitize_text_field( wp_unslash( $_GET['login'] ) ) : '';
$pn_done  = isset( $_GET['done'] ) && '1' === $_GET['done'];
$pn_err   = isset( $_GET['err'] ) ? sanitize_text_field( wp_unslash( $_GET['err'] ) ) : '';

// Validazione key (solo se non è il caso "done" e non c'è già un POST in arrivo).
$pn_user = null;
if ( ! $pn_done && '' !== $pn_key && '' !== $pn_login ) {
	$pn_user = check_password_reset_key( $pn_key, $pn_login );
}

get_header();
?>
<main class="container mx-auto max-w-md px-4 py-10 md:py-16">

	<?php if ( $pn_done ) : ?>
		<div class="rounded-xl border border-emerald-200 bg-emerald-50 p-8 text-center">
			<div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
				<?php pn_icon( 'check-circle-2', array( 'class' => 'h-7 w-7' ) ); ?>
			</div>
			<h1 class="mb-2 text-2xl font-bold text-emerald-800"><?php esc_html_e( 'Password aggiornata', 'pharmanow' ); ?></h1>
			<p class="mb-6 text-sm text-emerald-700"><?php esc_html_e( 'La tua password è stata reimpostata. Ora puoi accedere con la nuova password.', 'pharmanow' ); ?></p>
			<a href="<?php echo esc_url( home_url( '/login/' ) ); ?>" class="inline-flex h-11 items-center gap-2 rounded-md bg-pharma-teal px-6 text-sm font-semibold text-white hover:bg-pharma-teal-dark">
				<?php esc_html_e( 'Vai al login', 'pharmanow' ); ?>
			</a>
		</div>

	<?php elseif ( ! $pn_user || is_wp_error( $pn_user ) ) : ?>
		<div class="rounded-xl border border-rose-200 bg-rose-50 p-8 text-center">
			<div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-rose-100 text-rose-600">
				<?php pn_icon( 'x', array( 'class' => 'h-7 w-7' ) ); ?>
			</div>
			<h1 class="mb-2 text-2xl font-bold text-rose-800"><?php esc_html_e( 'Link scaduto o non valido', 'pharmanow' ); ?></h1>
			<p class="mb-6 text-sm text-rose-700"><?php esc_html_e( 'Il link per il reset della password è scaduto o non è valido. Richiedi un nuovo link.', 'pharmanow' ); ?></p>
			<a href="<?php echo esc_url( home_url( '/password-dimenticata/' ) ); ?>" class="inline-flex h-11 items-center gap-2 rounded-md bg-pharma-teal px-6 text-sm font-semibold text-white hover:bg-pharma-teal-dark">
				<?php esc_html_e( 'Richiedi nuovo link', 'pharmanow' ); ?>
			</a>
		</div>

	<?php else : ?>
		<div class="text-center mb-8">
			<h1 class="text-3xl font-bold uppercase mb-2"><?php esc_html_e( 'Imposta nuova password', 'pharmanow' ); ?></h1>
			<p class="text-sm text-muted-foreground">
				<?php
				/* translators: %s: user email */
				printf( esc_html__( 'Account: %s', 'pharmanow' ), '<strong>' . esc_html( $pn_user->user_email ) . '</strong>' );
				?>
			</p>
		</div>

		<?php if ( '' !== $pn_err ) : ?>
			<div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">
				<?php
				echo esc_html(
					'mismatch' === $pn_err ? __( 'Le password non coincidono.', 'pharmanow' )
					: ( 'weak' === $pn_err ? __( 'La password deve essere di almeno 8 caratteri.', 'pharmanow' )
					: __( 'Errore. Riprova.', 'pharmanow' ) )
				);
				?>
			</div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( home_url( '/reset-password/' ) ); ?>" class="space-y-4 rounded-xl border bg-card p-6 shadow-sm">
			<input type="hidden" name="pn_action" value="pn_reset_password">
			<?php wp_nonce_field( 'pn_reset_password', '_pn_nonce' ); ?>
			<input type="hidden" name="pn_key" value="<?php echo esc_attr( $pn_key ); ?>">
			<input type="hidden" name="pn_login" value="<?php echo esc_attr( $pn_login ); ?>">

			<div class="space-y-1.5" x-data="{ show: false }">
				<label for="new_pass1" class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"><?php esc_html_e( 'Nuova password', 'pharmanow' ); ?></label>
				<div class="relative">
					<span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-muted-foreground"><?php pn_icon( 'lock', array( 'class' => 'h-4 w-4' ) ); ?></span>
					<input :type="show ? 'text' : 'password'" name="pn_password" id="new_pass1" required minlength="8" autocomplete="new-password" class="w-full h-11 rounded-md border border-input bg-background pl-10 pr-12 text-sm focus:outline-none focus:ring-2 focus:ring-pharma-teal/30 focus:border-pharma-teal">
					<button type="button" @click="show = !show" class="absolute inset-y-0 right-3 flex items-center text-xs text-muted-foreground hover:text-pharma-teal">
						<span x-show="!show"><?php esc_html_e( 'Mostra', 'pharmanow' ); ?></span>
						<span x-show="show" x-cloak><?php esc_html_e( 'Nascondi', 'pharmanow' ); ?></span>
					</button>
				</div>
				<p class="text-xs text-muted-foreground"><?php esc_html_e( 'Minimo 8 caratteri.', 'pharmanow' ); ?></p>
			</div>

			<div class="space-y-1.5">
				<label for="new_pass2" class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"><?php esc_html_e( 'Conferma password', 'pharmanow' ); ?></label>
				<div class="relative">
					<span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-muted-foreground"><?php pn_icon( 'lock', array( 'class' => 'h-4 w-4' ) ); ?></span>
					<input type="password" name="pn_password_confirm" id="new_pass2" required minlength="8" autocomplete="new-password" class="w-full h-11 rounded-md border border-input bg-background pl-10 pr-3 text-sm focus:outline-none focus:ring-2 focus:ring-pharma-teal/30 focus:border-pharma-teal">
				</div>
			</div>

			<button type="submit" class="pn-atc-btn w-full h-12 rounded-md bg-pharma-teal text-white text-base font-semibold hover:bg-pharma-teal-dark">
				<span class="pn-atc-default">
					<?php pn_icon( 'check-circle-2', array( 'class' => 'h-5 w-5' ) ); ?>
					<?php esc_html_e( 'Imposta password', 'pharmanow' ); ?>
				</span>
				<span class="pn-atc-success">
					<?php pn_icon( 'check-circle-2', array( 'class' => 'h-5 w-5' ) ); ?>
					<?php esc_html_e( 'Salvataggio...', 'pharmanow' ); ?>
				</span>
			</button>
		</form>
	<?php endif; ?>
</main>
<?php
get_footer();
