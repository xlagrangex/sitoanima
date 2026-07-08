<?php
/**
 * Progress bar verso spedizione gratuita.
 *
 * Replica `FreeShippingProgress.tsx`. Si aggancia ad Alpine.store('pnCart')
 * (subtotal aggiornato live dal mini-cart Block).
 *
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

$pn_threshold = pn_get_free_shipping_threshold();
?>
<div
	x-data="{
		threshold: <?php echo (float) $pn_threshold; ?>,
		get subtotal() { return (window.Alpine?.store('pnCart')?.subtotal) ?? 0; },
		get reached() { return this.subtotal >= this.threshold; },
		get progress() { return Math.min(100, this.threshold > 0 ? (this.subtotal/this.threshold)*100 : 0); },
		get remaining() { return Math.max(0, this.threshold - this.subtotal); },
		fmt(n) { return new Intl.NumberFormat('it-IT', { style:'currency', currency:'EUR' }).format(n); }
	}"
	class="rounded-xl border border-emerald-200 bg-emerald-50 p-4"
>
	<div class="mb-2 flex items-center justify-between gap-2 text-sm">
		<span class="flex items-center gap-2 font-medium text-emerald-700">
			<span :class="reached ? '' : 'opacity-40'"><?php pn_icon( 'check-circle-2', array( 'class' => 'h-4 w-4' ) ); ?></span>
			<span x-show="reached" x-cloak><?php esc_html_e( 'Spedizione GRATUITA!', 'pharmanow' ); ?></span>
			<span x-show="!reached"><?php esc_html_e( 'Aggiungi', 'pharmanow' ); ?> <span x-text="fmt(remaining)"></span> <?php esc_html_e( 'per spedizione gratuita', 'pharmanow' ); ?></span>
		</span>
		<span class="text-xs font-semibold text-emerald-700" x-text="fmt(threshold)"></span>
	</div>
	<div class="h-2 w-full overflow-hidden rounded-full bg-emerald-200/40">
		<div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 transition-all duration-500" :style="`width: ${progress}%`"></div>
	</div>
</div>
