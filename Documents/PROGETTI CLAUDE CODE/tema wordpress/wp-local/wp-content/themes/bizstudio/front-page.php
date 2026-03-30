<?php
/**
 * Homepage template
 *
 * @package BizStudio
 */

get_header();
?>

<main class="biz-main">
	<!-- Hero Section -->
	<section class="biz-hero" data-reveal>
		<div class="biz-container">
			<div class="biz-hero__content">
				<span class="biz-hero__tag"><?php esc_html_e( 'Nuova collezione', 'bizstudio' ); ?></span>
				<h1 class="biz-hero__title"><?php esc_html_e( 'Scopri il tuo stile', 'bizstudio' ); ?></h1>
				<p class="biz-hero__text"><?php esc_html_e( 'Prodotti selezionati con cura per chi cerca qualita e design.', 'bizstudio' ); ?></p>
				<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '/shop/' ); ?>" class="biz-btn biz-btn--primary biz-btn--lg">
					<?php esc_html_e( 'Esplora lo Shop', 'bizstudio' ); ?>
					<?php echo bizstudio_icon( 'arrow-r' ); ?>
				</a>
			</div>
		</div>
	</section>

	<?php if ( function_exists( 'WC' ) ) : ?>

	<!-- Categories -->
	<?php
	$categories = get_terms( [
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'exclude'    => get_option( 'default_product_cat' ),
		'number'     => 4,
	] );
	if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) :
	?>
	<section class="biz-section biz-categories" data-reveal>
		<div class="biz-container">
			<div class="biz-section__header">
				<h2 class="biz-section__title"><?php esc_html_e( 'Categorie', 'bizstudio' ); ?></h2>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="biz-section__link">
					<?php esc_html_e( 'Vedi tutto', 'bizstudio' ); ?> <?php echo bizstudio_icon( 'arrow-r' ); ?>
				</a>
			</div>
			<div class="biz-categories__grid">
				<?php foreach ( $categories as $cat ) : ?>
					<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="biz-category-card" data-reveal-item>
						<div class="biz-category-card__icon">
							<?php echo esc_html( mb_substr( $cat->name, 0, 1 ) ); ?>
						</div>
						<h3 class="biz-category-card__name"><?php echo esc_html( $cat->name ); ?></h3>
						<span class="biz-category-card__count">
							<?php echo esc_html( $cat->count ); ?> <?php esc_html_e( 'prodotti', 'bizstudio' ); ?>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<!-- Featured Products -->
	<?php
	$featured = wc_get_products( [
		'featured' => true,
		'limit'    => 8,
		'status'   => 'publish',
		'orderby'  => 'date',
		'order'    => 'DESC',
	] );
	if ( ! empty( $featured ) ) :
	?>
	<section class="biz-section biz-featured" data-reveal>
		<div class="biz-container">
			<div class="biz-section__header">
				<h2 class="biz-section__title"><?php esc_html_e( 'Prodotti in evidenza', 'bizstudio' ); ?></h2>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="biz-section__link">
					<?php esc_html_e( 'Vedi tutto', 'bizstudio' ); ?> <?php echo bizstudio_icon( 'arrow-r' ); ?>
				</a>
			</div>
			<div class="biz-product-grid biz-product-grid--4">
				<?php foreach ( $featured as $product ) :
					$GLOBALS['product'] = $product;
					setup_postdata( $product->get_id() );
					get_template_part( 'template-parts/product/card-product' );
				endforeach;
				wp_reset_postdata(); ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<!-- On Sale Products -->
	<?php
	$on_sale = wc_get_products( [
		'on_sale' => true,
		'limit'   => 4,
		'status'  => 'publish',
	] );
	if ( ! empty( $on_sale ) ) :
	?>
	<section class="biz-section biz-sale" data-reveal>
		<div class="biz-container">
			<div class="biz-section__header">
				<h2 class="biz-section__title"><?php esc_html_e( 'In offerta', 'bizstudio' ); ?></h2>
			</div>
			<div class="biz-product-grid biz-product-grid--4">
				<?php foreach ( $on_sale as $product ) :
					$GLOBALS['product'] = $product;
					setup_postdata( $product->get_id() );
					get_template_part( 'template-parts/product/card-product' );
				endforeach;
				wp_reset_postdata(); ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<?php endif; ?>
</main>

<?php
get_footer();
