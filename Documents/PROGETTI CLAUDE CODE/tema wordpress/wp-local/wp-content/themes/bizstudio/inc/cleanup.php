<?php
/**
 * WordPress Head Cleanup & Security Hardening
 *
 * Removes unnecessary items from wp_head output and applies
 * basic security measures. Keeps the frontend lean and fast.
 *
 * @package BizStudio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/* ================================================================
   1. REMOVE WORDPRESS EMOJI SCRIPTS & STYLES
   ================================================================ */

add_action( 'init', function () {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );

	// Remove emoji from RSS feeds
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );

	// Remove emoji from emails
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

	// Remove emoji DNS prefetch
	add_filter( 'emoji_svg_url', '__return_false' );
} );

// Remove TinyMCE emoji plugin in admin
add_filter( 'tiny_mce_plugins', function ( $plugins ) {
	if ( is_array( $plugins ) ) {
		return array_diff( $plugins, [ 'wpemoji' ] );
	}
	return $plugins;
} );

/* ================================================================
   2. REMOVE WORDPRESS EMBED SCRIPTS
   ================================================================ */

add_action( 'wp_footer', function () {
	wp_deregister_script( 'wp-embed' );
} );

/* ================================================================
   3. REMOVE RSD (Really Simple Discovery) LINK
   ================================================================ */

remove_action( 'wp_head', 'rsd_link' );

/* ================================================================
   4. REMOVE WLWMANIFEST (Windows Live Writer) LINK
   ================================================================ */

remove_action( 'wp_head', 'wlwmanifest_link' );

/* ================================================================
   5. REMOVE WORDPRESS GENERATOR META TAG
   ================================================================

   Hides the WordPress version number from the source code.
   This is a basic security measure to prevent version-targeting. */

remove_action( 'wp_head', 'wp_generator' );

// Also remove from RSS feeds
add_filter( 'the_generator', '__return_empty_string' );

/* ================================================================
   6. DISABLE XML-RPC
   ================================================================

   XML-RPC is rarely needed for modern WordPress sites and is a
   common target for brute-force attacks. Disable it entirely.

   Note: If you use the WordPress mobile app or Jetpack, you may
   need to re-enable this. */

add_filter( 'xmlrpc_enabled', '__return_false' );

// Remove XML-RPC discovery link from head
remove_action( 'wp_head', 'rsd_link' );

// Remove X-Pingback header
add_filter( 'wp_headers', function ( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
} );

/* ================================================================
   7. REMOVE QUERY STRINGS FROM STATIC RESOURCES
   ================================================================

   Removes ?ver= query strings from CSS and JS files.
   This improves caching performance, as some CDNs and proxies
   do not cache resources with query strings.

   Note: This only runs on the frontend, not in admin. */

add_action( 'wp_enqueue_scripts', function () {
	if ( is_admin() ) {
		return;
	}

	add_filter( 'style_loader_src', 'bizstudio_remove_query_strings', 15 );
	add_filter( 'script_loader_src', 'bizstudio_remove_query_strings', 15 );
}, 1 );

/**
 * Strip the ?ver= query string from a resource URL.
 *
 * Preserves query strings for external resources (Google Fonts, CDN, etc.)
 * so only local assets are affected.
 *
 * @param string $src The resource URL.
 * @return string The cleaned URL.
 */
function bizstudio_remove_query_strings( $src ) {
	if ( ! $src ) {
		return $src;
	}

	// Only strip from local resources
	$site_url = site_url();
	if ( strpos( $src, $site_url ) === false && strpos( $src, '/' ) !== 0 ) {
		return $src;
	}

	$parts = explode( '?', $src );
	return $parts[0];
}

/* ================================================================
   8. CLEAN UP WP_HEAD OUTPUT
   ================================================================ */

// Remove shortlink
remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );

// Remove REST API link from head (it's still accessible via API)
remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );

// Remove oEmbed discovery links
remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );

// Remove adjacent posts links (prev/next) from head
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );

// Remove WordPress DNS prefetch for external emoji CDN
add_filter( 'wp_resource_hints', function ( $hints, $relation_type ) {
	if ( 'dns-prefetch' === $relation_type ) {
		$hints = array_filter( $hints, function ( $hint ) {
			// Remove emoji CDN prefetch
			if ( is_string( $hint ) && strpos( $hint, 's.w.org' ) !== false ) {
				return false;
			}
			return true;
		} );
	}
	return $hints;
}, 10, 2 );

/* ================================================================
   9. DISABLE SELF-PINGBACKS
   ================================================================

   Prevents WordPress from creating pingbacks to your own site
   when you link to your own posts. */

add_action( 'pre_ping', function ( &$links ) {
	$home = home_url();
	foreach ( $links as $i => $link ) {
		if ( strpos( $link, $home ) === 0 ) {
			unset( $links[ $i ] );
		}
	}
} );

/* ================================================================
   10. LIMIT POST REVISIONS (optional, can be overridden in wp-config)
   ================================================================

   Only apply if WP_POST_REVISIONS is not already defined in wp-config.php. */

if ( ! defined( 'WP_POST_REVISIONS' ) ) {
	define( 'WP_POST_REVISIONS', 5 );
}
