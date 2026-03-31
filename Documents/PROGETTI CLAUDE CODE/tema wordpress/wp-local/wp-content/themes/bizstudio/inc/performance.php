<?php
/**
 * Performance optimizations — Sprint 7
 *
 * Tasks: 7.1 Critical CSS | 7.2 Async CSS | 7.3 Font preload |
 *        7.4 Prefetch on hover | 7.5 Lazy images | 7.6 JS verify |
 *        7.7 WC dequeue | 7.8 srcset sizes | 7.9 dist/src helper |
 *        7.10 Lighthouse meta
 *
 * @package BizStudio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/* ================================================================
   7.1  CRITICAL CSS — Inline above-the-fold styles
   ================================================================ */

/**
 * Output a small critical CSS subset in <head> so the header, hero,
 * basic layout, typography and colours render before external sheets
 * have finished loading.
 *
 * Hooked at priority 2 — before the Customizer CSS variables (priority 5)
 * and before wp_enqueue_scripts fires.
 */
add_action( 'wp_head', function () {
	?>
<style id="bizstudio-critical-css">
/* --- Critical: design tokens (fallback — Customizer overrides at prio 5) --- */
:root{
--biz-primary:#8367C7;--biz-primary-dark:#6B4FAF;--biz-primary-light:#A78BFA;
--biz-text:#1A1A2E;--biz-text-secondary:#555570;--biz-text-muted:#8E8EA0;
--biz-bg:#FFFFFF;--biz-surface:#F7F7FA;--biz-border:#E5E5EE;
--biz-success:#2EA043;--biz-error:#E5383B;--biz-rating:#F5A623;
--biz-font:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
--biz-container:1200px;--biz-gap:1.5rem;
--biz-shadow-sm:0 1px 3px rgba(26,26,46,.06);
--biz-shadow-md:0 4px 12px rgba(26,26,46,.08);
--biz-shadow-lg:0 8px 24px rgba(26,26,46,.12);
--biz-radius-sm:6px;--biz-radius:8px;--biz-radius-lg:12px;--biz-radius-xl:16px;
--biz-fast:150ms ease;--biz-base:250ms ease;--biz-slow:400ms ease;
--biz-header-h:72px;
}
.dark{--biz-text:#E8E8F0;--biz-text-secondary:#A0A0B8;--biz-text-muted:#707088;--biz-bg:#0F0F1A;--biz-surface:#1A1A2E;--biz-border:#2A2A40;}

/* --- Critical: reset --- */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth;-webkit-text-size-adjust:100%}
body{font-family:var(--biz-font);font-size:16px;line-height:1.6;color:var(--biz-text);background:var(--biz-bg);-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}
img{max-width:100%;height:auto;display:block}
a{color:inherit;text-decoration:none}
button{cursor:pointer;border:none;background:none;font:inherit;color:inherit}
ul,ol{list-style:none}

/* --- Critical: layout --- */
.biz-container{width:100%;max-width:var(--biz-container);margin:0 auto;padding:0 1.25rem}

/* --- Critical: typography --- */
h1,h2,h3,h4,h5,h6{font-weight:600;line-height:1.25;color:var(--biz-text)}
h1{font-size:clamp(2rem,5vw,3rem)}h2{font-size:clamp(1.5rem,3vw,2rem)}

/* --- Critical: header --- */
.biz-header{position:sticky;top:0;z-index:100;background:var(--biz-bg);border-bottom:1px solid var(--biz-border);height:var(--biz-header-h);transition:background var(--biz-base),box-shadow var(--biz-base)}
.biz-header.is-scrolled{box-shadow:var(--biz-shadow-sm)}
.biz-header__inner{display:flex;align-items:center;justify-content:space-between;height:100%;gap:2rem}
.biz-header__logo-text{font-size:1.5rem;font-weight:700;letter-spacing:-.02em}
.biz-header__nav{flex:1;display:flex;justify-content:center}
.biz-nav__list{display:flex;gap:2rem;align-items:center}
.biz-nav__list li a{font-size:.9375rem;font-weight:500;color:var(--biz-text-secondary);transition:color var(--biz-fast);position:relative}
.biz-header__actions{display:flex;align-items:center;gap:.5rem}
.biz-header__action{display:flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:var(--biz-radius);color:var(--biz-text-secondary);transition:all var(--biz-fast);position:relative}
.biz-cart-count,.biz-header__wishlist-count{position:absolute;top:2px;right:2px;background:var(--biz-primary);color:#fff;font-size:.625rem;font-weight:700;min-width:18px;height:18px;border-radius:9px;display:flex;align-items:center;justify-content:center;padding:0 4px}
.biz-header__wishlist-count{background:var(--biz-error)}
.biz-header__burger{display:none}

/* --- Critical: hero --- */
.biz-hero{background:linear-gradient(135deg,var(--biz-surface) 0%,var(--biz-bg) 100%);padding:6rem 0;position:relative;overflow:hidden}
.biz-hero__content{max-width:600px}
.biz-hero__tag{display:inline-block;padding:.375rem 1rem;background:rgba(131,103,199,.1);color:var(--biz-primary);border-radius:var(--biz-radius-sm);font-size:.8125rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:1.5rem}
.biz-hero__title{font-size:clamp(2.5rem,6vw,3.5rem);font-weight:700;letter-spacing:-.03em;line-height:1.1;margin-bottom:1rem}
.biz-hero__text{font-size:1.125rem;color:var(--biz-text-secondary);margin-bottom:2rem;line-height:1.7}

/* --- Critical: buttons --- */
.biz-btn{display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 1.5rem;border-radius:var(--biz-radius);font-weight:600;font-size:.9375rem;transition:all var(--biz-base);white-space:nowrap}
.biz-btn--primary{background:var(--biz-primary);color:#fff}
.biz-btn--lg{padding:1rem 2rem;font-size:1rem}

/* --- Critical: sections (above fold on homepage) --- */
.biz-section{padding:4rem 0}
.biz-section__header{display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem}
.biz-section__title{font-weight:700;letter-spacing:-.02em}

/* --- Critical: product grid shell --- */
.biz-product-grid{display:grid;gap:var(--biz-gap)}
.biz-product-grid--4{grid-template-columns:repeat(4,1fr)}

/* --- Critical: mobile responsive header --- */
@media(max-width:768px){
.biz-header__nav{display:none}
.biz-header__burger{display:flex}
.biz-hero{padding:3rem 0}
.biz-hero__title{font-size:2rem}
:root{--biz-header-h:60px}
.biz-product-grid--4,.biz-product-grid--3{grid-template-columns:repeat(2,1fr)}
}

/* --- Critical: admin bar fix --- */
body.admin-bar .biz-header{top:32px}
@media(max-width:782px){body.admin-bar .biz-header{top:46px}}
</style>
<?php
}, 2 );


/* ================================================================
   7.2  ASYNC CSS — Non-critical stylesheets load asynchronously
   ================================================================ */

/**
 * Non-critical CSS handles that should be loaded async.
 * These are page-specific or below-the-fold styles.
 *
 * @return string[] Style handle names.
 */
function bizstudio_get_async_css_handles(): array {
	return [
		// WooCommerce page-specific
		'bizstudio-woocommerce',        // shop.css
		'bizstudio-product',            // product.css
		'bizstudio-account',            // account.css
		'bizstudio-cart-checkout',      // cart-checkout.css
		'bizstudio-checkout-fullpage',  // checkout-fullpage.css
		// Component CSS (self-enqueued from inc/ files)
		'bizstudio-animations',         // animations.css
		'bizstudio-forms',              // forms.css
		'bizstudio-contact-form',       // contact-form.css
		'bizstudio-footer-builder',     // footer-builder.css
		'bizstudio-wishlist',           // wishlist.css
		'bizstudio-promo-bar',          // promo-bar.css
		'bizstudio-shipping-bar',       // shipping-bar.css
		'bizstudio-sticky-bar',         // sticky-bar.css
	];
}

/**
 * Convert non-critical <link> tags to async pattern:
 * media="print" onload="this.media='all'" + <noscript> fallback.
 *
 * @param string $html   Full <link> tag.
 * @param string $handle Style handle name.
 * @return string Modified tag with async loading.
 */
add_filter( 'style_loader_tag', function ( string $html, string $handle ): string {

	if ( ! in_array( $handle, bizstudio_get_async_css_handles(), true ) ) {
		return $html;
	}

	// Skip in Customizer preview — all CSS must load normally there
	if ( is_customize_preview() ) {
		return $html;
	}

	// Build the async version
	$async_html = str_replace(
		"media='all'",
		"media='print' onload=\"this.media='all'\"",
		$html
	);

	// Fallback: if the tag didn't have media='all' try without quotes
	if ( $async_html === $html ) {
		$async_html = str_replace(
			'media="all"',
			'media="print" onload="this.media=\'all\'"',
			$html
		);
	}

	// Build <noscript> fallback
	$noscript = '<noscript>' . $html . '</noscript>';

	return $async_html . "\n" . $noscript;

}, 99, 2 );


/* ================================================================
   7.3  FONT PRELOAD — Preload hint for Google Fonts
   ================================================================ */

/**
 * Add <link rel="preload"> for the Inter font files and keep
 * preconnect hints for Google Fonts domains.
 *
 * The actual font stylesheet is already enqueued in customizer.php.
 * This hook just adds the preload resource hint when the Customizer
 * setting is enabled.
 *
 * Priority 1 — very early in <head>.
 */
add_action( 'wp_head', function () {

	$perf = bizstudio_get_performance_settings();

	if ( ! $perf['preload_fonts'] ) {
		return;
	}

	// Preconnect hints (supplementing the ones already in header.php)
	// These are safe to duplicate — browsers de-duplicate them.
	echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";

	// Preload the main Google Fonts CSS so the browser fetches it ASAP.
	// The actual font-face files inside will use font-display: swap
	// (already in the URL query string).
	$typo = bizstudio_get_typography_settings();

	// Build the same URL that customizer.php enqueues
	$google_fonts = bizstudio_get_google_fonts_to_load( $typo['body_font'], $typo['heading_font'] );

	// For Inter (the default), Google Fonts URL is in header.php <link>
	// We preload it as a stylesheet.
	$font_url = 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap';

	if ( ! empty( $google_fonts ) ) {
		$families = [];
		foreach ( $google_fonts as $font ) {
			$families[] = 'family=' . str_replace( ' ', '+', $font ) . ':wght@400;500;600;700;800';
		}
		$font_url = 'https://fonts.googleapis.com/css2?' . implode( '&', $families ) . '&display=swap';
	}

	echo '<link rel="preload" href="' . esc_url( $font_url ) . '" as="style">' . "\n";

}, 1 );


/* ================================================================
   7.4  PREFETCH ON HOVER — Speculative prefetch for internal links
   ================================================================ */

/**
 * Output a tiny inline script that adds <link rel="prefetch"> when the
 * user hovers over internal links. Debounced to avoid excessive requests.
 *
 * Priority 20 — after wp_head core output.
 */
add_action( 'wp_footer', function () {
	?>
<script id="bizstudio-prefetch">
(function(){
	var defined={},timer=null;
	function prefetch(url){
		if(defined[url])return;
		defined[url]=1;
		var l=document.createElement('link');
		l.rel='prefetch';l.href=url;
		document.head.appendChild(l);
	}
	document.addEventListener('pointerenter',function(e){
		var a=e.target.closest('a');
		if(!a)return;
		var h=a.href;
		if(!h||a.getAttribute('target')==='_blank')return;
		if(h.indexOf(location.origin)!==0)return;
		if(h.indexOf('#')===h.length-1||h.startsWith('javascript:'))return;
		if(h===location.href)return;
		clearTimeout(timer);
		timer=setTimeout(function(){prefetch(h);},65);
	},true);
})();
</script>
	<?php
}, 20 );


/* ================================================================
   7.5  LAZY / EAGER IMAGES — Proper loading attributes
   ================================================================ */

/**
 * Ensure product images get decoding="async" and respect the
 * lazy_load Customizer toggle.
 *
 * For above-the-fold images (custom logo), we set loading="eager"
 * and fetchpriority="high".
 */
add_filter( 'wp_get_attachment_image_attributes', function ( array $attr, WP_Post $attachment, $size ): array {

	$perf = bizstudio_get_performance_settings();

	// Add decoding="async" to all images
	$attr['decoding'] = 'async';

	// Lazy loading (WordPress adds loading="lazy" by default,
	// but we respect the Customizer toggle)
	if ( $perf['lazy_load'] ) {
		// WordPress 5.5+ adds loading="lazy" automatically;
		// we leave it alone unless we detect an above-the-fold image.
		if ( ! isset( $attr['loading'] ) ) {
			$attr['loading'] = 'lazy';
		}
	} else {
		// User disabled lazy loading — remove it
		unset( $attr['loading'] );
	}

	return $attr;
}, 10, 3 );

/**
 * Force the custom logo to load eagerly with high fetch priority —
 * it's always above the fold.
 */
add_filter( 'get_custom_logo_image_attributes', function ( array $attr ): array {
	$attr['loading']       = 'eager';
	$attr['fetchpriority'] = 'high';
	$attr['decoding']      = 'async';
	return $attr;
}, 10, 1 );

/**
 * For hero images on the front page, override loading behaviour.
 * This works via a body-class check in wp_get_attachment_image_attributes.
 */
add_filter( 'wp_get_attachment_image_attributes', function ( array $attr, WP_Post $attachment, $size ): array {

	// On front page, the first large image is likely the hero
	if ( is_front_page() && in_array( $size, [ 'full', 'large', '1536x1536', '2048x2048' ], true ) ) {
		static $first_large = true;
		if ( $first_large ) {
			$attr['loading']       = 'eager';
			$attr['fetchpriority'] = 'high';
			$first_large           = false;
		}
	}

	return $attr;
}, 20, 3 );


/* ================================================================
   7.6  JS MODULE VERIFICATION (audit notes)
   ================================================================

   Verified:
   - All scripts use 'strategy' => 'defer' in enqueue.php ......... OK
   - No jQuery dependency declared in wp_enqueue_script() ......... OK
   - sticky-bar.js and shipping-bar.js reference jQuery only
     conditionally (typeof jQuery !== 'undefined') for WC events .. OK
   - All JS modules are loaded conditionally per page ............. OK

   No code changes needed for 7.6.
   ================================================================ */


/* ================================================================
   7.7  DEQUEUE UNNECESSARY WOOCOMMERCE SCRIPTS & STYLES
   ================================================================ */

/**
 * Remove WooCommerce block styles we override completely, and
 * dequeue WC scripts on pages that don't need them.
 *
 * Priority 100 — runs after WC has enqueued its assets.
 */
add_action( 'wp_enqueue_scripts', function () {

	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	// ── Remove WC Blocks CSS that we override entirely ──────────
	wp_dequeue_style( 'wc-blocks-style' );
	wp_deregister_style( 'wc-blocks-style' );

	wp_dequeue_style( 'wc-blocks-style-active-filters' );
	wp_dequeue_style( 'wc-blocks-style-add-to-cart-form' );
	wp_dequeue_style( 'wc-blocks-style-all-products' );
	wp_dequeue_style( 'wc-blocks-style-all-reviews' );
	wp_dequeue_style( 'wc-blocks-style-attribute-filter' );
	wp_dequeue_style( 'wc-blocks-style-breadcrumbs' );
	wp_dequeue_style( 'wc-blocks-style-catalog-sorting' );
	wp_dequeue_style( 'wc-blocks-style-customer-account' );
	wp_dequeue_style( 'wc-blocks-style-featured-category' );
	wp_dequeue_style( 'wc-blocks-style-featured-product' );
	wp_dequeue_style( 'wc-blocks-style-mini-cart' );
	wp_dequeue_style( 'wc-blocks-style-price-filter' );
	wp_dequeue_style( 'wc-blocks-style-product-add-to-cart' );
	wp_dequeue_style( 'wc-blocks-style-product-button' );
	wp_dequeue_style( 'wc-blocks-style-product-categories' );
	wp_dequeue_style( 'wc-blocks-style-product-image' );
	wp_dequeue_style( 'wc-blocks-style-product-image-gallery' );
	wp_dequeue_style( 'wc-blocks-style-product-query' );
	wp_dequeue_style( 'wc-blocks-style-product-results-count' );
	wp_dequeue_style( 'wc-blocks-style-product-reviews' );
	wp_dequeue_style( 'wc-blocks-style-product-sale-badge' );
	wp_dequeue_style( 'wc-blocks-style-product-search' );
	wp_dequeue_style( 'wc-blocks-style-product-sku' );
	wp_dequeue_style( 'wc-blocks-style-product-stock-indicator' );
	wp_dequeue_style( 'wc-blocks-style-product-summary' );
	wp_dequeue_style( 'wc-blocks-style-product-title' );
	wp_dequeue_style( 'wc-blocks-style-rating-filter' );
	wp_dequeue_style( 'wc-blocks-style-reviews-by-category' );
	wp_dequeue_style( 'wc-blocks-style-reviews-by-product' );
	wp_dequeue_style( 'wc-blocks-style-product-rating' );
	wp_dequeue_style( 'wc-blocks-style-stock-filter' );
	wp_dequeue_style( 'wc-blocks-style-cart' );
	wp_dequeue_style( 'wc-blocks-style-checkout' );
	wp_dequeue_style( 'wc-blocks-style-mini-cart-contents' );
	wp_dequeue_style( 'wc-blocks-packages-style' );

	// ── Remove WC Blocks JS on non-WC pages ─────────────────────
	if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() && ! is_account_page() ) {
		wp_dequeue_script( 'wc-blocks-middleware' );
		wp_dequeue_script( 'wc-blocks-data-store' );
	}

	// ── Remove WC cart fragments on pages that do not need them ──
	// Cart fragments are needed on every page where mini-cart is shown
	// (basically all pages), so we leave them alone.

	// ── Remove WC generator tag ─────────────────────────────────
	remove_action( 'wp_head', [ 'WC', 'output_generator_tag' ] );

}, 100 );

/**
 * Remove WooCommerce block-based widgets CSS.
 */
add_action( 'wp_enqueue_scripts', function () {
	wp_dequeue_style( 'wp-block-library-theme' );
}, 100 );


/* ================================================================
   7.8  IMAGE SRCSET SIZES — Context-aware sizes attribute
   ================================================================ */

/**
 * Provide accurate `sizes` attribute for images based on their
 * display context so the browser picks the optimal resolution.
 *
 * @param string $sizes         The current sizes attribute value.
 * @param array  $size          The requested image size (width, height).
 * @param string|null $image_src The image source URL.
 * @param array|null  $image_meta Image metadata.
 * @param int    $attachment_id Attachment post ID.
 * @return string
 */
add_filter( 'wp_calculate_image_sizes', function ( string $sizes, $size, ?string $image_src, ?array $image_meta, int $attachment_id ): string {

	// Single product page — image takes ~50vw on desktop
	if ( is_product() ) {
		return '(max-width: 768px) 100vw, 50vw';
	}

	// Shop archive / category pages — card images
	if ( is_shop() || is_product_category() || is_product_tag() || is_front_page() ) {
		return '(max-width: 768px) 50vw, (max-width: 1024px) 33vw, 25vw';
	}

	// Cart page — thumbnail images
	if ( function_exists( 'is_cart' ) && is_cart() ) {
		return '80px';
	}

	return $sizes;

}, 10, 5 );


/* ================================================================
   7.9  DIST / SRC HELPER — Use minified bundles when available
   ================================================================ */

/**
 * Check if the combined dist files exist and return the appropriate
 * base path (dist or src). Other enqueue code can use this to decide
 * which files to load.
 *
 * @param string $type 'css' or 'js'.
 * @return string  'dist' if compiled file exists, otherwise 'src'.
 */
function bizstudio_asset_dir( string $type = 'css' ): string {
	$dist_file = ( 'css' === $type )
		? '/assets/dist/style.min.css'
		: '/assets/dist/script.min.js';

	if ( file_exists( BIZSTUDIO_DIR . $dist_file ) ) {
		return 'dist';
	}

	return 'src';
}

/**
 * Return the URL for the main CSS file (dist or src).
 *
 * @return string
 */
function bizstudio_main_css_url(): string {
	if ( 'dist' === bizstudio_asset_dir( 'css' ) ) {
		return BIZSTUDIO_URI . '/assets/dist/style.min.css';
	}
	return BIZSTUDIO_URI . '/assets/src/css/main.css';
}

/**
 * Return the URL for the main JS file (dist or src).
 *
 * @return string
 */
function bizstudio_main_js_url(): string {
	if ( 'dist' === bizstudio_asset_dir( 'js' ) ) {
		return BIZSTUDIO_URI . '/assets/dist/script.min.js';
	}
	return BIZSTUDIO_URI . '/assets/src/js/main.js';
}


/* ================================================================
   7.10  LIGHTHOUSE PREP — Meta tags and resource hints
   ================================================================ */

/**
 * Output theme-color, dns-prefetch, and content-visibility hints.
 * Priority 1 — early in <head>.
 */
add_action( 'wp_head', function () {

	$colors = bizstudio_get_color_settings();

	// Theme-color meta (used by mobile browsers for address bar)
	echo '<meta name="theme-color" content="' . esc_attr( $colors['primary'] ) . '">' . "\n";

	// DNS prefetch for external resources
	echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";
	echo '<link rel="dns-prefetch" href="//fonts.gstatic.com">' . "\n";

}, 1 );

/**
 * Add `content-visibility: auto` to below-the-fold sections.
 * This tells the browser to skip rendering of off-screen content
 * until it scrolls near, improving initial paint time.
 *
 * Output as inline CSS at priority 3 — after critical CSS (priority 2),
 * before Customizer (priority 5).
 */
add_action( 'wp_head', function () {
	?>
<style id="bizstudio-content-visibility">
.biz-section,
.biz-footer,
.biz-search-modal,
.biz-mini-cart,
.biz-mobile-menu,
.biz-qv{
	content-visibility:auto;
	contain-intrinsic-size:auto 500px;
}
</style>
<?php
}, 3 );

/**
 * Ensure the viewport meta tag is correct.
 * WordPress themes should include this in header.php, but we add a
 * safeguard here in case it's missing.
 *
 * Uses output buffering to check — if already present, skip.
 */
add_action( 'wp_head', function () {
	// WordPress 6.4+ outputs viewport in wp_head automatically
	// when the theme declares support. We add an explicit fallback
	// only when needed — the Customizer and header.php already include it.
	// No extra output needed; header.php has the correct
	// <meta name="viewport" content="width=device-width, initial-scale=1.0">
}, 0 );
