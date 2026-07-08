<?php
/**
 * Banner urgenza: "Ordina entro HH:MM:SS per riceverlo lunedì 15 maggio".
 *
 * Replica `DeliveryCountdown.tsx`. Cutoff e carrier da pn_shop_settings().
 *
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

$pn_settings = pn_shop_settings();
$pn_cutoff   = (int) ( $pn_settings['delivery_cutoff_hour'] ?? 14 );
$pn_carriers = $pn_settings['carriers'] ?? array( 'FedEx', 'GLS' );

$pn_carrier_logos = array(
	'FedEx' => 'shipping/fedex.jpg',
	'GLS'   => 'shipping/gls.png',
);
?>
<div
	x-data="{
		cutoff: <?php echo (int) $pn_cutoff; ?>,
		h: 0, m: 0, s: 0, expired: false, deliveryDate: '',
		dayNames: ['domenica','lunedì','martedì','mercoledì','giovedì','venerdì','sabato'],
		monthNames: ['gennaio','febbraio','marzo','aprile','maggio','giugno','luglio','agosto','settembre','ottobre','novembre','dicembre'],
		nextDeliveryDay(now) { const d = new Date(now); d.setDate(d.getDate()+1); if (d.getDay() === 0) d.setDate(d.getDate()+1); return d; },
		formatDate(d) { return `${this.dayNames[d.getDay()]} ${d.getDate()} ${this.monthNames[d.getMonth()]}`; },
		pad(n) { return n.toString().padStart(2,'0'); },
		update() {
			const now = new Date();
			const cut = new Date(now); cut.setHours(this.cutoff, 0, 0, 0);
			let target = cut, delivery = this.nextDeliveryDay(now);
			if (now >= cut) { target = new Date(cut); target.setDate(target.getDate()+1); delivery = this.nextDeliveryDay(target); }
			const diff = target.getTime() - now.getTime();
			if (diff <= 0) { this.expired = true; return; }
			this.h = Math.floor(diff/3600000);
			this.m = Math.floor((diff%3600000)/60000);
			this.s = Math.floor((diff%60000)/1000);
			this.deliveryDate = this.formatDate(delivery);
		},
		init() { this.update(); setInterval(() => this.update(), 1000); }
	}"
	x-show="!expired"
	x-cloak
	class="space-y-2 rounded-xl border border-amber-200 bg-amber-50 p-4"
>
	<div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm">
		<?php pn_icon( 'clock', array( 'class' => 'h-4 w-4 shrink-0 text-amber-700' ) ); ?>
		<span><?php esc_html_e( 'Ordina entro', 'pharmanow' ); ?></span>
		<strong class="font-bold tabular-nums text-amber-700">
			<span x-text="pad(h)"></span>:<span x-text="pad(m)"></span>:<span x-text="pad(s)"></span>
		</strong>
		<span><?php esc_html_e( 'per riceverlo', 'pharmanow' ); ?></span>
		<strong class="font-semibold capitalize" x-text="deliveryDate"></strong>
	</div>
	<p x-show="h === 0 && m < 60" x-cloak class="flex items-center gap-1.5 text-xs font-medium text-rose-600">
		<?php pn_icon( 'zap', array( 'class' => 'h-3 w-3' ) ); ?>
		<?php esc_html_e( 'Sbrigati! Tempo quasi scaduto per la consegna rapida', 'pharmanow' ); ?>
	</p>
	<div class="flex items-center gap-3 pt-1 text-xs text-muted-foreground">
		<?php pn_icon( 'truck', array( 'class' => 'h-3.5 w-3.5' ) ); ?>
		<span><?php esc_html_e( 'Spediamo con:', 'pharmanow' ); ?></span>
		<div class="flex items-center gap-2">
			<?php
			foreach ( $pn_carriers as $pn_c ) :
				$pn_logo = $pn_carrier_logos[ $pn_c ] ?? null;
				if ( $pn_logo && file_exists( PN_THEME_DIR . '/assets/images/' . $pn_logo ) ) :
					?>
					<div class="flex h-6 w-12 items-center justify-center rounded border bg-background p-0.5">
						<img src="<?php echo esc_url( pn_asset( 'images/' . $pn_logo ) ); ?>" alt="<?php echo esc_attr( $pn_c ); ?>" class="h-auto max-h-full w-auto max-w-full object-contain">
					</div>
					<?php
				else :
					?>
					<span class="rounded border bg-background px-2 py-0.5 text-[11px] font-semibold"><?php echo esc_html( $pn_c ); ?></span>
					<?php
				endif;
			endforeach;
			?>
		</div>
	</div>
</div>
