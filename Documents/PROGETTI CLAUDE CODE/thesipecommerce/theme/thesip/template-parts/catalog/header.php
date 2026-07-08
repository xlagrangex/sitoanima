<?php
/**
 * Catalog header: titolo + descrizione + InstantSearch.
 *
 * @package Pharmanow
 *
 * @var array $args { title, description }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn_title = isset( $args['title'] ) ? (string) $args['title'] : __( 'Catalogo', 'pharmanow' );
$pn_desc  = isset( $args['description'] ) ? (string) $args['description'] : '';
?>
<header class="mb-6 space-y-3">
	<h1 class="text-2xl md:text-3xl font-bold text-gray-900 tracking-tight">
		<?php echo esc_html( $pn_title ); ?>
	</h1>
	<?php if ( $pn_desc ) : ?>
		<p class="text-sm text-gray-500"><?php echo esc_html( $pn_desc ); ?></p>
	<?php endif; ?>
	<div class="max-w-xl">
		<?php get_template_part( 'template-parts/header/search-bar' ); ?>
	</div>
</header>
