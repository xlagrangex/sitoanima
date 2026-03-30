<?php
/**
 * Product Card Template
 *
 * @package BizStudio
 */

defined( 'ABSPATH' ) || exit;

global $product;
if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
	return;
}

$product_id   = $product->get_id();
$permalink    = get_permalink( $product_id );
$image_id     = $product->get_image_id();
$gallery_ids  = $product->get_gallery_image_ids();
$second_image = ! empty( $gallery_ids ) ? $gallery_ids[0] : null;
$on_sale      = $product->is_on_sale();
$sale_pct     = bizstudio_sale_percentage( $product );
$rating_count = $product->get_rating_count();
$avg_rating   = $product->get_average_rating();
$stock_status = $product->get_stock_status();
$categories   = wc_get_product_category_list( $product_id, ', ' );
?>

<div class="biz-card <?php echo $stock_status !== 'instock' ? 'biz-card--out-of-stock' : ''; ?>" data-product-id="<?php echo esc_attr( $product_id ); ?>" data-reveal-item>
	<!-- Image -->
	<div class="biz-card__image-wrap">
		<a href="<?php echo esc_url( $permalink ); ?>" class="biz-card__image-link">
			<?php if ( $image_id ) : ?>
				<?php echo wp_get_attachment_image( $image_id, 'woocommerce_thumbnail', false, [
					'class'   => 'biz-card__image biz-card__image--primary',
					'loading' => 'lazy',
				] ); ?>
				<?php if ( $second_image ) : ?>
					<?php echo wp_get_attachment_image( $second_image, 'woocommerce_thumbnail', false, [
						'class'   => 'biz-card__image biz-card__image--secondary',
						'loading' => 'lazy',
					] ); ?>
				<?php endif; ?>
			<?php else : ?>
				<div class="biz-card__image-placeholder">
					<span><?php echo esc_html( mb_substr( $product->get_name(), 0, 1 ) ); ?></span>
				</div>
			<?php endif; ?>
		</a>

		<!-- Badges -->
		<div class="biz-card__badges">
			<?php if ( $on_sale && $sale_pct > 0 ) : ?>
				<span class="biz-badge biz-badge--sale">-<?php echo esc_html( $sale_pct ); ?>%</span>
			<?php endif; ?>
			<?php if ( $stock_status !== 'instock' ) : ?>
				<span class="biz-badge biz-badge--out"><?php esc_html_e( 'Esaurito', 'bizstudio' ); ?></span>
			<?php endif; ?>
			<?php if ( $product->is_featured() ) : ?>
				<span class="biz-badge biz-badge--new"><?php esc_html_e( 'New', 'bizstudio' ); ?></span>
			<?php endif; ?>
		</div>

		<!-- Quick Actions -->
		<div class="biz-card__actions">
			<?php if ( $stock_status === 'instock' && $product->is_type( 'simple' ) ) : ?>
				<button class="biz-card__action biz-add-to-cart-btn" data-product-id="<?php echo esc_attr( $product_id ); ?>" aria-label="<?php esc_attr_e( 'Aggiungi al carrello', 'bizstudio' ); ?>">
					<?php echo bizstudio_icon( 'cart' ); ?>
				</button>
			<?php endif; ?>
			<a href="<?php echo esc_url( $permalink ); ?>" class="biz-card__action" aria-label="<?php esc_attr_e( 'Vedi prodotto', 'bizstudio' ); ?>">
				<?php echo bizstudio_icon( 'eye' ); ?>
			</a>
			<?php if ( function_exists( 'bizstudio_render_wishlist_button' ) ) : ?>
				<?php echo bizstudio_render_wishlist_button( $product_id, 'biz-card__action' ); ?>
			<?php endif; ?>
		</div>
	</div>

	<!-- Info -->
	<div class="biz-card__info">
		<?php if ( $categories ) : ?>
			<div class="biz-card__category"><?php echo wp_kses_post( $categories ); ?></div>
		<?php endif; ?>

		<h3 class="biz-card__title">
			<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
		</h3>

		<?php if ( $rating_count > 0 ) : ?>
			<?php echo bizstudio_star_rating( $avg_rating, $rating_count ); ?>
		<?php endif; ?>

		<div class="biz-card__price">
			<?php echo $product->get_price_html(); ?>
		</div>
	</div>
</div>
