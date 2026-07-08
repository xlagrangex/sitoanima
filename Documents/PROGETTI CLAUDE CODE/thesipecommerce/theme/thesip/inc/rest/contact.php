<?php
/**
 * REST endpoint contact form — sostituisce app/api/contact del Next.
 * POST /wp-json/pharmanow/v1/contact
 *
 * Validazione server-side, honeypot, throttling per IP, wp_mail al customer-care.
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
			'/contact',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => 'pn_rest_contact_submit',
			)
		);
	}
);

function pn_rest_contact_submit( WP_REST_Request $req ) {
	// Verifica nonce REST (PN_DATA.nonce è wp_rest).
	$nonce = $req->get_header( 'x_wp_nonce' );
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error( 'pn_invalid_nonce', __( 'Sessione scaduta. Ricarica la pagina e riprova.', 'pharmanow' ), array( 'status' => 403 ) );
	}

	$ip   = pn_contact_client_ip();
	$thr  = 'pn_contact_thr_' . md5( $ip );
	$last = get_transient( $thr );
	if ( $last && ( time() - (int) $last ) < 30 ) {
		return new WP_Error( 'pn_rate_limit', __( 'Hai inviato un messaggio da poco. Riprova tra qualche secondo.', 'pharmanow' ), array( 'status' => 429 ) );
	}

	$nome      = sanitize_text_field( (string) $req->get_param( 'nome' ) );
	$email     = sanitize_email( (string) $req->get_param( 'email' ) );
	$telefono  = sanitize_text_field( (string) $req->get_param( 'telefono' ) );
	$oggetto   = sanitize_text_field( (string) $req->get_param( 'oggetto' ) );
	$messaggio = sanitize_textarea_field( (string) $req->get_param( 'messaggio' ) );
	$hp        = (string) $req->get_param( '_hp' );

	// Honeypot: se compilato, fingiamo successo ma non spediamo.
	if ( '' !== trim( $hp ) ) {
		return rest_ensure_response( array( 'ok' => true ) );
	}

	$errors = array();
	if ( strlen( $nome ) < 2 ) {
		$errors[] = __( 'Nome non valido.', 'pharmanow' );
	}
	if ( ! is_email( $email ) ) {
		$errors[] = __( 'Email non valida.', 'pharmanow' );
	}
	if ( strlen( $oggetto ) < 3 ) {
		$errors[] = __( 'Oggetto troppo corto.', 'pharmanow' );
	}
	if ( strlen( $messaggio ) < 10 ) {
		$errors[] = __( 'Messaggio troppo corto.', 'pharmanow' );
	}
	if ( $errors ) {
		return new WP_Error( 'pn_validation', implode( ' ', $errors ), array( 'status' => 422 ) );
	}

	$settings = pn_shop_settings();
	$to       = apply_filters( 'pn_contact_to_email', $settings['email'] );
	$subject  = sprintf( '[Contatto sito] %s', $oggetto );
	$body     = sprintf(
		"Nome: %s\nEmail: %s\nTelefono: %s\n\nOggetto: %s\n\nMessaggio:\n%s\n\n— IP: %s\n",
		$nome,
		$email,
		$telefono ? $telefono : '—',
		$oggetto,
		$messaggio,
		$ip
	);
	$headers  = array(
		'Content-Type: text/plain; charset=UTF-8',
		'Reply-To: ' . sprintf( '%s <%s>', $nome, $email ),
	);

	$ok = wp_mail( $to, $subject, $body, $headers );
	if ( ! $ok ) {
		return new WP_Error( 'pn_mail_failed', __( 'Invio fallito. Riprova più tardi.', 'pharmanow' ), array( 'status' => 500 ) );
	}

	set_transient( $thr, time(), MINUTE_IN_SECONDS );

	return rest_ensure_response( array( 'ok' => true ) );
}

function pn_contact_client_ip(): string {
	$keys = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
	foreach ( $keys as $k ) {
		if ( ! empty( $_SERVER[ $k ] ) ) {
			$ip = explode( ',', (string) $_SERVER[ $k ] )[0];
			$ip = trim( $ip );
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}
	}
	return '0.0.0.0';
}
