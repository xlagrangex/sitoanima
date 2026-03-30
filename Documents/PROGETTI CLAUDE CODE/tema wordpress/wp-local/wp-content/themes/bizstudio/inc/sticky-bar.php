<?php
/**
 * Sticky Add-to-Cart Bottom Bar
 *
 * Renders a fixed bottom bar on single product pages that appears
 * when the user scrolls past the original add-to-cart form.
 * Shows product thumbnail, name, price, and an add-to-cart button.
 * For variable products, includes compact variation selectors.
 *
 * @package BizStudio
 */

defined( 'ABSPATH' ) || exit;

/**
 * Output the sticky add-to-cart bar HTML.
 *
 * Hooked to `wp_footer` (priority 50) so it prints at the
 * bottom of the page, only on single product pages.
 */
function bizstudio_render_sticky_bar() {

	if ( ! is_product() ) {
		return;
	}

	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	// Product data
	$product_name  = $product->get_name();
	$product_price = $product->get_price_html();
	$product_id    = $product->get_id();
	$image_id      = $product->get_image_id();
	$is_variable   = $product->is_type( 'variable' );

	// Get thumbnail URL (50x50 is fine for the sticky bar)
	$thumbnail = $image_id
		? wp_get_attachment_image( $image_id, 'thumbnail', false, [
			'class'   => 'biz-sticky-bar__image',
			'alt'     => esc_attr( $product_name ),
			'loading' => 'lazy',
		] )
		: '';

	?>
	<div class="biz-sticky-bar biz-sticky-bar--hidden"
		 id="biz-sticky-bar"
		 role="complementary"
		 aria-label="<?php esc_attr_e( 'Aggiungi al carrello rapido', 'bizstudio' ); ?>">
		<div class="biz-sticky-bar__inner">

			<?php if ( $thumbnail ) : ?>
				<div class="biz-sticky-bar__thumb">
					<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			<?php endif; ?>

			<div class="biz-sticky-bar__info">
				<div class="biz-sticky-bar__name"><?php echo esc_html( $product_name ); ?></div>
				<div class="biz-sticky-bar__price" id="biz-sticky-bar-price"><?php echo wp_kses_post( $product_price ); ?></div>
			</div>

			<div class="biz-sticky-bar__actions">
				<?php if ( $is_variable ) : ?>
					<?php
					$attributes = $product->get_variation_attributes();
					foreach ( $attributes as $attribute_name => $options ) :
						$sanitized_name = 'attribute_' . sanitize_title( $attribute_name );
						$taxonomy       = wc_attribute_taxonomy_name( str_replace( 'pa_', '', $attribute_name ) );
					?>
						<select class="biz-sticky-bar__select"
								id="biz-sticky-<?php echo esc_attr( $sanitized_name ); ?>"
								data-attribute="<?php echo esc_attr( $sanitized_name ); ?>"
								aria-label="<?php echo esc_attr( wc_attribute_label( $attribute_name ) ); ?>">
							<option value=""><?php echo esc_html( wc_attribute_label( $attribute_name ) ); ?></option>
							<?php foreach ( $options as $option ) :
								$label = taxonomy_exists( $attribute_name )
									? get_term_by( 'slug', $option, $attribute_name )->name ?? $option
									: $option;
							?>
								<option value="<?php echo esc_attr( $option ); ?>">
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					<?php endforeach; ?>
				<?php endif; ?>

				<button type="button"
						class="biz-sticky-bar__btn"
						id="biz-sticky-bar-btn"
						data-product-id="<?php echo esc_attr( $product_id ); ?>">
					<?php esc_html_e( 'Aggiungi al carrello', 'bizstudio' ); ?>
				</button>
			</div>

		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'bizstudio_render_sticky_bar', 50 );

/**
 * Enqueue sticky bar CSS and JS on single product pages.
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_product() ) {
		return;
	}

	$ver = BIZSTUDIO_VERSION;

	wp_enqueue_style(
		'bizstudio-sticky-bar',
		BIZSTUDIO_URI . '/assets/src/css/components/sticky-bar.css',
		[ 'bizstudio-main' ],
		$ver
	);

	wp_enqueue_script(
		'bizstudio-sticky-bar',
		BIZSTUDIO_URI . '/assets/src/js/modules/sticky-bar.js',
		[ 'bizstudio-main' ],
		$ver,
		[ 'strategy' => 'defer', 'in_footer' => true ]
	);
} );
