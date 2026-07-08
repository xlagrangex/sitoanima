<?php
/**
 * Cookie Consent — setup, dbDelta tabella log, helpers, render banner.
 *
 * Architettura:
 * - Cookie `pn_consent` (1 anno): valore `all` | `necessary` | `statistics,marketing`
 * - Cookie `pn_consent_ts`: timestamp scelta (audit)
 * - Tabella `wp_pn_consent_log` (append-only, GDPR audit trail)
 * - Hook wp_footer per render banner sticky + modal
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/categories.php';
require_once __DIR__ . '/rest.php';

const PN_CONSENT_VERSION = '1.0';
const PN_CONSENT_COOKIE  = 'pn_consent';
const PN_CONSENT_TS      = 'pn_consent_ts';
const PN_CONSENT_LIFETIME = YEAR_IN_SECONDS;

/**
 * Crea/aggiorna tabella wp_pn_consent_log all'attivazione del tema.
 */
function pn_consent_install_table(): void {
	global $wpdb;
	$table = $wpdb->prefix . 'pn_consent_log';
	$charset = $wpdb->get_charset_collate();

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( "CREATE TABLE {$table} (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		user_id BIGINT UNSIGNED NULL,
		ip_hash VARCHAR(64) NOT NULL DEFAULT '',
		user_agent VARCHAR(255) NOT NULL DEFAULT '',
		consent VARCHAR(50) NOT NULL DEFAULT '',
		categories TEXT NULL,
		version VARCHAR(10) NOT NULL DEFAULT '1.0',
		action VARCHAR(20) NOT NULL DEFAULT '',
		created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		KEY idx_user (user_id),
		KEY idx_created (created_at)
	) {$charset};" );
}
add_action( 'after_switch_theme', 'pn_consent_install_table' );

// Esegui una volta se ancora non esiste (per chi è già attivo).
add_action(
	'init',
	function () {
		global $wpdb;
		$table = $wpdb->prefix . 'pn_consent_log';
		if ( ! get_option( 'pn_consent_table_v1' ) ) {
			pn_consent_install_table();
			update_option( 'pn_consent_table_v1', '1' );
		}
	}
);

/**
 * Returns user's current consent value or empty string.
 */
function pn_consent_get(): string {
	return isset( $_COOKIE[ PN_CONSENT_COOKIE ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ PN_CONSENT_COOKIE ] ) ) : '';
}

/**
 * True se l'utente ha già fatto una scelta (qualunque).
 */
function pn_consent_decided(): bool {
	return '' !== pn_consent_get();
}

/**
 * Verifica se l'utente ha consentito una specifica categoria.
 *
 * @param string $category 'necessary' | 'statistics' | 'marketing'
 */
function pn_consent_has( string $category ): bool {
	if ( 'necessary' === $category ) {
		return true; // sempre attivi
	}
	$consent = pn_consent_get();
	if ( '' === $consent ) {
		return false;
	}
	if ( 'all' === $consent ) {
		return true;
	}
	if ( 'necessary' === $consent ) {
		return false;
	}
	return in_array( $category, array_map( 'trim', explode( ',', $consent ) ), true );
}

/**
 * Render banner + modal a fine body su pagine pubbliche.
 */
add_action(
	'wp_footer',
	function () {
		if ( is_admin() ) {
			return;
		}
		// Renderizza sempre il markup (Alpine decide se aprire);
		// così il footer link "Cookie preferences" può sempre riaprirlo.
		get_template_part( 'template-parts/shared/cookie-banner' );
	},
	5 // prima del popup (priority 100)
);

/**
 * Espone già a JS il versioning + helpers via wp_localize_script.
 * Aggancio dopo PN_DATA così non sovrappone.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! wp_script_is( 'pn-main', 'enqueued' ) ) {
			return;
		}
		wp_localize_script(
			'pn-main',
			'PN_CONSENT',
			array(
				'version'   => PN_CONSENT_VERSION,
				'restUrl'   => esc_url_raw( rest_url( 'pharmanow/v1/consent' ) ),
				'restNonce' => wp_create_nonce( 'wp_rest' ),
				'cookieName'  => PN_CONSENT_COOKIE,
				'tsCookie'    => PN_CONSENT_TS,
				'lifetime'    => PN_CONSENT_LIFETIME,
				'currentValue' => pn_consent_get(),
			)
		);
	},
	25
);
