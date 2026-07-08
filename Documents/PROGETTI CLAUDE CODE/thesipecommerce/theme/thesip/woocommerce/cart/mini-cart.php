<?php
/**
 * Mini cart contents — markup interno del drawer (sostituito live da wc-cart-fragments).
 * Layout replica fedelmente components/shop/layout/cart-sidebar.tsx del Next.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn_threshold = (float) get_theme_mod( 'pn_free_shipping_threshold', 29.90 );
$pn_subtotal  = (float) WC()->cart->get_subtotal();
$pn_remaining = max( 0.0, $pn_threshold - $pn_subtotal );
$pn_pct       = $pn_threshold > 0 ? min( 100.0, ( $pn_subtotal / $pn_threshold ) * 100.0 ) : 0.0;
$pn_is_free   = $pn_subtotal >= $pn_threshold && $pn_subtotal > 0;

do_action( 'woocommerce_before_mini_cart' );

// Espone count + subtotal in data attributi: il JS legge da qui ad ogni
// fragment refresh e aggiorna Alpine.store('pnCart') in modo affidabile.
$pn_count_data    = function_exists( 'WC' ) && WC()->cart ? (int) WC()->cart->get_cart_contents_count() : 0;
$pn_subtotal_data = function_exists( 'WC' ) && WC()->cart ? (float) WC()->cart->get_displayed_subtotal() : 0.0;
?>

<div class="pn-mc <?php echo WC()->cart->is_empty() ? 'pn-mc--empty-state' : 'pn-mc--filled'; ?>" data-pn-count="<?php echo esc_attr( (string) $pn_count_data ); ?>" data-pn-subtotal="<?php echo esc_attr( (string) $pn_subtotal_data ); ?>">

	<?php if ( ! WC()->cart->is_empty() ) : ?>

		<!-- Free shipping bar -->
		<div class="pn-mc-fsb">
			<div class="pn-mc-fsb__row">
				<?php pn_icon( 'truck', array( 'class' => 'pn-mc-fsb__icon' ) ); ?>
				<p class="pn-mc-fsb__text">
					<?php if ( $pn_is_free ) : ?>
						<?php esc_html_e( 'Hai la spedizione', 'pharmanow' ); ?> <strong class="pn-mc-fsb__free"><?php esc_html_e( 'GRATUITA!', 'pharmanow' ); ?></strong>
					<?php else : ?>
						<?php esc_html_e( 'Mancano', 'pharmanow' ); ?> <strong>€<?php echo esc_html( number_format( $pn_remaining, 2, ',', '.' ) ); ?></strong> <?php esc_html_e( 'per la spedizione GRATUITA', 'pharmanow' ); ?>
					<?php endif; ?>
				</p>
			</div>
			<div class="pn-mc-fsb__track">
				<div class="pn-mc-fsb__bar" style="width: <?php echo esc_attr( (string) $pn_pct ); ?>%"></div>
			</div>
		</div>

		<!-- Items list -->
		<ul class="pn-mc-list">
			<?php
			do_action( 'woocommerce_before_mini_cart_contents' );

			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

				if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
					$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
					$thumbnail_html    = $_product->get_image( 'pn-product-thumb' );
					$product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
					$product_subtotal  = apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
					$has_discount      = $_product->get_regular_price() && $_product->get_sale_price() && (float) $_product->get_regular_price() > (float) $_product->get_sale_price();
					?>
					<li class="pn-mc-item">

						<!-- Thumb -->
						<?php if ( $product_permalink ) : ?>
							<a href="<?php echo esc_url( $product_permalink ); ?>" class="pn-mc-item__thumb">
								<?php echo $thumbnail_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
						<?php else : ?>
							<span class="pn-mc-item__thumb"><?php echo $thumbnail_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<?php endif; ?>

						<!-- Body: name + unit price + qty controls -->
						<div class="pn-mc-item__body">
							<?php if ( $product_permalink ) : ?>
								<a href="<?php echo esc_url( $product_permalink ); ?>" class="pn-mc-item__name">
									<?php echo wp_kses_post( $product_name ); ?>
								</a>
							<?php else : ?>
								<span class="pn-mc-item__name"><?php echo wp_kses_post( $product_name ); ?></span>
							<?php endif; ?>

							<?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

							<div class="pn-mc-item__price-row">
								<span class="pn-mc-item__price"><?php echo $product_price; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<?php if ( $has_discount ) : ?>
									<span class="pn-mc-item__price-regular"><?php echo wc_price( $_product->get_regular_price() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<?php endif; ?>
							</div>

							<?php if ( ! $_product->is_sold_individually() ) : ?>
								<div class="pn-qty-group" data-pn-qty-group data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>">
									<button type="button" class="pn-qty-btn" data-pn-qty-action="dec" aria-label="<?php esc_attr_e( 'Riduci quantità', 'pharmanow' ); ?>">
										<?php pn_icon( 'minus', array( 'class' => 'pn-qty-btn__icon' ) ); ?>
									</button>
									<span class="pn-qty-value" data-pn-qty-value><?php echo esc_html( (string) $cart_item['quantity'] ); ?></span>
									<button type="button" class="pn-qty-btn" data-pn-qty-action="inc" aria-label="<?php esc_attr_e( 'Aumenta quantità', 'pharmanow' ); ?>">
										<?php pn_icon( 'plus', array( 'class' => 'pn-qty-btn__icon' ) ); ?>
									</button>
								</div>
							<?php else : ?>
								<span class="pn-mc-item__qty-fixed">x1</span>
							<?php endif; ?>
						</div>

						<!-- Right column: line subtotal + trash -->
						<div class="pn-mc-item__side">
							<span class="pn-mc-item__line-total"><?php echo $product_subtotal; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<?php
							echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								'woocommerce_cart_item_remove_link',
								sprintf(
									'<a href="%s" class="pn-mc-item__trash" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s">%s</a>',
									esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
									esc_attr( sprintf( __( 'Rimuovi %s', 'pharmanow' ), wp_strip_all_tags( $product_name ) ) ),
									esc_attr( $product_id ),
									esc_attr( $cart_item_key ),
									esc_attr( $_product->get_sku() ),
									pn_icon_string( 'trash-2', array( 'class' => 'pn-mc-item__trash-icon' ) )
								),
								$cart_item_key
							);
							?>
						</div>
					</li>
					<?php
				}
			}

			do_action( 'woocommerce_mini_cart_contents' );
			?>
		</ul>

		<!-- Footer: totals + CTAs + payment safety -->
		<div class="pn-mc-footer">

			<div class="pn-mc-totals">
				<div class="pn-mc-totals__row">
					<span class="pn-mc-totals__label"><?php esc_html_e( 'Subtotale', 'pharmanow' ); ?></span>
					<span class="pn-mc-totals__value"><?php echo wc_price( WC()->cart->get_subtotal() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				</div>

				<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
					<div class="pn-mc-totals__row pn-mc-totals__row--discount">
						<span class="pn-mc-totals__label"><?php echo esc_html( wc_cart_totals_coupon_label( $coupon, false ) ); ?></span>
						<span class="pn-mc-totals__value"><?php wc_cart_totals_coupon_html( $coupon ); ?></span>
					</div>
				<?php endforeach; ?>

				<?php
				// Calcola lo shipping reale che WC addebiterà.
				// Nota: fuori dal checkout non sempre WC ha tutti i dati per scegliere
				// una rate (cliente non loggato, niente indirizzo) → in quel caso
				// usiamo una stima trasparente etichettata come "stimato".
				WC()->cart->calculate_shipping();
				WC()->cart->calculate_totals();
				$pn_real_ship       = (float) WC()->cart->get_shipping_total();
				$pn_has_ship_rates  = false;
				foreach ( WC()->shipping()->get_packages() as $_pkg ) {
					if ( ! empty( $_pkg['rates'] ) ) {
						$pn_has_ship_rates = true;
						break;
					}
				}
				$pn_standard_shipping = (float) get_theme_mod( 'pn_standard_shipping_cost', 4.90 );

				if ( $pn_has_ship_rates ) {
					$pn_show_ship   = $pn_real_ship;
					$pn_real_total  = (float) WC()->cart->get_total( 'raw' );
					$pn_ship_is_free = ( 0.0 === $pn_real_ship );
					$pn_total_label = __( 'Totale', 'pharmanow' );
				} else {
					$pn_show_ship   = $pn_is_free ? 0.0 : $pn_standard_shipping;
					$pn_real_total  = (float) WC()->cart->get_total( 'raw' ) + $pn_show_ship;
					$pn_ship_is_free = $pn_is_free;
					$pn_total_label = __( 'Totale stimato', 'pharmanow' );
				}
				?>
				<div class="pn-mc-totals__row">
					<span class="pn-mc-totals__label"><?php esc_html_e( 'Spedizione', 'pharmanow' ); ?></span>
					<span class="pn-mc-totals__value">
						<?php if ( $pn_ship_is_free ) : ?>
							<span class="pn-mc-totals__free"><?php esc_html_e( 'Gratuita', 'pharmanow' ); ?></span>
						<?php else : ?>
							<?php echo wc_price( $pn_show_ship ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php endif; ?>
					</span>
				</div>

				<div class="pn-mc-totals__sep"></div>

				<div class="pn-mc-totals__row pn-mc-totals__row--total">
					<span class="pn-mc-totals__label-total"><?php echo esc_html( $pn_total_label ); ?></span>
					<span class="pn-mc-totals__value-total"><?php echo wc_price( $pn_real_total ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				</div>
			</div>

			<?php do_action( 'woocommerce_widget_shopping_cart_before_buttons' ); ?>

			<?php
			// Express Checkout: Apple Pay / Google Pay / Link / PayPal Smart Buttons.
			// Stripe e PayPal si agganciano all'hook standard `woocommerce_widget_shopping_cart_buttons`.
			// WC core registra a quello stesso hook anche i 2 bottoni "Visualizza carrello"
			// e "Procedi al checkout" — li disregistreremo per non duplicare il nostro CTA gradient.
			remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_button_view_cart', 10 );
			remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_proceed_to_checkout', 20 );

			ob_start();
			do_action( 'woocommerce_widget_shopping_cart_buttons' );
			do_action( 'pn_mini_cart_express_checkout' ); // backward compat con eventuali integrazioni custom.
			$pn_express_html = trim( ob_get_clean() );

			if ( '' !== $pn_express_html ) {
				echo '<div class="pn-mc-express">' . $pn_express_html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<div class="pn-mc-divider"><span>' . esc_html__( 'oppure', 'pharmanow' ) . '</span></div>';
			}
			?>

			<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="pn-btn-gradient pn-mc-cta">
				<span class="pn-btn-slide">
					<span class="pn-btn-slide__text">
						<?php pn_icon( 'lock', array( 'class' => 'pn-btn-slide__icon' ) ); ?>
						<?php esc_html_e( 'Procedi al Checkout', 'pharmanow' ); ?>
					</span>
					<span class="pn-btn-slide__clone" aria-hidden="true">
						<?php pn_icon( 'lock', array( 'class' => 'pn-btn-slide__icon' ) ); ?>
						<?php esc_html_e( 'Procedi al Checkout', 'pharmanow' ); ?>
					</span>
				</span>
			</a>
			<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="pn-btn-outline pn-mc-cta">
				<?php esc_html_e( 'Vedi carrello completo', 'pharmanow' ); ?>
			</a>

			<!-- Payment safety (stesse icone del footer) -->
			<div class="pn-mc-payments">
				<span class="pn-mc-payments__label">
					<?php pn_icon( 'lock', array( 'class' => 'pn-mc-payments__lock' ) ); ?>
					<?php esc_html_e( 'Pagamenti sicuri', 'pharmanow' ); ?>
				</span>
				<div class="pn-mc-payments__cards">
					<?php
					$pn_mc_payments = array(
						'visa.svg'             => 'Visa',
						'mastercard.svg'       => 'Mastercard',
						'maestro.svg'          => 'Maestro',
						'american-express.svg' => 'American Express',
						'paypal.svg'           => 'PayPal',
						'apple-pay.svg'        => 'Apple Pay',
						'google-pay.svg'       => 'Google Pay',
					);
					foreach ( $pn_mc_payments as $pn_mc_file => $pn_mc_alt ) :
						?>
						<img
							src="<?php echo esc_url( pn_asset( "images/payment/{$pn_mc_file}" ) ); ?>"
							alt="<?php echo esc_attr( $pn_mc_alt ); ?>"
							class="pn-mc-payments__card"
						>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

	<?php else : ?>

		<!-- Empty state -->
		<div class="pn-mc-empty">
			<div class="pn-mc-empty__icon">
				<?php pn_icon( 'shopping-bag', array( 'class' => '' ) ); ?>
			</div>
			<p class="pn-mc-empty__text"><?php esc_html_e( 'Il tuo carrello è vuoto', 'pharmanow' ); ?></p>
			<a href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>" class="pn-btn-outline pn-mc-cta">
				<?php esc_html_e( 'Esplora il catalogo', 'pharmanow' ); ?>
			</a>
		</div>

	<?php endif; ?>

</div>

<?php do_action( 'woocommerce_after_mini_cart' ); ?>
