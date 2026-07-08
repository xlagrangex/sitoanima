<?php
/**
 * Theme footer — entry point.
 * Renderizza template-parts/footer/main.php.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</main>

<?php
// Sul checkout footer minimal: solo legali + carrier + payments. Sul resto, footer completo.
if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_wc_endpoint_url( 'order-received' ) ) :
	?>
	<footer class="pn-co-footer">
		<div class="pn-co-footer__inner">
			<p class="pn-co-footer__copy">
				© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?> ·
				<a href="<?php echo esc_url( get_privacy_policy_url() ); ?>"><?php esc_html_e( 'Privacy', 'pharmanow' ); ?></a> ·
				<a href="<?php echo esc_url( home_url( '/legale/termini' ) ); ?>"><?php esc_html_e( 'Termini', 'pharmanow' ); ?></a>
			</p>
		</div>
	</footer>
<?php else : ?>
	<?php get_template_part( 'template-parts/footer/main' ); ?>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
