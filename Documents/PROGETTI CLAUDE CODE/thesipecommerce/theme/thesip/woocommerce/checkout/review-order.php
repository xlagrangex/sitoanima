<?php
/**
 * Review order — sidebar checkout.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="pn-review-order text-sm">

	<!-- Items -->
	<ul class="space-y-3 m-0 p-0 list-none mb-4">
		<?php
		do_action( 'woocommerce_review_order_before_cart_contents' );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				$thumbnail = $_product->get_image( 'pn-product-thumb', array( 'class' => 'h-12 w-12 object-contain rounded-md border border-gray-200 bg-white p-1' ) );
				?>
				<li class="flex items-start gap-3 cart_item">
					<span class="shrink-0 relative">
						<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span class="absolute -top-1.5 -right-1.5 h-5 min-w-5 px-1 rounded-full bg-pharma-teal text-white text-[10px] font-bold flex items-center justify-center">
							<?php echo esc_html( (string) $cart_item['quantity'] ); ?>
						</span>
					</span>
					<div class="flex-1 min-w-0">
						<p class="font-medium text-gray-900 leading-snug line-clamp-2">
							<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ); ?>
						</p>
						<?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<span class="font-semibold text-gray-900 tabular-nums shrink-0">
						<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
				</li>
				<?php
			}
		}

		do_action( 'woocommerce_review_order_after_cart_contents' );
		?>
	</ul>

	<!-- Totals -->
	<div class="space-y-2 py-3 border-t border-gray-100">
		<div class="flex items-center justify-between cart-subtotal">
			<span class="text-gray-600"><?php esc_html_e( 'Subtotale', 'pharmanow' ); ?></span>
			<span class="font-semibold text-gray-900 tabular-nums"><?php wc_cart_totals_subtotal_html(); ?></span>
		</div>

		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<div class="flex items-center justify-between cart-discount coupon-<?php echo esc_attr( sanitize_html_class( $code ) ); ?>">
				<span class="text-gray-600"><?php wc_cart_totals_coupon_label( $coupon ); ?></span>
				<span class="font-semibold text-emerald-600 tabular-nums"><?php wc_cart_totals_coupon_html( $coupon ); ?></span>
			</div>
		<?php endforeach; ?>

		<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
			<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>
			<?php wc_cart_totals_shipping_html(); ?>
			<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>
		<?php endif; ?>

		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<div class="flex items-center justify-between fee">
				<span class="text-gray-600"><?php echo esc_html( $fee->name ); ?></span>
				<span class="font-semibold text-gray-900 tabular-nums"><?php wc_cart_totals_fee_html( $fee ); ?></span>
			</div>
		<?php endforeach; ?>

		<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
			<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
				<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
					<div class="flex items-center justify-between tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
						<span class="text-gray-600"><?php echo esc_html( $tax->label ); ?></span>
						<span class="font-semibold text-gray-900 tabular-nums"><?php echo wp_kses_post( $tax->formatted_amount ); ?></span>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="flex items-center justify-between tax-total">
					<span class="text-gray-600"><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></span>
					<span class="font-semibold text-gray-900 tabular-nums"><?php wc_cart_totals_taxes_total_html(); ?></span>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

		<div class="flex items-center justify-between pt-3 mt-2 border-t-2 border-gray-900 order-total">
			<span class="text-base font-semibold text-gray-900"><?php esc_html_e( 'Totale', 'pharmanow' ); ?></span>
			<strong class="text-2xl font-extrabold text-pharma-teal tracking-tight tabular-nums"><?php wc_cart_totals_order_total_html(); ?></strong>
		</div>

		<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>
	</div>

	<?php
	// Niente woocommerce_checkout_payment() qui: questo template viene
	// renderizzato anche dall'AJAX update_order_review come fragment
	// (mai montato nel DOM del tema) e duplicherebbe il render server-side
	// dei payment_fields di ogni gateway a ogni refresh del checkout.
	// Il box pagamenti vive in form-checkout.php / fragment #payment di WC.
	?>

</div>
