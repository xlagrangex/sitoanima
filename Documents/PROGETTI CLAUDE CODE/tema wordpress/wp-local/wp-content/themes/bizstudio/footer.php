<footer class="biz-footer">
	<div class="biz-container">
		<div class="biz-footer__grid">
			<!-- Col 1: About -->
			<div class="biz-footer__col">
				<h4 class="biz-footer__title"><?php bloginfo( 'name' ); ?></h4>
				<p class="biz-footer__text">
					<?php esc_html_e( 'Tema premium per WooCommerce. Design moderno, performance eccezionali.', 'bizstudio' ); ?>
				</p>
			</div>

			<!-- Widget areas -->
			<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
				<?php if ( is_active_sidebar( "footer-{$i}" ) ) : ?>
				<div class="biz-footer__col">
					<?php dynamic_sidebar( "footer-{$i}" ); ?>
				</div>
				<?php endif; ?>
			<?php endfor; ?>
		</div>

		<div class="biz-footer__bottom">
			<p class="biz-footer__copy">
				&copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>.
				<?php esc_html_e( 'Tutti i diritti riservati.', 'bizstudio' ); ?>
			</p>
			<?php
			wp_nav_menu( [
				'theme_location' => 'footer',
				'container'      => false,
				'menu_class'     => 'biz-footer__menu',
				'fallback_cb'    => false,
				'depth'          => 1,
			] );
			?>
		</div>
	</div>
</footer>

<?php get_template_part( 'template-parts/product/quick-view-modal' ); ?>
<?php wp_footer(); ?>
</body>
</html>
