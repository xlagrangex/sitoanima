<?php
/**
 * Cookie categories — singola fonte di verità per banner + modal + admin.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array<int, array{key:string, label:string, description:string, required:bool}>
 */
function pn_consent_categories(): array {
	return array(
		array(
			'key'         => 'necessary',
			'label'       => __( 'Cookie necessari', 'pharmanow' ),
			'description' => __( 'Indispensabili per il funzionamento del sito (sessione, carrello, login). Sempre attivi, non disattivabili.', 'pharmanow' ),
			'required'    => true,
		),
		array(
			'key'         => 'statistics',
			'label'       => __( 'Cookie statistici', 'pharmanow' ),
			'description' => __( 'Ci aiutano a capire come gli utenti usano il sito (Google Analytics, PostHog) per migliorarlo. Dati aggregati e anonimizzati.', 'pharmanow' ),
			'required'    => false,
		),
		array(
			'key'         => 'marketing',
			'label'       => __( 'Cookie di marketing', 'pharmanow' ),
			'description' => __( 'Permettono di mostrare annunci personalizzati basati sui tuoi interessi (Meta Pixel, TikTok Pixel, Pinterest Tag).', 'pharmanow' ),
			'required'    => false,
		),
	);
}
