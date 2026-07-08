<?php
/**
 * Pharmanow — Test automatico cleanup ordini pending duplicati.
 *
 * Pagina admin che crea ordini WC fittizi, triggera l'hook
 * `woocommerce_checkout_order_processed` (lo stesso del checkout reale),
 * verifica che il cleanup in `inc/checkout/cleanup-orphan-pendings.php`
 * agisca correttamente, e cancella TUTTI gli ordini di test alla fine
 * (force delete, niente cestino).
 *
 * Sicuro da eseguire in produzione:
 *  - email fittizia univoca per esecuzione (`pn-test-dup-{ts}@pharmanow.test`)
 *  - tutti gli ordini di test hanno meta `_pn_test_dup_orders` = 1
 *  - cleanup nel finally: anche se un assert lancia, gli ordini sono rimossi
 *
 * Accesso: WooCommerce → Test Ordini Duplicati (capability manage_woocommerce).
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
			__( 'Test Ordini Duplicati', 'pharmanow' ),
			__( 'Test Ordini Duplicati', 'pharmanow' ),
			'manage_woocommerce',
			'pn-dup-orders-test',
			'pn_render_dup_orders_test_page'
		);
	}
);

function pn_render_dup_orders_test_page(): void {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'Permesso negato.', 'pharmanow' ) );
	}

	$run = ! empty( $_GET['run'] ) && check_admin_referer( 'pn_dup_orders_test' );

	echo '<div class="wrap"><h1>Pharmanow — Test Cleanup Ordini Duplicati</h1>';

	if ( ! $run ) {
		echo '<p>Questo test crea ordini fittizi con email <code>pn-test-*@pharmanow.test</code>, esegue il cleanup automatico (hook <code>woocommerce_checkout_order_processed</code>), verifica i risultati per ogni scenario coperto, e poi <strong>cancella fisicamente tutti gli ordini di test</strong>.</p>';
		echo '<p>Sicuro da eseguire in produzione: gli ordini sono identificati dal meta <code>_pn_test_dup_orders</code> e force-deleted alla fine.</p>';
		echo '<p><strong>Scenari testati:</strong></p>';
		echo '<ul style="list-style:disc;margin-left:24px">';
		echo '<li>3 pending stessa email entro 30 min → tutti cancellati al checkout di un 4°</li>';
		echo '<li>Guardian pay-for-order in <code>on-hold</code> → cancellato anche se non pending</li>';
		echo '<li>Pending oltre 30 min (fuori finestra) → NON cancellato</li>';
		echo '<li>Pending di altro cliente → NON cancellato</li>';
		echo '<li>Pending già in <code>cancelled</code> → non ri-toccato</li>';
		echo '</ul>';
		$url = wp_nonce_url( admin_url( 'admin.php?page=pn-dup-orders-test&run=1' ), 'pn_dup_orders_test' );
		echo '<p style="margin-top:24px"><a href="' . esc_url( $url ) . '" class="button button-primary button-large">▶ Esegui test</a></p>';
		echo '</div>';
		return;
	}

	$results = pn_run_dup_orders_test();

	$total  = count( $results );
	$passed = count( array_filter( $results, static fn( $r ) => $r['pass'] ) );

	$banner_bg = $passed === $total ? '#dcfce7' : '#fee2e2';
	$banner_fg = $passed === $total ? '#15803d' : '#b91c1c';

	echo '<div style="background:' . esc_attr( $banner_bg ) . ';color:' . esc_attr( $banner_fg ) . ';padding:16px 20px;border-radius:8px;margin:16px 0;font-size:18px;font-weight:600">';
	echo esc_html( $passed . ' / ' . $total ) . ' check superati';
	if ( $passed === $total ) {
		echo ' — ✓ Cleanup funziona correttamente.';
	} else {
		echo ' — ✗ Qualche check fallito, vedi sotto.';
	}
	echo '</div>';

	echo '<table class="widefat striped" style="margin-top:12px"><thead><tr><th style="width:60px">Esito</th><th>Check</th><th>Dettaglio</th></tr></thead><tbody>';
	foreach ( $results as $r ) {
		$color = $r['pass'] ? '#15803d' : '#b91c1c';
		$icon  = $r['pass'] ? '✓ PASS' : '✗ FAIL';
		echo '<tr>';
		echo '<td style="color:' . esc_attr( $color ) . ';font-weight:700;font-family:monospace">' . esc_html( $icon ) . '</td>';
		echo '<td>' . esc_html( $r['name'] ) . '</td>';
		echo '<td style="font-family:monospace;font-size:12px;color:#555">' . esc_html( $r['detail'] ) . '</td>';
		echo '</tr>';
	}
	echo '</tbody></table>';

	$url = wp_nonce_url( admin_url( 'admin.php?page=pn-dup-orders-test&run=1' ), 'pn_dup_orders_test' );
	echo '<p style="margin-top:24px"><a href="' . esc_url( $url ) . '" class="button button-primary">▶ Ri-esegui</a> ';
	echo '<a href="' . esc_url( admin_url( 'admin.php?page=pn-dup-orders-test' ) ) . '" class="button">← Indietro</a></p>';
	echo '</div>';
}

/**
 * Esegue tutti gli scenari di test. Garantisce cleanup nel finally.
 *
 * @return array<int,array{name:string,pass:bool,detail:string}>
 */
function pn_run_dup_orders_test(): array {
	$results = array();
	$ts      = time();

	// Helper inline per creare ordine test.
	$create = function ( string $status, int $minutes_ago, string $email ) {
		$order = wc_create_order();
		$order->set_billing_email( $email );
		$order->set_billing_first_name( 'TEST' );
		$order->set_billing_last_name( 'DUPLICATE' );
		$order->set_status( $status );
		$order->update_meta_data( '_pn_test_dup_orders', 1 );
		// Backdate la creation date per simulare ordine vecchio.
		if ( $minutes_ago > 0 ) {
			$order->set_date_created( time() - ( $minutes_ago * 60 ) );
		}
		$order->save();
		return $order;
	};

	try {
		// ============================================================
		// SCENARIO 1 — 3 pending dello stesso cliente entro 30 min.
		// Tutti devono essere cancellati al checkout di un 4°.
		// ============================================================
		$email1 = "pn-test-dup-{$ts}-s1@pharmanow.test";
		$old1   = $create( 'pending', 25, $email1 );
		$old2   = $create( 'pending', 15, $email1 );
		$old3   = $create( 'pending', 5, $email1 );
		$new1   = $create( 'pending', 0, $email1 );

		do_action( 'woocommerce_checkout_order_processed', $new1->get_id(), array(), $new1 );

		foreach ( array( $old1, $old2, $old3 ) as $i => $o ) {
			$after = wc_get_order( $o->get_id() );
			$results[] = array(
				'name'   => 'S1 — pending #' . $o->get_id() . " (creato {$i} min fa) → cancelled",
				'pass'   => $after && $after->has_status( 'cancelled' ),
				'detail' => 'status finale: ' . ( $after ? $after->get_status() : 'NOT FOUND' ),
			);
		}
		$new1_after = wc_get_order( $new1->get_id() );
		$results[]  = array(
			'name'   => 'S1 — nuovo ordine #' . $new1->get_id() . ' resta pending (non auto-cancellato)',
			'pass'   => $new1_after && $new1_after->has_status( 'pending' ),
			'detail' => 'status finale: ' . ( $new1_after ? $new1_after->get_status() : 'NOT FOUND' ),
		);

		// ============================================================
		// SCENARIO 2 — Guardian pay-for-order: il cleanup pre-pagamento NON
		// deve toccarlo (né on-hold né pending). Si cancella solo a sostituto
		// PAGATO, via pn_pay_for_order_trash_guardian.
		// ============================================================
		$email2     = "pn-test-dup-{$ts}-s2@pharmanow.test";
		$guardian   = $create( 'on-hold', 0, $email2 );
		$replacement = $create( 'pending', 0, $email2 );
		$replacement->update_meta_data( '_pn_replaces_order_id', $guardian->get_id() );
		$replacement->save();

		do_action( 'woocommerce_checkout_order_processed', $replacement->get_id(), array(), $replacement );

		$guardian_after = wc_get_order( $guardian->get_id() );
		$results[]      = array(
			'name'   => 'S2 — guardian on-hold #' . $guardian->get_id() . ' NON cancellato pre-pagamento (resta vivo fino al pagamento del sostituto)',
			'pass'   => $guardian_after && $guardian_after->has_status( 'on-hold' ),
			'detail' => 'status finale: ' . ( $guardian_after ? $guardian_after->get_status() : 'NOT FOUND' ),
		);

		// S2b — guardian PENDING entro la finestra dup: anche questo protetto
		// dallo sweep duplicati grazie a _pn_replaces_order_id sul sostituto.
		$email2b      = "pn-test-dup-{$ts}-s2b@pharmanow.test";
		$guardian2    = $create( 'pending', 5, $email2b );
		$replacement2 = $create( 'pending', 0, $email2b );
		$replacement2->update_meta_data( '_pn_replaces_order_id', $guardian2->get_id() );
		$replacement2->save();

		do_action( 'woocommerce_checkout_order_processed', $replacement2->get_id(), array(), $replacement2 );

		$guardian2_after = wc_get_order( $guardian2->get_id() );
		$results[]       = array(
			'name'   => 'S2b — guardian pending #' . $guardian2->get_id() . ' (5 min, stessa email) escluso dallo sweep duplicati',
			'pass'   => $guardian2_after && $guardian2_after->has_status( 'pending' ),
			'detail' => 'status finale: ' . ( $guardian2_after ? $guardian2_after->get_status() : 'NOT FOUND' ),
		);

		// ============================================================
		// SCENARIO 3 — Pending OLTRE 30 min: NON deve essere toccato.
		// Importante perché potrebbe essere un ordine legittimo del cliente
		// in attesa di bonifico o gateway lento.
		// ============================================================
		$email3   = "pn-test-dup-{$ts}-s3@pharmanow.test";
		$very_old = $create( 'pending', 45, $email3 );
		$new3     = $create( 'pending', 0, $email3 );

		do_action( 'woocommerce_checkout_order_processed', $new3->get_id(), array(), $new3 );

		$very_old_after = wc_get_order( $very_old->get_id() );
		$results[]      = array(
			'name'   => 'S3 — pending vecchio #' . $very_old->get_id() . ' (45 min) NON cancellato (fuori finestra)',
			'pass'   => $very_old_after && $very_old_after->has_status( 'pending' ),
			'detail' => 'status finale: ' . ( $very_old_after ? $very_old_after->get_status() : 'NOT FOUND' ),
		);

		// ============================================================
		// SCENARIO 4 — Pending di ALTRO cliente: non deve essere toccato.
		// ============================================================
		$email4a = "pn-test-dup-{$ts}-s4a@pharmanow.test";
		$email4b = "pn-test-dup-{$ts}-s4b@pharmanow.test";
		$other   = $create( 'pending', 5, $email4a );
		$new4    = $create( 'pending', 0, $email4b );

		do_action( 'woocommerce_checkout_order_processed', $new4->get_id(), array(), $new4 );

		$other_after = wc_get_order( $other->get_id() );
		$results[]   = array(
			'name'   => 'S4 — pending di altro cliente #' . $other->get_id() . ' NON cancellato',
			'pass'   => $other_after && $other_after->has_status( 'pending' ),
			'detail' => 'status finale: ' . ( $other_after ? $other_after->get_status() : 'NOT FOUND' ),
		);

		// ============================================================
		// SCENARIO 5 — Pending già cancellato in precedenza: idempotenza,
		// nessuna nota duplicata, nessun errore.
		// ============================================================
		$email5    = "pn-test-dup-{$ts}-s5@pharmanow.test";
		$already   = $create( 'cancelled', 10, $email5 );
		$new5      = $create( 'pending', 0, $email5 );

		do_action( 'woocommerce_checkout_order_processed', $new5->get_id(), array(), $new5 );

		$already_after = wc_get_order( $already->get_id() );
		$results[]     = array(
			'name'   => 'S5 — ordine già cancelled #' . $already->get_id() . ' resta cancelled (idempotente)',
			'pass'   => $already_after && $already_after->has_status( 'cancelled' ),
			'detail' => 'status finale: ' . ( $already_after ? $already_after->get_status() : 'NOT FOUND' ),
		);

		// ============================================================
		// SCENARIO 6 — Verifica note di audit sul nuovo ordine.
		// Deve contenere riferimenti agli ordini cancellati.
		// ============================================================
		$new1_reloaded = wc_get_order( $new1->get_id() );
		$notes         = wc_get_order_notes( array( 'order_id' => $new1->get_id(), 'limit' => 50 ) );
		$cleanup_notes = array_filter(
			$notes,
			static fn( $n ) => stripos( $n->content, 'Cleanup automatico' ) !== false
		);
		$results[]     = array(
			'name'   => 'S6 — nuovo ordine #' . $new1->get_id() . ' ha note audit "Cleanup automatico"',
			'pass'   => count( $cleanup_notes ) >= 3,
			'detail' => 'note cleanup trovate: ' . count( $cleanup_notes ) . ' (atteso ≥3)',
		);

	} finally {
		// CLEANUP totale: cancella OGNI ordine creato dal test, anche se gli assert
		// hanno lanciato eccezioni.
		$test_order_ids = wc_get_orders(
			array(
				'meta_key'   => '_pn_test_dup_orders',
				'meta_value' => '1',
				'limit'      => 200,
				'return'     => 'ids',
				'status'     => 'any',
			)
		);
		$deleted = 0;
		foreach ( $test_order_ids as $id ) {
			$o = wc_get_order( $id );
			if ( ! $o ) {
				continue;
			}
			// HPOS-safe: $order->delete(true) cancella sia da wp_wc_orders sia
			// dal mirror legacy. wp_delete_post() ignora la tabella HPOS.
			if ( $o->delete( true ) ) {
				$deleted++;
			}
		}
		$results[] = array(
			'name'   => 'CLEANUP — ' . $deleted . ' / ' . count( $test_order_ids ) . ' ordini di test rimossi dal DB',
			'pass'   => $deleted === count( $test_order_ids ),
			'detail' => 'force-delete via $order->delete(true) (HPOS-safe)',
		);
	}

	return $results;
}
