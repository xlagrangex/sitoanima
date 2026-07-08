<?php
/**
 * Hero "info" — variant da account: header sobrio con order number, data, status badge.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn = is_array( $args ) ? $args : array();
$order        = $pn['order'];
$date_iso     = $pn['date_iso'] ?? '';
$date_fmt     = $pn['date_fmt'] ?? '';
$status       = $pn['status'] ?? '';
$status_label = $pn['status_label'] ?? '';

// Color mapping per stato — coerente con timeline (gradient teal per stati attivi).
$pn_status_color = array(
	'pending'         => 'bg-amber-100 text-amber-800',
	'on-hold'         => 'bg-amber-100 text-amber-800',
	'processing'      => 'bg-blue-100 text-blue-800',
	'shipped'         => 'bg-indigo-100 text-indigo-800',
	'partial-shipped' => 'bg-indigo-100 text-indigo-800',
	'completed'       => 'bg-green-100 text-green-800',
	'cancelled'       => 'bg-gray-100 text-gray-700',
	'refunded'        => 'bg-rose-100 text-rose-800',
	'failed'          => 'bg-rose-100 text-rose-800',
);
$pn_badge_class = $pn_status_color[ $status ] ?? 'bg-gray-100 text-gray-700';
?>
<section class="bg-gradient-to-r from-[#008692] to-[#6AD6E3] text-white">
	<div class="max-w-[1280px] mx-auto px-4 py-10 sm:py-12">
		<nav class="flex items-center gap-2 text-sm mb-3" aria-label="<?php esc_attr_e( 'Breadcrumb', 'pharmanow' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="text-white/70 hover:text-white transition-colors"><?php esc_html_e( 'Home', 'pharmanow' ); ?></a>
			<?php pn_icon( 'chevron-right', array( 'class' => 'w-4 h-4 text-white/50' ) ); ?>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="text-white/70 hover:text-white transition-colors"><?php esc_html_e( 'Account', 'pharmanow' ); ?></a>
			<?php pn_icon( 'chevron-right', array( 'class' => 'w-4 h-4 text-white/50' ) ); ?>
			<a href="<?php echo esc_url( wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) ) ); ?>" class="text-white/70 hover:text-white transition-colors"><?php esc_html_e( 'Ordini', 'pharmanow' ); ?></a>
			<?php pn_icon( 'chevron-right', array( 'class' => 'w-4 h-4 text-white/50' ) ); ?>
			<span class="text-white">#<?php echo esc_html( $order->get_order_number() ); ?></span>
		</nav>

		<div class="flex flex-wrap items-end justify-between gap-4">
			<div>
				<h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold">
					<?php
					printf(
						/* translators: %s = order number */
						esc_html__( 'Ordine #%s', 'pharmanow' ),
						esc_html( $order->get_order_number() )
					);
					?>
				</h1>
				<?php if ( $date_fmt ) : ?>
					<p class="text-white/85 mt-2 text-sm sm:text-base">
						<?php esc_html_e( 'Effettuato il', 'pharmanow' ); ?>
						<time datetime="<?php echo esc_attr( $date_iso ); ?>" class="font-medium"><?php echo esc_html( $date_fmt ); ?></time>
					</p>
				<?php endif; ?>
			</div>
			<?php if ( $status_label ) : ?>
				<span class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold <?php echo esc_attr( $pn_badge_class ); ?>">
					<span class="w-2 h-2 rounded-full bg-current opacity-70"></span>
					<?php echo esc_html( $status_label ); ?>
				</span>
			<?php endif; ?>
		</div>
	</div>
</section>
