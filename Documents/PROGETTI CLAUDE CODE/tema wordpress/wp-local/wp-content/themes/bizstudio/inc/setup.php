<?php
/**
 * Theme Setup
 *
 * @package BizStudio
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	// Text domain
	load_theme_textdomain( 'bizstudio', BIZSTUDIO_DIR . '/languages' );

	// Theme supports
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [
		'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script',
	] );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'custom-logo', [
		'height'      => 60,
		'width'       => 200,
		'flex-height' => true,
		'flex-width'  => true,
	] );

	// Image sizes
	add_image_size( 'bizstudio-product-card', 400, 500, true );
	add_image_size( 'bizstudio-product-single', 800, 1000, false );
	add_image_size( 'bizstudio-hero', 1920, 800, true );

	// Menus
	register_nav_menus( [
		'primary'  => __( 'Menu Principale', 'bizstudio' ),
		'mobile'   => __( 'Menu Mobile', 'bizstudio' ),
		'footer'   => __( 'Menu Footer', 'bizstudio' ),
	] );
} );

// Widget areas
add_action( 'widgets_init', function () {
	register_sidebar( [
		'name'          => __( 'Shop Sidebar', 'bizstudio' ),
		'id'            => 'shop-sidebar',
		'description'   => __( 'Sidebar della pagina shop', 'bizstudio' ),
		'before_widget' => '<div id="%1$s" class="biz-widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="biz-widget__title">',
		'after_title'   => '</h3>',
	] );

	$footer_cols = [ 'Footer Colonna 1', 'Footer Colonna 2', 'Footer Colonna 3' ];
	foreach ( $footer_cols as $i => $name ) {
		register_sidebar( [
			'name'          => $name,
			'id'            => 'footer-' . ( $i + 1 ),
			'before_widget' => '<div id="%1$s" class="biz-footer-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="biz-footer-widget__title">',
			'after_title'   => '</h4>',
		] );
	}
} );
