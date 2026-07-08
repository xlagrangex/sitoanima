<?php
/**
 * Pharmanow theme bootstrap.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PN_THEME_VERSION', '0.1.0' );
define( 'PN_THEME_DIR', get_template_directory() );
define( 'PN_THEME_URI', get_template_directory_uri() );

require_once PN_THEME_DIR . '/inc/setup.php';
require_once PN_THEME_DIR . '/inc/enqueue.php';
require_once PN_THEME_DIR . '/inc/perf.php';
require_once PN_THEME_DIR . '/inc/helpers.php';
require_once PN_THEME_DIR . '/inc/roles/customer-care.php';
require_once PN_THEME_DIR . '/inc/cart-ajax.php';
require_once PN_THEME_DIR . '/inc/checkout-fields.php';
require_once PN_THEME_DIR . '/inc/debug/shipping-audit.php';
require_once PN_THEME_DIR . '/inc/debug/dup-orders-test.php';
require_once PN_THEME_DIR . '/inc/debug/dup-orders-log.php';
require_once PN_THEME_DIR . '/inc/checkout/pay-for-order-draft.php';
require_once PN_THEME_DIR . '/inc/checkout/cleanup-orphan-pendings.php';
require_once PN_THEME_DIR . '/inc/checkout/abandoned-recovery.php';
require_once PN_THEME_DIR . '/inc/consent/setup.php';
require_once PN_THEME_DIR . '/inc/menu/mega-menu.php';
require_once PN_THEME_DIR . '/inc/queries/products.php';
require_once PN_THEME_DIR . '/inc/catalog/filters.php';
require_once PN_THEME_DIR . '/inc/cpt/brand.php';
require_once PN_THEME_DIR . '/inc/cpt/promo.php';
require_once PN_THEME_DIR . '/inc/cpt/banner.php';
require_once PN_THEME_DIR . '/inc/cpt/popup.php';
require_once PN_THEME_DIR . '/inc/cpt/newsletter.php';
require_once PN_THEME_DIR . '/inc/product/meta.php';
require_once PN_THEME_DIR . '/inc/product/recommendations.php';
require_once PN_THEME_DIR . '/inc/product/recently-viewed.php';
require_once PN_THEME_DIR . '/inc/product/substitution.php';
require_once PN_THEME_DIR . '/inc/product/jsonld.php';
require_once PN_THEME_DIR . '/inc/rest/contact.php';
require_once PN_THEME_DIR . '/inc/rest/newsletter.php';
require_once PN_THEME_DIR . '/inc/rest/orders-export.php';
require_once PN_THEME_DIR . '/inc/queries/faq-data.php';
require_once PN_THEME_DIR . '/inc/order/view.php';
require_once PN_THEME_DIR . '/inc/order/canonical-route.php';
require_once PN_THEME_DIR . '/inc/order/geocode.php';
require_once PN_THEME_DIR . '/inc/order/tracking.php';
require_once PN_THEME_DIR . '/inc/account/setup.php';
require_once PN_THEME_DIR . '/inc/account/handlers.php';
require_once PN_THEME_DIR . '/inc/account/email-templates.php';
require_once PN_THEME_DIR . '/inc/account/track-order.php';
require_once PN_THEME_DIR . '/inc/account/wishlist.php';
require_once PN_THEME_DIR . '/inc/search/search.php';
