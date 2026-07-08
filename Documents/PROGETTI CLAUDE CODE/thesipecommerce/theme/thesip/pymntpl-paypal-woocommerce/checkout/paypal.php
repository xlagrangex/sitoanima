<?php
/**
 * Override Pharmanow del template paypal.php (plugin pymntpl-paypal-woocommerce).
 * Tutte le stringhe sono in italiano — il plugin non ha file di traduzione it_IT.
 *
 * @var \PaymentPlugins\WooCommerce\PPCP\Payments\Gateways\PayPalGateway $gateway
 * @var \PaymentPlugins\WooCommerce\PPCP\Assets\AssetsApi                $assets
 * @var bool                                                             $connected
 */
?>
<?php
if ( $gateway->supports( 'vault' ) && is_checkout() ) {
	$gateway->saved_payment_methods();
}
?>
<div class="wc-ppcp-payment-method__container wc-payment-form">
	<?php if ( $connected ): ?>
		<?php if ( $gateway->is_show_popup_icon_enabled() ): ?>
			<div class="wc-ppcp-popup__container">
				<img src="<?php echo esc_url( $assets->assets_url( 'assets/img/popup.svg' ) ) ?>"/>
				<p>
					<?php if ( is_add_payment_method_page() ): ?>
						Clicca il pulsante PayPal per aggiungere il tuo metodo di pagamento.
					<?php elseif ( $gateway->is_place_order_button() ): ?>
						<?php printf( 'Dopo aver cliccato "%s" verrai reindirizzato su PayPal per completare il pagamento in modo sicuro.', esc_html( $gateway->get_order_button_text() ) ) ?>
					<?php else: ?>
						Clicca il pulsante PayPal qui sotto per completare l'ordine.
					<?php endif; ?>
				</p>
			</div>
		<?php endif; ?>
		<div class="wc-ppcp-order-review-message__container" style="display: none">
			<div class="wc-ppcp-order-review__message">
				Il tuo metodo di pagamento PayPal è pronto. Verifica i dettagli dell'ordine e clicca %s
			</div>
			<a href="#" class="wc-ppcp-cancel__payment">Annulla</a>
		</div>
	<?php else: ?>
		<div class="wc-ppcp-notice__info">
			<?php wc_print_notice( 'Collega prima il tuo account PayPal per poterlo utilizzare.', 'error' ) ?>
		</div>
	<?php endif ?>
</div>
