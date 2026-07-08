<?php
/**
 * Account menu dropdown — porting da components/shop/layout/AccountMenu.tsx (Next).
 *
 * Variante logged / not-logged in base a is_user_logged_in().
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn_logged = is_user_logged_in();
$pn_user   = $pn_logged ? wp_get_current_user() : null;
$pn_name   = $pn_user ? ( $pn_user->display_name ?: $pn_user->user_email ) : '';
?>

<div x-data="{ open: false }" x-on:click.outside="open = false" x-on:keydown.escape.window="open = false" class="relative">
	<button
		type="button"
		x-on:click="open = !open"
		x-bind:aria-expanded="open"
		aria-label="<?php esc_attr_e( 'Menu account', 'pharmanow' ); ?>"
		class="hidden h-9 w-9 items-center justify-center rounded-md text-gray-600 transition-colors hover:bg-gray-100 hover:text-pharma-teal sm:flex"
	>
		<?php pn_icon( 'user', array( 'class' => 'h-5 w-5' ) ); ?>
	</button>

	<div
		x-show="open"
		x-cloak
		x-transition.opacity
		class="absolute right-0 top-full z-50 mt-2 w-64 overflow-hidden rounded-lg border bg-background shadow-xl"
	>
		<?php if ( $pn_logged ) : ?>
			<div class="border-b bg-muted/40 px-4 py-3">
				<p class="text-xs uppercase tracking-wider text-muted-foreground"><?php esc_html_e( 'Ciao', 'pharmanow' ); ?></p>
				<p class="truncate text-sm font-semibold"><?php echo esc_html( $pn_name ); ?></p>
			</div>
			<nav class="py-1">
				<?php
				$pn_logged_links = array(
					array( 'label' => __( 'Panoramica', 'pharmanow' ), 'href' => home_url( '/account' ),           'icon' => 'layout-dashboard' ),
					array( 'label' => __( 'Profilo', 'pharmanow' ),    'href' => home_url( '/account/profilo' ),    'icon' => 'user' ),
					array( 'label' => __( 'I miei ordini', 'pharmanow' ), 'href' => home_url( '/account/ordini' ),  'icon' => 'package' ),
					array( 'label' => __( 'Indirizzi', 'pharmanow' ),  'href' => home_url( '/account/indirizzi' ),  'icon' => 'map-pin' ),
					array( 'label' => __( 'Wishlist', 'pharmanow' ),   'href' => home_url( '/account/wishlist' ),   'icon' => 'heart' ),
					array( 'label' => __( 'Traccia ordine', 'pharmanow' ), 'href' => home_url( '/traccia-ordine' ), 'icon' => 'search' ),
				);
				foreach ( $pn_logged_links as $pn_l ) :
					?>
					<a href="<?php echo esc_url( $pn_l['href'] ); ?>" class="flex items-center gap-3 px-4 py-2 text-sm text-foreground hover:bg-muted transition-colors">
						<?php pn_icon( $pn_l['icon'], array( 'class' => 'h-4 w-4 text-muted-foreground' ) ); ?>
						<?php echo esc_html( $pn_l['label'] ); ?>
					</a>
				<?php endforeach; ?>
			</nav>
			<div class="border-t py-1">
				<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="flex w-full items-center gap-3 px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 transition-colors">
					<?php pn_icon( 'log-out', array( 'class' => 'h-4 w-4' ) ); ?>
					<?php esc_html_e( 'Esci', 'pharmanow' ); ?>
				</a>
			</div>
		<?php else : ?>
			<div class="border-b bg-muted/40 px-4 py-3">
				<p class="text-sm font-semibold"><?php esc_html_e( 'Il tuo account', 'pharmanow' ); ?></p>
				<p class="text-xs text-muted-foreground"><?php esc_html_e( 'Accedi o crea un account', 'pharmanow' ); ?></p>
			</div>
			<nav class="py-1">
				<a href="<?php echo esc_url( wp_login_url( home_url( '/' ) ) ); ?>" class="flex items-center gap-3 px-4 py-2 text-sm hover:bg-muted">
					<?php pn_icon( 'log-in', array( 'class' => 'h-4 w-4 text-muted-foreground' ) ); ?>
					<?php esc_html_e( 'Accedi', 'pharmanow' ); ?>
				</a>
				<a href="<?php echo esc_url( wp_registration_url() ); ?>" class="flex items-center gap-3 px-4 py-2 text-sm hover:bg-muted">
					<?php pn_icon( 'user-plus', array( 'class' => 'h-4 w-4 text-muted-foreground' ) ); ?>
					<?php esc_html_e( 'Registrati', 'pharmanow' ); ?>
				</a>
				<a href="<?php echo esc_url( home_url( '/traccia-ordine' ) ); ?>" class="flex items-center gap-3 px-4 py-2 text-sm hover:bg-muted">
					<?php pn_icon( 'search', array( 'class' => 'h-4 w-4 text-muted-foreground' ) ); ?>
					<?php esc_html_e( 'Traccia ordine', 'pharmanow' ); ?>
				</a>
			</nav>
		<?php endif; ?>
	</div>
</div>
