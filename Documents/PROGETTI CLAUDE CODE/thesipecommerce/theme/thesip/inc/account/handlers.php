<?php
/**
 * Handler form account: login, register, lost-password, profile-update, address-update.
 *
 * Eseguiti su template_redirect (priority 5, prima del redirect-non-loggato in setup.php).
 * Non usiamo admin-post.php perché alcuni host bloccano POST a /wp-admin/.
 *
 * Distinzione del POST tramite campo `pn_action` (no clash con `action` WP).
 *
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'template_redirect',
	function () {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			return;
		}
		$action = isset( $_POST['pn_action'] ) ? sanitize_key( wp_unslash( $_POST['pn_action'] ) ) : '';
		if ( '' === $action ) {
			return;
		}
		switch ( $action ) {
			case 'pn_login':
				pn_handle_login();
				break;
			case 'pn_register':
				pn_handle_register();
				break;
			case 'pn_lost_password':
				pn_handle_lost_password();
				break;
			case 'pn_reset_password':
				pn_handle_reset_password();
				break;
			case 'pn_profile_update':
				pn_handle_profile_update();
				break;
			case 'pn_address_update':
				pn_handle_address_update();
				break;
		}
	},
	5
);

/* ============================================================
   LOGIN
   ============================================================ */
function pn_handle_login(): void {
	$redirect_to = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : wc_get_page_permalink( 'myaccount' );
	$back_url    = home_url( '/login/' );
	if ( $redirect_to ) {
		$back_url = add_query_arg( 'redirect', rawurlencode( $redirect_to ), $back_url );
	}

	$bail = function ( $code ) use ( $back_url ) {
		wp_safe_redirect( add_query_arg( 'err', $code, $back_url ) );
		exit;
	};

	if ( ! isset( $_POST['_pn_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['_pn_nonce'] ), 'pn_login' ) ) {
		$bail( 'invalid_nonce' );
	}

	$login    = sanitize_text_field( wp_unslash( $_POST['pn_email'] ?? '' ) );
	$password = (string) ( $_POST['pn_password'] ?? '' );
	$remember = ! empty( $_POST['rememberme'] );

	if ( '' === $login || '' === $password ) {
		$bail( 'missing' );
	}

	$user = wp_signon(
		array(
			'user_login'    => $login,
			'user_password' => $password,
			'remember'      => $remember,
		),
		is_ssl()
	);

	if ( is_wp_error( $user ) ) {
		$bail( 'invalid' );
	}

	wp_set_current_user( $user->ID );
	wp_safe_redirect( $redirect_to ?: wc_get_page_permalink( 'myaccount' ) );
	exit;
}

/* ============================================================
   REGISTER
   ============================================================ */
function pn_handle_register(): void {
	$redirect_to = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : wc_get_page_permalink( 'myaccount' );
	$back_url    = home_url( '/registrati/' );

	$bail = function ( $code ) use ( $back_url ) {
		wp_safe_redirect( add_query_arg( 'err', $code, $back_url ) );
		exit;
	};

	if ( ! isset( $_POST['_pn_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['_pn_nonce'] ), 'pn_register' ) ) {
		$bail( 'invalid_nonce' );
	}

	$first_name = sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) );
	$last_name  = sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) );
	$email      = sanitize_email( wp_unslash( $_POST['pn_email'] ?? '' ) );
	$password   = (string) ( $_POST['pn_password'] ?? '' );
	$privacy    = ! empty( $_POST['privacy'] );

	if ( ! $privacy )                  $bail( 'privacy' );
	if ( ! is_email( $email ) )        $bail( 'invalid_email' );
	if ( strlen( $password ) < 8 )     $bail( 'weak_password' );
	if ( email_exists( $email ) )      $bail( 'email_exists' );

	$user_id = wp_insert_user(
		array(
			'user_login' => $email,
			'user_email' => $email,
			'user_pass'  => $password,
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'role'       => 'customer',
		)
	);
	if ( is_wp_error( $user_id ) ) {
		$bail( 'generic' );
	}

	wp_set_current_user( $user_id );
	wp_set_auth_cookie( $user_id, true, is_ssl() );

	if ( function_exists( 'WC' ) ) {
		$mailer = WC()->mailer();
		$emails = $mailer->get_emails();
		if ( isset( $emails['WC_Email_Customer_New_Account'] ) ) {
			$emails['WC_Email_Customer_New_Account']->trigger( $user_id, $password, true );
		}
	}

	wp_safe_redirect( $redirect_to );
	exit;
}

/* ============================================================
   LOST PASSWORD
   ============================================================ */
function pn_handle_lost_password(): void {
	$back = home_url( '/password-dimenticata/' );

	if ( ! isset( $_POST['_pn_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['_pn_nonce'] ), 'pn_lost_password' ) ) {
		wp_safe_redirect( add_query_arg( 'err', '1', $back ) );
		exit;
	}

	if ( isset( $_POST['pn_email'] ) ) {
		// retrieve_password() legge $_POST['user_login']: lo iniettiamo.
		$_POST['user_login'] = sanitize_email( wp_unslash( $_POST['pn_email'] ) );
		retrieve_password();
	}

	wp_safe_redirect( add_query_arg( 'sent', '1', $back ) );
	exit;
}

/* ============================================================
   RESET PASSWORD (dopo click link email)
   ============================================================ */
function pn_handle_reset_password(): void {
	$key   = sanitize_text_field( wp_unslash( $_POST['pn_key'] ?? '' ) );
	$login = sanitize_text_field( wp_unslash( $_POST['pn_login'] ?? '' ) );
	$back  = add_query_arg(
		array(
			'key'   => $key,
			'login' => rawurlencode( $login ),
		),
		home_url( '/reset-password/' )
	);

	if ( ! isset( $_POST['_pn_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['_pn_nonce'] ), 'pn_reset_password' ) ) {
		wp_safe_redirect( add_query_arg( 'err', 'nonce', $back ) );
		exit;
	}

	$user = check_password_reset_key( $key, $login );
	if ( ! $user || is_wp_error( $user ) ) {
		wp_safe_redirect( home_url( '/password-dimenticata/' ) );
		exit;
	}

	$pass1 = (string) ( $_POST['pn_password'] ?? '' );
	$pass2 = (string) ( $_POST['pn_password_confirm'] ?? '' );

	if ( strlen( $pass1 ) < 8 ) {
		wp_safe_redirect( add_query_arg( 'err', 'weak', $back ) );
		exit;
	}
	if ( $pass1 !== $pass2 ) {
		wp_safe_redirect( add_query_arg( 'err', 'mismatch', $back ) );
		exit;
	}

	reset_password( $user, $pass1 );
	wp_safe_redirect( add_query_arg( 'done', '1', home_url( '/reset-password/' ) ) );
	exit;
}

/* ============================================================
   PROFILE UPDATE
   ============================================================ */
function pn_handle_profile_update(): void {
	if ( ! is_user_logged_in() ) {
		wp_safe_redirect( home_url( '/login/' ) );
		exit;
	}
	$back = home_url( '/account/profilo/' );

	if ( ! isset( $_POST['_pn_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['_pn_nonce'] ), 'pn_profile_update' ) ) {
		wp_safe_redirect( add_query_arg( 'err', 'nonce', $back ) );
		exit;
	}

	$user_id    = get_current_user_id();
	$first_name = sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) );
	$last_name  = sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) );
	$phone      = sanitize_text_field( wp_unslash( $_POST['billing_phone'] ?? '' ) );
	$new_pass   = (string) ( $_POST['pn_new_password'] ?? '' );

	wp_update_user(
		array(
			'ID'         => $user_id,
			'first_name' => $first_name,
			'last_name'  => $last_name,
		)
	);
	update_user_meta( $user_id, 'billing_first_name', $first_name );
	update_user_meta( $user_id, 'billing_last_name', $last_name );
	if ( '' !== $phone ) {
		update_user_meta( $user_id, 'billing_phone', $phone );
	}
	if ( '' !== $new_pass ) {
		if ( strlen( $new_pass ) < 8 ) {
			wp_safe_redirect( add_query_arg( 'err', 'weak', $back ) );
			exit;
		}
		wp_set_password( $new_pass, $user_id );
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, true, is_ssl() );
	}

	wp_safe_redirect( add_query_arg( 'saved', '1', $back ) );
	exit;
}

/* ============================================================
   ADDRESS UPDATE
   ============================================================ */
function pn_handle_address_update(): void {
	if ( ! is_user_logged_in() ) {
		wp_safe_redirect( home_url( '/login/' ) );
		exit;
	}
	$type = isset( $_POST['type'] ) && in_array( $_POST['type'], array( 'billing', 'shipping' ), true ) ? $_POST['type'] : 'billing';
	$back = home_url( '/account/indirizzi/' );

	if ( ! isset( $_POST['_pn_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['_pn_nonce'] ), 'pn_address_update_' . $type ) ) {
		wp_safe_redirect( add_query_arg( 'err', 'nonce', $back ) );
		exit;
	}

	$user_id = get_current_user_id();
	$fields  = array( 'first_name', 'last_name', 'address_1', 'address_2', 'postcode', 'city', 'state', 'country', 'phone' );
	foreach ( $fields as $f ) {
		$key   = $type . '_' . $f;
		$value = sanitize_text_field( wp_unslash( $_POST[ $key ] ?? '' ) );
		update_user_meta( $user_id, $key, $value );
	}

	wp_safe_redirect( add_query_arg( array( 'saved' => '1', 'type' => $type ), $back ) );
	exit;
}
