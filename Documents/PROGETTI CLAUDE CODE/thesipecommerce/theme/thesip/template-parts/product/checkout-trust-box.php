<?php
/**
 * Box "Checkout protetto · Pagamenti sicuri" con strip metodi pagamento.
 *
 * Replica `CheckoutTrustBox.tsx`. Asset: assets/images/payment/ (stessi del footer).
 *
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

$pn_payment_assets = array(
	'visa.svg'             => 'Visa',
	'mastercard.svg'       => 'Mastercard',
	'maestro.svg'          => 'Maestro',
	'american-express.svg' => 'American Express',
	'paypal.svg'           => 'PayPal',
	'apple-pay.svg'        => 'Apple Pay',
	'google-pay.svg'       => 'Google Pay',
);
?>
<div class="rounded-lg border bg-card p-3">
	<div class="mb-2 flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
		<span class="text-emerald-600"><?php pn_icon( 'lock', array( 'class' => 'h-3 w-3' ) ); ?></span>
		<span><?php esc_html_e( 'Checkout protetto · Pagamenti sicuri', 'pharmanow' ); ?></span>
	</div>
	<div class="flex flex-wrap items-center gap-2">
		<?php foreach ( $pn_payment_assets as $pn_file => $pn_alt ) : ?>
			<img src="<?php echo esc_url( pn_asset( "images/payment/{$pn_file}" ) ); ?>" alt="<?php echo esc_attr( $pn_alt ); ?>" class="h-5 w-auto">
		<?php endforeach; ?>
	</div>
</div>
