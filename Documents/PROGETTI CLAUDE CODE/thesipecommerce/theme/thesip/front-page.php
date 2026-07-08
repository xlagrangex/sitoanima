<?php
/**
 * Home page — orchestratore.
 *
 * Layout core (sezioni fisse) + 8 zone banner popolate via CPT pn_banner.
 *
 *   [hero]
 *     ↓ zone 1: home_after_hero
 *   [categorie]
 *     ↓ zone 2: home_after_categories
 *   [il set e i bundle]
 *     ↓ zone 3: home_after_farmaci
 *   [progetto arte e scienza]
 *     ↓ zone 4: home_after_fiducia
 *     ↓ zone 5: home_after_integratori
 *   [esplora per categoria]
 *     ↓ zone 6: home_after_explore
 *   [pharmacist CTA]
 *     ↓ zone 7: home_after_pharmacist
 *   [faq]
 *     ↓ zone 8: home_before_newsletter
 *   [newsletter]
 *
 * I banner si gestiscono da WP Admin → Banner. Ogni banner ha:
 * immagine desktop+mobile, link (URL o pagina promo), zona, ordine, schedule.
 *
 * La sezione prodotto "Il set e i bundle" resta curata da codice
 * (lista SKU nel get_template_part qui sotto).
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<?php get_template_part( 'template-parts/home/hero' ); ?>

<?php do_action( 'pn_banner_zone', 'home_after_hero' ); ?>

<?php get_template_part( 'template-parts/home/category-grid' ); ?>

<?php do_action( 'pn_banner_zone', 'home_after_categories' ); ?>

<?php
get_template_part(
	'template-parts/home/product-section',
	null,
	array(
		'title'      => __( 'Il set e i bundle', 'pharmanow' ),
		'subtitle'   => __( 'Colleziona il mare o regalalo: scegli il set che fa per te.', 'pharmanow' ),
		'view_all'   => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalogo' ),
		'limit'      => 4,
		'cols'       => 4,
		'icon'       => 'package',
		'icon_color' => 'text-blue-600',
		'bg_color'   => '#F0FDFA',
		'skus'       => array(
			'THESIP-SET',
			'THESIP-SET-STICKERS',
			'THESIP-DOUBLE',
			'THESIP-DELUXE',
		),
	)
);
?>

<?php do_action( 'pn_banner_zone', 'home_after_farmaci' ); ?>

<?php get_template_part( 'template-parts/home/farmacia-fiducia' ); ?>

<?php do_action( 'pn_banner_zone', 'home_after_fiducia' ); ?>

<?php get_template_part( 'template-parts/home/promo-banners' ); ?>

<?php do_action( 'pn_banner_zone', 'home_after_integratori' ); ?>

<?php do_action( 'pn_banner_zone', 'home_after_explore' ); ?>

<?php get_template_part( 'template-parts/home/pharmacist-cta' ); ?>

<?php do_action( 'pn_banner_zone', 'home_after_pharmacist' ); ?>

<?php get_template_part( 'template-parts/home/faq-home' ); ?>

<?php do_action( 'pn_banner_zone', 'home_before_newsletter' ); ?>

<?php get_template_part( 'template-parts/home/newsletter' ); ?>

<?php
get_footer();
