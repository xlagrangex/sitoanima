<?php
/**
 * REST endpoint per logging consensi cookie (audit trail GDPR).
 *
 * POST /wp-json/pharmanow/v1/consent
 * Body: { consent: "all"|"necessary"|"statistics,marketing", action: "accept_all"|"accept_necessary"|"customize"|"revoke" }
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'pharmanow/v1',
			'/consent',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => 'pn_consent_rest_save',
				'args'                => array(
					'consent' => array( 'type' => 'string', 'required' => true ),
					'action'  => array( 'type' => 'string', 'required' => true ),
				),
			)
		);
	}
);

function pn_consent_rest_save( WP_REST_Request $request ) {
	global $wpdb;

	$consent = sanitize_text_field( (string) $request->get_param( 'consent' ) );
	$action  = sanitize_text_field( (string) $request->get_param( 'action' ) );

	$allowed_consent_values  = array( 'all', 'necessary', 'statistics', 'marketing', 'statistics,marketing' );
	$allowed_actions         = array( 'accept_all', 'accept_necessary', 'customize', 'revoke' );

	if ( ! in_array( $action, $allowed_actions, true ) ) {
		return new WP_Error( 'invalid_action', 'Action non valida', array( 'status' => 400 ) );
	}
	// `consent` può essere anche combinazioni custom (es. "statistics" da solo)
	if ( ! preg_match( '/^[a-z_,]+$/', $consent ) ) {
		return new WP_Error( 'invalid_consent', 'Consent non valido', array( 'status' => 400 ) );
	}

	// Categories JSON
	$categories = array();
	foreach ( pn_consent_categories() as $cat ) {
		if ( 'necessary' === $cat['key'] ) {
			$categories[ $cat['key'] ] = true;
			continue;
		}
		$categories[ $cat['key'] ] = ( 'all' === $consent ) || in_array( $cat['key'], explode( ',', $consent ), true );
	}

	// Hash IP per pseudonymization GDPR
	$ip      = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$ip_hash = $ip ? hash( 'sha256', $ip . wp_salt( 'auth' ) ) : '';
	$ua      = isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 0, 255 ) : '';

	$table = $wpdb->prefix . 'pn_consent_log';

	$wpdb->insert(
		$table,
		array(
			'user_id'    => is_user_logged_in() ? get_current_user_id() : null,
			'ip_hash'    => $ip_hash,
			'user_agent' => $ua,
			'consent'    => $consent,
			'categories' => wp_json_encode( $categories ),
			'version'    => PN_CONSENT_VERSION,
			'action'     => $action,
		),
		array( '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
	);

	$insert_id = (int) $wpdb->insert_id;

	// Sync user_meta se loggato (portabilità cross-device)
	if ( is_user_logged_in() ) {
		$uid = get_current_user_id();
		update_user_meta( $uid, 'pn_consent_value', $consent );
		update_user_meta( $uid, 'pn_consent_version', PN_CONSENT_VERSION );
		update_user_meta( $uid, 'pn_consent_ts', time() );
	}

	return rest_ensure_response(
		array(
			'logged' => true,
			'id'     => $insert_id,
		)
	);
}
