<?php
/**
 * Contact Form
 *
 * Built-in contact form via shortcode [bizstudio_contact_form].
 * AJAX submission, honeypot spam protection, rate limiting.
 *
 * @package BizStudio
 */

defined( 'ABSPATH' ) || exit;

/* ──────────────────────────────────────────────
   1. Shortcode Registration
   ────────────────────────────────────────────── */

add_shortcode( 'bizstudio_contact_form', 'bizstudio_contact_form_render' );

/**
 * Render the contact form.
 *
 * @param array $atts Shortcode attributes.
 * @return string Form HTML.
 */
function bizstudio_contact_form_render( $atts ) {

	$atts = shortcode_atts( [
		'to'             => get_option( 'admin_email' ),
		'subject_prefix' => '[BizStudio]',
	], $atts, 'bizstudio_contact_form' );

	// Enqueue the component CSS
	wp_enqueue_style(
		'bizstudio-contact-form',
		BIZSTUDIO_URI . '/assets/src/css/components/contact-form.css',
		[ 'bizstudio-main', 'bizstudio-forms' ],
		BIZSTUDIO_VERSION
	);

	$nonce = wp_create_nonce( 'bizstudio_contact_nonce' );

	ob_start();
	?>
	<div class="biz-contact-form">
		<form class="biz-contact-form__form" novalidate>

			<?php wp_nonce_field( 'bizstudio_contact_nonce', 'bizstudio_contact_nonce_field' ); ?>
			<input type="hidden" name="biz_cf_to" value="<?php echo esc_attr( $atts['to'] ); ?>">
			<input type="hidden" name="biz_cf_prefix" value="<?php echo esc_attr( $atts['subject_prefix'] ); ?>">

			<div class="biz-contact-form__row">
				<div class="biz-contact-form__field">
					<label for="biz-cf-nome"><?php esc_html_e( 'Nome', 'bizstudio' ); ?> <span class="required">*</span></label>
					<input type="text" id="biz-cf-nome" name="biz_cf_nome" placeholder="<?php esc_attr_e( 'Il tuo nome', 'bizstudio' ); ?>" required>
				</div>
				<div class="biz-contact-form__field">
					<label for="biz-cf-email"><?php esc_html_e( 'Email', 'bizstudio' ); ?> <span class="required">*</span></label>
					<input type="email" id="biz-cf-email" name="biz_cf_email" placeholder="<?php esc_attr_e( 'La tua email', 'bizstudio' ); ?>" required>
				</div>
			</div>

			<div class="biz-contact-form__field">
				<label for="biz-cf-oggetto"><?php esc_html_e( 'Oggetto', 'bizstudio' ); ?></label>
				<input type="text" id="biz-cf-oggetto" name="biz_cf_oggetto" placeholder="<?php esc_attr_e( 'Oggetto del messaggio', 'bizstudio' ); ?>">
			</div>

			<div class="biz-contact-form__field">
				<label for="biz-cf-messaggio"><?php esc_html_e( 'Messaggio', 'bizstudio' ); ?> <span class="required">*</span></label>
				<textarea id="biz-cf-messaggio" name="biz_cf_messaggio" rows="6" placeholder="<?php esc_attr_e( 'Scrivi il tuo messaggio...', 'bizstudio' ); ?>" required></textarea>
			</div>

			<!-- Honeypot -->
			<div class="biz-contact-form__hp" aria-hidden="true">
				<label for="biz-cf-website">Website</label>
				<input type="text" id="biz-cf-website" name="biz_cf_website" tabindex="-1" autocomplete="off">
			</div>

			<button type="submit" class="biz-btn biz-btn--primary biz-btn--full biz-contact-form__submit">
				<span class="biz-contact-form__submit-text"><?php esc_html_e( 'Invia messaggio', 'bizstudio' ); ?></span>
				<span class="biz-contact-form__submit-loading" style="display:none;">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
						<path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
					</svg>
				</span>
			</button>

			<div class="biz-contact-form__msg" style="display:none;" role="alert"></div>
		</form>
	</div>
	<?php
	// Inline JS (small, loaded only when shortcode is used)
	bizstudio_contact_form_inline_js();

	return ob_get_clean();
}

/* ──────────────────────────────────────────────
   2. Inline JavaScript
   ────────────────────────────────────────────── */

function bizstudio_contact_form_inline_js() {
	static $printed = false;
	if ( $printed ) return;
	$printed = true;
	?>
	<script>
	(function(){
		document.querySelectorAll('.biz-contact-form__form').forEach(function(form){
			form.addEventListener('submit', function(e){
				e.preventDefault();
				var btn = form.querySelector('.biz-contact-form__submit');
				var msg = form.querySelector('.biz-contact-form__msg');
				var txtEl = btn.querySelector('.biz-contact-form__submit-text');
				var ldEl = btn.querySelector('.biz-contact-form__submit-loading');
				btn.disabled = true;
				txtEl.style.display = 'none';
				ldEl.style.display = 'inline-flex';
				msg.style.display = 'none';
				msg.className = 'biz-contact-form__msg';
				var fd = new FormData(form);
				fd.append('action', 'bizstudio_contact_submit');
				fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
					method: 'POST', body: fd
				}).then(function(r){ return r.json(); }).then(function(data){
					msg.textContent = data.data.message;
					msg.classList.add(data.success ? 'biz-contact-form__msg--success' : 'biz-contact-form__msg--error');
					msg.style.display = 'block';
					if(data.success) form.reset();
				}).catch(function(){
					msg.textContent = '<?php echo esc_js( __( 'Si e verificato un errore. Riprova.', 'bizstudio' ) ); ?>';
					msg.classList.add('biz-contact-form__msg--error');
					msg.style.display = 'block';
				}).finally(function(){
					btn.disabled = false;
					txtEl.style.display = '';
					ldEl.style.display = 'none';
				});
			});
		});
	})();
	</script>
	<?php
}

/* ──────────────────────────────────────────────
   3. AJAX Handler
   ────────────────────────────────────────────── */

add_action( 'wp_ajax_bizstudio_contact_submit',        'bizstudio_contact_submit_handler' );
add_action( 'wp_ajax_nopriv_bizstudio_contact_submit',  'bizstudio_contact_submit_handler' );

function bizstudio_contact_submit_handler() {

	// Verify nonce
	if ( ! isset( $_POST['bizstudio_contact_nonce_field'] ) ||
	     ! wp_verify_nonce( $_POST['bizstudio_contact_nonce_field'], 'bizstudio_contact_nonce' ) ) {
		wp_send_json_error( [ 'message' => __( 'Si e verificato un errore. Riprova.', 'bizstudio' ) ] );
	}

	// Honeypot check — if filled, silently return success
	if ( ! empty( $_POST['biz_cf_website'] ) ) {
		wp_send_json_success( [ 'message' => __( 'Messaggio inviato con successo! Ti risponderemo al piu presto.', 'bizstudio' ) ] );
	}

	// Rate limiting: max 3 per IP per hour
	$ip_hash       = md5( bizstudio_get_client_ip() . 'biz_contact_salt' );
	$transient_key = 'biz_contact_limit_' . $ip_hash;
	$submissions   = (int) get_transient( $transient_key );

	if ( $submissions >= 3 ) {
		wp_send_json_error( [ 'message' => __( 'Hai inviato troppi messaggi. Riprova tra un\'ora.', 'bizstudio' ) ] );
	}

	// Sanitize fields
	$nome      = sanitize_text_field( wp_unslash( $_POST['biz_cf_nome'] ?? '' ) );
	$email     = sanitize_email( wp_unslash( $_POST['biz_cf_email'] ?? '' ) );
	$oggetto   = sanitize_text_field( wp_unslash( $_POST['biz_cf_oggetto'] ?? '' ) );
	$messaggio = sanitize_textarea_field( wp_unslash( $_POST['biz_cf_messaggio'] ?? '' ) );

	// Validate required fields
	if ( empty( $nome ) || empty( $email ) || empty( $messaggio ) ) {
		wp_send_json_error( [ 'message' => __( 'Compila tutti i campi obbligatori.', 'bizstudio' ) ] );
	}

	// Validate email format
	if ( ! is_email( $email ) ) {
		wp_send_json_error( [ 'message' => __( 'Inserisci un indirizzo email valido.', 'bizstudio' ) ] );
	}

	// Determine recipient (from shortcode data attribute, fallback to admin)
	$to             = sanitize_email( $_POST['biz_cf_to'] ?? get_option( 'admin_email' ) );
	$subject_prefix = sanitize_text_field( $_POST['biz_cf_prefix'] ?? '[BizStudio]' );

	if ( ! is_email( $to ) ) {
		$to = get_option( 'admin_email' );
	}

	// Build subject
	$subject = $subject_prefix . ' ' . ( ! empty( $oggetto ) ? $oggetto : __( 'Nuovo messaggio dal sito', 'bizstudio' ) );

	// Build HTML email body
	$body  = '<html><body style="font-family:Arial,sans-serif;color:#333;">';
	$body .= '<h2 style="color:#8367C7;">' . esc_html( $subject ) . '</h2>';
	$body .= '<table style="width:100%;border-collapse:collapse;">';
	$body .= '<tr><td style="padding:8px 12px;font-weight:bold;border-bottom:1px solid #eee;">' . esc_html__( 'Nome', 'bizstudio' ) . '</td>';
	$body .= '<td style="padding:8px 12px;border-bottom:1px solid #eee;">' . esc_html( $nome ) . '</td></tr>';
	$body .= '<tr><td style="padding:8px 12px;font-weight:bold;border-bottom:1px solid #eee;">' . esc_html__( 'Email', 'bizstudio' ) . '</td>';
	$body .= '<td style="padding:8px 12px;border-bottom:1px solid #eee;">' . esc_html( $email ) . '</td></tr>';
	if ( ! empty( $oggetto ) ) {
		$body .= '<tr><td style="padding:8px 12px;font-weight:bold;border-bottom:1px solid #eee;">' . esc_html__( 'Oggetto', 'bizstudio' ) . '</td>';
		$body .= '<td style="padding:8px 12px;border-bottom:1px solid #eee;">' . esc_html( $oggetto ) . '</td></tr>';
	}
	$body .= '<tr><td style="padding:8px 12px;font-weight:bold;border-bottom:1px solid #eee;">' . esc_html__( 'Messaggio', 'bizstudio' ) . '</td>';
	$body .= '<td style="padding:8px 12px;border-bottom:1px solid #eee;">' . nl2br( esc_html( $messaggio ) ) . '</td></tr>';
	$body .= '</table>';
	$body .= '<p style="margin-top:20px;font-size:12px;color:#999;">' . esc_html__( 'Inviato dal modulo di contatto del sito.', 'bizstudio' ) . '</p>';
	$body .= '</body></html>';

	// Headers
	$headers = [
		'Content-Type: text/html; charset=UTF-8',
		'Reply-To: ' . $nome . ' <' . $email . '>',
	];

	// Send email
	$sent = wp_mail( $to, $subject, $body, $headers );

	if ( $sent ) {
		// Increment rate limit counter
		if ( $submissions === 0 ) {
			set_transient( $transient_key, 1, HOUR_IN_SECONDS );
		} else {
			set_transient( $transient_key, $submissions + 1, HOUR_IN_SECONDS );
		}

		wp_send_json_success( [ 'message' => __( 'Messaggio inviato con successo! Ti risponderemo al piu presto.', 'bizstudio' ) ] );
	}

	wp_send_json_error( [ 'message' => __( 'Si e verificato un errore. Riprova.', 'bizstudio' ) ] );
}

/* ──────────────────────────────────────────────
   4. Helpers
   ────────────────────────────────────────────── */

/**
 * Get client IP address (respects proxies).
 *
 * @return string
 */
function bizstudio_get_client_ip() {
	$keys = [
		'HTTP_CF_CONNECTING_IP', // Cloudflare
		'HTTP_X_FORWARDED_FOR',
		'HTTP_X_REAL_IP',
		'REMOTE_ADDR',
	];

	foreach ( $keys as $key ) {
		if ( ! empty( $_SERVER[ $key ] ) ) {
			$ip = explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) );
			return trim( $ip[0] );
		}
	}

	return '127.0.0.1';
}
