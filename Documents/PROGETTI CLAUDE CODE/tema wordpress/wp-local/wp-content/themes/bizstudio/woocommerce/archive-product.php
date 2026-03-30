<?php
/**
 * WooCommerce Shop / Category Archive
 *
 * @package BizStudio
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<?php woocommerce_breadcrumb(); ?>

<main class="biz-main">
	<div class="biz-container">
		<div class="biz-shop-header" data-reveal>
			<?php if ( is_shop() ) : ?>
				<h1 class="biz-shop-header__title"><?php woocommerce_page_title(); ?></h1>
			<?php else : ?>
				<h1 class="biz-shop-header__title"><?php single_cat_title(); ?></h1>
				<?php if ( category_description() ) : ?>
					<div class="biz-shop-header__desc"><?php echo category_description(); ?></div>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<div class="biz-shop-layout">
			<!-- Sidebar -->
			<aside class="biz-shop-sidebar" id="biz-shop-sidebar">
				<div class="biz-shop-sidebar__header">
					<h3 class="biz-shop-sidebar__title"><?php esc_html_e( 'Filtri', 'bizstudio' ); ?></h3>
					<button class="biz-shop-sidebar__close" id="biz-filter-close">
						<?php echo bizstudio_icon( 'close' ); ?>
					</button>
				</div>

				<!-- Categories filter -->
				<?php
				$categories = get_terms( [
					'taxonomy'   => 'product_cat',
					'hide_empty' => true,
					'exclude'    => get_option( 'default_product_cat' ),
				] );
				if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) :
				?>
				<div class="biz-filter-group">
					<h4 class="biz-filter-group__title"><?php esc_html_e( 'Categorie', 'bizstudio' ); ?></h4>
					<ul class="biz-filter-group__list">
						<?php foreach ( $categories as $cat ) :
							$active = is_product_category( $cat->slug ) ? ' biz-filter-group__item--active' : '';
						?>
							<li class="biz-filter-group__item<?php echo $active; ?>">
								<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>">
									<?php echo esc_html( $cat->name ); ?>
									<span class="biz-filter-group__count"><?php echo esc_html( $cat->count ); ?></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
				<?php endif; ?>

				<!-- Price filter -->
				<div class="biz-filter-group">
					<h4 class="biz-filter-group__title"><?php esc_html_e( 'Prezzo', 'bizstudio' ); ?></h4>
					<div class="biz-price-filter">
						<div class="biz-price-filter__inputs">
							<input type="number" class="biz-price-filter__input" id="biz-price-min" placeholder="Min" min="0" step="1">
							<span class="biz-price-filter__sep">-</span>
							<input type="number" class="biz-price-filter__input" id="biz-price-max" placeholder="Max" min="0" step="1">
						</div>
						<button class="biz-btn biz-btn--primary biz-btn--sm biz-price-filter__apply" id="biz-price-apply">
							<?php esc_html_e( 'Filtra', 'bizstudio' ); ?>
						</button>
					</div>
				</div>

				<!-- Stock filter -->
				<div class="biz-filter-group">
					<h4 class="biz-filter-group__title"><?php esc_html_e( 'Disponibilita', 'bizstudio' ); ?></h4>
					<label class="biz-filter-check">
						<input type="checkbox" id="biz-filter-instock">
						<span><?php esc_html_e( 'Solo prodotti disponibili', 'bizstudio' ); ?></span>
					</label>
					<label class="biz-filter-check">
						<input type="checkbox" id="biz-filter-onsale">
						<span><?php esc_html_e( 'Solo in offerta', 'bizstudio' ); ?></span>
					</label>
				</div>
			</aside>

			<!-- Products -->
			<div class="biz-shop-content">
				<!-- Toolbar -->
				<div class="biz-shop-toolbar" data-reveal>
					<div class="biz-shop-toolbar__left">
						<button class="biz-shop-toolbar__filter-btn" id="biz-filter-toggle">
							<?php echo bizstudio_icon( 'menu' ); ?>
							<?php esc_html_e( 'Filtri', 'bizstudio' ); ?>
						</button>
						<span class="biz-shop-toolbar__count">
							<?php woocommerce_result_count(); ?>
						</span>
					</div>
					<div class="biz-shop-toolbar__right">
						<!-- View toggle -->
						<div class="biz-view-toggle">
							<button class="biz-view-toggle__btn biz-view-toggle__btn--active" data-view="grid" aria-label="<?php esc_attr_e( 'Vista griglia', 'bizstudio' ); ?>">
								<svg width="18" height="18" viewBox="0 0 18 18" fill="currentColor"><rect width="8" height="8"/><rect x="10" width="8" height="8"/><rect y="10" width="8" height="8"/><rect x="10" y="10" width="8" height="8"/></svg>
							</button>
							<button class="biz-view-toggle__btn" data-view="list" aria-label="<?php esc_attr_e( 'Vista lista', 'bizstudio' ); ?>">
								<svg width="18" height="18" viewBox="0 0 18 18" fill="currentColor"><rect width="18" height="3"/><rect y="5" width="18" height="3"/><rect y="10" width="18" height="3"/><rect y="15" width="18" height="3"/></svg>
							</button>
						</div>
						<?php woocommerce_catalog_ordering(); ?>
					</div>
				</div>

				<?php if ( woocommerce_product_loop() ) : ?>
					<?php woocommerce_product_loop_start(); ?>
						<?php while ( have_posts() ) : the_post(); ?>
							<?php wc_get_template_part( 'content', 'product' ); ?>
						<?php endwhile; ?>
					<?php woocommerce_product_loop_end(); ?>

					<?php woocommerce_pagination(); ?>
				<?php else : ?>
					<div class="biz-no-products" data-reveal>
						<h2><?php esc_html_e( 'Nessun prodotto trovato', 'bizstudio' ); ?></h2>
						<p><?php esc_html_e( 'Prova a cambiare i filtri o torna allo shop.', 'bizstudio' ); ?></p>
						<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="biz-btn biz-btn--primary">
							<?php esc_html_e( 'Torna allo Shop', 'bizstudio' ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</main>

<?php
get_footer();
