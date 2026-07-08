<?php
/**
 * Cart totals sidebar.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<section class="cart_totals <?php echo WC()->customer->has_calculated_shipping() ? 'calculated_shipping' : ''; ?> rounded-xl border border-gray-200 bg-white shadow-sm p-5">

	<?php do_action( 'woocommerce_before_cart_totals' ); ?>

	<h2 class="text-base font-bold uppercase tracking-wider text-gray-900 mb-4">
		<?php esc_html_e( 'Riepilogo ordine', 'pharmanow' ); ?>
	</h2>

	<div class="space-y-3 text-sm">
		<div class="flex items-center justify-between py-2 border-b border-gray-100">
			<span class="text-gray-600"><?php esc_html_e( 'Subtotale', 'pharmanow' ); ?></span>
			<span class="font-semibold text-gray-900 tabular-nums">
				<?php wc_cart_totals_subtotal_html(); ?>
			</span>
		</div>

		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<div class="flex items-center justify-between py-2 border-b border-gray-100 cart-discount coupon-<?php echo esc_attr( sanitize_html_class( $code ) ); ?>">
				<span class="text-gray-600"><?php wc_cart_totals_coupon_label( $coupon ); ?></span>
				<span class="font-semibold text-emerald-600 tabular-nums">
					<?php wc_cart_totals_coupon_html( $coupon ); ?>
				</span>
			</div>
		<?php endforeach; ?>

		<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
			<?php do_action( 'woocommerce_cart_totals_before_shipping' ); ?>
			<div class="py-2 border-b border-gray-100 pn-cart-shipping">
				<?php wc_cart_totals_shipping_html(); ?>
			</div>
			<?php do_action( 'woocommerce_cart_totals_after_shipping' ); ?>
		<?php elseif ( WC()->cart->needs_shipping() && 'yes' === get_option( 'woocommerce_enable_shipping_calc' ) ) : ?>
			<div class="py-2 border-b border-gray-100">
				<span class="text-gray-600"><?php esc_html_e( 'Spedizione', 'pharmanow' ); ?></span>
				<span class="text-sm text-gray-500"><?php esc_html_e( 'Calcolata al checkout', 'pharmanow' ); ?></span>
			</div>
		<?php endif; ?>

		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<div class="flex items-center justify-between py-2 border-b border-gray-100 fee">
				<span class="text-gray-600"><?php echo esc_html( $fee->name ); ?></span>
				<span class="font-semibold text-gray-900 tabular-nums">
					<?php wc_cart_totals_fee_html( $fee ); ?>
				</span>
			</div>
		<?php endforeach; ?>

		<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
			<?php
			$taxable_address = WC()->customer->get_taxable_address();
			$estimated_text  = WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping()
				? sprintf( ' <small>' . esc_html__( '(stimata per %s)', 'pharmanow' ) . '</small>', WC()->countries->estimated_for_prefix( $taxable_address[0] ) . esc_html( WC()->countries->countries[ $taxable_address[0] ] ) )
				: '';
			if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) :
				foreach ( WC()->cart->get_tax_totals() as $code => $tax ) :
					?>
					<div class="flex items-center justify-between py-2 border-b border-gray-100 tax-rate">
						<span class="text-gray-600"><?php echo esc_html( $tax->label ) . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="font-semibold text-gray-900 tabular-nums">
							<?php echo wp_kses_post( $tax->formatted_amount ); ?>
						</span>
					</div>
					<?php
				endforeach;
			else :
				?>
				<div class="flex items-center justify-between py-2 border-b border-gray-100">
					<span class="text-gray-600"><?php echo esc_html( WC()->countries->tax_or_vat() ) . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="font-semibold text-gray-900 tabular-nums">
						<?php wc_cart_totals_taxes_total_html(); ?>
					</span>
				</div>
				<?php
			endif;
			?>
		<?php endif; ?>

		<?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>

		<div class="flex items-center justify-between pt-4 mt-2 border-t-2 border-gray-900 order-total">
			<span class="text-base font-semibold text-gray-900"><?php esc_html_e( 'Totale', 'pharmanow' ); ?></span>
			<strong class="text-2xl font-extrabold text-pharma-teal tracking-tight tabular-nums">
				<?php wc_cart_totals_order_total_html(); ?>
			</strong>
		</div>

		<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>
	</div>

	<div class="wc-proceed-to-checkout mt-5">
		<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
	</div>

	<?php do_action( 'woocommerce_after_cart_totals' ); ?>

</section>
