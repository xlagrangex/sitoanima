<?php
/**
 * Mini Cart Drawer
 *
 * @package BizStudio
 */

defined( 'ABSPATH' ) || exit;

$cart = WC()->cart;
if ( ! $cart ) return;
?>

<div class="biz-mini-cart" id="biz-mini-cart">
	<div class="biz-mini-cart__header">
		<h3 class="biz-mini-cart__title">
			<?php esc_html_e( 'Carrello', 'bizstudio' ); ?>
			<span class="biz-mini-cart__count">(<?php echo $cart->get_cart_contents_count(); ?>)</span>
		</h3>
		<button class="biz-mini-cart__close" id="biz-mini-cart-close">
			<?php echo bizstudio_icon( 'close' ); ?>
		</button>
	</div>

	<?php if ( function_exists( 'bizstudio_render_shipping_bar' ) ) : ?>
		<div class="biz-mini-cart__shipping-bar">
			<?php bizstudio_render_shipping_bar(); ?>
		</div>
	<?php endif; ?>

	<div class="biz-mini-cart__body">
		<?php if ( $cart->is_empty() ) : ?>
			<div class="biz-mini-cart__empty">
				<p><?php esc_html_e( 'Il tuo carrello e vuoto.', 'bizstudio' ); ?></p>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="biz-btn biz-btn--primary">
					<?php esc_html_e( 'Inizia lo shopping', 'bizstudio' ); ?>
				</a>
			</div>
		<?php else : ?>
			<ul class="biz-mini-cart__items">
				<?php foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) :
					$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
					if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 ) continue;
				?>
					<li class="biz-mini-cart__item">
						<div class="biz-mini-cart__item-image">
							<?php echo $_product->get_image( 'thumbnail' ); ?>
						</div>
						<div class="biz-mini-cart__item-info">
							<h4 class="biz-mini-cart__item-name">
								<?php echo esc_html( $_product->get_name() ); ?>
							</h4>
							<span class="biz-mini-cart__item-qty">
								<?php echo esc_html( $cart_item['quantity'] ); ?> &times; <?php echo $_product->get_price_html(); ?>
							</span>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>

			<div class="biz-mini-cart__footer">
				<div class="biz-mini-cart__total">
					<span><?php esc_html_e( 'Totale', 'bizstudio' ); ?></span>
					<strong><?php echo $cart->get_cart_total(); ?></strong>
				</div>
				<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="biz-btn biz-btn--outline biz-btn--full">
					<?php esc_html_e( 'Vai al carrello', 'bizstudio' ); ?>
				</a>
				<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="biz-btn biz-btn--primary biz-btn--full">
					<?php esc_html_e( 'Checkout', 'bizstudio' ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</div>
