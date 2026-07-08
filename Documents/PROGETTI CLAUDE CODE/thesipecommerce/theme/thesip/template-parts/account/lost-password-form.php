<?php
/**
 * Lost password form. Usa retrieve_password() di WP nativo.
 *
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

$pn_sent = isset( $_GET['sent'] ) && '1' === $_GET['sent'];
$pn_err  = isset( $_GET['err'] ) ? sanitize_text_field( wp_unslash( $_GET['err'] ) ) : '';
?>

<?php if ( $pn_sent ) : ?>
	<div class="rounded-xl border border-emerald-200 bg-emerald-50 p-6 text-center">
		<div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
			<?php pn_icon( 'check-circle-2', array( 'class' => 'h-6 w-6' ) ); ?>
		</div>
		<h3 class="mb-1 text-lg font-semibold text-emerald-800"><?php esc_html_e( 'Controlla la tua email', 'pharmanow' ); ?></h3>
		<p class="text-sm text-emerald-700"><?php esc_html_e( "Se l'email è registrata, abbiamo inviato un link per reimpostare la password.", 'pharmanow' ); ?></p>
	</div>
<?php else : ?>
	<?php if ( '' !== $pn_err ) : ?>
		<div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">
			<?php esc_html_e( "Errore nell'invio dell'email. Riprova.", 'pharmanow' ); ?>
		</div>
	<?php endif; ?>
	<form method="post" action="<?php echo esc_url( home_url( '/password-dimenticata/' ) ); ?>" class="space-y-4 rounded-xl border bg-card p-6 shadow-sm">
		<input type="hidden" name="pn_action" value="pn_lost_password">
		<?php wp_nonce_field( 'pn_lost_password', '_pn_nonce' ); ?>

		<div class="space-y-1.5">
			<label for="user_login" class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"><?php esc_html_e( 'Email', 'pharmanow' ); ?></label>
			<div class="relative">
				<span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-muted-foreground"><?php pn_icon( 'mail', array( 'class' => 'h-4 w-4' ) ); ?></span>
				<input type="email" name="pn_email" id="user_login" required autocomplete="email" placeholder="email@esempio.com" class="w-full h-11 rounded-md border border-input bg-background pl-10 pr-3 text-sm focus:outline-none focus:ring-2 focus:ring-pharma-teal/30 focus:border-pharma-teal">
			</div>
		</div>

		<button type="submit" class="pn-atc-btn w-full h-12 rounded-md bg-pharma-teal text-white text-base font-semibold hover:bg-pharma-teal-dark">
			<span class="pn-atc-default">
				<?php pn_icon( 'mail', array( 'class' => 'h-5 w-5' ) ); ?>
				<?php esc_html_e( 'Invia link reset', 'pharmanow' ); ?>
			</span>
			<span class="pn-atc-success">
				<?php pn_icon( 'check-circle-2', array( 'class' => 'h-5 w-5' ) ); ?>
				<?php esc_html_e( 'Invio...', 'pharmanow' ); ?>
			</span>
		</button>
	</form>
<?php endif; ?>
