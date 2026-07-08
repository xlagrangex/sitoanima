<?php
/**
 * Email branding Pharmanow — sostituisce le email plain-text di WP/WC.
 *
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wrapper HTML brandizzato. Logo + header teal + body + footer disclaimer.
 *
 * @param string $body_html
 * @param string $title
 * @return string
 */
function pn_email_wrap( string $body_html, string $title = '' ): string {
	$site = get_bloginfo( 'name' );
	$logo = pn_asset( 'images/logo-pharmanow.svg' );
	$year = gmdate( 'Y' );
	$home = home_url( '/' );

	ob_start();
	?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo esc_html( $title ?: $site ); ?></title>
</head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#0f172a;-webkit-font-smoothing:antialiased;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f6f8;padding:32px 16px;">
	<tr>
		<td align="center">
			<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.08);">
				<!-- Header: gradient teal -->
				<tr>
					<td style="background:linear-gradient(135deg,#0a6e9e 0%,#07496b 100%);padding:32px;text-align:center;">
						<a href="<?php echo esc_url( $home ); ?>" style="display:inline-block;text-decoration:none;">
							<img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $site ); ?>" width="160" style="display:block;height:auto;filter:brightness(0) invert(1);">
						</a>
					</td>
				</tr>
				<!-- Body -->
				<tr>
					<td style="padding:32px;font-size:15px;line-height:1.6;color:#0f172a;">
						<?php echo $body_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</td>
				</tr>
				<!-- Footer -->
				<tr>
					<td style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:20px 32px;font-size:12px;line-height:1.6;color:#64748b;text-align:center;">
						<p style="margin:0 0 8px 0;">
							<strong style="color:#0a6e9e;"><?php echo esc_html( $site ); ?></strong> · The Sea In your Pocket — flashcard illustrate di biologia marina
						</p>
						<p style="margin:0;">
							© <?php echo esc_html( $year ); ?> <?php echo esc_html( $site ); ?>. Tutti i diritti riservati.<br>
							Hai ricevuto questa email perché è associata a un account su <a href="<?php echo esc_url( $home ); ?>" style="color:#0a6e9e;text-decoration:none;"><?php echo esc_html( wp_parse_url( $home, PHP_URL_HOST ) ); ?></a>.
						</p>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</body>
</html>
	<?php
	return (string) ob_get_clean();
}

/**
 * Bottone CTA inline (table-based per compatibility client mail).
 */
function pn_email_button( string $href, string $label ): string {
	return sprintf(
		'<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:24px 0;"><tr><td style="background:linear-gradient(135deg,#0a6e9e 0%%,#07496b 100%%);border-radius:8px;"><a href="%s" target="_blank" style="display:inline-block;padding:14px 28px;color:#ffffff;text-decoration:none;font-weight:600;font-size:15px;letter-spacing:0.02em;">%s</a></td></tr></table>',
		esc_url( $href ),
		esc_html( $label )
	);
}

/* ============================================================
   Reset password email — sostituisce template WP nativo.
   ============================================================ */

/**
 * Forza HTML content-type quando inviamo l'email reset.
 */
add_filter(
	'retrieve_password_notification_email',
	function ( $defaults, $key, $user_login, $user_data ) {
		$reset_url = add_query_arg(
			array(
				'key'   => $key,
				'login' => rawurlencode( $user_login ),
			),
			home_url( '/reset-password/' )
		);

		$first    = trim( (string) $user_data->first_name ) ?: ( explode( '@', $user_data->user_email )[0] );
		$ip       = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$site     = get_bloginfo( 'name' );

		$body  = '';
		$body .= '<h1 style="margin:0 0 16px 0;font-size:22px;font-weight:700;color:#0f172a;">Reimposta la tua password</h1>';
		$body .= '<p style="margin:0 0 12px 0;">Ciao ' . esc_html( $first ) . ',</p>';
		$body .= '<p style="margin:0 0 12px 0;">Hai richiesto di reimpostare la password del tuo account su <strong>' . esc_html( $site ) . '</strong>. Clicca il bottone qui sotto per impostare una nuova password:</p>';
		$body .= pn_email_button( $reset_url, 'Reimposta la password' );
		$body .= '<p style="margin:0 0 12px 0;font-size:13px;color:#64748b;">Il link è valido per 24 ore. Se non hai richiesto tu il reset, ignora questa email — la tua password resterà invariata.</p>';
		$body .= '<p style="margin:24px 0 0 0;font-size:12px;color:#94a3b8;border-top:1px solid #e2e8f0;padding-top:16px;">Se il bottone non funziona, copia e incolla questo link nel browser:<br><a href="' . esc_url( $reset_url ) . '" style="color:#0a6e9e;word-break:break-all;">' . esc_html( $reset_url ) . '</a></p>';
		if ( $ip ) {
			$body .= '<p style="margin:8px 0 0 0;font-size:11px;color:#94a3b8;">Richiesta originata dall\'indirizzo IP ' . esc_html( $ip ) . '.</p>';
		}

		$defaults['subject'] = sprintf( 'Reimposta la password — %s', $site );
		$defaults['message'] = pn_email_wrap( $body, $defaults['subject'] );
		$defaults['headers'] = array_merge(
			(array) ( $defaults['headers'] ?? array() ),
			array( 'Content-Type: text/html; charset=UTF-8' )
		);
		return $defaults;
	},
	10,
	4
);

/* ============================================================
   New account email (welcome dopo registrazione) — branding via WC.
   WooCommerce già genera l'email con il template HTML del tema.
   Qui sovrascriviamo solo il subject per allinearlo al brand.
   ============================================================ */
add_filter(
	'woocommerce_email_subject_customer_new_account',
	function () {
		return sprintf( 'Benvenuto su %s 🎉', get_bloginfo( 'name' ) );
	}
);
