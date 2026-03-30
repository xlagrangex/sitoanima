<?php
/**
 * Color Swatches for Variable Products
 *
 * Replaces the <select> dropdown for color attributes with
 * clickable color circles (swatches).
 *
 * @package BizStudio
 */

defined( 'ABSPATH' ) || exit;

/**
 * Override variation attribute HTML for color swatches.
 */
add_filter( 'woocommerce_dropdown_variation_attribute_options_html', 'bizstudio_swatches_html', 10, 2 );

function bizstudio_swatches_html( string $html, array $args ): string {
	$attribute = $args['attribute'];
	$product   = $args['product'];

	// Only apply to color-type attributes
	if ( ! bizstudio_is_color_attribute( $attribute ) ) {
		return $html;
	}

	$options   = $args['options'];
	$selected  = $args['selected'];
	$name      = 'attribute_' . sanitize_title( $attribute );

	if ( empty( $options ) ) {
		return $html;
	}

	// Build swatches HTML
	$swatches  = '<div class="biz-swatches" data-attribute="' . esc_attr( $name ) . '">';

	// Keep the original select but hide it (WC JS needs it for variation logic)
	$hidden_html = preg_replace( '/class="([^"]*)"/', 'class="$1 biz-swatches__select-hidden"', $html, 1 );
	$hidden_html = str_replace( '<select', '<select style="position:absolute;opacity:0;pointer-events:none;height:0;width:0;overflow:hidden"', $hidden_html );
	$swatches .= $hidden_html;

	foreach ( $options as $option ) {
		$term = get_term_by( 'slug', $option, $attribute );
		if ( ! $term ) continue;

		$hex   = get_term_meta( $term->term_id, 'bizstudio_color_hex', true );
		$label = $term->name;
		$slug  = $term->slug;

		// Fallback: try to guess color from slug
		if ( ! $hex ) {
			$hex = bizstudio_guess_color_hex( $slug );
		}

		$active_class = ( $selected === $slug ) ? ' biz-swatch--active' : '';
		$is_light     = bizstudio_is_light_color( $hex );
		$border_class = $is_light ? ' biz-swatch--light' : '';

		$swatches .= sprintf(
			'<button type="button" class="biz-swatch%s%s" data-value="%s" title="%s" aria-label="%s" style="background-color:%s;"></button>',
			$active_class,
			$border_class,
			esc_attr( $slug ),
			esc_attr( $label ),
			esc_attr( $label ),
			esc_attr( $hex )
		);
	}

	$swatches .= '</div>';

	return $swatches;
}

/**
 * Check if an attribute is a color-type attribute.
 */
function bizstudio_is_color_attribute( string $attribute ): bool {
	$color_slugs = [ 'pa_colore', 'pa_color', 'pa_colour' ];
	return in_array( $attribute, $color_slugs, true );
}

/**
 * Guess hex color from slug name.
 */
function bizstudio_guess_color_hex( string $slug ): string {
	$map = [
		'nero'       => '#2D2D3A',
		'black'      => '#2D2D3A',
		'bianco'     => '#F0F0F0',
		'white'      => '#F0F0F0',
		'blu'        => '#1A1A4E',
		'blue'       => '#1A1A4E',
		'rosso'      => '#D32F2F',
		'red'        => '#D32F2F',
		'verde'      => '#2E7D32',
		'green'      => '#2E7D32',
		'grigio'     => '#9E9E9E',
		'gray'       => '#9E9E9E',
		'grey'       => '#9E9E9E',
		'beige'      => '#D4C5A9',
		'marrone'    => '#6D4C41',
		'brown'      => '#6D4C41',
		'giallo'     => '#FBC02D',
		'yellow'     => '#FBC02D',
		'arancione'  => '#F57C00',
		'orange'     => '#F57C00',
		'rosa'       => '#E91E63',
		'pink'       => '#E91E63',
		'viola'      => '#7B1FA2',
		'purple'     => '#7B1FA2',
	];

	return $map[ $slug ] ?? '#CCCCCC';
}

/**
 * Check if a hex color is light (for border on white-ish swatches).
 */
function bizstudio_is_light_color( string $hex ): bool {
	$hex = ltrim( $hex, '#' );
	$r   = hexdec( substr( $hex, 0, 2 ) );
	$g   = hexdec( substr( $hex, 2, 2 ) );
	$b   = hexdec( substr( $hex, 4, 2 ) );
	$brightness = ( $r * 299 + $g * 587 + $b * 114 ) / 1000;
	return $brightness > 200;
}

/**
 * Add color swatch admin field to attribute term edit page.
 */
add_action( 'pa_colore_edit_form_fields', function ( $term ) {
	$hex = get_term_meta( $term->term_id, 'bizstudio_color_hex', true );
	?>
	<tr class="form-field">
		<th><label for="bizstudio_color_hex"><?php esc_html_e( 'Colore HEX', 'bizstudio' ); ?></label></th>
		<td>
			<input type="color" name="bizstudio_color_hex" id="bizstudio_color_hex" value="<?php echo esc_attr( $hex ?: '#CCCCCC' ); ?>" style="width:60px;height:40px;padding:2px;">
			<p class="description"><?php esc_html_e( 'Seleziona il colore per lo swatch.', 'bizstudio' ); ?></p>
		</td>
	</tr>
	<?php
} );

add_action( 'pa_colore_add_form_fields', function () {
	?>
	<div class="form-field">
		<label for="bizstudio_color_hex"><?php esc_html_e( 'Colore HEX', 'bizstudio' ); ?></label>
		<input type="color" name="bizstudio_color_hex" id="bizstudio_color_hex" value="#CCCCCC" style="width:60px;height:40px;padding:2px;">
	</div>
	<?php
} );

add_action( 'edited_pa_colore', function ( $term_id ) {
	if ( isset( $_POST['bizstudio_color_hex'] ) ) {
		update_term_meta( $term_id, 'bizstudio_color_hex', sanitize_hex_color( $_POST['bizstudio_color_hex'] ) );
	}
} );

add_action( 'created_pa_colore', function ( $term_id ) {
	if ( isset( $_POST['bizstudio_color_hex'] ) ) {
		update_term_meta( $term_id, 'bizstudio_color_hex', sanitize_hex_color( $_POST['bizstudio_color_hex'] ) );
	}
} );
