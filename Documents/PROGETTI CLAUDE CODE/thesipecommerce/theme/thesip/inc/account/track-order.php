<?php
/**
 * /traccia-ordine/ — lookup pubblico ordine via display_id + email.
 *
 * Replica app/(shop)/traccia-ordine + components/shop/account/TrackOrderForm
 * del Next. Endpoint REST per evitare full page reload, rate-limited per
 * impedire enumeration.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Query var virtuale.
 */
add_filter(
	'query_vars',
	function ( $vars ) {
		$vars[] = 'pn_track_order';
		return $vars;
	}
);

/**
 * Rewrite /traccia-ordine/ → query var.
 */
add_action(
	'init',
	function () {
		add_rewrite_rule( '^traccia-ordine/?$', 'index.php?pn_track_order=1', 'top' );
	},
	11
);

/**
 * Template loader: cerca page-traccia-ordine.php nel tema.
 */
add_filter(
	'template_include',
	function ( $template ) {
		if ( '1' === (string) get_query_var( 'pn_track_order' ) ) {
			$candidate = locate_template( array( 'page-traccia-ordine.php' ) );
			if ( $candidate ) {
				return $candidate;
			}
		}
		return $template;
	}
);

/**
 * <title> e meta description per la route virtuale.
 */
add_filter(
	'document_title_parts',
	function ( $parts ) {
		if ( '1' === (string) get_query_var( 'pn_track_order' ) ) {
			$parts['title'] = __( 'Traccia ordine', 'pharmanow' );
		}
		return $parts;
	}
);

/**
 * REST endpoint pubblico.
 */
add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'pharmanow/v1',
			'/track-order',
			array(
				'methods'             => 'GET',
				'callback'            => 'pn_track_order_rest',
				'permission_callback' => '__return_true',
				'args'                => array(
					'display_id' => array( 'required' => true, 'type' => 'string' ),
					'email'      => array( 'required' => true, 'type' => 'string' ),
				),
			)
		);
	}
);

/**
 * Rate limit semplice via transient (5 req/min/IP).
 */
function pn_track_order_rate_check( string $ip ): bool {
	$key  = 'pn_to_rate_' . md5( $ip );
	$data = get_transient( $key );
	if ( ! is_array( $data ) ) {
		set_transient( $key, array( 'count' => 1, 'ts' => time() ), MINUTE_IN_SECONDS );
		return true;
	}
	if ( $data['count'] >= 5 ) {
		return false;
	}
	$data['count']++;
	set_transient( $key, $data, MINUTE_IN_SECONDS );
	return true;
}

/**
 * Mappa stato Woo → label IT + tone (allineato al Next OrderStatusBadge).
 *
 * @return array{label:string, tone:string}
 */
function pn_track_order_status_meta( string $status ): array {
	$map = array(
		'pending'    => array( 'label' => __( 'In attesa', 'pharmanow' ),         'tone' => 'amber' ),
		'on-hold'    => array( 'label' => __( 'In attesa', 'pharmanow' ),         'tone' => 'amber' ),
		'processing' => array( 'label' => __( 'In lavorazione', 'pharmanow' ),    'tone' => 'blue' ),
		'completed'  => array( 'label' => __( 'Completato', 'pharmanow' ),        'tone' => 'emerald' ),
		'cancelled'  => array( 'label' => __( 'Annullato', 'pharmanow' ),         'tone' => 'rose' ),
		'refunded'   => array( 'label' => __( 'Rimborsato', 'pharmanow' ),        'tone' => 'gray' ),
		'failed'     => array( 'label' => __( 'Fallito', 'pharmanow' ),           'tone' => 'rose' ),
		'shipped'    => array( 'label' => __( 'Spedito', 'pharmanow' ),           'tone' => 'blue' ),
		'delivered'  => array( 'label' => __( 'Consegnato', 'pharmanow' ),        'tone' => 'emerald' ),
	);
	return $map[ $status ] ?? array( 'label' => ucfirst( $status ), 'tone' => 'gray' );
}

/**
 * Calcola URL tracking dato corriere + numero.
 */
function pn_track_order_build_tracking_url( string $provider, string $number ): string {
	$provider = strtolower( trim( $provider ) );
	$number   = trim( $number );
	if ( '' === $provider || '' === $number ) {
		return '';
	}
	$num_enc = rawurlencode( $number );
	$map = array(
		'fedex'    => 'https://www.fedex.com/fedextrack/?trknbr=',
		'gls'      => 'https://gls-italy.com/?action=mail-traking&numero_spedizione=',
		'gls-italy'=> 'https://gls-italy.com/?action=mail-traking&numero_spedizione=',
		'brt'      => 'https://vas.brt.it/vas/sped_det_show.hsm?referer=sped_numspe_par.htm&Nspediz=',
		'sda'      => 'https://www.sda.it/wps/portal/Servizi_online/dettaglio_spedizione?locale=it&tracing.letteraVettura=',
		'dhl'      => 'https://www.dhl.com/it-it/home/tracking.html?tracking-id=',
		'ups'      => 'https://www.ups.com/track?loc=it_IT&tracknum=',
		'tnt'      => 'https://www.tnt.com/express/it_it/site/shipping-tools/tracking.html?searchType=con&cons=',
		'poste'    => 'https://www.poste.it/cerca/index.html#/risultati-spedizioni/',
	);
	if ( isset( $map[ $provider ] ) ) {
		return $map[ $provider ] . $num_enc;
	}
	return '';
}

/**
 * Endpoint handler.
 */
function pn_track_order_rest( WP_REST_Request $req ) {
	$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
	if ( is_string( $ip ) && str_contains( $ip, ',' ) ) {
		$ip = trim( explode( ',', $ip )[0] );
	}
	if ( ! pn_track_order_rate_check( (string) $ip ) ) {
		return new WP_Error( 'rate_limited', __( 'Troppe richieste, riprova tra un minuto', 'pharmanow' ), array( 'status' => 429 ) );
	}

	$display_id = trim( (string) $req->get_param( 'display_id' ) );
	$email      = strtolower( trim( (string) $req->get_param( 'email' ) ) );

	if ( '' === $display_id || '' === $email || ! is_email( $email ) ) {
		return new WP_Error( 'bad_request', __( 'Numero ordine ed email obbligatori', 'pharmanow' ), array( 'status' => 400 ) );
	}

	if ( ! function_exists( 'wc_get_order' ) ) {
		return new WP_Error( 'wc_unavailable', __( 'WooCommerce non disponibile', 'pharmanow' ), array( 'status' => 500 ) );
	}

	// 1) Prova ID diretto se numerico.
	$order = null;
	$id_clean = preg_replace( '/\D/', '', $display_id );
	if ( '' !== $id_clean ) {
		$candidate = wc_get_order( (int) $id_clean );
		if ( $candidate instanceof WC_Order ) {
			$order = $candidate;
		}
	}

	// 2) Fallback: cerca per order number custom (Pharmanow usa
	// _pharmanow_order_number; altri plugin _order_number).
	if ( ! $order ) {
		foreach ( array( '_pharmanow_order_number', '_order_number' ) as $meta_key ) {
			$orders = wc_get_orders(
				array(
					'limit'      => 1,
					'orderby'    => 'ID',
					'order'      => 'DESC',
					'meta_query' => array(
						array( 'key' => $meta_key, 'value' => $display_id, 'compare' => '=' ),
					),
				)
			);
			if ( ! empty( $orders ) && $orders[0] instanceof WC_Order ) {
				$order = $orders[0];
				break;
			}
		}
	}

	if ( ! $order ) {
		return new WP_Error( 'not_found', __( 'Ordine non trovato', 'pharmanow' ), array( 'status' => 404 ) );
	}

	// Email check (case-insensitive).
	$order_email = strtolower( (string) $order->get_billing_email() );
	if ( $order_email !== $email ) {
		// Stesso messaggio del 404 per non fare enumeration.
		return new WP_Error( 'not_found', __( 'Ordine non trovato', 'pharmanow' ), array( 'status' => 404 ) );
	}

	$status        = $order->get_status();
	$status_meta   = pn_track_order_status_meta( $status );
	$tracking_no   = (string) $order->get_meta( '_tracking_number' );
	$tracking_prov = (string) $order->get_meta( '_tracking_provider' );
	$tracking_url  = (string) $order->get_meta( '_tracking_url' );
	if ( '' === $tracking_url && '' !== $tracking_no && '' !== $tracking_prov ) {
		$tracking_url = pn_track_order_build_tracking_url( $tracking_prov, $tracking_no );
	}

	$created = $order->get_date_created();

	return rest_ensure_response(
		array(
			'display_id'        => $order->get_order_number(),
			'status'            => $status,
			'status_label'      => $status_meta['label'],
			'status_tone'       => $status_meta['tone'],
			'created_at'        => $created ? $created->date( DATE_ATOM ) : '',
			'total'             => (float) $order->get_total(),
			'currency_code'     => $order->get_currency(),
			'total_formatted'   => wp_strip_all_tags( wc_price( $order->get_total(), array( 'currency' => $order->get_currency() ) ) ),
			'items_count'       => (int) $order->get_item_count(),
			'tracking_number'   => $tracking_no,
			'tracking_provider' => $tracking_prov,
			'tracking_url'      => $tracking_url,
		)
	);
}
