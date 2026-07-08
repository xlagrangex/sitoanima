<?php
/**
 * Badges prodotto derivati da tag/meta. Replica `PharmacyBadges.tsx`.
 *
 * @var array $args { tags: string[] }
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

$pn_tags = $args['tags'] ?? array();
if ( empty( $pn_tags ) ) {
	return;
}

$pn_map = array(
	'edizione-limitata' => array(
		'label' => __( 'Edizione limitata', 'pharmanow' ),
		'icon'  => 'sparkles',
		'class' => 'border-blue-200 bg-blue-50 text-blue-700',
	),
	'spedizione' => array(
		'label' => __( 'Spedizione 24/48h', 'pharmanow' ),
		'icon'  => 'truck',
		'class' => 'border-sky-200 bg-sky-50 text-sky-700',
	),
	'originale' => array(
		'label' => __( 'Originale', 'pharmanow' ),
		'icon'  => 'shield-check',
		'class' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
	),
	'novita'    => array(
		'label' => __( 'Novità', 'pharmanow' ),
		'icon'  => 'sparkles',
		'class' => 'border-amber-200 bg-amber-50 text-amber-700',
	),
	'promo'     => array(
		'label' => __( 'Promo', 'pharmanow' ),
		'icon'  => 'flame',
		'class' => 'border-violet-200 bg-violet-50 text-violet-700',
	),
);
$pn_fallback = array( 'icon' => 'tag', 'class' => 'border-gray-200 bg-gray-50 text-gray-700' );
?>
<div class="flex flex-wrap gap-2">
	<?php
	foreach ( $pn_tags as $raw ) :
		$pn_key   = strtolower( trim( $raw ) );
		$pn_def   = $pn_map[ $pn_key ] ?? null;
		$pn_label = $pn_def['label'] ?? $raw;
		$pn_class = $pn_def['class'] ?? $pn_fallback['class'];
		$pn_icon  = $pn_def['icon'] ?? $pn_fallback['icon'];
		?>
		<span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-medium <?php echo esc_attr( $pn_class ); ?>">
			<?php pn_icon( $pn_icon, array( 'class' => 'h-3 w-3' ) ); ?>
			<?php echo esc_html( $pn_label ); ?>
		</span>
	<?php endforeach; ?>
</div>
