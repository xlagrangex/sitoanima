<?php
/**
 * My-address: 2 form (billing + shipping) inline. Replica `AddressForm.tsx` × 2.
 *
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

$pn_user_id = get_current_user_id();
$pn_saved   = isset( $_GET['saved'] ) && '1' === $_GET['saved'];
$pn_type    = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : '';
?>
<div class="space-y-6">
	<div>
		<h2 class="text-2xl font-bold"><?php esc_html_e( 'Indirizzi', 'pharmanow' ); ?></h2>
		<p class="mt-1 text-sm text-muted-foreground"><?php esc_html_e( 'Gestisci gli indirizzi di spedizione e fatturazione.', 'pharmanow' ); ?></p>
	</div>

	<?php if ( $pn_saved ) : ?>
		<div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">
			<?php esc_html_e( 'Indirizzo salvato.', 'pharmanow' ); ?>
		</div>
	<?php endif; ?>

	<?php
	$pn_types = array(
		'shipping' => __( 'Indirizzo di spedizione', 'pharmanow' ),
		'billing'  => __( 'Indirizzo di fatturazione', 'pharmanow' ),
	);
	foreach ( $pn_types as $pn_t => $pn_label ) :
		get_template_part(
			'template-parts/account/address-form',
			null,
			array(
				'type'    => $pn_t,
				'label'   => $pn_label,
				'user_id' => $pn_user_id,
			)
		);
	endforeach;
	?>
</div>
