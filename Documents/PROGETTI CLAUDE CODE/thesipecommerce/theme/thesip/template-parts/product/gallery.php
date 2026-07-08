<?php
/**
 * Gallery prodotto: thumbs verticali desktop, dots mobile, click → lightbox.
 *
 * Replica `components/shop/product/Gallery.tsx` del Next.
 *
 * @var array $args { product: WC_Product }
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

$pn_p = $args['product'] ?? null;
if ( ! $pn_p instanceof WC_Product ) {
	return;
}

$pn_id     = $pn_p->get_id();
$pn_images = array();

$pn_thumb_id = get_post_thumbnail_id( $pn_id );
if ( $pn_thumb_id ) {
	$pn_images[] = $pn_thumb_id;
}
foreach ( $pn_p->get_gallery_image_ids() as $gid ) {
	if ( $gid && (int) $gid !== (int) $pn_thumb_id ) {
		$pn_images[] = (int) $gid;
	}
}
$pn_total    = count( $pn_images );
$pn_multiple = $pn_total > 1;
$pn_name     = $pn_p->get_name();
?>
<?php if ( 0 === $pn_total ) : ?>
	<div class="flex aspect-square w-full items-center justify-center rounded-lg bg-muted text-muted-foreground">
		<?php esc_html_e( 'Nessuna immagine disponibile', 'pharmanow' ); ?>
	</div>
<?php else : ?>
	<div
		x-data="{
			active: 0,
			lightbox: -1,
			open(i) { this.lightbox = i; document.body.style.overflow = 'hidden'; },
			close() { this.lightbox = -1; document.body.style.overflow = ''; },
			next() { this.lightbox = (this.lightbox + 1) % <?php echo (int) $pn_total; ?>; },
			prev() { this.lightbox = (this.lightbox - 1 + <?php echo (int) $pn_total; ?>) % <?php echo (int) $pn_total; ?>; }
		}"
		@keydown.escape.window="close()"
		@keydown.arrow-right.window="if (lightbox >= 0) next()"
		@keydown.arrow-left.window="if (lightbox >= 0) prev()"
		class="flex flex-col gap-3 md:flex-row md:gap-4"
	>
		<?php if ( $pn_multiple ) : ?>
			<!-- Thumbs verticali (desktop) -->
			<div class="order-2 hidden w-20 shrink-0 flex-col gap-2 md:order-1 md:flex">
				<?php foreach ( $pn_images as $i => $img_id ) : ?>
					<button
						type="button"
						@click="active = <?php echo (int) $i; ?>"
						aria-label="<?php echo esc_attr( sprintf( __( 'Mostra immagine %d', 'pharmanow' ), $i + 1 ) ); ?>"
						:class="active === <?php echo (int) $i; ?> ? 'border-pharma-teal' : 'border-transparent hover:border-gray-300'"
						class="relative aspect-square w-full overflow-hidden rounded-md border-2 bg-muted transition-colors"
					>
						<?php
						echo wp_get_attachment_image(
							$img_id,
							array( 80, 80 ),
							false,
							array(
								'class' => 'absolute inset-0 h-full w-full object-cover',
								'alt'   => esc_attr( $pn_name ),
							)
						);
						?>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<!-- Immagine principale -->
		<div class="order-1 min-w-0 flex-1 md:order-2">
			<?php foreach ( $pn_images as $i => $img_id ) : ?>
				<button
					type="button"
					x-show="active === <?php echo (int) $i; ?>"
					x-cloak
					@click="open(<?php echo (int) $i; ?>)"
					aria-label="<?php esc_attr_e( 'Apri immagine ingrandita', 'pharmanow' ); ?>"
					class="relative block aspect-square w-full overflow-hidden rounded-xl border border-border/40 bg-muted/30 p-6 md:p-10"
				>
					<?php
					echo wp_get_attachment_image(
						$img_id,
						'full',
						false,
						array(
							'class'   => 'absolute inset-0 h-full w-full object-contain',
							'alt'     => esc_attr( $pn_name ),
							'loading' => 0 === $i ? 'eager' : 'lazy',
						)
					);
					?>
				</button>
			<?php endforeach; ?>
		</div>

		<?php if ( $pn_multiple ) : ?>
			<!-- Dots mobile -->
			<div class="order-3 flex justify-center gap-1.5 md:hidden">
				<?php foreach ( $pn_images as $i => $_ ) : ?>
					<button
						type="button"
						@click="active = <?php echo (int) $i; ?>"
						aria-label="<?php echo esc_attr( sprintf( __( 'Vai a immagine %d', 'pharmanow' ), $i + 1 ) ); ?>"
						:class="active === <?php echo (int) $i; ?> ? 'w-6 bg-pharma-teal' : 'w-1.5 bg-gray-300'"
						class="h-1.5 rounded-full transition-all"
					></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<!-- Lightbox -->
		<div
			x-show="lightbox >= 0"
			x-cloak
			@click.self="close()"
			class="fixed inset-0 z-[100] flex items-center justify-center bg-black/85 p-4"
		>
			<button type="button" @click="close()" aria-label="<?php esc_attr_e( 'Chiudi', 'pharmanow' ); ?>" class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20">
				<?php pn_icon( 'x', array( 'class' => 'h-6 w-6' ) ); ?>
			</button>
			<?php if ( $pn_multiple ) : ?>
				<button type="button" @click.stop="prev()" aria-label="<?php esc_attr_e( 'Precedente', 'pharmanow' ); ?>" class="absolute left-4 flex h-12 w-12 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20">
					<?php pn_icon( 'chevron-left', array( 'class' => 'h-6 w-6' ) ); ?>
				</button>
				<button type="button" @click.stop="next()" aria-label="<?php esc_attr_e( 'Successiva', 'pharmanow' ); ?>" class="absolute right-4 flex h-12 w-12 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20">
					<?php pn_icon( 'chevron-right', array( 'class' => 'h-6 w-6' ) ); ?>
				</button>
			<?php endif; ?>
			<?php foreach ( $pn_images as $i => $img_id ) : ?>
				<div x-show="lightbox === <?php echo (int) $i; ?>" x-cloak class="max-h-[90vh] max-w-[90vw]">
					<?php
					echo wp_get_attachment_image(
						$img_id,
						'full',
						false,
						array(
							'class' => 'max-h-[90vh] max-w-[90vw] object-contain',
							'alt'   => esc_attr( $pn_name ),
						)
					);
					?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
<?php endif; ?>
