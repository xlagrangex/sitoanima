<?php
/**
 * Hero "celebration" — variant post-checkout: gradient + check animato + nome utente.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn = is_array( $args ) ? $args : array();
$order        = $pn['order'];
$first_name   = $pn['first_name'] ?? '';
$email        = $pn['email'] ?? '';
$date_iso     = $pn['date_iso'] ?? '';
$date_fmt     = $pn['date_fmt'] ?? '';
$settings     = $pn['settings'] ?? array();
?>
<section class="relative overflow-hidden bg-gradient-to-br from-[#008692] via-[#0B8894] to-[#43CCB1] text-white">
	<div class="absolute inset-0 opacity-20" aria-hidden="true">
		<svg width="100%" height="100%"><defs><pattern id="pn-grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="0.5"/></pattern></defs><rect width="100%" height="100%" fill="url(#pn-grid)"/></svg>
	</div>

	<div class="relative max-w-[1280px] mx-auto px-4 py-12 sm:py-16 text-center">
		<div class="relative inline-flex items-center justify-center pn-burst">
			<div class="relative w-20 h-20 rounded-full bg-white/95 shadow-xl flex items-center justify-center pn-anim-pop">
				<span class="pn-anim-check inline-flex">
					<?php pn_icon( 'check', array( 'class' => 'w-10 h-10 text-[#0B8894]', 'stroke-width' => '3' ) ); ?>
				</span>
			</div>
		</div>

		<h1 class="mt-6 text-3xl sm:text-4xl lg:text-5xl font-bold pn-anim-rise pn-anim-rise-1">
			<?php
			if ( $first_name ) {
				printf(
					/* translators: %s = first name */
					esc_html__( 'Grazie, %s!', 'pharmanow' ),
					esc_html( $first_name )
				);
			} else {
				esc_html_e( 'Grazie!', 'pharmanow' );
			}
			?>
		</h1>
		<p class="mt-3 text-white/90 text-base sm:text-lg pn-anim-rise pn-anim-rise-2">
			<?php esc_html_e( 'Il tuo ordine è stato ricevuto: il mare sta arrivando da te!', 'pharmanow' ); ?>
		</p>

		<div class="mt-6 inline-flex flex-wrap items-center justify-center gap-2 sm:gap-4 pn-anim-rise pn-anim-rise-3">
			<span class="inline-flex items-center gap-2 bg-white/15 backdrop-blur-sm rounded-full px-4 py-2 text-sm font-medium">
				<?php pn_icon( 'tag', array( 'class' => 'w-4 h-4' ) ); ?>
				<?php
				printf(
					/* translators: %s = order number */
					esc_html__( 'Ordine #%s', 'pharmanow' ),
					esc_html( $order->get_order_number() )
				);
				?>
			</span>
			<?php if ( $date_fmt ) : ?>
				<span class="inline-flex items-center gap-2 bg-white/15 backdrop-blur-sm rounded-full px-4 py-2 text-sm">
					<?php pn_icon( 'clock', array( 'class' => 'w-4 h-4' ) ); ?>
					<time datetime="<?php echo esc_attr( $date_iso ); ?>"><?php echo esc_html( $date_fmt ); ?></time>
				</span>
			<?php endif; ?>
			<span class="inline-flex items-center gap-2 bg-white/15 backdrop-blur-sm rounded-full px-4 py-2 text-sm">
				<?php pn_icon( 'shield-check', array( 'class' => 'w-4 h-4' ) ); ?>
				<?php esc_html_e( 'Arte + scienza · da Napoli', 'pharmanow' ); ?>
			</span>
		</div>

		<?php if ( $email ) : ?>
			<p class="mt-6 text-white/85 text-sm pn-anim-rise pn-anim-rise-4">
				<?php
				printf(
					/* translators: %s = email */
					esc_html__( 'Una conferma è stata inviata a %s.', 'pharmanow' ),
					'<strong class="font-semibold">' . esc_html( $email ) . '</strong>'
				);
				?>
			</p>
		<?php endif; ?>
	</div>
</section>
