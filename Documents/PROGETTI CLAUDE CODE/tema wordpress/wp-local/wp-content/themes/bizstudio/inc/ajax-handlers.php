<?php
/**
 * AJAX Handlers
 *
 * @package BizStudio
 */

defined( 'ABSPATH' ) || exit;

/**
 * AJAX Add to Cart
 */
add_action( 'wp_ajax_bizstudio_add_to_cart', 'bizstudio_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_bizstudio_add_to_cart', 'bizstudio_ajax_add_to_cart' );

function bizstudio_ajax_add_to_cart(): void {
	check_ajax_referer( 'bizstudio_nonce', 'nonce' );

	$product_id   = absint( $_POST['product_id'] ?? 0 );
	$quantity     = absint( $_POST['quantity'] ?? 1 );
	$variation_id = absint( $_POST['variation_id'] ?? 0 );

	if ( ! $product_id ) {
		wp_send_json_error( [ 'message' => __( 'Prodotto non valido.', 'bizstudio' ) ] );
	}

	// Collect variation attributes
	$variation = [];
	foreach ( $_POST as $key => $value ) {
		if ( str_starts_with( $key, 'attribute_' ) ) {
			$variation[ $key ] = sanitize_text_field( $value );
		}
	}

	$added = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation );

	if ( $added ) {
		// Get updated mini-cart HTML
		ob_start();
		get_template_part( 'template-parts/global/mini-cart' );
		$mini_cart_html = ob_get_clean();

		wp_send_json_success( [
			'message'   => __( 'Prodotto aggiunto al carrello!', 'bizstudio' ),
			'cartCount' => WC()->cart->get_cart_contents_count(),
			'cartTotal' => WC()->cart->get_cart_total(),
			'miniCart'  => $mini_cart_html,
		] );
	} else {
		wp_send_json_error( [ 'message' => __( 'Impossibile aggiungere al carrello.', 'bizstudio' ) ] );
	}
}

/**
 * AJAX Quick View
 */
add_action( 'wp_ajax_bizstudio_quick_view', 'bizstudio_ajax_quick_view' );
add_action( 'wp_ajax_nopriv_bizstudio_quick_view', 'bizstudio_ajax_quick_view' );

function bizstudio_ajax_quick_view(): void {
	$product_id = absint( $_GET['product_id'] ?? 0 );
	if ( ! $product_id ) {
		wp_send_json_error( [ 'message' => 'Prodotto non valido.' ] );
	}

	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		wp_send_json_error( [ 'message' => 'Prodotto non trovato.' ] );
	}

	$image_id   = $product->get_image_id();
	$image_url  = $image_id ? wp_get_attachment_image_url( $image_id, 'woocommerce_single' ) : wc_placeholder_img_src();
	$categories = wc_get_product_category_list( $product_id, ', ' );
	$rating     = $product->get_average_rating();
	$count      = $product->get_rating_count();

	ob_start();
	?>
	<div class="biz-qv__grid">
		<div class="biz-qv__image">
			<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>" loading="eager">
			<?php if ( $product->is_on_sale() ) : ?>
				<span class="biz-badge biz-badge--sale">-<?php echo bizstudio_sale_percentage( $product ); ?>%</span>
			<?php endif; ?>
		</div>
		<div class="biz-qv__info">
			<?php if ( $categories ) : ?>
				<div class="biz-qv__category"><?php echo wp_kses_post( $categories ); ?></div>
			<?php endif; ?>
			<h2 class="biz-qv__title"><?php echo esc_html( $product->get_name() ); ?></h2>
			<?php if ( $count > 0 ) : ?>
				<?php echo bizstudio_star_rating( $rating, $count ); ?>
			<?php endif; ?>
			<div class="biz-qv__price"><?php echo $product->get_price_html(); ?></div>
			<div class="biz-qv__desc">
				<?php echo wpautop( $product->get_short_description() ); ?>
			</div>
			<?php if ( $product->is_type( 'simple' ) && $product->is_in_stock() ) : ?>
				<button class="biz-btn biz-btn--primary biz-btn--full biz-add-to-cart-btn" data-product-id="<?php echo esc_attr( $product_id ); ?>">
					<?php esc_html_e( 'Aggiungi al carrello', 'bizstudio' ); ?>
				</button>
			<?php else : ?>
				<a href="<?php echo esc_url( get_permalink( $product_id ) ); ?>" class="biz-btn biz-btn--primary biz-btn--full">
					<?php esc_html_e( 'Vedi prodotto', 'bizstudio' ); ?>
				</a>
			<?php endif; ?>
			<a href="<?php echo esc_url( get_permalink( $product_id ) ); ?>" class="biz-qv__full-link">
				<?php esc_html_e( 'Vedi tutti i dettagli', 'bizstudio' ); ?> &rarr;
			</a>
		</div>
	</div>
	<?php
	$html = ob_get_clean();

	wp_send_json_success( [ 'html' => $html ] );
}

/**
 * AJAX Live Search
 */
add_action( 'wp_ajax_bizstudio_live_search', 'bizstudio_ajax_live_search' );
add_action( 'wp_ajax_nopriv_bizstudio_live_search', 'bizstudio_ajax_live_search' );

function bizstudio_ajax_live_search(): void {
	$query = sanitize_text_field( $_GET['q'] ?? '' );
	if ( strlen( $query ) < 2 ) {
		wp_send_json_success( [ 'html' => '', 'count' => 0 ] );
	}

	$products = wc_get_products( [
		'limit'  => 6,
		'status' => 'publish',
		's'      => $query,
	] );

	if ( empty( $products ) ) {
		$html = '<div class="biz-search-results__empty">' . __( 'Nessun prodotto trovato.', 'bizstudio' ) . '</div>';
		wp_send_json_success( [ 'html' => $html, 'count' => 0 ] );
	}

	ob_start();
	foreach ( $products as $product ) {
		$image_url = $product->get_image_id()
			? wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' )
			: wc_placeholder_img_src();
		$cats = wc_get_product_category_list( $product->get_id(), ', ' );
		?>
		<a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>" class="biz-search-result">
			<div class="biz-search-result__image">
				<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>" loading="lazy">
			</div>
			<div class="biz-search-result__info">
				<?php if ( $cats ) : ?>
					<span class="biz-search-result__cat"><?php echo wp_strip_all_tags( $cats ); ?></span>
				<?php endif; ?>
				<span class="biz-search-result__name"><?php echo esc_html( $product->get_name() ); ?></span>
				<span class="biz-search-result__price"><?php echo $product->get_price_html(); ?></span>
			</div>
		</a>
		<?php
	}
	$html = ob_get_clean();

	wp_send_json_success( [ 'html' => $html, 'count' => count( $products ) ] );
}

/**
 * AJAX Get Mini Cart
 */
add_action( 'wp_ajax_bizstudio_get_mini_cart', 'bizstudio_ajax_get_mini_cart' );
add_action( 'wp_ajax_nopriv_bizstudio_get_mini_cart', 'bizstudio_ajax_get_mini_cart' );

function bizstudio_ajax_get_mini_cart(): void {
	ob_start();
	get_template_part( 'template-parts/global/mini-cart' );
	$html = ob_get_clean();

	wp_send_json_success( [
		'html'      => $html,
		'cartCount' => WC()->cart->get_cart_contents_count(),
		'cartTotal' => WC()->cart->get_cart_total(),
	] );
}
