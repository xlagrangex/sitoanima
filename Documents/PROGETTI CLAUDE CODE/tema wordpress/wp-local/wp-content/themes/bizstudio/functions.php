<?php
/**
 * BizStudio Theme Functions
 *
 * @package BizStudio
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

define( 'BIZSTUDIO_VERSION', '1.0.0' );
define( 'BIZSTUDIO_DIR', get_template_directory() );
define( 'BIZSTUDIO_URI', get_template_directory_uri() );

// Core includes
require_once BIZSTUDIO_DIR . '/inc/setup.php';
require_once BIZSTUDIO_DIR . '/inc/enqueue.php';
require_once BIZSTUDIO_DIR . '/inc/template-tags.php';
require_once BIZSTUDIO_DIR . '/inc/translations.php';

// Theme features (always loaded)
require_once BIZSTUDIO_DIR . '/inc/promo-bar.php';
require_once BIZSTUDIO_DIR . '/inc/customizer.php';
require_once BIZSTUDIO_DIR . '/inc/header-builder.php';
require_once BIZSTUDIO_DIR . '/inc/seo.php';
require_once BIZSTUDIO_DIR . '/inc/contact-form.php';
require_once BIZSTUDIO_DIR . '/inc/footer-builder.php';
require_once BIZSTUDIO_DIR . '/inc/performance.php';
require_once BIZSTUDIO_DIR . '/inc/cleanup.php';
require_once BIZSTUDIO_DIR . '/inc/compat.php';

// WooCommerce integration
if ( class_exists( 'WooCommerce' ) ) {
	require_once BIZSTUDIO_DIR . '/inc/woocommerce.php';
	require_once BIZSTUDIO_DIR . '/inc/ajax-handlers.php';
	require_once BIZSTUDIO_DIR . '/inc/swatches.php';
	require_once BIZSTUDIO_DIR . '/inc/wishlist.php';
	require_once BIZSTUDIO_DIR . '/inc/shipping-bar.php';
	require_once BIZSTUDIO_DIR . '/inc/sticky-bar.php';
}
