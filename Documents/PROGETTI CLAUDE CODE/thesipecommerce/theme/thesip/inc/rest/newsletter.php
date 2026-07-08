<?php
/**
 * REST endpoint newsletter — single opt-in.
 * POST /wp-json/pharmanow/v1/newsletter
 *
 * Salva iscritto in CPT pn_newsletter, manda welcome all'utente + notifica admin.
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
			'/newsletter',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => 'pn_rest_newsletter_submit',
			)
		);
	}
);

function pn_rest_newsletter_submit( WP_REST_Request $req ) {
	$nonce = $req->get_header( 'x_wp_nonce' );
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error( 'pn_invalid_nonce', __( 'Sessione scaduta. Ricarica la pagina e riprova.', 'pharmanow' ), array( 'status' => 403 ) );
	}

	$ip   = pn_newsletter_client_ip();
	$thr  = 'pn_nl_thr_' . md5( $ip );
	$last = get_transient( $thr );
	if ( $last && ( time() - (int) $last ) < 30 ) {
		return new WP_Error( 'pn_rate_limit', __( 'Hai inviato una richiesta da poco. Riprova tra qualche secondo.', 'pharmanow' ), array( 'status' => 429 ) );
	}

	$email_raw = (string) $req->get_param( 'email' );
	$email     = strtolower( sanitize_email( $email_raw ) );
	$source    = sanitize_key( (string) $req->get_param( 'source' ) ) ?: 'homepage';
	$hp        = (string) $req->get_param( '_hp' );

	// Honeypot: se compilato, fingiamo successo ma non salviamo.
	if ( '' !== trim( $hp ) ) {
		return rest_ensure_response( array( 'ok' => true ) );
	}

	if ( ! is_email( $email ) ) {
		return new WP_Error( 'pn_validation', __( 'Email non valida.', 'pharmanow' ), array( 'status' => 422 ) );
	}

	set_transient( $thr, time(), MINUTE_IN_SECONDS );

	$existing = pn_newsletter_find_by_email( $email );

	if ( $existing ) {
		$status = get_post_status( $existing );
		if ( 'publish' === $status ) {
			// Già iscritto: ritorno ok silente, non rispedisco la welcome.
			return rest_ensure_response( array( 'ok' => true, 'already' => true ) );
		}
		// Era cestinato → riattivo.
		wp_update_post(
			array(
				'ID'          => $existing,
				'post_status' => 'publish',
			)
		);
		$post_id = $existing;
	} else {
		$post_id = wp_insert_post(
			array(
				'post_type'   => 'pn_newsletter',
				'post_title'  => $email,
				'post_status' => 'publish',
				'post_name'   => sanitize_title( $email ),
			),
			true
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			return new WP_Error( 'pn_save_failed', __( 'Salvataggio non riuscito. Riprova più tardi.', 'pharmanow' ), array( 'status' => 500 ) );
		}

		$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		update_post_meta( $post_id, '_pn_nl_ip', $ip );
		update_post_meta( $post_id, '_pn_nl_source', $source );
		if ( $ua ) {
			update_post_meta( $post_id, '_pn_nl_user_agent', $ua );
		}

		// Tag default = source (così filtri da admin per provenienza).
		wp_set_object_terms( $post_id, $source, 'pn_newsletter_tag', false );
	}

	// Mail welcome (non bloccante: se fallisce, l'iscrizione resta valida).
	pn_newsletter_send_welcome( $email );
	pn_newsletter_notify_admin( $email, $source, $ip );

	/**
	 * Hook per integrazioni esterne (es. push verso Brevo).
	 *
	 * @param string $email
	 * @param int    $post_id
	 * @param string $source
	 */
	do_action( 'pn_newsletter_subscribed', $email, $post_id, $source );

	return rest_ensure_response( array( 'ok' => true ) );
}

/**
 * Welcome mail brandizzata — usa lo stesso template Brevo delle mail
 * transazionali (ordini, account, reset password) per coerenza visiva.
 *
 * Path:
 *   - se `pn_send_brevo()` esiste (mu-plugin pharmanow-brevo-emails) → invio API Brevo + log SQLite
 *   - fallback: `wp_mail()` con `pn_email_wrap()` (template legacy del tema)
 *
 * Il codice è filtrabile via `pn_newsletter_welcome_coupon`.
 */
function pn_newsletter_send_welcome( string $email ): bool {
	$site    = get_bloginfo( 'name' );
	$home    = home_url( '/' );
	$coupon  = (string) apply_filters( 'pn_newsletter_welcome_coupon', 'BENVENUTO10' );
	$pct     = (string) apply_filters( 'pn_newsletter_welcome_coupon_label', '10%' );
	$subject = sprintf( 'Il tuo codice %s ti aspetta — %s', $coupon, $site );

	// Body coerente col look & feel delle mail transazionali (table-based, color palette Brevo).
	$body  = '<h1 style="margin:0 0 16px;font-size:22px;font-weight:700;color:#1a1a1a;line-height:1.3;">Benvenuto su The SIP!</h1>';
	$body .= '<p style="margin:0 0 12px;font-size:14px;color:#555;line-height:1.5;">Ciao,</p>';
	$body .= '<p style="margin:0 0 20px;font-size:14px;color:#555;line-height:1.5;">Grazie per esserti iscritto alla nostra newsletter. Come promesso, ecco il tuo codice sconto da usare sul primo ordine:</p>';

	// Box coupon — palette teal/blu coerente con header Brevo (#38a3a5 / #22577a).
	$body .= '<table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;"><tr><td align="center">';
	$body .= '<table cellpadding="0" cellspacing="0" style="border:2px dashed #38a3a5;border-radius:10px;background:#f0fbfb;"><tr><td style="padding:18px 32px;text-align:center;">';
	$body .= '<div style="font-size:11px;color:#22577a;text-transform:uppercase;letter-spacing:1.5px;font-weight:700;margin-bottom:6px;">' . esc_html( sprintf( '%s di sconto sul primo ordine', $pct ) ) . '</div>';
	$body .= '<div style="font-family:Menlo,Consolas,monospace;font-size:26px;font-weight:800;color:#22577a;letter-spacing:3px;">' . esc_html( $coupon ) . '</div>';
	$body .= '</td></tr></table>';
	$body .= '</td></tr></table>';

	$body .= '<p style="margin:0 0 12px;font-size:13px;color:#777;line-height:1.5;">Inserisci il codice al checkout per ricevere lo sconto.</p>';

	// CTA button — riusa pn_btn() del mu-plugin Brevo se disponibile, altrimenti rendering inline.
	if ( function_exists( 'pn_btn' ) ) {
		$body .= pn_btn( 'Vai allo shop', $home );
	} else {
		$body .= '<table width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:20px 0 8px;"><a href="' . esc_url( $home ) . '" style="display:inline-block;padding:12px 32px;background:linear-gradient(135deg,#38a3a5,#22577a);color:#ffffff;text-decoration:none;border-radius:8px;font-weight:600;font-size:14px;">Vai allo shop</a></td></tr></table>';
	}

	$body .= '<p style="margin:24px 0 0;font-size:12px;color:#888;line-height:1.5;">Da oggi riceverai in anteprima offerte esclusive, nuove uscite e piccole storie di mare firmate dai nostri artisti e biologi marini. Nessuno spam: puoi cancellarti in qualsiasi momento dal link in fondo a ogni email.</p>';

	// Path A: invio via Brevo API + log SQLite (preferito).
	if ( function_exists( 'pn_send_brevo' ) && function_exists( 'pn_email_header' ) && function_exists( 'pn_email_footer' ) ) {
		$html = pn_email_header( 'Benvenuto' ) . $body . pn_email_footer();
		return (bool) pn_send_brevo( $email, $subject, $html, false, 'newsletter_welcome', null );
	}

	// Path B: fallback wp_mail con template legacy.
	$message = function_exists( 'pn_email_wrap' ) ? pn_email_wrap( $body, $subject ) : $body;
	$headers = array( 'Content-Type: text/html; charset=UTF-8' );
	return (bool) wp_mail( $email, $subject, $message, $headers );
}

/**
 * Notifica admin nuova iscrizione (testo plain, basta sapere chi/quando/da dove).
 */
function pn_newsletter_notify_admin( string $email, string $source, string $ip ): void {
	$settings = pn_shop_settings();
	$to       = apply_filters( 'pn_newsletter_admin_to', $settings['email'] );
	$subject  = sprintf( '[Newsletter] Nuovo iscritto: %s', $email );
	$body     = sprintf(
		"Nuovo iscritto alla newsletter.\n\nEmail: %s\nOrigine: %s\nIP: %s\nData: %s\n",
		$email,
		$source,
		$ip,
		current_time( 'mysql' )
	);
	$headers  = array( 'Content-Type: text/plain; charset=UTF-8' );
	wp_mail( $to, $subject, $body, $headers );
}

/**
 * Recupera la Brevo API key configurata in wp_mail_smtp.
 */
function pn_newsletter_brevo_api_key(): string {
	$opts = get_option( 'wp_mail_smtp', array() );
	return (string) ( $opts['sendinblue']['api_key'] ?? '' );
}

/**
 * Push iscritto a Brevo Contacts + add a lista marketing.
 *
 * Chiamato via hook `pn_newsletter_subscribed`. Non-bloccante: errori loggati
 * ma non interrompono il flusso di iscrizione.
 *
 * Default list ID: 3 ("Newsletter Pharmanow"). Override via:
 *   - costante PN_NEWSLETTER_BREVO_LIST_ID
 *   - filter `pn_newsletter_brevo_list_id`
 *
 * @param string $email
 * @param int    $post_id  ID del CPT pn_newsletter
 * @param string $source   homepage|popup_X|...
 */
function pn_newsletter_push_to_brevo( string $email, int $post_id, string $source ): void {
	$api_key = pn_newsletter_brevo_api_key();
	if ( ! $api_key ) {
		error_log( '[PN Newsletter] Brevo API key mancante, skip push contatto' );
		return;
	}

	$default_list = defined( 'PN_NEWSLETTER_BREVO_LIST_ID' ) ? (int) PN_NEWSLETTER_BREVO_LIST_ID : 3;
	$list_id      = (int) apply_filters( 'pn_newsletter_brevo_list_id', $default_list, $email, $source );

	$coupon = (string) apply_filters( 'pn_newsletter_welcome_coupon', 'BENVENUTO10' );

	$payload = array(
		'email'          => $email,
		'updateEnabled'  => true, // Se esiste, aggiorna invece di errore 400.
		'attributes'     => array(
			'SOURCE'         => $source,
			'WELCOME_COUPON' => $coupon,
			'SIGNUP_DATE'    => gmdate( 'Y-m-d' ),
		),
	);
	if ( $list_id > 0 ) {
		$payload['listIds'] = array( $list_id );
	}

	$response = wp_remote_post(
		'https://api.brevo.com/v3/contacts',
		array(
			'headers' => array(
				'api-key'      => $api_key,
				'Content-Type' => 'application/json',
				'accept'       => 'application/json',
			),
			'body'    => wp_json_encode( $payload ),
			'timeout' => 15,
		)
	);

	if ( is_wp_error( $response ) ) {
		error_log( '[PN Newsletter] Brevo push errore: ' . $response->get_error_message() );
		update_post_meta( $post_id, '_pn_nl_brevo_status', 'error: ' . $response->get_error_message() );
		return;
	}

	$code = wp_remote_retrieve_response_code( $response );
	$body = wp_remote_retrieve_body( $response );

	if ( $code >= 300 ) {
		// 400 con "Contact already exist" è normale quando updateEnabled=false; con true non dovrebbe accadere.
		error_log( "[PN Newsletter] Brevo push HTTP $code: $body" );
		update_post_meta( $post_id, '_pn_nl_brevo_status', "http_$code" );
		return;
	}

	$data       = json_decode( $body, true );
	$contact_id = isset( $data['id'] ) ? (int) $data['id'] : 0;

	update_post_meta( $post_id, '_pn_nl_brevo_status', 'ok' );
	update_post_meta( $post_id, '_pn_nl_brevo_contact_id', $contact_id );
	update_post_meta( $post_id, '_pn_nl_brevo_list_id', $list_id );
}

// Hook: ogni nuova iscrizione viene pushata in Brevo Contacts.
add_action( 'pn_newsletter_subscribed', 'pn_newsletter_push_to_brevo', 10, 3 );

function pn_newsletter_client_ip(): string {
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
