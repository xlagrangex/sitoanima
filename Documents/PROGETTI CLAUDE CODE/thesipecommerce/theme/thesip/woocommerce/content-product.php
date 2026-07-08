<?php
/**
 * The template for displaying product content within loops.
 * Override Woo content-product.php — delega al partial template-parts/shop/product-card.php.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( ! is_a( $product, 'WC_Product' ) ) {
	return;
}

get_template_part( 'template-parts/shop/product-card', null, array( 'product' => $product ) );
