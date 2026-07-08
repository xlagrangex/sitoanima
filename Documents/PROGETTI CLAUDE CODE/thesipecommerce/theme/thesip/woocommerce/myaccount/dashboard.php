<?php
/**
 * Dashboard My-Account. Replica `app/(shop)/account/page.tsx` del Next.
 *
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

$pn_user      = wp_get_current_user();
$pn_first     = $pn_user->first_name ?: explode( '@', $pn_user->user_email )[0];

$pn_orders = wc_get_orders(
	array(
		'customer_id' => $pn_user->ID,
		'limit'       => 3,
		'orderby'     => 'date',
		'order'       => 'DESC',
		'status'      => array_keys( wc_get_order_statuses() ),
	)
);

$pn_billing_address = wc_get_account_formatted_address( 'billing' );
?>
<div class="space-y-6">

	<!-- Hero greeting -->
	<div class="rounded-xl border bg-gradient-to-br from-pharma-teal/5 to-transparent p-6">
		<p class="text-sm text-muted-foreground"><?php esc_html_e( 'Bentornato', 'pharmanow' ); ?></p>
		<h2 class="text-2xl font-bold"><?php echo esc_html( $pn_first ); ?> 👋</h2>
		<p class="mt-1 text-sm text-muted-foreground"><?php esc_html_e( 'Da qui gestisci ordini, profilo e indirizzi.', 'pharmanow' ); ?></p>
	</div>

	<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">

		<!-- Card ordini recenti -->
		<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" class="group flex flex-col gap-3 rounded-xl border bg-card p-5 transition-shadow hover:shadow-md">
			<div class="flex items-center justify-between">
				<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-700"><?php pn_icon( 'package', array( 'class' => 'h-5 w-5' ) ); ?></div>
				<?php pn_icon( 'arrow-right', array( 'class' => 'h-4 w-4 text-muted-foreground transition-transform group-hover:translate-x-1' ) ); ?>
			</div>
			<div>
				<h3 class="text-sm font-semibold"><?php esc_html_e( 'Ordini recenti', 'pharmanow' ); ?></h3>
				<?php if ( empty( $pn_orders ) ) : ?>
					<p class="mt-1 text-xs text-muted-foreground"><?php esc_html_e( 'Nessun ordine ancora', 'pharmanow' ); ?></p>
				<?php else : ?>
					<ul class="mt-2 space-y-1 text-xs">
						<?php foreach ( $pn_orders as $pn_o ) : ?>
							<li class="flex items-center justify-between gap-2">
								<span class="truncate text-muted-foreground">#<?php echo esc_html( $pn_o->get_order_number() ); ?></span>
								<span class="font-semibold tabular-nums"><?php echo wp_kses_post( $pn_o->get_formatted_order_total() ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</a>

		<!-- Card indirizzo -->
		<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address' ) ); ?>" class="group flex flex-col gap-3 rounded-xl border bg-card p-5 transition-shadow hover:shadow-md">
			<div class="flex items-center justify-between">
				<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700"><?php pn_icon( 'map-pin', array( 'class' => 'h-5 w-5' ) ); ?></div>
				<?php pn_icon( 'arrow-right', array( 'class' => 'h-4 w-4 text-muted-foreground transition-transform group-hover:translate-x-1' ) ); ?>
			</div>
			<div>
				<h3 class="text-sm font-semibold"><?php esc_html_e( 'Indirizzo predefinito', 'pharmanow' ); ?></h3>
				<?php if ( $pn_billing_address ) : ?>
					<p class="mt-1 text-xs text-muted-foreground line-clamp-3"><?php echo wp_kses_post( $pn_billing_address ); ?></p>
				<?php else : ?>
					<p class="mt-1 text-xs text-muted-foreground"><?php esc_html_e( 'Nessun indirizzo impostato', 'pharmanow' ); ?></p>
				<?php endif; ?>
			</div>
		</a>

		<!-- Card profilo -->
		<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-account' ) ); ?>" class="group flex flex-col gap-3 rounded-xl border bg-card p-5 transition-shadow hover:shadow-md">
			<div class="flex items-center justify-between">
				<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-100 text-violet-700"><?php pn_icon( 'user', array( 'class' => 'h-5 w-5' ) ); ?></div>
				<?php pn_icon( 'arrow-right', array( 'class' => 'h-4 w-4 text-muted-foreground transition-transform group-hover:translate-x-1' ) ); ?>
			</div>
			<div>
				<h3 class="text-sm font-semibold"><?php esc_html_e( 'Profilo', 'pharmanow' ); ?></h3>
				<p class="mt-1 text-xs text-muted-foreground"><?php esc_html_e( 'Dati personali e password', 'pharmanow' ); ?></p>
			</div>
		</a>
	</div>
</div>
