<?php
/**
 * WooCommerce String Translations
 *
 * Translates all WooCommerce frontend strings to Italian
 * using the gettext filter. This ensures the theme works
 * in Italian regardless of the WC language pack status.
 *
 * @package BizStudio
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'gettext', 'bizstudio_translate_strings', 20, 3 );

function bizstudio_translate_strings( string $translated, string $text, string $domain ): string {
	// Only filter WooCommerce strings
	if ( $domain !== 'woocommerce' ) {
		return $translated;
	}

	static $translations = null;

	if ( $translations === null ) {
		$translations = [
			// Product page
			'Choose an option'                => 'Scegli un\'opzione',
			'Choose an Option'                => 'Scegli un\'opzione',
			'Clear'                           => 'Cancella',
			'clear'                           => 'cancella',
			'Clear options'                   => 'Cancella selezione',
			'Add to cart'                     => 'Aggiungi al carrello',
			'Read more'                       => 'Scopri di piu',
			'Select options'                  => 'Seleziona opzioni',
			'View products'                   => 'Vedi prodotti',
			'Out of stock'                    => 'Esaurito',
			'In stock'                        => 'Disponibile',
			'%s in stock'                     => '%s disponibili',
			'Only %s left in stock'           => 'Solo %s rimasti',
			'Available on backorder'          => 'Disponibile su ordinazione',
			'SKU:'                            => 'COD:',
			'SKU'                             => 'COD',
			'Category:'                       => 'Categoria:',
			'Categories:'                     => 'Categorie:',
			'Tag:'                            => 'Tag:',
			'Tags:'                           => 'Tag:',
			'Description'                     => 'Descrizione',
			'Additional information'          => 'Informazioni aggiuntive',
			'Reviews'                         => 'Recensioni',
			'reviews'                         => 'recensioni',
			'Review'                          => 'Recensione',
			'Related products'                => 'Prodotti correlati',
			'You may also like&hellip;'       => 'Potrebbe piacerti anche&hellip;',
			'Sale!'                           => 'Offerta!',
			'New!'                            => 'Nuovo!',

			// Cart
			'View cart'                       => 'Vedi carrello',
			'Cart'                            => 'Carrello',
			'Cart totals'                     => 'Totale carrello',
			'Subtotal'                        => 'Subtotale',
			'Total'                           => 'Totale',
			'Coupon:'                         => 'Coupon:',
			'Coupon code'                     => 'Codice coupon',
			'Apply coupon'                    => 'Applica coupon',
			'Remove this item'                => 'Rimuovi',
			'Update cart'                     => 'Aggiorna carrello',
			'Proceed to checkout'             => 'Vai al checkout',
			'Your cart is currently empty.'   => 'Il tuo carrello e vuoto.',
			'Return to shop'                  => 'Torna allo shop',
			'Shipping'                        => 'Spedizione',
			'Free shipping'                   => 'Spedizione gratuita',
			'Flat rate'                       => 'Tariffa fissa',
			'Calculate shipping'              => 'Calcola spedizione',

			// Checkout
			'Checkout'                        => 'Checkout',
			'Billing details'                 => 'Dati di fatturazione',
			'Shipping details'                => 'Dati di spedizione',
			'Your order'                      => 'Il tuo ordine',
			'Place order'                     => 'Effettua ordine',
			'Order notes'                     => 'Note ordine',
			'Notes about your order, e.g. special notes for delivery.' => 'Note sul tuo ordine, es. istruzioni per la consegna.',

			// Form fields
			'First name'                      => 'Nome',
			'Last name'                       => 'Cognome',
			'Company name'                    => 'Azienda',
			'Country / Region'                => 'Paese / Regione',
			'Street address'                  => 'Indirizzo',
			'Apartment, suite, unit, etc.'    => 'Interno, scala, ecc.',
			'Town / City'                     => 'Citta',
			'State / County'                  => 'Provincia',
			'Postcode / ZIP'                  => 'CAP',
			'Phone'                           => 'Telefono',
			'Email address'                   => 'Email',
			'Email'                           => 'Email',
			'Password'                        => 'Password',

			// Account
			'My account'                      => 'Il mio account',
			'Dashboard'                       => 'Panoramica',
			'Orders'                          => 'Ordini',
			'Downloads'                       => 'Download',
			'Addresses'                       => 'Indirizzi',
			'Account details'                 => 'Dettagli account',
			'Log out'                         => 'Esci',
			'Login'                           => 'Accedi',
			'Register'                        => 'Registrati',
			'Username or email address'       => 'Nome utente o email',
			'Remember me'                     => 'Ricordami',
			'Lost your password?'             => 'Password dimenticata?',
			'Log in'                          => 'Accedi',

			// Shop/archive
			'Shop'                            => 'Shop',
			'Default sorting'                 => 'Ordinamento predefinito',
			'Sort by popularity'              => 'Ordina per popolarita',
			'Sort by average rating'          => 'Ordina per valutazione',
			'Sort by latest'                  => 'Ordina per novita',
			'Sort by price: low to high'      => 'Ordina per prezzo: crescente',
			'Sort by price: high to low'      => 'Ordina per prezzo: decrescente',
			'Showing all %d results'          => 'Mostrando tutti i %d risultati',
			'Showing %1$d&ndash;%2$d of %3$d results' => 'Mostrando %1$d&ndash;%2$d di %3$d risultati',
			'Showing the single result'       => 'Mostrando l\'unico risultato',
			'Search products&hellip;'         => 'Cerca prodotti&hellip;',
			'Search Products'                 => 'Cerca Prodotti',
			'No products were found matching your selection.' => 'Nessun prodotto trovato.',

			// Messages
			'has been added to your cart.'          => 'e stato aggiunto al carrello.',
			'have been added to your cart.'         => 'sono stati aggiunti al carrello.',
			'&ldquo;%s&rdquo; has been added to your cart.' => '&ldquo;%s&rdquo; e stato aggiunto al carrello.',

			// Reviews
			'There are no reviews yet.'       => 'Non ci sono ancora recensioni.',
			'Be the first to review'          => 'Scrivi la prima recensione',
			'Your review'                     => 'La tua recensione',
			'Your rating'                     => 'Il tuo voto',
			'Submit'                          => 'Invia',

			// My Account
			'No order has been made yet.'     => 'Non hai ancora effettuato nessun ordine.',
			'Browse products'                 => 'Sfoglia i prodotti',
			'Order'                           => 'Ordine',
			'Date'                            => 'Data',
			'Status'                          => 'Stato',
			'Actions'                         => 'Azioni',
			'View'                            => 'Visualizza',
			'Billing address'                 => 'Indirizzo di fatturazione',
			'Shipping address'                => 'Indirizzo di spedizione',
			'Edit'                            => 'Modifica',
			'First name'                      => 'Nome',
			'Last name'                       => 'Cognome',
			'Display name'                    => 'Nome visualizzato',
			'Current password (leave blank to leave unchanged)' => 'Password attuale (lascia vuoto per non cambiare)',
			'New password (leave blank to leave unchanged)'     => 'Nuova password (lascia vuoto per non cambiare)',
			'Confirm new password'            => 'Conferma nuova password',
			'Save changes'                    => 'Salva modifiche',
			'Save address'                    => 'Salva indirizzo',

			// Cart (block strings)
			'Your cart'                       => 'Il tuo carrello',
			'Your cart is currently empty!'    => 'Il tuo carrello e vuoto!',
			'New in store'                    => 'Novita in negozio',
			'You may be interested in&hellip;' => 'Potrebbe interessarti&hellip;',
			'Coupon code'                     => 'Codice coupon',
			'Apply'                           => 'Applica',
			'Remove item'                     => 'Rimuovi',
			'Order summary'                   => 'Riepilogo ordine',

			// Checkout (block strings)
			'Contact information'             => 'Informazioni di contatto',
			'Shipping address'                => 'Indirizzo di spedizione',
			'Billing address'                 => 'Indirizzo di fatturazione',
			'Shipping options'                => 'Opzioni di spedizione',
			'Payment options'                 => 'Opzioni di pagamento',
			'Additional information'          => 'Informazioni aggiuntive',
			'Order notes'                     => 'Note ordine',
			'Add a note to your order'        => 'Aggiungi una nota al tuo ordine',
			'Place Order'                     => 'Effettua ordine',
			'Use same address for billing'    => 'Usa lo stesso indirizzo per la fatturazione',

			// Misc
			'Home'                            => 'Home',
			'Search'                          => 'Cerca',
			'Product'                         => 'Prodotto',
			'Products'                        => 'Prodotti',
			'Price'                           => 'Prezzo',
			'Quantity'                        => 'Quantita',
		];
	}

	return $translations[ $text ] ?? $translated;
}

/**
 * Translate plural strings (WC uses _n() for result counts).
 */
add_filter( 'ngettext', function ( $translated, $single, $plural, $number, $domain ) {
	if ( $domain !== 'woocommerce' ) return $translated;

	$plurals = [
		'Showing all %1$d result'   => [ 'Mostrando l\'unico risultato', 'Mostrando tutti i %1$d risultati' ],
		'Showing %1$d&ndash;%2$d of %3$d result' => [ 'Mostrando %1$d&ndash;%2$d di %3$d risultato', 'Mostrando %1$d&ndash;%2$d di %3$d risultati' ],
	];

	if ( isset( $plurals[ $single ] ) ) {
		return $number === 1 ? $plurals[ $single ][0] : $plurals[ $single ][1];
	}

	return $translated;
}, 20, 5 );

/**
 * Translate the result count strings that WC generates via sprintf.
 */
add_filter( 'gettext', function ( $translated, $text, $domain ) {
	if ( $domain !== 'woocommerce' ) return $translated;

	// "Showing all X results"
	if ( str_contains( $text, 'Showing all' ) ) {
		return str_replace( [ 'Showing all', 'results', 'result' ], [ 'Mostrando tutti i', 'risultati', 'risultato' ], $text );
	}
	// "Showing X-Y of Z results"
	if ( str_contains( $text, 'Showing' ) && str_contains( $text, 'of' ) ) {
		$text = str_replace( 'Showing', 'Mostrando', $text );
		$text = str_replace( 'results', 'risultati', $text );
		$text = str_replace( 'result', 'risultato', $text );
		// Replace " of " carefully (not inside words)
		$text = preg_replace( '/\bof\b/', 'di', $text );
		return $text;
	}
	// "Showing the single result"
	if ( str_contains( $text, 'Showing the single' ) ) {
		return 'Mostrando l\'unico risultato';
	}
	return $translated;
}, 21, 3 );

/**
 * Also filter the final HTML output as safety net.
 */
add_filter( 'woocommerce_result_count_html', function ( $html ) {
	$html = preg_replace( '/Showing all (\d+) results?/', 'Mostrando tutti i $1 risultati', $html );
	$html = preg_replace( '/Showing (\d+).(\d+) of (\d+) results?/', 'Mostrando $1-$2 di $3 risultati', $html );
	$html = str_replace( 'Showing the single result', "Mostrando l'unico risultato", $html );
	return $html;
} );
