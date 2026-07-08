<?php
/**
 * Pharmanow — Audit Spedizioni.
 *
 * Strumento diagnostico per capire perché un ordine può ricevere spedizione
 * gratuita sotto la soglia attesa. Mostra in admin:
 *  - soglia letta dal Customizer + soglia minima dal codice
 *  - tutte le shipping zones WC con i loro metodi (id, enabled, cost,
 *    min_amount, requires)
 *  - tutti i coupon con flag "Concedi spedizione gratuita"
 *
 * Accessibile da: WooCommerce → Audit Spedizioni (capability manage_woocommerce).
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'admin_menu',
	function () {
		if ( ! function_exists( 'wc' ) ) {
			return;
		}
		add_submenu_page(
			'woocommerce',
			__( 'Audit Spedizioni', 'pharmanow' ),
			__( 'Audit Spedizioni', 'pharmanow' ),
			'manage_woocommerce',
			'pn-shipping-audit',
			'pn_render_shipping_audit_page'
		);
	}
);

function pn_render_shipping_audit_page(): void {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'Permesso negato.', 'pharmanow' ) );
	}

	$customizer_threshold = get_theme_mod( 'pn_free_shipping_threshold', null );
	$effective_threshold  = function_exists( 'pn_get_shipping_state' ) ? pn_get_shipping_state()['threshold'] : 29.90;

	echo '<div class="wrap"><h1>Pharmanow — Audit Spedizioni</h1>';
	echo '<p>Strumento diagnostico per capire perché potrebbero arrivare ordini con spedizione gratuita sotto soglia.</p>';

	// 1. Soglia.
	echo '<h2>1. Soglia spedizione gratuita</h2>';
	echo '<table class="widefat striped" style="max-width:720px"><tbody>';
	echo '<tr><td><strong>Soglia attesa (codice)</strong></td><td>€ 29,90 (default fallback)</td></tr>';
	echo '<tr><td><strong>Soglia da Customizer (<code>pn_free_shipping_threshold</code>)</strong></td><td>';
	if ( null === $customizer_threshold || '' === $customizer_threshold ) {
		echo '<em style="color:#666">non impostata → usa fallback</em>';
	} else {
		$val = (float) $customizer_threshold;
		echo '€ ' . esc_html( number_format( $val, 2, ',', '.' ) );
		if ( $val < 29.90 ) {
			echo ' <span style="color:#b32d2e;font-weight:bold">⚠ INFERIORE A 29,90 — sospetta causa</span>';
		}
	}
	echo '</td></tr>';
	echo '<tr><td><strong>Soglia effettiva applicata</strong></td><td>€ ' . esc_html( number_format( (float) $effective_threshold, 2, ',', '.' ) ) . '</td></tr>';
	echo '</tbody></table>';

	// 2. Shipping zones.
	echo '<h2>2. Metodi di spedizione configurati in WooCommerce</h2>';
	if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
		echo '<p><em>WooCommerce non disponibile.</em></p>';
	} else {
		$zones   = WC_Shipping_Zones::get_zones();
		$zones[] = array(
			'zone_name'        => __( 'Resto del mondo (zona 0)', 'pharmanow' ),
			'shipping_methods' => ( new WC_Shipping_Zone( 0 ) )->get_shipping_methods(),
		);

		echo '<table class="widefat striped"><thead><tr>';
		echo '<th>Zona</th><th>Metodo (id)</th><th>Titolo</th><th>Attivo</th><th>Cost</th><th>min_amount</th><th>requires</th><th>Note</th>';
		echo '</tr></thead><tbody>';

		$found_suspect = false;
		foreach ( $zones as $zone ) {
			$zone_name = isset( $zone['zone_name'] ) ? $zone['zone_name'] : __( '—', 'pharmanow' );
			if ( empty( $zone['shipping_methods'] ) ) {
				echo '<tr><td>' . esc_html( $zone_name ) . '</td><td colspan="7"><em>nessun metodo</em></td></tr>';
				continue;
			}
			foreach ( $zone['shipping_methods'] as $method ) {
				$id         = $method->id;
				$title      = $method->get_title();
				$enabled    = ( 'yes' === $method->enabled ) ? '✓' : '✗';
				$cost       = $method->get_option( 'cost', '' );
				$min_amount = $method->get_option( 'min_amount', '' );
				$requires   = $method->get_option( 'requires', '' );
				$note       = '';

				if ( 'free_shipping' === $id ) {
					$min_f = (float) $min_amount;
					if ( 'yes' === $method->enabled ) {
						if ( in_array( $requires, array( '', 'min_amount', 'either' ), true ) && $min_f < 29.90 ) {
							$note         .= '<span style="color:#b32d2e;font-weight:bold">⚠ Free shipping con soglia &lt; 29,90 o senza minimo. Causa probabile.</span>';
							$found_suspect = true;
						}
						if ( in_array( $requires, array( 'coupon', 'either' ), true ) ) {
							$note .= '<span style="color:#dba617"> · Attivabile via coupon</span>';
						}
					}
				}

				echo '<tr>';
				echo '<td>' . esc_html( $zone_name ) . '</td>';
				echo '<td><code>' . esc_html( $id ) . '</code></td>';
				echo '<td>' . esc_html( $title ) . '</td>';
				echo '<td>' . esc_html( $enabled ) . '</td>';
				echo '<td>' . esc_html( (string) $cost ) . '</td>';
				echo '<td>' . esc_html( (string) $min_amount ) . '</td>';
				echo '<td>' . esc_html( (string) $requires ) . '</td>';
				echo '<td>' . wp_kses_post( $note ) . '</td>';
				echo '</tr>';
			}
		}
		echo '</tbody></table>';
		if ( ! $found_suspect ) {
			echo '<p style="color:#0a7d2c">✓ Nessun metodo free_shipping configurato sotto soglia.</p>';
		}
	}

	// 3. Coupon free shipping.
	echo '<h2>3. Coupon che concedono spedizione gratuita</h2>';
	$coupons = get_posts(
		array(
			'post_type'      => 'shop_coupon',
			'post_status'    => array( 'publish' ),
			'posts_per_page' => -1,
			'meta_key'       => 'free_shipping',
			'meta_value'     => 'yes',
		)
	);
	if ( ! $coupons ) {
		echo '<p style="color:#0a7d2c">✓ Nessun coupon attivo con free shipping.</p>';
	} else {
		echo '<table class="widefat striped"><thead><tr>';
		echo '<th>Codice</th><th>Tipo sconto</th><th>Importo</th><th>Min spesa</th><th>Scadenza</th>';
		echo '</tr></thead><tbody>';
		foreach ( $coupons as $cpt ) {
			$c = new WC_Coupon( $cpt->ID );
			echo '<tr>';
			echo '<td><strong>' . esc_html( $c->get_code() ) . '</strong></td>';
			echo '<td>' . esc_html( $c->get_discount_type() ) . '</td>';
			echo '<td>' . esc_html( (string) $c->get_amount() ) . '</td>';
			echo '<td>' . esc_html( (string) $c->get_minimum_amount() ) . '</td>';
			echo '<td>' . esc_html( $c->get_date_expires() ? $c->get_date_expires()->date( 'Y-m-d' ) : '—' ) . '</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
		echo '<p><em>I clienti che applicano questi coupon ricevono spedizione gratis a prescindere dalla soglia.</em></p>';
	}

	// 4. Recap fix.
	echo '<h2>4. Fix attivo nel tema</h2>';
	echo '<p>Da <code>inc/checkout-fields.php</code>: il filtro <code>woocommerce_package_rates</code> rimuove tutti i metodi <code>free_shipping</code> proposti quando il subtotale è sotto la soglia, indipendentemente dalla loro configurazione o da coupon. Questo significa che <strong>anche se il problema sopra non viene risolto in admin, il cliente NON vedrà più spedizione gratuita sotto soglia</strong>.</p>';
	echo '<p>Caso limite gestito: se in una zona esistono SOLO metodi free_shipping e nessun flat_rate, il fix lascia comunque la free per non bloccare il checkout (warning loggato in <code>WooCommerce → Status → Logs</code> con source <code>pharmanow</code>).</p>';

	echo '</div>';
}
