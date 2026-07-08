<?php
/**
 * Checkout — campi custom (fatturazione, scontrino con CF, fattura aziendale).
 * Salvati come post_meta dell'ordine.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Spedizione: single source of truth.
 *
 * Comportamento atteso al checkout: il cliente vede UNA sola opzione di
 * spedizione, coerente con la soglia (`pn_free_shipping_threshold`, default
 * 29,90 €). Mai due opzioni "gratuita" / "gratuita" che confondono.
 *
 * Sopra soglia → tieni solo `free_shipping`, rimuovi i flat_rate (a pagamento).
 * Sotto soglia → tieni solo i flat_rate, rimuovi i `free_shipping` (anche se
 *   proposti da coupon free-shipping o zone mal configurate).
 *
 * Fallback safety: se la rimozione lascerebbe il package vuoto, NON tocchiamo
 * nulla — checkout senza opzioni è peggio di un'opzione incoerente. Loggiamo
 * un warning su WC logs per audit.
 *
 * Hook: woocommerce_package_rates.
 */
add_filter(
	'woocommerce_package_rates',
	function ( $rates, $package ) {
		if ( ! function_exists( 'pn_get_shipping_state' ) || empty( $rates ) ) {
			return $rates;
		}
		$state    = pn_get_shipping_state();
		$is_free  = ! empty( $state['is_free'] );
		$keep_id  = $is_free ? 'free_shipping' : null; // sopra soglia → solo free
		$drop_ids = $is_free ? array( 'flat_rate' ) : array( 'free_shipping' );

		// Conta cosa resterebbe dopo la rimozione.
		$would_remain = 0;
		foreach ( $rates as $rate ) {
			if ( ! in_array( $rate->method_id, $drop_ids, true ) ) {
				$would_remain++;
			}
		}

		if ( 0 === $would_remain ) {
			if ( function_exists( 'wc_get_logger' ) ) {
				wc_get_logger()->warning(
					sprintf(
						'[pn-shipping] is_free=%s, subtotal=%.2f, threshold=%.2f — la rimozione di [%s] svuoterebbe il package. Lasciato invariato.',
						$is_free ? 'true' : 'false',
						$state['subtotal'],
						$state['threshold'],
						implode( ',', $drop_ids )
					),
					array( 'source' => 'pharmanow' )
				);
			}
			return $rates;
		}

		foreach ( $rates as $key => $rate ) {
			if ( in_array( $rate->method_id, $drop_ids, true ) ) {
				unset( $rates[ $key ] );
			}
		}
		return $rates;
	},
	10,
	2
);

/**
 * Aggiunge campi al checkout: invoice_type radio + campi conditional.
 * I campi sono renderizzati nel template custom form-checkout.php.
 */
add_filter(
	'woocommerce_checkout_fields',
	function ( $fields ) {
		// Tipo documento fiscale
		$fields['order']['pn_invoice_type'] = array(
			'type'     => 'radio',
			'label'    => __( 'Documento fiscale', 'pharmanow' ),
			'required' => true,
			'options'  => array(
				'scontrino'    => __( 'Scontrino fiscale', 'pharmanow' ),
				'scontrino_cf' => __( 'Scontrino con codice fiscale', 'pharmanow' ),
				'fattura'      => __( 'Fattura', 'pharmanow' ),
			),
			'default'  => 'scontrino',
			'priority' => 5,
			'class'    => array( 'pn-invoice-type' ),
		);

		// Codice fiscale per scontrino_cf
		$fields['order']['pn_codice_fiscale'] = array(
			'type'              => 'text',
			'label'             => __( 'Codice fiscale', 'pharmanow' ),
			'placeholder'       => 'RSSMRA85M01H501Z',
			'required'          => false,
			'maxlength'         => 16,
			'priority'          => 10,
			'class'             => array( 'pn-cf-field' ),
			'custom_attributes' => array( 'style' => 'text-transform:uppercase' ),
		);

		// Fattura: ragione sociale, P.IVA, CF azienda, SDI/PEC, email
		$fields['order']['pn_ragione_sociale'] = array(
			'type'     => 'text',
			'label'    => __( 'Ragione sociale', 'pharmanow' ),
			'required' => false,
			'priority' => 20,
			'class'    => array( 'pn-fattura-field' ),
		);
		$fields['order']['pn_partita_iva'] = array(
			'type'      => 'text',
			'label'     => __( 'Partita IVA', 'pharmanow' ),
			'required'  => false,
			'maxlength' => 11,
			'priority'  => 21,
			'class'     => array( 'pn-fattura-field' ),
		);
		$fields['order']['pn_cf_azienda'] = array(
			'type'              => 'text',
			'label'             => __( 'Codice fiscale azienda', 'pharmanow' ),
			'required'          => false,
			'maxlength'         => 16,
			'priority'          => 22,
			'class'             => array( 'pn-fattura-field' ),
			'custom_attributes' => array( 'style' => 'text-transform:uppercase' ),
		);
		$fields['order']['pn_sdi_pec'] = array(
			'type'        => 'text',
			'label'       => __( 'Codice SDI o PEC', 'pharmanow' ),
			'placeholder' => '0000000 o pec@example.it',
			'required'    => false,
			'priority'    => 23,
			'class'       => array( 'pn-fattura-field' ),
		);
		$fields['order']['pn_email_fattura'] = array(
			'type'     => 'email',
			'label'    => __( 'Email copia fattura (facoltativo)', 'pharmanow' ),
			'required' => false,
			'priority' => 24,
			'class'    => array( 'pn-fattura-field' ),
		);

		// Marketing consent
		$fields['order']['pn_marketing_consent'] = array(
			'type'     => 'checkbox',
			'label'    => __( 'Acconsento a ricevere offerte e promozioni via email', 'pharmanow' ),
			'required' => false,
			'priority' => 90,
			'class'    => array( 'pn-marketing-field' ),
		);

		// Rimuoviamo company di default WC (lo gestiamo via P.IVA in fattura)
		unset( $fields['billing']['billing_company'] );

		// Rendiamo phone obbligatorio
		if ( isset( $fields['billing']['billing_phone'] ) ) {
			$fields['billing']['billing_phone']['required'] = true;
		}

		return $fields;
	}
);

/**
 * Validation custom: se invoice_type è scontrino_cf richiediamo CF; se fattura
 * richiediamo ragione sociale + P.IVA + CF azienda + SDI/PEC.
 */
add_action(
	'woocommerce_checkout_process',
	function () {
		$type = isset( $_POST['pn_invoice_type'] ) ? sanitize_text_field( wp_unslash( $_POST['pn_invoice_type'] ) ) : 'scontrino';

		if ( 'scontrino_cf' === $type ) {
			if ( empty( $_POST['pn_codice_fiscale'] ) ) {
				wc_add_notice( __( 'Inserisci il codice fiscale.', 'pharmanow' ), 'error' );
			}
		} elseif ( 'fattura' === $type ) {
			$required_fields = array(
				'pn_ragione_sociale' => __( 'Ragione sociale', 'pharmanow' ),
				'pn_partita_iva'     => __( 'Partita IVA', 'pharmanow' ),
				'pn_cf_azienda'      => __( 'Codice fiscale azienda', 'pharmanow' ),
				'pn_sdi_pec'         => __( 'Codice SDI o PEC', 'pharmanow' ),
			);
			foreach ( $required_fields as $field => $label ) {
				if ( empty( $_POST[ $field ] ) ) {
					wc_add_notice( sprintf( /* translators: %s = nome campo */ __( '%s è obbligatorio per fattura.', 'pharmanow' ), $label ), 'error' );
				}
			}
			// Campo facoltativo, ma se compilato dev'essere un'email valida:
			// col form in novalidate questo è l'unico controllo di formato
			// lato server (il check JS è bypassabile riaprendo l'accordion).
			if ( ! empty( $_POST['pn_email_fattura'] ) && ! is_email( sanitize_text_field( wp_unslash( $_POST['pn_email_fattura'] ) ) ) ) {
				wc_add_notice( __( "L'email per l'invio della fattura non è un indirizzo valido.", 'pharmanow' ), 'error' );
			}
		}
	}
);

/**
 * Salva i meta sull'ordine + copia billing→shipping se "spedisci a indirizzo
 * diverso" non è spuntato.
 *
 * NB: HPOS-friendly. Useremo `$order->update_meta_data()` (write su
 * `wp_wc_orders_meta`) invece di `update_post_meta()` che con HPOS scrive
 * solo su `wp_postmeta` e non viene visto dall'admin order screen.
 *
 * Hook: `woocommerce_checkout_create_order` viene chiamato PRIMA del save,
 * quindi non serve chiamare `$order->save()` qui — WC lo fa dopo.
 */
add_action(
	'woocommerce_checkout_create_order',
	function ( $order, $data ) {
		// 1. Custom meta: documento fiscale, CF, fattura, marketing consent.
		$meta_keys = array(
			'pn_invoice_type',
			'pn_codice_fiscale',
			'pn_ragione_sociale',
			'pn_partita_iva',
			'pn_cf_azienda',
			'pn_sdi_pec',
			'pn_email_fattura',
		);
		foreach ( $meta_keys as $key ) {
			if ( isset( $_POST[ $key ] ) && '' !== $_POST[ $key ] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$order->update_meta_data( '_' . $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			}
		}
		if ( ! empty( $_POST['pn_marketing_consent'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$order->update_meta_data( '_pn_marketing_consent', 1 );
		}

		// 2. Shipping = billing quando "spedisci a indirizzo diverso" è OFF.
		// Quando il template non rende i campi shipping_*, WC li lascia vuoti
		// → ordine senza indirizzo di spedizione. Forzeremo la copia.
		$ship_to_different = ! empty( $_POST['ship_to_different_address'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! $ship_to_different ) {
			$order->set_shipping_first_name( $order->get_billing_first_name() );
			$order->set_shipping_last_name( $order->get_billing_last_name() );
			$order->set_shipping_company( $order->get_billing_company() );
			$order->set_shipping_address_1( $order->get_billing_address_1() );
			$order->set_shipping_address_2( $order->get_billing_address_2() );
			$order->set_shipping_city( $order->get_billing_city() );
			$order->set_shipping_state( $order->get_billing_state() );
			$order->set_shipping_postcode( $order->get_billing_postcode() );
			$order->set_shipping_country( $order->get_billing_country() ?: 'IT' );
			$order->set_shipping_phone( $order->get_billing_phone() );
		}
	},
	10,
	2
);

/**
 * Backfill HPOS: re-salva i meta usando update_meta_data() anche per ordini
 * che vengono creati da gateway esterni (PayPal Smart Buttons, Stripe Express)
 * che bypassano `woocommerce_checkout_create_order` ma triggerano comunque
 * `woocommerce_checkout_update_order_meta` (legacy hook).
 */
add_action(
	'woocommerce_checkout_update_order_meta',
	function ( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}
		$meta_keys = array(
			'pn_invoice_type',
			'pn_codice_fiscale',
			'pn_ragione_sociale',
			'pn_partita_iva',
			'pn_cf_azienda',
			'pn_sdi_pec',
			'pn_email_fattura',
		);
		$dirty = false;
		foreach ( $meta_keys as $key ) {
			if ( isset( $_POST[ $key ] ) && '' !== $_POST[ $key ] && ! $order->meta_exists( '_' . $key ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$order->update_meta_data( '_' . $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$dirty = true;
			}
		}
		if ( ! empty( $_POST['pn_marketing_consent'] ) && ! $order->meta_exists( '_pn_marketing_consent' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$order->update_meta_data( '_pn_marketing_consent', 1 );
			$dirty = true;
		}
		if ( $dirty ) {
			$order->save();
		}
	}
);

/**
 * Display dei meta nel pannello admin dell'ordine.
 */
add_action(
	'woocommerce_admin_order_data_after_billing_address',
	function ( $order ) {
		$type = $order->get_meta( '_pn_invoice_type' );
		if ( ! $type ) {
			return;
		}
		echo '<p><strong>' . esc_html__( 'Documento fiscale', 'pharmanow' ) . ':</strong> ';
		switch ( $type ) {
			case 'scontrino':
				esc_html_e( 'Scontrino', 'pharmanow' );
				break;
			case 'scontrino_cf':
				esc_html_e( 'Scontrino con CF', 'pharmanow' );
				echo ' — CF: ' . esc_html( $order->get_meta( '_pn_codice_fiscale' ) );
				break;
			case 'fattura':
				esc_html_e( 'Fattura', 'pharmanow' );
				echo '<br>' . esc_html__( 'Ragione sociale', 'pharmanow' ) . ': ' . esc_html( $order->get_meta( '_pn_ragione_sociale' ) );
				echo '<br>' . esc_html__( 'P.IVA', 'pharmanow' ) . ': ' . esc_html( $order->get_meta( '_pn_partita_iva' ) );
				echo '<br>' . esc_html__( 'CF azienda', 'pharmanow' ) . ': ' . esc_html( $order->get_meta( '_pn_cf_azienda' ) );
				echo '<br>' . esc_html__( 'SDI/PEC', 'pharmanow' ) . ': ' . esc_html( $order->get_meta( '_pn_sdi_pec' ) );
				$email_fat = $order->get_meta( '_pn_email_fattura' );
				if ( $email_fat ) {
					echo '<br>' . esc_html__( 'Email copia', 'pharmanow' ) . ': ' . esc_html( $email_fat );
				}
				break;
		}
		echo '</p>';
	}
);

/**
 * Pay-for-order bypass login (stile Shopify draft order).
 *
 * Quando il customer-care manda al cliente il link
 * `/pagamento/order-pay/{id}/?pay_for_order=true&key=wc_order_xxx`,
 * WooCommerce di default chiede login se l'ordine appartiene a un utente
 * registrato (anche con `key` valida), tramite il check in
 * `WC_Shortcode_Checkout::pay_action()`.
 *
 * Qui autorizziamo la richiesta per quella SINGOLA pagina settando il
 * `current_user` come owner dell'ordine — solo in-memory, niente cookie auth.
 * Sicurezza: equivalente al token-based access (key = secret di 16 char).
 *
 * Limita a:
 *  - is_checkout_pay_page()
 *  - utente non loggato
 *  - key match esatto contro $order->get_order_key()
 *  - ordine ha un customer_id reale (altrimenti già funziona)
 */
add_action(
	'wp',
	function () {
		if ( is_user_logged_in() ) {
			return;
		}
		if ( ! function_exists( 'is_checkout_pay_page' ) || ! is_checkout_pay_page() ) {
			return;
		}
		if ( empty( $_GET['key'] ) || empty( $_GET['pay_for_order'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		global $wp;
		$order_id = isset( $wp->query_vars['order-pay'] ) ? absint( $wp->query_vars['order-pay'] ) : 0;
		if ( ! $order_id ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$key = sanitize_text_field( wp_unslash( (string) $_GET['key'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! hash_equals( (string) $order->get_order_key(), $key ) ) {
			return;
		}

		$customer_id = (int) $order->get_user_id();
		if ( ! $customer_id ) {
			return;
		}

		// Auth temporanea solo per questa request: WC pay_action vede l'utente loggato
		// come owner dell'ordine → bypass del login form.
		wp_set_current_user( $customer_id );
	},
	5
);

/**
 * Fragment AJAX al checkout — tieni in sync UI con cart senza refresh pagina.
 *
 * `update_order_review` viene triggerato quando: cliente cambia qty da
 * mini-cart, applica/rimuove coupon, cambia indirizzo. Senza questi fragment
 * il riquadro destro (summary) e le shipping methods restano stale finché
 * non si refresha — bug visibile quando il subtotale supera/scende sotto
 * la soglia free-shipping e l'opzione mostrata non corrisponde più al carrello.
 *
 * Selector CSS:
 *  - `.pn-checkout-summary-fragment` → riquadro riepilogo a destra
 *  - `#pn-shipping-methods`           → blocco metodi spedizione
 */
add_filter(
	'woocommerce_update_order_review_fragments',
	function ( $fragments ) {
		ob_start();
		get_template_part( 'template-parts/checkout/order-summary' );
		$summary_html = ob_get_clean();
		if ( $summary_html ) {
			$fragments['.pn-checkout-summary-fragment'] = $summary_html;
		}

		ob_start();
		get_template_part( 'template-parts/checkout/shipping-methods' );
		$shipping_html = ob_get_clean();
		if ( $shipping_html ) {
			$fragments['#pn-shipping-methods'] = $shipping_html;
		}

		return $fragments;
	}
);
