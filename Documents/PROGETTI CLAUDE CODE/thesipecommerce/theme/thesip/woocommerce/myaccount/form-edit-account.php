<?php
/**
 * Profilo (edit-account). Replica `ProfileForm.tsx` del Next.
 *
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

$pn_user  = wp_get_current_user();
$pn_phone = (string) get_user_meta( $pn_user->ID, 'billing_phone', true );
$pn_saved = isset( $_GET['saved'] ) && '1' === $_GET['saved'];
$pn_err   = isset( $_GET['err'] ) ? sanitize_text_field( wp_unslash( $_GET['err'] ) ) : '';
?>
<div class="space-y-6">
	<div>
		<h2 class="text-2xl font-bold"><?php esc_html_e( 'Profilo', 'pharmanow' ); ?></h2>
		<p class="mt-1 text-sm text-muted-foreground"><?php esc_html_e( 'Gestisci i tuoi dati personali', 'pharmanow' ); ?></p>
	</div>

	<?php if ( $pn_saved ) : ?>
		<div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">
			<?php esc_html_e( 'Profilo aggiornato con successo.', 'pharmanow' ); ?>
		</div>
	<?php elseif ( '' !== $pn_err ) : ?>
		<div class="rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">
			<?php
			echo esc_html(
				'weak' === $pn_err
					? __( 'La nuova password deve essere di almeno 8 caratteri.', 'pharmanow' )
					: __( 'Impossibile salvare. Riprova.', 'pharmanow' )
			);
			?>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-account' ) ); ?>" class="space-y-4 rounded-xl border bg-card p-5">
		<input type="hidden" name="pn_action" value="pn_profile_update">
		<?php wp_nonce_field( 'pn_profile_update', '_pn_nonce' ); ?>

		<div class="space-y-1.5">
			<label class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"><?php esc_html_e( 'Email', 'pharmanow' ); ?></label>
			<input type="email" value="<?php echo esc_attr( $pn_user->user_email ); ?>" disabled class="w-full h-11 rounded-md border border-input bg-muted/30 px-3 text-sm text-muted-foreground">
			<p class="text-xs text-muted-foreground"><?php esc_html_e( "L'email non può essere modificata", 'pharmanow' ); ?></p>
		</div>

		<div class="grid grid-cols-1 gap-3 md:grid-cols-2">
			<div class="space-y-1.5">
				<label for="first_name" class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"><?php esc_html_e( 'Nome', 'pharmanow' ); ?></label>
				<input type="text" name="first_name" id="first_name" required minlength="2" value="<?php echo esc_attr( $pn_user->first_name ); ?>" class="w-full h-11 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-pharma-teal/30 focus:border-pharma-teal">
			</div>
			<div class="space-y-1.5">
				<label for="last_name" class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"><?php esc_html_e( 'Cognome', 'pharmanow' ); ?></label>
				<input type="text" name="last_name" id="last_name" required minlength="2" value="<?php echo esc_attr( $pn_user->last_name ); ?>" class="w-full h-11 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-pharma-teal/30 focus:border-pharma-teal">
			</div>
		</div>

		<div class="space-y-1.5">
			<label for="billing_phone" class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"><?php esc_html_e( 'Telefono', 'pharmanow' ); ?></label>
			<input type="tel" name="billing_phone" id="billing_phone" autocomplete="tel" value="<?php echo esc_attr( $pn_phone ); ?>" class="w-full h-11 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-pharma-teal/30 focus:border-pharma-teal">
		</div>

		<div class="border-t pt-4 space-y-1.5" x-data="{ open: false }">
			<button type="button" @click="open = !open" class="text-sm font-medium text-pharma-teal hover:underline">
				<span x-show="!open"><?php esc_html_e( 'Cambia password', 'pharmanow' ); ?></span>
				<span x-show="open" x-cloak><?php esc_html_e( 'Annulla cambio password', 'pharmanow' ); ?></span>
			</button>
			<div x-show="open" x-cloak class="pt-2 space-y-1.5">
				<label for="new_password" class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"><?php esc_html_e( 'Nuova password', 'pharmanow' ); ?></label>
				<input type="password" name="pn_new_password" id="new_password" minlength="8" autocomplete="new-password" class="w-full h-11 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-pharma-teal/30 focus:border-pharma-teal">
				<p class="text-xs text-muted-foreground"><?php esc_html_e( 'Minimo 8 caratteri.', 'pharmanow' ); ?></p>
			</div>
		</div>

		<div class="flex justify-end pt-2">
			<button type="submit" class="pn-atc-btn h-11 px-6 rounded-md bg-pharma-teal text-white text-sm font-semibold hover:bg-pharma-teal-dark">
				<span class="pn-atc-default"><?php esc_html_e( 'Salva modifiche', 'pharmanow' ); ?></span>
				<span class="pn-atc-success"><?php pn_icon( 'check-circle-2', array( 'class' => 'h-4 w-4' ) ); ?> <?php esc_html_e( 'Salvato', 'pharmanow' ); ?></span>
			</button>
		</div>
	</form>
</div>
