<?php
/**
 * Customizer — Colori, Tipografia, Shop, Performance
 *
 * Registers 4 global-settings sections inside the existing
 * BizStudio panel and outputs the corresponding CSS variables.
 *
 * @package BizStudio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/* ================================================================
   1. CUSTOMIZER REGISTRATION
   ================================================================ */

add_action( 'customize_register', function ( WP_Customize_Manager $wp_customize ) {

	/* ────────────────────────────────────────────────────────
	   Section: Colori (priority 30)
	   ──────────────────────────────────────────────────────── */

	$wp_customize->add_section( 'bizstudio_colors', [
		'title'    => __( 'Colori', 'bizstudio' ),
		'panel'    => 'bizstudio_panel',
		'priority' => 30,
	] );

	$color_settings = [
		'bizstudio_color_primary'        => [ __( 'Colore primario', 'bizstudio' ),       '#8367C7' ],
		'bizstudio_color_primary_dark'   => [ __( 'Primario scuro', 'bizstudio' ),        '#6B4FAF' ],
		'bizstudio_color_text'           => [ __( 'Colore testo', 'bizstudio' ),          '#1A1A2E' ],
		'bizstudio_color_text_secondary' => [ __( 'Testo secondario', 'bizstudio' ),      '#555570' ],
		'bizstudio_color_bg'             => [ __( 'Colore di sfondo', 'bizstudio' ),      '#FFFFFF' ],
		'bizstudio_color_surface'        => [ __( 'Colore superficie', 'bizstudio' ),     '#F7F7FA' ],
		'bizstudio_color_border'         => [ __( 'Colore bordo', 'bizstudio' ),          '#E5E5EE' ],
		'bizstudio_color_success'        => [ __( 'Colore successo', 'bizstudio' ),       '#2EA043' ],
		'bizstudio_color_error'          => [ __( 'Colore errore', 'bizstudio' ),         '#E5383B' ],
		'bizstudio_color_rating'         => [ __( 'Colore valutazione', 'bizstudio' ),    '#F5A623' ],
	];

	foreach ( $color_settings as $id => [ $label, $default ] ) {
		$wp_customize->add_setting( $id, [
			'default'           => $default,
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		] );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $id, [
			'label'   => $label,
			'section' => 'bizstudio_colors',
		] ) );
	}

	/* ────────────────────────────────────────────────────────
	   Section: Tipografia (priority 35)
	   ──────────────────────────────────────────────────────── */

	$wp_customize->add_section( 'bizstudio_typography', [
		'title'    => __( 'Tipografia', 'bizstudio' ),
		'panel'    => 'bizstudio_panel',
		'priority' => 35,
	] );

	$font_choices = [
		'Inter'     => 'Inter',
		'System'    => 'System (default di sistema)',
		'Roboto'    => 'Roboto',
		'Open Sans' => 'Open Sans',
		'Lato'      => 'Lato',
		'Poppins'   => 'Poppins',
		'Nunito'    => 'Nunito',
	];

	$heading_font_choices = array_merge(
		[ 'same' => __( 'Uguale al corpo', 'bizstudio' ) ],
		$font_choices
	);

	// Body Font Family
	$wp_customize->add_setting( 'bizstudio_typo_body_font', [
		'default'           => 'Inter',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'postMessage',
	] );

	$wp_customize->add_control( 'bizstudio_typo_body_font', [
		'label'   => __( 'Font corpo', 'bizstudio' ),
		'section' => 'bizstudio_typography',
		'type'    => 'select',
		'choices' => $font_choices,
	] );

	// Heading Font Family
	$wp_customize->add_setting( 'bizstudio_typo_heading_font', [
		'default'           => 'same',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'postMessage',
	] );

	$wp_customize->add_control( 'bizstudio_typo_heading_font', [
		'label'   => __( 'Font titoli', 'bizstudio' ),
		'section' => 'bizstudio_typography',
		'type'    => 'select',
		'choices' => $heading_font_choices,
	] );

	// Base Font Size
	$wp_customize->add_setting( 'bizstudio_typo_font_size', [
		'default'           => 16,
		'sanitize_callback' => 'absint',
		'transport'         => 'postMessage',
	] );

	$wp_customize->add_control( 'bizstudio_typo_font_size', [
		'label'       => __( 'Dimensione font base (px)', 'bizstudio' ),
		'section'     => 'bizstudio_typography',
		'type'        => 'number',
		'input_attrs' => [ 'min' => 14, 'max' => 20, 'step' => 1 ],
	] );

	// Body Line Height
	$wp_customize->add_setting( 'bizstudio_typo_line_height', [
		'default'           => '1.6',
		'sanitize_callback' => 'bizstudio_sanitize_float',
		'transport'         => 'postMessage',
	] );

	$wp_customize->add_control( 'bizstudio_typo_line_height', [
		'label'       => __( 'Altezza riga corpo', 'bizstudio' ),
		'section'     => 'bizstudio_typography',
		'type'        => 'number',
		'input_attrs' => [ 'min' => 1.2, 'max' => 2, 'step' => 0.1 ],
	] );

	// Heading Font Weight
	$wp_customize->add_setting( 'bizstudio_typo_heading_weight', [
		'default'           => '600',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'postMessage',
	] );

	$wp_customize->add_control( 'bizstudio_typo_heading_weight', [
		'label'   => __( 'Peso font titoli', 'bizstudio' ),
		'section' => 'bizstudio_typography',
		'type'    => 'select',
		'choices' => [
			'400' => '400 — Regular',
			'500' => '500 — Medium',
			'600' => '600 — Semi Bold',
			'700' => '700 — Bold',
			'800' => '800 — Extra Bold',
		],
	] );

	/* ────────────────────────────────────────────────────────
	   Section: Shop (priority 40)
	   ──────────────────────────────────────────────────────── */

	$wp_customize->add_section( 'bizstudio_shop', [
		'title'    => __( 'Shop', 'bizstudio' ),
		'panel'    => 'bizstudio_panel',
		'priority' => 40,
	] );

	// Products per Page
	$wp_customize->add_setting( 'bizstudio_shop_per_page', [
		'default'           => 12,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	] );

	$wp_customize->add_control( 'bizstudio_shop_per_page', [
		'label'       => __( 'Prodotti per pagina', 'bizstudio' ),
		'section'     => 'bizstudio_shop',
		'type'        => 'number',
		'input_attrs' => [ 'min' => 4, 'max' => 48, 'step' => 1 ],
	] );

	// Products per Row
	$wp_customize->add_setting( 'bizstudio_shop_columns', [
		'default'           => '4',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	] );

	$wp_customize->add_control( 'bizstudio_shop_columns', [
		'label'   => __( 'Prodotti per riga', 'bizstudio' ),
		'section' => 'bizstudio_shop',
		'type'    => 'select',
		'choices' => [
			'2' => '2',
			'3' => '3',
			'4' => '4',
			'5' => '5',
		],
	] );

	// Product Card Style
	$wp_customize->add_setting( 'bizstudio_shop_card_style', [
		'default'           => 'default',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	] );

	$wp_customize->add_control( 'bizstudio_shop_card_style', [
		'label'   => __( 'Stile scheda prodotto', 'bizstudio' ),
		'section' => 'bizstudio_shop',
		'type'    => 'select',
		'choices' => [
			'default'  => __( 'Predefinito', 'bizstudio' ),
			'minimal'  => __( 'Minimale', 'bizstudio' ),
			'bordered' => __( 'Con bordo', 'bizstudio' ),
		],
	] );

	// Show Rating on Cards
	$wp_customize->add_setting( 'bizstudio_shop_show_rating', [
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );

	$wp_customize->add_control( 'bizstudio_shop_show_rating', [
		'label'   => __( 'Mostra valutazione nelle schede', 'bizstudio' ),
		'section' => 'bizstudio_shop',
		'type'    => 'checkbox',
	] );

	// Show Category on Cards
	$wp_customize->add_setting( 'bizstudio_shop_show_category', [
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );

	$wp_customize->add_control( 'bizstudio_shop_show_category', [
		'label'   => __( 'Mostra categoria nelle schede', 'bizstudio' ),
		'section' => 'bizstudio_shop',
		'type'    => 'checkbox',
	] );

	// Show Quick View Button
	$wp_customize->add_setting( 'bizstudio_shop_show_quick_view', [
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );

	$wp_customize->add_control( 'bizstudio_shop_show_quick_view', [
		'label'   => __( 'Mostra pulsante vista rapida', 'bizstudio' ),
		'section' => 'bizstudio_shop',
		'type'    => 'checkbox',
	] );

	// Show Wishlist Button
	$wp_customize->add_setting( 'bizstudio_shop_show_wishlist', [
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );

	$wp_customize->add_control( 'bizstudio_shop_show_wishlist', [
		'label'   => __( 'Mostra pulsante lista desideri', 'bizstudio' ),
		'section' => 'bizstudio_shop',
		'type'    => 'checkbox',
	] );

	// Sale Badge Style
	$wp_customize->add_setting( 'bizstudio_shop_sale_badge', [
		'default'           => 'percentage',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	] );

	$wp_customize->add_control( 'bizstudio_shop_sale_badge', [
		'label'   => __( 'Stile badge sconto', 'bizstudio' ),
		'section' => 'bizstudio_shop',
		'type'    => 'select',
		'choices' => [
			'percentage' => __( 'Percentuale', 'bizstudio' ),
			'text'       => __( 'Testo', 'bizstudio' ),
			'both'       => __( 'Entrambi', 'bizstudio' ),
		],
	] );

	/* ────────────────────────────────────────────────────────
	   Section: Performance (priority 45)
	   ──────────────────────────────────────────────────────── */

	$wp_customize->add_section( 'bizstudio_performance', [
		'title'    => __( 'Performance', 'bizstudio' ),
		'panel'    => 'bizstudio_panel',
		'priority' => 45,
	] );

	// Scroll Reveal Animations
	$wp_customize->add_setting( 'bizstudio_perf_scroll_reveal', [
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );

	$wp_customize->add_control( 'bizstudio_perf_scroll_reveal', [
		'label'       => __( 'Abilita animazioni scroll reveal', 'bizstudio' ),
		'description' => __( 'Gli elementi appaiono con animazione al passaggio dello scroll.', 'bizstudio' ),
		'section'     => 'bizstudio_performance',
		'type'        => 'checkbox',
	] );

	// Image Hover Swap
	$wp_customize->add_setting( 'bizstudio_perf_image_hover_swap', [
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );

	$wp_customize->add_control( 'bizstudio_perf_image_hover_swap', [
		'label'       => __( 'Abilita cambio immagine al passaggio', 'bizstudio' ),
		'description' => __( 'Al passaggio del mouse la scheda prodotto mostra la seconda immagine.', 'bizstudio' ),
		'section'     => 'bizstudio_performance',
		'type'        => 'checkbox',
	] );

	// Preload Fonts
	$wp_customize->add_setting( 'bizstudio_perf_preload_fonts', [
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );

	$wp_customize->add_control( 'bizstudio_perf_preload_fonts', [
		'label'       => __( 'Pre-carica i font', 'bizstudio' ),
		'description' => __( 'Aggiunge link preload per i Google Fonts selezionati.', 'bizstudio' ),
		'section'     => 'bizstudio_performance',
		'type'        => 'checkbox',
	] );

	// Lazy Load Images
	$wp_customize->add_setting( 'bizstudio_perf_lazy_load', [
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );

	$wp_customize->add_control( 'bizstudio_perf_lazy_load', [
		'label'       => __( 'Caricamento lazy immagini', 'bizstudio' ),
		'description' => __( 'Le immagini vengono caricate solo quando entrano nella viewport.', 'bizstudio' ),
		'section'     => 'bizstudio_performance',
		'type'        => 'checkbox',
	] );

} );


/* ================================================================
   2. SANITIZATION HELPERS
   ================================================================ */

/**
 * Sanitize a float value.
 *
 * @param mixed $val Value to sanitize.
 * @return float
 */
function bizstudio_sanitize_float( $val ) {
	return (float) $val;
}


/* ================================================================
   3. GETTER FUNCTIONS
   ================================================================ */

/**
 * Return all color settings as an associative array.
 *
 * Keys match the CSS variable suffix (e.g. 'primary' => '#8367C7').
 *
 * @return array<string, string>
 */
function bizstudio_get_color_settings(): array {
	return [
		'primary'        => get_theme_mod( 'bizstudio_color_primary',        '#8367C7' ),
		'primary_dark'   => get_theme_mod( 'bizstudio_color_primary_dark',   '#6B4FAF' ),
		'text'           => get_theme_mod( 'bizstudio_color_text',           '#1A1A2E' ),
		'text_secondary' => get_theme_mod( 'bizstudio_color_text_secondary', '#555570' ),
		'bg'             => get_theme_mod( 'bizstudio_color_bg',             '#FFFFFF' ),
		'surface'        => get_theme_mod( 'bizstudio_color_surface',        '#F7F7FA' ),
		'border'         => get_theme_mod( 'bizstudio_color_border',         '#E5E5EE' ),
		'success'        => get_theme_mod( 'bizstudio_color_success',        '#2EA043' ),
		'error'          => get_theme_mod( 'bizstudio_color_error',          '#E5383B' ),
		'rating'         => get_theme_mod( 'bizstudio_color_rating',         '#F5A623' ),
	];
}

/**
 * Return all typography settings.
 *
 * @return array{body_font: string, heading_font: string, font_size: int, line_height: float, heading_weight: string}
 */
function bizstudio_get_typography_settings(): array {
	$body_font    = get_theme_mod( 'bizstudio_typo_body_font',       'Inter' );
	$heading_font = get_theme_mod( 'bizstudio_typo_heading_font',    'same' );

	return [
		'body_font'      => $body_font,
		'heading_font'   => 'same' === $heading_font ? $body_font : $heading_font,
		'font_size'      => (int) get_theme_mod( 'bizstudio_typo_font_size',      16 ),
		'line_height'    => (float) get_theme_mod( 'bizstudio_typo_line_height',  1.6 ),
		'heading_weight' => get_theme_mod( 'bizstudio_typo_heading_weight',       '600' ),
	];
}

/**
 * Return all shop settings.
 *
 * @return array{per_page: int, columns: int, card_style: string, show_rating: bool, show_category: bool, show_quick_view: bool, show_wishlist: bool, sale_badge: string}
 */
function bizstudio_get_shop_settings(): array {
	return [
		'per_page'       => (int) get_theme_mod( 'bizstudio_shop_per_page',        12 ),
		'columns'        => (int) get_theme_mod( 'bizstudio_shop_columns',         4 ),
		'card_style'     => get_theme_mod( 'bizstudio_shop_card_style',            'default' ),
		'show_rating'    => (bool) get_theme_mod( 'bizstudio_shop_show_rating',    true ),
		'show_category'  => (bool) get_theme_mod( 'bizstudio_shop_show_category',  true ),
		'show_quick_view' => (bool) get_theme_mod( 'bizstudio_shop_show_quick_view', true ),
		'show_wishlist'  => (bool) get_theme_mod( 'bizstudio_shop_show_wishlist',  true ),
		'sale_badge'     => get_theme_mod( 'bizstudio_shop_sale_badge',            'percentage' ),
	];
}

/**
 * Return all performance settings.
 *
 * @return array{scroll_reveal: bool, image_hover_swap: bool, preload_fonts: bool, lazy_load: bool}
 */
function bizstudio_get_performance_settings(): array {
	return [
		'scroll_reveal'    => (bool) get_theme_mod( 'bizstudio_perf_scroll_reveal',      true ),
		'image_hover_swap' => (bool) get_theme_mod( 'bizstudio_perf_image_hover_swap',   true ),
		'preload_fonts'    => (bool) get_theme_mod( 'bizstudio_perf_preload_fonts',      true ),
		'lazy_load'        => (bool) get_theme_mod( 'bizstudio_perf_lazy_load',          true ),
	];
}


/* ================================================================
   4. CSS VARIABLE OUTPUT (inline <style> on wp_head, priority 5)
   ================================================================ */

add_action( 'wp_head', function () {

	$colors = bizstudio_get_color_settings();
	$typo   = bizstudio_get_typography_settings();

	// Build font-family stacks
	$body_stack    = bizstudio_font_stack( $typo['body_font'] );
	$heading_stack = bizstudio_font_stack( $typo['heading_font'] );

	$css = ":root {\n";

	// Colors
	$css .= "\t--biz-primary: {$colors['primary']};\n";
	$css .= "\t--biz-primary-dark: {$colors['primary_dark']};\n";
	$css .= "\t--biz-text: {$colors['text']};\n";
	$css .= "\t--biz-text-secondary: {$colors['text_secondary']};\n";
	$css .= "\t--biz-bg: {$colors['bg']};\n";
	$css .= "\t--biz-surface: {$colors['surface']};\n";
	$css .= "\t--biz-border: {$colors['border']};\n";
	$css .= "\t--biz-success: {$colors['success']};\n";
	$css .= "\t--biz-error: {$colors['error']};\n";
	$css .= "\t--biz-rating: {$colors['rating']};\n";

	// Typography
	$css .= "\t--biz-font: {$body_stack};\n";
	$css .= "\t--biz-font-heading: {$heading_stack};\n";
	$css .= "\t--biz-font-size: {$typo['font_size']}px;\n";
	$css .= "\t--biz-line-height: {$typo['line_height']};\n";
	$css .= "\t--biz-heading-weight: {$typo['heading_weight']};\n";

	$css .= "}\n";

	// Apply typography overrides to body & headings
	$css .= "body { font-family: var(--biz-font); font-size: var(--biz-font-size); line-height: var(--biz-line-height); }\n";
	$css .= "h1, h2, h3, h4, h5, h6 { font-family: var(--biz-font-heading); font-weight: var(--biz-heading-weight); }\n";

	echo "<style id=\"bizstudio-customizer-css\">\n" . $css . "</style>\n";

}, 5 );


/* ================================================================
   5. GOOGLE FONTS ENQUEUE
   ================================================================ */

/**
 * Build a CSS font-family stack for a given font name.
 *
 * @param string $font Font name from the Customizer select.
 * @return string CSS font-family value.
 */
function bizstudio_font_stack( string $font ): string {
	$system_stack = "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif";

	switch ( $font ) {
		case 'System':
			return $system_stack;
		case 'Inter':
			return "'Inter', {$system_stack}";
		case 'Roboto':
			return "'Roboto', {$system_stack}";
		case 'Open Sans':
			return "'Open Sans', {$system_stack}";
		case 'Lato':
			return "'Lato', {$system_stack}";
		case 'Poppins':
			return "'Poppins', {$system_stack}";
		case 'Nunito':
			return "'Nunito', {$system_stack}";
		default:
			return "'Inter', {$system_stack}";
	}
}

/**
 * Enqueue Google Fonts if needed.
 *
 * Only loads fonts that actually require Google Fonts
 * (System and Inter are already available / bundled).
 */
add_action( 'wp_enqueue_scripts', function () {
	$typo         = bizstudio_get_typography_settings();
	$google_fonts = bizstudio_get_google_fonts_to_load( $typo['body_font'], $typo['heading_font'] );

	if ( empty( $google_fonts ) ) {
		return;
	}

	// Build families string: each font requests weights 400-800
	$families = [];
	foreach ( $google_fonts as $font ) {
		$families[] = str_replace( ' ', '+', $font ) . ':wght@400;500;600;700;800';
	}

	$url = 'https://fonts.googleapis.com/css2?' . implode( '&', array_map( function ( $f ) {
		return 'family=' . $f;
	}, $families ) ) . '&display=swap';

	wp_enqueue_style( 'bizstudio-google-fonts', $url, [], null );

	// Preload hint for performance
	$perf = bizstudio_get_performance_settings();
	if ( $perf['preload_fonts'] ) {
		add_filter( 'style_loader_tag', function ( $html, $handle ) {
			if ( 'bizstudio-google-fonts' === $handle ) {
				$html = str_replace( "rel='stylesheet'", "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"", $html );
				$html .= "<noscript>" . str_replace( [ "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"" ], [ "rel='stylesheet'" ], $html ) . "</noscript>";
			}
			return $html;
		}, 10, 2 );
	}
}, 5 );

/**
 * Determine which fonts need loading from Google Fonts.
 *
 * @param string $body_font    Body font name.
 * @param string $heading_font Heading font name.
 * @return string[] Font names that need Google Fonts.
 */
function bizstudio_get_google_fonts_to_load( string $body_font, string $heading_font ): array {
	// Fonts that do NOT require Google Fonts loading
	$local_fonts = [ 'Inter', 'System' ];

	$fonts = [];

	if ( ! in_array( $body_font, $local_fonts, true ) ) {
		$fonts[] = $body_font;
	}

	if ( $heading_font !== $body_font && ! in_array( $heading_font, $local_fonts, true ) ) {
		$fonts[] = $heading_font;
	}

	return array_unique( $fonts );
}


/* ================================================================
   6. WOOCOMMERCE SHOP FILTERS
   ================================================================ */

/**
 * Override products per page from Customizer setting.
 *
 * Uses priority 20 so it runs after the default in woocommerce.php.
 */
add_filter( 'loop_shop_per_page', function () {
	$shop = bizstudio_get_shop_settings();
	return $shop['per_page'];
}, 20 );

/**
 * Override products per row from Customizer setting.
 *
 * Uses priority 20 so it runs after the default in woocommerce.php.
 */
add_filter( 'loop_shop_columns', function () {
	$shop = bizstudio_get_shop_settings();
	return $shop['columns'];
}, 20 );


/* ================================================================
   7. LIVE PREVIEW JS (postMessage transport)
   ================================================================ */

add_action( 'customize_preview_init', function () {
	wp_add_inline_script( 'customize-preview', "
		( function( $ ) {

			/* ── Color live preview ───────────────────── */
			var colorMap = {
				'bizstudio_color_primary':        '--biz-primary',
				'bizstudio_color_primary_dark':   '--biz-primary-dark',
				'bizstudio_color_text':           '--biz-text',
				'bizstudio_color_text_secondary': '--biz-text-secondary',
				'bizstudio_color_bg':             '--biz-bg',
				'bizstudio_color_surface':        '--biz-surface',
				'bizstudio_color_border':         '--biz-border',
				'bizstudio_color_success':        '--biz-success',
				'bizstudio_color_error':          '--biz-error',
				'bizstudio_color_rating':         '--biz-rating'
			};

			$.each( colorMap, function( settingId, cssVar ) {
				wp.customize( settingId, function( value ) {
					value.bind( function( to ) {
						document.documentElement.style.setProperty( cssVar, to );
					} );
				} );
			} );

			/* ── Typography live preview ──────────────── */
			var fontStacks = {
				'Inter':     \"'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif\",
				'System':    \"-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif\",
				'Roboto':    \"'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif\",
				'Open Sans': \"'Open Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif\",
				'Lato':      \"'Lato', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif\",
				'Poppins':   \"'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif\",
				'Nunito':    \"'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif\"
			};

			wp.customize( 'bizstudio_typo_body_font', function( value ) {
				value.bind( function( to ) {
					var stack = fontStacks[ to ] || fontStacks['Inter'];
					document.documentElement.style.setProperty( '--biz-font', stack );
					// If heading is 'same', update it too
					var headingVal = wp.customize( 'bizstudio_typo_heading_font' ).get();
					if ( headingVal === 'same' ) {
						document.documentElement.style.setProperty( '--biz-font-heading', stack );
					}
				} );
			} );

			wp.customize( 'bizstudio_typo_heading_font', function( value ) {
				value.bind( function( to ) {
					var stack;
					if ( to === 'same' ) {
						var bodyVal = wp.customize( 'bizstudio_typo_body_font' ).get();
						stack = fontStacks[ bodyVal ] || fontStacks['Inter'];
					} else {
						stack = fontStacks[ to ] || fontStacks['Inter'];
					}
					document.documentElement.style.setProperty( '--biz-font-heading', stack );
				} );
			} );

			wp.customize( 'bizstudio_typo_font_size', function( value ) {
				value.bind( function( to ) {
					document.documentElement.style.setProperty( '--biz-font-size', to + 'px' );
				} );
			} );

			wp.customize( 'bizstudio_typo_line_height', function( value ) {
				value.bind( function( to ) {
					document.documentElement.style.setProperty( '--biz-line-height', to );
				} );
			} );

			wp.customize( 'bizstudio_typo_heading_weight', function( value ) {
				value.bind( function( to ) {
					document.documentElement.style.setProperty( '--biz-heading-weight', to );
				} );
			} );

		} )( jQuery );
	" );
} );
