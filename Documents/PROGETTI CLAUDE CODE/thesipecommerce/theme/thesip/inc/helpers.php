<?php
/**
 * Theme helpers riusabili in tutti i template.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * True se nel carrello è applicato almeno un coupon con spedizione gratuita.
 *
 * Un coupon free-shipping sblocca la spedizione gratuita anche sotto la soglia
 * d'importo: pn_get_shipping_state() ne tiene conto per restare coerente con
 * ciò che WooCommerce propone effettivamente al checkout.
 */
function pn_cart_has_free_shipping_coupon(): bool {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return false;
	}
	foreach ( WC()->cart->get_coupons() as $coupon ) {
		if ( $coupon instanceof WC_Coupon && $coupon->get_free_shipping() ) {
			return true;
		}
	}
	return false;
}

/**
 * Stato di spedizione corrente (subtotal, soglia free, costo, gratuita?).
 * Centralizzato per evitare drift tra template (mini-cart, cart, checkout, sidebar).
 *
 * @return array{subtotal:float, threshold:float, is_free:bool, ship_cost:float, standard_cost:float, remaining:float, pct:float}
 */
function pn_get_shipping_state(): array {
	$threshold_raw = get_theme_mod( 'pn_free_shipping_threshold', 30.00 );
	$threshold     = is_numeric( $threshold_raw ) && (float) $threshold_raw > 0 ? (float) $threshold_raw : 30.00;

	$standard_raw = get_theme_mod( 'pn_standard_shipping_cost', 4.90 );
	$standard     = is_numeric( $standard_raw ) && (float) $standard_raw >= 0 ? (float) $standard_raw : 4.90;

	$subtotal = 0.0;
	if ( function_exists( 'WC' ) && WC()->cart ) {
		$subtotal = (float) WC()->cart->get_displayed_subtotal();
	}

	$has_fs_coupon = pn_cart_has_free_shipping_coupon();

	$is_free   = $has_fs_coupon || ( $subtotal >= $threshold && $subtotal > 0 );
	$ship_cost = $is_free ? 0.0 : $standard;
	$remaining = $is_free ? 0.0 : max( 0.0, $threshold - $subtotal );
	$pct       = $is_free ? 100.0 : ( $threshold > 0 ? min( 100.0, ( $subtotal / $threshold ) * 100.0 ) : 0.0 );

	return array(
		'subtotal'      => $subtotal,
		'threshold'     => $threshold,
		'is_free'       => $is_free,
		'ship_cost'     => $ship_cost,
		'standard_cost' => $standard,
		'remaining'     => $remaining,
		'pct'           => $pct,
	);
}

/**
 * Stampa un'icona SVG inline da assets/icons/{name}.svg.
 *
 * Esempio: pn_icon( 'phone', array( 'class' => 'h-4 w-4' ) );
 *           pn_icon( 'social/facebook', array( 'class' => 'h-4 w-4' ) );
 *
 * @param string $name Nome (può contenere subdir, es. "social/facebook"; senza .svg).
 * @param array  $attr Attributi extra da iniettare nel root <svg>.
 */
function pn_icon( string $name, array $attr = array() ): void {
	$safe_parts = array_map( 'sanitize_file_name', explode( '/', $name ) );
	$path       = PN_THEME_DIR . '/assets/icons/' . implode( '/', $safe_parts ) . '.svg';
	if ( ! file_exists( $path ) ) {
		return;
	}

	$svg = file_get_contents( $path );
	if ( ! $svg ) {
		return;
	}

	if ( $attr ) {
		$attr_str = '';
		foreach ( $attr as $k => $v ) {
			$attr_str .= sprintf( ' %s="%s"', esc_attr( $k ), esc_attr( $v ) );
		}
		$svg = preg_replace( '/<svg\b/', '<svg' . $attr_str, $svg, 1 );
	}

	// SVG locali, già fidati.
	echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Formatta un prezzo usando wc_price() se Woo è attivo.
 *
 * @param mixed $price Numero o stringa.
 */
function pn_format_price( $price ): string {
	if ( function_exists( 'wc_price' ) ) {
		return wc_price( $price );
	}
	return '€' . number_format( (float) $price, 2, ',', '.' );
}

/**
 * Stampa un asset path versionato.
 *
 * @param string $relative Es. 'images/logo.svg'.
 */
function pn_asset( string $relative ): string {
	return PN_THEME_URI . '/assets/' . ltrim( $relative, '/' );
}

/**
 * Shop settings — porting da DEFAULT_SHOP_SETTINGS in lib/shop-settings.ts del Next.
 *
 * Override possibili: filtro `pn_shop_settings`, oppure Customizer (theme_mod `pn_*`).
 *
 * @return array
 */
function pn_shop_settings(): array {
	$defaults = array(
		'free_shipping_threshold'   => 30.00,
		'delivery_cutoff_hour'      => 14,
		'ministerial_authorization' => '',
		'carriers'                  => array( 'FedEx', 'GLS' ),
		'payment_methods'           => array( 'VISA', 'MASTERCARD', 'AMEX', 'PAYPAL', 'APPLE-PAY' ),
		'email'                     => 'info@thesip.it',
		'phone'                     => '+39 335 130 6771',
		'whatsapp'                  => get_theme_mod( 'pn_whatsapp_number', '+393331234567' ),
		'business_hours'            => 'Lun–Ven 9:00–18:00, Sab 9:00–13:00',
		'address'                   => array(
			'street'   => 'Via Bellone 1',
			'city'     => 'Vigano',
			'zip'      => '20083',
			'province' => 'MI',
			'country'  => 'IT',
		),
		'legal_name'                => 'Gekofar S.r.l.',
		'vat_number'                => '13897610963',
		'socials'                   => array(
			'facebook'  => 'https://facebook.com/pharmanow',
			'instagram' => 'https://instagram.com/pharmanow',
			'linkedin'  => 'https://linkedin.com/company/pharmanow',
		),
		'trustpilot_url'            => 'https://it.trustpilot.com/review/pharmanow.com',
	);

	// Customizer overrides.
	$mods = array(
		'free_shipping_threshold' => get_theme_mod( 'pn_free_shipping_threshold', null ),
		'phone'                   => get_theme_mod( 'pn_phone', null ),
		'email'                   => get_theme_mod( 'pn_email', null ),
		'business_hours'          => get_theme_mod( 'pn_business_hours', null ),
		'legal_name'              => get_theme_mod( 'pn_legal_name', null ),
		'vat_number'              => get_theme_mod( 'pn_vat_number', null ),
	);
	foreach ( $mods as $k => $v ) {
		if ( null !== $v && '' !== $v ) {
			$defaults[ $k ] = $v;
		}
	}

	return apply_filters( 'pn_shop_settings', $defaults );
}

/**
 * Avvolge il contenuto del bottone/link nello stile slide-up hover.
 *
 * Usage:
 *   echo pn_slide_text( pn_icon_string( 'arrow-right' ) . '<span>Esplora</span>' );
 *
 * Richiede sull'elemento esterno: data-slot="button" + classi CSS slide-aware.
 *
 * @param string $html Contenuto HTML interno.
 * @param string $gap  Gap interno (default 0.5rem).
 * @return string Markup con .btn-slide-wrap + inner + clone.
 */
function pn_slide_text( string $html, string $gap = '0.5rem' ): string {
	return sprintf(
		'<span class="btn-slide-wrap" style="gap:%1$s"><span class="btn-slide-inner">%2$s</span><span class="btn-slide-clone" aria-hidden="true">%2$s</span></span>',
		esc_attr( $gap ),
		$html
	);
}

/**
 * Versione di pn_icon che ritorna stringa invece di echo.
 */
function pn_icon_string( string $name, array $attr = array() ): string {
	$safe_parts = array_map( 'sanitize_file_name', explode( '/', $name ) );
	$path       = PN_THEME_DIR . '/assets/icons/' . implode( '/', $safe_parts ) . '.svg';
	if ( ! file_exists( $path ) ) {
		return '';
	}
	$svg = file_get_contents( $path );
	if ( ! $svg ) {
		return '';
	}
	if ( $attr ) {
		$attr_str = '';
		foreach ( $attr as $k => $v ) {
			$attr_str .= sprintf( ' %s="%s"', esc_attr( $k ), esc_attr( $v ) );
		}
		$svg = preg_replace( '/<svg\b/', '<svg' . $attr_str, $svg, 1 );
	}
	return $svg;
}

/**
 * Mapbox public token — `PN_MAPBOX_TOKEN` constant prevale, fallback theme_mod `pn_mapbox_token`.
 */
function pn_mapbox_token(): string {
	if ( defined( 'PN_MAPBOX_TOKEN' ) && PN_MAPBOX_TOKEN ) {
		return (string) PN_MAPBOX_TOKEN;
	}
	return (string) get_theme_mod( 'pn_mapbox_token', '' );
}

/**
 * Mappa codice metodo pagamento → label leggibile.
 */
function pn_format_payment_method( string $method ): string {
	$map = array(
		'VISA'       => 'Visa',
		'MASTERCARD' => 'Mastercard',
		'AMEX'       => 'Amex',
		'PAYPAL'     => 'PayPal',
		'APPLE-PAY'  => 'Apple Pay',
		'GOOGLE-PAY' => 'Google Pay',
		'STRIPE'     => 'Stripe',
		'MAESTRO'    => 'Maestro',
	);
	return $map[ $method ] ?? $method;
}

/**
 * Free shipping threshold da WC shipping zones, con fallback hard-coded.
 *
 * Itera tutte le zone, cerca metodi `free_shipping` con `requires` ∈ {min_amount, either}
 * e ritorna il `min_amount` minore. Cached 5 min via wp_cache.
 *
 * @return float Threshold in EUR. Fallback: 30.00.
 */
function pn_get_free_shipping_threshold(): float {
	$cached = wp_cache_get( 'pn_free_shipping_threshold', 'pharmanow' );
	if ( false !== $cached ) {
		return (float) $cached;
	}

	$fallback = 30.00;
	$found    = null;

	if ( ! function_exists( 'WC' ) || ! WC()->shipping ) {
		wp_cache_set( 'pn_free_shipping_threshold', $fallback, 'pharmanow', 300 );
		return $fallback;
	}

	$zones = WC_Shipping_Zones::get_zones();
	// Aggiungi anche la "zona 0" (Rest of the world).
	$zones[] = array( 'shipping_methods' => ( new WC_Shipping_Zone( 0 ) )->get_shipping_methods() );

	foreach ( $zones as $zone ) {
		foreach ( $zone['shipping_methods'] as $method ) {
			if ( 'free_shipping' !== $method->id ) {
				continue;
			}
			if ( 'no' === $method->enabled ) {
				continue;
			}
			$requires   = $method->get_option( 'requires' );
			$min_amount = (float) $method->get_option( 'min_amount' );
			if ( in_array( $requires, array( 'min_amount', 'either' ), true ) && $min_amount > 0 ) {
				if ( null === $found || $min_amount < $found ) {
					$found = $min_amount;
				}
			}
		}
	}

	$threshold = null !== $found ? $found : $fallback;
	wp_cache_set( 'pn_free_shipping_threshold', $threshold, 'pharmanow', 300 );
	return (float) $threshold;
}
