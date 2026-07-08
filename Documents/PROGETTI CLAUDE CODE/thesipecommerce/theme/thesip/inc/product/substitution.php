<?php
/**
 * Sostituzione Farmadati: redirect 301 al nuovo prodotto se sono passati ≥30 giorni.
 *
 * Replica logica `app/(shop)/prodotto/[slug]/page.tsx:6` del Next.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const PN_SUBSTITUTION_DAYS = 30;

add_action(
	'template_redirect',
	function () {
		if ( ! is_singular( 'product' ) ) {
			return;
		}
		$product_id = get_queried_object_id();
		$product    = wc_get_product( $product_id );
		if ( ! $product ) {
			return;
		}

		$meta = pn_pharma_meta( $product );
		if ( ! $meta['sostituito_da'] || '' === $meta['sostituito_at'] ) {
			return;
		}

		$ts = strtotime( $meta['sostituito_at'] );
		if ( false === $ts ) {
			return;
		}
		$days = floor( ( time() - $ts ) / DAY_IN_SECONDS );

		if ( $days >= PN_SUBSTITUTION_DAYS ) {
			$new_url = get_permalink( $meta['sostituito_da'] );
			if ( $new_url ) {
				wp_safe_redirect( $new_url, 301 );
				exit;
			}
		}
	}
);

/**
 * Quanti giorni dalla sostituzione (per banner).
 *
 * @param string $sostituito_at ISO date
 * @return int
 */
function pn_substitution_days_since( string $sostituito_at ): int {
	$ts = strtotime( $sostituito_at );
	if ( false === $ts ) {
		return 0;
	}
	return (int) floor( ( time() - $ts ) / DAY_IN_SECONDS );
}
