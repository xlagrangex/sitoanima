<?php
/**
 * Footer — Modern e-commerce footer
 *
 * Pre-footer vantaggi + colonne info + bottom bar con
 * copyright, pagine legali, metodi di pagamento, social.
 *
 * @package BizStudio
 */

defined( 'ABSPATH' ) || exit;

$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy' );
$terms_page_id   = (int) get_option( 'woocommerce_terms_page_id' );
$shop_url        = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '#';
$account_url     = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : '#';
?>

<?php
// Pre-footer: Spedizione gratuita / Reso facile / Pagamento sicuro
if ( function_exists( 'bizstudio_render_pre_footer' ) ) {
	bizstudio_render_pre_footer();
}
?>

<footer class="biz-footer">
	<div class="biz-container">

		<!-- Colonne principali -->
		<div class="biz-footer__grid">
			<!-- Col 1: Brand -->
			<div class="biz-footer__col">
				<div class="biz-footer__brand">
					<?php if ( has_custom_logo() ) : ?>
						<?php the_custom_logo(); ?>
					<?php else : ?>
						<h4 class="biz-footer__title"><?php bloginfo( 'name' ); ?></h4>
					<?php endif; ?>
				</div>
				<p class="biz-footer__text">
					<?php echo esc_html( get_bloginfo( 'description' ) ?: __( 'Il tuo negozio online di fiducia. Qualita, stile e convenienza.', 'bizstudio' ) ); ?>
				</p>
				<?php if ( function_exists( 'bizstudio_render_social_links' ) ) : ?>
					<?php bizstudio_render_social_links(); ?>
				<?php endif; ?>
			</div>

			<!-- Col 2: Shop -->
			<div class="biz-footer__col">
				<h4 class="biz-footer__title"><?php esc_html_e( 'Shop', 'bizstudio' ); ?></h4>
				<ul class="biz-footer__links">
					<li><a href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Tutti i prodotti', 'bizstudio' ); ?></a></li>
					<?php
					$categories = get_terms( [ 'taxonomy' => 'product_cat', 'hide_empty' => true, 'number' => 4 ] );
					if ( ! is_wp_error( $categories ) ) :
						foreach ( $categories as $cat ) : ?>
							<li><a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a></li>
						<?php endforeach;
					endif;
					?>
				</ul>
			</div>

			<!-- Col 3: Account -->
			<div class="biz-footer__col">
				<h4 class="biz-footer__title"><?php esc_html_e( 'Il mio account', 'bizstudio' ); ?></h4>
				<ul class="biz-footer__links">
					<li><a href="<?php echo esc_url( $account_url ); ?>"><?php esc_html_e( 'Accedi / Registrati', 'bizstudio' ); ?></a></li>
					<?php if ( function_exists( 'wc_get_account_endpoint_url' ) ) : ?>
						<li><a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>"><?php esc_html_e( 'I miei ordini', 'bizstudio' ); ?></a></li>
						<li><a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address' ) ); ?>"><?php esc_html_e( 'Indirizzi', 'bizstudio' ); ?></a></li>
					<?php endif; ?>
					<li><a href="<?php echo esc_url( home_url( '/wishlist/' ) ); ?>"><?php esc_html_e( 'Wishlist', 'bizstudio' ); ?></a></li>
					<?php if ( function_exists( 'wc_get_account_endpoint_url' ) ) : ?>
						<li><a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>"><?php esc_html_e( 'Traccia ordine', 'bizstudio' ); ?></a></li>
					<?php endif; ?>
				</ul>
			</div>

			<!-- Col 4: Info e Legale -->
			<div class="biz-footer__col">
				<h4 class="biz-footer__title"><?php esc_html_e( 'Informazioni', 'bizstudio' ); ?></h4>
				<ul class="biz-footer__links">
					<?php if ( $privacy_page_id ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $privacy_page_id ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'bizstudio' ); ?></a></li>
					<?php endif; ?>
					<?php if ( $terms_page_id ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $terms_page_id ) ); ?>"><?php esc_html_e( 'Termini e Condizioni', 'bizstudio' ); ?></a></li>
					<?php endif; ?>
					<li><a href="<?php echo esc_url( home_url( '/cookie-policy/' ) ); ?>"><?php esc_html_e( 'Cookie Policy', 'bizstudio' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/contatti/' ) ); ?>"><?php esc_html_e( 'Contattaci', 'bizstudio' ); ?></a></li>
				</ul>
			</div>
		</div>

		<!-- Bottom bar -->
		<div class="biz-footer__bottom">
			<div class="biz-footer__bottom-left">
				<p class="biz-footer__copy">
					&copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>.
					<?php esc_html_e( 'Tutti i diritti riservati.', 'bizstudio' ); ?>
				</p>
			</div>

			<div class="biz-footer__bottom-center">
				<?php if ( function_exists( 'bizstudio_render_payment_icons' ) ) : ?>
					<?php bizstudio_render_payment_icons(); ?>
				<?php endif; ?>
			</div>

			<div class="biz-footer__bottom-right">
				<ul class="biz-footer__legal">
					<?php if ( $privacy_page_id ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $privacy_page_id ) ); ?>"><?php esc_html_e( 'Privacy', 'bizstudio' ); ?></a></li>
					<?php endif; ?>
					<?php if ( $terms_page_id ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $terms_page_id ) ); ?>"><?php esc_html_e( 'Termini', 'bizstudio' ); ?></a></li>
					<?php endif; ?>
				</ul>
			</div>
		</div>

	</div>
</footer>

<?php get_template_part( 'template-parts/product/quick-view-modal' ); ?>
<?php wp_footer(); ?>
</body>
</html>
