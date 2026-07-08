<?php
/**
 * Social proof: "X stanno guardando · Y acquistati oggi".
 *
 * Replica `LiveIndicator.tsx` del Next, hash deterministico per SSR-safe.
 *
 * @var array $args { product_id: string }
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

$pn_pid = (string) ( $args['product_id'] ?? '' );

// Hash FNV-1a 32-bit (replica esatta del JS).
$pn_hash = function ( string $s ): int {
	$h = 2166136261;
	for ( $i = 0, $len = strlen( $s ); $i < $len; $i++ ) {
		$h ^= ord( $s[ $i ] );
		$h = ( $h * 16777619 ) & 0xFFFFFFFF;
	}
	return $h;
};
$pn_seed         = $pn_hash( $pn_pid );
$pn_day          = (int) floor( time() / 86400 );
$pn_initial_w    = 12 + ( $pn_seed % 22 );
$pn_initial_b    = 1 + ( $pn_hash( $pn_pid . (string) $pn_day ) % 12 );
?>
<div
	x-data="{
		watching: <?php echo (int) $pn_initial_w; ?>,
		bought: <?php echo (int) $pn_initial_b; ?>,
		init() {
			const tickW = () => {
				const delta = Math.floor(Math.random() * 7) - 3;
				this.watching = Math.max(8, Math.min(45, this.watching + delta));
				setTimeout(tickW, 25000 + Math.random() * 20000);
			};
			const tickB = () => {
				this.bought = Math.min(99, this.bought + 1);
				setTimeout(tickB, 60000 + Math.random() * 120000);
			};
			setTimeout(tickW, 25000 + Math.random() * 20000);
			setTimeout(tickB, 60000 + Math.random() * 120000);
		}
	}"
	class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-muted-foreground"
>
	<span class="flex items-center gap-1.5">
		<span class="relative flex h-2 w-2">
			<span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-rose-500 opacity-60"></span>
			<span class="relative inline-flex h-2 w-2 rounded-full bg-rose-500"></span>
		</span>
		<strong class="font-semibold text-foreground tabular-nums" x-text="watching"></strong> <?php esc_html_e( 'stanno guardando', 'pharmanow' ); ?>
	</span>
	<span class="text-muted-foreground/50">·</span>
	<span>
		<strong class="font-semibold text-foreground tabular-nums" x-text="bought"></strong> <?php esc_html_e( 'acquistati oggi', 'pharmanow' ); ?>
	</span>
</div>
