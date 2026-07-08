<?php
/**
 * Mega menu desktop — porting da components/shop/layout/MegaMenu.tsx (Next).
 *
 * Struttura:
 *   - Container scrollabile orizzontalmente (i triggers)
 *   - Dropdowns FUORI dal container scrollabile (Alpine x-show con timer su mouseleave)
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn_roots = pn_get_mega_menu_roots();

if ( empty( $pn_roots ) ) :
	$pn_shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/negozio' );
	$pn_fallback_nav = array(
		array( 'label' => __( 'Shop', 'pharmanow' ),              'href' => $pn_shop,                                              'highlight' => true ),
		array( 'label' => __( 'Set di carte', 'pharmanow' ),      'href' => add_query_arg( 'product_cat', 'set-di-carte', $pn_shop ) ),
		array( 'label' => __( 'Bundle', 'pharmanow' ),            'href' => add_query_arg( 'product_cat', 'bundle', $pn_shop ) ),
		array( 'label' => __( 'Edizioni speciali', 'pharmanow' ), 'href' => add_query_arg( 'product_cat', 'edizioni-speciali', $pn_shop ) ),
		array( 'label' => __( 'Il progetto', 'pharmanow' ),       'href' => home_url( '/chi-siamo' ) ),
		array( 'label' => __( 'FAQ', 'pharmanow' ),               'href' => home_url( '/faq' ) ),
		array( 'label' => __( 'Contatti', 'pharmanow' ),          'href' => home_url( '/contatti' ) ),
	);
	?>
	<nav class="border-t border-gray-100 h-[40px]">
		<div class="container max-w-7xl mx-auto px-4 h-full flex items-center gap-1 overflow-x-auto pn-no-scrollbar">
			<?php foreach ( $pn_fallback_nav as $pn_nav_item ) :
				$pn_is_hl = ! empty( $pn_nav_item['highlight'] );
				?>
				<a
					href="<?php echo esc_url( $pn_nav_item['href'] ); ?>"
					data-slot="button"
					class="shrink-0 whitespace-nowrap px-3 py-1.5 text-sm font-medium rounded-md <?php echo $pn_is_hl ? 'text-pharma-teal hover:bg-pharma-teal/5' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'; ?>"
				>
					<?php echo pn_slide_text( '<span>' . esc_html( $pn_nav_item['label'] ) . '</span>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
			<?php endforeach; ?>
		</div>
	</nav>
	<?php
	return;
endif;

// Pre-calcola children visibili per ogni root (servono sia trigger che dropdown).
$pn_processed = array();
foreach ( $pn_roots as $pn_root ) {
	$pn_visible_kids = array_values(
		array_filter(
			$pn_root['children'],
			function ( $c ) {
				return pn_node_has_products( $c );
			}
		)
	);
	$pn_processed[] = array(
		'root'         => $pn_root,
		'visible_kids' => $pn_visible_kids,
		'has_dropdown' => ! empty( $pn_visible_kids ),
	);
}
?>

<nav
	x-data="{
		open: 0,
		timer: null,
		canLeft: false,
		canRight: false,
		hintShown: false,
		dragging: false,
		dragged: false,
		startX: 0,
		startScroll: 0,
		set(i) { if (this.timer) clearTimeout(this.timer); this.open = i; },
		schedule() { if (this.timer) clearTimeout(this.timer); this.timer = setTimeout(() => this.open = 0, 150); },
		init() {
			this.$nextTick(() => this.update());
			window.addEventListener('resize', () => this.update());
		},
		update() {
			const el = this.$refs.scroller;
			if (!el) return;
			this.canLeft  = el.scrollLeft > 4;
			this.canRight = el.scrollLeft + el.clientWidth < el.scrollWidth - 4;
			if (el.scrollLeft > 0) this.hintShown = true;
		},
		nudge(dir) {
			this.hintShown = true;
			this.$refs.scroller.scrollBy({ left: dir * 240, behavior: 'smooth' });
		},
		dragStart(e) {
			if (e.pointerType === 'mouse' && e.button !== 0) return;
			this.dragging = true; this.dragged = false;
			this.startX = e.pageX;
			this.startScroll = this.$refs.scroller.scrollLeft;
		},
		dragMove(e) {
			if (!this.dragging) return;
			const dx = e.pageX - this.startX;
			if (Math.abs(dx) > 5) this.dragged = true;
			this.$refs.scroller.scrollLeft = this.startScroll - dx;
		},
		dragEnd() { this.dragging = false; },
		clickIfNotDrag(e) {
			if (this.dragged) { e.preventDefault(); e.stopPropagation(); this.dragged = false; }
		}
	}"
	class="relative border-t border-gray-100 h-[40px]"
>
	<!-- Triggers scrollabili orizzontalmente con UX hint -->
	<div class="container max-w-7xl mx-auto px-4 h-full relative">

		<!-- Gradient fade — sfuma le voci ai bordi -->
		<div x-show="canLeft" x-cloak class="absolute inset-y-0 left-0 w-24 bg-gradient-to-r from-white via-white to-transparent z-20 pointer-events-none"></div>
		<div x-show="canRight" x-cloak class="absolute inset-y-0 right-0 w-24 bg-gradient-to-l from-white via-white to-transparent z-20 pointer-events-none"></div>

		<!-- Frecce sopra il gradient (no riquadro, solo icona teal grande) -->
		<button
			type="button"
			x-show="canLeft"
			x-cloak
			x-transition.opacity
			x-on:click="nudge(-1)"
			aria-label="<?php esc_attr_e( 'Scorri a sinistra', 'pharmanow' ); ?>"
			class="absolute left-2 top-1/2 -translate-y-1/2 z-30 inline-flex items-center justify-center p-1 text-pharma-teal hover:text-pharma-teal-dark transition-colors"
		>
			<?php pn_icon( 'chevron-left', array( 'class' => 'h-6 w-6' ) ); ?>
		</button>
		<button
			type="button"
			x-show="canRight"
			x-cloak
			x-transition.opacity
			x-on:click="nudge(1)"
			x-bind:class="!hintShown ? 'pn-arrow-hint' : ''"
			aria-label="<?php esc_attr_e( 'Scorri a destra', 'pharmanow' ); ?>"
			class="absolute right-2 top-1/2 -translate-y-1/2 z-30 inline-flex items-center justify-center p-1 text-pharma-teal hover:text-pharma-teal-dark transition-colors"
		>
			<?php pn_icon( 'chevron-right', array( 'class' => 'h-6 w-6' ) ); ?>
		</button>

		<!-- Container scrollabile (drag-to-scroll + scroll listener) -->
		<div
			x-ref="scroller"
			x-on:scroll.passive="update()"
			x-on:pointerdown="dragStart($event)"
			x-on:pointermove="dragMove($event)"
			x-on:pointerup="dragEnd()"
			x-on:pointerleave="dragEnd()"
			x-on:click.capture="clickIfNotDrag($event)"
			x-bind:class="dragging ? 'cursor-grabbing select-none' : 'cursor-grab'"
			class="flex items-center h-full gap-1 overflow-x-auto pn-no-scrollbar scroll-smooth touch-pan-x"
		>
			<?php foreach ( $pn_processed as $pn_i => $pn_p ) :
				$pn_idx  = $pn_i + 1;
				$pn_root = $pn_p['root'];
				$pn_meta = pn_root_icon( $pn_root['handle'] );
				?>
				<a
					href="<?php echo esc_url( $pn_root['link'] ); ?>"
					x-on:mouseenter="set(<?php echo (int) $pn_idx; ?>)"
					x-on:mouseleave="schedule()"
					x-on:focus="set(<?php echo (int) $pn_idx; ?>)"
					x-on:blur="schedule()"
					x-bind:class="open === <?php echo (int) $pn_idx; ?> ? 'text-pharma-teal bg-gray-50' : ''"
					class="shrink-0 flex h-full items-center gap-1.5 whitespace-nowrap px-3 text-sm font-medium text-gray-700 hover:text-pharma-teal hover:bg-gray-50 transition-colors"
				>
					<?php if ( $pn_meta['icon'] ) : ?>
						<?php pn_icon( $pn_meta['icon'], array( 'class' => 'h-4 w-4 text-gray-400 transition-colors' ) ); ?>
					<?php endif; ?>
					<?php echo esc_html( $pn_root['name'] ); ?>
					<?php if ( $pn_p['has_dropdown'] ) : ?>
						<span x-bind:class="open === <?php echo (int) $pn_idx; ?> ? 'rotate-180' : ''" class="transition-transform">
							<?php pn_icon( 'chevron-down', array( 'class' => 'h-3.5 w-3.5' ) ); ?>
						</span>
					<?php endif; ?>
				</a>
			<?php endforeach; ?>
		</div>
	</div>

	<!-- Dropdowns (FUORI dal container scrollabile, position absolute rispetto al <nav>) -->
	<?php foreach ( $pn_processed as $pn_i => $pn_p ) :
		if ( ! $pn_p['has_dropdown'] ) {
			continue;
		}
		$pn_idx          = $pn_i + 1;
		$pn_root         = $pn_p['root'];
		$pn_visible_kids = $pn_p['visible_kids'];
		$pn_kids_count   = count( $pn_visible_kids );
		?>
		<div
			x-show="open === <?php echo (int) $pn_idx; ?>"
			x-cloak
			x-transition.opacity.duration.150ms
			x-on:mouseenter="set(<?php echo (int) $pn_idx; ?>)"
			x-on:mouseleave="schedule()"
			class="absolute left-0 right-0 top-[calc(100%-12px)] pt-3 z-50"
		>
			<div class="border-t border-b bg-white shadow-xl max-h-[60vh] overflow-y-auto">
				<div class="container max-w-7xl mx-auto px-4 py-4">
					<?php if ( $pn_kids_count <= 3 ) : ?>
						<div class="grid grid-cols-12 gap-6">
							<ul class="col-span-4 space-y-2">
								<?php foreach ( $pn_visible_kids as $pn_kid ) : ?>
									<li>
										<a href="<?php echo esc_url( $pn_kid['link'] ); ?>" class="block text-sm font-semibold text-gray-900 hover:text-pharma-teal">
											<?php echo esc_html( $pn_kid['name'] ); ?>
											<?php if ( $pn_kid['count'] > 0 ) : ?>
												<span class="ml-1.5 text-xs font-normal text-gray-400"><?php echo esc_html( $pn_kid['count'] ); ?></span>
											<?php endif; ?>
										</a>
										<?php
										$pn_leaves = array_values(
											array_filter(
												$pn_kid['children'],
												function ( $l ) {
													return pn_node_has_products( $l );
												}
											)
										);
										if ( $pn_leaves ) :
											?>
											<ul class="mt-1 space-y-0.5">
												<?php foreach ( array_slice( $pn_leaves, 0, 4 ) as $pn_leaf ) : ?>
													<li>
														<a href="<?php echo esc_url( $pn_leaf['link'] ); ?>" class="block text-xs text-gray-500 hover:text-pharma-teal">
															<?php echo esc_html( $pn_leaf['name'] ); ?>
														</a>
													</li>
												<?php endforeach; ?>
											</ul>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
							<div class="col-span-8 flex items-center justify-center text-xs text-gray-400">
								<?php esc_html_e( 'Prodotti in evidenza · Fase 1.4', 'pharmanow' ); ?>
							</div>
						</div>
					<?php else : ?>
						<div class="columns-2 md:columns-3 gap-6 [column-fill:_balance]">
							<?php foreach ( $pn_visible_kids as $pn_kid ) :
								$pn_leaves = array_values(
									array_filter(
										$pn_kid['children'],
										function ( $l ) {
											return pn_node_has_products( $l );
										}
									)
								);
								?>
								<div class="mb-5 break-inside-avoid">
									<a href="<?php echo esc_url( $pn_kid['link'] ); ?>" class="block text-sm font-semibold text-gray-900 hover:text-pharma-teal mb-1.5">
										<?php echo esc_html( $pn_kid['name'] ); ?>
										<?php if ( $pn_kid['count'] > 0 ) : ?>
											<span class="ml-1.5 text-xs font-normal text-gray-400"><?php echo esc_html( $pn_kid['count'] ); ?></span>
										<?php endif; ?>
									</a>
									<?php if ( $pn_leaves ) : ?>
										<ul class="space-y-1">
											<?php foreach ( array_slice( $pn_leaves, 0, 5 ) as $pn_leaf ) : ?>
												<li>
													<a href="<?php echo esc_url( $pn_leaf['link'] ); ?>" class="block text-xs text-gray-500 hover:text-pharma-teal">
														<?php echo esc_html( $pn_leaf['name'] ); ?>
													</a>
												</li>
											<?php endforeach; ?>
											<?php if ( count( $pn_leaves ) > 5 ) : ?>
												<li>
													<a href="<?php echo esc_url( $pn_kid['link'] ); ?>" class="block text-xs font-medium text-pharma-teal hover:underline">
														<?php
														printf(
															/* translators: %d = numero categorie aggiuntive */
															esc_html__( '+ altri %d →', 'pharmanow' ),
															(int) ( count( $pn_leaves ) - 5 )
														);
														?>
													</a>
												</li>
											<?php endif; ?>
										</ul>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<div class="mt-4 pt-3 border-t flex items-center justify-between">
						<a href="<?php echo esc_url( $pn_root['link'] ); ?>" class="text-sm font-medium text-pharma-teal hover:underline inline-flex items-center gap-1">
							<?php
							printf(
								/* translators: %s = nome categoria */
								esc_html__( 'Vedi tutti i prodotti %s', 'pharmanow' ),
								esc_html( $pn_root['name'] )
							);
							?>
							<?php if ( $pn_root['count'] > 0 ) : ?>
								<span class="text-gray-400">(<?php echo esc_html( $pn_root['count'] ); ?>)</span>
							<?php endif; ?>
							<?php pn_icon( 'arrow-right', array( 'class' => 'h-3.5 w-3.5' ) ); ?>
						</a>
						<a href="<?php echo esc_url( add_query_arg( 'on_sale', '1', $pn_root['link'] ) ); ?>" class="text-sm font-medium text-rose-600 hover:underline">
							<?php esc_html_e( 'In offerta →', 'pharmanow' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</nav>
