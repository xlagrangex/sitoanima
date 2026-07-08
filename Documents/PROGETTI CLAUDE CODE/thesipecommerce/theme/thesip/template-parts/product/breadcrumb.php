<?php
/**
 * @var array $args { product: WC_Product }
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

$pn_p = $args['product'] ?? null;
if ( ! $pn_p instanceof WC_Product ) {
	return;
}
$pn_cats    = wp_get_post_terms( $pn_p->get_id(), 'product_cat' );
$pn_primary = ( ! is_wp_error( $pn_cats ) && ! empty( $pn_cats ) ) ? $pn_cats[0] : null;
?>
<nav class="mb-4 text-sm text-muted-foreground" aria-label="Breadcrumb">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-pharma-teal">Home</a>
	<span class="mx-2">/</span>
	<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ?: home_url( '/catalogo/' ) ); ?>" class="hover:text-pharma-teal">Catalogo</a>
	<?php if ( $pn_primary ) : ?>
		<span class="mx-2">/</span>
		<a href="<?php echo esc_url( get_term_link( $pn_primary ) ); ?>" class="hover:text-pharma-teal"><?php echo esc_html( $pn_primary->name ); ?></a>
	<?php endif; ?>
	<span class="mx-2">/</span>
	<span class="text-foreground"><?php echo esc_html( $pn_p->get_name() ); ?></span>
</nav>
