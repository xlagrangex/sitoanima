<?php
/**
 * Wishlist Feature
 *
 * Handles wishlist storage (cookie for guests, user meta for logged-in users),
 * AJAX toggle/get endpoints, shortcode rendering, and helper functions.
 *
 * @package BizStudio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/* ================================================================
   Constants
   ================================================================ */
define( 'BIZSTUDIO_WISHLIST_COOKIE', 'biz_wishlist' );
define( 'BIZSTUDIO_WISHLIST_META',   'bizstudio_wishlist' );
define( 'BIZSTUDIO_WISHLIST_NONCE',  'bizstudio_wishlist_nonce' );
define( 'BIZSTUDIO_WISHLIST_EXPIRY', 30 * DAY_IN_SECONDS );

/* ================================================================
   Helper: get wishlist IDs
   ================================================================ */

/**
 * Return the current user's wishlist as a flat array of product IDs.
 *
 * @return int[]
 */
function bizstudio_get_wishlist_ids(): array {
	if ( is_user_logged_in() ) {
		$ids = get_user_meta( get_current_user_id(), BIZSTUDIO_WISHLIST_META, true );
		return is_array( $ids ) ? array_values( array_map( 'absint', $ids ) ) : [];
	}

	// Guest – read from cookie
	if ( isset( $_COOKIE[ BIZSTUDIO_WISHLIST_COOKIE ] ) ) {
		$raw = json_decode( wp_unslash( $_COOKIE[ BIZSTUDIO_WISHLIST_COOKIE ] ), true );
		if ( is_array( $raw ) ) {
			return array_values( array_map( 'absint', $raw ) );
		}
	}

	return [];
}

/**
 * Check whether a product is in the wishlist.
 */
function bizstudio_is_in_wishlist( int $product_id ): bool {
	return in_array( $product_id, bizstudio_get_wishlist_ids(), true );
}

/**
 * Return the wishlist count.
 */
function bizstudio_wishlist_count(): int {
	return count( bizstudio_get_wishlist_ids() );
}

/* ================================================================
   Helper: persist wishlist
   ================================================================ */

/**
 * Save the wishlist array.
 *
 * @param int[] $ids
 */
function bizstudio_save_wishlist( array $ids ): void {
	$ids = array_values( array_unique( array_map( 'absint', $ids ) ) );

	if ( is_user_logged_in() ) {
		update_user_meta( get_current_user_id(), BIZSTUDIO_WISHLIST_META, $ids );
	}

	// Always update the cookie so it stays in sync (also covers guest).
	setcookie(
		BIZSTUDIO_WISHLIST_COOKIE,
		wp_json_encode( $ids ),
		time() + BIZSTUDIO_WISHLIST_EXPIRY,
		COOKIEPATH,
		COOKIE_DOMAIN,
		is_ssl(),
		false // JS needs to read it? No – but cookie is set for consistency.
	);
}

/* ================================================================
   Merge cookie → user meta on login
   ================================================================ */
add_action( 'wp_login', function ( $user_login, $user ) {
	if ( ! isset( $_COOKIE[ BIZSTUDIO_WISHLIST_COOKIE ] ) ) {
		return;
	}

	$cookie_ids = json_decode( wp_unslash( $_COOKIE[ BIZSTUDIO_WISHLIST_COOKIE ] ), true );
	if ( ! is_array( $cookie_ids ) || empty( $cookie_ids ) ) {
		return;
	}

	$existing = get_user_meta( $user->ID, BIZSTUDIO_WISHLIST_META, true );
	$existing = is_array( $existing ) ? $existing : [];
	$merged   = array_values( array_unique( array_merge( $existing, array_map( 'absint', $cookie_ids ) ) ) );

	update_user_meta( $user->ID, BIZSTUDIO_WISHLIST_META, $merged );
}, 10, 2 );

/* ================================================================
   AJAX: Toggle wishlist
   ================================================================ */
add_action( 'wp_ajax_bizstudio_toggle_wishlist',        'bizstudio_ajax_toggle_wishlist' );
add_action( 'wp_ajax_nopriv_bizstudio_toggle_wishlist', 'bizstudio_ajax_toggle_wishlist' );

function bizstudio_ajax_toggle_wishlist(): void {
	check_ajax_referer( BIZSTUDIO_WISHLIST_NONCE, 'nonce' );

	$product_id = absint( $_POST['product_id'] ?? 0 );
	if ( ! $product_id || ! wc_get_product( $product_id ) ) {
		wp_send_json_error( [ 'message' => __( 'Prodotto non valido.', 'bizstudio' ) ] );
	}

	$ids         = bizstudio_get_wishlist_ids();
	$in_wishlist = in_array( $product_id, $ids, true );

	if ( $in_wishlist ) {
		$ids = array_values( array_diff( $ids, [ $product_id ] ) );
	} else {
		$ids[] = $product_id;
	}

	bizstudio_save_wishlist( $ids );

	wp_send_json_success( [
		'in_wishlist' => ! $in_wishlist,
		'count'       => count( $ids ),
	] );
}

/* ================================================================
   AJAX: Get wishlist (returns current state)
   ================================================================ */
add_action( 'wp_ajax_bizstudio_get_wishlist',        'bizstudio_ajax_get_wishlist' );
add_action( 'wp_ajax_nopriv_bizstudio_get_wishlist', 'bizstudio_ajax_get_wishlist' );

function bizstudio_ajax_get_wishlist(): void {
	$ids = bizstudio_get_wishlist_ids();

	wp_send_json_success( [
		'ids'   => $ids,
		'count' => count( $ids ),
	] );
}

/* ================================================================
   Render: heart button
   ================================================================ */

/**
 * Return the wishlist toggle button HTML for a given product.
 *
 * @param int    $product_id
 * @param string $extra_class Optional extra CSS class.
 * @return string
 */
function bizstudio_render_wishlist_button( int $product_id, string $extra_class = '' ): string {
	$active    = bizstudio_is_in_wishlist( $product_id );
	$cls       = 'biz-wishlist-btn';
	if ( $active ) {
		$cls .= ' biz-wishlist-btn--active';
	}
	if ( $extra_class ) {
		$cls .= ' ' . esc_attr( $extra_class );
	}

	$label_add    = esc_attr__( 'Aggiungi alla wishlist', 'bizstudio' );
	$label_remove = esc_attr__( 'Rimuovi dalla wishlist', 'bizstudio' );
	$label        = $active ? $label_remove : $label_add;

	// Outline heart
	$svg_outline = '<svg class="biz-wishlist-btn__icon biz-wishlist-btn__icon--outline" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>';

	// Filled heart
	$svg_filled = '<svg class="biz-wishlist-btn__icon biz-wishlist-btn__icon--filled" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>';

	return sprintf(
		'<button type="button" class="%s" data-product-id="%d" aria-label="%s" data-label-add="%s" data-label-remove="%s">%s%s</button>',
		esc_attr( $cls ),
		$product_id,
		$label,
		$label_add,
		$label_remove,
		$svg_outline,
		$svg_filled
	);
}

/* ================================================================
   Shortcode: [bizstudio_wishlist]
   ================================================================ */
add_shortcode( 'bizstudio_wishlist', 'bizstudio_wishlist_shortcode' );

function bizstudio_wishlist_shortcode(): string {
	$ids = bizstudio_get_wishlist_ids();

	ob_start();
	?>
	<div class="biz-wishlist-page">
		<?php if ( empty( $ids ) ) : ?>

			<div class="biz-wishlist-page__empty">
				<svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:.35;margin-bottom:1rem">
					<path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
				</svg>
				<h2><?php esc_html_e( 'La tua wishlist è vuota', 'bizstudio' ); ?></h2>
				<p><?php esc_html_e( 'Esplora il nostro catalogo e salva i prodotti che ti piacciono.', 'bizstudio' ); ?></p>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="biz-btn biz-btn--primary">
					<?php esc_html_e( 'Vai allo shop', 'bizstudio' ); ?>
				</a>
			</div>

		<?php else : ?>

			<div class="biz-wishlist-page__grid" data-reveal>
				<?php
				$args = [
					'post_type'      => 'product',
					'post__in'       => $ids,
					'orderby'        => 'post__in',
					'posts_per_page' => -1,
					'post_status'    => 'publish',
				];

				$query = new WP_Query( $args );

				if ( $query->have_posts() ) :
					while ( $query->have_posts() ) :
						$query->the_post();
						global $product;

						if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
							continue;
						}

						$pid       = $product->get_id();
						$permalink = get_permalink( $pid );
						$image_id  = $product->get_image_id();
						$on_sale   = $product->is_on_sale();
						$sale_pct  = bizstudio_sale_percentage( $product );
						?>
						<div class="biz-card biz-wishlist-page__item" data-product-id="<?php echo esc_attr( $pid ); ?>" data-reveal-item>
							<!-- Image -->
							<div class="biz-card__image-wrap">
								<a href="<?php echo esc_url( $permalink ); ?>" class="biz-card__image-link">
									<?php if ( $image_id ) : ?>
										<?php echo wp_get_attachment_image( $image_id, 'woocommerce_thumbnail', false, [
											'class'   => 'biz-card__image biz-card__image--primary',
											'loading' => 'lazy',
										] ); ?>
									<?php else : ?>
										<div class="biz-card__image-placeholder">
											<span><?php echo esc_html( mb_substr( $product->get_name(), 0, 1 ) ); ?></span>
										</div>
									<?php endif; ?>
								</a>

								<?php if ( $on_sale && $sale_pct > 0 ) : ?>
									<div class="biz-card__badges">
										<span class="biz-badge biz-badge--sale">-<?php echo esc_html( $sale_pct ); ?>%</span>
									</div>
								<?php endif; ?>

								<!-- Remove from wishlist -->
								<button type="button" class="biz-wishlist-page__remove biz-wishlist-btn biz-wishlist-btn--active" data-product-id="<?php echo esc_attr( $pid ); ?>" aria-label="<?php esc_attr_e( 'Rimuovi dalla wishlist', 'bizstudio' ); ?>">
									<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
								</button>
							</div>

							<!-- Info -->
							<div class="biz-card__info">
								<h3 class="biz-card__title">
									<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
								</h3>
								<div class="biz-card__price"><?php echo $product->get_price_html(); ?></div>

								<?php if ( $product->is_in_stock() && $product->is_type( 'simple' ) ) : ?>
									<button class="biz-btn biz-btn--primary biz-btn--sm biz-btn--full biz-add-to-cart-btn" data-product-id="<?php echo esc_attr( $pid ); ?>">
										<?php esc_html_e( 'Aggiungi al carrello', 'bizstudio' ); ?>
									</button>
								<?php else : ?>
									<a href="<?php echo esc_url( $permalink ); ?>" class="biz-btn biz-btn--outline biz-btn--sm biz-btn--full">
										<?php esc_html_e( 'Vedi prodotto', 'bizstudio' ); ?>
									</a>
								<?php endif; ?>
							</div>
						</div>
						<?php
					endwhile;
					wp_reset_postdata();
				endif;
				?>
			</div>

		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

/* ================================================================
   Enqueue wishlist assets
   ================================================================ */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	$ver = BIZSTUDIO_VERSION;

	// CSS
	wp_enqueue_style(
		'bizstudio-wishlist',
		BIZSTUDIO_URI . '/assets/src/css/components/wishlist.css',
		[ 'bizstudio-main' ],
		$ver
	);

	// JS
	wp_enqueue_script(
		'bizstudio-wishlist',
		BIZSTUDIO_URI . '/assets/src/js/modules/wishlist.js',
		[ 'bizstudio-main' ],
		$ver,
		[ 'strategy' => 'defer', 'in_footer' => true ]
	);

	// Localize
	wp_localize_script( 'bizstudio-wishlist', 'bizstudioWishlist', [
		'ajax_url'     => admin_url( 'admin-ajax.php' ),
		'nonce'        => wp_create_nonce( BIZSTUDIO_WISHLIST_NONCE ),
		'ids'          => bizstudio_get_wishlist_ids(),
		'count'        => bizstudio_wishlist_count(),
	] );
} );

/* ================================================================
   WooCommerce cart fragments — update wishlist count in header
   ================================================================ */
add_filter( 'woocommerce_add_to_cart_fragments', function ( array $fragments ): array {
	$count = bizstudio_wishlist_count();
	$fragments['.biz-header__wishlist-count'] = '<span class="biz-header__wishlist-count">' . esc_html( $count ) . '</span>';
	return $fragments;
} );
