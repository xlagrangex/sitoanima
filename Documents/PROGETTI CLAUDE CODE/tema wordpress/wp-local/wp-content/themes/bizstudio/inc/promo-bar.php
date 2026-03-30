<?php
/**
 * Promo Bar
 *
 * Promotional banner displayed above the header.
 * Configurable via the WordPress Customizer.
 *
 * @package BizStudio
 */

defined( 'ABSPATH' ) || exit;

/* ──────────────────────────────────────────────
   1. Customizer Settings
   ────────────────────────────────────────────── */

add_action( 'customize_register', function ( WP_Customize_Manager $wp_customize ) {

	// Panel (shared by all future BizStudio sections)
	$wp_customize->add_panel( 'bizstudio_panel', [
		'title'    => __( 'BizStudio', 'bizstudio' ),
		'priority' => 30,
	] );

	// Section
	$wp_customize->add_section( 'bizstudio_promo_bar', [
		'title'    => __( 'Barra Promozionale', 'bizstudio' ),
		'panel'    => 'bizstudio_panel',
		'priority' => 10,
	] );

	// ── Enable toggle ──────────────────────────
	$wp_customize->add_setting( 'bizstudio_promo_bar_enable', [
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );

	$wp_customize->add_control( 'bizstudio_promo_bar_enable', [
		'label'   => __( 'Mostra barra promozionale', 'bizstudio' ),
		'section' => 'bizstudio_promo_bar',
		'type'    => 'checkbox',
	] );

	// ── Promo text ─────────────────────────────
	$wp_customize->add_setting( 'bizstudio_promo_bar_text', [
		'default'           => __( 'Spedizione gratuita per ordini superiori a 49€', 'bizstudio' ),
		'sanitize_callback' => 'wp_kses_post',
		'transport'         => 'postMessage',
	] );

	$wp_customize->add_control( 'bizstudio_promo_bar_text', [
		'label'   => __( 'Testo promozionale', 'bizstudio' ),
		'section' => 'bizstudio_promo_bar',
		'type'    => 'text',
	] );

	// ── Background color ───────────────────────
	$wp_customize->add_setting( 'bizstudio_promo_bar_bg', [
		'default'           => '#8367C7',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	] );

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'bizstudio_promo_bar_bg', [
		'label'   => __( 'Colore di sfondo', 'bizstudio' ),
		'section' => 'bizstudio_promo_bar',
	] ) );

	// ── Text color ─────────────────────────────
	$wp_customize->add_setting( 'bizstudio_promo_bar_color', [
		'default'           => '#FFFFFF',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	] );

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'bizstudio_promo_bar_color', [
		'label'   => __( 'Colore del testo', 'bizstudio' ),
		'section' => 'bizstudio_promo_bar',
	] ) );

	/* Selective-refresh partial for the promo text */
	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial( 'bizstudio_promo_bar_text', [
			'selector'        => '.biz-promo-bar__text',
			'render_callback' => function () {
				echo esc_html( get_theme_mod( 'bizstudio_promo_bar_text', __( 'Spedizione gratuita per ordini superiori a 49€', 'bizstudio' ) ) );
			},
		] );
	}
} );

/* Live-preview JS for the Customizer */
add_action( 'customize_preview_init', function () {
	wp_add_inline_script( 'customize-preview', "
		( function( $ ) {
			wp.customize( 'bizstudio_promo_bar_text', function( value ) {
				value.bind( function( to ) {
					$( '.biz-promo-bar__text' ).text( to );
				} );
			} );
			wp.customize( 'bizstudio_promo_bar_bg', function( value ) {
				value.bind( function( to ) {
					$( '.biz-promo-bar' ).css( 'background-color', to );
				} );
			} );
			wp.customize( 'bizstudio_promo_bar_color', function( value ) {
				value.bind( function( to ) {
					$( '.biz-promo-bar' ).css( 'color', to );
					$( '.biz-promo-bar__close' ).css( 'color', to );
				} );
			} );
		} )( jQuery );
	" );
} );

/* ──────────────────────────────────────────────
   2. Render Promo Bar
   ────────────────────────────────────────────── */

/**
 * Output the promo bar HTML.
 *
 * Hooked to `wp_body_open` so it prints right after <body>,
 * above the header.
 */
function bizstudio_render_promo_bar() {

	// Bail if disabled in the Customizer
	if ( ! get_theme_mod( 'bizstudio_promo_bar_enable', true ) ) {
		return;
	}

	// Bail if dismissed via cookie (24 h)
	if ( isset( $_COOKIE['biz_promo_dismissed'] ) && '1' === $_COOKIE['biz_promo_dismissed'] ) {
		return;
	}

	$text  = get_theme_mod( 'bizstudio_promo_bar_text', __( 'Spedizione gratuita per ordini superiori a 49€', 'bizstudio' ) );
	$bg    = get_theme_mod( 'bizstudio_promo_bar_bg', '#8367C7' );
	$color = get_theme_mod( 'bizstudio_promo_bar_color', '#FFFFFF' );

	if ( empty( $text ) ) {
		return;
	}
	?>
	<div class="biz-promo-bar" id="biz-promo-bar"
		 style="background-color:<?php echo esc_attr( $bg ); ?>;color:<?php echo esc_attr( $color ); ?>"
		 role="banner"
		 aria-label="<?php esc_attr_e( 'Promozione', 'bizstudio' ); ?>">
		<div class="biz-promo-bar__inner">
			<span class="biz-promo-bar__text"><?php echo esc_html( $text ); ?></span>
			<button class="biz-promo-bar__close"
					id="biz-promo-close"
					type="button"
					style="color:<?php echo esc_attr( $color ); ?>"
					aria-label="<?php esc_attr_e( 'Chiudi barra promozionale', 'bizstudio' ); ?>">&times;</button>
		</div>
	</div>
	<script>
	(function(){
		var bar=document.getElementById('biz-promo-bar');
		var btn=document.getElementById('biz-promo-close');
		if(!bar||!btn) return;
		document.documentElement.style.setProperty('--biz-promo-bar-h',bar.offsetHeight+'px');
		btn.addEventListener('click',function(){
			bar.classList.add('biz-promo-bar--dismissed');
			document.documentElement.style.setProperty('--biz-promo-bar-h','0px');
			var d=new Date();d.setTime(d.getTime()+86400000);
			document.cookie='biz_promo_dismissed=1;expires='+d.toUTCString()+';path=/;SameSite=Lax';
		});
	})();
	</script>
	<?php
}
add_action( 'wp_body_open', 'bizstudio_render_promo_bar', 5 );

/* ──────────────────────────────────────────────
   3. Enqueue CSS
   ────────────────────────────────────────────── */

add_action( 'wp_enqueue_scripts', function () {
	if ( ! get_theme_mod( 'bizstudio_promo_bar_enable', true ) ) {
		return;
	}
	wp_enqueue_style(
		'bizstudio-promo-bar',
		BIZSTUDIO_URI . '/assets/src/css/components/promo-bar.css',
		[ 'bizstudio-main' ],
		BIZSTUDIO_VERSION
	);
} );
