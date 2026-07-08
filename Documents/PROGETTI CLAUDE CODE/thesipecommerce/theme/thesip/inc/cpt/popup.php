<?php
/**
 * CPT pn_popup: popup modali (welcome, newsletter, promo).
 *
 * Schema meta:
 *   _pn_popup_image_id      (int, attachment ID — opzionale)
 *   _pn_popup_subtitle      (string)
 *   _pn_popup_body          (string, plain text)
 *   _pn_popup_coupon        (string — opt., box ticket con copia)
 *   _pn_popup_has_form      ('1' | '') — form newsletter inline
 *   _pn_popup_cta_text      (string)
 *   _pn_popup_cta_url       (string — link alternativo se no form)
 *   _pn_popup_delay         (int seconds, default 3)
 *   _pn_popup_frequency     ('session' | 'forever' | 'always')
 *   _pn_popup_start_at      (string ISO datetime — opt.)
 *   _pn_popup_end_at        (string ISO datetime — opt.)
 *   _pn_popup_active        ('1' | '')
 *   _pn_popup_pages         ('home' | 'all') — dove mostrarlo
 *
 * Renderer: hook wp_footer, Alpine.js per stato, localStorage per memoria.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const PN_POPUP_FREQUENCIES = array( 'session', 'forever', 'always' );
const PN_POPUP_PAGES       = array( 'home', 'all' );

/**
 * Registra il CPT.
 */
add_action(
	'init',
	function () {
		register_post_type(
			'pn_popup',
			array(
				'labels'             => array(
					'name'               => __( 'Popup', 'pharmanow' ),
					'singular_name'      => __( 'Popup', 'pharmanow' ),
					'menu_name'          => __( 'Popup', 'pharmanow' ),
					'add_new'            => __( 'Aggiungi nuovo', 'pharmanow' ),
					'add_new_item'       => __( 'Aggiungi popup', 'pharmanow' ),
					'edit_item'          => __( 'Modifica popup', 'pharmanow' ),
					'new_item'           => __( 'Nuovo popup', 'pharmanow' ),
					'view_item'          => __( 'Vedi popup', 'pharmanow' ),
					'search_items'       => __( 'Cerca popup', 'pharmanow' ),
					'not_found'          => __( 'Nessun popup', 'pharmanow' ),
					'not_found_in_trash' => __( 'Nessun popup nel cestino', 'pharmanow' ),
				),
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'show_in_rest'       => false,
				'menu_icon'          => 'dashicons-format-chat',
				'menu_position'      => 28,
				'supports'           => array( 'title' ),
				'has_archive'        => false,
				'rewrite'            => false,
				'capability_type'    => 'post',
			)
		);
	}
);

/**
 * Disabilita Gutenberg per pn_popup.
 */
add_filter(
	'use_block_editor_for_post_type',
	function ( $use, $post_type ) {
		return 'pn_popup' === $post_type ? false : $use;
	},
	10,
	2
);

/**
 * Carica il media uploader nelle schermate del CPT.
 */
add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || 'pn_popup' !== $screen->post_type ) {
			return;
		}
		wp_enqueue_media();
	}
);

/**
 * Metabox.
 */
add_action(
	'add_meta_boxes',
	function () {
		add_meta_box(
			'pn_popup_config',
			__( 'Configurazione popup', 'pharmanow' ),
			'pn_popup_render_metabox',
			'pn_popup',
			'normal',
			'high'
		);
	}
);

/**
 * Render metabox — UI Popup Builder (header dashboard + cards + ticket coupon).
 */
function pn_popup_render_metabox( $post ): void {
	wp_nonce_field( 'pn_popup_save', 'pn_popup_nonce' );

	$image_id  = (int) get_post_meta( $post->ID, '_pn_popup_image_id', true );
	$subtitle  = (string) get_post_meta( $post->ID, '_pn_popup_subtitle', true );
	$body      = (string) get_post_meta( $post->ID, '_pn_popup_body', true );
	$coupon    = (string) get_post_meta( $post->ID, '_pn_popup_coupon', true );
	$has_form  = (string) get_post_meta( $post->ID, '_pn_popup_has_form', true );
	$cta_text  = (string) get_post_meta( $post->ID, '_pn_popup_cta_text', true );
	$cta_url   = (string) get_post_meta( $post->ID, '_pn_popup_cta_url', true );
	$delay     = (int) get_post_meta( $post->ID, '_pn_popup_delay', true );
	$frequency = (string) get_post_meta( $post->ID, '_pn_popup_frequency', true );
	$start_at  = (string) get_post_meta( $post->ID, '_pn_popup_start_at', true );
	$end_at    = (string) get_post_meta( $post->ID, '_pn_popup_end_at', true );
	$active    = (string) get_post_meta( $post->ID, '_pn_popup_active', true );
	$pages     = (string) get_post_meta( $post->ID, '_pn_popup_pages', true );

	if ( ! $delay ) {
		$delay = 3;
	}
	if ( ! in_array( $frequency, PN_POPUP_FREQUENCIES, true ) ) {
		$frequency = 'session';
	}
	if ( ! in_array( $pages, PN_POPUP_PAGES, true ) ) {
		$pages = 'home';
	}

	$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'medium_large' ) : '';
	?>
	<style>
		.pn-pop { --pn-bg:#f8fafc; --pn-card:#fff; --pn-border:#e5e7eb; --pn-text:#0f172a; --pn-muted:#64748b; --pn-primary:#0a6e9e; --pn-primary-dark:#07496b; --pn-radius:12px; --pn-shadow:0 1px 2px rgba(0,0,0,.04), 0 0 0 1px rgba(0,0,0,.04); --pn-shadow-lg:0 4px 16px rgba(0,0,0,.06), 0 0 0 1px rgba(0,0,0,.04); padding:4px; font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif; color:var(--pn-text); }

		/* Header dashboard */
		.pn-pop-header { background:linear-gradient(135deg,#7c3aed,#4f46e5); border-radius:var(--pn-radius); padding:20px 24px; margin-bottom:18px; color:#fff; box-shadow:var(--pn-shadow-lg); display:flex; align-items:center; justify-content:space-between; gap:24px; flex-wrap:wrap; }
		.pn-pop-header__brand { display:flex; align-items:center; gap:14px; }
		.pn-pop-header__icon { width:48px; height:48px; background:rgba(255,255,255,.18); border-radius:12px; display:inline-flex; align-items:center; justify-content:center; backdrop-filter:blur(8px); }
		.pn-pop-header__icon .dashicons { color:#fff; width:24px; height:24px; font-size:24px; }
		.pn-pop-header__text h2 { margin:0 0 2px; font-size:18px; font-weight:700; color:#fff; }
		.pn-pop-header__text p { margin:0; font-size:12px; opacity:.85; }
		.pn-pop-header__stats { display:flex; gap:14px; }
		.pn-pop-stat { background:rgba(255,255,255,.14); backdrop-filter:blur(8px); padding:10px 16px; border-radius:10px; min-width:90px; text-align:center; }
		.pn-pop-stat__num { display:block; font-size:18px; font-weight:700; line-height:1.1; font-family:ui-monospace,SFMono-Regular,Menlo,monospace; }
		.pn-pop-stat__label { display:block; font-size:10px; text-transform:uppercase; letter-spacing:.05em; opacity:.85; margin-top:2px; }

		/* Body */
		.pn-pop-body { display:grid; grid-template-columns:1fr 340px; gap:18px; }
		@media (max-width:1100px) { .pn-pop-body { grid-template-columns:1fr; } }
		.pn-pop__col { display:flex; flex-direction:column; gap:14px; }
		.pn-pop-card { background:var(--pn-card); border-radius:var(--pn-radius); box-shadow:var(--pn-shadow); padding:20px; }
		.pn-pop-card__title { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:var(--pn-muted); margin:0 0 16px; display:flex; align-items:center; gap:8px; }
		.pn-pop-card__title .dashicons { color:#7c3aed; width:16px; height:16px; font-size:16px; }

		.pn-pop-field { display:flex; flex-direction:column; gap:6px; margin-bottom:14px; }
		.pn-pop-field:last-child { margin-bottom:0; }
		.pn-pop-field__label { font-weight:600; font-size:12px; color:var(--pn-text); }
		.pn-pop-field__help { font-size:11px; color:var(--pn-muted); margin:2px 0 0; }
		.pn-pop-input, .pn-pop-select, .pn-pop-textarea { width:100%; padding:9px 12px; border:1px solid var(--pn-border); border-radius:8px; font-size:13px; background:#fff; box-sizing:border-box; line-height:1.4; transition:border-color .15s, box-shadow .15s; font-family:inherit; }
		.pn-pop-textarea { min-height:80px; resize:vertical; }
		.pn-pop-input:focus, .pn-pop-select:focus, .pn-pop-textarea:focus { outline:none; border-color:#7c3aed; box-shadow:0 0 0 3px rgba(124,58,237,.15); }
		.pn-pop-input--num { max-width:100px; }

		/* Toggle */
		.pn-pop-toggle { display:inline-flex; align-items:center; gap:10px; cursor:pointer; user-select:none; }
		.pn-pop-toggle input { display:none; }
		.pn-pop-toggle__track { width:38px; height:22px; border-radius:11px; background:#cbd5e1; position:relative; transition:background .2s; flex-shrink:0; }
		.pn-pop-toggle__track::after { content:''; position:absolute; width:18px; height:18px; border-radius:50%; background:#fff; top:2px; left:2px; box-shadow:0 1px 3px rgba(0,0,0,.25); transition:transform .2s; }
		.pn-pop-toggle input:checked + .pn-pop-toggle__track { background:#7c3aed; }
		.pn-pop-toggle input:checked + .pn-pop-toggle__track::after { transform:translateX(16px); }
		.pn-pop-toggle__label { font-size:13px; font-weight:500; }

		/* Image picker */
		.pn-pop-pic { border:2px dashed var(--pn-border); border-radius:var(--pn-radius); background:var(--pn-bg); transition:all .15s; overflow:hidden; position:relative; cursor:pointer; }
		.pn-pop-pic:hover { border-color:#7c3aed; background:#faf5ff; }
		.pn-pop-pic--has { padding:0; border-style:solid; border-color:var(--pn-border); background:#0f172a; }
		.pn-pop-pic__empty { padding:36px 16px; text-align:center; color:var(--pn-muted); font-size:12px; }
		.pn-pop-pic__empty .dashicons { color:#7c3aed; display:block; margin:0 auto 8px; width:32px; height:32px; font-size:32px; }
		.pn-pop-pic__img { width:100%; height:auto; max-height:240px; object-fit:contain; display:block; }
		.pn-pop-pic__overlay { position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,.7), rgba(0,0,0,0)); display:flex; align-items:flex-end; justify-content:center; gap:8px; padding:12px; opacity:0; transition:opacity .15s; }
		.pn-pop-pic--has:hover .pn-pop-pic__overlay { opacity:1; }
		.pn-pop-pic__btn { background:rgba(255,255,255,.95); border:0; padding:6px 14px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; color:var(--pn-text); }
		.pn-pop-pic__btn--danger { color:#dc2626; }

		/* Coupon ticket */
		.pn-pop-ticket { position:relative; background:linear-gradient(135deg,#faf5ff 0%,#fef3c7 100%); border:2px dashed #7c3aed; border-radius:12px; padding:20px 16px; text-align:center; overflow:hidden; }
		.pn-pop-ticket::before, .pn-pop-ticket::after { content:''; position:absolute; width:18px; height:18px; background:var(--pn-card); border-radius:50%; top:50%; transform:translateY(-50%); }
		.pn-pop-ticket::before { left:-10px; }
		.pn-pop-ticket::after { right:-10px; }
		.pn-pop-ticket__label { font-size:10px; text-transform:uppercase; letter-spacing:.1em; color:var(--pn-muted); margin-bottom:8px; }
		.pn-pop-ticket__input { width:100%; border:0; background:transparent; text-align:center; font-family:ui-monospace,SFMono-Regular,Menlo,monospace; font-size:22px; font-weight:800; letter-spacing:.15em; color:#5b21b6; text-transform:uppercase; padding:0; }
		.pn-pop-ticket__input:focus { outline:none; }
		.pn-pop-ticket__input::placeholder { color:rgba(124,58,237,.35); font-weight:400; letter-spacing:.05em; }

		/* Frequency chips */
		.pn-pop-chips { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; }
		.pn-pop-chip { padding:14px 8px; border:2px solid var(--pn-border); border-radius:10px; text-align:center; cursor:pointer; font-size:11px; font-weight:600; color:var(--pn-muted); transition:all .15s; background:#fff; display:flex; flex-direction:column; align-items:center; gap:6px; }
		.pn-pop-chip:hover { border-color:#cbd5e1; }
		.pn-pop-chip .dashicons { width:18px; height:18px; font-size:18px; }
		.pn-pop-chip input { display:none; }
		.pn-pop-chip--checked { border-color:#7c3aed; background:#faf5ff; color:#7c3aed; }

		/* Status badge */
		.pn-pop-status { display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:999px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
		.pn-pop-status--on { background:#d1fae5; color:#065f46; }
		.pn-pop-status--off { background:#fee2e2; color:#991b1b; }
		.pn-pop-status::before { content:''; width:6px; height:6px; border-radius:50%; background:currentColor; }
	</style>

	<div class="pn-pop" data-pop-root>

		<!-- HEADER DASHBOARD -->
		<div class="pn-pop-header">
			<div class="pn-pop-header__brand">
				<span class="pn-pop-header__icon"><span class="dashicons dashicons-format-chat"></span></span>
				<div class="pn-pop-header__text">
					<h2><?php echo esc_html( get_the_title( $post ) ?: __( 'Nuovo popup', 'pharmanow' ) ); ?></h2>
					<p>
						<?php esc_html_e( 'Modal di benvenuto / promo. Mostrato sul frontend con Alpine.js.', 'pharmanow' ); ?>
					</p>
				</div>
			</div>
			<div class="pn-pop-header__stats">
				<div class="pn-pop-stat">
					<span class="pn-pop-stat__num" data-stat-coupon><?php echo $coupon ? esc_html( strtoupper( $coupon ) ) : '—'; ?></span>
					<span class="pn-pop-stat__label"><?php esc_html_e( 'Coupon', 'pharmanow' ); ?></span>
				</div>
				<div class="pn-pop-stat">
					<span class="pn-pop-stat__num" data-stat-delay><?php echo (int) $delay; ?>s</span>
					<span class="pn-pop-stat__label"><?php esc_html_e( 'Ritardo', 'pharmanow' ); ?></span>
				</div>
				<div class="pn-pop-stat">
					<span class="pn-pop-stat__num" data-stat-freq><?php echo esc_html( ucfirst( $frequency ) ); ?></span>
					<span class="pn-pop-stat__label"><?php esc_html_e( 'Frequenza', 'pharmanow' ); ?></span>
				</div>
			</div>
		</div>

		<!-- BODY -->
		<div class="pn-pop-body">

			<!-- COL MAIN -->
			<div class="pn-pop__col">

				<!-- Card: Contenuto -->
				<div class="pn-pop-card">
					<h3 class="pn-pop-card__title"><span class="dashicons dashicons-edit"></span> <?php esc_html_e( 'Contenuto popup', 'pharmanow' ); ?></h3>

					<div class="pn-pop-field">
						<label class="pn-pop-field__label"><?php esc_html_e( 'Immagine (opzionale)', 'pharmanow' ); ?></label>
						<div class="pn-pop-pic <?php echo $image_url ? 'pn-pop-pic--has' : ''; ?>" data-pn-pop-media>
							<?php if ( $image_url ) : ?>
								<img src="<?php echo esc_url( $image_url ); ?>" alt="" class="pn-pop-pic__img">
								<div class="pn-pop-pic__overlay">
									<button type="button" class="pn-pop-pic__btn" data-pick><?php esc_html_e( 'Cambia', 'pharmanow' ); ?></button>
									<button type="button" class="pn-pop-pic__btn pn-pop-pic__btn--danger" data-clear><?php esc_html_e( 'Rimuovi', 'pharmanow' ); ?></button>
								</div>
							<?php else : ?>
								<div class="pn-pop-pic__empty" data-pick>
									<span class="dashicons dashicons-format-image"></span>
									<strong><?php esc_html_e( 'Aggiungi immagine illustrativa', 'pharmanow' ); ?></strong><br>
									<?php esc_html_e( 'consigliato 600×400px', 'pharmanow' ); ?>
								</div>
							<?php endif; ?>
							<input type="hidden" name="pn_popup_image_id" value="<?php echo esc_attr( (string) $image_id ); ?>" data-input>
						</div>
					</div>

					<div class="pn-pop-field">
						<label for="pn_popup_subtitle" class="pn-pop-field__label"><?php esc_html_e( 'Sottotitolo / occhiello', 'pharmanow' ); ?></label>
						<input type="text" id="pn_popup_subtitle" name="pn_popup_subtitle" value="<?php echo esc_attr( $subtitle ); ?>" class="pn-pop-input" placeholder="<?php esc_attr_e( 'Es. Solo per nuovi iscritti', 'pharmanow' ); ?>">
					</div>

					<div class="pn-pop-field">
						<label for="pn_popup_body" class="pn-pop-field__label"><?php esc_html_e( 'Corpo del messaggio', 'pharmanow' ); ?></label>
						<textarea id="pn_popup_body" name="pn_popup_body" class="pn-pop-textarea" placeholder="<?php esc_attr_e( 'Iscriviti alla newsletter e ricevi subito il codice sconto del 10%...', 'pharmanow' ); ?>"><?php echo esc_textarea( $body ); ?></textarea>
						<p class="pn-pop-field__help"><?php esc_html_e( 'Il titolo principale del popup è il titolo del post in alto. Qui scrivi il messaggio sotto.', 'pharmanow' ); ?></p>
					</div>
				</div>

				<!-- Card: CTA -->
				<div class="pn-pop-card">
					<h3 class="pn-pop-card__title"><span class="dashicons dashicons-admin-links"></span> <?php esc_html_e( 'Call-to-action', 'pharmanow' ); ?></h3>

					<label class="pn-pop-toggle" style="margin-bottom:12px">
						<input type="checkbox" name="pn_popup_has_form" value="1" <?php checked( '1', $has_form ); ?>>
						<span class="pn-pop-toggle__track"></span>
						<span class="pn-pop-toggle__label"><?php esc_html_e( 'Mostra form newsletter (email + iscriviti)', 'pharmanow' ); ?></span>
					</label>

					<div class="pn-pop-field">
						<label for="pn_popup_cta_text" class="pn-pop-field__label"><?php esc_html_e( 'Testo bottone CTA', 'pharmanow' ); ?></label>
						<input type="text" id="pn_popup_cta_text" name="pn_popup_cta_text" value="<?php echo esc_attr( $cta_text ); ?>" class="pn-pop-input" placeholder="<?php esc_attr_e( 'Es. Iscriviti / Scopri di più', 'pharmanow' ); ?>">
					</div>

					<div class="pn-pop-field">
						<label for="pn_popup_cta_url" class="pn-pop-field__label"><?php esc_html_e( 'URL bottone (se NO form)', 'pharmanow' ); ?></label>
						<input type="url" id="pn_popup_cta_url" name="pn_popup_cta_url" value="<?php echo esc_attr( $cta_url ); ?>" class="pn-pop-input" placeholder="https://...">
						<p class="pn-pop-field__help"><?php esc_html_e( 'Ignorato se il form è attivo (il submit è verso /wp-json/pharmanow/v1/newsletter).', 'pharmanow' ); ?></p>
					</div>
				</div>
			</div>

			<!-- SIDEBAR -->
			<div class="pn-pop__col">

				<!-- Card: Stato -->
				<div class="pn-pop-card">
					<h3 class="pn-pop-card__title"><span class="dashicons dashicons-visibility"></span> <?php esc_html_e( 'Stato', 'pharmanow' ); ?></h3>

					<div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
						<label class="pn-pop-toggle">
							<input type="checkbox" name="pn_popup_active" value="1" <?php checked( '1', $active ); ?> data-active-toggle>
							<span class="pn-pop-toggle__track"></span>
							<span class="pn-pop-toggle__label"><?php esc_html_e( 'Attivo', 'pharmanow' ); ?></span>
						</label>
						<span class="pn-pop-status pn-pop-status--<?php echo '1' === $active ? 'on' : 'off'; ?>" data-status-badge>
							<?php echo '1' === $active ? esc_html__( 'Visibile', 'pharmanow' ) : esc_html__( 'Disattivato', 'pharmanow' ); ?>
						</span>
					</div>
					<p class="pn-pop-field__help" style="margin-top:10px"><?php esc_html_e( 'Solo i popup attivi vengono renderizzati.', 'pharmanow' ); ?></p>
				</div>

				<!-- Card: Coupon ticket -->
				<div class="pn-pop-card">
					<h3 class="pn-pop-card__title"><span class="dashicons dashicons-tickets-alt"></span> <?php esc_html_e( 'Codice sconto', 'pharmanow' ); ?></h3>

					<div class="pn-pop-ticket">
						<div class="pn-pop-ticket__label"><?php esc_html_e( 'Coupon', 'pharmanow' ); ?></div>
						<input type="text" id="pn_popup_coupon" name="pn_popup_coupon" value="<?php echo esc_attr( $coupon ); ?>" class="pn-pop-ticket__input" placeholder="ES. BENVENUTO10" data-coupon-input>
					</div>
					<p class="pn-pop-field__help" style="margin-top:8px"><?php esc_html_e( 'Mostrato in box copia-incolla nel popup. Vuoto = niente coupon.', 'pharmanow' ); ?></p>
				</div>

				<!-- Card: Trigger -->
				<div class="pn-pop-card">
					<h3 class="pn-pop-card__title"><span class="dashicons dashicons-clock"></span> <?php esc_html_e( 'Trigger & frequenza', 'pharmanow' ); ?></h3>

					<div class="pn-pop-field">
						<label for="pn_popup_delay" class="pn-pop-field__label"><?php esc_html_e( 'Ritardo apertura (secondi)', 'pharmanow' ); ?></label>
						<input type="number" id="pn_popup_delay" name="pn_popup_delay" value="<?php echo (int) $delay; ?>" min="0" max="60" class="pn-pop-input pn-pop-input--num" data-delay-input>
					</div>

					<div class="pn-pop-field">
						<label class="pn-pop-field__label"><?php esc_html_e( 'Frequenza', 'pharmanow' ); ?></label>
						<div class="pn-pop-chips" data-freq-chips>
							<label class="pn-pop-chip <?php echo 'session' === $frequency ? 'pn-pop-chip--checked' : ''; ?>">
								<input type="radio" name="pn_popup_frequency" value="session" <?php checked( $frequency, 'session' ); ?>>
								<span class="dashicons dashicons-visibility"></span>
								<?php esc_html_e( 'Sessione', 'pharmanow' ); ?>
							</label>
							<label class="pn-pop-chip <?php echo 'forever' === $frequency ? 'pn-pop-chip--checked' : ''; ?>">
								<input type="radio" name="pn_popup_frequency" value="forever" <?php checked( $frequency, 'forever' ); ?>>
								<span class="dashicons dashicons-yes-alt"></span>
								<?php esc_html_e( '1 volta', 'pharmanow' ); ?>
							</label>
							<label class="pn-pop-chip <?php echo 'always' === $frequency ? 'pn-pop-chip--checked' : ''; ?>">
								<input type="radio" name="pn_popup_frequency" value="always" <?php checked( $frequency, 'always' ); ?>>
								<span class="dashicons dashicons-image-rotate"></span>
								<?php esc_html_e( 'Sempre', 'pharmanow' ); ?>
							</label>
						</div>
						<p class="pn-pop-field__help">
							<strong><?php esc_html_e( 'Sessione', 'pharmanow' ); ?>:</strong> <?php esc_html_e( '1 volta per visita.', 'pharmanow' ); ?><br>
							<strong>1 volta:</strong> <?php esc_html_e( 'mai più dopo la prima chiusura.', 'pharmanow' ); ?><br>
							<strong>Sempre:</strong> <?php esc_html_e( 'ogni volta che apre la home.', 'pharmanow' ); ?>
						</p>
					</div>

					<div class="pn-pop-field">
						<label for="pn_popup_pages" class="pn-pop-field__label"><?php esc_html_e( 'Dove mostrarlo', 'pharmanow' ); ?></label>
						<select id="pn_popup_pages" name="pn_popup_pages" class="pn-pop-select">
							<option value="home" <?php selected( $pages, 'home' ); ?>><?php esc_html_e( 'Solo homepage', 'pharmanow' ); ?></option>
							<option value="all" <?php selected( $pages, 'all' ); ?>><?php esc_html_e( 'Tutte le pagine', 'pharmanow' ); ?></option>
						</select>
					</div>
				</div>

				<!-- Card: Schedule -->
				<div class="pn-pop-card">
					<h3 class="pn-pop-card__title"><span class="dashicons dashicons-calendar-alt"></span> <?php esc_html_e( 'Programmazione', 'pharmanow' ); ?></h3>
					<div class="pn-pop-field">
						<label for="pn_popup_start_at" class="pn-pop-field__label"><?php esc_html_e( 'Inizio', 'pharmanow' ); ?></label>
						<input type="datetime-local" id="pn_popup_start_at" name="pn_popup_start_at" value="<?php echo esc_attr( $start_at ); ?>" class="pn-pop-input">
						<p class="pn-pop-field__help"><?php esc_html_e( 'Vuoto = subito.', 'pharmanow' ); ?></p>
					</div>
					<div class="pn-pop-field">
						<label for="pn_popup_end_at" class="pn-pop-field__label"><?php esc_html_e( 'Fine', 'pharmanow' ); ?></label>
						<input type="datetime-local" id="pn_popup_end_at" name="pn_popup_end_at" value="<?php echo esc_attr( $end_at ); ?>" class="pn-pop-input">
						<p class="pn-pop-field__help"><?php esc_html_e( 'Vuoto = senza scadenza.', 'pharmanow' ); ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script>
	(function(){
		var root = document.querySelector('[data-pop-root]');
		if (!root) return;

		// Image picker
		var box = root.querySelector('[data-pn-pop-media]');
		function attachMedia(){
			var input = box.querySelector('[data-input]');
			var picks = box.querySelectorAll('[data-pick]');
			var clear = box.querySelector('[data-clear]');
			var frame;
			picks.forEach(function(p){
				p.addEventListener('click', function(e){
					e.preventDefault();
					if (frame) { frame.open(); return; }
					frame = wp.media({ title: '<?php echo esc_js( __( 'Scegli immagine', 'pharmanow' ) ); ?>', button: { text: '<?php echo esc_js( __( 'Usa questa', 'pharmanow' ) ); ?>' }, library: { type: 'image' }, multiple: false });
					frame.on('select', function(){
						var att = frame.state().get('selection').first().toJSON();
						var url = (att.sizes && (att.sizes.medium_large || att.sizes.large || att.sizes.medium)) ? (att.sizes.medium_large || att.sizes.large || att.sizes.medium).url : att.url;
						box.classList.add('pn-pop-pic--has');
						box.innerHTML = '<img src="'+url+'" alt="" class="pn-pop-pic__img">'
							+ '<div class="pn-pop-pic__overlay">'
							+ '<button type="button" class="pn-pop-pic__btn" data-pick>Cambia</button>'
							+ '<button type="button" class="pn-pop-pic__btn pn-pop-pic__btn--danger" data-clear>Rimuovi</button>'
							+ '</div>'
							+ '<input type="hidden" name="pn_popup_image_id" value="'+att.id+'" data-input>';
						attachMedia();
					});
					frame.open();
				});
			});
			if (clear) {
				clear.addEventListener('click', function(e){
					e.preventDefault();
					box.classList.remove('pn-pop-pic--has');
					box.innerHTML = '<div class="pn-pop-pic__empty" data-pick><span class="dashicons dashicons-format-image"></span><strong>Aggiungi immagine</strong></div>'
						+ '<input type="hidden" name="pn_popup_image_id" value="" data-input>';
					attachMedia();
				});
			}
		}
		if (box) attachMedia();

		// Frequency chips
		root.querySelectorAll('[data-freq-chips] input[name="pn_popup_frequency"]').forEach(function(r){
			r.addEventListener('change', function(){
				root.querySelectorAll('[data-freq-chips] .pn-pop-chip').forEach(function(c){ c.classList.remove('pn-pop-chip--checked'); });
				r.closest('.pn-pop-chip').classList.add('pn-pop-chip--checked');
				var sf = root.querySelector('[data-stat-freq]');
				if (sf) sf.textContent = r.value.charAt(0).toUpperCase() + r.value.slice(1);
			});
		});

		// Status badge live
		var actT = root.querySelector('[data-active-toggle]');
		var sb   = root.querySelector('[data-status-badge]');
		if (actT && sb) {
			actT.addEventListener('change', function(){
				if (actT.checked) { sb.classList.remove('pn-pop-status--off'); sb.classList.add('pn-pop-status--on'); sb.textContent = '<?php echo esc_js( __( 'Visibile', 'pharmanow' ) ); ?>'; }
				else { sb.classList.remove('pn-pop-status--on'); sb.classList.add('pn-pop-status--off'); sb.textContent = '<?php echo esc_js( __( 'Disattivato', 'pharmanow' ) ); ?>'; }
			});
		}

		// Coupon stat live
		var couponI = root.querySelector('[data-coupon-input]');
		var statC   = root.querySelector('[data-stat-coupon]');
		if (couponI && statC) couponI.addEventListener('input', function(){ statC.textContent = (couponI.value||'').trim().toUpperCase() || '—'; });

		// Delay stat live
		var delayI = root.querySelector('[data-delay-input]');
		var statD  = root.querySelector('[data-stat-delay]');
		if (delayI && statD) delayI.addEventListener('input', function(){ statD.textContent = ((parseInt(delayI.value,10)||0)) + 's'; });
	})();
	</script>
	<?php
}

/**
 * Save handler.
 */
add_action(
	'save_post_pn_popup',
	function ( $post_id ) {
		if ( ! isset( $_POST['pn_popup_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['pn_popup_nonce'] ), 'pn_popup_save' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$pairs = array(
			'_pn_popup_image_id' => array( 'pn_popup_image_id', 'absint' ),
			'_pn_popup_subtitle' => array( 'pn_popup_subtitle', 'sanitize_text_field' ),
			'_pn_popup_body'     => array( 'pn_popup_body', 'sanitize_textarea_field' ),
			'_pn_popup_cta_text' => array( 'pn_popup_cta_text', 'sanitize_text_field' ),
			'_pn_popup_cta_url'  => array( 'pn_popup_cta_url', 'esc_url_raw' ),
			'_pn_popup_delay'    => array( 'pn_popup_delay', 'absint' ),
			'_pn_popup_start_at' => array( 'pn_popup_start_at', 'pn_popup_sanitize_dt' ),
			'_pn_popup_end_at'   => array( 'pn_popup_end_at', 'pn_popup_sanitize_dt' ),
		);
		foreach ( $pairs as $meta => $cfg ) {
			[ $field, $san ] = $cfg;
			$raw = isset( $_POST[ $field ] ) ? wp_unslash( $_POST[ $field ] ) : '';
			$val = call_user_func( $san, $raw );
			if ( '' === $val || 0 === $val ) {
				delete_post_meta( $post_id, $meta );
			} else {
				update_post_meta( $post_id, $meta, $val );
			}
		}

		// Coupon UPPERCASE.
		$coupon_raw = isset( $_POST['pn_popup_coupon'] ) ? sanitize_text_field( wp_unslash( $_POST['pn_popup_coupon'] ) ) : '';
		if ( $coupon_raw ) {
			update_post_meta( $post_id, '_pn_popup_coupon', strtoupper( $coupon_raw ) );
		} else {
			delete_post_meta( $post_id, '_pn_popup_coupon' );
		}

		// Frequency.
		$freq = isset( $_POST['pn_popup_frequency'] ) ? sanitize_key( wp_unslash( $_POST['pn_popup_frequency'] ) ) : 'session';
		if ( ! in_array( $freq, PN_POPUP_FREQUENCIES, true ) ) {
			$freq = 'session';
		}
		update_post_meta( $post_id, '_pn_popup_frequency', $freq );

		// Pages.
		$pages = isset( $_POST['pn_popup_pages'] ) ? sanitize_key( wp_unslash( $_POST['pn_popup_pages'] ) ) : 'home';
		if ( ! in_array( $pages, PN_POPUP_PAGES, true ) ) {
			$pages = 'home';
		}
		update_post_meta( $post_id, '_pn_popup_pages', $pages );

		// Checkboxes.
		foreach ( array( '_pn_popup_active' => 'pn_popup_active', '_pn_popup_has_form' => 'pn_popup_has_form' ) as $meta => $field ) {
			if ( ! empty( $_POST[ $field ] ) ) {
				update_post_meta( $post_id, $meta, '1' );
			} else {
				delete_post_meta( $post_id, $meta );
			}
		}
	}
);

function pn_popup_sanitize_dt( $raw ): string {
	$raw = trim( (string) $raw );
	if ( '' === $raw ) {
		return '';
	}
	if ( preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(:\d{2})?$/', $raw ) ) {
		return substr( $raw, 0, 16 );
	}
	return '';
}

/**
 * Recupera il primo popup attivo per il contesto corrente (home/all).
 */
function pn_popup_get_active(): ?WP_Post {
	$is_home = is_front_page();
	$ctx     = $is_home ? 'home' : 'all';

	$q = new WP_Query(
		array(
			'post_type'      => 'pn_popup',
			'post_status'    => 'publish',
			'posts_per_page' => 5,
			'no_found_rows'  => true,
			'meta_query'     => array(
				'relation' => 'AND',
				array( 'key' => '_pn_popup_active', 'value' => '1' ),
			),
		)
	);

	$now = current_time( 'timestamp' );
	foreach ( $q->posts as $p ) {
		// Pages target.
		$pages = (string) get_post_meta( $p->ID, '_pn_popup_pages', true );
		if ( 'home' === $pages && ! $is_home ) {
			continue;
		}
		// Schedule.
		$start = (string) get_post_meta( $p->ID, '_pn_popup_start_at', true );
		$end   = (string) get_post_meta( $p->ID, '_pn_popup_end_at', true );
		if ( $start && strtotime( $start ) > $now ) {
			continue;
		}
		if ( $end && strtotime( $end ) < $now ) {
			continue;
		}
		return $p;
	}
	return null;
}

/**
 * Render frontend del popup attivo.
 */
add_action( 'wp_footer', 'pn_popup_render_frontend', 100 );

function pn_popup_render_frontend(): void {
	// Mai sopra il funnel di pagamento: l'overlay (z-index 99999) coprirebbe
	// "Completa Ordine" e l'UI dei gateway.
	if ( function_exists( 'is_checkout' ) && ( is_checkout() || is_cart() ) ) {
		return;
	}

	$popup = pn_popup_get_active();
	if ( ! $popup ) {
		return;
	}

	$id        = (int) $popup->ID;
	$title     = get_the_title( $popup );
	$subtitle  = (string) get_post_meta( $id, '_pn_popup_subtitle', true );
	$body      = (string) get_post_meta( $id, '_pn_popup_body', true );
	$has_form  = '1' === get_post_meta( $id, '_pn_popup_has_form', true );
	$cta_text  = (string) get_post_meta( $id, '_pn_popup_cta_text', true ) ?: __( 'Iscriviti', 'pharmanow' );
	$cta_url   = (string) get_post_meta( $id, '_pn_popup_cta_url', true );
	$delay_ms  = max( 0, (int) get_post_meta( $id, '_pn_popup_delay', true ) ) * 1000;
	$frequency = (string) get_post_meta( $id, '_pn_popup_frequency', true ) ?: 'session';
	$image_id  = (int) get_post_meta( $id, '_pn_popup_image_id', true );
	$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'large' ) : '';
	$logo_url  = function_exists( 'pn_asset' ) ? pn_asset( 'images/logo-white.svg' ) : '';

	$storage_key = 'pn_popup_' . $id . '_dismissed';
	$session_key = 'pn_popup_' . $id . '_shown';
	?>
	<template
		x-data="pnPopup(<?php echo esc_attr( wp_json_encode( array(
			'id'          => $id,
			'delayMs'     => $delay_ms,
			'frequency'   => $frequency,
			'storageKey'  => $storage_key,
			'sessionKey'  => $session_key,
			'hasForm'     => $has_form,
			'restUrl'     => esc_url_raw( rest_url( 'pharmanow/v1/newsletter' ) ),
			'restNonce'   => wp_create_nonce( 'wp_rest' ),
		) ) ); ?>)"
		x-teleport="body"
	>
	<div
		x-show="open"
		x-cloak
		x-transition:enter="transition ease-out duration-300"
		x-transition:enter-start="opacity-0"
		x-transition:enter-end="opacity-100"
		x-transition:leave="transition ease-in duration-200"
		x-transition:leave-start="opacity-100"
		x-transition:leave-end="opacity-0"
		@keydown.escape.window="close()"
		class="fixed inset-0 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
		style="display:none; z-index:99999; isolation:isolate;"
	>
		<div
			@click.outside="close()"
			x-transition:enter="transition ease-out duration-300 delay-75"
			x-transition:enter-start="opacity-0 scale-95"
			x-transition:enter-end="opacity-100 scale-100"
			class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden"
		>
			<button
				type="button"
				@click="close()"
				aria-label="<?php esc_attr_e( 'Chiudi', 'pharmanow' ); ?>"
				class="absolute top-3 right-3 z-10 inline-flex items-center justify-center w-8 h-8 rounded-full bg-white/90 backdrop-blur-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition shadow-sm"
			>
				<?php pn_icon( 'x', array( 'class' => 'w-5 h-5' ) ); ?>
			</button>

			<?php /* Brand header: gradient teal + logo bianco. Sostituisce l'immagine se non specificata. */ ?>
			<?php if ( $image_url ) : ?>
				<div class="relative aspect-[3/2] w-full overflow-hidden bg-gray-100">
					<img src="<?php echo esc_url( $image_url ); ?>" alt="" class="w-full h-full object-cover">
					<div class="absolute inset-0 pharma-hero opacity-80"></div>
					<?php if ( $logo_url ) : ?>
						<div class="absolute inset-0 flex items-center justify-center">
							<img src="<?php echo esc_url( $logo_url ); ?>" alt="Pharmanow" class="h-10 md:h-12 w-auto drop-shadow-lg">
						</div>
					<?php endif; ?>
				</div>
			<?php else : ?>
				<div class="relative pharma-hero py-8 px-6 text-center overflow-hidden">
					<span aria-hidden="true" class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-white/10 blur-2xl"></span>
					<span aria-hidden="true" class="absolute -left-12 -bottom-12 h-40 w-40 rounded-full bg-white/5 blur-3xl"></span>
					<?php if ( $logo_url ) : ?>
						<img src="<?php echo esc_url( $logo_url ); ?>" alt="Pharmanow" class="relative h-10 md:h-11 w-auto mx-auto">
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="p-6 md:p-8 text-center">
				<?php if ( $subtitle ) : ?>
					<p class="text-xs font-semibold uppercase tracking-wider text-pharma-teal mb-2">
						<?php echo esc_html( $subtitle ); ?>
					</p>
				<?php endif; ?>

				<h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3 leading-tight">
					<?php echo esc_html( $title ); ?>
				</h2>

				<?php if ( $body ) : ?>
					<p class="text-sm md:text-base text-gray-600 mb-5 whitespace-pre-line">
						<?php echo esc_html( $body ); ?>
					</p>
				<?php endif; ?>

				<?php if ( $has_form ) : ?>
					<form @submit.prevent="submit()" class="mt-4 space-y-3">
						<input
							type="email"
							x-model="email"
							required
							:disabled="loading"
							placeholder="<?php esc_attr_e( 'Il tuo indirizzo email', 'pharmanow' ); ?>"
							class="w-full h-11 px-4 rounded-lg border border-gray-200 text-sm focus:outline-none focus:border-pharma-teal focus:ring-2 focus:ring-pharma-teal/20 transition disabled:opacity-60"
						>
						<button
							type="submit"
							:disabled="loading"
							class="w-full h-11 rounded-lg pharma-primary-bg text-white font-semibold text-sm hover:opacity-90 transition disabled:opacity-60"
						>
							<span x-show="!loading"><?php echo esc_html( $cta_text ); ?></span>
							<span x-show="loading" x-cloak><?php esc_html_e( 'Invio...', 'pharmanow' ); ?></span>
						</button>

						<p x-show="success" x-cloak class="text-sm text-emerald-600 font-medium">
							<?php esc_html_e( '✓ Iscrizione completata! Controlla la tua email.', 'pharmanow' ); ?>
						</p>
						<p x-show="error" x-cloak class="text-sm text-rose-600 font-medium" x-text="error"></p>
					</form>
				<?php elseif ( $cta_url && $cta_text ) : ?>
					<a
						href="<?php echo esc_url( $cta_url ); ?>"
						class="inline-flex items-center justify-center h-11 px-6 rounded-lg pharma-primary-bg text-white font-semibold text-sm hover:opacity-90 transition mt-4"
					>
						<?php echo esc_html( $cta_text ); ?>
					</a>
				<?php endif; ?>

				<label class="mt-5 flex items-center justify-center gap-2 text-xs text-gray-500 cursor-pointer">
					<input type="checkbox" x-model="dontShowAgain" class="rounded border-gray-300">
					<?php esc_html_e( 'Non mostrare più', 'pharmanow' ); ?>
				</label>
			</div>
		</div>
	</div>
	</template>

	<script>
	window.pnPopup = function (cfg) {
		return {
			open: false,
			email: '',
			dontShowAgain: false,
			loading: false,
			success: false,
			error: '',
			copied: false,
			init() {
				if (cfg.frequency === 'forever' && localStorage.getItem(cfg.storageKey)) return;
				if (cfg.frequency === 'session' && sessionStorage.getItem(cfg.sessionKey)) return;

				const self = this;
				const POPUP_DELAY_AFTER_CONSENT_MS = 5000;

				const showPopup = function () {
					self.open = true;
					if (cfg.frequency === 'session') sessionStorage.setItem(cfg.sessionKey, '1');
				};

				// Cerca il cookie pn_consent. Se presente → popup parte dopo cfg.delayMs.
				// Altrimenti aspetta evento pn:consent-given dal banner cookie e parte 5s dopo.
				var hasConsent = (function () {
					var m = document.cookie.match(/(?:^|; )pn_consent=([^;]*)/);
					return !!(m && m[1]);
				})();

				if (hasConsent) {
					setTimeout(showPopup, cfg.delayMs);
				} else {
					window.addEventListener('pn:consent-given', function () {
						setTimeout(showPopup, POPUP_DELAY_AFTER_CONSENT_MS);
					}, { once: true });
				}
			},
			close() {
				this.open = false;
				if (this.dontShowAgain || cfg.frequency === 'forever') {
					localStorage.setItem(cfg.storageKey, '1');
				}
			},
			copyCoupon(code) {
				navigator.clipboard.writeText(code);
				this.copied = true;
				setTimeout(() => { this.copied = false; }, 2000);
			},
			async submit() {
				if (this.loading) return;
				this.error = ''; this.success = false;
				const email = (this.email || '').trim();
				if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
					this.error = <?php echo wp_json_encode( __( 'Inserisci un indirizzo email valido.', 'pharmanow' ) ); ?>;
					return;
				}
				this.loading = true;
				try {
					const res = await fetch(cfg.restUrl, {
						method: 'POST',
						headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.restNonce },
						body: JSON.stringify({ email: email, source: 'popup_' + cfg.id }),
					});
					const data = await res.json().catch(() => ({}));
					if (!res.ok) {
						this.error = (data && data.message) || <?php echo wp_json_encode( __( 'Iscrizione non riuscita.', 'pharmanow' ) ); ?>;
						return;
					}
					this.success = true;
					localStorage.setItem(cfg.storageKey, '1');
					setTimeout(() => this.close(), 2200);
				} catch (e) {
					this.error = <?php echo wp_json_encode( __( 'Errore di rete. Riprova.', 'pharmanow' ) ); ?>;
				} finally {
					this.loading = false;
				}
			},
		};
	};
	</script>
	<?php
}

/**
 * Colonne admin custom: Stato, Frequenza, Coupon.
 */
add_filter(
	'manage_pn_popup_posts_columns',
	function ( $cols ) {
		$new = array();
		foreach ( $cols as $k => $v ) {
			$new[ $k ] = $v;
			if ( 'title' === $k ) {
				$new['pn_active'] = __( 'Attivo', 'pharmanow' );
				$new['pn_freq']   = __( 'Frequenza', 'pharmanow' );
				$new['pn_coupon'] = __( 'Coupon', 'pharmanow' );
			}
		}
		return $new;
	}
);

add_action(
	'manage_pn_popup_posts_custom_column',
	function ( $col, $post_id ) {
		if ( 'pn_active' === $col ) {
			echo '1' === get_post_meta( $post_id, '_pn_popup_active', true ) ? '✅' : '⏸️';
		} elseif ( 'pn_freq' === $col ) {
			echo esc_html( ucfirst( (string) get_post_meta( $post_id, '_pn_popup_frequency', true ) ) );
		} elseif ( 'pn_coupon' === $col ) {
			$c = (string) get_post_meta( $post_id, '_pn_popup_coupon', true );
			echo $c ? '<code>' . esc_html( $c ) . '</code>' : '—';
		}
	},
	10,
	2
);
