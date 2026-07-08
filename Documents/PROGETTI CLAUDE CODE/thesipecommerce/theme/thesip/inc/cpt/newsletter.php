<?php
/**
 * CPT pn_newsletter: iscritti alla newsletter (single opt-in).
 *
 * Schema:
 *   post_title          → email iscritto (univoco)
 *   post_status         → publish (attivo) | trash (cancellato)
 *   post_date           → data iscrizione
 *   tassonomia          → pn_newsletter_tag (etichette manuali)
 *
 * Meta:
 *   _pn_nl_ip           → IP iscrizione
 *   _pn_nl_source       → source string ('homepage', 'footer', ...)
 *   _pn_nl_user_agent   → UA al momento dell'iscrizione
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'init',
	function () {
		register_post_type(
			'pn_newsletter',
			array(
				'labels'              => array(
					'name'               => __( 'Newsletter', 'pharmanow' ),
					'singular_name'      => __( 'Iscritto', 'pharmanow' ),
					'menu_name'          => __( 'Newsletter', 'pharmanow' ),
					'add_new'            => __( 'Aggiungi iscritto', 'pharmanow' ),
					'add_new_item'       => __( 'Aggiungi iscritto', 'pharmanow' ),
					'edit_item'          => __( 'Modifica iscritto', 'pharmanow' ),
					'new_item'           => __( 'Nuovo iscritto', 'pharmanow' ),
					'view_item'          => __( 'Vedi iscritto', 'pharmanow' ),
					'search_items'       => __( 'Cerca iscritti', 'pharmanow' ),
					'not_found'          => __( 'Nessun iscritto', 'pharmanow' ),
					'not_found_in_trash' => __( 'Nessun iscritto nel cestino', 'pharmanow' ),
				),
				'public'              => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => false,
				'show_in_rest'        => false,
				'menu_icon'           => 'dashicons-email-alt',
				'menu_position'       => 27,
				'supports'            => array( 'title' ),
				'has_archive'         => false,
				'rewrite'             => false,
				'capability_type'     => 'post',
			)
		);

		register_taxonomy(
			'pn_newsletter_tag',
			'pn_newsletter',
			array(
				'labels'            => array(
					'name'          => __( 'Etichette', 'pharmanow' ),
					'singular_name' => __( 'Etichetta', 'pharmanow' ),
					'search_items'  => __( 'Cerca etichette', 'pharmanow' ),
					'all_items'     => __( 'Tutte le etichette', 'pharmanow' ),
					'edit_item'     => __( 'Modifica etichetta', 'pharmanow' ),
					'update_item'   => __( 'Aggiorna etichetta', 'pharmanow' ),
					'add_new_item'  => __( 'Aggiungi etichetta', 'pharmanow' ),
					'new_item_name' => __( 'Nuova etichetta', 'pharmanow' ),
					'menu_name'     => __( 'Etichette', 'pharmanow' ),
				),
				'hierarchical'      => false,
				'public'            => false,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => false,
				'rewrite'           => false,
			)
		);
	}
);

/**
 * Admin: colonne custom nella lista iscritti.
 */
add_filter(
	'manage_pn_newsletter_posts_columns',
	function ( $cols ) {
		$new = array();
		foreach ( $cols as $k => $v ) {
			if ( 'title' === $k ) {
				$new[ $k ]            = __( 'Email', 'pharmanow' );
				$new['pn_nl_source']  = __( 'Origine', 'pharmanow' );
			} else {
				$new[ $k ] = $v;
			}
		}
		$new['pn_nl_ip'] = __( 'IP', 'pharmanow' );
		return $new;
	}
);

add_action(
	'manage_pn_newsletter_posts_custom_column',
	function ( $column, $post_id ) {
		switch ( $column ) {
			case 'pn_nl_source':
				echo esc_html( (string) get_post_meta( $post_id, '_pn_nl_source', true ) );
				break;
			case 'pn_nl_ip':
				echo esc_html( (string) get_post_meta( $post_id, '_pn_nl_ip', true ) );
				break;
		}
	},
	10,
	2
);

/**
 * Admin list: aggiunge ricerca per email anche su post_title (default funziona già) + ordine per data.
 */
add_filter(
	'manage_edit-pn_newsletter_sortable_columns',
	function ( $cols ) {
		$cols['pn_nl_source'] = 'pn_nl_source';
		return $cols;
	}
);

/**
 * Trova iscritto per email (post_title). Ritorna post_id o 0.
 */
function pn_newsletter_find_by_email( string $email ): int {
	$email = strtolower( trim( $email ) );
	if ( ! is_email( $email ) ) {
		return 0;
	}
	$q = new WP_Query(
		array(
			'post_type'              => 'pn_newsletter',
			'post_status'            => array( 'publish', 'trash' ),
			'posts_per_page'         => 1,
			'title'                  => $email,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		)
	);
	return $q->posts ? (int) $q->posts[0] : 0;
}
