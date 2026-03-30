<?php
/**
 * Shipping Progress Bar
 *
 * Multi-step shipping progress bar with configurable thresholds,
 * animated fill, confetti effects, and live AJAX updates via cart fragments.
 *
 * @package BizStudio
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/* ============================================================
   Customizer Settings
   ============================================================ */

add_action( 'customize_register', function ( WP_Customize_Manager $wp_customize ) {

	$wp_customize->add_section( 'bizstudio_shipping_bar', [
		'title'    => __( 'Barra Spedizione', 'bizstudio' ),
		'panel'    => 'bizstudio_panel',
		'priority' => 20,
	] );

	// Enable / Disable
	$wp_customize->add_setting( 'bizstudio_shipping_bar_enabled', [
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'bizstudio_shipping_bar_enabled', [
		'label'   => __( 'Abilita barra spedizione', 'bizstudio' ),
		'section' => 'bizstudio_shipping_bar',
		'type'    => 'checkbox',
	] );

	// --- Step 1 ---
	$wp_customize->add_setting( 'bizstudio_shipping_bar_step1_amount', [
		'default'           => 29,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'bizstudio_shipping_bar_step1_amount', [
		'label'       => __( 'Step 1 — Soglia (€)', 'bizstudio' ),
		'section'     => 'bizstudio_shipping_bar',
		'type'        => 'number',
		'input_attrs' => [ 'min' => 1, 'step' => 1 ],
	] );

	$wp_customize->add_setting( 'bizstudio_shipping_bar_step1_label', [
		'default'           => 'Sconto 50% spedizione',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'bizstudio_shipping_bar_step1_label', [
		'label'   => __( 'Step 1 — Etichetta', 'bizstudio' ),
		'section' => 'bizstudio_shipping_bar',
		'type'    => 'text',
	] );

	$wp_customize->add_setting( 'bizstudio_shipping_bar_step1_color', [
		'default'           => '#F59E0B',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'bizstudio_shipping_bar_step1_color', [
		'label'   => __( 'Step 1 — Colore barra', 'bizstudio' ),
		'section' => 'bizstudio_shipping_bar',
	] ) );

	// --- Step 2 ---
	$wp_customize->add_setting( 'bizstudio_shipping_bar_step2_amount', [
		'default'           => 49,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'bizstudio_shipping_bar_step2_amount', [
		'label'       => __( 'Step 2 — Soglia (€)', 'bizstudio' ),
		'section'     => 'bizstudio_shipping_bar',
		'type'        => 'number',
		'input_attrs' => [ 'min' => 1, 'step' => 1 ],
	] );

	$wp_customize->add_setting( 'bizstudio_shipping_bar_step2_label', [
		'default'           => 'Spedizione gratuita',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'bizstudio_shipping_bar_step2_label', [
		'label'   => __( 'Step 2 — Etichetta', 'bizstudio' ),
		'section' => 'bizstudio_shipping_bar',
		'type'    => 'text',
	] );

	$wp_customize->add_setting( 'bizstudio_shipping_bar_step2_color', [
		'default'           => '#10B981',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'bizstudio_shipping_bar_step2_color', [
		'label'   => __( 'Step 2 — Colore barra', 'bizstudio' ),
		'section' => 'bizstudio_shipping_bar',
	] ) );

	// Completion message
	$wp_customize->add_setting( 'bizstudio_shipping_bar_complete_msg', [
		'default'           => 'Complimenti! Hai la spedizione gratuita!',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'bizstudio_shipping_bar_complete_msg', [
		'label'   => __( 'Messaggio completamento', 'bizstudio' ),
		'section' => 'bizstudio_shipping_bar',
		'type'    => 'text',
	] );
} );


/* ============================================================
   Helper: get shipping bar configuration
   ============================================================ */

/**
 * Retrieve all shipping bar steps and settings.
 *
 * @return array{enabled:bool, steps:array, complete_msg:string}
 */
function bizstudio_shipping_bar_config(): array {
	$enabled = (bool) get_theme_mod( 'bizstudio_shipping_bar_enabled', true );

	$steps = [
		[
			'amount' => (float) get_theme_mod( 'bizstudio_shipping_bar_step1_amount', 29 ),
			'label'  => get_theme_mod( 'bizstudio_shipping_bar_step1_label', 'Sconto 50% spedizione' ),
			'color'  => get_theme_mod( 'bizstudio_shipping_bar_step1_color', '#F59E0B' ),
		],
		[
			'amount' => (float) get_theme_mod( 'bizstudio_shipping_bar_step2_amount', 49 ),
			'label'  => get_theme_mod( 'bizstudio_shipping_bar_step2_label', 'Spedizione gratuita' ),
			'color'  => get_theme_mod( 'bizstudio_shipping_bar_step2_color', '#10B981' ),
		],
	];

	// Ensure steps are sorted by amount ascending
	usort( $steps, fn( $a, $b ) => $a['amount'] <=> $b['amount'] );

	return [
		'enabled'      => $enabled,
		'steps'        => $steps,
		'complete_msg' => get_theme_mod( 'bizstudio_shipping_bar_complete_msg', 'Complimenti! Hai la spedizione gratuita!' ),
	];
}


/* ============================================================
   Helper: calculate progress data
   ============================================================ */

/**
 * Calculate the current progress percentage and active step index.
 *
 * @return array{
 *   cart_total: float,
 *   percentage: float,
 *   active_step: int,
 *   all_complete: bool,
 *   next_step: array|null,
 *   remaining: float,
 *   fill_color: string,
 *   steps: array
 * }
 */
function bizstudio_shipping_bar_progress(): array {
	$config    = bizstudio_shipping_bar_config();
	$steps     = $config['steps'];
	$max       = end( $steps )['amount'];
	$cart      = WC()->cart;
	$subtotal  = $cart ? (float) $cart->get_subtotal() : 0.0;

	// Calculate percentage relative to the max step
	$percentage   = $max > 0 ? min( ( $subtotal / $max ) * 100, 100 ) : 0;
	$active_step  = -1; // -1 means no step reached
	$fill_color   = $steps[0]['color']; // default to first step color
	$next_step    = null;
	$all_complete = false;

	// Determine which steps have been reached
	foreach ( $steps as $i => $step ) {
		if ( $subtotal >= $step['amount'] ) {
			$active_step = $i;
			$fill_color  = $step['color'];
		}
	}

	// Determine next step (if any)
	if ( $active_step < count( $steps ) - 1 ) {
		$next_index = $active_step + 1;
		$next_step  = $steps[ $next_index ];
	} else if ( $active_step === count( $steps ) - 1 ) {
		$all_complete = true;
	}

	// Calculate remaining amount to next step
	$remaining = 0.0;
	if ( $next_step ) {
		$remaining = $next_step['amount'] - $subtotal;
	}

	return [
		'cart_total'   => $subtotal,
		'percentage'   => round( $percentage, 1 ),
		'active_step'  => $active_step,
		'all_complete' => $all_complete,
		'next_step'    => $next_step,
		'remaining'    => max( 0, round( $remaining, 2 ) ),
		'fill_color'   => $fill_color,
		'steps'        => $steps,
	];
}


/* ============================================================
   Render function
   ============================================================ */

/**
 * Render the shipping progress bar HTML.
 *
 * Outputs a self-contained block wrapped in `.biz-shipping-bar-wrap`
 * for cart fragment replacement.
 *
 * @param bool $echo Whether to echo or return the HTML.
 * @return string HTML output (when $echo is false).
 */
function bizstudio_render_shipping_bar( bool $echo = true ): string {
	$config = bizstudio_shipping_bar_config();

	if ( ! $config['enabled'] ) {
		$html = '<div class="biz-shipping-bar-wrap"></div>';
		if ( $echo ) {
			echo $html;
		}
		return $html;
	}

	$progress      = bizstudio_shipping_bar_progress();
	$steps         = $progress['steps'];
	$max_amount    = end( $steps )['amount'];
	$percentage    = $progress['percentage'];
	$active_step   = $progress['active_step'];
	$all_complete  = $progress['all_complete'];
	$next_step     = $progress['next_step'];
	$remaining     = $progress['remaining'];
	$fill_color    = $progress['fill_color'];
	$complete_msg  = $config['complete_msg'];

	// Build the message
	if ( $all_complete ) {
		$message = $complete_msg;
	} elseif ( $next_step ) {
		$formatted_remaining = number_format( $remaining, 2, ',', '.' );
		/* translators: %1$s = remaining amount, %2$s = step label */
		$message = sprintf(
			__( 'Aggiungi %1$s&euro; per ottenere: %2$s!', 'bizstudio' ),
			$formatted_remaining,
			$next_step['label']
		);
	} else {
		$formatted_remaining = number_format( $steps[0]['amount'] - $progress['cart_total'], 2, ',', '.' );
		$message = sprintf(
			__( 'Aggiungi %1$s&euro; per ottenere: %2$s!', 'bizstudio' ),
			$formatted_remaining,
			$steps[0]['label']
		);
	}

	// JSON data for JS module
	$js_data = wp_json_encode( [
		'activeStep'  => $active_step,
		'allComplete' => $all_complete,
		'percentage'  => $percentage,
	] );

	ob_start();
	?>
	<div class="biz-shipping-bar-wrap" data-shipping-bar='<?php echo esc_attr( $js_data ); ?>'>
		<div class="biz-shipping-bar<?php echo $all_complete ? ' biz-shipping-bar--complete' : ''; ?>"
			style="--sb-fill-color: <?php echo esc_attr( $fill_color ); ?>;">

			<?php /* Message */ ?>
			<div class="biz-shipping-bar__message">
				<?php if ( $all_complete ) : ?>
					<span class="biz-shipping-bar__complete">
						<svg class="biz-shipping-bar__check-icon" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
							<circle cx="8" cy="8" r="8" fill="currentColor" opacity="0.15"/>
							<path d="M5 8.5L7 10.5L11 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						<?php echo esc_html( $complete_msg ); ?>
					</span>
				<?php else : ?>
					<span class="biz-shipping-bar__next-msg"><?php echo wp_kses_post( $message ); ?></span>
				<?php endif; ?>
			</div>

			<?php /* Track */ ?>
			<div class="biz-shipping-bar__track">
				<div class="biz-shipping-bar__fill" style="width: <?php echo esc_attr( $percentage ); ?>%;"></div>

				<?php /* Step markers */ ?>
				<div class="biz-shipping-bar__steps">
					<?php foreach ( $steps as $i => $step ) :
						$step_position = $max_amount > 0 ? ( $step['amount'] / $max_amount ) * 100 : 0;
						$reached       = $active_step >= $i;
					?>
						<div class="biz-shipping-bar__step<?php echo $reached ? ' biz-shipping-bar__step--reached' : ''; ?>"
							style="left: <?php echo esc_attr( $step_position ); ?>%; --step-color: <?php echo esc_attr( $step['color'] ); ?>;"
							data-step="<?php echo esc_attr( $i ); ?>"
							title="<?php echo esc_attr( $step['label'] . ' — ' . number_format( $step['amount'], 0, ',', '.' ) . '€' ); ?>">
							<?php if ( $reached ) : ?>
								<svg class="biz-shipping-bar__step-check" width="10" height="10" viewBox="0 0 10 10" fill="none" aria-hidden="true">
									<path d="M2.5 5.5L4.5 7.5L7.5 3.5" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							<?php endif; ?>
						</div>
						<span class="biz-shipping-bar__step-label<?php echo $reached ? ' biz-shipping-bar__step-label--reached' : ''; ?>"
							style="left: <?php echo esc_attr( $step_position ); ?>%;">
							<?php echo esc_html( number_format( $step['amount'], 0, ',', '.' ) ); ?>&euro;
						</span>
					<?php endforeach; ?>
				</div>
			</div>

			<?php /* Confetti container (populated by JS) */ ?>
			<div class="biz-shipping-bar__confetti" aria-hidden="true"></div>
		</div>
	</div>
	<?php
	$html = ob_get_clean();

	if ( $echo ) {
		echo $html;
	}

	return $html;
}


/* ============================================================
   Cart Fragments Integration
   ============================================================ */

add_filter( 'woocommerce_add_to_cart_fragments', function ( array $fragments ): array {
	$config = bizstudio_shipping_bar_config();
	if ( ! $config['enabled'] ) {
		$fragments['.biz-shipping-bar-wrap'] = '<div class="biz-shipping-bar-wrap"></div>';
		return $fragments;
	}

	$fragments['.biz-shipping-bar-wrap'] = bizstudio_render_shipping_bar( false );

	return $fragments;
} );


/* ============================================================
   Enqueue assets
   ============================================================ */

add_action( 'wp_enqueue_scripts', function () {
	$config = bizstudio_shipping_bar_config();
	if ( ! $config['enabled'] ) {
		return;
	}

	$ver = BIZSTUDIO_VERSION;

	// CSS
	wp_enqueue_style(
		'bizstudio-shipping-bar',
		BIZSTUDIO_URI . '/assets/src/css/components/shipping-bar.css',
		[ 'bizstudio-main' ],
		$ver
	);

	// JS
	wp_enqueue_script(
		'bizstudio-shipping-bar',
		BIZSTUDIO_URI . '/assets/src/js/modules/shipping-bar.js',
		[ 'bizstudio-main' ],
		$ver,
		[ 'strategy' => 'defer', 'in_footer' => true ]
	);
}, 20 );
