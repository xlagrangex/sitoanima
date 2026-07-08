<?php
/**
 * Address form (billing/shipping). Replica `AddressForm.tsx`.
 *
 * @var array $args { type: 'billing'|'shipping', label: string, user_id: int }
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

$pn_t      = $args['type'] ?? 'billing';
$pn_label  = $args['label'] ?? '';
$pn_uid    = (int) ( $args['user_id'] ?? get_current_user_id() );

$pn_get = function ( string $field ) use ( $pn_t, $pn_uid ): string {
	return (string) get_user_meta( $pn_uid, $pn_t . '_' . $field, true );
};

$pn_country = $pn_get( 'country' ) ?: 'IT';
?>
<form method="post" action="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address' ) ); ?>" class="space-y-4 rounded-xl border bg-card p-5">
	<input type="hidden" name="pn_action" value="pn_address_update">
	<input type="hidden" name="type" value="<?php echo esc_attr( $pn_t ); ?>">
	<?php wp_nonce_field( 'pn_address_update_' . $pn_t, '_pn_nonce' ); ?>

	<h3 class="text-base font-semibold"><?php echo esc_html( $pn_label ); ?></h3>

	<div class="grid grid-cols-1 gap-3 md:grid-cols-2">
		<div class="space-y-1.5">
			<label class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"><?php esc_html_e( 'Nome', 'pharmanow' ); ?></label>
			<input type="text" name="<?php echo esc_attr( $pn_t ); ?>_first_name" required minlength="2" value="<?php echo esc_attr( $pn_get( 'first_name' ) ); ?>" class="w-full h-11 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-pharma-teal/30 focus:border-pharma-teal">
		</div>
		<div class="space-y-1.5">
			<label class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"><?php esc_html_e( 'Cognome', 'pharmanow' ); ?></label>
			<input type="text" name="<?php echo esc_attr( $pn_t ); ?>_last_name" required minlength="2" value="<?php echo esc_attr( $pn_get( 'last_name' ) ); ?>" class="w-full h-11 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-pharma-teal/30 focus:border-pharma-teal">
		</div>
	</div>

	<div class="space-y-1.5">
		<label class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"><?php esc_html_e( 'Indirizzo', 'pharmanow' ); ?></label>
		<input type="text" name="<?php echo esc_attr( $pn_t ); ?>_address_1" required minlength="3" placeholder="<?php esc_attr_e( 'Via, numero civico', 'pharmanow' ); ?>" value="<?php echo esc_attr( $pn_get( 'address_1' ) ); ?>" class="w-full h-11 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-pharma-teal/30 focus:border-pharma-teal">
	</div>

	<div class="space-y-1.5">
		<label class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"><?php esc_html_e( 'Indirizzo aggiuntivo (facoltativo)', 'pharmanow' ); ?></label>
		<input type="text" name="<?php echo esc_attr( $pn_t ); ?>_address_2" placeholder="<?php esc_attr_e( 'Scala, interno...', 'pharmanow' ); ?>" value="<?php echo esc_attr( $pn_get( 'address_2' ) ); ?>" class="w-full h-11 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-pharma-teal/30 focus:border-pharma-teal">
	</div>

	<div class="grid grid-cols-3 gap-3">
		<div class="space-y-1.5">
			<label class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"><?php esc_html_e( 'CAP', 'pharmanow' ); ?></label>
			<input type="text" name="<?php echo esc_attr( $pn_t ); ?>_postcode" required pattern="\d{5}" maxlength="5" inputmode="numeric" value="<?php echo esc_attr( $pn_get( 'postcode' ) ); ?>" class="w-full h-11 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-pharma-teal/30 focus:border-pharma-teal">
		</div>
		<div class="space-y-1.5">
			<label class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"><?php esc_html_e( 'Città', 'pharmanow' ); ?></label>
			<input type="text" name="<?php echo esc_attr( $pn_t ); ?>_city" required minlength="2" value="<?php echo esc_attr( $pn_get( 'city' ) ); ?>" class="w-full h-11 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-pharma-teal/30 focus:border-pharma-teal">
		</div>
		<div class="space-y-1.5">
			<label class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"><?php esc_html_e( 'Provincia', 'pharmanow' ); ?></label>
			<input type="text" name="<?php echo esc_attr( $pn_t ); ?>_state" required minlength="2" maxlength="2" placeholder="MI" value="<?php echo esc_attr( $pn_get( 'state' ) ); ?>" class="w-full h-11 rounded-md border border-input bg-background px-3 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-pharma-teal/30 focus:border-pharma-teal">
		</div>
	</div>

	<div class="space-y-1.5">
		<label class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"><?php esc_html_e( 'Telefono (facoltativo)', 'pharmanow' ); ?></label>
		<input type="tel" name="<?php echo esc_attr( $pn_t ); ?>_phone" autocomplete="tel" value="<?php echo esc_attr( $pn_get( 'phone' ) ); ?>" class="w-full h-11 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-pharma-teal/30 focus:border-pharma-teal">
	</div>

	<input type="hidden" name="<?php echo esc_attr( $pn_t ); ?>_country" value="<?php echo esc_attr( $pn_country ); ?>">

	<div class="flex justify-end pt-1">
		<button type="submit" class="pn-atc-btn h-11 px-6 rounded-md bg-pharma-teal text-white text-sm font-semibold hover:bg-pharma-teal-dark">
			<span class="pn-atc-default"><?php esc_html_e( 'Salva indirizzo', 'pharmanow' ); ?></span>
			<span class="pn-atc-success"><?php pn_icon( 'check-circle-2', array( 'class' => 'h-4 w-4' ) ); ?> <?php esc_html_e( 'Salvato', 'pharmanow' ); ?></span>
		</button>
	</div>
</form>
