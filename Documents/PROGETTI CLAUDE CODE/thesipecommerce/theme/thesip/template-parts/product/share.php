<?php
/**
 * Share dropdown (WhatsApp / Email / Copia link). Replica `ShareIcon` di ProductInfo.tsx.
 *
 * @var array $args { title: string }
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

$pn_title = $args['title'] ?? '';
$pn_text  = rawurlencode( $pn_title . ' su Pharmanow' );
?>
<div
	x-data="{ open: false, async copy() { try { await navigator.clipboard.writeText(window.location.href); this.toast('Link copiato'); } catch { this.toast('Impossibile copiare'); } }, toast(msg) { window.dispatchEvent(new CustomEvent('pn-toast', { detail: msg })); } }"
	@mouseleave="open = false"
	class="relative"
>
	<button type="button" @click="open = !open" aria-label="<?php esc_attr_e( 'Condividi', 'pharmanow' ); ?>" class="flex h-10 w-10 items-center justify-center rounded-full border bg-background text-muted-foreground shadow-sm transition-colors hover:bg-muted">
		<?php pn_icon( 'share-2', array( 'class' => 'h-4 w-4' ) ); ?>
	</button>
	<div
		x-show="open"
		x-cloak
		x-transition.opacity
		class="absolute left-0 top-full z-20 mt-2 flex gap-1 rounded-lg border bg-background p-1.5 shadow-lg"
	>
		<a :href="`https://wa.me/?text=<?php echo $pn_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>%20${encodeURIComponent(window.location.href)}`" target="_blank" rel="noopener noreferrer" class="flex h-8 w-8 items-center justify-center rounded text-muted-foreground hover:bg-muted">
			<?php pn_icon( 'message-circle', array( 'class' => 'h-4 w-4' ) ); ?>
		</a>
		<a :href="`mailto:?subject=<?php echo $pn_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>&body=${encodeURIComponent(window.location.href)}`" class="flex h-8 w-8 items-center justify-center rounded text-muted-foreground hover:bg-muted">
			<?php pn_icon( 'mail', array( 'class' => 'h-4 w-4' ) ); ?>
		</a>
		<button type="button" @click="copy()" class="flex h-8 w-8 items-center justify-center rounded text-muted-foreground hover:bg-muted">
			<?php pn_icon( 'copy', array( 'class' => 'h-4 w-4' ) ); ?>
		</button>
	</div>
</div>
