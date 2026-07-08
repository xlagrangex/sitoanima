<?php
/**
 * Drawer mobile filtri (Sheet replica del Next).
 *
 * Aperto via evento custom 'open-filters' (dispatched dalla toolbar).
 * Sliding-in da sinistra; backdrop click + tasto X per chiudere.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div
	x-data="{ open: false }"
	@open-filters.window="open = true; document.body.style.overflow = 'hidden'"
	@keydown.escape.window="open = false; document.body.style.overflow = ''"
	x-cloak
	class="md:hidden"
>
	<!-- Backdrop -->
	<div
		x-show="open"
		x-transition.opacity
		@click="open = false; document.body.style.overflow = ''"
		class="fixed inset-0 z-[60] bg-black/40"
	></div>

	<!-- Panel -->
	<div
		x-show="open"
		x-transition:enter="transition ease-out duration-300"
		x-transition:enter-start="-translate-x-full"
		x-transition:enter-end="translate-x-0"
		x-transition:leave="transition ease-in duration-200"
		x-transition:leave-start="translate-x-0"
		x-transition:leave-end="-translate-x-full"
		class="fixed inset-y-0 left-0 z-[61] w-full max-w-sm bg-white shadow-xl overflow-y-auto"
	>
		<div class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-200 bg-white px-6 py-4">
			<h2 class="text-lg font-semibold text-gray-900"><?php esc_html_e( 'Filtri', 'pharmanow' ); ?></h2>
			<button
				type="button"
				@click="open = false; document.body.style.overflow = ''"
				aria-label="<?php esc_attr_e( 'Chiudi filtri', 'pharmanow' ); ?>"
				class="inline-flex items-center justify-center h-9 w-9 rounded-md text-gray-500 hover:bg-gray-100 transition-colors"
			>
				<?php pn_icon( 'x', array( 'class' => 'h-5 w-5' ) ); ?>
			</button>
		</div>
		<div class="p-6">
			<?php get_template_part( 'template-parts/catalog/sidebar-filters' ); ?>
		</div>
	</div>
</div>
