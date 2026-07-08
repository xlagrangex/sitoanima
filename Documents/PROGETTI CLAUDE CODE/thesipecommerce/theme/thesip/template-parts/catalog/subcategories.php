<?php
/**
 * Strip orizzontale di sottocategorie.
 *
 * @package Pharmanow
 *
 * @var array $args { parent_id (int) }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn_parent = isset( $args['parent_id'] ) ? (int) $args['parent_id'] : 0;
$pn_terms  = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'parent'     => $pn_parent,
		'hide_empty' => true,
		'number'     => 30,
	)
);

if ( is_wp_error( $pn_terms ) || empty( $pn_terms ) ) {
	return;
}

// Escludi la "uncategorized" di default.
$pn_terms = array_filter(
	$pn_terms,
	function ( $t ) {
		return 'uncategorized' !== $t->slug;
	}
);

if ( empty( $pn_terms ) ) {
	return;
}
?>
<div class="mb-6 -mx-4 overflow-x-auto px-4 md:mx-0 md:overflow-visible md:px-0 pn-no-scrollbar">
	<div class="flex gap-4 pb-2 md:flex-wrap md:pb-0">
		<?php foreach ( $pn_terms as $pn_term ) :
			$pn_url   = get_term_link( $pn_term );
			$pn_thumb = function_exists( 'get_term_meta' ) ? get_term_meta( $pn_term->term_id, 'thumbnail_id', true ) : 0;
			$pn_image = $pn_thumb ? wp_get_attachment_image_url( (int) $pn_thumb, 'pn-category-tile' ) : '';
			?>
			<a
				href="<?php echo esc_url( is_wp_error( $pn_url ) ? '#' : $pn_url ); ?>"
				class="group flex w-20 shrink-0 flex-col items-center gap-2 md:w-24"
			>
				<div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-pharma-teal/10 to-pharma-teal/25 text-xl font-bold text-pharma-teal transition-colors group-hover:from-pharma-teal group-hover:to-pharma-teal-dark group-hover:text-white md:h-20 md:w-20 overflow-hidden">
					<?php if ( $pn_image ) : ?>
						<img src="<?php echo esc_url( $pn_image ); ?>" alt="<?php echo esc_attr( $pn_term->name ); ?>" class="h-full w-full object-cover" loading="lazy">
					<?php else : ?>
						<?php echo esc_html( mb_strtoupper( mb_substr( $pn_term->name, 0, 1 ) ) ); ?>
					<?php endif; ?>
				</div>
				<span class="line-clamp-2 text-center text-xs leading-tight text-gray-500 transition-colors group-hover:text-pharma-teal">
					<?php echo esc_html( $pn_term->name ); ?>
				</span>
			</a>
		<?php endforeach; ?>
	</div>
</div>
