<?php
/**
 * Order tracking — metabox admin + status custom + email transazionale.
 *
 * Salva i meta `_tracking_number`, `_tracking_provider`, `_tracking_url`
 * letti dalla pagina pubblica /traccia-ordine/ (inc/account/track-order.php)
 * e dalla view ordine (inc/order/view.php). Registra anche gli status
 * `wc-shipped` e `wc-delivered` già referenziati nei template ma mai
 * registrati prima.
 *
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lista corrieri supportati. Le chiavi sono quelle accettate da
 * pn_track_order_build_tracking_url(); modificare in sync.
 *
 * @return array<string,string>
 */
function pn_tracking_providers(): array {
	return array(
		'gls'      => 'GLS Italy',
		'brt'      => 'BRT (Bartolini)',
		'sda'      => 'SDA',
		'poste'    => 'Poste Italiane',
		'fedex'    => 'FedEx',
		'dhl'      => 'DHL',
		'ups'      => 'UPS',
		'tnt'      => 'TNT',
	);
}

/* ============================================================
   Custom order statuses: shipped, delivered.
   ============================================================ */

add_action(
	'init',
	function () {
		register_post_status(
			'wc-shipped',
			array(
				'label'                     => _x( 'Spedito', 'Order status', 'pharmanow' ),
				'public'                    => true,
				'show_in_admin_status_list' => true,
				'show_in_admin_all_list'    => true,
				/* translators: %s: order count */
				'label_count'               => _n_noop( 'Spedito <span class="count">(%s)</span>', 'Spediti <span class="count">(%s)</span>', 'pharmanow' ),
			)
		);
		register_post_status(
			'wc-delivered',
			array(
				'label'                     => _x( 'Consegnato', 'Order status', 'pharmanow' ),
				'public'                    => true,
				'show_in_admin_status_list' => true,
				'show_in_admin_all_list'    => true,
				/* translators: %s: order count */
				'label_count'               => _n_noop( 'Consegnato <span class="count">(%s)</span>', 'Consegnati <span class="count">(%s)</span>', 'pharmanow' ),
			)
		);
	}
);

add_filter(
	'wc_order_statuses',
	function ( $statuses ) {
		$new = array();
		foreach ( $statuses as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'wc-processing' === $key ) {
				$new['wc-shipped']   = _x( 'Spedito', 'Order status', 'pharmanow' );
				$new['wc-delivered'] = _x( 'Consegnato', 'Order status', 'pharmanow' );
			}
		}
		if ( ! isset( $new['wc-shipped'] ) ) {
			$new['wc-shipped']   = _x( 'Spedito', 'Order status', 'pharmanow' );
			$new['wc-delivered'] = _x( 'Consegnato', 'Order status', 'pharmanow' );
		}
		return $new;
	}
);

// Render bulk actions e azioni rapide includendo gli status custom.
add_filter(
	'woocommerce_admin_order_actions',
	function ( $actions, $order ) {
		if ( $order && 'processing' === $order->get_status() ) {
			$actions['pn_mark_shipped'] = array(
				'url'    => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=shipped&order_id=' . $order->get_id() ), 'woocommerce-mark-order-status' ),
				'name'   => __( 'Marca come spedito', 'pharmanow' ),
				'action' => 'shipped',
			);
		}
		return $actions;
	},
	10,
	2
);

// Lista di status considerati "paid" — shipped e delivered derivano da processing.
add_filter(
	'woocommerce_order_is_paid_statuses',
	function ( $statuses ) {
		$statuses[] = 'shipped';
		$statuses[] = 'delivered';
		return $statuses;
	}
);

/* ============================================================
   Admin metabox sull'edit-ordine (HPOS + legacy).
   ============================================================ */

add_action(
	'add_meta_boxes',
	function () {
		$hpos_screen = function_exists( 'wc_get_page_screen_id' )
			? wc_get_page_screen_id( 'shop-order' )
			: null;

		$targets = array_filter( array( 'shop_order', $hpos_screen ) );

		foreach ( $targets as $target ) {
			add_meta_box(
				'pn_order_tracking',
				__( 'Tracking spedizione', 'pharmanow' ),
				'pn_order_tracking_metabox_render',
				$target,
				'side',
				'high'
			);
		}
	}
);

/**
 * Render del metabox.
 *
 * @param WP_Post|WC_Order $post_or_order
 */
function pn_order_tracking_metabox_render( $post_or_order ): void {
	$order = $post_or_order instanceof WC_Order
		? $post_or_order
		: wc_get_order( $post_or_order );

	if ( ! $order instanceof WC_Order ) {
		echo '<p>' . esc_html__( 'Ordine non valido.', 'pharmanow' ) . '</p>';
		return;
	}

	$number   = (string) $order->get_meta( '_tracking_number' );
	$provider = (string) $order->get_meta( '_tracking_provider' );
	$url      = (string) $order->get_meta( '_tracking_url' );
	$status   = $order->get_status();

	wp_nonce_field( 'pn_save_tracking_' . $order->get_id(), 'pn_tracking_nonce' );
	?>
	<style>
		#pn_order_tracking .pn-field { margin: 10px 0; }
		#pn_order_tracking label { display:block; font-weight:600; font-size:12px; margin-bottom:4px; }
		#pn_order_tracking input[type=text], #pn_order_tracking input[type=url], #pn_order_tracking select { width:100%; }
		#pn_order_tracking .pn-help { color:#646970; font-size:11px; margin-top:3px; }
		#pn_order_tracking .pn-check { margin: 8px 0; display:flex; align-items:flex-start; gap:6px; font-size:12px; line-height:1.4; }
		#pn_order_tracking .pn-check input { margin-top:2px; }
		#pn_order_tracking .pn-current-link { display:inline-block; margin-top:4px; font-size:11px; }
	</style>

	<div class="pn-field">
		<label for="pn_tracking_provider"><?php esc_html_e( 'Corriere', 'pharmanow' ); ?></label>
		<select name="pn_tracking_provider" id="pn_tracking_provider">
			<option value=""><?php esc_html_e( '— Seleziona corriere —', 'pharmanow' ); ?></option>
			<?php foreach ( pn_tracking_providers() as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $provider, $key ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>

	<div class="pn-field">
		<label for="pn_tracking_number"><?php esc_html_e( 'Numero di tracking', 'pharmanow' ); ?></label>
		<input type="text" name="pn_tracking_number" id="pn_tracking_number" value="<?php echo esc_attr( $number ); ?>" placeholder="<?php esc_attr_e( 'es. ABC123456789', 'pharmanow' ); ?>">
	</div>

	<div class="pn-field">
		<label for="pn_tracking_url"><?php esc_html_e( 'URL tracking (opzionale)', 'pharmanow' ); ?></label>
		<input type="url" name="pn_tracking_url" id="pn_tracking_url" value="<?php echo esc_attr( $url ); ?>" placeholder="https://...">
		<p class="pn-help"><?php esc_html_e( 'Lascia vuoto per usare il link standard del corriere.', 'pharmanow' ); ?></p>
		<?php
		if ( '' === $url && '' !== $number && '' !== $provider && function_exists( 'pn_track_order_build_tracking_url' ) ) {
			$preview = pn_track_order_build_tracking_url( $provider, $number );
			if ( $preview ) {
				echo '<a class="pn-current-link" href="' . esc_url( $preview ) . '" target="_blank" rel="noopener">' . esc_html__( 'Anteprima link →', 'pharmanow' ) . '</a>';
			}
		} elseif ( $url ) {
			echo '<a class="pn-current-link" href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html__( 'Apri link corrente →', 'pharmanow' ) . '</a>';
		}
		?>
	</div>

	<?php
	$can_mark_shipped = ! in_array( $status, array( 'shipped', 'delivered', 'completed', 'refunded', 'cancelled', 'failed' ), true );
	$already_sent     = (bool) $order->get_meta( '_pn_tracking_email_sent' );
	?>

	<?php if ( $can_mark_shipped ) : ?>
		<label class="pn-check">
			<input type="checkbox" name="pn_tracking_mark_shipped" value="1" checked>
			<span><?php esc_html_e( 'Marca ordine come "Spedito"', 'pharmanow' ); ?></span>
		</label>
	<?php endif; ?>

	<label class="pn-check">
		<input type="checkbox" name="pn_tracking_send_email" value="1" <?php checked( ! $already_sent ); ?>>
		<span>
			<?php
			echo $already_sent
				? esc_html__( 'Re-invia email tracking al cliente', 'pharmanow' )
				: esc_html__( 'Invia email tracking al cliente', 'pharmanow' );
			?>
		</span>
	</label>
	<?php
}

/* ============================================================
   Save handler — HPOS + legacy usano lo stesso hook.
   ============================================================ */

add_action(
	'woocommerce_process_shop_order_meta',
	function ( $order_id ) {
		pn_order_tracking_save( (int) $order_id );
	},
	30
);

/**
 * Persiste i meta del tracking dal metabox e applica side-effects.
 */
function pn_order_tracking_save( int $order_id ): void {
	if ( ! isset( $_POST['pn_tracking_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pn_tracking_nonce'] ) ), 'pn_save_tracking_' . $order_id ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_shop_orders' ) ) {
		return;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order instanceof WC_Order ) {
		return;
	}

	$old_number   = (string) $order->get_meta( '_tracking_number' );
	$old_provider = (string) $order->get_meta( '_tracking_provider' );

	$providers   = pn_tracking_providers();
	$new_provider = isset( $_POST['pn_tracking_provider'] ) ? sanitize_key( wp_unslash( $_POST['pn_tracking_provider'] ) ) : '';
	if ( '' !== $new_provider && ! isset( $providers[ $new_provider ] ) ) {
		$new_provider = '';
	}
	$new_number = isset( $_POST['pn_tracking_number'] )
		? trim( sanitize_text_field( wp_unslash( $_POST['pn_tracking_number'] ) ) )
		: '';
	$new_url    = isset( $_POST['pn_tracking_url'] )
		? esc_url_raw( trim( (string) wp_unslash( $_POST['pn_tracking_url'] ) ) )
		: '';

	$order->update_meta_data( '_tracking_provider', $new_provider );
	$order->update_meta_data( '_tracking_number', $new_number );
	$order->update_meta_data( '_tracking_url', $new_url );
	$order->save();

	$changed = ( $new_number !== $old_number ) || ( $new_provider !== $old_provider );

	// Status transition: solo se l'admin l'ha chiesto e l'ordine è in stato
	// che lo permette (no completed/refunded/cancelled per evitare regressioni).
	if ( ! empty( $_POST['pn_tracking_mark_shipped'] )
		&& '' !== $new_number
		&& ! in_array( $order->get_status(), array( 'shipped', 'delivered', 'completed', 'refunded', 'cancelled', 'failed' ), true )
	) {
		$order->update_status( 'shipped', __( 'Tracking aggiunto, ordine marcato come spedito.', 'pharmanow' ) );
	}

	// Nota interna admin con i dati del tracking.
	if ( $changed && '' !== $new_number ) {
		$provider_label = $providers[ $new_provider ] ?? $new_provider;
		$order->add_order_note(
			sprintf(
				/* translators: 1: provider, 2: number */
				__( 'Tracking aggiornato: %1$s — %2$s', 'pharmanow' ),
				$provider_label ?: __( 'Corriere non specificato', 'pharmanow' ),
				$new_number
			)
		);
	}

	// Email cliente.
	if ( ! empty( $_POST['pn_tracking_send_email'] ) && '' !== $new_number ) {
		pn_send_tracking_email( $order );
		$order->update_meta_data( '_pn_tracking_email_sent', current_time( 'mysql' ) );
		$order->save();
	}
}

/* ============================================================
   Email transazionale "Il tuo ordine è in viaggio".
   ============================================================ */

/**
 * Invia email tracking al cliente. Usa pn_email_wrap() per consistenza brand.
 */
function pn_send_tracking_email( WC_Order $order ): bool {
	if ( ! function_exists( 'pn_email_wrap' ) ) {
		return false;
	}

	$to = $order->get_billing_email();
	if ( ! $to || ! is_email( $to ) ) {
		return false;
	}

	$number   = (string) $order->get_meta( '_tracking_number' );
	$provider = (string) $order->get_meta( '_tracking_provider' );
	$url      = (string) $order->get_meta( '_tracking_url' );

	if ( '' === $url && '' !== $number && '' !== $provider && function_exists( 'pn_track_order_build_tracking_url' ) ) {
		$url = pn_track_order_build_tracking_url( $provider, $number );
	}

	$providers      = pn_tracking_providers();
	$provider_label = $providers[ $provider ] ?? ( $provider ? ucfirst( $provider ) : '' );
	$first_name     = trim( (string) $order->get_billing_first_name() );
	$order_number   = $order->get_order_number();
	$track_page_url = home_url( '/traccia-ordine/' );
	$site_name      = get_bloginfo( 'name' );

	$greeting = $first_name
		? sprintf( __( 'Ciao %s,', 'pharmanow' ), $first_name )
		: __( 'Ciao,', 'pharmanow' );

	ob_start();
	?>
	<h1 style="margin:0 0 16px 0;font-size:22px;font-weight:700;color:#0a6e9e;">
		<?php esc_html_e( 'Il tuo ordine è in viaggio', 'pharmanow' ); ?>
	</h1>
	<p style="margin:0 0 12px 0;"><?php echo esc_html( $greeting ); ?></p>
	<p style="margin:0 0 16px 0;">
		<?php
		printf(
			/* translators: %s = order number */
			esc_html__( 'Buone notizie: il tuo ordine #%s è stato spedito ed è in viaggio verso di te.', 'pharmanow' ),
			'<strong>' . esc_html( $order_number ) . '</strong>'
		);
		?>
	</p>

	<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width:100%;margin:20px 0;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
		<?php if ( $provider_label ) : ?>
			<tr>
				<td style="padding:12px 16px;font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;border-bottom:1px solid #f1f5f9;width:120px;">
					<?php esc_html_e( 'Corriere', 'pharmanow' ); ?>
				</td>
				<td style="padding:12px 16px;font-size:14px;color:#0f172a;font-weight:600;border-bottom:1px solid #f1f5f9;">
					<?php echo esc_html( $provider_label ); ?>
				</td>
			</tr>
		<?php endif; ?>
		<tr>
			<td style="padding:12px 16px;font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;">
				<?php esc_html_e( 'Tracking', 'pharmanow' ); ?>
			</td>
			<td style="padding:12px 16px;font-size:14px;color:#0f172a;font-weight:600;font-family:ui-monospace,'SF Mono',Menlo,Consolas,monospace;">
				<?php echo esc_html( $number ); ?>
			</td>
		</tr>
	</table>

	<?php if ( $url ) : ?>
		<?php echo pn_email_button( $url, __( 'Traccia la spedizione', 'pharmanow' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php endif; ?>

	<p style="margin:24px 0 8px 0;font-size:13px;color:#475569;">
		<?php esc_html_e( 'Puoi controllare lo stato in qualsiasi momento sulla nostra pagina di tracciamento:', 'pharmanow' ); ?>
	</p>
	<p style="margin:0 0 24px 0;font-size:13px;">
		<a href="<?php echo esc_url( $track_page_url ); ?>" style="color:#0a6e9e;text-decoration:none;font-weight:600;">
			<?php echo esc_html( $track_page_url ); ?>
		</a>
	</p>

	<p style="margin:24px 0 0 0;font-size:13px;color:#64748b;line-height:1.6;">
		<?php esc_html_e( 'Se non riconosci questo ordine o hai bisogno di aiuto, rispondi a questa email: siamo qui per te.', 'pharmanow' ); ?>
	</p>
	<?php
	$body = (string) ob_get_clean();

	$subject = sprintf(
		/* translators: 1: site, 2: order */
		__( '[%1$s] Il tuo ordine #%2$s è stato spedito', 'pharmanow' ),
		$site_name,
		$order_number
	);

	$html    = pn_email_wrap( $body, $subject );
	$headers = array( 'Content-Type: text/html; charset=UTF-8' );

	return wp_mail( $to, $subject, $html, $headers );
}
