<?php
/**
 * CPT pn_banner: banner pubblicitari posizionabili dal customer care.
 *
 * Schema meta:
 *   _pn_banner_image_desktop_id  (int, attachment ID)
 *   _pn_banner_image_mobile_id   (int, attachment ID; 0 = usa desktop)
 *   _pn_banner_alt               (string)
 *   _pn_banner_link_type         ('url' | 'promo' | 'none')
 *   _pn_banner_link_url          (string, URL completo se link_type=url)
 *   _pn_banner_link_promo_id     (int, post ID di pn_promo se link_type=promo)
 *   _pn_banner_link_new_tab      ('1' | '')
 *   _pn_banner_placement         (string, slug zona)
 *   _pn_banner_order             (int, ordinamento crescente; default 10)
 *   _pn_banner_start_at          (string, ISO datetime YYYY-MM-DDTHH:MM; vuoto = sempre)
 *   _pn_banner_end_at            (string, ISO datetime; vuoto = sempre)
 *   _pn_banner_active            ('1' | '') interruttore rapido
 *   _pn_banner_full_width        ('1' | '') edge-to-edge invece che dentro container
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const PN_BANNER_CACHE_GROUP = 'pn_banners_zone_';
const PN_BANNER_CACHE_TTL   = 5 * MINUTE_IN_SECONDS;

/**
 * Lista placements disponibili. Filtrabile.
 *
 * @return array<string,string> slug => label
 */
function pn_banner_placements(): array {
	return apply_filters(
		'pn_banner_placements',
		array(
			'home_after_hero'         => __( 'Home — 1. Sotto Hero (sopra Categorie)', 'pharmanow' ),
			'home_after_categories'   => __( 'Home — 2. Sotto Categorie', 'pharmanow' ),
			'home_after_farmaci'      => __( 'Home — 3. Terza sezione', 'pharmanow' ),
			'home_after_fiducia'      => __( 'Home — 4. Quarta sezione', 'pharmanow' ),
			'home_after_integratori'  => __( 'Home — 5. Quinta sezione', 'pharmanow' ),
			'home_after_explore'      => __( 'Home — 6. Sotto Esplora per categoria', 'pharmanow' ),
			'home_after_pharmacist'   => __( 'Home — 7. Settima sezione (sopra FAQ)', 'pharmanow' ),
			'home_before_newsletter'  => __( 'Home — 8. Sopra Newsletter (sotto FAQ)', 'pharmanow' ),
		)
	);
}

/**
 * Registra il CPT.
 */
add_action(
	'init',
	function () {
		register_post_type(
			'pn_banner',
			array(
				'labels'             => array(
					'name'               => __( 'Banner', 'pharmanow' ),
					'singular_name'      => __( 'Banner', 'pharmanow' ),
					'menu_name'          => __( 'Banner', 'pharmanow' ),
					'add_new'            => __( 'Aggiungi nuovo', 'pharmanow' ),
					'add_new_item'       => __( 'Aggiungi banner', 'pharmanow' ),
					'edit_item'          => __( 'Modifica banner', 'pharmanow' ),
					'new_item'           => __( 'Nuovo banner', 'pharmanow' ),
					'view_item'          => __( 'Vedi banner', 'pharmanow' ),
					'search_items'       => __( 'Cerca banner', 'pharmanow' ),
					'not_found'          => __( 'Nessun banner', 'pharmanow' ),
					'not_found_in_trash' => __( 'Nessun banner nel cestino', 'pharmanow' ),
				),
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'show_in_rest'       => false,
				'menu_icon'          => 'dashicons-format-image',
				'menu_position'      => 27,
				'supports'           => array( 'title' ),
				'has_archive'        => false,
				'rewrite'            => false,
				'capability_type'    => 'post',
			)
		);
	}
);

/**
 * Metabox configurazione.
 */
add_action(
	'add_meta_boxes',
	function () {
		add_meta_box(
			'pn_banner_config',
			__( 'Configurazione banner', 'pharmanow' ),
			'pn_banner_render_metabox',
			'pn_banner',
			'normal',
			'high'
		);
	}
);

/**
 * Enqueue media uploader nelle schermate del CPT.
 */
add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || 'pn_banner' !== $screen->post_type ) {
			return;
		}
		wp_enqueue_media();
	}
);

/**
 * Render metabox — UI Banner Builder moderna (card layout, toggle, preview live, link type chips).
 */
function pn_banner_render_metabox( $post ): void {
	wp_nonce_field( 'pn_banner_save', 'pn_banner_nonce' );

	$img_d_id   = (int) get_post_meta( $post->ID, '_pn_banner_image_desktop_id', true );
	$img_m_id   = (int) get_post_meta( $post->ID, '_pn_banner_image_mobile_id', true );
	$alt        = (string) get_post_meta( $post->ID, '_pn_banner_alt', true );
	$link_type  = (string) get_post_meta( $post->ID, '_pn_banner_link_type', true );
	$link_url   = (string) get_post_meta( $post->ID, '_pn_banner_link_url', true );
	$link_promo = (int) get_post_meta( $post->ID, '_pn_banner_link_promo_id', true );
	$new_tab    = (string) get_post_meta( $post->ID, '_pn_banner_link_new_tab', true );
	$placement  = (string) get_post_meta( $post->ID, '_pn_banner_placement', true );
	$order_raw  = get_post_meta( $post->ID, '_pn_banner_order', true );
	$order      = '' === $order_raw ? 10 : (int) $order_raw;
	$start_at   = (string) get_post_meta( $post->ID, '_pn_banner_start_at', true );
	$end_at     = (string) get_post_meta( $post->ID, '_pn_banner_end_at', true );
	$active     = (string) get_post_meta( $post->ID, '_pn_banner_active', true );
	$full_width = (string) get_post_meta( $post->ID, '_pn_banner_full_width', true );

	if ( '' === $link_type ) {
		$link_type = 'none';
	}

	$placements = pn_banner_placements();
	$promos     = get_posts(
		array(
			'post_type'      => 'pn_promo',
			'post_status'    => 'publish',
			'posts_per_page' => 200,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);

	$img_d_url = $img_d_id ? wp_get_attachment_image_url( $img_d_id, 'medium_large' ) : '';
	$img_m_url = $img_m_id ? wp_get_attachment_image_url( $img_m_id, 'medium' ) : '';
	?>
	<style>
		.pn-bb { --pn-bg:#f8fafc; --pn-card:#fff; --pn-border:#e5e7eb; --pn-text:#0f172a; --pn-muted:#64748b; --pn-primary:#0b8894; --pn-radius:10px; --pn-shadow:0 1px 2px rgba(0,0,0,.04), 0 0 0 1px rgba(0,0,0,.04); display:grid; grid-template-columns:1fr 320px; gap:18px; padding:4px; font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif; color:var(--pn-text); }
		@media (max-width:1100px) { .pn-bb { grid-template-columns:1fr; } }
		.pn-bb__col { display:flex; flex-direction:column; gap:14px; }
		.pn-bb-card { background:var(--pn-card); border-radius:var(--pn-radius); box-shadow:var(--pn-shadow); padding:18px; }
		.pn-bb-card__title { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--pn-muted); margin:0 0 14px; display:flex; align-items:center; gap:8px; }
		.pn-bb-card__title .dashicons { color:var(--pn-primary); width:16px; height:16px; font-size:16px; }
		.pn-bb-field { display:flex; flex-direction:column; gap:6px; margin-bottom:14px; }
		.pn-bb-field:last-child { margin-bottom:0; }
		.pn-bb-field__label { font-weight:600; font-size:12px; color:var(--pn-text); }
		.pn-bb-field__help { font-size:11px; color:var(--pn-muted); margin:2px 0 0; }
		.pn-bb-input, .pn-bb-select { width:100%; padding:8px 12px; border:1px solid var(--pn-border); border-radius:6px; font-size:13px; background:#fff; transition:border-color .15s, box-shadow .15s; box-sizing:border-box; height:auto; line-height:1.4; }
		.pn-bb-input:focus, .pn-bb-select:focus { outline:none; border-color:var(--pn-primary); box-shadow:0 0 0 3px rgba(11,136,148,.15); }
		.pn-bb-input--num { max-width:100px; }

		/* Toggle switch */
		.pn-bb-toggle { display:inline-flex; align-items:center; gap:10px; cursor:pointer; user-select:none; }
		.pn-bb-toggle input { display:none; }
		.pn-bb-toggle__track { width:38px; height:22px; border-radius:11px; background:#cbd5e1; position:relative; transition:background .2s; flex-shrink:0; }
		.pn-bb-toggle__track::after { content:''; position:absolute; width:18px; height:18px; border-radius:50%; background:#fff; top:2px; left:2px; box-shadow:0 1px 3px rgba(0,0,0,.25); transition:transform .2s; }
		.pn-bb-toggle input:checked + .pn-bb-toggle__track { background:#10b981; }
		.pn-bb-toggle input:checked + .pn-bb-toggle__track::after { transform:translateX(16px); }
		.pn-bb-toggle__label { font-size:13px; font-weight:500; }

		/* Image picker dropzone */
		.pn-bb-pic { border:2px dashed var(--pn-border); border-radius:var(--pn-radius); background:var(--pn-bg); transition:border-color .15s, background .15s; overflow:hidden; position:relative; cursor:pointer; }
		.pn-bb-pic:hover { border-color:var(--pn-primary); background:#f0fdfa; }
		.pn-bb-pic--has { padding:0; border-style:solid; border-color:var(--pn-border); background:#000; }
		.pn-bb-pic__empty { padding:32px 16px; text-align:center; color:var(--pn-muted); font-size:12px; }
		.pn-bb-pic__empty .dashicons { color:var(--pn-primary); display:block; margin:0 auto 8px; width:32px; height:32px; font-size:32px; }
		.pn-bb-pic__img { width:100%; height:auto; max-height:240px; object-fit:contain; display:block; }
		.pn-bb-pic__img--mobile { max-height:200px; max-width:280px; margin:0 auto; }
		.pn-bb-pic__overlay { position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,.7), rgba(0,0,0,.1) 50%, rgba(0,0,0,0)); display:flex; align-items:flex-end; justify-content:center; gap:8px; padding:12px; opacity:0; transition:opacity .15s; }
		.pn-bb-pic--has:hover .pn-bb-pic__overlay { opacity:1; }
		.pn-bb-pic__btn { background:rgba(255,255,255,.95); border:0; padding:6px 14px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; color:var(--pn-text); transition:background .15s; }
		.pn-bb-pic__btn:hover { background:#fff; }
		.pn-bb-pic__btn--danger { color:#dc2626; }

		/* Link type chips */
		.pn-bb-chips { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-bottom:14px; }
		.pn-bb-chip { padding:14px 10px; border:2px solid var(--pn-border); border-radius:8px; text-align:center; cursor:pointer; font-size:12px; font-weight:600; color:var(--pn-muted); transition:all .15s; background:#fff; display:flex; flex-direction:column; align-items:center; gap:6px; }
		.pn-bb-chip:hover { border-color:#cbd5e1; }
		.pn-bb-chip .dashicons { width:20px; height:20px; font-size:20px; }
		.pn-bb-chip input { display:none; }
		.pn-bb-chip--checked { border-color:var(--pn-primary); background:#f0fdfa; color:var(--pn-primary); }
		.pn-bb-link-block { padding:12px; background:var(--pn-bg); border:1px solid var(--pn-border); border-radius:8px; }

		/* Status badge */
		.pn-bb-status { display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:999px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
		.pn-bb-status--on { background:#d1fae5; color:#065f46; }
		.pn-bb-status--off { background:#fee2e2; color:#991b1b; }
		.pn-bb-status::before { content:''; width:6px; height:6px; border-radius:50%; background:currentColor; }

		/* Inline row label + control */
		.pn-bb-row { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:10px 0; border-bottom:1px solid var(--pn-border); }
		.pn-bb-row:last-child { border-bottom:0; padding-bottom:0; }
		.pn-bb-row:first-child { padding-top:0; }
		.pn-bb-row__label { font-size:12px; color:var(--pn-muted); font-weight:500; }
	</style>

	<div class="pn-bb" data-bb-root>
		<!-- ═══════════════════════ COLONNA SX (immagini + link) ═══════════════════════ -->
		<div class="pn-bb__col">

			<!-- Card: Immagini -->
			<div class="pn-bb-card">
				<h3 class="pn-bb-card__title"><span class="dashicons dashicons-format-image"></span> <?php esc_html_e( 'Immagini banner', 'pharmanow' ); ?></h3>

				<div class="pn-bb-field">
					<label class="pn-bb-field__label"><?php esc_html_e( 'Desktop (≥ 768px)', 'pharmanow' ); ?> <span style="color:#dc2626">*</span></label>
					<div class="pn-bb-pic <?php echo $img_d_url ? 'pn-bb-pic--has' : ''; ?>" data-pn-banner-media data-target="desktop">
						<?php if ( $img_d_url ) : ?>
							<img src="<?php echo esc_url( $img_d_url ); ?>" alt="" class="pn-bb-pic__img" data-preview-img>
							<div class="pn-bb-pic__overlay">
								<button type="button" class="pn-bb-pic__btn" data-pick><?php esc_html_e( 'Cambia', 'pharmanow' ); ?></button>
								<button type="button" class="pn-bb-pic__btn pn-bb-pic__btn--danger" data-clear><?php esc_html_e( 'Rimuovi', 'pharmanow' ); ?></button>
							</div>
						<?php else : ?>
							<div class="pn-bb-pic__empty" data-pick>
								<span class="dashicons dashicons-cloud-upload"></span>
								<strong><?php esc_html_e( 'Carica immagine desktop', 'pharmanow' ); ?></strong><br>
								<?php esc_html_e( 'consigliato 1920×500px', 'pharmanow' ); ?>
							</div>
						<?php endif; ?>
						<input type="hidden" name="pn_banner_image_desktop_id" value="<?php echo esc_attr( (string) $img_d_id ); ?>" data-input>
					</div>
				</div>

				<div class="pn-bb-field">
					<label class="pn-bb-field__label"><?php esc_html_e( 'Mobile (< 768px)', 'pharmanow' ); ?></label>
					<div class="pn-bb-pic <?php echo $img_m_url ? 'pn-bb-pic--has' : ''; ?>" data-pn-banner-media data-target="mobile">
						<?php if ( $img_m_url ) : ?>
							<img src="<?php echo esc_url( $img_m_url ); ?>" alt="" class="pn-bb-pic__img pn-bb-pic__img--mobile" data-preview-img>
							<div class="pn-bb-pic__overlay">
								<button type="button" class="pn-bb-pic__btn" data-pick><?php esc_html_e( 'Cambia', 'pharmanow' ); ?></button>
								<button type="button" class="pn-bb-pic__btn pn-bb-pic__btn--danger" data-clear><?php esc_html_e( 'Rimuovi', 'pharmanow' ); ?></button>
							</div>
						<?php else : ?>
							<div class="pn-bb-pic__empty" data-pick>
								<span class="dashicons dashicons-smartphone"></span>
								<strong><?php esc_html_e( 'Carica versione mobile (opzionale)', 'pharmanow' ); ?></strong><br>
								<?php esc_html_e( 'consigliato 750×500px — vuoto = usa desktop', 'pharmanow' ); ?>
							</div>
						<?php endif; ?>
						<input type="hidden" name="pn_banner_image_mobile_id" value="<?php echo esc_attr( (string) $img_m_id ); ?>" data-input>
					</div>
				</div>

				<div class="pn-bb-field">
					<label for="pn_banner_alt" class="pn-bb-field__label"><?php esc_html_e( 'Testo alternativo (alt)', 'pharmanow' ); ?></label>
					<input type="text" id="pn_banner_alt" name="pn_banner_alt" value="<?php echo esc_attr( $alt ); ?>" class="pn-bb-input" placeholder="<?php esc_attr_e( 'Es. The SIP — Set Completo di biologia marina', 'pharmanow' ); ?>">
					<p class="pn-bb-field__help"><?php esc_html_e( 'Descrive l\'immagine per accessibilità e SEO. Se vuoto, usa il titolo del banner.', 'pharmanow' ); ?></p>
				</div>
			</div>

			<!-- Card: Destinazione click -->
			<div class="pn-bb-card">
				<h3 class="pn-bb-card__title"><span class="dashicons dashicons-admin-links"></span> <?php esc_html_e( 'Destinazione click', 'pharmanow' ); ?></h3>

				<div class="pn-bb-chips" data-link-chips>
					<label class="pn-bb-chip <?php echo 'none' === $link_type ? 'pn-bb-chip--checked' : ''; ?>">
						<input type="radio" name="pn_banner_link_type" value="none" <?php checked( $link_type, 'none' ); ?>>
						<span class="dashicons dashicons-no-alt"></span>
						<?php esc_html_e( 'Nessun link', 'pharmanow' ); ?>
					</label>
					<label class="pn-bb-chip <?php echo 'promo' === $link_type ? 'pn-bb-chip--checked' : ''; ?>">
						<input type="radio" name="pn_banner_link_type" value="promo" <?php checked( $link_type, 'promo' ); ?>>
						<span class="dashicons dashicons-megaphone"></span>
						<?php esc_html_e( 'Pagina promozione', 'pharmanow' ); ?>
					</label>
					<label class="pn-bb-chip <?php echo 'url' === $link_type ? 'pn-bb-chip--checked' : ''; ?>">
						<input type="radio" name="pn_banner_link_type" value="url" <?php checked( $link_type, 'url' ); ?>>
						<span class="dashicons dashicons-admin-site-alt3"></span>
						<?php esc_html_e( 'URL custom', 'pharmanow' ); ?>
					</label>
				</div>

				<div class="pn-bb-link-block" data-link-block="promo" style="<?php echo 'promo' === $link_type ? '' : 'display:none'; ?>">
					<div class="pn-bb-field">
						<label for="pn_banner_link_promo_id" class="pn-bb-field__label"><?php esc_html_e( 'Scegli promozione', 'pharmanow' ); ?></label>
						<select id="pn_banner_link_promo_id" name="pn_banner_link_promo_id" class="pn-bb-select">
							<option value="0"><?php esc_html_e( '— Seleziona una promo pubblicata —', 'pharmanow' ); ?></option>
							<?php foreach ( $promos as $promo ) : ?>
								<option value="<?php echo (int) $promo->ID; ?>" <?php selected( $link_promo, (int) $promo->ID ); ?>>
									<?php echo esc_html( $promo->post_title ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<?php if ( ! $promos ) : ?>
							<p class="pn-bb-field__help"><?php esc_html_e( 'Nessuna promozione pubblicata. Creane una dal menu "Promozioni".', 'pharmanow' ); ?></p>
						<?php endif; ?>
					</div>
				</div>

				<div class="pn-bb-link-block" data-link-block="url" style="<?php echo 'url' === $link_type ? '' : 'display:none'; ?>">
					<div class="pn-bb-field">
						<label for="pn_banner_link_url" class="pn-bb-field__label"><?php esc_html_e( 'URL destinazione', 'pharmanow' ); ?></label>
						<input type="url" id="pn_banner_link_url" name="pn_banner_link_url" value="<?php echo esc_attr( $link_url ); ?>" placeholder="https://... oppure /catalogo?on_sale=1" class="pn-bb-input">
					</div>
				</div>

				<label class="pn-bb-toggle" style="margin-top:14px">
					<input type="checkbox" name="pn_banner_link_new_tab" value="1" <?php checked( '1', $new_tab ); ?>>
					<span class="pn-bb-toggle__track"></span>
					<span class="pn-bb-toggle__label"><?php esc_html_e( 'Apri in nuova scheda', 'pharmanow' ); ?></span>
				</label>
			</div>
		</div>

		<!-- ═══════════════════════ SIDEBAR (stato + zona + schedule) ═══════════════════════ -->
		<div class="pn-bb__col">

			<!-- Card: Stato -->
			<div class="pn-bb-card">
				<h3 class="pn-bb-card__title"><span class="dashicons dashicons-visibility"></span> <?php esc_html_e( 'Stato', 'pharmanow' ); ?></h3>

				<div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
					<label class="pn-bb-toggle">
						<input type="checkbox" name="pn_banner_active" value="1" <?php checked( '1', $active ); ?> data-active-toggle>
						<span class="pn-bb-toggle__track"></span>
						<span class="pn-bb-toggle__label"><?php esc_html_e( 'Pubblicato', 'pharmanow' ); ?></span>
					</label>
					<span class="pn-bb-status pn-bb-status--<?php echo '1' === $active ? 'on' : 'off'; ?>" data-status-badge>
						<?php echo '1' === $active ? esc_html__( 'Visibile', 'pharmanow' ) : esc_html__( 'Nascosto', 'pharmanow' ); ?>
					</span>
				</div>
				<p class="pn-bb-field__help" style="margin-top:10px"><?php esc_html_e( 'Disattiva per nascondere il banner senza eliminarlo.', 'pharmanow' ); ?></p>
			</div>

			<!-- Card: Posizione -->
			<div class="pn-bb-card">
				<h3 class="pn-bb-card__title"><span class="dashicons dashicons-location"></span> <?php esc_html_e( 'Posizione nella home', 'pharmanow' ); ?></h3>

				<div class="pn-bb-field">
					<label for="pn_banner_placement" class="pn-bb-field__label"><?php esc_html_e( 'Zona', 'pharmanow' ); ?></label>
					<select id="pn_banner_placement" name="pn_banner_placement" class="pn-bb-select">
						<option value=""><?php esc_html_e( '— Seleziona zona —', 'pharmanow' ); ?></option>
						<?php foreach ( $placements as $slug => $label ) : ?>
							<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $placement, $slug ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="pn-bb-field">
					<label for="pn_banner_order" class="pn-bb-field__label"><?php esc_html_e( 'Ordine nella zona', 'pharmanow' ); ?></label>
					<input type="number" id="pn_banner_order" name="pn_banner_order" value="<?php echo esc_attr( (string) $order ); ?>" min="0" step="1" class="pn-bb-input pn-bb-input--num">
					<p class="pn-bb-field__help"><?php esc_html_e( 'Più basso = mostrato prima. Default 10.', 'pharmanow' ); ?></p>
				</div>

				<label class="pn-bb-toggle" style="margin-top:6px">
					<input type="checkbox" name="pn_banner_full_width" value="1" <?php checked( '1', $full_width ); ?>>
					<span class="pn-bb-toggle__track"></span>
					<span class="pn-bb-toggle__label"><?php esc_html_e( 'Edge-to-edge', 'pharmanow' ); ?></span>
				</label>
				<p class="pn-bb-field__help" style="margin-top:6px"><?php esc_html_e( 'Banner a tutta larghezza schermo invece che dentro container.', 'pharmanow' ); ?></p>
			</div>

			<!-- Card: Programmazione -->
			<div class="pn-bb-card">
				<h3 class="pn-bb-card__title"><span class="dashicons dashicons-clock"></span> <?php esc_html_e( 'Programmazione', 'pharmanow' ); ?></h3>

				<div class="pn-bb-field">
					<label for="pn_banner_start_at" class="pn-bb-field__label"><?php esc_html_e( 'Inizio pubblicazione', 'pharmanow' ); ?></label>
					<input type="datetime-local" id="pn_banner_start_at" name="pn_banner_start_at" value="<?php echo esc_attr( $start_at ); ?>" class="pn-bb-input">
					<p class="pn-bb-field__help"><?php esc_html_e( 'Vuoto = mostra subito.', 'pharmanow' ); ?></p>
				</div>

				<div class="pn-bb-field">
					<label for="pn_banner_end_at" class="pn-bb-field__label"><?php esc_html_e( 'Fine pubblicazione', 'pharmanow' ); ?></label>
					<input type="datetime-local" id="pn_banner_end_at" name="pn_banner_end_at" value="<?php echo esc_attr( $end_at ); ?>" class="pn-bb-input">
					<p class="pn-bb-field__help"><?php esc_html_e( 'Vuoto = senza scadenza.', 'pharmanow' ); ?></p>
				</div>
			</div>
		</div>
	</div>

	<script>
	(function(){
		// Media uploader
		document.querySelectorAll('[data-pn-banner-media]').forEach(function(box){
			var input  = box.querySelector('[data-input]');
			var picks  = box.querySelectorAll('[data-pick]');
			var clear  = box.querySelector('[data-clear]');
			var target = box.getAttribute('data-target');
			var frame;

			function open(e){
				if (e) e.preventDefault();
				if (frame) { frame.open(); return; }
				frame = wp.media({
					title: '<?php echo esc_js( __( 'Scegli immagine', 'pharmanow' ) ); ?>',
					button: { text: '<?php echo esc_js( __( 'Usa questa immagine', 'pharmanow' ) ); ?>' },
					library: { type: 'image' },
					multiple: false
				});
				frame.on('select', function(){
					var att = frame.state().get('selection').first().toJSON();
					input.value = att.id;
					var url = (att.sizes && (att.sizes.medium_large || att.sizes.large || att.sizes.medium)) ? (att.sizes.medium_large || att.sizes.large || att.sizes.medium).url : att.url;
					box.classList.add('pn-bb-pic--has');
					box.innerHTML = '<img src="'+url+'" alt="" class="pn-bb-pic__img'+(target==="mobile"?" pn-bb-pic__img--mobile":"")+'" data-preview-img>'
						+ '<div class="pn-bb-pic__overlay">'
						+ '<button type="button" class="pn-bb-pic__btn" data-pick><?php echo esc_js( __( 'Cambia', 'pharmanow' ) ); ?></button>'
						+ '<button type="button" class="pn-bb-pic__btn pn-bb-pic__btn--danger" data-clear><?php echo esc_js( __( 'Rimuovi', 'pharmanow' ) ); ?></button>'
						+ '</div>'
						+ '<input type="hidden" name="pn_banner_image_'+target+'_id" value="'+att.id+'" data-input>';
					attachHandlers(box);
				});
				frame.open();
			}

			picks.forEach(function(p){ p.addEventListener('click', open); });
			if (clear) {
				clear.addEventListener('click', function(e){
					e.preventDefault();
					var inp = box.querySelector('[data-input]');
					inp.value = '';
					box.classList.remove('pn-bb-pic--has');
					var label = (target === 'desktop')
						? '<?php echo esc_js( __( 'Carica immagine desktop', 'pharmanow' ) ); ?>'
						: '<?php echo esc_js( __( 'Carica versione mobile (opzionale)', 'pharmanow' ) ); ?>';
					var hint = (target === 'desktop')
						? '<?php echo esc_js( __( 'consigliato 1920×500px', 'pharmanow' ) ); ?>'
						: '<?php echo esc_js( __( 'consigliato 750×500px — vuoto = usa desktop', 'pharmanow' ) ); ?>';
					var icon = (target === 'desktop') ? 'cloud-upload' : 'smartphone';
					box.innerHTML = '<div class="pn-bb-pic__empty" data-pick>'
						+ '<span class="dashicons dashicons-'+icon+'"></span>'
						+ '<strong>'+label+'</strong><br>'
						+ hint
						+ '</div>'
						+ '<input type="hidden" name="pn_banner_image_'+target+'_id" value="" data-input>';
					attachHandlers(box);
				});
			}
		});

		function attachHandlers(box){
			// Re-attach quando il contenuto del box cambia (dopo upload/clear).
			var picks = box.querySelectorAll('[data-pick]');
			var clear = box.querySelector('[data-clear]');
			var input = box.querySelector('[data-input]');
			var target = box.getAttribute('data-target');
			var frame;

			function open(e){
				if (e) e.preventDefault();
				if (frame) { frame.open(); return; }
				frame = wp.media({ title: 'Scegli immagine', button: { text: 'Usa questa immagine' }, library: { type: 'image' }, multiple: false });
				frame.on('select', function(){
					var att = frame.state().get('selection').first().toJSON();
					input.value = att.id;
					var url = (att.sizes && (att.sizes.medium_large || att.sizes.large || att.sizes.medium)) ? (att.sizes.medium_large || att.sizes.large || att.sizes.medium).url : att.url;
					box.classList.add('pn-bb-pic--has');
					box.innerHTML = '<img src="'+url+'" alt="" class="pn-bb-pic__img'+(target==="mobile"?" pn-bb-pic__img--mobile":"")+'" data-preview-img>'
						+ '<div class="pn-bb-pic__overlay">'
						+ '<button type="button" class="pn-bb-pic__btn" data-pick>Cambia</button>'
						+ '<button type="button" class="pn-bb-pic__btn pn-bb-pic__btn--danger" data-clear>Rimuovi</button>'
						+ '</div>'
						+ '<input type="hidden" name="pn_banner_image_'+target+'_id" value="'+att.id+'" data-input>';
					attachHandlers(box);
				});
				frame.open();
			}
			picks.forEach(function(p){ p.addEventListener('click', open); });
			if (clear) {
				clear.addEventListener('click', function(e){
					e.preventDefault();
					var inp = box.querySelector('[data-input]');
					inp.value = '';
					box.classList.remove('pn-bb-pic--has');
					box.innerHTML = '<div class="pn-bb-pic__empty" data-pick>'
						+ '<span class="dashicons dashicons-format-image"></span><br>'
						+ '<strong>Carica immagine</strong>'
						+ '</div>'
						+ '<input type="hidden" name="pn_banner_image_'+target+'_id" value="" data-input>';
					attachHandlers(box);
				});
			}
		}

		// Link type chips
		document.querySelectorAll('[data-link-chips] input[name="pn_banner_link_type"]').forEach(function(r){
			r.addEventListener('change', function(){
				document.querySelectorAll('[data-link-chips] .pn-bb-chip').forEach(function(c){ c.classList.remove('pn-bb-chip--checked'); });
				r.closest('.pn-bb-chip').classList.add('pn-bb-chip--checked');
				document.querySelectorAll('[data-link-block]').forEach(function(b){
					b.style.display = (b.getAttribute('data-link-block') === r.value) ? '' : 'none';
				});
			});
		});

		// Active toggle: live update status badge
		var activeToggle = document.querySelector('[data-active-toggle]');
		var statusBadge  = document.querySelector('[data-status-badge]');
		if (activeToggle && statusBadge) {
			activeToggle.addEventListener('change', function(){
				if (activeToggle.checked) {
					statusBadge.classList.remove('pn-bb-status--off');
					statusBadge.classList.add('pn-bb-status--on');
					statusBadge.textContent = '<?php echo esc_js( __( 'Visibile', 'pharmanow' ) ); ?>';
				} else {
					statusBadge.classList.remove('pn-bb-status--on');
					statusBadge.classList.add('pn-bb-status--off');
					statusBadge.textContent = '<?php echo esc_js( __( 'Nascosto', 'pharmanow' ) ); ?>';
				}
			});
		}
	})();
	</script>
	<?php
}

/**
 * Salva metabox.
 */
add_action(
	'save_post_pn_banner',
	function ( $post_id ) {
		if ( ! isset( $_POST['pn_banner_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['pn_banner_nonce'] ), 'pn_banner_save' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$placements_keys = array_keys( pn_banner_placements() );

		$placement = isset( $_POST['pn_banner_placement'] ) ? sanitize_key( wp_unslash( $_POST['pn_banner_placement'] ) ) : '';
		if ( $placement && ! in_array( $placement, $placements_keys, true ) ) {
			$placement = '';
		}
		update_post_meta( $post_id, '_pn_banner_placement', $placement );

		$link_type = isset( $_POST['pn_banner_link_type'] ) ? sanitize_key( wp_unslash( $_POST['pn_banner_link_type'] ) ) : 'none';
		if ( ! in_array( $link_type, array( 'none', 'url', 'promo' ), true ) ) {
			$link_type = 'none';
		}
		update_post_meta( $post_id, '_pn_banner_link_type', $link_type );

		$pairs = array(
			'_pn_banner_image_desktop_id' => array( 'pn_banner_image_desktop_id', 'absint' ),
			'_pn_banner_image_mobile_id'  => array( 'pn_banner_image_mobile_id', 'absint' ),
			'_pn_banner_alt'              => array( 'pn_banner_alt', 'sanitize_text_field' ),
			'_pn_banner_link_url'         => array( 'pn_banner_link_url', 'esc_url_raw' ),
			'_pn_banner_link_promo_id'    => array( 'pn_banner_link_promo_id', 'absint' ),
			'_pn_banner_order'            => array( 'pn_banner_order', 'absint' ),
			'_pn_banner_start_at'         => array( 'pn_banner_start_at', 'pn_banner_sanitize_datetime' ),
			'_pn_banner_end_at'           => array( 'pn_banner_end_at', 'pn_banner_sanitize_datetime' ),
		);
		foreach ( $pairs as $meta_key => $cfg ) {
			[ $field, $sanitizer ] = $cfg;
			$raw = isset( $_POST[ $field ] ) ? wp_unslash( $_POST[ $field ] ) : '';
			$val = call_user_func( $sanitizer, $raw );
			if ( '' === $val || 0 === $val ) {
				delete_post_meta( $post_id, $meta_key );
			} else {
				update_post_meta( $post_id, $meta_key, $val );
			}
		}

		$checkboxes = array(
			'_pn_banner_active'        => 'pn_banner_active',
			'_pn_banner_link_new_tab'  => 'pn_banner_link_new_tab',
			'_pn_banner_full_width'    => 'pn_banner_full_width',
		);
		foreach ( $checkboxes as $meta_key => $field ) {
			if ( ! empty( $_POST[ $field ] ) ) {
				update_post_meta( $post_id, $meta_key, '1' );
			} else {
				delete_post_meta( $post_id, $meta_key );
			}
		}

		pn_banner_flush_cache();
	},
	10,
	1
);

add_action(
	'deleted_post',
	function ( $post_id ) {
		if ( 'pn_banner' === get_post_type( $post_id ) ) {
			pn_banner_flush_cache();
		}
	}
);

add_action(
	'trashed_post',
	function ( $post_id ) {
		if ( 'pn_banner' === get_post_type( $post_id ) ) {
			pn_banner_flush_cache();
		}
	}
);

/**
 * Sanitizza datetime-local (formato YYYY-MM-DDTHH:MM).
 */
function pn_banner_sanitize_datetime( $raw ): string {
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
 * Invalida tutti i transient banner.
 */
function pn_banner_flush_cache(): void {
	foreach ( array_keys( pn_banner_placements() ) as $slug ) {
		delete_transient( PN_BANNER_CACHE_GROUP . $slug );
	}
}

/**
 * Restituisce i banner attivi per una placement (filtrati per schedule + active).
 *
 * @return array<int,WP_Post>
 */
function pn_banner_get_for_placement( string $placement ): array {
	if ( '' === $placement ) {
		return array();
	}

	$cache_key = PN_BANNER_CACHE_GROUP . $placement;
	$cached    = get_transient( $cache_key );
	if ( false !== $cached && is_array( $cached ) ) {
		$ids   = $cached;
		$posts = array();
		foreach ( $ids as $id ) {
			$p = get_post( (int) $id );
			if ( $p ) {
				$posts[] = $p;
			}
		}
		return pn_banner_filter_active( $posts );
	}

	$q = new WP_Query(
		array(
			'post_type'              => 'pn_banner',
			'post_status'            => 'publish',
			'posts_per_page'         => 50,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'meta_query'             => array(
				array(
					'key'   => '_pn_banner_placement',
					'value' => $placement,
				),
			),
			'meta_key'               => '_pn_banner_order',
			'orderby'                => 'meta_value_num',
			'order'                  => 'ASC',
		)
	);

	$posts = $q->posts;
	set_transient( $cache_key, wp_list_pluck( $posts, 'ID' ), PN_BANNER_CACHE_TTL );

	return pn_banner_filter_active( $posts );
}

/**
 * Filtra banner per active flag + finestra schedule (calcolata a runtime).
 *
 * @param array<int,WP_Post> $posts
 * @return array<int,WP_Post>
 */
function pn_banner_filter_active( array $posts ): array {
	$now = current_time( 'timestamp' );
	$out = array();
	foreach ( $posts as $p ) {
		if ( '1' !== get_post_meta( $p->ID, '_pn_banner_active', true ) ) {
			continue;
		}
		$start = (string) get_post_meta( $p->ID, '_pn_banner_start_at', true );
		$end   = (string) get_post_meta( $p->ID, '_pn_banner_end_at', true );
		if ( $start ) {
			$ts = strtotime( $start );
			if ( $ts && $ts > $now ) {
				continue;
			}
		}
		if ( $end ) {
			$ts = strtotime( $end );
			if ( $ts && $ts < $now ) {
				continue;
			}
		}
		$out[] = $p;
	}
	return $out;
}

/**
 * URL di destinazione del banner (vuoto = nessun link).
 */
function pn_banner_resolve_link( int $banner_id ): string {
	$type = (string) get_post_meta( $banner_id, '_pn_banner_link_type', true );
	if ( 'url' === $type ) {
		return (string) get_post_meta( $banner_id, '_pn_banner_link_url', true );
	}
	if ( 'promo' === $type ) {
		$pid = (int) get_post_meta( $banner_id, '_pn_banner_link_promo_id', true );
		if ( $pid > 0 ) {
			$url = get_permalink( $pid );
			return $url ? $url : '';
		}
	}
	return '';
}

/**
 * Hook: stampa tutti i banner attivi per una zona.
 * Uso: do_action( 'pn_banner_zone', 'homepage_top' );
 */
add_action(
	'pn_banner_zone',
	function ( string $placement ) {
		$banners = pn_banner_get_for_placement( $placement );
		if ( ! $banners ) {
			return;
		}
		foreach ( $banners as $banner ) {
			set_query_var( 'pn_banner', $banner );
			get_template_part( 'template-parts/shared/banner' );
		}
	}
);

/**
 * Colonna admin: posizione + stato.
 */
add_filter(
	'manage_pn_banner_posts_columns',
	function ( $cols ) {
		$new = array();
		foreach ( $cols as $k => $v ) {
			$new[ $k ] = $v;
			if ( 'title' === $k ) {
				$new['pn_placement'] = __( 'Posizione', 'pharmanow' );
				$new['pn_active']    = __( 'Attivo', 'pharmanow' );
				$new['pn_order']     = __( 'Ordine', 'pharmanow' );
			}
		}
		return $new;
	}
);

add_action(
	'manage_pn_banner_posts_custom_column',
	function ( $col, $post_id ) {
		if ( 'pn_placement' === $col ) {
			$slug       = (string) get_post_meta( $post_id, '_pn_banner_placement', true );
			$placements = pn_banner_placements();
			echo esc_html( $placements[ $slug ] ?? '—' );
		} elseif ( 'pn_active' === $col ) {
			echo '1' === get_post_meta( $post_id, '_pn_banner_active', true ) ? '✅' : '⏸️';
		} elseif ( 'pn_order' === $col ) {
			echo (int) get_post_meta( $post_id, '_pn_banner_order', true );
		}
	},
	10,
	2
);
