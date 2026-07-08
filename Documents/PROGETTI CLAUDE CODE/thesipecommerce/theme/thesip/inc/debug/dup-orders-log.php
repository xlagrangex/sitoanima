<?php
/**
 * Pharmanow — Log monitoring cleanup ordini duplicati.
 *
 * Visualizza in admin la cronologia degli interventi automatici del cleanup
 * (option `pn_dup_cleanup_log`, max 200 entries). Permette di verificare in
 * produzione quante volte il fix sta intercettando duplicati reali.
 *
 * Accesso: WooCommerce → Log Cleanup Duplicati (manage_woocommerce).
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'admin_menu',
	function () {
		if ( ! function_exists( 'wc' ) ) {
			return;
		}
		add_submenu_page(
			'woocommerce',
			__( 'Log Cleanup Duplicati', 'pharmanow' ),
			__( 'Log Cleanup Duplicati', 'pharmanow' ),
			'manage_woocommerce',
			'pn-dup-orders-log',
			'pn_render_dup_orders_log_page'
		);
	}
);

function pn_render_dup_orders_log_page(): void {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'Permesso negato.', 'pharmanow' ) );
	}

	// Reset log se richiesto.
	if ( ! empty( $_GET['reset'] ) && check_admin_referer( 'pn_dup_orders_log_reset' ) ) {
		delete_option( 'pn_dup_cleanup_log' );
		echo '<div class="notice notice-success"><p>Log azzerato.</p></div>';
	}

	$log = get_option( 'pn_dup_cleanup_log', array() );
	if ( ! is_array( $log ) ) {
		$log = array();
	}

	echo '<div class="wrap"><h1>Pharmanow — Log Cleanup Ordini Duplicati</h1>';
	echo '<p>Cronologia degli interventi automatici del cleanup pending duplicati (max 200 entries più recenti).</p>';

	// Statistiche aggregate.
	$total_events     = count( $log );
	$total_duplicates = array_sum( array_map( static fn( $e ) => count( $e['duplicates'] ?? array() ), $log ) );
	$total_guardians  = count( array_filter( $log, static fn( $e ) => ! empty( $e['guardian'] ) ) );

	$since_7d = time() - ( 7 * DAY_IN_SECONDS );
	$last_7d  = count( array_filter( $log, static fn( $e ) => ( $e['ts'] ?? 0 ) >= $since_7d ) );

	echo '<div style="display:flex;gap:16px;margin:16px 0">';
	pn_dup_log_stat_box( 'Eventi totali', $total_events, '#1d4ed8' );
	pn_dup_log_stat_box( 'Negli ultimi 7 gg', $last_7d, '#15803d' );
	pn_dup_log_stat_box( 'Pending cancellati', $total_duplicates, '#b45309' );
	pn_dup_log_stat_box( 'Guardian cancellati', $total_guardians, '#9333ea' );
	echo '</div>';

	if ( empty( $log ) ) {
		echo '<div class="notice notice-info"><p><strong>Log vuoto.</strong> Nessun cleanup ancora eseguito. Significa o che il fix non ha avuto occasione di agire (nessun cliente ha generato duplicati dal deploy), o che la option è stata appena resettata.</p></div>';
		echo '</div>';
		return;
	}

	$reset_url = wp_nonce_url( admin_url( 'admin.php?page=pn-dup-orders-log&reset=1' ), 'pn_dup_orders_log_reset' );
	echo '<p><a href="' . esc_url( $reset_url ) . '" class="button" onclick="return confirm(\'Azzerare il log? L\\\'azione è irreversibile.\')">⟲ Azzera log</a></p>';

	echo '<table class="widefat striped"><thead><tr>';
	echo '<th style="width:160px">Quando</th>';
	echo '<th style="width:90px">Nuovo</th>';
	echo '<th>Email cliente</th>';
	echo '<th>Duplicati cancellati</th>';
	echo '<th style="width:90px">Guardian</th>';
	echo '</tr></thead><tbody>';

	foreach ( $log as $entry ) {
		$ts    = (int) ( $entry['ts'] ?? 0 );
		$new   = (int) ( $entry['new_order_id'] ?? 0 );
		$email = (string) ( $entry['email'] ?? '' );
		$dups  = (array) ( $entry['duplicates'] ?? array() );
		$guard = $entry['guardian'] ?? null;

		$when = $ts ? wp_date( 'd/m/Y H:i:s', $ts ) : '?';
		$ago  = $ts ? human_time_diff( $ts, time() ) . ' fa' : '';

		echo '<tr>';
		echo '<td>' . esc_html( $when ) . '<br><small style="color:#777">' . esc_html( $ago ) . '</small></td>';
		echo '<td>' . pn_dup_log_order_link( $new ) . '</td>';
		echo '<td><code>' . esc_html( $email ) . '</code></td>';
		echo '<td>';
		if ( empty( $dups ) ) {
			echo '<em style="color:#777">nessuno</em>';
		} else {
			$links = array_map( 'pn_dup_log_order_link', $dups );
			echo implode( ', ', $links );
		}
		echo '</td>';
		echo '<td>' . ( $guard ? pn_dup_log_order_link( (int) $guard ) : '—' ) . '</td>';
		echo '</tr>';
	}

	echo '</tbody></table>';
	echo '</div>';
}

function pn_dup_log_stat_box( string $label, int $value, string $color ): void {
	echo '<div style="flex:1;background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:16px 20px">';
	echo '<div style="font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;font-weight:600">' . esc_html( $label ) . '</div>';
	echo '<div style="font-size:32px;font-weight:700;color:' . esc_attr( $color ) . ';margin-top:4px">' . esc_html( (string) $value ) . '</div>';
	echo '</div>';
}

function pn_dup_log_order_link( int $order_id ): string {
	if ( ! $order_id ) {
		return '<em>—</em>';
	}
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return '<span style="color:#999">#' . esc_html( (string) $order_id ) . ' (eliminato)</span>';
	}
	$edit_url = $order->get_edit_order_url();
	$status   = $order->get_status();
	return '<a href="' . esc_url( $edit_url ) . '" target="_blank">#' . esc_html( (string) $order_id ) . '</a> <small style="color:#777">(' . esc_html( $status ) . ')</small>';
}
