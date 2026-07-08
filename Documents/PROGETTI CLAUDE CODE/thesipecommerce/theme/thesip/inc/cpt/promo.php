<?php
/**
 * CPT pn_promo: pagine promozionali (allineate a /promozione/[slug] del Next).
 *
 * Schema meta:
 *   _pn_promo_hero_desktop_id (int, attachment ID — preferito)
 *   _pn_promo_hero_mobile_id  (int, attachment ID — opzionale, fallback desktop)
 *   _pn_promo_hero_desktop    (string, URL — legacy / fallback se no ID)
 *   _pn_promo_hero_mobile     (string, URL — legacy / fallback se no ID)
 *   _pn_promo_hero_alt        (string)
 *   _pn_promo_coupon          (string)
 *   _pn_promo_end_date        (string, ISO date YYYY-MM-DD)
 *   _pn_promo_product_ids     (string, CSV di product ID Woo)
 *   _pn_promo_category_id     (int, term_id product_cat; 0 = none)
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registra il CPT.
 */
add_action(
	'init',
	function () {
		register_post_type(
			'pn_promo',
			array(
				'labels'             => array(
					'name'               => __( 'Promozioni', 'pharmanow' ),
					'singular_name'      => __( 'Promozione', 'pharmanow' ),
					'menu_name'          => __( 'Promozioni', 'pharmanow' ),
					'add_new'            => __( 'Aggiungi nuova', 'pharmanow' ),
					'add_new_item'       => __( 'Aggiungi promozione', 'pharmanow' ),
					'edit_item'          => __( 'Modifica promozione', 'pharmanow' ),
					'new_item'           => __( 'Nuova promozione', 'pharmanow' ),
					'view_item'          => __( 'Vedi promozione', 'pharmanow' ),
					'search_items'       => __( 'Cerca promozioni', 'pharmanow' ),
					'not_found'          => __( 'Nessuna promozione', 'pharmanow' ),
					'not_found_in_trash' => __( 'Nessuna promozione nel cestino', 'pharmanow' ),
				),
				'public'             => true,
				'publicly_queryable' => true,
				'show_in_menu'       => true,
				'show_in_rest'       => true,
				'menu_icon'          => 'dashicons-megaphone',
				'menu_position'      => 26,
				'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
				'has_archive'        => false,
				'rewrite'            => array(
					'slug'       => 'promozione',
					'with_front' => false,
				),
				'capability_type'    => 'post',
			)
		);
	}
);

/**
 * Disabilita il block editor (Gutenberg) per pn_promo — il customer care
 * gestisce tutto dal metabox custom + classic editor per la descrizione.
 */
add_filter(
	'use_block_editor_for_post_type',
	function ( $use, $post_type ) {
		return 'pn_promo' === $post_type ? false : $use;
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
		if ( ! $screen || 'pn_promo' !== $screen->post_type ) {
			return;
		}
		wp_enqueue_media();
	}
);

/**
 * Metabox configurazione promo.
 */
add_action(
	'add_meta_boxes',
	function () {
		add_meta_box(
			'pn_promo_config',
			__( 'Configurazione promozione', 'pharmanow' ),
			'pn_promo_render_metabox',
			'pn_promo',
			'normal',
			'high'
		);
	}
);

/**
 * Render metabox — Promo Builder UI (card layout, image picker, prodotti search).
 */
function pn_promo_render_metabox( $post ): void {
	wp_nonce_field( 'pn_promo_save', 'pn_promo_nonce' );

	$hero_d_id = (int) get_post_meta( $post->ID, '_pn_promo_hero_desktop_id', true );
	$hero_m_id = (int) get_post_meta( $post->ID, '_pn_promo_hero_mobile_id', true );
	$hero_d    = (string) get_post_meta( $post->ID, '_pn_promo_hero_desktop', true );
	$hero_m    = (string) get_post_meta( $post->ID, '_pn_promo_hero_mobile', true );
	$hero_alt  = (string) get_post_meta( $post->ID, '_pn_promo_hero_alt', true );
	$coupon    = (string) get_post_meta( $post->ID, '_pn_promo_coupon', true );
	$end_date  = (string) get_post_meta( $post->ID, '_pn_promo_end_date', true );
	$prod_ids  = (string) get_post_meta( $post->ID, '_pn_promo_product_ids', true );
	$cat_id    = (int) get_post_meta( $post->ID, '_pn_promo_category_id', true );

	$hero_d_url = $hero_d_id ? wp_get_attachment_image_url( $hero_d_id, 'medium_large' ) : $hero_d;
	$hero_m_url = $hero_m_id ? wp_get_attachment_image_url( $hero_m_id, 'medium' ) : $hero_m;

	$cats = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'fields'     => 'id=>name',
		)
	);

	// Risolvi prodotti correnti per la chip-list visiva.
	$selected_products = array();
	if ( $prod_ids ) {
		$ids = array_filter( array_map( 'absint', explode( ',', $prod_ids ) ) );
		foreach ( $ids as $pid ) {
			$p = function_exists( 'wc_get_product' ) ? wc_get_product( $pid ) : null;
			if ( $p ) {
				$selected_products[] = array(
					'id'    => (int) $pid,
					'title' => $p->get_name(),
					'sku'   => $p->get_sku(),
					'thumb' => wp_get_attachment_image_url( $p->get_image_id(), 'thumbnail' ),
				);
			}
		}
	}
	$is_published   = 'publish' === $post->post_status;
	$end_date_label = '';
	$end_date_class = 'pn-pb-stat__num--ok';
	if ( $end_date ) {
		$ts = strtotime( $end_date );
		if ( $ts ) {
			$days_left = (int) floor( ( $ts - current_time( 'timestamp' ) ) / DAY_IN_SECONDS );
			if ( $days_left < 0 ) {
				$end_date_label = __( 'Scaduta', 'pharmanow' );
				$end_date_class = 'pn-pb-stat__num--err';
			} elseif ( 0 === $days_left ) {
				$end_date_label = __( 'Oggi', 'pharmanow' );
				$end_date_class = 'pn-pb-stat__num--warn';
			} elseif ( $days_left <= 7 ) {
				$end_date_label = sprintf( /* translators: %d giorni */ _n( '%d giorno', '%d giorni', $days_left, 'pharmanow' ), $days_left );
				$end_date_class = 'pn-pb-stat__num--warn';
			} else {
				$end_date_label = sprintf( /* translators: %d giorni */ _n( '%d giorno', '%d giorni', $days_left, 'pharmanow' ), $days_left );
			}
		}
	} else {
		$end_date_label = '∞';
	}
	?>
	<style>
		.pn-pb { --pn-bg:#f8fafc; --pn-card:#fff; --pn-border:#e5e7eb; --pn-text:#0f172a; --pn-muted:#64748b; --pn-primary:#0b8894; --pn-primary-dark:#065a62; --pn-radius:12px; --pn-shadow:0 1px 2px rgba(0,0,0,.04), 0 0 0 1px rgba(0,0,0,.04); --pn-shadow-lg:0 4px 16px rgba(0,0,0,.06), 0 0 0 1px rgba(0,0,0,.04); padding:4px; font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif; color:var(--pn-text); }

		/* ───── Header dashboard ───── */
		.pn-pb-header { background:linear-gradient(135deg,var(--pn-primary),var(--pn-primary-dark)); border-radius:var(--pn-radius); padding:20px 24px; margin-bottom:18px; color:#fff; box-shadow:var(--pn-shadow-lg); display:flex; align-items:center; justify-content:space-between; gap:24px; flex-wrap:wrap; }
		.pn-pb-header__brand { display:flex; align-items:center; gap:14px; }
		.pn-pb-header__icon { width:48px; height:48px; background:rgba(255,255,255,.18); border-radius:12px; display:inline-flex; align-items:center; justify-content:center; backdrop-filter:blur(8px); }
		.pn-pb-header__icon .dashicons { color:#fff; width:24px; height:24px; font-size:24px; }
		.pn-pb-header__text h2 { margin:0 0 2px; font-size:18px; font-weight:700; color:#fff; }
		.pn-pb-header__text p { margin:0; font-size:12px; opacity:.85; }
		.pn-pb-header__stats { display:flex; gap:14px; align-items:stretch; }
		.pn-pb-stat { background:rgba(255,255,255,.14); backdrop-filter:blur(8px); padding:10px 16px; border-radius:10px; min-width:90px; text-align:center; }
		.pn-pb-stat__num { display:block; font-size:18px; font-weight:700; line-height:1.1; font-family:ui-monospace,SFMono-Regular,Menlo,monospace; }
		.pn-pb-stat__num--warn { color:#fde68a; }
		.pn-pb-stat__num--err { color:#fecaca; }
		.pn-pb-stat__label { display:block; font-size:10px; text-transform:uppercase; letter-spacing:.05em; opacity:.85; margin-top:2px; }

		/* ───── Body grid ───── */
		.pn-pb-body { display:grid; grid-template-columns:1fr 340px; gap:18px; }
		@media (max-width:1100px) { .pn-pb-body { grid-template-columns:1fr; } }
		.pn-pb__col { display:flex; flex-direction:column; gap:14px; }

		/* ───── Card ───── */
		.pn-pb-card { background:var(--pn-card); border-radius:var(--pn-radius); box-shadow:var(--pn-shadow); padding:20px; }
		.pn-pb-card__title { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:var(--pn-muted); margin:0 0 16px; display:flex; align-items:center; gap:8px; }
		.pn-pb-card__title .dashicons { color:var(--pn-primary); width:16px; height:16px; font-size:16px; }
		.pn-pb-card__title-count { margin-left:auto; background:var(--pn-bg); color:var(--pn-text); padding:2px 8px; border-radius:10px; font-size:10px; }

		/* ───── Field ───── */
		.pn-pb-field { display:flex; flex-direction:column; gap:6px; margin-bottom:14px; }
		.pn-pb-field:last-child { margin-bottom:0; }
		.pn-pb-field__label { font-weight:600; font-size:12px; color:var(--pn-text); }
		.pn-pb-field__help { font-size:11px; color:var(--pn-muted); margin:2px 0 0; }
		.pn-pb-input, .pn-pb-select { width:100%; padding:9px 12px; border:1px solid var(--pn-border); border-radius:8px; font-size:13px; background:#fff; box-sizing:border-box; line-height:1.4; transition:border-color .15s, box-shadow .15s; }
		.pn-pb-input:focus, .pn-pb-select:focus { outline:none; border-color:var(--pn-primary); box-shadow:0 0 0 3px rgba(11,136,148,.15); }

		/* ───── Hero picker ───── */
		.pn-pb-hero-grid { display:grid; grid-template-columns:1fr 240px; gap:14px; }
		@media (max-width:880px) { .pn-pb-hero-grid { grid-template-columns:1fr; } }
		.pn-pb-pic { border:2px dashed var(--pn-border); border-radius:var(--pn-radius); background:var(--pn-bg); transition:border-color .15s, background .15s; overflow:hidden; position:relative; cursor:pointer; }
		.pn-pb-pic:hover { border-color:var(--pn-primary); background:#f0fdfa; }
		.pn-pb-pic--has { padding:0; border-style:solid; border-color:var(--pn-border); background:#0f172a; }
		.pn-pb-pic__empty { padding:48px 16px; text-align:center; color:var(--pn-muted); font-size:12px; }
		.pn-pb-pic__empty .dashicons { color:var(--pn-primary); display:block; margin:0 auto 10px; width:36px; height:36px; font-size:36px; }
		.pn-pb-pic__img { width:100%; height:auto; max-height:280px; object-fit:contain; display:block; }
		.pn-pb-pic__img--mobile { max-height:240px; max-width:200px; margin:0 auto; }
		.pn-pb-pic__overlay { position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,.75), rgba(0,0,0,.15) 60%, rgba(0,0,0,0)); display:flex; align-items:flex-end; justify-content:center; gap:8px; padding:14px; opacity:0; transition:opacity .15s; }
		.pn-pb-pic--has:hover .pn-pb-pic__overlay { opacity:1; }
		.pn-pb-pic__btn { background:rgba(255,255,255,.95); border:0; padding:7px 16px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; color:var(--pn-text); }
		.pn-pb-pic__btn:hover { background:#fff; }
		.pn-pb-pic__btn--danger { color:#dc2626; }

		/* ───── Coupon ticket ───── */
		.pn-pb-ticket { position:relative; background:linear-gradient(135deg,#f0fdfa 0%,#fef3c7 100%); border:2px dashed var(--pn-primary); border-radius:12px; padding:20px 16px; text-align:center; overflow:hidden; }
		.pn-pb-ticket::before, .pn-pb-ticket::after { content:''; position:absolute; width:18px; height:18px; background:var(--pn-card); border-radius:50%; top:50%; transform:translateY(-50%); }
		.pn-pb-ticket::before { left:-10px; }
		.pn-pb-ticket::after { right:-10px; }
		.pn-pb-ticket__label { font-size:10px; text-transform:uppercase; letter-spacing:.1em; color:var(--pn-muted); margin-bottom:8px; }
		.pn-pb-ticket__input { width:100%; border:0; background:transparent; text-align:center; font-family:ui-monospace,SFMono-Regular,Menlo,monospace; font-size:22px; font-weight:800; letter-spacing:.15em; color:var(--pn-primary-dark); text-transform:uppercase; padding:0; }
		.pn-pb-ticket__input:focus { outline:none; }
		.pn-pb-ticket__input::placeholder { color:rgba(11,136,148,.35); font-weight:400; letter-spacing:.05em; }

		/* ───── Date with countdown ───── */
		.pn-pb-date-wrap { position:relative; }
		.pn-pb-countdown { display:flex; align-items:center; justify-content:space-between; padding:10px 12px; background:var(--pn-bg); border:1px solid var(--pn-border); border-radius:8px; font-size:12px; margin-top:8px; }
		.pn-pb-countdown__label { color:var(--pn-muted); }
		.pn-pb-countdown__val { font-weight:700; color:var(--pn-text); }
		.pn-pb-countdown--warn .pn-pb-countdown__val { color:#d97706; }
		.pn-pb-countdown--err .pn-pb-countdown__val { color:#dc2626; }

		/* ───── Product list — visual grid ───── */
		.pn-pb-search-wrap { position:relative; margin-bottom:14px; }
		.pn-pb-search-wrap .dashicons { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--pn-muted); pointer-events:none; }
		.pn-pb-search { padding-left:34px !important; }
		.pn-pb-search__results { position:absolute; top:100%; left:0; right:0; max-height:280px; overflow-y:auto; background:#fff; border:1px solid var(--pn-border); border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,.10); z-index:100; margin-top:4px; display:none; }
		.pn-pb-search__results.show { display:block; }
		.pn-pb-search__row { display:flex; align-items:center; gap:10px; padding:10px 12px; cursor:pointer; border-bottom:1px solid #f1f5f9; font-size:12px; }
		.pn-pb-search__row:hover { background:#f0fdfa; }
		.pn-pb-search__row:last-child { border-bottom:0; }
		.pn-pb-search__row img { width:36px; height:36px; object-fit:cover; border-radius:4px; flex-shrink:0; }
		.pn-pb-search__none { padding:14px; color:var(--pn-muted); font-size:12px; text-align:center; }

		.pn-pb-products { display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:10px; min-height:80px; }
		.pn-pb-product { display:flex; align-items:center; gap:10px; padding:8px; background:#fff; border:1px solid var(--pn-border); border-radius:10px; cursor:grab; transition:border-color .15s, box-shadow .15s; }
		.pn-pb-product:hover { border-color:var(--pn-primary); box-shadow:0 2px 8px rgba(0,0,0,.06); }
		.pn-pb-product.pn-pb-dragging { opacity:.4; }
		.pn-pb-product__handle { color:var(--pn-muted); font-size:14px; padding:0 4px; cursor:grab; user-select:none; flex-shrink:0; }
		.pn-pb-product__thumb { width:48px; height:48px; border-radius:6px; background:#f1f5f9; flex-shrink:0; overflow:hidden; }
		.pn-pb-product__thumb img { width:100%; height:100%; object-fit:cover; display:block; }
		.pn-pb-product__info { flex:1; min-width:0; }
		.pn-pb-product__name { font-size:12px; font-weight:600; color:var(--pn-text); line-height:1.3; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
		.pn-pb-product__sku { font-size:10px; color:var(--pn-muted); font-family:ui-monospace,monospace; margin-top:2px; }
		.pn-pb-product__remove { background:transparent; border:0; color:#94a3b8; cursor:pointer; padding:6px 8px; font-size:14px; border-radius:6px; flex-shrink:0; transition:all .15s; }
		.pn-pb-product__remove:hover { background:#fee2e2; color:#dc2626; }
		.pn-pb-products__empty { grid-column:1/-1; padding:32px 16px; text-align:center; color:var(--pn-muted); font-size:13px; background:var(--pn-bg); border:2px dashed var(--pn-border); border-radius:10px; }
		.pn-pb-products__empty .dashicons { display:block; margin:0 auto 8px; width:32px; height:32px; font-size:32px; color:var(--pn-border); }
	</style>

	<div class="pn-pb" data-pb-root data-rest-url="<?php echo esc_url( rest_url( 'wc/v3/products' ) ); ?>" data-rest-nonce="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>">

		<!-- ═══════════════════════ HEADER DASHBOARD ═══════════════════════ -->
		<div class="pn-pb-header">
			<div class="pn-pb-header__brand">
				<span class="pn-pb-header__icon"><span class="dashicons dashicons-megaphone"></span></span>
				<div class="pn-pb-header__text">
					<h2><?php echo esc_html( get_the_title( $post ) ?: __( 'Nuova promozione', 'pharmanow' ) ); ?></h2>
					<p>
						<?php if ( $is_published ) : ?>
							<?php esc_html_e( 'Pubblicata', 'pharmanow' ); ?> · <a href="<?php echo esc_url( get_permalink( $post ) ); ?>" target="_blank" style="color:#fff; text-decoration:underline; opacity:.9"><?php esc_html_e( 'Vedi pagina pubblica ↗', 'pharmanow' ); ?></a>
						<?php else : ?>
							<?php esc_html_e( 'Bozza — non ancora pubblicata', 'pharmanow' ); ?>
						<?php endif; ?>
					</p>
				</div>
			</div>
			<div class="pn-pb-header__stats">
				<div class="pn-pb-stat">
					<span class="pn-pb-stat__num" data-stat-products><?php echo (int) count( $selected_products ); ?></span>
					<span class="pn-pb-stat__label"><?php esc_html_e( 'Prodotti', 'pharmanow' ); ?></span>
				</div>
				<div class="pn-pb-stat">
					<span class="pn-pb-stat__num" data-stat-coupon><?php echo $coupon ? esc_html( strtoupper( $coupon ) ) : '—'; ?></span>
					<span class="pn-pb-stat__label"><?php esc_html_e( 'Coupon', 'pharmanow' ); ?></span>
				</div>
				<div class="pn-pb-stat">
					<span class="pn-pb-stat__num <?php echo esc_attr( $end_date_class ); ?>" data-stat-expiry><?php echo esc_html( $end_date_label ); ?></span>
					<span class="pn-pb-stat__label"><?php esc_html_e( 'Scadenza', 'pharmanow' ); ?></span>
				</div>
			</div>
		</div>

		<!-- ═══════════════════════ BODY ═══════════════════════ -->
		<div class="pn-pb-body">

			<!-- ───── COLONNA MAIN ───── -->
			<div class="pn-pb__col">

				<!-- Card: Hero banner -->
				<div class="pn-pb-card">
					<h3 class="pn-pb-card__title"><span class="dashicons dashicons-format-image"></span> <?php esc_html_e( 'Hero banner pagina', 'pharmanow' ); ?></h3>

					<div class="pn-pb-hero-grid">
						<div>
							<div class="pn-pb-field">
								<label class="pn-pb-field__label"><?php esc_html_e( 'Desktop (≥ 768px)', 'pharmanow' ); ?> <span style="color:#dc2626">*</span></label>
								<div class="pn-pb-pic <?php echo $hero_d_url ? 'pn-pb-pic--has' : ''; ?>" data-pn-pb-media data-target="desktop">
									<?php if ( $hero_d_url ) : ?>
										<img src="<?php echo esc_url( $hero_d_url ); ?>" alt="" class="pn-pb-pic__img">
										<div class="pn-pb-pic__overlay">
											<button type="button" class="pn-pb-pic__btn" data-pick><?php esc_html_e( 'Cambia', 'pharmanow' ); ?></button>
											<button type="button" class="pn-pb-pic__btn pn-pb-pic__btn--danger" data-clear><?php esc_html_e( 'Rimuovi', 'pharmanow' ); ?></button>
										</div>
									<?php else : ?>
										<div class="pn-pb-pic__empty" data-pick>
											<span class="dashicons dashicons-cloud-upload"></span>
											<strong><?php esc_html_e( 'Carica hero desktop', 'pharmanow' ); ?></strong><br>
											<?php esc_html_e( 'consigliato 1920×500px', 'pharmanow' ); ?>
										</div>
									<?php endif; ?>
									<input type="hidden" name="pn_promo_hero_desktop_id" value="<?php echo esc_attr( (string) $hero_d_id ); ?>" data-input>
									<input type="hidden" name="pn_promo_hero_desktop" value="<?php echo esc_attr( $hero_d ); ?>" data-input-url>
								</div>
							</div>
						</div>
						<div>
							<div class="pn-pb-field">
								<label class="pn-pb-field__label"><?php esc_html_e( 'Mobile', 'pharmanow' ); ?></label>
								<div class="pn-pb-pic <?php echo $hero_m_url ? 'pn-pb-pic--has' : ''; ?>" data-pn-pb-media data-target="mobile">
									<?php if ( $hero_m_url ) : ?>
										<img src="<?php echo esc_url( $hero_m_url ); ?>" alt="" class="pn-pb-pic__img pn-pb-pic__img--mobile">
										<div class="pn-pb-pic__overlay">
											<button type="button" class="pn-pb-pic__btn" data-pick><?php esc_html_e( 'Cambia', 'pharmanow' ); ?></button>
											<button type="button" class="pn-pb-pic__btn pn-pb-pic__btn--danger" data-clear><?php esc_html_e( 'Rimuovi', 'pharmanow' ); ?></button>
										</div>
									<?php else : ?>
										<div class="pn-pb-pic__empty" data-pick>
											<span class="dashicons dashicons-smartphone"></span>
											<strong><?php esc_html_e( 'Mobile (opt.)', 'pharmanow' ); ?></strong><br>
											<?php esc_html_e( 'vuoto = desktop', 'pharmanow' ); ?>
										</div>
									<?php endif; ?>
									<input type="hidden" name="pn_promo_hero_mobile_id" value="<?php echo esc_attr( (string) $hero_m_id ); ?>" data-input>
									<input type="hidden" name="pn_promo_hero_mobile" value="<?php echo esc_attr( $hero_m ); ?>" data-input-url>
								</div>
							</div>
						</div>
					</div>

					<div class="pn-pb-field" style="margin-top:14px">
						<label for="pn_promo_hero_alt" class="pn-pb-field__label"><?php esc_html_e( 'Testo alternativo (alt)', 'pharmanow' ); ?></label>
						<input type="text" id="pn_promo_hero_alt" name="pn_promo_hero_alt" value="<?php echo esc_attr( $hero_alt ); ?>" class="pn-pb-input" placeholder="<?php esc_attr_e( 'Es. La Roche-Posay — skincare di precisione', 'pharmanow' ); ?>">
						<p class="pn-pb-field__help"><?php esc_html_e( 'Descrive l\'immagine per accessibilità e SEO. Se vuoto, usa il titolo.', 'pharmanow' ); ?></p>
					</div>
				</div>

				<!-- Card: Prodotti in vetrina -->
				<div class="pn-pb-card">
					<h3 class="pn-pb-card__title">
						<span class="dashicons dashicons-products"></span>
						<?php esc_html_e( 'Prodotti in vetrina', 'pharmanow' ); ?>
						<span class="pn-pb-card__title-count" data-count-badge><?php echo (int) count( $selected_products ); ?></span>
					</h3>

					<div class="pn-pb-search-wrap">
						<span class="dashicons dashicons-search"></span>
						<input type="text" placeholder="<?php esc_attr_e( 'Cerca prodotto per nome o SKU…', 'pharmanow' ); ?>" class="pn-pb-input pn-pb-search" data-pb-search>
						<div class="pn-pb-search__results" data-pb-search-results></div>
					</div>

					<div class="pn-pb-products" data-pb-products>
						<?php if ( empty( $selected_products ) ) : ?>
							<div class="pn-pb-products__empty" data-empty>
								<span class="dashicons dashicons-screenoptions"></span>
								<?php esc_html_e( 'Nessun prodotto selezionato. Cerca sopra per aggiungerne.', 'pharmanow' ); ?>
							</div>
						<?php endif; ?>
						<?php foreach ( $selected_products as $sp ) : ?>
							<div class="pn-pb-product" data-id="<?php echo (int) $sp['id']; ?>" draggable="true">
								<span class="pn-pb-product__handle">⋮⋮</span>
								<div class="pn-pb-product__thumb">
									<?php if ( $sp['thumb'] ) : ?><img src="<?php echo esc_url( $sp['thumb'] ); ?>" alt=""><?php endif; ?>
								</div>
								<div class="pn-pb-product__info">
									<div class="pn-pb-product__name"><?php echo esc_html( $sp['title'] ); ?></div>
									<?php if ( $sp['sku'] ) : ?><div class="pn-pb-product__sku">SKU: <?php echo esc_html( $sp['sku'] ); ?></div><?php endif; ?>
								</div>
								<button type="button" class="pn-pb-product__remove" data-remove title="<?php esc_attr_e( 'Rimuovi', 'pharmanow' ); ?>">✕</button>
							</div>
						<?php endforeach; ?>
					</div>
					<input type="hidden" name="pn_promo_product_ids" value="<?php echo esc_attr( $prod_ids ); ?>" data-pb-ids>

					<p class="pn-pb-field__help" style="margin-top:12px"><?php esc_html_e( 'Trascina i prodotti per riordinarli. L\'ordine qui è quello mostrato sulla pagina promo.', 'pharmanow' ); ?></p>

					<div class="pn-pb-field" style="margin-top:18px; padding-top:14px; border-top:1px solid var(--pn-border)">
						<label for="pn_promo_category_id" class="pn-pb-field__label"><?php esc_html_e( 'Aggiungi anche tutta una categoria', 'pharmanow' ); ?></label>
						<select id="pn_promo_category_id" name="pn_promo_category_id" class="pn-pb-select">
							<option value="0"><?php esc_html_e( '— Nessuna —', 'pharmanow' ); ?></option>
							<?php if ( ! is_wp_error( $cats ) && $cats ) : foreach ( $cats as $id => $name ) : ?>
								<option value="<?php echo (int) $id; ?>" <?php selected( $cat_id, (int) $id ); ?>>
									<?php echo esc_html( $name ); ?>
								</option>
							<?php endforeach; endif; ?>
						</select>
						<p class="pn-pb-field__help"><?php esc_html_e( 'I prodotti della categoria appariranno dopo quelli selezionati manualmente.', 'pharmanow' ); ?></p>
					</div>
				</div>
			</div>

			<!-- ───── SIDEBAR (coupon + scadenza) ───── -->
			<div class="pn-pb__col">

				<!-- Card: Coupon (ticket) -->
				<div class="pn-pb-card">
					<h3 class="pn-pb-card__title"><span class="dashicons dashicons-tickets-alt"></span> <?php esc_html_e( 'Codice coupon', 'pharmanow' ); ?></h3>

					<div class="pn-pb-ticket">
						<div class="pn-pb-ticket__label"><?php esc_html_e( 'Codice sconto', 'pharmanow' ); ?></div>
						<input type="text" id="pn_promo_coupon" name="pn_promo_coupon" value="<?php echo esc_attr( $coupon ); ?>" class="pn-pb-ticket__input" placeholder="ES. BENVENUTO10" data-coupon-input>
					</div>
					<p class="pn-pb-field__help" style="margin-top:10px"><?php esc_html_e( 'Mostrato in box copia-incolla nella pagina promo. Vuoto = niente box coupon.', 'pharmanow' ); ?></p>
				</div>

				<!-- Card: Scadenza -->
				<div class="pn-pb-card">
					<h3 class="pn-pb-card__title"><span class="dashicons dashicons-calendar-alt"></span> <?php esc_html_e( 'Scadenza', 'pharmanow' ); ?></h3>

					<div class="pn-pb-field">
						<label for="pn_promo_end_date" class="pn-pb-field__label"><?php esc_html_e( 'Data fine promozione', 'pharmanow' ); ?></label>
						<div class="pn-pb-date-wrap">
							<input type="date" id="pn_promo_end_date" name="pn_promo_end_date" value="<?php echo esc_attr( $end_date ); ?>" class="pn-pb-input" data-date-input>
						</div>
						<div class="pn-pb-countdown" data-countdown style="<?php echo $end_date ? '' : 'display:none'; ?>">
							<span class="pn-pb-countdown__label"><?php esc_html_e( 'Stato:', 'pharmanow' ); ?></span>
							<span class="pn-pb-countdown__val" data-countdown-val><?php echo esc_html( $end_date_label ); ?></span>
						</div>
						<p class="pn-pb-field__help"><?php esc_html_e( 'Vuoto = nessuna scadenza. Dopo questa data appare "Promozione terminata".', 'pharmanow' ); ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script>
	(function(){
		var root = document.querySelector('[data-pb-root]');
		if (!root) return;

		// ───── Hero image picker ─────
		root.querySelectorAll('[data-pn-pb-media]').forEach(function(box){
			function attach(){
				var picks = box.querySelectorAll('[data-pick]');
				var clear = box.querySelector('[data-clear]');
				var input = box.querySelector('[data-input]');
				var inputUrl = box.querySelector('[data-input-url]');
				var target = box.getAttribute('data-target');
				var frame;
				picks.forEach(function(p){
					p.addEventListener('click', function(e){
						e.preventDefault();
						if (frame) { frame.open(); return; }
						frame = wp.media({ title: '<?php echo esc_js( __( 'Scegli immagine', 'pharmanow' ) ); ?>', button: { text: '<?php echo esc_js( __( 'Usa questa', 'pharmanow' ) ); ?>' }, library: { type: 'image' }, multiple: false });
						frame.on('select', function(){
							var att = frame.state().get('selection').first().toJSON();
							var url = (att.sizes && (att.sizes.medium_large || att.sizes.large || att.sizes.medium)) ? (att.sizes.medium_large || att.sizes.large || att.sizes.medium).url : att.url;
							box.classList.add('pn-pb-pic--has');
							box.innerHTML = '<img src="'+url+'" alt="" class="pn-pb-pic__img'+(target==="mobile"?" pn-pb-pic__img--mobile":"")+'">'
								+ '<div class="pn-pb-pic__overlay">'
								+ '<button type="button" class="pn-pb-pic__btn" data-pick>Cambia</button>'
								+ '<button type="button" class="pn-pb-pic__btn pn-pb-pic__btn--danger" data-clear>Rimuovi</button>'
								+ '</div>'
								+ '<input type="hidden" name="pn_promo_hero_'+target+'_id" value="'+att.id+'" data-input>'
								+ '<input type="hidden" name="pn_promo_hero_'+target+'" value="" data-input-url>';
							attach();
						});
						frame.open();
					});
				});
				if (clear) {
					clear.addEventListener('click', function(e){
						e.preventDefault();
						box.classList.remove('pn-pb-pic--has');
						var lblD = '<?php echo esc_js( __( 'Carica hero desktop', 'pharmanow' ) ); ?>';
						var lblM = '<?php echo esc_js( __( 'Carica versione mobile (opzionale)', 'pharmanow' ) ); ?>';
						var hintD = '<?php echo esc_js( __( 'consigliato 1920×500px', 'pharmanow' ) ); ?>';
						var hintM = '<?php echo esc_js( __( 'vuoto = usa desktop', 'pharmanow' ) ); ?>';
						var icon = (target === 'desktop') ? 'cloud-upload' : 'smartphone';
						var lbl = (target === 'desktop') ? lblD : lblM;
						var hint = (target === 'desktop') ? hintD : hintM;
						box.innerHTML = '<div class="pn-pb-pic__empty" data-pick>'
							+ '<span class="dashicons dashicons-'+icon+'"></span>'
							+ '<strong>'+lbl+'</strong><br>'+hint
							+ '</div>'
							+ '<input type="hidden" name="pn_promo_hero_'+target+'_id" value="" data-input>'
							+ '<input type="hidden" name="pn_promo_hero_'+target+'" value="" data-input-url>';
						attach();
					});
				}
			}
			attach();
		});

		// ───── Product search & list ─────
		var searchInput = root.querySelector('[data-pb-search]');
		var resultsBox  = root.querySelector('[data-pb-search-results]');
		var listBox     = root.querySelector('[data-pb-products]');
		var idsInput    = root.querySelector('[data-pb-ids]');
		var emptyMsg    = listBox.querySelector('[data-empty]');
		var restUrl     = root.getAttribute('data-rest-url');
		var restNonce   = root.getAttribute('data-rest-nonce');
		var debounceT;

		var countBadge = root.querySelector('[data-count-badge]');
		var statProducts = root.querySelector('[data-stat-products]');

		function syncIds(){
			var ids = Array.from(listBox.querySelectorAll('[data-id]')).map(function(el){ return el.getAttribute('data-id'); });
			idsInput.value = ids.join(',');
			if (emptyMsg) emptyMsg.style.display = ids.length ? 'none' : 'block';
			if (countBadge) countBadge.textContent = ids.length;
			if (statProducts) statProducts.textContent = ids.length;
		}

		function addProduct(prod){
			if (listBox.querySelector('[data-id="'+prod.id+'"]')) return;
			if (emptyMsg) emptyMsg.style.display = 'none';
			var thumb = (prod.images && prod.images[0] && prod.images[0].src) ? prod.images[0].src : '';
			var sku   = prod.sku || '';
			var div = document.createElement('div');
			div.className = 'pn-pb-product';
			div.setAttribute('data-id', prod.id);
			div.draggable = true;
			div.innerHTML = '<span class="pn-pb-product__handle">⋮⋮</span>'
				+ '<div class="pn-pb-product__thumb">'+(thumb?'<img src="'+thumb+'" alt="">':'')+'</div>'
				+ '<div class="pn-pb-product__info">'
				+ '<div class="pn-pb-product__name">'+escapeHtml(prod.name)+'</div>'
				+ (sku?'<div class="pn-pb-product__sku">SKU: '+escapeHtml(sku)+'</div>':'')
				+ '</div>'
				+ '<button type="button" class="pn-pb-product__remove" data-remove>✕</button>';
			listBox.appendChild(div);
			attachRowHandlers(div);
			syncIds();
		}

		function attachRowHandlers(row){
			var btn = row.querySelector('[data-remove]');
			if (btn) btn.addEventListener('click', function(){ row.remove(); syncIds(); });
			// Drag & drop reorder
			row.addEventListener('dragstart', function(){ row.style.opacity = '0.4'; row.classList.add('pn-pb-dragging'); });
			row.addEventListener('dragend',   function(){ row.style.opacity = '1'; row.classList.remove('pn-pb-dragging'); });
		}

		// Drop target listeners
		listBox.addEventListener('dragover', function(e){
			e.preventDefault();
			var dragging = listBox.querySelector('.pn-pb-dragging');
			if (!dragging) return;
			var after = getDragAfterElement(listBox, e.clientY);
			if (after == null) listBox.appendChild(dragging);
			else listBox.insertBefore(dragging, after);
		});
		listBox.addEventListener('drop', function(){ syncIds(); });

		function getDragAfterElement(container, y){
			var els = Array.from(container.querySelectorAll('.pn-pb-product:not(.pn-pb-dragging)'));
			return els.reduce(function(closest, child){
				var box = child.getBoundingClientRect();
				var offset = y - box.top - box.height / 2;
				if (offset < 0 && offset > closest.offset) return { offset: offset, element: child };
				return closest;
			}, { offset: Number.NEGATIVE_INFINITY }).element;
		}

		// Make existing rows draggable
		listBox.querySelectorAll('.pn-pb-product').forEach(function(r){ r.draggable = true; attachRowHandlers(r); });

		// Search via WC REST
		searchInput.addEventListener('input', function(){
			clearTimeout(debounceT);
			var q = searchInput.value.trim();
			if (q.length < 2) { resultsBox.classList.remove('show'); resultsBox.innerHTML = ''; return; }
			debounceT = setTimeout(function(){
				fetch(restUrl + '?per_page=10&search=' + encodeURIComponent(q), {
					headers: { 'X-WP-Nonce': restNonce }
				}).then(function(r){ return r.json(); }).then(function(data){
					if (!Array.isArray(data) || !data.length) {
						resultsBox.innerHTML = '<div class="pn-pb-search__none"><?php echo esc_js( __( 'Nessun prodotto trovato', 'pharmanow' ) ); ?></div>';
						resultsBox.classList.add('show');
						return;
					}
					resultsBox.innerHTML = data.map(function(p){
						var thumb = (p.images && p.images[0] && p.images[0].src) ? '<img src="'+p.images[0].src+'" alt="">' : '<div style="width:32px;height:32px;background:#f1f1f1;border-radius:4px;flex-shrink:0"></div>';
						return '<div class="pn-pb-search__row" data-add=\''+escapeAttr(JSON.stringify(p))+'\'>'+thumb+'<div><strong>'+escapeHtml(p.name)+'</strong>'+(p.sku?'<div style="font-size:10px;color:#64748b">SKU: '+escapeHtml(p.sku)+'</div>':'')+'</div></div>';
					}).join('');
					resultsBox.classList.add('show');
					resultsBox.querySelectorAll('[data-add]').forEach(function(row){
						row.addEventListener('click', function(){
							addProduct(JSON.parse(row.getAttribute('data-add')));
							searchInput.value = '';
							resultsBox.classList.remove('show');
							resultsBox.innerHTML = '';
						});
					});
				}).catch(function(){ resultsBox.innerHTML = '<div class="pn-pb-search__none"><?php echo esc_js( __( 'Errore di rete', 'pharmanow' ) ); ?></div>'; resultsBox.classList.add('show'); });
			}, 250);
		});

		document.addEventListener('click', function(e){
			if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
				resultsBox.classList.remove('show');
			}
		});

		function escapeHtml(s){ return (s||'').replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
		function escapeAttr(s){ return (s||'').replace(/'/g, '&#39;'); }

		// ───── Live header stats: coupon ─────
		var couponInput = root.querySelector('[data-coupon-input]');
		var statCoupon  = root.querySelector('[data-stat-coupon]');
		if (couponInput && statCoupon) {
			couponInput.addEventListener('input', function(){
				var v = (couponInput.value || '').trim().toUpperCase();
				statCoupon.textContent = v || '—';
			});
		}

		// ───── Live header stats: scadenza countdown ─────
		var dateInput   = root.querySelector('[data-date-input]');
		var countdown   = root.querySelector('[data-countdown]');
		var countVal    = root.querySelector('[data-countdown-val]');
		var statExpiry  = root.querySelector('[data-stat-expiry]');
		if (dateInput) {
			dateInput.addEventListener('input', function(){
				var v = dateInput.value;
				if (!v) {
					if (countdown) countdown.style.display = 'none';
					if (statExpiry) { statExpiry.textContent = '∞'; statExpiry.className = 'pn-pb-stat__num'; }
					return;
				}
				var d = new Date(v + 'T00:00:00');
				var now = new Date(); now.setHours(0,0,0,0);
				var diff = Math.floor((d - now) / 86400000);
				var label, cls;
				if (diff < 0)       { label = 'Scaduta';  cls = 'pn-pb-stat__num--err';  }
				else if (diff === 0){ label = 'Oggi';     cls = 'pn-pb-stat__num--warn'; }
				else if (diff <= 7) { label = diff + (diff===1?' giorno':' giorni'); cls = 'pn-pb-stat__num--warn'; }
				else                { label = diff + ' giorni'; cls = ''; }
				if (countdown) { countdown.style.display = ''; countdown.className = 'pn-pb-countdown' + (cls?' '+cls.replace('pn-pb-stat__num','pn-pb-countdown'):''); }
				if (countVal)  countVal.textContent = label;
				if (statExpiry){ statExpiry.textContent = label; statExpiry.className = 'pn-pb-stat__num' + (cls?' '+cls:''); }
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
	'save_post_pn_promo',
	function ( $post_id ) {
		if ( ! isset( $_POST['pn_promo_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['pn_promo_nonce'] ), 'pn_promo_save' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$map = array(
			'_pn_promo_hero_desktop_id' => array( 'pn_promo_hero_desktop_id', 'absint' ),
			'_pn_promo_hero_mobile_id'  => array( 'pn_promo_hero_mobile_id', 'absint' ),
			'_pn_promo_hero_desktop'    => array( 'pn_promo_hero_desktop', 'esc_url_raw' ),
			'_pn_promo_hero_mobile'     => array( 'pn_promo_hero_mobile', 'esc_url_raw' ),
			'_pn_promo_hero_alt'        => array( 'pn_promo_hero_alt', 'sanitize_text_field' ),
			'_pn_promo_coupon'          => array( 'pn_promo_coupon', 'sanitize_text_field' ),
			'_pn_promo_end_date'        => array( 'pn_promo_end_date', 'sanitize_text_field' ),
			'_pn_promo_product_ids'     => array( 'pn_promo_product_ids', 'pn_promo_sanitize_ids_csv' ),
			'_pn_promo_category_id'     => array( 'pn_promo_category_id', 'absint' ),
		);
		foreach ( $map as $meta_key => $cfg ) {
			[ $field, $sanitizer ] = $cfg;
			if ( ! isset( $_POST[ $field ] ) ) {
				continue;
			}
			$raw = wp_unslash( $_POST[ $field ] );
			$val = call_user_func( $sanitizer, $raw );
			if ( '' === $val || 0 === $val || '0' === (string) $val ) {
				delete_post_meta( $post_id, $meta_key );
			} else {
				update_post_meta( $post_id, $meta_key, $val );
			}
		}

		// Coupon UPPERCASE.
		$coupon = (string) get_post_meta( $post_id, '_pn_promo_coupon', true );
		if ( $coupon ) {
			update_post_meta( $post_id, '_pn_promo_coupon', strtoupper( $coupon ) );
		}
	}
);

/**
 * Sanitizza CSV di interi.
 */
function pn_promo_sanitize_ids_csv( $raw ): string {
	$parts = preg_split( '/[\s,]+/', (string) $raw );
	$ids   = array();
	foreach ( (array) $parts as $p ) {
		$n = absint( $p );
		if ( $n > 0 ) {
			$ids[] = $n;
		}
	}
	return implode( ',', array_unique( $ids ) );
}

/**
 * URL hero (desktop|mobile) — preferisce attachment ID, fallback URL legacy.
 */
function pn_promo_hero_url( int $promo_id, string $variant = 'desktop' ): string {
	$id_key  = '_pn_promo_hero_' . $variant . '_id';
	$url_key = '_pn_promo_hero_' . $variant;
	$att_id  = (int) get_post_meta( $promo_id, $id_key, true );
	if ( $att_id ) {
		$url = wp_get_attachment_image_url( $att_id, 'full' );
		if ( $url ) {
			return $url;
		}
	}
	return (string) get_post_meta( $promo_id, $url_key, true );
}

/**
 * Risolve i prodotti per una promo: manuali (in ordine) + categoria (dedupe).
 *
 * @return WC_Product[]
 */
function pn_promo_get_products( int $promo_id ): array {
	$ids_csv = (string) get_post_meta( $promo_id, '_pn_promo_product_ids', true );
	$cat_id  = (int) get_post_meta( $promo_id, '_pn_promo_category_id', true );

	$out  = array();
	$seen = array();

	if ( $ids_csv ) {
		$ids = array_filter( array_map( 'absint', explode( ',', $ids_csv ) ) );
		foreach ( $ids as $pid ) {
			if ( isset( $seen[ $pid ] ) ) {
				continue;
			}
			$p = function_exists( 'wc_get_product' ) ? wc_get_product( $pid ) : null;
			if ( $p && 'publish' === $p->get_status() ) {
				$out[]        = $p;
				$seen[ $pid ] = true;
			}
		}
	}

	if ( $cat_id > 0 ) {
		$q = new WP_Query(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => 48,
				'no_found_rows'  => true,
				'tax_query'      => array(
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'term_id',
						'terms'    => array( $cat_id ),
					),
				),
			)
		);
		foreach ( (array) $q->posts as $post ) {
			$pid = (int) $post->ID;
			if ( isset( $seen[ $pid ] ) ) {
				continue;
			}
			$p = wc_get_product( $pid );
			if ( $p ) {
				$out[]        = $p;
				$seen[ $pid ] = true;
			}
		}
		wp_reset_postdata();
	}

	return $out;
}

/**
 * Coupon NEXI 5€ SMS: logica condizionale di spedizione gratuita.
 *
 * - Sotto i 29,90 € di sottototale: il coupon abilita la spedizione gratuita
 *   oltre ai 5 € di sconto.
 * - Sopra i 29,90 €: il coupon lascia solo i 5 € di sconto (la spedizione
 *   gratuita è già garantita dalla soglia del negozio).
 */
function pn_is_nexi_5e_sms_coupon( $coupon ): bool {
	if ( ! is_a( $coupon, 'WC_Coupon' ) ) {
		return false;
	}
	return 'NEXI 5€ SMS (campagna investimento 1500€)  -  5K CODICI - 01/07/2026 al 31/07/2026 (scadenza codici 31/08/2026)' === $coupon->get_description();
}

function pn_get_cart_subtotal_for_coupon(): float {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return 0.0;
	}
	return (float) WC()->cart->get_subtotal();
}

add_filter(
	'woocommerce_coupon_get_free_shipping',
	function ( $free_shipping, $coupon ) {
		$coupon_obj = is_array( $coupon ) && isset( $coupon[0] ) ? $coupon[0] : $coupon;
		if ( ! pn_is_nexi_5e_sms_coupon( $coupon_obj ) ) {
			return $free_shipping;
		}
		return pn_get_cart_subtotal_for_coupon() < 29.90;
	},
	10,
	2
);
