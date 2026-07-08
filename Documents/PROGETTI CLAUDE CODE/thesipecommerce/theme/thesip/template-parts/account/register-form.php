<?php
/**
 * Register form. POST a /?action=pn_register che usa wp_create_user + wc.
 *
 * @var array $args { redirect: string }
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

$pn_redirect = $args['redirect'] ?? wc_get_page_permalink( 'myaccount' );
$pn_err      = isset( $_GET['err'] ) ? sanitize_text_field( wp_unslash( $_GET['err'] ) ) : '';
$pn_err_map  = array(
	'email_exists'    => __( 'Esiste già un account con questa email.', 'pharmanow' ),
	'invalid_email'   => __( "L'email non è valida.", 'pharmanow' ),
	'weak_password'   => __( 'La password deve essere di almeno 8 caratteri.', 'pharmanow' ),
	'privacy'         => __( "Devi accettare l'informativa privacy.", 'pharmanow' ),
	'invalid_nonce'   => __( 'Sessione scaduta. Ricarica la pagina e riprova.', 'pharmanow' ),
	'generic'         => __( 'Impossibile completare la registrazione. Riprova.', 'pharmanow' ),
);
$pn_err_msg  = $pn_err && isset( $pn_err_map[ $pn_err ] ) ? $pn_err_map[ $pn_err ] : '';
?>
<?php if ( '' !== $pn_err_msg ) : ?>
	<div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">
		<?php echo esc_html( $pn_err_msg ); ?>
	</div>
<?php endif; ?>

<form method="post" action="<?php echo esc_url( home_url( '/registrati/' ) ); ?>" class="space-y-4 rounded-xl border bg-card p-6 shadow-sm">
	<input type="hidden" name="pn_action" value="pn_register">
	<?php wp_nonce_field( 'pn_register', '_pn_nonce' ); ?>
	<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $pn_redirect ); ?>">

	<div class="grid grid-cols-2 gap-3">
		<div class="space-y-1.5">
			<label for="first_name" class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"><?php esc_html_e( 'Nome', 'pharmanow' ); ?></label>
			<input type="text" name="first_name" id="first_name" required minlength="2" autocomplete="given-name" value="<?php echo esc_attr( $_POST['first_name'] ?? '' ); ?>" class="w-full h-11 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-pharma-teal/30 focus:border-pharma-teal">
		</div>
		<div class="space-y-1.5">
			<label for="last_name" class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"><?php esc_html_e( 'Cognome', 'pharmanow' ); ?></label>
			<input type="text" name="last_name" id="last_name" required minlength="2" autocomplete="family-name" value="<?php echo esc_attr( $_POST['last_name'] ?? '' ); ?>" class="w-full h-11 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-pharma-teal/30 focus:border-pharma-teal">
		</div>
	</div>

	<div class="space-y-1.5">
		<label for="reg_email" class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"><?php esc_html_e( 'Email', 'pharmanow' ); ?></label>
		<div class="relative">
			<span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-muted-foreground"><?php pn_icon( 'mail', array( 'class' => 'h-4 w-4' ) ); ?></span>
			<input type="email" name="pn_email" id="reg_email" required autocomplete="email" placeholder="email@esempio.com" value="<?php echo esc_attr( $_POST['pn_email'] ?? '' ); ?>" class="w-full h-11 rounded-md border border-input bg-background pl-10 pr-3 text-sm focus:outline-none focus:ring-2 focus:ring-pharma-teal/30 focus:border-pharma-teal">
		</div>
	</div>

	<div class="space-y-1.5">
		<label for="reg_password" class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"><?php esc_html_e( 'Password', 'pharmanow' ); ?></label>
		<div class="relative" x-data="{ show: false }">
			<span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-muted-foreground"><?php pn_icon( 'lock', array( 'class' => 'h-4 w-4' ) ); ?></span>
			<input :type="show ? 'text' : 'password'" name="pn_password" id="reg_password" required minlength="8" autocomplete="new-password" class="w-full h-11 rounded-md border border-input bg-background pl-10 pr-12 text-sm focus:outline-none focus:ring-2 focus:ring-pharma-teal/30 focus:border-pharma-teal">
			<button type="button" @click="show = !show" class="absolute inset-y-0 right-3 flex items-center text-xs text-muted-foreground hover:text-pharma-teal">
				<span x-show="!show"><?php esc_html_e( 'Mostra', 'pharmanow' ); ?></span>
				<span x-show="show" x-cloak><?php esc_html_e( 'Nascondi', 'pharmanow' ); ?></span>
			</button>
		</div>
		<p class="text-xs text-muted-foreground"><?php esc_html_e( 'Minimo 8 caratteri.', 'pharmanow' ); ?></p>
	</div>

	<label class="flex items-start gap-2 text-xs text-muted-foreground">
		<input type="checkbox" name="privacy" value="1" required class="mt-0.5 h-4 w-4 rounded border-input text-pharma-teal focus:ring-pharma-teal">
		<span>
			<?php esc_html_e( "Accetto l'", 'pharmanow' ); ?><a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>" class="text-pharma-teal hover:underline"><?php esc_html_e( 'informativa privacy', 'pharmanow' ); ?></a>
			<?php esc_html_e( ' e i ', 'pharmanow' ); ?>
			<a href="<?php echo esc_url( home_url( '/termini/' ) ); ?>" class="text-pharma-teal hover:underline"><?php esc_html_e( 'termini di servizio', 'pharmanow' ); ?></a>.
		</span>
	</label>

	<button type="submit" class="pn-atc-btn w-full h-12 rounded-md bg-pharma-teal text-white text-base font-semibold hover:bg-pharma-teal-dark">
		<span class="pn-atc-default">
			<?php pn_icon( 'user-plus', array( 'class' => 'h-5 w-5' ) ); ?>
			<?php esc_html_e( 'Crea account', 'pharmanow' ); ?>
		</span>
		<span class="pn-atc-success">
			<?php pn_icon( 'check-circle-2', array( 'class' => 'h-5 w-5' ) ); ?>
			<?php esc_html_e( 'Creazione...', 'pharmanow' ); ?>
		</span>
	</button>
</form>
