<?php
/**
 * 404 Page Template
 *
 * @package BizStudio
 */

get_header();
?>

<main class="biz-main">
	<div class="biz-container">
		<div class="biz-404" data-reveal>
			<div class="biz-404__code">404</div>
			<h1 class="biz-404__title"><?php esc_html_e( 'Pagina non trovata', 'bizstudio' ); ?></h1>
			<p class="biz-404__text">
				<?php esc_html_e( 'La pagina che cerchi non esiste o e stata spostata.', 'bizstudio' ); ?>
			</p>
			<div class="biz-404__actions">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="biz-btn biz-btn--primary">
					<?php esc_html_e( 'Torna alla Home', 'bizstudio' ); ?>
				</a>
				<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="biz-btn biz-btn--outline">
						<?php esc_html_e( 'Vai allo Shop', 'bizstudio' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</main>

<?php
get_footer();
