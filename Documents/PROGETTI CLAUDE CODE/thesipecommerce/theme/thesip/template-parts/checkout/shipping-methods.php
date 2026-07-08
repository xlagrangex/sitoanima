<?php
/**
 * Shipping methods al checkout — partial estraibile come fragment AJAX.
 *
 * Wrapper id="pn-shipping-methods": è il selettore usato in
 * `inc/checkout-fields.php` per `woocommerce_update_order_review_fragments`,
 * così quando il cart cambia (qty +/-, coupon, indirizzo) WC rimpiazza il
 * blocco senza refresh pagina. Mantenere id e markup wrapper invariati.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
	return;
}

WC()->cart->calculate_shipping();
WC()->cart->calculate_totals();
$pn_packages = WC()->shipping()->get_packages();

$pn_has_methods = false;
foreach ( $pn_packages as $pn_pkg ) {
	if ( ! empty( $pn_pkg['rates'] ) ) {
		$pn_has_methods = true;
		break;
	}
}
?>
<div class="pn-co-shipping woocommerce-shipping-fields" id="pn-shipping-methods">
	<?php if ( ! $pn_has_methods ) : ?>
		<?php $pn_state_fb = pn_get_shipping_state(); ?>
		<label class="pn-co-ship-method">
			<input type="radio" name="shipping_method[0]" value="<?php echo $pn_state_fb['is_free'] ? 'pn_free_fallback' : 'pn_standard_fallback'; ?>" checked>
			<svg class="pn-co-ship-method__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 18H3c-.6 0-1-.4-1-1V7c0-.6.4-1 1-1h10c.6 0 1 .4 1 1v11"/><path d="M14 9h4l4 4v4c0 .6-.4 1-1 1h-2"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/></svg>
			<div class="pn-co-ship-method__body">
				<div class="pn-co-ship-method__head">
					<span class="pn-co-ship-method__title"><?php esc_html_e( 'Spedizione standard', 'pharmanow' ); ?></span>
					<?php if ( $pn_state_fb['is_free'] ) : ?>
						<span class="pn-co-ship-method__free-badge"><?php esc_html_e( 'GRATUITA', 'pharmanow' ); ?></span>
					<?php endif; ?>
				</div>
				<p class="pn-co-ship-method__desc"><?php esc_html_e( 'Consegna 24-48h', 'pharmanow' ); ?></p>
			</div>
			<span class="pn-co-ship-method__cost">
				<?php if ( $pn_state_fb['is_free'] ) : ?>
					<span class="pn-co-ship-method__cost-strike"><?php echo wc_price( $pn_state_fb['standard_cost'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="pn-co-ship-method__cost-free"><?php esc_html_e( 'Gratuita', 'pharmanow' ); ?></span>
				<?php else : ?>
					<?php echo wc_price( $pn_state_fb['standard_cost'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endif; ?>
			</span>
		</label>
	<?php else : ?>
		<?php $pn_state_real = pn_get_shipping_state(); ?>
		<?php foreach ( $pn_packages as $pn_i => $pn_package ) : ?>
			<?php
			$pn_chosen     = isset( WC()->session->chosen_shipping_methods[ $pn_i ] ) ? WC()->session->chosen_shipping_methods[ $pn_i ] : '';
			$pn_is_first   = true;
			?>
			<?php foreach ( $pn_package['rates'] as $pn_method ) : ?>
				<?php
				$pn_is_checked = ( $pn_method->id === $pn_chosen ) || ( ! $pn_chosen && $pn_is_first );
				$pn_is_first   = false;
				// IMPORTANTE: "Gratuita" solo se la rate WC reale costa 0. Mai derivare
				// dal solo subtotale (`is_free`) — porterebbe a UI bugiarda quando WC
				// non propone free_shipping (es. coupon che riduce il subtotale post-sconto
				// sotto soglia). In passato bug visto: cliente vede "Gratuita" e paga 4,90.
				$pn_show_free  = ( 0 == $pn_method->cost );
				?>
				<label class="pn-co-ship-method" data-cost="<?php echo esc_attr( (string) $pn_method->cost ); ?>">
					<input type="radio" name="shipping_method[<?php echo esc_attr( (string) $pn_i ); ?>]" value="<?php echo esc_attr( $pn_method->id ); ?>" <?php checked( $pn_is_checked, true ); ?>>
					<svg class="pn-co-ship-method__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 18H3c-.6 0-1-.4-1-1V7c0-.6.4-1 1-1h10c.6 0 1 .4 1 1v11"/><path d="M14 9h4l4 4v4c0 .6-.4 1-1 1h-2"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/></svg>
					<div class="pn-co-ship-method__body">
						<div class="pn-co-ship-method__head">
							<span class="pn-co-ship-method__title"><?php echo esc_html( $pn_method->get_label() ); ?></span>
							<?php if ( $pn_show_free ) : ?>
								<span class="pn-co-ship-method__free-badge"><?php esc_html_e( 'GRATUITA', 'pharmanow' ); ?></span>
							<?php endif; ?>
						</div>
						<p class="pn-co-ship-method__desc"><?php echo esc_html__( 'Consegna stimata 24-48h', 'pharmanow' ); ?></p>
					</div>
					<span class="pn-co-ship-method__cost">
						<?php if ( $pn_show_free ) : ?>
							<span class="pn-co-ship-method__cost-strike"><?php echo wc_price( $pn_state_real['standard_cost'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span class="pn-co-ship-method__cost-free"><?php esc_html_e( 'Gratuita', 'pharmanow' ); ?></span>
						<?php else : ?>
							<?php echo wc_price( $pn_method->cost ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php endif; ?>
					</span>
				</label>
			<?php endforeach; ?>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
