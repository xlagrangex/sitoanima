<?php
/**
 * Hero "payment-pending" — landing post-checkout per ordini non pagati
 * (failed, pending, cancelled): niente celebrazione, stato chiaro e CTA
 * per riprovare il pagamento.
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
$retry_url    = $pn['retry_url'] ?? '';
$settings     = $pn['settings'] ?? array();

if ( 'failed' === $status ) {
	$pn_title = __( 'Il pagamento non è andato a buon fine', 'pharmanow' );
	$pn_sub   = __( 'Nessun importo ti è stato addebitato. Puoi riprovare in un click: il tuo ordine è salvato.', 'pharmanow' );
} elseif ( 'cancelled' === $status ) {
	$pn_title = __( 'Ordine annullato', 'pharmanow' );
	$pn_sub   = __( 'Questo ordine è stato annullato. Se hai completato il pagamento, contattaci e lo sistemiamo subito.', 'pharmanow' );
} else {
	$pn_title = __( 'Ordine in attesa di pagamento', 'pharmanow' );
	$pn_sub   = __( 'Il pagamento non risulta ancora completato. Se hai appena pagato, attendi qualche istante e ricarica: la conferma del gateway può richiedere qualche minuto.', 'pharmanow' );
}
?>
<section class="bg-gradient-to-r from-amber-600 to-amber-500 text-white">
	<div class="max-w-[1280px] mx-auto px-4 py-10 sm:py-14">
		<div class="flex items-start gap-4">
			<span class="shrink-0 mt-1 inline-flex items-center justify-center w-12 h-12 rounded-full bg-white/15">
				<?php pn_icon( 'credit-card', array( 'class' => 'w-6 h-6' ) ); ?>
			</span>
			<div>
				<h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold"><?php echo esc_html( $pn_title ); ?></h1>
				<p class="text-white/90 mt-2 text-sm sm:text-base max-w-2xl"><?php echo esc_html( $pn_sub ); ?></p>

				<p class="text-white/80 mt-4 text-sm">
					<?php
					printf(
						/* translators: %s = order number */
						esc_html__( 'Ordine #%s', 'pharmanow' ),
						esc_html( $order->get_order_number() )
					);
					?>
					<?php if ( $date_fmt ) : ?>
						· <time datetime="<?php echo esc_attr( $date_iso ); ?>"><?php echo esc_html( $date_fmt ); ?></time>
					<?php endif; ?>
					<?php if ( $status_label ) : ?>
						· <span class="font-semibold"><?php echo esc_html( $status_label ); ?></span>
					<?php endif; ?>
				</p>

				<div class="mt-6 flex flex-wrap gap-3">
					<?php if ( $retry_url ) : ?>
						<a href="<?php echo esc_url( $retry_url ); ?>" class="inline-flex items-center gap-2 rounded-full bg-white text-amber-700 font-semibold px-6 py-3 hover:bg-amber-50 transition-colors">
							<?php pn_icon( 'credit-card', array( 'class' => 'w-5 h-5' ) ); ?>
							<?php esc_html_e( 'Riprova il pagamento', 'pharmanow' ); ?>
						</a>
					<?php endif; ?>
					<?php if ( ! empty( $settings['email'] ) ) : ?>
						<a href="mailto:<?php echo esc_attr( $settings['email'] ); ?>?subject=<?php echo esc_attr( rawurlencode( sprintf( /* translators: %s = order number */ __( 'Aiuto pagamento ordine #%s', 'pharmanow' ), $order->get_order_number() ) ) ); ?>" class="inline-flex items-center gap-2 rounded-full border border-white/40 text-white font-semibold px-6 py-3 hover:bg-white/10 transition-colors">
							<?php pn_icon( 'message-circle', array( 'class' => 'w-5 h-5' ) ); ?>
							<?php esc_html_e( 'Contattaci', 'pharmanow' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>
