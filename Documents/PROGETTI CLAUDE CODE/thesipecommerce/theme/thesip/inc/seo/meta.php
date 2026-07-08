<?php
/**
 * Meta description + Open Graph / Twitter Card.
 *
 * Il tema non usa plugin SEO: qui emettiamo i tag essenziali per
 * condivisioni social e snippet motori di ricerca.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Descrizione di default del sito.
 */
function pn_seo_default_description(): string {
	return __( 'The SIP — The Sea In your Pocket: 31 flashcard illustrate di biologia marina, create da 25 artisti e 2 biologi marini a Napoli. Colleziona il mare, una carta alla volta.', 'pharmanow' );
}

/**
 * Descrizione per la vista corrente.
 */
function pn_seo_description(): string {
	if ( is_front_page() ) {
		return pn_seo_default_description();
	}

	if ( function_exists( 'is_product' ) && is_product() ) {
		$post = get_post();
		if ( $post && $post->post_excerpt ) {
			return wp_strip_all_tags( $post->post_excerpt );
		}
	}

	if ( is_singular() ) {
		$post = get_post();
		if ( $post ) {
			if ( $post->post_excerpt ) {
				return wp_strip_all_tags( $post->post_excerpt );
			}
			$text = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
			if ( $text ) {
				return wp_trim_words( $text, 30, '…' );
			}
		}
	}

	if ( is_tax( 'product_cat' ) ) {
		$desc = term_description();
		if ( $desc ) {
			return wp_strip_all_tags( $desc );
		}
	}

	return pn_seo_default_description();
}

/**
 * Immagine OG per la vista corrente.
 */
function pn_seo_image(): string {
	if ( is_singular() && has_post_thumbnail() ) {
		$src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
		if ( $src ) {
			return $src[0];
		}
	}
	return pn_asset( 'images/thesip/hero-cards.jpg' );
}

add_action(
	'wp_head',
	function () {
		$desc  = esc_attr( pn_seo_description() );
		$image = esc_url( pn_seo_image() );
		$title = esc_attr( wp_get_document_title() );
		$url   = esc_url( home_url( add_query_arg( array() ) ) );
		?>
		<meta name="description" content="<?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
		<meta property="og:site_name" content="The SIP — The Sea In your Pocket">
		<meta property="og:type" content="<?php echo is_singular() && function_exists( 'is_product' ) && is_product() ? 'product' : 'website'; ?>">
		<meta property="og:title" content="<?php echo $title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
		<meta property="og:description" content="<?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
		<meta property="og:url" content="<?php echo $url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
		<meta property="og:image" content="<?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
		<meta property="og:locale" content="it_IT">
		<meta name="twitter:card" content="summary_large_image">
		<meta name="twitter:title" content="<?php echo $title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
		<meta name="twitter:description" content="<?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
		<meta name="twitter:image" content="<?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
		<?php
	},
	5
);
