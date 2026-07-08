<?php
/**
 * Body condiviso pagina ordine (timeline + items + sidebar + trust strip).
 * Riusato da thank-you e my-account view-order.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn = is_array( $args ) ? $args : array();
$order        = $pn['order'];
$variant      = $pn['variant'] ?? 'info';
$step_idx     = (int) ( $pn['step_idx'] ?? 0 );
$eta_label    = $pn['eta_label'] ?? '';
$steps        = $pn['steps'] ?? array();
$ship_addr    = $pn['ship_addr'] ?? '';
$bill_addr    = $pn['bill_addr'] ?? '';
$show_billing = ! empty( $pn['show_billing'] );
$ship_method  = $pn['ship_method'] ?? '';
$totals       = $pn['totals'] ?? array();
$qty_total    = (int) ( $pn['qty_total'] ?? 0 );
$settings     = $pn['settings'] ?? array();
$shop_url     = $pn['shop_url'] ?? home_url( '/' );
$track_url    = $pn['track_url'] ?? '';
$account_url  = $pn['account_url'] ?? '';
$payment_id   = $pn['payment_id'] ?? '';
$payment_lbl  = $pn['payment_title'] ?? '';
$customer_note = $pn['customer_note'] ?? '';
$phone        = $pn['phone'] ?? '';
$email        = $pn['email'] ?? '';

$total_amt    = (float) ( $totals['total'] ?? 0 );
$subtotal_amt = (float) ( $totals['subtotal'] ?? 0 );
$shipping_amt = (float) ( $totals['shipping'] ?? 0 );
$discount_amt = (float) ( $totals['discount'] ?? 0 );
$tax_amt      = (float) ( $totals['tax'] ?? 0 );
?>

<div class="max-w-[1280px] mx-auto px-4 py-10 sm:py-12 <?php echo 'celebration' === $variant ? '-mt-10 sm:-mt-14' : ''; ?> relative z-10">

	<?php // Quick action bar. ?>
	<div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-8">
		<?php
		// In variant "info" siamo già su account, niente "vedi dettaglio". Sostituisco con scarica fattura/email.
		if ( 'celebration' === $variant ) {
			$pn_actions = array(
				array( 'href' => $account_url, 'icon' => 'file-text',     'label' => __( 'Vedi dettaglio ordine', 'pharmanow' ), 'variant' => 'primary' ),
				array( 'href' => $track_url,   'icon' => 'truck',         'label' => __( 'Traccia spedizione', 'pharmanow' ),     'variant' => 'secondary' ),
				array( 'href' => $shop_url,    'icon' => 'shopping-bag',  'label' => __( 'Continua lo shopping', 'pharmanow' ),   'variant' => 'secondary' ),
			);
		} else {
			$pn_actions = array(
				array( 'href' => $track_url, 'icon' => 'truck',        'label' => __( 'Traccia spedizione', 'pharmanow' ),         'variant' => 'primary' ),
				array( 'href' => 'mailto:' . ( $settings['email'] ?? '' ) . '?subject=' . rawurlencode( sprintf( /* translators: %s = order number */ __( 'Richiesta info ordine #%s', 'pharmanow' ), $order->get_order_number() ) ), 'icon' => 'message-circle', 'label' => __( 'Contatta il supporto', 'pharmanow' ), 'variant' => 'secondary' ),
				array( 'href' => $shop_url, 'icon' => 'shopping-bag',  'label' => __( 'Continua lo shopping', 'pharmanow' ),       'variant' => 'secondary' ),
			);
		}
		foreach ( $pn_actions as $pn_a ) :
			$pn_btn_class = 'primary' === $pn_a['variant']
				? 'bg-gradient-to-r from-[#0B8894] to-[#43CCB1] text-white shadow-md hover:shadow-lg hover:opacity-95'
				: 'bg-white text-gray-800 border border-gray-200 hover:border-[#0B8894] hover:text-[#0B8894]';
			?>
			<a href="<?php echo esc_url( $pn_a['href'] ); ?>" class="<?php echo esc_attr( $pn_btn_class ); ?> inline-flex items-center justify-center gap-2 font-semibold py-3 px-5 rounded-xl transition-all">
				<?php pn_icon( $pn_a['icon'], array( 'class' => 'w-5 h-5' ) ); ?>
				<span><?php echo esc_html( $pn_a['label'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>

	<?php // Timeline stepper. ?>
	<div class="bg-white rounded-2xl shadow-sm p-6 sm:p-8 mb-6">
		<div class="flex items-center justify-between mb-4">
			<h2 class="text-lg font-bold text-gray-900"><?php esc_html_e( 'Stato del tuo ordine', 'pharmanow' ); ?></h2>
			<?php if ( $eta_label && $step_idx < 3 ) : ?>
				<span class="text-sm text-gray-500 hidden sm:inline">
					<?php
					printf(
						/* translators: %s = ETA range */
						esc_html__( 'Consegna stimata: %s', 'pharmanow' ),
						'<strong class="text-gray-900">' . esc_html( $eta_label ) . '</strong>'
					);
					?>
				</span>
			<?php elseif ( 3 === $step_idx ) : ?>
				<span class="text-sm text-green-700 font-semibold inline-flex items-center gap-1.5">
					<?php pn_icon( 'check', array( 'class' => 'w-4 h-4' ) ); ?>
					<?php esc_html_e( 'Consegnato', 'pharmanow' ); ?>
				</span>
			<?php endif; ?>
		</div>

		<div class="relative pt-2">
			<div class="absolute left-0 right-0 top-[26px] h-1 bg-gray-100 rounded-full hidden sm:block" aria-hidden="true"></div>
			<div
				class="absolute left-0 top-[26px] h-1 bg-gradient-to-r from-[#0B8894] to-[#43CCB1] rounded-full hidden sm:block transition-all"
				style="width: <?php echo esc_attr( max( 0, min( 100, ( $step_idx / 3 ) * 100 ) ) ); ?>%;"
				aria-hidden="true"
			></div>

			<ol class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-2 relative">
				<?php foreach ( $steps as $i => $pn_step ) :
					$pn_done    = $i < $step_idx;
					$pn_current = $i === $step_idx;
					$pn_circle_cls = $pn_done || $pn_current
						? 'bg-gradient-to-br from-[#0B8894] to-[#43CCB1] text-white shadow-md'
						: 'bg-gray-100 text-gray-400';
					$pn_label_cls = $pn_done || $pn_current ? 'text-gray-900 font-semibold' : 'text-gray-500';
					?>
					<li class="flex flex-col items-center text-center relative">
						<div class="relative">
							<div class="w-12 h-12 sm:w-13 sm:h-13 rounded-full flex items-center justify-center <?php echo esc_attr( $pn_circle_cls ); ?> transition-all">
								<?php pn_icon( $pn_step['icon'], array( 'class' => 'w-6 h-6' ) ); ?>
							</div>
							<?php if ( $pn_current && $step_idx < 3 ) : ?>
								<span class="absolute -inset-1 rounded-full ring-2 ring-[#43CCB1] animate-pulse"></span>
							<?php endif; ?>
						</div>
						<p class="mt-3 text-sm <?php echo esc_attr( $pn_label_cls ); ?>"><?php echo esc_html( $pn_step['label'] ); ?></p>
						<p class="text-xs text-gray-500 hidden sm:block"><?php echo esc_html( $pn_step['sub'] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>

		<?php if ( $eta_label && $step_idx < 3 ) : ?>
			<div class="sm:hidden mt-4 text-sm text-gray-500 text-center">
				<?php
				printf(
					/* translators: %s = ETA range */
					esc_html__( 'Consegna stimata: %s', 'pharmanow' ),
					'<strong class="text-gray-900">' . esc_html( $eta_label ) . '</strong>'
				);
				?>
			</div>
		<?php endif; ?>
	</div>

	<?php
	// Delivery map (Shopify-style) — mostrata solo se Mapbox token + geocoding ok.
	get_template_part(
		'template-parts/order/delivery-map',
		null,
		array(
			'order'     => $order,
			'ship_addr' => $ship_addr,
		)
	);
	?>

	<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

		<?php // Items + summary + what's next. ?>
		<div class="lg:col-span-2 space-y-6">

			<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
				<div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
					<h2 class="text-lg font-bold text-gray-900"><?php esc_html_e( 'Riepilogo ordine', 'pharmanow' ); ?></h2>
					<span class="text-sm text-gray-500">
						<?php
						printf(
							/* translators: %d = numero pezzi */
							esc_html( _n( '%d articolo', '%d articoli', $qty_total, 'pharmanow' ) ),
							(int) $qty_total
						);
						?>
					</span>
				</div>

				<ul class="divide-y divide-gray-100">
					<?php foreach ( $order->get_items() as $pn_item ) :
						$pn_product = $pn_item->get_product();
						if ( ! $pn_product ) {
							continue;
						}
						$pn_thumb = $pn_product->get_image( 'pn-product-thumb', array( 'class' => 'w-full h-full object-cover' ) );
						$pn_link  = $pn_product->is_visible() ? $pn_product->get_permalink( $pn_item ) : '';
						$pn_qty   = (int) $pn_item->get_quantity();
						$pn_price = wc_price( $order->get_line_subtotal( $pn_item, true ) );
						?>
						<li class="px-4 sm:px-6 py-4 flex items-center gap-4">
							<div class="relative w-16 h-16 sm:w-20 sm:h-20 rounded-xl bg-gray-50 border border-gray-100 overflow-hidden shrink-0">
								<?php
								if ( $pn_link ) {
									echo '<a href="' . esc_url( $pn_link ) . '" class="block w-full h-full">' . $pn_thumb . '</a>';
								} else {
									echo $pn_thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
								?>
								<span class="absolute -top-2 -right-2 min-w-[22px] h-[22px] px-1.5 rounded-full bg-[#0B8894] text-white text-[11px] font-bold flex items-center justify-center"><?php echo (int) $pn_qty; ?></span>
							</div>
							<div class="min-w-0 flex-1">
								<p class="font-medium text-gray-900 line-clamp-2 text-sm sm:text-base">
									<?php
									if ( $pn_link ) {
										echo '<a href="' . esc_url( $pn_link ) . '" class="hover:text-[#0B8894]">' . esc_html( $pn_item->get_name() ) . '</a>';
									} else {
										echo esc_html( $pn_item->get_name() );
									}
									?>
								</p>
								<?php
								$pn_meta = wc_display_item_meta( $pn_item, array( 'echo' => false ) );
								if ( $pn_meta ) :
									?>
									<div class="text-xs text-gray-500 mt-0.5"><?php echo wp_kses_post( $pn_meta ); ?></div>
								<?php endif; ?>
							</div>
							<div class="text-right shrink-0">
								<p class="font-semibold text-gray-900"><?php echo wp_kses_post( $pn_price ); ?></p>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>

				<div class="px-4 sm:px-6 py-4 bg-gray-50/60 border-t border-gray-100 space-y-2 text-sm">
					<div class="flex justify-between text-gray-600">
						<span><?php esc_html_e( 'Subtotale', 'pharmanow' ); ?></span>
						<span class="text-gray-900"><?php echo wp_kses_post( wc_price( $subtotal_amt ) ); ?></span>
					</div>
					<?php if ( $discount_amt > 0 ) : ?>
						<div class="flex justify-between text-green-700">
							<span class="inline-flex items-center gap-1.5">
								<?php pn_icon( 'tag', array( 'class' => 'w-4 h-4' ) ); ?>
								<?php esc_html_e( 'Sconto', 'pharmanow' ); ?>
							</span>
							<span>− <?php echo wp_kses_post( wc_price( $discount_amt ) ); ?></span>
						</div>
					<?php endif; ?>
					<div class="flex justify-between text-gray-600">
						<span class="inline-flex items-center gap-1.5">
							<?php pn_icon( 'truck', array( 'class' => 'w-4 h-4' ) ); ?>
							<?php echo $ship_method ? esc_html( $ship_method ) : esc_html__( 'Spedizione', 'pharmanow' ); ?>
						</span>
						<span class="text-gray-900">
							<?php
							if ( $shipping_amt <= 0.001 ) {
								echo '<span class="text-green-700 font-semibold">' . esc_html__( 'Gratis', 'pharmanow' ) . '</span>';
							} else {
								echo wp_kses_post( wc_price( $shipping_amt ) );
							}
							?>
						</span>
					</div>
					<?php if ( $tax_amt > 0 && wc_tax_enabled() && 'incl' !== get_option( 'woocommerce_tax_display_cart' ) ) : ?>
						<div class="flex justify-between text-gray-600">
							<span><?php esc_html_e( 'IVA', 'pharmanow' ); ?></span>
							<span class="text-gray-900"><?php echo wp_kses_post( wc_price( $tax_amt ) ); ?></span>
						</div>
					<?php endif; ?>
					<div class="flex justify-between pt-2 border-t border-gray-200 text-base">
						<span class="font-bold text-gray-900"><?php esc_html_e( 'Totale', 'pharmanow' ); ?></span>
						<span class="font-bold text-gray-900"><?php echo wp_kses_post( wc_price( $total_amt ) ); ?></span>
					</div>
				</div>
			</div>

			<?php // What's next — solo se l'ordine non è già completato. ?>
			<?php if ( $step_idx < 3 ) : ?>
				<div class="bg-white rounded-2xl shadow-sm p-6 sm:p-8">
					<h2 class="text-lg font-bold text-gray-900 mb-4"><?php esc_html_e( 'Cosa succede ora', 'pharmanow' ); ?></h2>
					<ul class="space-y-4">
						<?php
						$pn_next_steps = array(
							array(
								'icon'  => 'mail',
								'title' => __( 'Conferma via email', 'pharmanow' ),
								'desc'  => sprintf(
									/* translators: %s = email */
									__( 'Riceverai a %s una conferma con tutti i dettagli del tuo ordine.', 'pharmanow' ),
									$email ? '<strong>' . esc_html( $email ) . '</strong>' : __( "all'indirizzo registrato", 'pharmanow' )
								),
							),
							array(
								'icon'  => 'package',
								'title' => __( 'Prepariamo le tue carte', 'pharmanow' ),
								'desc'  => __( 'Imballiamo il tuo set con cura entro 24 ore lavorative, pronto a portarti un pezzo di mare.', 'pharmanow' ),
							),
							array(
								'icon'  => 'truck',
								'title' => __( 'Spedizione tracciata', 'pharmanow' ),
								'desc'  => __( 'Quando il pacco parte ricevi un secondo messaggio con il link per seguirne il tracciamento.', 'pharmanow' ),
							),
							array(
								'icon'  => 'house',
								'title' => __( 'Consegna a casa', 'pharmanow' ),
								'desc'  => sprintf(
									/* translators: %s = ETA range */
									__( 'Consegna prevista entro %s. Se sei a Napoli, possiamo consegnartele a mano.', 'pharmanow' ),
									'<strong>' . esc_html( $eta_label ) . '</strong>'
								),
							),
						);
						foreach ( $pn_next_steps as $pn_n ) :
							?>
							<li class="flex gap-4">
								<div class="w-10 h-10 shrink-0 rounded-xl bg-[#0B8894]/10 text-[#0B8894] flex items-center justify-center">
									<?php pn_icon( $pn_n['icon'], array( 'class' => 'w-5 h-5' ) ); ?>
								</div>
								<div class="min-w-0">
									<p class="font-semibold text-gray-900"><?php echo esc_html( $pn_n['title'] ); ?></p>
									<p class="text-sm text-gray-600 mt-0.5"><?php echo wp_kses_post( $pn_n['desc'] ); ?></p>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>

		<?php // Sidebar destra. ?>
		<aside class="space-y-6">

			<div class="bg-white rounded-2xl shadow-sm p-6">
				<div class="flex items-center justify-between mb-3">
					<h3 class="font-bold text-gray-900 inline-flex items-center gap-2">
						<?php pn_icon( 'map-pin', array( 'class' => 'w-5 h-5 text-[#0B8894]' ) ); ?>
						<?php esc_html_e( 'Spedizione', 'pharmanow' ); ?>
					</h3>
				</div>
				<address class="not-italic text-sm text-gray-700 leading-relaxed">
					<?php echo $ship_addr ? wp_kses_post( $ship_addr ) : esc_html__( 'Nessun indirizzo fornito', 'pharmanow' ); ?>
				</address>
				<?php if ( $phone ) : ?>
					<p class="text-sm text-gray-600 mt-3 inline-flex items-center gap-2">
						<?php pn_icon( 'phone', array( 'class' => 'w-4 h-4 text-[#0B8894]' ) ); ?>
						<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>" class="hover:underline"><?php echo esc_html( $phone ); ?></a>
					</p>
				<?php endif; ?>
			</div>

			<div class="bg-white rounded-2xl shadow-sm p-6">
				<h3 class="font-bold text-gray-900 mb-3 inline-flex items-center gap-2">
					<?php pn_icon( 'credit-card', array( 'class' => 'w-5 h-5 text-[#0B8894]' ) ); ?>
					<?php esc_html_e( 'Pagamento', 'pharmanow' ); ?>
				</h3>
				<p class="text-sm text-gray-700"><?php echo esc_html( $payment_lbl ); ?></p>
				<p class="text-xs text-gray-500 mt-1">
					<?php
					if ( $order->is_paid() ) {
						/* translators: %s = totale ordine */
						$pn_amount_tpl = esc_html__( 'Importo addebitato: %s', 'pharmanow' );
					} elseif ( $order->needs_payment() ) {
						/* translators: %s = totale ordine */
						$pn_amount_tpl = esc_html__( 'Totale da pagare: %s', 'pharmanow' );
					} else {
						/* translators: %s = totale ordine */
						$pn_amount_tpl = esc_html__( 'Totale ordine: %s', 'pharmanow' );
					}
					printf( $pn_amount_tpl, wp_kses_post( wc_price( $total_amt ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</p>
				<?php if ( ! empty( $pn['retry_url'] ) ) : ?>
					<a href="<?php echo esc_url( $pn['retry_url'] ); ?>" class="mt-3 inline-flex items-center gap-2 text-xs font-semibold text-amber-700 bg-amber-50 rounded-full px-3 py-1.5 hover:bg-amber-100 transition-colors">
						<?php pn_icon( 'credit-card', array( 'class' => 'w-3.5 h-3.5' ) ); ?>
						<?php esc_html_e( 'Completa il pagamento', 'pharmanow' ); ?>
					</a>
				<?php endif; ?>
				<?php if ( 'cod' !== $payment_id ) : ?>
					<div class="mt-3 inline-flex items-center gap-2 text-xs text-green-700 bg-green-50 rounded-full px-3 py-1">
						<?php pn_icon( 'shield-check', array( 'class' => 'w-3.5 h-3.5' ) ); ?>
						<?php esc_html_e( 'Transazione protetta SSL', 'pharmanow' ); ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $show_billing ) : ?>
				<div class="bg-white rounded-2xl shadow-sm p-6">
					<h3 class="font-bold text-gray-900 mb-3 inline-flex items-center gap-2">
						<?php pn_icon( 'file-text', array( 'class' => 'w-5 h-5 text-[#0B8894]' ) ); ?>
						<?php esc_html_e( 'Fatturazione', 'pharmanow' ); ?>
					</h3>
					<address class="not-italic text-sm text-gray-700 leading-relaxed">
						<?php echo wp_kses_post( $bill_addr ); ?>
					</address>
				</div>
			<?php endif; ?>

			<?php if ( $customer_note ) : ?>
				<div class="bg-amber-50 border border-amber-100 rounded-2xl p-5">
					<h3 class="font-semibold text-amber-900 mb-2 inline-flex items-center gap-2">
						<?php pn_icon( 'message-circle', array( 'class' => 'w-4 h-4' ) ); ?>
						<?php esc_html_e( 'Nota al tuo ordine', 'pharmanow' ); ?>
					</h3>
					<p class="text-sm text-amber-900/80 leading-relaxed whitespace-pre-line"><?php echo esc_html( $customer_note ); ?></p>
				</div>
			<?php endif; ?>

			<div class="bg-gradient-to-br from-[#0B8894]/10 to-[#43CCB1]/10 rounded-2xl p-6">
				<h3 class="font-bold text-gray-900 mb-2"><?php esc_html_e( 'Hai bisogno di aiuto?', 'pharmanow' ); ?></h3>
				<p class="text-sm text-gray-700 mb-4"><?php esc_html_e( 'Il nostro customer care risponde entro 24 ore lavorative.', 'pharmanow' ); ?></p>
				<div class="space-y-2">
					<?php $pn_subj = rawurlencode( sprintf( /* translators: %s = order number */ __( 'Richiesta info ordine #%s', 'pharmanow' ), $order->get_order_number() ) ); ?>
					<a href="mailto:<?php echo esc_attr( $settings['email'] ?? '' ); ?>?subject=<?php echo $pn_subj; ?>" class="flex items-center gap-3 text-sm text-gray-800 hover:text-[#0B8894] transition-colors">
						<?php pn_icon( 'mail', array( 'class' => 'w-4 h-4 text-[#0B8894]' ) ); ?>
						<span><?php echo esc_html( $settings['email'] ?? '' ); ?></span>
					</a>
					<?php if ( ! empty( $settings['phone'] ) ) : ?>
						<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $settings['phone'] ) ); ?>" class="flex items-center gap-3 text-sm text-gray-800 hover:text-[#0B8894] transition-colors">
							<?php pn_icon( 'phone', array( 'class' => 'w-4 h-4 text-[#0B8894]' ) ); ?>
							<span><?php echo esc_html( $settings['phone'] ); ?></span>
						</a>
					<?php endif; ?>
					<a href="<?php echo esc_url( home_url( '/faq' ) ); ?>" class="flex items-center gap-3 text-sm text-gray-800 hover:text-[#0B8894] transition-colors">
						<?php pn_icon( 'help-circle', array( 'class' => 'w-4 h-4 text-[#0B8894]' ) ); ?>
						<span><?php esc_html_e( 'Domande frequenti', 'pharmanow' ); ?></span>
					</a>
				</div>
			</div>
		</aside>
	</div>

	<?php // Trust strip. ?>
	<div class="mt-10 grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
		<?php
		$pn_trust = array(
			array( 'icon' => 'shield-check', 'title' => __( 'Arte + scienza', 'pharmanow' ),        'sub' => __( '25 artisti · 2 biologi marini', 'pharmanow' ) ),
			array( 'icon' => 'truck',        'title' => __( 'Spedizione 24/48h', 'pharmanow' ),      'sub' => __( 'Gratis sopra €30', 'pharmanow' ) ),
			array( 'icon' => 'rotate-ccw',   'title' => __( 'Reso entro 14 giorni', 'pharmanow' ),   'sub' => __( 'Codice del Consumo', 'pharmanow' ) ),
			array( 'icon' => 'lock',         'title' => __( 'Pagamenti protetti', 'pharmanow' ),     'sub' => __( 'SSL · 3D Secure', 'pharmanow' ) ),
		);
		foreach ( $pn_trust as $pn_t ) :
			?>
			<div class="bg-white rounded-2xl p-4 shadow-sm">
				<div class="w-10 h-10 mx-auto mb-2 rounded-xl bg-[#0B8894]/10 text-[#0B8894] flex items-center justify-center">
					<?php pn_icon( $pn_t['icon'], array( 'class' => 'w-5 h-5' ) ); ?>
				</div>
				<p class="text-xs sm:text-sm font-semibold text-gray-900"><?php echo esc_html( $pn_t['title'] ); ?></p>
				<p class="text-[11px] text-gray-500 mt-0.5"><?php echo esc_html( $pn_t['sub'] ); ?></p>
			</div>
		<?php endforeach; ?>
	</div>

</div>
