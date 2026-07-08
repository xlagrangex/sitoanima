<?php
/**
 * Mobile menu — porting da components/shop/layout/MobileMenu.tsx (Next).
 *
 * Accordion con depth max 2. Alpine x-data per ogni nodo (toggle indipendente).
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn_roots = pn_get_mega_menu_roots();
if ( empty( $pn_roots ) ) {
	return;
}

/**
 * Render ricorsivo di un nodo accordion mobile.
 *
 * @param array $node  Nodo dal tree.
 * @param int   $depth Livello (0 root, 1 figlio, 2 nipote).
 */
function pn_mobile_menu_node( array $node, int $depth = 0 ): void {
	$visible_kids = array_values(
		array_filter(
			$node['children'] ?? array(),
			function ( $c ) {
				return pn_node_has_products( $c );
			}
		)
	);
	$has_kids   = $depth < 2 && ! empty( $visible_kids );
	$indent     = 0 === $depth ? 'px-4' : ( 1 === $depth ? 'pl-8 pr-4' : 'pl-12 pr-4' );
	$text_size  = 0 === $depth ? 'text-sm font-semibold' : 'text-sm';
	$text_color = 0 === $depth ? 'text-gray-900' : 'text-gray-700';
	$meta       = 0 === $depth ? pn_root_icon( $node['handle'] ) : array( 'icon' => '', 'color' => '' );

	if ( ! $has_kids ) {
		?>
		<a
			href="<?php echo esc_url( $node['link'] ); ?>"
			class="<?php echo esc_attr( "$indent $text_size $text_color" ); ?> py-2.5 hover:bg-gray-50 flex items-center gap-2"
			x-on:click="mobileOpen = false"
		>
			<?php if ( $meta['icon'] ) : ?>
				<?php pn_icon( $meta['icon'], array( 'class' => 'h-4 w-4 text-gray-400' ) ); ?>
			<?php endif; ?>
			<span><?php echo esc_html( $node['name'] ); ?></span>
			<?php if ( $node['count'] > 0 ) : ?>
				<span class="text-xs font-normal text-gray-400"><?php echo esc_html( $node['count'] ); ?></span>
			<?php endif; ?>
		</a>
		<?php
		return;
	}
	?>
	<div x-data="{ open: false }" class="flex flex-col">
		<button
			type="button"
			x-on:click="open = !open"
			x-bind:aria-expanded="open"
			class="<?php echo esc_attr( "$indent $text_size $text_color" ); ?> py-2.5 flex items-center justify-between hover:bg-gray-50 w-full text-left"
		>
			<span class="flex items-center gap-2">
				<?php if ( $meta['icon'] ) : ?>
					<?php pn_icon( $meta['icon'], array( 'class' => 'h-4 w-4 text-gray-400' ) ); ?>
				<?php endif; ?>
				<span><?php echo esc_html( $node['name'] ); ?></span>
				<?php if ( $node['count'] > 0 ) : ?>
					<span class="text-xs font-normal text-gray-400"><?php echo esc_html( $node['count'] ); ?></span>
				<?php endif; ?>
			</span>
			<span x-show="!open" x-cloak><?php pn_icon( 'chevron-right', array( 'class' => 'h-4 w-4 text-gray-400' ) ); ?></span>
			<span x-show="open" x-cloak><?php pn_icon( 'chevron-down', array( 'class' => 'h-4 w-4 text-gray-400' ) ); ?></span>
		</button>

		<div x-show="open" x-cloak x-transition.opacity class="flex flex-col border-l border-gray-100 ml-4">
			<a
				href="<?php echo esc_url( $node['link'] ); ?>"
				class="<?php echo esc_attr( 0 === $depth ? 'pl-8 pr-4' : 'pl-12 pr-4' ); ?> py-2 text-xs font-medium text-pharma-teal hover:bg-gray-50"
				x-on:click="mobileOpen = false"
			>
				<?php
				printf(
					/* translators: %s = nome categoria */
					esc_html__( 'Vedi tutto in %s →', 'pharmanow' ),
					esc_html( $node['name'] )
				);
				?>
			</a>
			<?php
			foreach ( $visible_kids as $child ) {
				pn_mobile_menu_node( $child, $depth + 1 );
			}
			?>
		</div>
	</div>
	<?php
}
?>

<nav class="flex flex-col py-1">
	<?php
	foreach ( $pn_roots as $pn_root ) {
		pn_mobile_menu_node( $pn_root, 0 );
	}
	?>
</nav>
