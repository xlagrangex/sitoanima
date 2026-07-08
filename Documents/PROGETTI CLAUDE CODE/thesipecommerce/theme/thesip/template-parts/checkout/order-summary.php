<?php
/**
 * Checkout order summary sidebar — replica del Next OrderSummary.
 * Lista prodotti con qty +/-, coupon collapsible, totali, totale grande.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
	return;
}

$pn_cart        = WC()->cart;

// IMPORTANTE: tutti i numeri esposti devono coincidere con quello che WC
// addebita davvero. Mai derivare il "totale" o lo "shipping" dal solo stato
// del theme (`pn_get_shipping_state`): quello è informativo (basato sul
// subtotale prodotti vs soglia), non rappresenta il prezzo finale quando ci
// sono coupon, fee, tasse o configurazioni Woo che spostano la rate.
$pn_cart->calculate_shipping();
$pn_cart->calculate_totals();

$pn_subtotal    = (float) $pn_cart->get_subtotal();
$pn_ship_cost   = (float) $pn_cart->get_shipping_total();
$pn_total       = (float) $pn_cart->get_total( 'raw' );
$pn_is_free     = ( 0.0 === $pn_ship_cost );
$pn_std_ship    = (float) get_theme_mod( 'pn_standard_shipping_cost', 4.90 );
$pn_total_items = (int) $pn_cart->get_cart_contents_count();
?>

<div class="pn-checkout-summary-fragment">
<div class="pn-co-summary" x-data="{ open: false }">

	<!-- Mobile toggle -->
	<button type="button" class="pn-co-summary__mobile" @click="open = !open" :aria-expanded="open">
		<span>
			<?php
			printf(
				esc_html( _n( '%d articolo', '%d articoli', $pn_total_items, 'pharmanow' ) ),
				$pn_total_items
			);
			?>
			·
			<strong><?php echo wc_price( $pn_total ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
		</span>
		<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="{ 'rotate-180': open }" style="transition: transform 0.2s"><polyline points="6 9 12 15 18 9"/></svg>
	</button>

	<div class="pn-co-summary__body" :class="{ 'is-open': open }">

		<!-- Items list -->
		<ul class="pn-co-summary__items">
			<?php foreach ( $pn_cart->get_cart() as $cart_item_key => $cart_item ) :
				$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 ) {
					continue;
				}
				$thumbnail = $_product->get_image( 'pn-product-thumb' );
				$name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
				$line      = apply_filters( 'woocommerce_cart_item_subtotal', $pn_cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
				?>
				<li class="pn-co-summary__item">
					<div class="pn-co-summary__thumb">
						<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="pn-co-summary__body">
						<p class="pn-co-summary__name"><?php echo wp_kses_post( $name ); ?></p>
						<?php if ( ! $_product->is_sold_individually() ) : ?>
							<div class="pn-qty-group" data-pn-qty-group data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>">
								<button type="button" class="pn-qty-btn" data-pn-qty-action="dec" aria-label="<?php esc_attr_e( 'Riduci', 'pharmanow' ); ?>">
									<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg>
								</button>
								<span class="pn-qty-value" data-pn-qty-value><?php echo esc_html( (string) $cart_item['quantity'] ); ?></span>
								<button type="button" class="pn-qty-btn" data-pn-qty-action="inc" aria-label="<?php esc_attr_e( 'Aumenta', 'pharmanow' ); ?>">
									<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
								</button>
							</div>
						<?php else : ?>
							<span class="pn-co-summary__qty-fixed">×1</span>
						<?php endif; ?>
					</div>
					<span class="pn-co-summary__line"><?php echo $line; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				</li>
			<?php endforeach; ?>
		</ul>

		<div class="pn-co-summary__sep"></div>

		<!-- Coupon sempre aperto -->
		<div class="pn-co-summary__coupon">
			<p class="pn-co-summary__coupon-title">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
				<?php esc_html_e( 'Hai un codice sconto?', 'pharmanow' ); ?>
			</p>
			<?php /* NB: niente <form> qui — questo template è incluso DENTRO il form #checkout di Woo
			       e form annidati sono illegali in HTML5 (il browser fa de-nesting e i submit
			       finiscono al form esterno → Stripe Elements valida la carta → "card incomplete").
			       Wrapping con <div data-pn-coupon> e fetch AJAX a wc-ajax=apply_coupon. */ ?>
			<div class="pn-co-summary__coupon-form" data-pn-coupon>
				<input type="text" name="coupon_code" placeholder="<?php esc_attr_e( 'Inserisci codice', 'pharmanow' ); ?>" class="pn-co-summary__coupon-input" data-pn-coupon-input autocomplete="off" autocapitalize="characters">
				<button type="button" class="pn-co-summary__coupon-btn" data-pn-coupon-apply>
					<span class="pn-co-summary__coupon-btn-label" data-pn-coupon-btn-label><?php esc_html_e( 'Applica', 'pharmanow' ); ?></span>
					<span class="pn-co-summary__coupon-btn-spinner" data-pn-coupon-btn-spinner aria-hidden="true"></span>
				</button>
			</div>
			<div class="pn-co-summary__coupon-feedback" data-pn-coupon-feedback role="status" aria-live="polite"></div>
		</div>

		<div class="pn-co-summary__sep"></div>

		<!-- Totals -->
		<div class="pn-co-summary__totals">
			<div class="pn-co-summary__row">
				<span><?php esc_html_e( 'Subtotale', 'pharmanow' ); ?></span>
				<span class="pn-co-summary__value"><?php echo wc_price( $pn_subtotal ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			</div>

			<?php foreach ( $pn_cart->get_coupons() as $code => $coupon ) : ?>
				<div class="pn-co-summary__row pn-co-summary__row--discount">
					<span class="pn-co-summary__coupon-label">
						<?php wc_cart_totals_coupon_label( $coupon ); ?>
						<button
							type="button"
							class="pn-co-summary__coupon-remove"
							data-pn-coupon-remove="<?php echo esc_attr( $code ); ?>"
							aria-label="<?php echo esc_attr( sprintf( __( 'Rimuovi codice %s', 'pharmanow' ), strtoupper( $code ) ) ); ?>"
							title="<?php esc_attr_e( 'Rimuovi codice sconto', 'pharmanow' ); ?>"
						>×</button>
					</span>
					<span class="pn-co-summary__value">-<?php echo wc_price( $pn_cart->get_coupon_discount_amount( $code ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				</div>
			<?php endforeach; ?>

			<div class="pn-co-summary__row" data-pn-shipping-row>
				<span><?php esc_html_e( 'Spedizione', 'pharmanow' ); ?></span>
				<span class="pn-co-summary__value" data-pn-shipping-value>
					<?php if ( $pn_is_free ) : ?>
						<span class="pn-co-summary__ship-strike"><?php echo wc_price( $pn_std_ship ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="pn-co-summary__ship-free"><?php esc_html_e( 'Gratuita', 'pharmanow' ); ?></span>
					<?php else : ?>
						<?php echo wc_price( $pn_ship_cost ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php endif; ?>
				</span>
			</div>
		</div>

		<div class="pn-co-summary__sep"></div>

		<div class="pn-co-summary__total">
			<span class="pn-co-summary__total-label"><?php esc_html_e( 'Totale', 'pharmanow' ); ?></span>
			<span class="pn-co-summary__total-value"><?php echo wc_price( $pn_total ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		</div>

	</div>
</div>
</div><!-- /.pn-checkout-summary-fragment -->
