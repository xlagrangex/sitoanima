<?php
/**
 * Single Product Page
 *
 * @package BizStudio
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	global $product;
?>

<?php woocommerce_breadcrumb(); ?>

<main class="biz-main biz-single-product">
	<div class="biz-container">
		<div class="biz-product-layout" data-reveal>
			<!-- Gallery -->
			<div class="biz-product-gallery" id="biz-product-gallery">
				<?php
				$image_id    = $product->get_image_id();
				$gallery_ids = $product->get_gallery_image_ids();
				$all_images  = $image_id ? array_merge( [ $image_id ], $gallery_ids ) : $gallery_ids;
				?>

				<!-- Main Image -->
				<div class="biz-product-gallery__main">
					<?php if ( $product->is_on_sale() ) : ?>
						<span class="biz-badge biz-badge--sale biz-product-gallery__badge">
							-<?php echo bizstudio_sale_percentage( $product ); ?>%
						</span>
					<?php endif; ?>
					<?php if ( $image_id ) : ?>
						<div class="biz-product-gallery__image-wrap" id="biz-gallery-main">
							<?php echo wp_get_attachment_image( $image_id, 'woocommerce_single', false, [
								'class' => 'biz-product-gallery__image',
								'id'    => 'biz-main-image',
							] ); ?>
						</div>
					<?php else : ?>
						<div class="biz-product-gallery__placeholder">
							<span><?php echo esc_html( mb_substr( $product->get_name(), 0, 1 ) ); ?></span>
						</div>
					<?php endif; ?>
				</div>

				<!-- Thumbnails -->
				<?php if ( count( $all_images ) > 1 ) : ?>
				<div class="biz-product-gallery__thumbs">
					<?php foreach ( $all_images as $i => $img_id ) : ?>
						<button class="biz-product-gallery__thumb <?php echo $i === 0 ? 'biz-product-gallery__thumb--active' : ''; ?>"
							data-image-id="<?php echo esc_attr( $img_id ); ?>"
							data-full="<?php echo esc_url( wp_get_attachment_image_url( $img_id, 'woocommerce_single' ) ); ?>">
							<?php echo wp_get_attachment_image( $img_id, 'thumbnail', false, [
								'class' => 'biz-product-gallery__thumb-img',
							] ); ?>
						</button>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
			</div>

			<!-- Product Info -->
			<div class="biz-product-info">
				<!-- Categories -->
				<?php $cats = wc_get_product_category_list( $product->get_id(), ', ' ); ?>
				<?php if ( $cats ) : ?>
					<div class="biz-product-info__category"><?php echo wp_kses_post( $cats ); ?></div>
				<?php endif; ?>

				<!-- Title -->
				<h1 class="biz-product-info__title"><?php the_title(); ?></h1>

				<!-- Rating -->
				<?php if ( $product->get_rating_count() > 0 ) : ?>
					<div class="biz-product-info__rating">
						<?php echo bizstudio_star_rating( $product->get_average_rating(), $product->get_rating_count() ); ?>
					</div>
				<?php endif; ?>

				<!-- Price -->
				<div class="biz-product-info__price">
					<?php echo $product->get_price_html(); ?>
				</div>

				<!-- Short description -->
				<?php if ( $product->get_short_description() ) : ?>
					<div class="biz-product-info__short-desc">
						<?php echo wpautop( $product->get_short_description() ); ?>
					</div>
				<?php endif; ?>

				<!-- Add to cart form -->
				<div class="biz-product-info__cart">
					<?php woocommerce_template_single_add_to_cart(); ?>
				</div>

				<!-- Meta -->
				<div class="biz-product-info__meta">
					<?php if ( $product->get_sku() ) : ?>
						<div class="biz-product-meta__item">
							<span class="biz-product-meta__label"><?php esc_html_e( 'SKU:', 'bizstudio' ); ?></span>
							<span><?php echo esc_html( $product->get_sku() ); ?></span>
						</div>
					<?php endif; ?>
					<?php if ( $cats ) : ?>
						<div class="biz-product-meta__item">
							<span class="biz-product-meta__label"><?php esc_html_e( 'Categoria:', 'bizstudio' ); ?></span>
							<?php echo wp_kses_post( $cats ); ?>
						</div>
					<?php endif; ?>
					<?php $tags = wc_get_product_tag_list( $product->get_id(), ', ' ); ?>
					<?php if ( $tags ) : ?>
						<div class="biz-product-meta__item">
							<span class="biz-product-meta__label"><?php esc_html_e( 'Tag:', 'bizstudio' ); ?></span>
							<?php echo wp_kses_post( $tags ); ?>
						</div>
					<?php endif; ?>
				</div>

				<!-- Share -->
				<div class="biz-product-info__share">
					<span class="biz-product-meta__label"><?php esc_html_e( 'Condividi:', 'bizstudio' ); ?></span>
					<div class="biz-share-links">
						<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode( get_permalink() ); ?>" target="_blank" rel="noopener" class="biz-share-link" aria-label="Facebook">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
						</a>
						<a href="https://twitter.com/intent/tweet?url=<?php echo urlencode( get_permalink() ); ?>&text=<?php echo urlencode( get_the_title() ); ?>" target="_blank" rel="noopener" class="biz-share-link" aria-label="X">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M4 4l6.5 8.5L4 20h2l5.5-6.5L16 20h4l-7-9 6-7h-2l-5 5.5L8 4z"/></svg>
						</a>
						<a href="https://wa.me/?text=<?php echo urlencode( get_the_title() . ' ' . get_permalink() ); ?>" target="_blank" rel="noopener" class="biz-share-link" aria-label="WhatsApp">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0 0 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18a8 8 0 0 1-4.106-1.134l-.288-.172-2.98.78.794-2.9-.187-.297A7.98 7.98 0 0 1 4 12a8 8 0 1 1 16 0 8 8 0 0 1-8 8z"/></svg>
						</a>
						<button class="biz-share-link biz-copy-link" data-url="<?php echo esc_attr( get_permalink() ); ?>" aria-label="<?php esc_attr_e( 'Copia link', 'bizstudio' ); ?>">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
						</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Tabs -->
		<div class="biz-product-tabs" data-reveal>
			<?php woocommerce_output_product_data_tabs(); ?>
		</div>

		<!-- Related Products -->
		<div class="biz-related-products" data-reveal>
			<?php woocommerce_output_related_products(); ?>
		</div>
	</div>
</main>

<?php endwhile; ?>

<?php
get_footer();
