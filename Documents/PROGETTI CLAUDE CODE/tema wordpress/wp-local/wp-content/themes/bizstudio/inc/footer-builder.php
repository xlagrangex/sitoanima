<?php
/**
 * Footer Builder
 *
 * Customizer-based footer builder with presets, pre-footer section,
 * payment icons, and social links.
 *
 * @package BizStudio
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/* ============================================================
   1. Customizer Settings
   ============================================================ */

add_action( 'customize_register', function ( WP_Customize_Manager $wp_customize ) {

	// Section: Footer
	$wp_customize->add_section( 'bizstudio_footer', [
		'title'    => __( 'Footer', 'bizstudio' ),
		'panel'    => 'bizstudio_panel',
		'priority' => 50,
	] );

	/* ── Footer Preset ────────────────────────── */
	$wp_customize->add_setting( 'bizstudio_footer_preset', [
		'default'           => 'default',
		'sanitize_callback' => function ( $value ) {
			$valid = [ 'default', 'centered', 'two-col', 'three-col', 'asymmetric' ];
			return in_array( $value, $valid, true ) ? $value : 'default';
		},
		'transport'         => 'refresh',
	] );

	$wp_customize->add_control( 'bizstudio_footer_preset', [
		'label'   => __( 'Layout Footer', 'bizstudio' ),
		'section' => 'bizstudio_footer',
		'type'    => 'select',
		'choices' => [
			'default'    => __( 'Predefinito (4 colonne)', 'bizstudio' ),
			'centered'   => __( 'Centrato', 'bizstudio' ),
			'two-col'    => __( '2 colonne', 'bizstudio' ),
			'three-col'  => __( '3 colonne', 'bizstudio' ),
			'asymmetric' => __( 'Asimmetrico (2fr 1fr 1fr)', 'bizstudio' ),
		],
	] );

	/* ── Pre-Footer Enable ────────────────────── */
	$wp_customize->add_setting( 'bizstudio_footer_pre_enable', [
		'default'           => false,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );

	$wp_customize->add_control( 'bizstudio_footer_pre_enable', [
		'label'   => __( 'Mostra Pre-Footer', 'bizstudio' ),
		'section' => 'bizstudio_footer',
		'type'    => 'checkbox',
	] );

	/* ── Pre-Footer Feature Boxes (3) ─────────── */
	$features_defaults = [
		1 => [
			'title' => __( 'Spedizione gratuita', 'bizstudio' ),
			'text'  => __( 'Per ordini superiori a 49€', 'bizstudio' ),
		],
		2 => [
			'title' => __( 'Reso facile in 14 giorni', 'bizstudio' ),
			'text'  => __( 'Rimborso garantito', 'bizstudio' ),
		],
		3 => [
			'title' => __( 'Pagamento 100% sicuro', 'bizstudio' ),
			'text'  => __( 'Crittografia SSL avanzata', 'bizstudio' ),
		],
	];

	for ( $i = 1; $i <= 3; $i++ ) {
		// Title
		$wp_customize->add_setting( "bizstudio_footer_feature_{$i}_title", [
			'default'           => $features_defaults[ $i ]['title'],
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		] );

		$wp_customize->add_control( "bizstudio_footer_feature_{$i}_title", [
			/* translators: %d: feature number */
			'label'   => sprintf( __( 'Feature %d — Titolo', 'bizstudio' ), $i ),
			'section' => 'bizstudio_footer',
			'type'    => 'text',
		] );

		// Text
		$wp_customize->add_setting( "bizstudio_footer_feature_{$i}_text", [
			'default'           => $features_defaults[ $i ]['text'],
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		] );

		$wp_customize->add_control( "bizstudio_footer_feature_{$i}_text", [
			/* translators: %d: feature number */
			'label'   => sprintf( __( 'Feature %d — Testo', 'bizstudio' ), $i ),
			'section' => 'bizstudio_footer',
			'type'    => 'text',
		] );
	}

	/* ── Footer BG Color ──────────────────────── */
	$wp_customize->add_setting( 'bizstudio_footer_bg_color', [
		'default'           => '',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	] );

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'bizstudio_footer_bg_color', [
		'label'       => __( 'Colore di sfondo Footer', 'bizstudio' ),
		'description' => __( 'Lascia vuoto per usare il colore di superficie del tema.', 'bizstudio' ),
		'section'     => 'bizstudio_footer',
	] ) );

	/* ── Footer Text Color ────────────────────── */
	$wp_customize->add_setting( 'bizstudio_footer_text_color', [
		'default'           => '',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	] );

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'bizstudio_footer_text_color', [
		'label'       => __( 'Colore testo Footer', 'bizstudio' ),
		'description' => __( 'Lascia vuoto per usare il colore testo del tema.', 'bizstudio' ),
		'section'     => 'bizstudio_footer',
	] ) );

	/* ── Copyright Text ───────────────────────── */
	$wp_customize->add_setting( 'bizstudio_footer_copyright', [
		'default'           => __( '© {year} {site_name}. Tutti i diritti riservati.', 'bizstudio' ),
		'sanitize_callback' => 'wp_kses_post',
		'transport'         => 'postMessage',
	] );

	$wp_customize->add_control( 'bizstudio_footer_copyright', [
		'label'       => __( 'Testo Copyright', 'bizstudio' ),
		'description' => __( 'Usa {year} per l\'anno corrente e {site_name} per il nome del sito.', 'bizstudio' ),
		'section'     => 'bizstudio_footer',
		'type'        => 'text',
	] );

	/* ── Show Payment Icons ───────────────────── */
	$wp_customize->add_setting( 'bizstudio_footer_payment_icons', [
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );

	$wp_customize->add_control( 'bizstudio_footer_payment_icons', [
		'label'   => __( 'Mostra icone pagamento', 'bizstudio' ),
		'section' => 'bizstudio_footer',
		'type'    => 'checkbox',
	] );

	/* ── Social Links ─────────────────────────── */
	$socials = [
		'facebook'  => 'Facebook',
		'instagram' => 'Instagram',
		'x'         => 'X (Twitter)',
		'linkedin'  => 'LinkedIn',
		'tiktok'    => 'TikTok',
	];

	foreach ( $socials as $key => $label ) {
		$wp_customize->add_setting( "bizstudio_footer_social_{$key}", [
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		] );

		$wp_customize->add_control( "bizstudio_footer_social_{$key}", [
			/* translators: %s: social network name */
			'label'   => sprintf( __( 'Social: %s URL', 'bizstudio' ), $label ),
			'section' => 'bizstudio_footer',
			'type'    => 'url',
		] );
	}

	/* ── Selective Refresh Partials ───────────── */
	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial( 'bizstudio_footer_copyright', [
			'selector'        => '.biz-footer__copy',
			'render_callback' => function () {
				echo wp_kses_post( bizstudio_get_copyright_text() );
			},
		] );
	}
} );

/* Live-preview JS for the Customizer */
add_action( 'customize_preview_init', function () {
	wp_add_inline_script( 'customize-preview', "
		( function( $ ) {
			wp.customize( 'bizstudio_footer_bg_color', function( value ) {
				value.bind( function( to ) {
					$( '.biz-footer' ).css( 'background-color', to || '' );
				} );
			} );
			wp.customize( 'bizstudio_footer_text_color', function( value ) {
				value.bind( function( to ) {
					$( '.biz-footer' ).css( 'color', to || '' );
				} );
			} );
			wp.customize( 'bizstudio_footer_copyright', function( value ) {
				value.bind( function( to ) {
					var text = to
						.replace( '{year}', new Date().getFullYear() )
						.replace( '{site_name}', '" . esc_js( get_bloginfo( 'name' ) ) . "' );
					$( '.biz-footer__copy' ).html( text );
				} );
			} );
		} )( jQuery );
	" );
} );

/* ============================================================
   2. Configuration Helper
   ============================================================ */

/**
 * Get all footer configuration values.
 *
 * @return array Associative array of footer settings.
 */
function bizstudio_get_footer_config() {
	$config = [
		'preset'        => get_theme_mod( 'bizstudio_footer_preset', 'default' ),
		'pre_footer'    => (bool) get_theme_mod( 'bizstudio_footer_pre_enable', false ),
		'bg_color'      => get_theme_mod( 'bizstudio_footer_bg_color', '' ),
		'text_color'    => get_theme_mod( 'bizstudio_footer_text_color', '' ),
		'copyright'     => get_theme_mod( 'bizstudio_footer_copyright', __( '© {year} {site_name}. Tutti i diritti riservati.', 'bizstudio' ) ),
		'payment_icons' => (bool) get_theme_mod( 'bizstudio_footer_payment_icons', true ),
		'features'      => [],
		'social'        => [],
	];

	// Features
	$defaults = [
		1 => [ __( 'Spedizione gratuita', 'bizstudio' ), __( 'Per ordini superiori a 49€', 'bizstudio' ) ],
		2 => [ __( 'Reso facile in 14 giorni', 'bizstudio' ), __( 'Rimborso garantito', 'bizstudio' ) ],
		3 => [ __( 'Pagamento 100% sicuro', 'bizstudio' ), __( 'Crittografia SSL avanzata', 'bizstudio' ) ],
	];

	for ( $i = 1; $i <= 3; $i++ ) {
		$config['features'][ $i ] = [
			'title' => get_theme_mod( "bizstudio_footer_feature_{$i}_title", $defaults[ $i ][0] ),
			'text'  => get_theme_mod( "bizstudio_footer_feature_{$i}_text", $defaults[ $i ][1] ),
		];
	}

	// Social links
	$socials = [ 'facebook', 'instagram', 'x', 'linkedin', 'tiktok' ];
	foreach ( $socials as $key ) {
		$url = get_theme_mod( "bizstudio_footer_social_{$key}", '' );
		if ( ! empty( $url ) ) {
			$config['social'][ $key ] = $url;
		}
	}

	return $config;
}

/**
 * Parse the copyright text with dynamic placeholders.
 *
 * @return string Parsed copyright text.
 */
function bizstudio_get_copyright_text() {
	$config = bizstudio_get_footer_config();
	$text   = $config['copyright'];

	$text = str_replace( '{year}', date( 'Y' ), $text );
	$text = str_replace( '{site_name}', get_bloginfo( 'name' ), $text );

	return $text;
}

/* ============================================================
   3. Render Functions
   ============================================================ */

/**
 * Render the pre-footer section with 3 feature boxes.
 *
 * Each box has an SVG icon (truck, refresh-ccw, shield),
 * a title, and descriptive text.
 */
function bizstudio_render_pre_footer() {
	$config = bizstudio_get_footer_config();

	if ( ! $config['pre_footer'] ) {
		return;
	}

	$icons = [
		1 => '<svg class="biz-pre-footer__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 3h15v13H1z"/><path d="M16 8h4l3 4v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
		2 => '<svg class="biz-pre-footer__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="1 4 1 10 7 10"/><polyline points="23 20 23 14 17 14"/><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"/></svg>',
		3 => '<svg class="biz-pre-footer__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
	];

	?>
	<div class="biz-pre-footer" role="complementary" aria-label="<?php esc_attr_e( 'Vantaggi', 'bizstudio' ); ?>">
		<div class="biz-container">
			<div class="biz-pre-footer__grid">
				<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
					<div class="biz-pre-footer__box">
						<?php echo $icons[ $i ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — static SVG ?>
						<div class="biz-pre-footer__content">
							<h4 class="biz-pre-footer__title"><?php echo esc_html( $config['features'][ $i ]['title'] ); ?></h4>
							<p class="biz-pre-footer__text"><?php echo esc_html( $config['features'][ $i ]['text'] ); ?></p>
						</div>
					</div>
				<?php endfor; ?>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Render payment method SVG icons.
 *
 * Displays Visa, Mastercard, PayPal, Amex, and Apple Pay icons
 * in a horizontal row.
 */
function bizstudio_render_payment_icons() {
	$config = bizstudio_get_footer_config();

	if ( ! $config['payment_icons'] ) {
		return;
	}

	$icons = [
		'visa' => [
			'label' => 'Visa',
			'svg'   => '<svg viewBox="0 0 48 30" fill="none" aria-hidden="true"><rect width="48" height="30" rx="4" fill="#fff" stroke="#E5E5EE"/><path d="M19.5 19.5h-3.2L18.1 11h3.2l-1.8 8.5zm8.4-8.2c-.6-.3-1.6-.5-2.8-.5-3.1 0-5.3 1.6-5.3 4 0 1.7 1.6 2.7 2.8 3.3 1.2.6 1.6 1 1.6 1.5 0 .8-1 1.2-1.9 1.2-1.2 0-1.9-.2-2.9-.6l-.4-.2-.4 2.5c.7.3 2.1.6 3.5.6 3.3 0 5.4-1.6 5.4-4.1 0-1.4-.8-2.4-2.7-3.3-1.1-.6-1.8-.9-1.8-1.5 0-.5.6-1 1.8-1 1-.1 1.8.2 2.4.5l.3.1.4-2.5zm8.1-.3h-2.4c-.7 0-1.3.2-1.6 1L28.3 19.5h3.3l.7-1.8h4l.4 1.8H40L37.5 11h-1.5zm-3.4 5.5l1.4-3.7.6 3.7h-2zM17.5 11l-3 5.8-.3-1.6c-.6-1.8-2.3-3.8-4.2-4.8l2.8 8.1h3.3L21 11h-3.5z" fill="#1A1F71"/><path d="M11.8 11H7l-.1.3c3.9 1 6.5 3.3 7.6 6.1l-1.1-5.5c-.2-.8-.7-1-1.6-.9z" fill="#F7A600"/></svg>',
		],
		'mastercard' => [
			'label' => 'Mastercard',
			'svg'   => '<svg viewBox="0 0 48 30" fill="none" aria-hidden="true"><rect width="48" height="30" rx="4" fill="#fff" stroke="#E5E5EE"/><circle cx="19" cy="15" r="7" fill="#EB001B"/><circle cx="29" cy="15" r="7" fill="#F79E1B"/><path d="M24 9.8a7 7 0 0 1 2.6 5.2A7 7 0 0 1 24 20.2 7 7 0 0 1 21.4 15 7 7 0 0 1 24 9.8z" fill="#FF5F00"/></svg>',
		],
		'paypal' => [
			'label' => 'PayPal',
			'svg'   => '<svg viewBox="0 0 48 30" fill="none" aria-hidden="true"><rect width="48" height="30" rx="4" fill="#fff" stroke="#E5E5EE"/><path d="M20.2 7h5.5c2.7 0 4.5 1.4 4.2 3.8-.5 3.5-2.7 5.4-5.8 5.4h-1.5c-.4 0-.8.3-.9.8l-.7 4.2c-.1.3-.3.5-.6.5h-2.8c-.3 0-.5-.3-.4-.6L20.2 7z" fill="#003087"/><path d="M17.2 9.5h5.5c2.7 0 4.5 1.4 4.2 3.8-.5 3.5-2.7 5.4-5.8 5.4h-1.5c-.4 0-.8.3-.9.8l-.7 4.2c-.1.3-.3.5-.6.5h-2.8c-.3 0-.5-.3-.4-.6l3-13.5c.1-.4.5-.6 1-.6z" fill="#0070E0"/></svg>',
		],
		'amex' => [
			'label' => 'American Express',
			'svg'   => '<svg viewBox="0 0 48 30" fill="none" aria-hidden="true"><rect width="48" height="30" rx="4" fill="#016FD0"/><path d="M10 11l-3.5 8h3l.5-1.2h1.1l.5 1.2h3.5v-.9l.3.9h1.8l.3-1v1h8.6l1-.5 1 .5h2.8l-3.5-4 3.5-4h-2.7l-1 .5-1-.5h-9.3l-.7 1.5-.7-1.5H12v.9l-.4-.9H10zm1.1 1.3h1.5l1.7 3.8v-3.8h1.6l1.3 2.7 1.2-2.7h1.6v5.4h-1l-.1-4.2-1.4 3h-.9l-1.5-3v4.2h-2l-.5-1.2h-2.7l-.5 1.2h-1.1l2.3-5.4zm1 1.5l-.8 2h1.6l-.8-2zm11.3-1.5h4l1.2 1.4 1.3-1.4h1.2l-1.9 2.7 1.9 2.7h-1.3l-1.2-1.4-1.3 1.4h-4v-5.4zm1.1 1v1.1h2.2v1h-2.2v1.2h2.5l1.1-1.6-1.1-1.7h-2.5z" fill="#fff"/></svg>',
		],
		'apple-pay' => [
			'label' => 'Apple Pay',
			'svg'   => '<svg viewBox="0 0 48 30" fill="none" aria-hidden="true"><rect width="48" height="30" rx="4" fill="#fff" stroke="#E5E5EE"/><path d="M16.4 10.3c.4-.5.7-1.2.6-1.9-.6 0-1.3.4-1.7.9-.4.4-.7 1.1-.6 1.8.7.1 1.3-.3 1.7-.8zm.6 1c-.9-.1-1.7.5-2.2.5-.4 0-1.1-.5-1.9-.5-1 0-1.9.6-2.4 1.4-1 1.8-.3 4.4.7 5.8.5.7 1.1 1.5 1.8 1.5.7 0 1-.5 1.9-.5.9 0 1.1.5 1.9.5.8 0 1.3-.7 1.8-1.5.5-.8.8-1.6.8-1.6 0 0-1.4-.6-1.4-2.2 0-1.4 1.1-2 1.2-2.1-.7-1-1.7-1.2-2.2-1.3z" fill="#000"/><path d="M24.6 9.3c2.3 0 3.9 1.6 3.9 3.9 0 2.3-1.6 3.9-4 3.9h-2.5v4h-1.8V9.3h4.4zm-2.6 6.3h2.1c1.6 0 2.5-.9 2.5-2.4 0-1.5-.9-2.4-2.5-2.4H22v4.8zm7 2.1c0-1.5 1.2-2.4 3.2-2.5l2.4-.1v-.7c0-1-.6-1.5-1.7-1.5-1 0-1.6.5-1.7 1.2h-1.7c.1-1.5 1.3-2.7 3.5-2.7 2.1 0 3.4 1.1 3.4 2.9v6h-1.7v-1.4h0c-.5 1-1.6 1.6-2.7 1.6-1.7 0-3-1.1-3-2.8zm5.6-.7v-.7l-2.1.1c-1.1.1-1.7.5-1.7 1.3 0 .8.7 1.3 1.6 1.3 1.2 0 2.2-.8 2.2-2zm3.2 5.1v-1.4c.1 0 .4.1.7.1 1 0 1.6-.4 1.9-1.5l.2-.6-3.2-8.8h1.9l2.2 7.2h0l2.2-7.2h1.9l-3.3 9.3c-.8 2.1-1.6 2.8-3.5 2.8-.2.1-.8.1-1 .1z" fill="#000"/></svg>',
		],
	];

	?>
	<div class="biz-payment-icons" aria-label="<?php esc_attr_e( 'Metodi di pagamento accettati', 'bizstudio' ); ?>">
		<?php foreach ( $icons as $method => $data ) : ?>
			<span class="biz-payment-icons__item" title="<?php echo esc_attr( $data['label'] ); ?>">
				<?php echo $data['svg']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — static SVG ?>
			</span>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Render social media icon links.
 *
 * Displays circular icon buttons for each configured social platform.
 */
function bizstudio_render_social_links() {
	$config = bizstudio_get_footer_config();

	if ( empty( $config['social'] ) ) {
		return;
	}

	$icons = [
		'facebook'  => [
			'label' => 'Facebook',
			'svg'   => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047v-2.66c0-3.025 1.792-4.697 4.533-4.697 1.312 0 2.686.236 2.686.236v2.971h-1.513c-1.49 0-1.956.93-1.956 1.886v2.264h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/></svg>',
		],
		'instagram' => [
			'label' => 'Instagram',
			'svg'   => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 1.17.054 1.97.24 2.43.403a4.088 4.088 0 0 1 1.523.99 4.088 4.088 0 0 1 .99 1.524c.163.46.35 1.26.403 2.43.058 1.265.07 1.645.07 4.849s-.012 3.584-.07 4.85c-.054 1.17-.24 1.97-.403 2.43a4.088 4.088 0 0 1-.99 1.523 4.088 4.088 0 0 1-1.524.99c-.46.163-1.26.35-2.43.403-1.265.058-1.645.07-4.849.07s-3.584-.012-4.85-.07c-1.17-.054-1.97-.24-2.43-.403a4.088 4.088 0 0 1-1.523-.99 4.088 4.088 0 0 1-.99-1.524c-.163-.46-.35-1.26-.403-2.43C2.175 15.584 2.163 15.204 2.163 12s.012-3.584.07-4.85c.054-1.17.24-1.97.403-2.43a4.088 4.088 0 0 1 .99-1.523A4.088 4.088 0 0 1 5.15 2.207c.46-.163 1.26-.35 2.43-.403C8.845 2.175 9.225 2.163 12 2.163zM12 0C8.741 0 8.333.014 7.053.072 5.775.131 4.902.333 4.14.63a5.876 5.876 0 0 0-2.126 1.384A5.876 5.876 0 0 0 .63 4.14C.333 4.902.131 5.775.072 7.053.014 8.333 0 8.741 0 12s.014 3.667.072 4.947c.059 1.278.261 2.15.558 2.913a5.876 5.876 0 0 0 1.384 2.126A5.876 5.876 0 0 0 4.14 23.37c.763.297 1.635.499 2.913.558C8.333 23.986 8.741 24 12 24s3.667-.014 4.947-.072c1.278-.059 2.15-.261 2.913-.558a5.876 5.876 0 0 0 2.126-1.384 5.876 5.876 0 0 0 1.384-2.126c.297-.763.499-1.635.558-2.913C23.986 15.667 24 15.259 24 12s-.014-3.667-.072-4.947c-.059-1.278-.261-2.15-.558-2.913a5.876 5.876 0 0 0-1.384-2.126A5.876 5.876 0 0 0 19.86.63C19.098.333 18.225.131 16.947.072 15.667.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>',
		],
		'x'         => [
			'label' => 'X',
			'svg'   => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
		],
		'linkedin'  => [
			'label' => 'LinkedIn',
			'svg'   => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 1 1 0-4.125 2.062 2.062 0 0 1 0 4.125zM6.89 20.452H3.58V9h3.31v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
		],
		'tiktok'    => [
			'label' => 'TikTok',
			'svg'   => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>',
		],
	];

	?>
	<nav class="biz-social-links" aria-label="<?php esc_attr_e( 'Social media', 'bizstudio' ); ?>">
		<?php foreach ( $config['social'] as $key => $url ) : ?>
			<?php if ( isset( $icons[ $key ] ) ) : ?>
				<a href="<?php echo esc_url( $url ); ?>"
				   class="biz-social-links__item"
				   target="_blank"
				   rel="noopener noreferrer"
				   aria-label="<?php echo esc_attr( $icons[ $key ]['label'] ); ?>">
					<?php echo $icons[ $key ]['svg']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — static SVG ?>
				</a>
			<?php endif; ?>
		<?php endforeach; ?>
	</nav>
	<?php
}

/* ============================================================
   4. Body Class Filter
   ============================================================ */

add_filter( 'body_class', function ( $classes ) {
	$preset = get_theme_mod( 'bizstudio_footer_preset', 'default' );
	if ( 'default' !== $preset ) {
		$classes[] = 'biz-footer-preset--' . sanitize_html_class( $preset );
	}

	if ( get_theme_mod( 'bizstudio_footer_pre_enable', false ) ) {
		$classes[] = 'has-pre-footer';
	}

	return $classes;
} );

/* ============================================================
   5. Inline CSS Variables (wp_head)
   ============================================================ */

add_action( 'wp_head', function () {
	$bg_color   = get_theme_mod( 'bizstudio_footer_bg_color', '' );
	$text_color = get_theme_mod( 'bizstudio_footer_text_color', '' );

	if ( empty( $bg_color ) && empty( $text_color ) ) {
		return;
	}

	$css = '.biz-footer {';
	if ( ! empty( $bg_color ) ) {
		$css .= 'background-color:' . esc_attr( $bg_color ) . ';';
	}
	if ( ! empty( $text_color ) ) {
		$css .= 'color:' . esc_attr( $text_color ) . ';';
	}
	$css .= '}';

	if ( ! empty( $text_color ) ) {
		$css .= '.biz-footer__title,.biz-footer-widget__title,.biz-footer__copy,.biz-footer__menu li a,.biz-footer__text,.biz-pre-footer__title,.biz-pre-footer__text{color:' . esc_attr( $text_color ) . ';}';
	}

	echo '<style id="bizstudio-footer-inline">' . $css . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}, 20 );

/* ============================================================
   6. Enqueue Footer Builder CSS
   ============================================================ */

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'bizstudio-footer-builder',
		BIZSTUDIO_URI . '/assets/src/css/components/footer-builder.css',
		[ 'bizstudio-main' ],
		BIZSTUDIO_VERSION
	);
} );
