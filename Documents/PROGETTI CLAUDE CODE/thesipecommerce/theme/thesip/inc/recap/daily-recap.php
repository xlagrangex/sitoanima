<?php
/**
 * Pharmanow — Daily recap email (chiusura di giornata).
 *
 * Genera il consuntivo della giornata in corso e lo invia con il template
 * grafico delle email transazionali Pharmanow (gradient teal + logo).
 * Pensato per girare in serata (~23:30): i numeri principali sono di OGGI,
 * la giornata che si sta chiudendo. Confronti con ieri e con lo stesso giorno
 * della settimana scorsa.
 * Stesso path d'invio delle email ordini (`pn_send_brevo`) per non innescare
 * il filtro marketing di Brevo.
 *
 * Uso:
 *   wp eval-file inc/recap/daily-recap.php --allow-root
 *   PN_RECAP_TO="a@x.it,b@y.it" wp eval-file inc/recap/daily-recap.php --allow-root
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

// ---------------------------------------------------------------------------
// Destinatari.
// ---------------------------------------------------------------------------
$env_to = getenv( 'PN_RECAP_TO' );
if ( $env_to ) {
	$recipients = array_filter( array_map( 'trim', explode( ',', $env_to ) ) );
} else {
	$recipients = array(
		'vincenzopetronebiz@gmail.com',
		'ettore.rossi@pharmanow.com',
		'customer-care@pharmanow.com',
	);
}

// ---------------------------------------------------------------------------
// Date di riferimento (timezone Europe/Rome).
// Recap serale: il giorno principale è OGGI (la giornata che si chiude).
// ---------------------------------------------------------------------------
$tz = new DateTimeZone( 'Europe/Rome' );

// PN_RECAP_DAY=YYYY-MM-DD ancora il report a una data specifica (test / backfill),
// con orario di riferimento fisso alle 23:30 (invio serale). Senza env: adesso.
$ref_day = getenv( 'PN_RECAP_DAY' );
if ( $ref_day && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $ref_day ) ) {
	$now = new DateTime( $ref_day . ' 23:30:00', $tz );
} else {
	$now = new DateTime( 'now', $tz );
}

$today          = $now->format( 'Y-m-d' );
$yesterday      = ( clone $now )->modify( '-1 day' )->format( 'Y-m-d' );
$last_week_same = ( clone $now )->modify( '-7 days' )->format( 'Y-m-d' );
$tz_offset      = $now->format( 'P' );

$gg_it = array( 'Domenica', 'Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato' );
$mm_it = array( '', 'gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre' );

$today_full = sprintf(
	'%s %d %s %d',
	$gg_it[ (int) $now->format( 'w' ) ],
	(int) $now->format( 'j' ),
	$mm_it[ (int) $now->format( 'n' ) ],
	(int) $now->format( 'Y' )
);

$today_dt    = new DateTime( $today, $tz );
$today_short = $today_dt->format( 'd/m/Y' );

// ---------------------------------------------------------------------------
// Helpers.
// ---------------------------------------------------------------------------
$fmt_eur = function ( $v ) {
	return number_format( (float) $v, 2, ',', '.' ) . ' EUR';
};
$fmt_int = function ( $v ) {
	return number_format( (int) $v, 0, ',', '.' );
};
$pct = function ( $now, $prev ) {
	if ( $prev <= 0 ) {
		return $now > 0 ? 'n/a' : '0%';
	}
	$delta = ( ( $now - $prev ) / $prev ) * 100;
	return sprintf( '%s%.0f%%', $delta >= 0 ? '+' : '', $delta );
};

// Espressione SQL per data di creazione reale (fallback su wp_posts.post_date_gmt
// per ordini REST API con date_created_gmt placeholder).
$created_expr = "COALESCE(NULLIF(o.date_created_gmt,'2000-01-01 00:00:00'), p.post_date_gmt, o.date_updated_gmt)";

// ---------------------------------------------------------------------------
// QUERIES.
// ---------------------------------------------------------------------------
$paid_today = $wpdb->get_row(
	$wpdb->prepare(
		"SELECT COUNT(*) AS n, COALESCE(SUM(o.total_amount),0) AS gross,
				COALESCE(SUM(op.shipping_total_amount),0) AS shipping,
				COALESCE(SUM(op.discount_total_amount),0) AS discount,
				COALESCE(AVG(o.total_amount),0) AS aov
		 FROM {$wpdb->prefix}wc_orders o
		 JOIN {$wpdb->prefix}wc_order_operational_data op ON op.order_id = o.id
		 WHERE o.type='shop_order' AND op.date_paid_gmt IS NOT NULL
		   AND DATE(CONVERT_TZ(op.date_paid_gmt,'+00:00',%s)) = %s",
		$tz_offset,
		$today
	),
	ARRAY_A
);

$paid_yest = $wpdb->get_row(
	$wpdb->prepare(
		"SELECT COUNT(*) AS n, COALESCE(SUM(o.total_amount),0) AS gross
		 FROM {$wpdb->prefix}wc_orders o
		 JOIN {$wpdb->prefix}wc_order_operational_data op ON op.order_id = o.id
		 WHERE o.type='shop_order' AND DATE(CONVERT_TZ(op.date_paid_gmt,'+00:00',%s)) = %s",
		$tz_offset,
		$yesterday
	),
	ARRAY_A
);

$paid_lw = $wpdb->get_row(
	$wpdb->prepare(
		"SELECT COUNT(*) AS n, COALESCE(SUM(o.total_amount),0) AS gross
		 FROM {$wpdb->prefix}wc_orders o
		 JOIN {$wpdb->prefix}wc_order_operational_data op ON op.order_id = o.id
		 WHERE o.type='shop_order' AND DATE(CONVERT_TZ(op.date_paid_gmt,'+00:00',%s)) = %s",
		$tz_offset,
		$last_week_same
	),
	ARRAY_A
);

$coupons_today = $wpdb->get_row(
	$wpdb->prepare(
		"SELECT COUNT(DISTINCT cl.order_id) AS orders_with_coupon,
				COALESCE(SUM(cl.discount_amount),0) AS total_discount
		 FROM {$wpdb->prefix}wc_order_coupon_lookup cl
		 WHERE DATE(CONVERT_TZ(cl.date_created,'+00:00',%s)) = %s",
		$tz_offset,
		$today
	),
	ARRAY_A
);

$closed_today = $wpdb->get_row(
	$wpdb->prepare(
		"SELECT COUNT(*) AS n, COALESCE(SUM(total_amount),0) AS gross
		 FROM {$wpdb->prefix}wc_orders
		 WHERE type='shop_order'
		   AND status IN ('wc-completed','wc-delivered','wc-shipped')
		   AND DATE(CONVERT_TZ(date_updated_gmt,'+00:00',%s)) = %s",
		$tz_offset,
		$today
	),
	ARRAY_A
);

$refunds_today = $wpdb->get_row(
	$wpdb->prepare(
		"SELECT COUNT(*) AS n, COALESCE(SUM(ABS(total_amount)),0) AS amount
		 FROM {$wpdb->prefix}wc_orders
		 WHERE type='shop_order_refund'
		   AND DATE(CONVERT_TZ(date_created_gmt,'+00:00',%s)) = %s",
		$tz_offset,
		$today
	),
	ARRAY_A
);

$open_rows = $wpdb->get_results(
	"SELECT status, COUNT(*) AS n, COALESCE(SUM(total_amount),0) AS tot
	 FROM {$wpdb->prefix}wc_orders
	 WHERE type='shop_order' AND status IN ('wc-pending','wc-on-hold','wc-processing')
	 GROUP BY status",
	ARRAY_A
);

$by_status = array(
	'wc-pending'    => array( 'n' => 0, 'tot' => 0 ),
	'wc-on-hold'    => array( 'n' => 0, 'tot' => 0 ),
	'wc-processing' => array( 'n' => 0, 'tot' => 0 ),
);
foreach ( $open_rows as $r ) {
	$by_status[ $r['status'] ] = array( 'n' => (int) $r['n'], 'tot' => (float) $r['tot'] );
}

$processing_aging = $wpdb->get_row(
	"SELECT
		SUM(CASE WHEN age_hours < 24 THEN 1 ELSE 0 END) AS lt24,
		SUM(CASE WHEN age_hours >= 24 AND age_hours < 48 THEN 1 ELSE 0 END) AS h24_48,
		SUM(CASE WHEN age_hours >= 48 AND age_hours < 72 THEN 1 ELSE 0 END) AS h48_72,
		SUM(CASE WHEN age_hours >= 72 AND age_hours < 168 THEN 1 ELSE 0 END) AS gt72,
		SUM(CASE WHEN age_hours >= 168 THEN 1 ELSE 0 END) AS gt7d
	 FROM (
		SELECT TIMESTAMPDIFF(HOUR, {$created_expr}, UTC_TIMESTAMP()) AS age_hours
		FROM {$wpdb->prefix}wc_orders o
		LEFT JOIN {$wpdb->prefix}posts p ON p.ID = o.id
		WHERE o.type='shop_order' AND o.status='wc-processing'
	 ) ages",
	ARRAY_A
);

$new_aging = $wpdb->get_row(
	"SELECT
		SUM(CASE WHEN age_hours < 24 THEN 1 ELSE 0 END) AS lt24,
		SUM(CASE WHEN age_hours >= 24 AND age_hours < 48 THEN 1 ELSE 0 END) AS h24_48,
		SUM(CASE WHEN age_hours >= 48 AND age_hours < 72 THEN 1 ELSE 0 END) AS h48_72,
		SUM(CASE WHEN age_hours >= 72 THEN 1 ELSE 0 END) AS gt72
	 FROM (
		SELECT TIMESTAMPDIFF(HOUR, {$created_expr}, UTC_TIMESTAMP()) AS age_hours
		FROM {$wpdb->prefix}wc_orders o
		LEFT JOIN {$wpdb->prefix}posts p ON p.ID = o.id
		WHERE o.type='shop_order' AND o.status IN ('wc-pending','wc-on-hold')
	 ) ages",
	ARRAY_A
);

$oldest_processing = $wpdb->get_results(
	"SELECT
		o.id, o.total_amount, o.billing_email, o.payment_method_title,
		TIMESTAMPDIFF(HOUR, {$created_expr}, UTC_TIMESTAMP()) AS age_hours,
		DATE_FORMAT(CONVERT_TZ({$created_expr},'+00:00','{$tz_offset}'),'%d/%m %H:%i') AS created_local
	 FROM {$wpdb->prefix}wc_orders o
	 LEFT JOIN {$wpdb->prefix}posts p ON p.ID = o.id
	 WHERE o.type='shop_order' AND o.status='wc-processing'
	 ORDER BY age_hours DESC
	 LIMIT 10",
	ARRAY_A
);

$failed_recent = $wpdb->get_results(
	"SELECT o.id, o.total_amount, o.billing_email,
			DATE_FORMAT(CONVERT_TZ(o.date_updated_gmt,'+00:00','{$tz_offset}'),'%d/%m %H:%i') AS updated_local
	 FROM {$wpdb->prefix}wc_orders o
	 WHERE o.type='shop_order' AND o.status='wc-failed'
	   AND o.date_updated_gmt >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 48 HOUR)
	 ORDER BY o.date_updated_gmt DESC
	 LIMIT 10",
	ARRAY_A
);

// ---------------------------------------------------------------------------
// Calcoli sintesi.
// ---------------------------------------------------------------------------
$total_open       = array_sum( array_map( fn( $s ) => $s['n'], $by_status ) );
$total_open_value = array_sum( array_map( fn( $s ) => $s['tot'], $by_status ) );
$new_n            = $by_status['wc-pending']['n'] + $by_status['wc-on-hold']['n'];
$new_tot          = $by_status['wc-pending']['tot'] + $by_status['wc-on-hold']['tot'];
$proc_n           = $by_status['wc-processing']['n'];
$proc_tot         = $by_status['wc-processing']['tot'];

// ---------------------------------------------------------------------------
// Subject: stile transazionale, niente emoji, "EUR" invece di simbolo.
// ---------------------------------------------------------------------------
$subject = sprintf(
	'Recap Pharmanow del %s - %d ordini oggi, %d aperti',
	$today_short,
	(int) $paid_today['n'],
	$total_open
);

// ---------------------------------------------------------------------------
// HTML — usa pn_email_header / pn_email_footer / pn_btn del mu-plugin.
// ---------------------------------------------------------------------------
if ( ! function_exists( 'pn_email_header' ) ) {
	if ( defined( 'WP_CLI' ) && class_exists( 'WP_CLI' ) ) {
		WP_CLI::error( 'Mu-plugin Brevo non caricato.' );
	}
	return;
}

$ACCENT = '#22577a';
$TEAL   = '#38a3a5';
$INK    = '#1a1a1a';
$MUTED  = '#666';
$LINE   = '#eee';
$SOFTBG = '#f8fafc';

$html  = pn_email_header( 'Recap di fine giornata' );
$html .= '<h1 style="font-size:20px;font-weight:700;color:' . $INK . ';margin:0 0 6px;text-align:center;">' . esc_html( $today_full ) . '</h1>';
$html .= '<p style="text-align:center;color:' . $MUTED . ';font-size:13px;margin:0 0 4px;">Oggi ' . esc_html( $fmt_int( $paid_today['n'] ) ) . ' ordini pagati per ' . esc_html( $fmt_eur( $paid_today['gross'] ) ) . '</p>';
$html .= '<p style="text-align:center;color:#999;font-size:12px;margin:0 0 22px;">Restano aperti: ' . esc_html( $fmt_int( $total_open ) ) . ' ordini, ' . esc_html( $fmt_eur( $total_open_value ) ) . ' esposti</p>';

// ---------- OGGI ----------
$html .= '<h2 style="font-size:14px;font-weight:700;color:' . $INK . ';margin:0 0 10px;letter-spacing:0.3px;text-transform:uppercase;border-bottom:1px solid ' . $LINE . ';padding-bottom:6px;">Com\'è andata oggi (' . esc_html( $today_short ) . ')</h2>';

// Tabella riepilogo: chiave/valore stile pn_totals
$html .= '<table width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;color:#555;margin:8px 0 16px;">';
$html .= '<tr><td style="padding:5px 0;">Incassato lordo</td><td style="text-align:right;font-weight:700;color:' . $INK . ';">' . esc_html( $fmt_eur( $paid_today['gross'] ) ) . '</td></tr>';
$html .= '<tr><td style="padding:5px 0;">Ordini pagati</td><td style="text-align:right;font-weight:600;color:' . $INK . ';">' . esc_html( $fmt_int( $paid_today['n'] ) ) . '</td></tr>';
$html .= '<tr><td style="padding:5px 0;">Valore medio ordine (AOV)</td><td style="text-align:right;font-weight:600;color:' . $INK . ';">' . esc_html( $fmt_eur( $paid_today['aov'] ) ) . '</td></tr>';
$html .= '<tr><td style="padding:5px 0;">Spedizione incassata</td><td style="text-align:right;color:' . $INK . ';">' . esc_html( $fmt_eur( $paid_today['shipping'] ) ) . '</td></tr>';
$html .= '<tr><td style="padding:5px 0;">Coupon applicati</td><td style="text-align:right;color:' . $INK . ';">' . esc_html( $fmt_eur( $coupons_today['total_discount'] ) ) . ' su ' . (int) $coupons_today['orders_with_coupon'] . ' ordini</td></tr>';
if ( (int) $refunds_today['n'] > 0 ) {
	$html .= '<tr><td style="padding:5px 0;color:#b91c1c;">Rimborsi</td><td style="text-align:right;color:#b91c1c;font-weight:600;">-' . esc_html( $fmt_eur( $refunds_today['amount'] ) ) . ' (' . (int) $refunds_today['n'] . ')</td></tr>';
}
$html .= '</table>';

// Box verde "chiusi oggi" — stile uguale al box "Consegna stimata" della transazionale processing
$html .= '<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;padding:12px;margin:0 0 16px;text-align:center;">';
$html .= '<div style="font-size:13px;color:#166534;font-weight:600;">Ordini chiusi oggi: ' . esc_html( $fmt_int( $closed_today['n'] ) ) . ' per ' . esc_html( $fmt_eur( $closed_today['gross'] ) ) . '</div>';
$html .= '<div style="font-size:11px;color:#15803d;margin-top:3px;">(stato shipped, delivered, completed)</div>';
$html .= '</div>';

// Confronti
$html .= '<table width="100%" cellpadding="0" cellspacing="0" style="font-size:12px;color:' . $MUTED . ';margin:0 0 20px;">';
$html .= '<tr><td style="padding:3px 0;">vs ieri (' . esc_html( ( new DateTime( $yesterday ) )->format( 'd/m' ) ) . ')</td><td style="text-align:right;">' . esc_html( $fmt_eur( $paid_yest['gross'] ) ) . ' &middot; <strong>' . esc_html( $pct( (float) $paid_today['gross'], (float) $paid_yest['gross'] ) ) . '</strong></td></tr>';
$html .= '<tr><td style="padding:3px 0;">vs stesso giorno settimana scorsa (' . esc_html( ( new DateTime( $last_week_same ) )->format( 'd/m' ) ) . ')</td><td style="text-align:right;">' . esc_html( $fmt_eur( $paid_lw['gross'] ) ) . ' &middot; <strong>' . esc_html( $pct( (float) $paid_today['gross'], (float) $paid_lw['gross'] ) ) . '</strong></td></tr>';
$html .= '</table>';

// ---------- STATO ATTUALE ----------
$html .= '<h2 style="font-size:14px;font-weight:700;color:' . $INK . ';margin:18px 0 10px;letter-spacing:0.3px;text-transform:uppercase;border-bottom:1px solid ' . $LINE . ';padding-bottom:6px;">Cosa resta aperto a fine giornata (' . esc_html( $now->format( 'H:i' ) ) . ')</h2>';

// Stato attuale: tabella riassuntiva senza card colorate (più sobrio, più transazionale)
$html .= '<table width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;color:#555;margin:8px 0 16px;">';
$html .= '<tr style="border-bottom:1px solid ' . $LINE . ';"><td style="padding:8px 0;">Nuovi (pending + on-hold)</td><td style="text-align:right;font-weight:700;color:' . $INK . ';font-size:15px;">' . esc_html( $fmt_int( $new_n ) ) . '</td><td style="text-align:right;color:' . $MUTED . ';padding-left:12px;width:120px;">' . esc_html( $fmt_eur( $new_tot ) ) . '</td></tr>';
$html .= '<tr style="border-bottom:1px solid ' . $LINE . ';"><td style="padding:8px 0;">In lavorazione (processing)</td><td style="text-align:right;font-weight:700;color:' . $INK . ';font-size:15px;">' . esc_html( $fmt_int( $proc_n ) ) . '</td><td style="text-align:right;color:' . $MUTED . ';padding-left:12px;">' . esc_html( $fmt_eur( $proc_tot ) ) . '</td></tr>';
$html .= '<tr><td style="padding:8px 0;font-weight:600;color:' . $INK . ';border-top:2px solid ' . $ACCENT . ';">Totale aperti</td><td style="text-align:right;font-weight:700;color:' . $INK . ';font-size:15px;border-top:2px solid ' . $ACCENT . ';">' . esc_html( $fmt_int( $total_open ) ) . '</td><td style="text-align:right;font-weight:600;color:' . $INK . ';padding-left:12px;border-top:2px solid ' . $ACCENT . ';">' . esc_html( $fmt_eur( $total_open_value ) ) . '</td></tr>';
$html .= '</table>';

// ---------- ANZIANITÀ ----------
$html .= '<h3 style="font-size:13px;font-weight:700;color:' . $INK . ';margin:14px 0 8px;">Anzianità ordini "in lavorazione"</h3>';

$html .= '<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:12px;color:#555;margin:0 0 16px;">';
$html .= '<tr style="background:' . $SOFTBG . ';">';
$html .= '<td style="padding:8px 6px;text-align:center;border-right:1px solid ' . $LINE . ';">Meno di 24h</td>';
$html .= '<td style="padding:8px 6px;text-align:center;border-right:1px solid ' . $LINE . ';">24-48h</td>';
$html .= '<td style="padding:8px 6px;text-align:center;border-right:1px solid ' . $LINE . ';">48-72h</td>';
$html .= '<td style="padding:8px 6px;text-align:center;border-right:1px solid ' . $LINE . ';">3-7 giorni</td>';
$html .= '<td style="padding:8px 6px;text-align:center;">Oltre 7 giorni</td>';
$html .= '</tr>';
$html .= '<tr>';
$html .= '<td style="padding:10px 6px;text-align:center;font-size:20px;font-weight:700;color:#166534;border-right:1px solid ' . $LINE . ';">' . esc_html( $fmt_int( $processing_aging['lt24'] ) ) . '</td>';
$html .= '<td style="padding:10px 6px;text-align:center;font-size:20px;font-weight:700;color:#b45309;border-right:1px solid ' . $LINE . ';">' . esc_html( $fmt_int( $processing_aging['h24_48'] ) ) . '</td>';
$html .= '<td style="padding:10px 6px;text-align:center;font-size:20px;font-weight:700;color:#c2410c;border-right:1px solid ' . $LINE . ';">' . esc_html( $fmt_int( $processing_aging['h48_72'] ) ) . '</td>';
$html .= '<td style="padding:10px 6px;text-align:center;font-size:20px;font-weight:700;color:#b91c1c;border-right:1px solid ' . $LINE . ';">' . esc_html( $fmt_int( $processing_aging['gt72'] ) ) . '</td>';
$html .= '<td style="padding:10px 6px;text-align:center;font-size:20px;font-weight:700;color:#991b1b;">' . esc_html( $fmt_int( $processing_aging['gt7d'] ) ) . '</td>';
$html .= '</tr>';
$html .= '</table>';

$html .= '<p style="font-size:12px;color:' . $MUTED . ';margin:0 0 18px;">Nuovi (pending+on-hold): ' . esc_html( $fmt_int( $new_aging['lt24'] ) ) . ' sotto le 24h &middot; ' . esc_html( $fmt_int( $new_aging['h24_48'] ) ) . ' tra 24 e 48h &middot; ' . esc_html( $fmt_int( $new_aging['h48_72'] ) ) . ' tra 48 e 72h &middot; ' . esc_html( $fmt_int( $new_aging['gt72'] ) ) . ' oltre 72h.</p>';

// ---------- ALERT: oldest processing ----------
if ( ! empty( $oldest_processing ) ) {
	$html .= '<h3 style="font-size:13px;font-weight:700;color:' . $INK . ';margin:18px 0 8px;">Ordini più vecchi in lavorazione</h3>';
	$html .= '<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:12px;margin:0 0 16px;">';
	$html .= '<tr style="background:' . $SOFTBG . ';color:' . $MUTED . ';font-weight:600;">';
	$html .= '<td style="padding:8px 6px;text-align:left;">Ordine</td>';
	$html .= '<td style="padding:8px 6px;text-align:left;">Creato</td>';
	$html .= '<td style="padding:8px 6px;text-align:left;">Età</td>';
	$html .= '<td style="padding:8px 6px;text-align:left;">Cliente</td>';
	$html .= '<td style="padding:8px 6px;text-align:right;">Totale</td>';
	$html .= '</tr>';

	foreach ( $oldest_processing as $r ) {
		$hours     = (int) $r['age_hours'];
		$days      = (int) floor( $hours / 24 );
		$age_label = $days >= 2 ? $days . ' giorni' : $hours . ' ore';
		$age_color = $hours >= 168 ? '#991b1b' : ( $hours >= 72 ? '#b91c1c' : ( $hours >= 48 ? '#c2410c' : ( $hours >= 24 ? '#b45309' : '#166534' ) ) );
		$order_url = admin_url( 'admin.php?page=wc-orders&action=edit&id=' . (int) $r['id'] );

		$html .= '<tr style="border-bottom:1px solid ' . $LINE . ';">';
		$html .= '<td style="padding:8px 6px;font-family:monospace;"><a href="' . esc_url( $order_url ) . '" style="color:' . $TEAL . ';text-decoration:none;">#' . (int) $r['id'] . '</a></td>';
		$html .= '<td style="padding:8px 6px;color:' . $MUTED . ';">' . esc_html( $r['created_local'] ) . '</td>';
		$html .= '<td style="padding:8px 6px;font-weight:700;color:' . $age_color . ';">' . esc_html( $age_label ) . '</td>';
		$html .= '<td style="padding:8px 6px;color:#333;">' . esc_html( $r['billing_email'] ?: '-' ) . '</td>';
		$html .= '<td style="padding:8px 6px;text-align:right;font-weight:600;color:' . $INK . ';">' . esc_html( $fmt_eur( $r['total_amount'] ) ) . '</td>';
		$html .= '</tr>';
	}
	$html .= '</table>';
}

// ---------- ALERT: failed recenti ----------
if ( ! empty( $failed_recent ) ) {
	$html .= '<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:12px 14px;margin:8px 0 16px;">';
	$html .= '<div style="font-size:13px;font-weight:700;color:#991b1b;margin-bottom:6px;">Ordini falliti nelle ultime 48 ore: ' . count( $failed_recent ) . '</div>';
	$html .= '<table width="100%" cellpadding="0" cellspacing="0" style="font-size:12px;color:#7f1d1d;">';
	foreach ( $failed_recent as $f ) {
		$html .= '<tr><td style="padding:2px 0;font-family:monospace;">#' . (int) $f['id'] . '</td><td style="padding:2px 0;">' . esc_html( $f['updated_local'] ) . '</td><td style="padding:2px 0;text-align:right;">' . esc_html( $fmt_eur( $f['total_amount'] ) ) . '</td></tr>';
	}
	$html .= '</table>';
	$html .= '</div>';
}

// ---------- CTA ----------
$admin_url = defined( 'PN_SITE_URL' ) ? PN_SITE_URL . '/wp-admin/admin.php?page=wc-orders&status=processing' : admin_url( 'admin.php?page=wc-orders&status=processing' );
$html     .= pn_btn( 'Apri ordini in lavorazione', $admin_url );

$html .= pn_email_footer();

// ---------------------------------------------------------------------------
// Preview file (per debug / archivio storico).
// ---------------------------------------------------------------------------
$preview_dir  = '/var/www/pharmanow/recap-preview';
$preview_name = sprintf( 'recap-%s.html', $now->format( 'Ymd-His' ) );
$preview_path = $preview_dir . '/' . $preview_name;

if ( ! is_dir( $preview_dir ) ) {
	@mkdir( $preview_dir, 0755, true );
}
@file_put_contents( $preview_path, $html );

// ---------------------------------------------------------------------------
// Invio via pn_send_brevo() del mu-plugin.
// ---------------------------------------------------------------------------
$to_csv = implode( ',', $recipients );
$ok     = pn_send_brevo( $to_csv, $subject, $html, false, 'daily_recap', null );

if ( defined( 'WP_CLI' ) && class_exists( 'WP_CLI' ) ) {
	WP_CLI::log( 'Preview: https://pharmanow.com/recap-preview/' . $preview_name );
	if ( $ok ) {
		WP_CLI::success( 'Email inviata a: ' . $to_csv );
	} else {
		WP_CLI::warning( 'pn_send_brevo() ha restituito false. Verifica email_logs.' );
	}
}
