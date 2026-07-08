<?php
/**
 * Login form. POST a wp-login.php (action=login) con redirect dopo successo.
 *
 * @var array $args { redirect: string }
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

$pn_redirect = $args['redirect'] ?? wc_get_page_permalink( 'myaccount' );
$pn_err      = isset( $_GET['err'] ) ? sanitize_text_field( wp_unslash( $_GET['err'] ) ) : '';
$pn_err_map  = array(
	'invalid_nonce'   => __( 'Sessione scaduta. Ricarica la pagina e riprova.', 'pharmanow' ),
	'missing'         => __( 'Inserisci email e password.', 'pharmanow' ),
	'invalid'         => __( 'Email o password non corrette.', 'pharmanow' ),
);
$pn_err_msg = $pn_err && isset( $pn_err_map[ $pn_err ] ) ? $pn_err_map[ $pn_err ] : '';
?>
<?php if ( '' !== $pn_err_msg ) : ?>
	<div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">
		<?php echo esc_html( $pn_err_msg ); ?>
	</div>
<?php endif; ?>
<form method="post" action="<?php echo esc_url( home_url( '/login/' ) ); ?>" class="space-y-4 rounded-xl border bg-card p-6 shadow-sm">
	<input type="hidden" name="pn_action" value="pn_login">
	<?php wp_nonce_field( 'pn_login', '_pn_nonce' ); ?>
	<div class="space-y-1.5">
		<label for="user_login" class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"><?php esc_html_e( 'Email', 'pharmanow' ); ?></label>
		<div class="relative">
			<span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-muted-foreground"><?php pn_icon( 'mail', array( 'class' => 'h-4 w-4' ) ); ?></span>
			<input type="email" name="pn_email" id="user_login" required autocomplete="email" placeholder="email@esempio.com" class="w-full h-11 rounded-md border border-input bg-background pl-10 pr-3 text-sm focus:outline-none focus:ring-2 focus:ring-pharma-teal/30 focus:border-pharma-teal">
		</div>
	</div>

	<div class="space-y-1.5">
		<div class="flex items-center justify-between">
			<label for="user_pass" class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"><?php esc_html_e( 'Password', 'pharmanow' ); ?></label>
			<a href="<?php echo esc_url( home_url( '/password-dimenticata/' ) ); ?>" class="text-xs text-pharma-teal hover:underline"><?php esc_html_e( 'Dimenticata?', 'pharmanow' ); ?></a>
		</div>
		<div class="relative" x-data="{ show: false }">
			<span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-muted-foreground"><?php pn_icon( 'lock', array( 'class' => 'h-4 w-4' ) ); ?></span>
			<input :type="show ? 'text' : 'password'" name="pn_password" id="user_pass" required autocomplete="current-password" class="w-full h-11 rounded-md border border-input bg-background pl-10 pr-12 text-sm focus:outline-none focus:ring-2 focus:ring-pharma-teal/30 focus:border-pharma-teal">
			<button type="button" @click="show = !show" class="absolute inset-y-0 right-3 flex items-center text-xs text-muted-foreground hover:text-pharma-teal" :aria-label="show ? 'Nascondi password' : 'Mostra password'">
				<span x-show="!show"><?php esc_html_e( 'Mostra', 'pharmanow' ); ?></span>
				<span x-show="show" x-cloak><?php esc_html_e( 'Nascondi', 'pharmanow' ); ?></span>
			</button>
		</div>
	</div>

	<label class="flex items-center gap-2 text-sm text-muted-foreground">
		<input type="checkbox" name="rememberme" value="forever" class="h-4 w-4 rounded border-input text-pharma-teal focus:ring-pharma-teal">
		<?php esc_html_e( 'Ricordami', 'pharmanow' ); ?>
	</label>

	<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $pn_redirect ); ?>">

	<button type="submit" class="pn-atc-btn w-full h-12 rounded-md bg-pharma-teal text-white text-base font-semibold hover:bg-pharma-teal-dark">
		<span class="pn-atc-default">
			<?php pn_icon( 'log-in', array( 'class' => 'h-5 w-5' ) ); ?>
			<?php esc_html_e( 'Accedi', 'pharmanow' ); ?>
		</span>
		<span class="pn-atc-success">
			<?php pn_icon( 'check-circle-2', array( 'class' => 'h-5 w-5' ) ); ?>
			<?php esc_html_e( 'Accesso in corso...', 'pharmanow' ); ?>
		</span>
	</button>
</form>
