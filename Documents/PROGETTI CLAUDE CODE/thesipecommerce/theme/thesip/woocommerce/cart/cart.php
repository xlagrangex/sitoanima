<?php
/**
 * Cart page — HTML Tailwind pulito, no table legacy.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Rimuove le success notices "è stato aggiunto al carrello" quando si è già nella cart
// (sono ridondanti: l'utente vede il prodotto nella tabella sotto).
if ( function_exists( 'WC' ) && WC()->session ) {
	$pn_notices = WC()->session->get( 'wc_notices', array() );
	if ( isset( $pn_notices['success'] ) ) {
		unset( $pn_notices['success'] );
		WC()->session->set( 'wc_notices', $pn_notices );
	}
}

do_action( 'woocommerce_before_cart' );

if ( WC()->cart && method_exists( WC()->cart, 'calculate_totals' ) ) {
	WC()->cart->calculate_totals();
}

$pn_threshold = (float) get_theme_mod( 'pn_free_shipping_threshold', 29.90 );
$pn_subtotal  = function_exists( 'WC' ) && WC()->cart ? (float) WC()->cart->get_displayed_subtotal() : 0.0;
$pn_remaining = max( 0.0, $pn_threshold - $pn_subtotal );
$pn_pct       = $pn_threshold > 0 ? min( 100.0, ( $pn_subtotal / $pn_threshold ) * 100.0 ) : 0.0;
$pn_is_free   = $pn_subtotal >= $pn_threshold && $pn_subtotal > 0;
?>

<div class="pn-cart max-w-6xl mx-auto px-4 py-8 lg:py-12">

	<header class="mb-6">
		<h1 class="text-3xl lg:text-4xl font-extrabold tracking-tight text-gray-900">
			<?php esc_html_e( 'Il tuo carrello', 'pharmanow' ); ?>
		</h1>
		<p class="text-sm text-gray-500 mt-1">
			<?php
			$pn_count = WC()->cart->get_cart_contents_count();
			printf(
				esc_html( _n( '%d articolo nel carrello', '%d articoli nel carrello', $pn_count, 'pharmanow' ) ),
				(int) $pn_count
			);
			?>
		</p>
	</header>

	<!-- Free shipping bar -->
	<div class="pn-fsb-page rounded-xl border border-gray-200 bg-white p-4 mb-6 shadow-sm" data-pn-fsb-page data-pn-fsb-threshold="<?php echo esc_attr( (string) $pn_threshold ); ?>">
		<div class="flex items-center gap-2 text-sm mb-2">
			<?php pn_icon( 'truck', array( 'class' => 'h-4 w-4 shrink-0 text-pharma-teal' ) ); ?>
			<p class="pn-fsb-page__text leading-tight">
				<?php if ( $pn_is_free ) : ?>
					<?php esc_html_e( 'Hai la spedizione', 'pharmanow' ); ?> <strong class="text-emerald-600"><?php esc_html_e( 'GRATUITA!', 'pharmanow' ); ?></strong>
				<?php else : ?>
					<?php esc_html_e( 'Mancano', 'pharmanow' ); ?> <strong>€<?php echo esc_html( number_format( $pn_remaining, 2, ',', '.' ) ); ?></strong> <?php esc_html_e( 'per la spedizione GRATUITA', 'pharmanow' ); ?>
				<?php endif; ?>
			</p>
		</div>
		<div class="h-2 rounded-full bg-gray-100 overflow-hidden">
			<div class="pn-fsb-page__bar h-full bg-gradient-to-r from-pharma-teal to-pharma-accent-light transition-[width] duration-500 ease-out" style="width: <?php echo esc_attr( (string) $pn_pct ); ?>%"></div>
		</div>
	</div>

	<form class="woocommerce-cart-form pn-cart-form grid grid-cols-1 lg:grid-cols-[1fr_22rem] gap-6 items-start" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
		<?php do_action( 'woocommerce_before_cart_table' ); ?>

		<!-- Items list (sx) -->
		<section class="rounded-xl border border-gray-200 bg-white shadow-sm divide-y divide-gray-100">
			<header class="hidden md:grid grid-cols-[1fr_8rem_8rem_3rem] gap-4 px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500">
				<span><?php esc_html_e( 'Prodotto', 'pharmanow' ); ?></span>
				<span class="text-center"><?php esc_html_e( 'Quantità', 'pharmanow' ); ?></span>
				<span class="text-right"><?php esc_html_e( 'Subtotale', 'pharmanow' ); ?></span>
				<span></span>
			</header>

			<?php do_action( 'woocommerce_before_cart_contents' ); ?>

			<?php
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

				if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
					$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'pn-product-thumb', array( 'class' => 'h-20 w-20 md:h-24 md:w-24 object-contain rounded-lg border border-gray-200 bg-white p-2' ) ), $cart_item, $cart_item_key );
					$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
					$product_subtotal  = apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
					?>
					<div class="pn-cart-item grid grid-cols-[5rem_1fr_2rem] md:grid-cols-[1fr_8rem_8rem_3rem] gap-4 px-5 py-4 items-center">

						<!-- Product (image + name) -->
						<div class="flex items-center gap-4 col-span-1 md:col-span-1">
							<?php if ( $product_permalink ) : ?>
								<a href="<?php echo esc_url( $product_permalink ); ?>" class="shrink-0">
									<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</a>
							<?php else : ?>
								<span class="shrink-0"><?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<?php endif; ?>
							<div class="min-w-0">
								<?php if ( $product_permalink ) : ?>
									<a href="<?php echo esc_url( $product_permalink ); ?>" class="text-sm md:text-base font-semibold text-gray-900 hover:text-pharma-teal leading-snug line-clamp-2">
										<?php echo wp_kses_post( $product_name ); ?>
									</a>
								<?php else : ?>
									<span class="text-sm md:text-base font-semibold text-gray-900 leading-snug line-clamp-2">
										<?php echo wp_kses_post( $product_name ); ?>
									</span>
								<?php endif; ?>
								<div class="mt-1 text-sm text-pharma-teal font-bold tabular-nums">
									<?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</div>
								<?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
						</div>

						<!-- Quantity -->
						<div class="md:text-center md:justify-self-center">
							<?php
							if ( $_product->is_sold_individually() ) {
								echo '<span class="inline-flex items-center justify-center h-10 w-12 border border-gray-300 font-semibold tabular-nums">1</span>';
								echo '<input type="hidden" name="cart[' . esc_attr( $cart_item_key ) . '][qty]" value="1">';
							} else {
								?>
								<div class="pn-qty-group" data-pn-qty-group data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>">
									<button type="button" class="pn-qty-btn" data-pn-qty-action="dec" aria-label="<?php esc_attr_e( 'Riduci', 'pharmanow' ); ?>">
										<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg>
									</button>
									<span class="pn-qty-value" data-pn-qty-value><?php echo esc_html( (string) $cart_item['quantity'] ); ?></span>
									<button type="button" class="pn-qty-btn" data-pn-qty-action="inc" aria-label="<?php esc_attr_e( 'Aumenta', 'pharmanow' ); ?>">
										<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
									</button>
								</div>
								<input type="hidden" name="cart[<?php echo esc_attr( $cart_item_key ); ?>][qty]" value="<?php echo esc_attr( (string) $cart_item['quantity'] ); ?>">
								<?php
							}
							?>
						</div>

						<!-- Subtotal -->
						<div class="md:text-right md:justify-self-end font-bold text-pharma-teal tabular-nums">
							<?php echo $product_subtotal; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>

						<!-- Remove -->
						<div class="md:text-right md:justify-self-end">
							<?php
							echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								'woocommerce_cart_item_remove_link',
								sprintf(
									'<a href="%s" class="inline-flex items-center justify-center h-8 w-8 rounded text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
									esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
									esc_attr__( 'Rimuovi articolo', 'pharmanow' ),
									esc_attr( $product_id ),
									esc_attr( $_product->get_sku() )
								),
								$cart_item_key
							);
							?>
						</div>

					</div>
					<?php
				}
			}
			?>

			<?php do_action( 'woocommerce_cart_contents' ); ?>

			<!-- Coupon + Update row -->
			<div class="pn-cart-actions">
				<?php if ( wc_coupons_enabled() ) : ?>
					<div class="pn-cart-actions__coupon">
						<label for="coupon_code" class="sr-only"><?php esc_html_e( 'Codice coupon', 'pharmanow' ); ?></label>
						<input type="text" name="coupon_code" id="coupon_code" placeholder="<?php esc_attr_e( 'Codice coupon', 'pharmanow' ); ?>" class="pn-cart-actions__input">
						<button type="submit" class="pn-cart-actions__apply" name="apply_coupon" value="<?php esc_attr_e( 'Applica coupon', 'pharmanow' ); ?>">
							<?php esc_html_e( 'Applica', 'pharmanow' ); ?>
						</button>
					</div>
				<?php endif; ?>
				<button type="submit" class="pn-cart-actions__update" name="update_cart" value="<?php esc_attr_e( 'Aggiorna carrello', 'pharmanow' ); ?>">
					<?php esc_html_e( 'Aggiorna carrello', 'pharmanow' ); ?>
				</button>
				<?php do_action( 'woocommerce_cart_actions' ); ?>
				<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
			</div>

			<?php do_action( 'woocommerce_after_cart_contents' ); ?>
		</section>

		<?php do_action( 'woocommerce_after_cart_table' ); ?>

		<!-- Sidebar totals (dx, sticky on desktop) -->
		<aside class="lg:sticky lg:top-24">
			<?php
			// Render cart-totals.php directly — il loop standard `woocommerce_cart_collaterals()`
			// includerebbe anche cross-sells, che vogliamo invece sotto, fuori dalla griglia.
			wc_get_template( 'cart/cart-totals.php' );
			?>
		</aside>
	</form>

	<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>

	<div class="mt-12">
		<?php
		if ( function_exists( 'woocommerce_cross_sell_display' ) ) {
			woocommerce_cross_sell_display();
		}
		?>
	</div>

</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
