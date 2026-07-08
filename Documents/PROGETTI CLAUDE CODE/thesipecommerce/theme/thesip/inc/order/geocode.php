<?php
/**
 * Geocoding indirizzi ordine via Nominatim (OpenStreetMap, free, no API key).
 *
 * Caching:
 *  - per ordine come post meta (forever)
 *  - transient globale per query (1 mese)
 *  - rate-limit single-request via wp_cache lock per rispettare la usage policy Nominatim
 *    (https://operations.osmfoundation.org/policies/nominatim/): max 1 req/s.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const PN_NOMINATIM_USER_AGENT = 'Pharmanow/1.0 (https://wp.pharmanow.com; customer-care@pharmanow.com)';

/**
 * Geocodifica una stringa libera. Cache transient su md5 della query.
 *
 * @param string $query Indirizzo libero.
 * @return array|null   ['lat'=>float,'lng'=>float] oppure null.
 */
function pn_geocode_address( string $query ): ?array {
	$query = trim( preg_replace( '/\s+/', ' ', $query ) );
	if ( '' === $query ) {
		return null;
	}

	$cache_key = 'pn_geo_' . md5( $query );
	$cached    = get_transient( $cache_key );
	if ( false !== $cached ) {
		return is_array( $cached ) ? $cached : null;
	}

	// Rate-limit cooperativo: 1 richiesta/s globalmente.
	$lock_key = 'pn_nominatim_last_ts';
	$last_ts  = (int) wp_cache_get( $lock_key, 'pharmanow' );
	$now_ms   = (int) ( microtime( true ) * 1000 );
	if ( $last_ts && ( $now_ms - $last_ts ) < 1100 ) {
		usleep( ( 1100 - ( $now_ms - $last_ts ) ) * 1000 );
	}
	wp_cache_set( $lock_key, (int) ( microtime( true ) * 1000 ), 'pharmanow', 5 );

	$url = add_query_arg(
		array(
			'q'             => $query,
			'format'        => 'json',
			'limit'         => 1,
			'addressdetails' => 0,
			'countrycodes'  => 'it',
		),
		'https://nominatim.openstreetmap.org/search'
	);

	$resp = wp_remote_get(
		$url,
		array(
			'timeout' => 6,
			'headers' => array(
				'User-Agent' => PN_NOMINATIM_USER_AGENT,
				'Accept'     => 'application/json',
				'Referer'    => home_url( '/' ),
			),
		)
	);

	if ( is_wp_error( $resp ) || 200 !== (int) wp_remote_retrieve_response_code( $resp ) ) {
		set_transient( $cache_key, 0, HOUR_IN_SECONDS );
		return null;
	}

	$data = json_decode( (string) wp_remote_retrieve_body( $resp ), true );
	if ( ! is_array( $data ) || empty( $data[0]['lat'] ) || empty( $data[0]['lon'] ) ) {
		set_transient( $cache_key, 0, HOUR_IN_SECONDS );
		return null;
	}

	$result = array(
		'lat' => (float) $data[0]['lat'],
		'lng' => (float) $data[0]['lon'],
	);
	set_transient( $cache_key, $result, MONTH_IN_SECONDS );
	return $result;
}

/**
 * Geocodifica indirizzo di spedizione di un ordine. Risultato salvato come order meta.
 *
 * @param WC_Order $order
 * @return array|null
 */
function pn_get_order_shipping_geo( WC_Order $order ): ?array {
	$lat = $order->get_meta( '_pn_ship_geo_lat' );
	$lng = $order->get_meta( '_pn_ship_geo_lng' );
	if ( $lat && $lng ) {
		return array(
			'lat' => (float) $lat,
			'lng' => (float) $lng,
		);
	}

	$parts = array_filter(
		array(
			$order->get_shipping_address_1(),
			$order->get_shipping_postcode(),
			$order->get_shipping_city(),
			$order->get_shipping_state(),
			$order->get_shipping_country() ? $order->get_shipping_country() : 'IT',
		)
	);
	$query = implode( ', ', $parts );
	if ( '' === trim( $query ) ) {
		// Fallback su indirizzo di fatturazione.
		$parts = array_filter(
			array(
				$order->get_billing_address_1(),
				$order->get_billing_postcode(),
				$order->get_billing_city(),
				$order->get_billing_state(),
				$order->get_billing_country() ? $order->get_billing_country() : 'IT',
			)
		);
		$query = implode( ', ', $parts );
	}
	if ( '' === trim( $query ) ) {
		return null;
	}

	$geo = pn_geocode_address( $query );
	if ( $geo ) {
		$order->update_meta_data( '_pn_ship_geo_lat', $geo['lat'] );
		$order->update_meta_data( '_pn_ship_geo_lng', $geo['lng'] );
		$order->save();
	}
	return $geo;
}

/**
 * Coordinate della sede di spedizione (warehouse). Customizer overrides → fallback Vigano (MI).
 */
function pn_get_warehouse_geo(): array {
	$lat = (float) get_theme_mod( 'pn_address_lat', 45.7045 );
	$lng = (float) get_theme_mod( 'pn_address_lng', 9.4150 );
	return array(
		'lat' => $lat,
		'lng' => $lng,
	);
}
