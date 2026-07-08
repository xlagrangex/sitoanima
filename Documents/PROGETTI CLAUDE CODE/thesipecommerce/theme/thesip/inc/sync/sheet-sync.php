<?php
/**
 * Sync ordini → Google Sheet "ORDINI PHARMANOW NUOVO".
 *
 * Eseguito da crontab ogni 5 minuti:
 *   wp eval-file wp-content/themes/pharmanow/inc/sync/sheet-sync.php --allow-root
 *
 * Autenticazione: service account sheet-sync@pharmanow-sync-d8029f (JWT RS256),
 * editor del solo foglio ordini. Chiave JSON in
 * /root/.pharmanow-sheet-sync-sa.json (override: env PN_SHEET_SYNC_SA_KEY).
 *
 * Regole (concordate con Vincenzo, 2026-07-06):
 * - Solo append/insert: dedup per N. ORDINE, mai riscrivere righe esistenti.
 * - Ordine di un cliente già presente → INSERITO subito sotto le sue righe
 *   (blocchi stesso-cliente sempre adiacenti) ed eredita il suo colore.
 * - Cliente nuovo → in fondo, con la tinta successiva della palette
 *   (diversa dal blocco sopra). Il colore è uno sfondo statico su MAIL e
 *   N. ORDINE: il customer care può cambiarlo a mano, nessuna regola
 *   condizionale lo sovrascrive.
 * - Solo la prima riga prodotto del blocco porta coupon/CF/totali.
 * - Link: N. ORDINE → schermata ordine admin; COUPON → schermata coupon.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const PN_SHEET_SYNC_SPREADSHEET = '1YLWZ8G_D4Wcor8L2cqiDJOcG9qqB30nAWfll4B5N8SE';
const PN_SHEET_SYNC_TAB         = 'ORDINI DA EVADERE';
const PN_SHEET_SYNC_TAB_ID      = 494832788;
const PN_SHEET_SYNC_FIRST_ROW   = 6;

function pn_sheet_sync_palette() {
	return array( '9FC5E8', 'F9CB9C', 'B6D7A8', 'B4A7D6', 'EAD1DC' );
}

function pn_sheet_sync_log( $msg ) {
	echo '[' . gmdate( 'Y-m-d H:i:s' ) . '] ' . $msg . "\n";
}

function pn_sheet_sync_token() {
	$key_path = getenv( 'PN_SHEET_SYNC_SA_KEY' ) ?: '/root/.pharmanow-sheet-sync-sa.json';
	$sa       = json_decode( (string) file_get_contents( $key_path ), true );
	if ( empty( $sa['client_email'] ) || empty( $sa['private_key'] ) ) {
		throw new RuntimeException( 'Chiave service account mancante o invalida: ' . $key_path );
	}
	$b64 = function ( $data ) {
		return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
	};
	$now    = time();
	$header = $b64( wp_json_encode( array( 'alg' => 'RS256', 'typ' => 'JWT' ) ) );
	$claims = $b64(
		wp_json_encode(
			array(
				'iss'   => $sa['client_email'],
				'scope' => 'https://www.googleapis.com/auth/spreadsheets',
				'aud'   => 'https://oauth2.googleapis.com/token',
				'iat'   => $now,
				'exp'   => $now + 3600,
			)
		)
	);
	$sig = '';
	if ( ! openssl_sign( $header . '.' . $claims, $sig, $sa['private_key'], 'sha256WithRSAEncryption' ) ) {
		throw new RuntimeException( 'Firma JWT fallita' );
	}
	$jwt = $header . '.' . $claims . '.' . $b64( $sig );

	$res = wp_remote_post(
		'https://oauth2.googleapis.com/token',
		array(
			'timeout' => 20,
			'body'    => array(
				'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
				'assertion'  => $jwt,
			),
		)
	);
	$body = json_decode( (string) wp_remote_retrieve_body( $res ), true );
	if ( empty( $body['access_token'] ) ) {
		throw new RuntimeException( 'Token SA fallito: ' . wp_remote_retrieve_body( $res ) );
	}
	return $body['access_token'];
}

function pn_sheet_sync_api( $token, $method, $path, $payload = null ) {
	$res = wp_remote_request(
		'https://sheets.googleapis.com/v4/spreadsheets/' . PN_SHEET_SYNC_SPREADSHEET . $path,
		array(
			'method'  => $method,
			'timeout' => 30,
			'headers' => array(
				'Authorization' => 'Bearer ' . $token,
				'Content-Type'  => 'application/json',
			),
			'body'    => null !== $payload ? wp_json_encode( $payload ) : null,
		)
	);
	$code = (int) wp_remote_retrieve_response_code( $res );
	$body = json_decode( (string) wp_remote_retrieve_body( $res ), true );
	if ( $code < 200 || $code >= 300 ) {
		throw new RuntimeException( 'Sheets API ' . $code . ': ' . substr( (string) wp_remote_retrieve_body( $res ), 0, 400 ) );
	}
	return $body;
}

function pn_sheet_sync_cell( $v, $fmt = null ) {
	$cell = array();
	if ( is_int( $v ) || is_float( $v ) ) {
		$cell['userEnteredValue'] = array( 'numberValue' => $v );
	} elseif ( '' !== $v && null !== $v ) {
		$cell['userEnteredValue'] = array( 'stringValue' => (string) $v );
	}
	if ( $fmt ) {
		$cell['userEnteredFormat'] = $fmt;
	}
	// Cast a oggetto: una cella vuota deve serializzare come {} — con []
	// l'API la scarta e tutta la riga slitta a sinistra.
	return (object) $cell;
}

function pn_sheet_sync_color_to_hex( $bg ) {
	if ( ! is_array( $bg ) ) {
		return '';
	}
	$r = isset( $bg['red'] ) ? $bg['red'] : 0;
	$g = isset( $bg['green'] ) ? $bg['green'] : 0;
	$b = isset( $bg['blue'] ) ? $bg['blue'] : 0;
	return strtoupper( sprintf( '%02X%02X%02X', round( $r * 255 ), round( $g * 255 ), round( $b * 255 ) ) );
}

function pn_sheet_sync_hex_to_color( $hex ) {
	return array(
		'red'   => hexdec( substr( $hex, 0, 2 ) ) / 255,
		'green' => hexdec( substr( $hex, 2, 2 ) ) / 255,
		'blue'  => hexdec( substr( $hex, 4, 2 ) ) / 255,
	);
}

/**
 * Stato attuale del tab: righe (email, numero, colore mail), ultima riga dati.
 */
function pn_sheet_sync_read_state( $token ) {
	$range = rawurlencode( PN_SHEET_SYNC_TAB . '!B' . PN_SHEET_SYNC_FIRST_ROW . ':C3000' );
	$grid  = pn_sheet_sync_api(
		$token,
		'GET',
		'?ranges=' . $range . '&fields=' . rawurlencode( 'sheets(data(rowData(values(formattedValue,note,userEnteredFormat(backgroundColor)))))' )
	);
	$rows  = array();
	if ( isset( $grid['sheets'][0]['data'][0]['rowData'] ) ) {
		foreach ( $grid['sheets'][0]['data'][0]['rowData'] as $rd ) {
			$vals   = isset( $rd['values'] ) ? $rd['values'] : array();
			$email  = isset( $vals[0]['formattedValue'] ) ? strtolower( trim( $vals[0]['formattedValue'] ) ) : '';
			$number = isset( $vals[1]['formattedValue'] ) ? trim( $vals[1]['formattedValue'] ) : '';
			$bg     = isset( $vals[0]['userEnteredFormat']['backgroundColor'] ) ? $vals[0]['userEnteredFormat']['backgroundColor'] : null;
			$rows[] = array(
				'email'    => $email,
				'number'   => $number,
				'hex'      => $bg ? pn_sheet_sync_color_to_hex( $bg ) : '',
				'num_note' => isset( $vals[1]['note'] ) ? $vals[1]['note'] : '',
			);
		}
	}
	// taglia la coda vuota
	$last = -1;
	foreach ( $rows as $i => $r ) {
		if ( '' !== $r['email'] || '' !== $r['number'] ) {
			$last = $i;
		}
	}
	return array_slice( $rows, 0, $last + 1 );
}

function pn_sheet_sync_run() {
	if ( ! function_exists( 'pn_orders_export_collect' ) ) {
		throw new RuntimeException( 'pn_orders_export_collect non caricata (tema attivo?)' );
	}
	$data  = pn_orders_export_collect( array( 'processing', 'on-hold' ), 150 );
	$token = pn_sheet_sync_token();
	$state = pn_sheet_sync_read_state( $token );

	$existing = array();
	foreach ( $state as $r ) {
		if ( preg_match( '/^\d{6}-\d{7}$/', $r['number'] ) ) {
			$existing[ $r['number'] ] = true;
		}
		if ( preg_match( '/^\d{6}-\d{7}$/', $r['email'] ) ) {
			$existing[ $r['email'] ] = true; // riga disallineata: non duplicare mai
		}
	}

	$palette  = pn_sheet_sync_palette();
	$num_fmt  = array( 'numberFormat' => array( 'type' => 'NUMBER', 'pattern' => '0.00' ) );
	$txt_fmt  = array( 'numberFormat' => array( 'type' => 'TEXT' ) );
	$appended = 0;

	foreach ( $data['orders'] as $order ) {
		if ( isset( $existing[ $order['number'] ] ) || empty( $order['items'] ) ) {
			continue;
		}
		// i sospesi entrano solo se recenti: quelli vecchi sono zombie del
		// gateway, non ordini da evadere (i processing entrano sempre)
		if ( 'on-hold' === $order['order_status'] ) {
			$dt = DateTime::createFromFormat( 'd/m/Y', $order['date'] );
			if ( ! $dt || $dt->getTimestamp() < time() - 14 * DAY_IN_SECONDS ) {
				continue;
			}
		}

		// posizione: sotto l'ultimo blocco dello stesso cliente, altrimenti in fondo
		$insert_at = null; // indice 0-based dentro $state
		$hex       = '';
		foreach ( $state as $i => $r ) {
			if ( '' !== $order['email'] && $r['email'] === $order['email'] ) {
				$insert_at = $i + 1;
				$hex       = $r['hex'];
			}
		}
		$same_customer = null !== $insert_at;
		if ( null === $insert_at ) {
			$insert_at = count( $state );
			$last_hex  = $insert_at > 0 ? $state[ $insert_at - 1 ]['hex'] : '';
			$idx       = array_search( $last_hex, $palette, true );
			$hex       = $palette[ ( false === $idx ? $appended : $idx + 1 ) % count( $palette ) ];
		}
		if ( '' === $hex ) {
			$hex = $palette[0];
		}
		$color    = pn_sheet_sync_hex_to_color( $hex );
		$bg_fmt   = array( 'backgroundColor' => $color );
		$link_fmt = array(
			'textFormat'      => array( 'link' => array( 'uri' => $order['admin_url'] ) ),
			'backgroundColor' => $color,
		);
		$coupon_fmt = ! empty( $order['coupon_url'] )
			? array( 'textFormat' => array( 'link' => array( 'uri' => $order['coupon_url'] ) ) )
			: null;

		$block_rows = array();
		foreach ( $order['items'] as $idx => $item ) {
			$first        = 0 === $idx;
			$block_rows[] = array(
				'values' => array(
					pn_sheet_sync_cell( '' ),                                                 // A STATO
					pn_sheet_sync_cell( $order['email'], $bg_fmt ),                           // B MAIL
					pn_sheet_sync_cell( $order['number'], $link_fmt ),                        // C N. ORDINE
					pn_sheet_sync_cell( $order['payment'] ),                                  // D METODO
					pn_sheet_sync_cell( $item['minsan'], $txt_fmt ),                          // E CMS
					pn_sheet_sync_cell( $item['name'] ),                                      // F DESCRIZIONE
					pn_sheet_sync_cell( (int) $item['qty'] ),                                 // G QTA
					pn_sheet_sync_cell( (float) $item['price'], $num_fmt ),                   // H PREZZO
					pn_sheet_sync_cell( $order['date'] ),                                     // I DATA ORDINE
					pn_sheet_sync_cell( '' ),                                                 // J DATA SCONTRINO
					pn_sheet_sync_cell( $first ? $order['coupon_label'] : '', $first ? $coupon_fmt : null ), // K COUPON
					pn_sheet_sync_cell( $first ? $order['cf'] : '' ),                         // L CF
					pn_sheet_sync_cell( '' ),                                                 // M TOT. €
					pn_sheet_sync_cell( $first ? $order['totale_string'] : '' ),              // N TOTALE
					pn_sheet_sync_cell( $first ? $order['partner'] : '' ),                    // O PARTNER
					pn_sheet_sync_cell( $first ? $order['edenred'] : '' ),                    // P PAG. EDENRED
					pn_sheet_sync_cell( $first ? (float) $order['subtotal'] : '', $num_fmt ), // Q SUBTOTALE
					// R = valore card/coupon applicato; S = residuo pagato dal cliente
					pn_sheet_sync_cell( $first && $order['discount'] > 0 ? (float) $order['discount'] : '', $num_fmt ),
					pn_sheet_sync_cell( $first && $order['discount'] > 0 ? (float) $order['total'] : '', $num_fmt ),
					pn_sheet_sync_cell( $first ? (float) $order['shipping'] : '', $num_fmt ), // T SPEDIZIONE
					pn_sheet_sync_cell( $first ? (float) $order['total'] : '', $num_fmt ),    // U TOT SCONTRINO
				),
			);
		}

		$start_idx = PN_SHEET_SYNC_FIRST_ROW - 1 + $insert_at; // 0-based grid row
		$n         = count( $block_rows );
		$requests  = array(
			array(
				'insertDimension' => array(
					'range'             => array(
						'sheetId'    => PN_SHEET_SYNC_TAB_ID,
						'dimension'  => 'ROWS',
						'startIndex' => $start_idx,
						'endIndex'   => $start_idx + $n,
					),
					'inheritFromBefore' => true,
				),
			),
			array(
				'updateCells' => array(
					'range'  => array(
						'sheetId'          => PN_SHEET_SYNC_TAB_ID,
						'startRowIndex'    => $start_idx,
						'endRowIndex'      => $start_idx + $n,
						'startColumnIndex' => 0,
						'endColumnIndex'   => 21,
					),
					'fields' => 'userEnteredValue,userEnteredFormat(numberFormat,textFormat.link,backgroundColor),note',
					'rows'   => $block_rows,
				),
			),
		);

		if ( $same_customer || ! empty( $order['same_customer'] ) ) {
			// elenco completo degli ordini del cliente: quelli già nel foglio,
			// quelli in lavorazione sul sito e quello appena inserito
			$all       = array( $order['number'] => true );
			$head_idx  = null;
			foreach ( $state as $i => $r ) {
				if ( $r['email'] === $order['email'] && preg_match( '/^\d{6}-\d{7}$/', $r['number'] ) ) {
					$all[ $r['number'] ] = true;
					if ( null === $head_idx ) {
						$head_idx = $i;
					}
				}
			}
			foreach ( (array) $order['same_customer'] as $num ) {
				$all[ $num ] = true;
			}
			$nums = array_keys( $all );
			sort( $nums );
			$nota = sprintf(
				"⚠ STESSO CLIENTE — %d ordini separati da accorpare lato logistico (unico pacco, scontrini separati):\n%s",
				count( $nums ),
				implode( "\n", $nums )
			);
			$note_rows = array( $start_idx );
			if ( null !== $head_idx && $head_idx < $insert_at ) {
				$note_rows[] = PN_SHEET_SYNC_FIRST_ROW - 1 + $head_idx;
			}
			foreach ( $note_rows as $nr ) {
				$requests[] = array(
					'updateCells' => array(
						'range'  => array(
							'sheetId'          => PN_SHEET_SYNC_TAB_ID,
							'startRowIndex'    => $nr,
							'endRowIndex'      => $nr + 1,
							'startColumnIndex' => 1,
							'endColumnIndex'   => 2,
						),
						'fields' => 'note',
						'rows'   => array( array( 'values' => array( array( 'note' => $nota ) ) ) ),
					),
				);
			}
		}

		// ordine in sospeso (capture manuale WooPayments): avviso sulla cella
		// numero, rimosso automaticamente quando passa in lavorazione
		$onhold_note = '';
		if ( 'on-hold' === $order['order_status'] ) {
			$onhold_note = "⏳ IN SOSPESO — pagamento autorizzato ma NON ancora catturato.\nCatturare su WooPayments prima di spedire.";
			$requests[]  = array(
				'updateCells' => array(
					'range'  => array(
						'sheetId'          => PN_SHEET_SYNC_TAB_ID,
						'startRowIndex'    => $start_idx,
						'endRowIndex'      => $start_idx + 1,
						'startColumnIndex' => 2,
						'endColumnIndex'   => 3,
					),
					'fields' => 'note',
					'rows'   => array( array( 'values' => array( array( 'note' => $onhold_note ) ) ) ),
				),
			);
		}

		pn_sheet_sync_api( $token, 'POST', ':batchUpdate', array( 'requests' => $requests ) );

		// aggiorna il modello in memoria per gli ordini successivi dello stesso giro
		$new_rows = array();
		for ( $i = 0; $i < $n; $i++ ) {
			$new_rows[] = array(
				'email'    => $order['email'],
				'number'   => $order['number'],
				'hex'      => $hex,
				'num_note' => 0 === count( $new_rows ) ? $onhold_note : '',
			);
		}
		array_splice( $state, $insert_at, 0, $new_rows );
		$existing[ $order['number'] ] = true;
		$appended++;
	}

	// pulizia note "IN SOSPESO" ormai superate (ordine catturato → processing)
	$still_onhold = array();
	foreach ( $data['orders'] as $order ) {
		if ( 'on-hold' === $order['order_status'] ) {
			$still_onhold[ $order['number'] ] = true;
		}
	}
	$clear = array();
	foreach ( $state as $i => $r ) {
		if ( false !== strpos( $r['num_note'], 'IN SOSPESO' ) && ! isset( $still_onhold[ $r['number'] ] ) ) {
			$clear[] = array(
				'updateCells' => array(
					'range'  => array(
						'sheetId'          => PN_SHEET_SYNC_TAB_ID,
						'startRowIndex'    => PN_SHEET_SYNC_FIRST_ROW - 1 + $i,
						'endRowIndex'      => PN_SHEET_SYNC_FIRST_ROW + $i,
						'startColumnIndex' => 2,
						'endColumnIndex'   => 3,
					),
					'fields' => 'note',
					'rows'   => array( array( 'values' => array( array( 'note' => '' ) ) ) ),
				),
			);
		}
	}
	if ( $clear ) {
		pn_sheet_sync_api( $token, 'POST', ':batchUpdate', array( 'requests' => $clear ) );
	}

	pn_sheet_sync_log( sprintf( 'sync ok: %d nuovi ordini su %d (processing+sospesi), %d note sospeso rimosse (%d righe nel foglio)', $appended, $data['count'], count( $clear ), count( $state ) ) );
}

try {
	pn_sheet_sync_run();
} catch ( Exception $e ) {
	pn_sheet_sync_log( 'ERRORE: ' . $e->getMessage() );
	exit( 1 );
}
