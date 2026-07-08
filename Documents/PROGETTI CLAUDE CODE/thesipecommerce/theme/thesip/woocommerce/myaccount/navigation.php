<?php
/**
 * AccountSidebar override (replica `components/shop/account/AccountSidebar.tsx`).
 *
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

$pn_user      = wp_get_current_user();
$pn_full      = trim( $pn_user->first_name . ' ' . $pn_user->last_name );
if ( '' === $pn_full ) {
	$pn_full = $pn_user->display_name ?: $pn_user->user_email;
}
$pn_initials  = strtoupper( ( $pn_user->first_name ? $pn_user->first_name[0] : '' ) . ( $pn_user->last_name ? $pn_user->last_name[0] : '' ) );
if ( '' === $pn_initials ) {
	$pn_initials = strtoupper( substr( $pn_user->user_email, 0, 1 ) );
}

$pn_items = array(
	'dashboard'    => array( 'label' => __( 'Panoramica', 'pharmanow' ), 'icon' => 'layout-dashboard' ),
	'orders'       => array( 'label' => __( 'Ordini', 'pharmanow' ),     'icon' => 'package' ),
	'wishlist'     => array( 'label' => __( 'Wishlist', 'pharmanow' ),   'icon' => 'heart' ),
	'edit-address' => array( 'label' => __( 'Indirizzi', 'pharmanow' ),  'icon' => 'map-pin' ),
	'edit-account' => array( 'label' => __( 'Profilo', 'pharmanow' ),    'icon' => 'user' ),
);
?>
<aside class="space-y-4">
	<!-- User card -->
	<div class="rounded-xl border bg-card p-4">
		<div class="flex items-center gap-3">
			<div class="flex h-12 w-12 items-center justify-center rounded-full bg-pharma-teal/10 text-base font-bold text-pharma-teal">
				<?php echo esc_html( $pn_initials ); ?>
			</div>
			<div class="min-w-0 flex-1">
				<p class="truncate text-sm font-semibold"><?php echo esc_html( $pn_full ); ?></p>
				<p class="truncate text-xs text-muted-foreground"><?php echo esc_html( $pn_user->user_email ); ?></p>
			</div>
		</div>
	</div>

	<!-- Nav -->
	<nav class="rounded-xl border bg-card p-2">
		<?php
		$pn_current = WC()->query->get_current_endpoint();
		foreach ( $pn_items as $endpoint => $info ) :
			$pn_url    = wc_get_account_endpoint_url( $endpoint );
			$pn_active = ( $pn_current === $endpoint ) || ( 'dashboard' === $endpoint && '' === $pn_current );
			// orders + view-order condividono lo slug "ordini": evidenzia "Ordini" anche nel dettaglio.
			if ( 'orders' === $endpoint && 'view-order' === $pn_current ) {
				$pn_active = true;
			}
			?>
			<a href="<?php echo esc_url( $pn_url ); ?>" class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition-colors <?php echo $pn_active ? 'bg-pharma-teal/10 text-pharma-teal' : 'text-foreground/80 hover:bg-muted hover:text-foreground'; ?>">
				<?php pn_icon( $info['icon'], array( 'class' => 'h-4 w-4' ) ); ?>
				<span><?php echo esc_html( $info['label'] ); ?></span>
			</a>
		<?php endforeach; ?>
		<div class="my-1 border-t"></div>
		<a href="<?php echo esc_url( wc_logout_url( home_url( '/' ) ) ); ?>" class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium text-foreground/80 transition-colors hover:bg-rose-50 hover:text-rose-700">
			<?php pn_icon( 'log-out', array( 'class' => 'h-4 w-4' ) ); ?>
			<span><?php esc_html_e( 'Esci', 'pharmanow' ); ?></span>
		</a>
	</nav>
</aside>
