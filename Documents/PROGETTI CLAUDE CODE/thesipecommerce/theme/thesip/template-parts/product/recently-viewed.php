<?php
/**
 * Carousel "Visti di recente": legge IDs da localStorage, fetch via REST,
 * salva l'ID corrente nello store. Replica `RecentlyViewed.tsx`.
 *
 * @var array $args { product_id: int }
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

$pn_pid = (int) ( $args['product_id'] ?? 0 );
?>
<section
	x-data="pnRecentlyViewed(<?php echo (int) $pn_pid; ?>)"
	x-init="init()"
	x-show="products.length > 0"
	x-cloak
	class="space-y-4"
>
	<div class="flex items-center justify-between">
		<h2 class="text-xl font-bold md:text-2xl"><?php esc_html_e( 'Visti di recente', 'pharmanow' ); ?></h2>
		<div class="hidden gap-2 md:flex">
			<button type="button" @click="scroll(-1)" aria-label="<?php esc_attr_e( 'Scorri a sinistra', 'pharmanow' ); ?>" class="flex h-9 w-9 items-center justify-center rounded-full border bg-background transition-colors hover:bg-muted">
				<?php pn_icon( 'chevron-left', array( 'class' => 'h-4 w-4' ) ); ?>
			</button>
			<button type="button" @click="scroll(1)" aria-label="<?php esc_attr_e( 'Scorri a destra', 'pharmanow' ); ?>" class="flex h-9 w-9 items-center justify-center rounded-full border bg-background transition-colors hover:bg-muted">
				<?php pn_icon( 'chevron-right', array( 'class' => 'h-4 w-4' ) ); ?>
			</button>
		</div>
	</div>
	<div x-ref="scroller" class="flex snap-x snap-mandatory gap-3 overflow-x-auto scroll-smooth pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
		<template x-for="p in products" :key="p.id">
			<a :href="p.url" class="group relative flex w-[150px] shrink-0 snap-start flex-col overflow-hidden rounded-xl border border-pharma-teal/20 bg-card transition-all duration-200 hover:border-pharma-teal/50 hover:shadow-[0_8px_24px_-8px_rgb(11_136_148/0.18)]">
				<div class="relative aspect-square overflow-hidden bg-white p-3">
					<img :src="p.thumbnail" :alt="p.title" loading="lazy" class="absolute inset-0 h-full w-full object-contain p-3 transition-transform duration-500 ease-out group-hover:scale-[1.06]">
					<template x-if="p.is_new">
						<span class="absolute left-2 top-2 inline-flex items-center justify-center rounded-md bg-emerald-50/80 border-2 border-emerald-500 px-1.5 py-0.5 text-[10px] font-bold text-emerald-700">Nuovo</span>
					</template>
					<template x-if="!p.in_stock">
						<span class="absolute left-2 top-2 inline-flex items-center justify-center rounded-md bg-gradient-to-br from-gray-600 to-gray-800 px-1.5 py-0.5 text-[10px] font-bold text-white">Esaurito</span>
					</template>
				</div>
				<div class="flex flex-1 flex-col gap-1 p-3">
					<span class="uppercase tracking-wide text-gray-500 text-[10px] font-medium leading-tight line-clamp-1" x-text="p.brand || ' '"></span>
					<h3 class="line-clamp-2 font-bold uppercase tracking-wide leading-snug text-pharma-teal-dark text-xs transition-colors group-hover:text-pharma-teal" x-text="p.title"></h3>
					<div class="mt-auto pt-1" x-html="p.price_html"></div>
				</div>
			</a>
		</template>
	</div>
</section>
