<?php
/**
 * Header Builder
 *
 * Customizer-based header configuration system.
 * Provides 5 presets, 3-row layout (top bar, main header, bottom bar),
 * and per-row styling options. The actual rendering stays in header.php;
 * this file only registers settings and outputs configuration.
 *
 * @package BizStudio
 * @since 1.1.0
 */

defined( 'ABSPATH' ) || exit;

/* ================================================================
   1. Customizer Registration
   ================================================================ */

add_action( 'customize_register', function ( WP_Customize_Manager $wp_customize ) {

	/* ── Section: Header ─────────────────────────────────────── */
	$wp_customize->add_section( 'bizstudio_header', [
		'title'    => __( 'Header', 'bizstudio' ),
		'panel'    => 'bizstudio_panel',
		'priority' => 15,
	] );

	/* ── Helper: add setting + control ───────────────────────── */
	$add = function ( string $id, array $setting_args, array $control_args ) use ( $wp_customize ) {
		$wp_customize->add_setting( "bizstudio_header_{$id}", $setting_args );

		$control_class = $control_args['_class'] ?? null;
		unset( $control_args['_class'] );

		$control_args['section'] = $control_args['section'] ?? 'bizstudio_header';

		if ( $control_class ) {
			$wp_customize->add_control(
				new $control_class( $wp_customize, "bizstudio_header_{$id}", $control_args )
			);
		} else {
			$wp_customize->add_control( "bizstudio_header_{$id}", $control_args );
		}
	};

	/* ── Separatore descrittivo ──────────────────────────────── */
	$sep_index = 0;
	$add_separator = function ( string $label ) use ( $wp_customize, &$sep_index ) {
		$sep_index++;
		$wp_customize->add_setting( "bizstudio_header_sep_{$sep_index}", [
			'sanitize_callback' => 'sanitize_text_field',
		] );
		$wp_customize->add_control( "bizstudio_header_sep_{$sep_index}", [
			'label'       => "── {$label} ──",
			'section'     => 'bizstudio_header',
			'type'        => 'hidden',
			'description' => "<hr style='margin:12px 0 4px'><strong>{$label}</strong>",
		] );
	};

	/* ────────────────────────────────────────────────────────
	   PRESET
	   ──────────────────────────────────────────────────────── */
	$add( 'preset', [
		'default'           => 'default',
		'sanitize_callback' => function ( $val ) {
			$valid = [ 'default', 'centered', 'minimal', 'transparent', 'ecommerce' ];
			return in_array( $val, $valid, true ) ? $val : 'default';
		},
		'transport' => 'refresh',
	], [
		'label'   => __( 'Preset Header', 'bizstudio' ),
		'type'    => 'select',
		'choices' => [
			'default'     => __( 'Default — Logo sx, Menu centro, Azioni dx', 'bizstudio' ),
			'centered'    => __( 'Centrato — Menu sx, Logo centro, Azioni dx', 'bizstudio' ),
			'minimal'     => __( 'Minimale — Solo logo e hamburger', 'bizstudio' ),
			'transparent' => __( 'Trasparente — Sfondo trasparente, solido allo scroll', 'bizstudio' ),
			'ecommerce'   => __( 'E-commerce — Logo sx, Ricerca centro, Azioni dx', 'bizstudio' ),
		],
	] );

	/* ────────────────────────────────────────────────────────
	   TOP BAR
	   ──────────────────────────────────────────────────────── */
	$add_separator( __( 'Top Bar', 'bizstudio' ) );

	$add( 'topbar_enable', [
		'default'           => false,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	], [
		'label' => __( 'Mostra Top Bar', 'bizstudio' ),
		'type'  => 'checkbox',
	] );

	$add( 'topbar_height', [
		'default'           => 40,
		'sanitize_callback' => 'absint',
		'transport'         => 'postMessage',
	], [
		'label'       => __( 'Altezza Top Bar (px)', 'bizstudio' ),
		'type'        => 'number',
		'input_attrs' => [ 'min' => 28, 'max' => 80, 'step' => 1 ],
	] );

	$add( 'topbar_bg', [
		'default'           => '#1A1A2E',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	], [
		'label'  => __( 'Colore sfondo Top Bar', 'bizstudio' ),
		'_class' => 'WP_Customize_Color_Control',
	] );

	$add( 'topbar_color', [
		'default'           => '#FFFFFF',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	], [
		'label'  => __( 'Colore testo Top Bar', 'bizstudio' ),
		'_class' => 'WP_Customize_Color_Control',
	] );

	$add( 'topbar_text', [
		'default'           => '',
		'sanitize_callback' => 'wp_kses_post',
		'transport'         => 'postMessage',
	], [
		'label'       => __( 'Testo Top Bar', 'bizstudio' ),
		'type'        => 'text',
		'description' => __( 'Testo mostrato nella Top Bar (es. info contatto, promo).', 'bizstudio' ),
	] );

	$add( 'topbar_border', [
		'default'           => false,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'postMessage',
	], [
		'label' => __( 'Bordo inferiore Top Bar', 'bizstudio' ),
		'type'  => 'checkbox',
	] );

	$add( 'topbar_visibility', [
		'default'           => 'both',
		'sanitize_callback' => function ( $val ) {
			return in_array( $val, [ 'both', 'desktop', 'mobile' ], true ) ? $val : 'both';
		},
		'transport' => 'refresh',
	], [
		'label'   => __( 'Visibilità Top Bar', 'bizstudio' ),
		'type'    => 'select',
		'choices' => [
			'both'    => __( 'Desktop e Mobile', 'bizstudio' ),
			'desktop' => __( 'Solo Desktop', 'bizstudio' ),
			'mobile'  => __( 'Solo Mobile', 'bizstudio' ),
		],
	] );

	/* ────────────────────────────────────────────────────────
	   MAIN HEADER
	   ──────────────────────────────────────────────────────── */
	$add_separator( __( 'Header Principale', 'bizstudio' ) );

	$add( 'main_height', [
		'default'           => 72,
		'sanitize_callback' => 'absint',
		'transport'         => 'postMessage',
	], [
		'label'       => __( 'Altezza Header (px)', 'bizstudio' ),
		'type'        => 'number',
		'input_attrs' => [ 'min' => 48, 'max' => 120, 'step' => 1 ],
	] );

	$add( 'main_bg', [
		'default'           => '',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	], [
		'label'       => __( 'Colore sfondo Header', 'bizstudio' ),
		'description' => __( 'Lascia vuoto per usare il colore di sfondo del tema.', 'bizstudio' ),
		'_class'      => 'WP_Customize_Color_Control',
	] );

	$add( 'main_color', [
		'default'           => '',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	], [
		'label'       => __( 'Colore testo Header', 'bizstudio' ),
		'description' => __( 'Lascia vuoto per usare il colore del testo del tema.', 'bizstudio' ),
		'_class'      => 'WP_Customize_Color_Control',
	] );

	$add( 'main_border', [
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'postMessage',
	], [
		'label' => __( 'Bordo inferiore Header', 'bizstudio' ),
		'type'  => 'checkbox',
	] );

	$add( 'main_sticky', [
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	], [
		'label' => __( 'Header Sticky (fisso allo scroll)', 'bizstudio' ),
		'type'  => 'checkbox',
	] );

	$add( 'transparent_homepage', [
		'default'           => false,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	], [
		'label'       => __( 'Trasparente in Homepage', 'bizstudio' ),
		'type'        => 'checkbox',
		'description' => __( 'Attivo solo con preset Trasparente. Header con sfondo trasparente nella homepage, diventa solido allo scroll.', 'bizstudio' ),
	] );

	/* ────────────────────────────────────────────────────────
	   BOTTOM BAR
	   ──────────────────────────────────────────────────────── */
	$add_separator( __( 'Bottom Bar', 'bizstudio' ) );

	$add( 'bottombar_enable', [
		'default'           => false,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	], [
		'label' => __( 'Mostra Bottom Bar', 'bizstudio' ),
		'type'  => 'checkbox',
	] );

	$add( 'bottombar_height', [
		'default'           => 48,
		'sanitize_callback' => 'absint',
		'transport'         => 'postMessage',
	], [
		'label'       => __( 'Altezza Bottom Bar (px)', 'bizstudio' ),
		'type'        => 'number',
		'input_attrs' => [ 'min' => 32, 'max' => 80, 'step' => 1 ],
	] );

	$add( 'bottombar_bg', [
		'default'           => '#F7F7FA',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	], [
		'label'  => __( 'Colore sfondo Bottom Bar', 'bizstudio' ),
		'_class' => 'WP_Customize_Color_Control',
	] );

	$add( 'bottombar_color', [
		'default'           => '',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	], [
		'label'       => __( 'Colore testo Bottom Bar', 'bizstudio' ),
		'description' => __( 'Lascia vuoto per usare il colore del testo del tema.', 'bizstudio' ),
		'_class'      => 'WP_Customize_Color_Control',
	] );

	$add( 'bottombar_content', [
		'default'           => 'none',
		'sanitize_callback' => function ( $val ) {
			return in_array( $val, [ 'categories', 'secondary-menu', 'none' ], true ) ? $val : 'none';
		},
		'transport' => 'refresh',
	], [
		'label'   => __( 'Contenuto Bottom Bar', 'bizstudio' ),
		'type'    => 'select',
		'choices' => [
			'none'           => __( 'Nessuno', 'bizstudio' ),
			'categories'     => __( 'Categorie prodotto', 'bizstudio' ),
			'secondary-menu' => __( 'Menu secondario', 'bizstudio' ),
		],
	] );

	$add( 'bottombar_border', [
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'postMessage',
	], [
		'label' => __( 'Bordo inferiore Bottom Bar', 'bizstudio' ),
		'type'  => 'checkbox',
	] );

	$add( 'bottombar_visibility', [
		'default'           => 'desktop',
		'sanitize_callback' => function ( $val ) {
			return in_array( $val, [ 'both', 'desktop', 'mobile' ], true ) ? $val : 'desktop';
		},
		'transport' => 'refresh',
	], [
		'label'   => __( 'Visibilità Bottom Bar', 'bizstudio' ),
		'type'    => 'select',
		'choices' => [
			'both'    => __( 'Desktop e Mobile', 'bizstudio' ),
			'desktop' => __( 'Solo Desktop', 'bizstudio' ),
			'mobile'  => __( 'Solo Mobile', 'bizstudio' ),
		],
	] );

	/* ── Selective-refresh partials ──────────────────────────── */
	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial( 'bizstudio_header_topbar_text', [
			'selector'        => '.biz-header-topbar__text',
			'render_callback' => function () {
				echo wp_kses_post( get_theme_mod( 'bizstudio_header_topbar_text', '' ) );
			},
		] );
	}

} );

/* ================================================================
   2. Configuration Helper
   ================================================================ */

/**
 * Return all header settings as an associative array.
 *
 * Useful for templates that need to read the current header config
 * without calling get_theme_mod() multiple times.
 *
 * @return array
 */
function bizstudio_get_header_config(): array {
	return [
		/* Preset */
		'preset'                => get_theme_mod( 'bizstudio_header_preset', 'default' ),

		/* Top Bar */
		'topbar_enable'         => (bool) get_theme_mod( 'bizstudio_header_topbar_enable', false ),
		'topbar_height'         => (int) get_theme_mod( 'bizstudio_header_topbar_height', 40 ),
		'topbar_bg'             => get_theme_mod( 'bizstudio_header_topbar_bg', '#1A1A2E' ),
		'topbar_color'          => get_theme_mod( 'bizstudio_header_topbar_color', '#FFFFFF' ),
		'topbar_text'           => get_theme_mod( 'bizstudio_header_topbar_text', '' ),
		'topbar_border'         => (bool) get_theme_mod( 'bizstudio_header_topbar_border', false ),
		'topbar_visibility'     => get_theme_mod( 'bizstudio_header_topbar_visibility', 'both' ),

		/* Main Header */
		'main_height'           => (int) get_theme_mod( 'bizstudio_header_main_height', 72 ),
		'main_bg'               => get_theme_mod( 'bizstudio_header_main_bg', '' ),
		'main_color'            => get_theme_mod( 'bizstudio_header_main_color', '' ),
		'main_border'           => (bool) get_theme_mod( 'bizstudio_header_main_border', true ),
		'main_sticky'           => (bool) get_theme_mod( 'bizstudio_header_main_sticky', true ),
		'transparent_homepage'  => (bool) get_theme_mod( 'bizstudio_header_transparent_homepage', false ),

		/* Bottom Bar */
		'bottombar_enable'      => (bool) get_theme_mod( 'bizstudio_header_bottombar_enable', false ),
		'bottombar_height'      => (int) get_theme_mod( 'bizstudio_header_bottombar_height', 48 ),
		'bottombar_bg'          => get_theme_mod( 'bizstudio_header_bottombar_bg', '#F7F7FA' ),
		'bottombar_color'       => get_theme_mod( 'bizstudio_header_bottombar_color', '' ),
		'bottombar_content'     => get_theme_mod( 'bizstudio_header_bottombar_content', 'none' ),
		'bottombar_border'      => (bool) get_theme_mod( 'bizstudio_header_bottombar_border', true ),
		'bottombar_visibility'  => get_theme_mod( 'bizstudio_header_bottombar_visibility', 'desktop' ),
	];
}

/* ================================================================
   3. Preset Class Helper
   ================================================================ */

/**
 * Return the CSS class for the current header preset.
 *
 * @return string e.g. 'biz-header--default'
 */
function bizstudio_header_preset_class(): string {
	$preset = get_theme_mod( 'bizstudio_header_preset', 'default' );
	return 'biz-header--' . sanitize_html_class( $preset );
}

/* ================================================================
   4. Body Class Filter
   ================================================================ */

add_filter( 'body_class', function ( array $classes ): array {
	$config = bizstudio_get_header_config();

	// Preset class
	$classes[] = 'biz-header--' . sanitize_html_class( $config['preset'] );

	// Sticky
	if ( $config['main_sticky'] ) {
		$classes[] = 'biz-header--sticky';
	}

	// Transparent on homepage
	if ( 'transparent' === $config['preset'] && $config['transparent_homepage'] && is_front_page() ) {
		$classes[] = 'biz-header--transparent-home';
	}

	// Top bar active
	if ( $config['topbar_enable'] ) {
		$classes[] = 'biz-has-topbar';
		if ( 'desktop' === $config['topbar_visibility'] ) {
			$classes[] = 'biz-topbar--desktop-only';
		} elseif ( 'mobile' === $config['topbar_visibility'] ) {
			$classes[] = 'biz-topbar--mobile-only';
		}
	}

	// Bottom bar active
	if ( $config['bottombar_enable'] ) {
		$classes[] = 'biz-has-bottombar';
		if ( 'desktop' === $config['bottombar_visibility'] ) {
			$classes[] = 'biz-bottombar--desktop-only';
		} elseif ( 'mobile' === $config['bottombar_visibility'] ) {
			$classes[] = 'biz-bottombar--mobile-only';
		}
	}

	return $classes;
} );

/* ================================================================
   5. Inline CSS Variables (wp_head)
   ================================================================ */

add_action( 'wp_head', function () {
	$c = bizstudio_get_header_config();

	$vars = [];

	// Main header
	$vars[] = "--biz-header-h: {$c['main_height']}px";
	if ( $c['main_bg'] ) {
		$vars[] = '--biz-header-bg: ' . esc_attr( $c['main_bg'] );
	}
	if ( $c['main_color'] ) {
		$vars[] = '--biz-header-color: ' . esc_attr( $c['main_color'] );
	}

	// Top bar
	if ( $c['topbar_enable'] ) {
		$vars[] = "--biz-topbar-h: {$c['topbar_height']}px";
		$vars[] = '--biz-topbar-bg: ' . esc_attr( $c['topbar_bg'] );
		$vars[] = '--biz-topbar-color: ' . esc_attr( $c['topbar_color'] );
	} else {
		$vars[] = '--biz-topbar-h: 0px';
	}

	// Bottom bar
	if ( $c['bottombar_enable'] ) {
		$vars[] = "--biz-bottombar-h: {$c['bottombar_height']}px";
		$vars[] = '--biz-bottombar-bg: ' . esc_attr( $c['bottombar_bg'] );
		if ( $c['bottombar_color'] ) {
			$vars[] = '--biz-bottombar-color: ' . esc_attr( $c['bottombar_color'] );
		}
	} else {
		$vars[] = '--biz-bottombar-h: 0px';
	}

	if ( empty( $vars ) ) {
		return;
	}

	echo "<style id=\"biz-header-vars\">:root{" . implode( ';', $vars ) . "}</style>\n";
}, 5 );

/* ================================================================
   6. Render Top Bar (wp_body_open)
   ================================================================ */

/**
 * Render the optional top bar above the header.
 */
function bizstudio_render_header_topbar() {
	$c = bizstudio_get_header_config();

	if ( ! $c['topbar_enable'] || empty( $c['topbar_text'] ) ) {
		return;
	}

	$vis_class = '';
	if ( 'desktop' === $c['topbar_visibility'] ) {
		$vis_class = ' biz-header-topbar--desktop-only';
	} elseif ( 'mobile' === $c['topbar_visibility'] ) {
		$vis_class = ' biz-header-topbar--mobile-only';
	}

	printf(
		'<div class="biz-header-topbar%s" style="background:%s;color:%s;height:%dpx">',
		esc_attr( $vis_class ),
		esc_attr( $c['topbar_bg'] ),
		esc_attr( $c['topbar_color'] ),
		$c['topbar_height']
	);
	echo '<div class="biz-container biz-header-topbar__inner">';
	echo '<span class="biz-header-topbar__text">' . wp_kses_post( $c['topbar_text'] ) . '</span>';
	echo '</div></div>';
}
add_action( 'wp_body_open', 'bizstudio_render_header_topbar', 4 );

/* ================================================================
   7. Render Bottom Bar (after header — wp_body_open)
   ================================================================ */

/**
 * Render the optional bottom bar below the header.
 *
 * Uses a priority of 15 so it prints after the promo bar (5)
 * and after header.php has been included.
 */
function bizstudio_render_header_bottombar() {
	static $rendered = false;
	if ( $rendered ) {
		return;
	}
	$rendered = true;

	$c = bizstudio_get_header_config();

	if ( ! $c['bottombar_enable'] || 'none' === $c['bottombar_content'] ) {
		return;
	}

	$vis_class = '';
	if ( 'desktop' === $c['bottombar_visibility'] ) {
		$vis_class = ' biz-header-bottom--desktop-only';
	} elseif ( 'mobile' === $c['bottombar_visibility'] ) {
		$vis_class = ' biz-header-bottom--mobile-only';
	}

	$style_parts = [
		'background:' . esc_attr( $c['bottombar_bg'] ),
		'height:' . $c['bottombar_height'] . 'px',
	];
	if ( $c['bottombar_color'] ) {
		$style_parts[] = 'color:' . esc_attr( $c['bottombar_color'] );
	}
	if ( $c['bottombar_border'] ) {
		$style_parts[] = 'border-bottom:1px solid var(--biz-border)';
	}

	printf(
		'<div class="biz-header-bottom%s" style="%s">',
		esc_attr( $vis_class ),
		implode( ';', $style_parts )
	);
	echo '<div class="biz-container biz-header-bottom__inner">';

	if ( 'categories' === $c['bottombar_content'] && function_exists( 'WC' ) ) {
		// Output product categories as a horizontal nav
		$categories = get_terms( [
			'taxonomy'   => 'product_cat',
			'orderby'    => 'count',
			'order'      => 'DESC',
			'hide_empty' => true,
			'number'     => 8,
			'exclude'    => get_option( 'default_product_cat', 0 ),
		] );

		if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) {
			echo '<nav class="biz-header-bottom__nav" aria-label="' . esc_attr__( 'Categorie', 'bizstudio' ) . '">';
			echo '<ul class="biz-header-bottom__list">';
			foreach ( $categories as $cat ) {
				printf(
					'<li><a href="%s" class="biz-header-bottom__link">%s</a></li>',
					esc_url( get_term_link( $cat ) ),
					esc_html( $cat->name )
				);
			}
			echo '</ul></nav>';
		}
	} elseif ( 'secondary-menu' === $c['bottombar_content'] ) {
		wp_nav_menu( [
			'theme_location' => 'footer',
			'container'      => 'nav',
			'container_class' => 'biz-header-bottom__nav',
			'container_attr' => 'aria-label="' . esc_attr__( 'Menu secondario', 'bizstudio' ) . '"',
			'menu_class'     => 'biz-header-bottom__list',
			'fallback_cb'    => false,
			'depth'          => 1,
		] );
	}

	echo '</div></div>';
}
add_action( 'bizstudio_after_header', 'bizstudio_render_header_bottombar', 10 );
// Fallback: also hook to wp_body_open at a late priority in case the action above is not called
add_action( 'wp_body_open', 'bizstudio_render_header_bottombar', 20 );

/* ================================================================
   8. Enqueue Header Builder CSS
   ================================================================ */

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'bizstudio-header-builder',
		BIZSTUDIO_URI . '/assets/src/css/components/header-builder.css',
		[ 'bizstudio-main' ],
		BIZSTUDIO_VERSION
	);
} );

/* ================================================================
   9. Transparent Header Scroll Script
   ================================================================ */

add_action( 'wp_footer', function () {
	$config = bizstudio_get_header_config();

	// Transparent header scroll behavior
	if ( 'transparent' === $config['preset'] ) {
		?>
		<script>
		(function(){
			var header = document.getElementById('biz-header');
			if ( ! header ) return;
			var threshold = 50;
			function onScroll() {
				if ( window.scrollY > threshold ) {
					header.classList.add('is-scrolled');
					header.classList.remove('is-transparent');
				} else {
					header.classList.remove('is-scrolled');
					header.classList.add('is-transparent');
				}
			}
			header.classList.add('is-transparent');
			window.addEventListener('scroll', onScroll, { passive: true });
			onScroll();
		})();
		</script>
		<?php
	}

	// Ecommerce preset: show/hide search bar on mobile
	if ( 'ecommerce' === $config['preset'] ) {
		?>
		<script>
		(function(){
			var searchWrap = document.querySelector('.biz-header__search-bar');
			if ( ! searchWrap ) return;
			// On mobile, the search bar is hidden via CSS; no JS needed for basic behavior
		})();
		</script>
		<?php
	}
}, 20 );

/* ================================================================
   10. Customizer Live Preview (postMessage)
   ================================================================ */

add_action( 'customize_preview_init', function () {
	wp_add_inline_script( 'customize-preview', "
		( function( $ ) {

			/* ── Top Bar ─────────────────────────────── */
			wp.customize( 'bizstudio_header_topbar_height', function( v ) {
				v.bind( function( to ) {
					document.documentElement.style.setProperty( '--biz-topbar-h', to + 'px' );
					$( '.biz-header-topbar' ).css( 'height', to + 'px' );
				} );
			} );
			wp.customize( 'bizstudio_header_topbar_bg', function( v ) {
				v.bind( function( to ) {
					document.documentElement.style.setProperty( '--biz-topbar-bg', to );
					$( '.biz-header-topbar' ).css( 'background', to );
				} );
			} );
			wp.customize( 'bizstudio_header_topbar_color', function( v ) {
				v.bind( function( to ) {
					document.documentElement.style.setProperty( '--biz-topbar-color', to );
					$( '.biz-header-topbar' ).css( 'color', to );
				} );
			} );
			wp.customize( 'bizstudio_header_topbar_text', function( v ) {
				v.bind( function( to ) {
					$( '.biz-header-topbar__text' ).html( to );
				} );
			} );
			wp.customize( 'bizstudio_header_topbar_border', function( v ) {
				v.bind( function( to ) {
					$( '.biz-header-topbar' ).css( 'border-bottom', to ? '1px solid var(--biz-border)' : 'none' );
				} );
			} );

			/* ── Main Header ─────────────────────────── */
			wp.customize( 'bizstudio_header_main_height', function( v ) {
				v.bind( function( to ) {
					document.documentElement.style.setProperty( '--biz-header-h', to + 'px' );
				} );
			} );
			wp.customize( 'bizstudio_header_main_bg', function( v ) {
				v.bind( function( to ) {
					document.documentElement.style.setProperty( '--biz-header-bg', to || '' );
					$( '.biz-header' ).css( 'background', to || '' );
				} );
			} );
			wp.customize( 'bizstudio_header_main_color', function( v ) {
				v.bind( function( to ) {
					document.documentElement.style.setProperty( '--biz-header-color', to || '' );
					$( '.biz-header' ).css( 'color', to || '' );
				} );
			} );
			wp.customize( 'bizstudio_header_main_border', function( v ) {
				v.bind( function( to ) {
					$( '.biz-header' ).css( 'border-bottom', to ? '1px solid var(--biz-border)' : 'none' );
				} );
			} );

			/* ── Bottom Bar ──────────────────────────── */
			wp.customize( 'bizstudio_header_bottombar_height', function( v ) {
				v.bind( function( to ) {
					document.documentElement.style.setProperty( '--biz-bottombar-h', to + 'px' );
					$( '.biz-header-bottom' ).css( 'height', to + 'px' );
				} );
			} );
			wp.customize( 'bizstudio_header_bottombar_bg', function( v ) {
				v.bind( function( to ) {
					document.documentElement.style.setProperty( '--biz-bottombar-bg', to );
					$( '.biz-header-bottom' ).css( 'background', to );
				} );
			} );
			wp.customize( 'bizstudio_header_bottombar_color', function( v ) {
				v.bind( function( to ) {
					if ( to ) {
						document.documentElement.style.setProperty( '--biz-bottombar-color', to );
						$( '.biz-header-bottom' ).css( 'color', to );
					}
				} );
			} );
			wp.customize( 'bizstudio_header_bottombar_border', function( v ) {
				v.bind( function( to ) {
					$( '.biz-header-bottom' ).css( 'border-bottom', to ? '1px solid var(--biz-border)' : 'none' );
				} );
			} );

		} )( jQuery );
	" );
} );
