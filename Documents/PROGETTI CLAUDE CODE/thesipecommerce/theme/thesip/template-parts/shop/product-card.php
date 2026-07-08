<?php
/**
 * Product card riusabile.
 *
 * Required: $product (WC_Product) o $args['product'] (WC_Product).
 * Opzionale: $args['variant'] = 'default' | 'compact'
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn_product = null;
if ( isset( $args['product'] ) && $args['product'] instanceof WC_Product ) {
	$pn_product = $args['product'];
} elseif ( isset( $product ) && $product instanceof WC_Product ) {
	$pn_product = $product;
} else {
	global $product;
	if ( $product instanceof WC_Product ) {
		$pn_product = $product;
	}
}

if ( ! $pn_product ) {
	return;
}

$pn_compact   = isset( $args['variant'] ) && 'compact' === $args['variant'];
$pn_id        = $pn_product->get_id();
$pn_url       = get_permalink( $pn_id );
$pn_title     = $pn_product->get_name();
$pn_in_stock  = $pn_product->is_in_stock();
// Thumbnail: prima pn-product-card (600), poi medium (300, sempre presente),
// poi -scaled. Evita che WP serva l'originale -scaled.png da 2-3 MB sulle
// card 158x158 (problema rilevato in Lighthouse audit).
$pn_thumb_id = (int) get_post_thumbnail_id( $pn_id );
$pn_thumb_size = 'pn-product-card';
if ( $pn_thumb_id ) {
	$pn_meta = wp_get_attachment_metadata( $pn_thumb_id );
	$pn_sizes = is_array( $pn_meta['sizes'] ?? null ) ? $pn_meta['sizes'] : array();
	if ( ! isset( $pn_sizes['pn-product-card'] ) ) {
		$pn_thumb_size = isset( $pn_sizes['medium'] ) ? 'medium' : ( isset( $pn_sizes['woocommerce_thumbnail'] ) ? 'woocommerce_thumbnail' : 'thumbnail' );
	}
}
$pn_thumb = $pn_thumb_id ? wp_get_attachment_image_url( $pn_thumb_id, $pn_thumb_size ) : wc_placeholder_img_src( 'pn-product-card' );
$pn_regular   = (float) $pn_product->get_regular_price();
$pn_sale      = $pn_product->is_on_sale() ? (float) $pn_product->get_sale_price() : 0;
$pn_brand     = $pn_product->get_meta( '_brand_name' ) ?: ( $pn_product->get_meta( '_linea_name' ) ?: $pn_product->get_meta( '_nome_azienda' ) );
if ( $pn_brand ) {
	$pn_brand = preg_replace( '/\s+(s\.?\s*r\.?\s*l\.?|s\.?\s*p\.?\s*a\.?|spa|srl|s\.a\.s\.|sas)\s*$/i', '', $pn_brand );
}
$pn_is_new    = ( time() - get_post_time( 'U', false, $pn_id ) ) < 30 * DAY_IN_SECONDS;
$pn_savings   = ( $pn_sale && $pn_regular > 0 ) ? (int) round( ( ( $pn_regular - $pn_sale ) / $pn_regular ) * 100 ) : 0;

$pn_btn_label = $pn_in_stock ? __( 'Aggiungi al carrello', 'pharmanow' ) : __( 'Esaurito', 'pharmanow' );
?>
<article class="group relative flex flex-col h-full overflow-hidden rounded-xl border border-pharma-teal/20 bg-card transition-all duration-200 hover:border-pharma-teal/50 hover:shadow-[0_8px_24px_-8px_rgb(11_136_148/0.18)]">

	<!-- Wishlist button (Alpine.store('pnWishlist')) -->
	<button
		type="button"
		x-data
		:class="$store.pnWishlist?.has(<?php echo (int) $pn_id; ?>) ? 'is-active' : ''"
		:aria-pressed="$store.pnWishlist?.has(<?php echo (int) $pn_id; ?>) ? 'true' : 'false'"
		:aria-label="$store.pnWishlist?.has(<?php echo (int) $pn_id; ?>) ? 'Rimuovi dai preferiti' : 'Aggiungi ai preferiti'"
		@click.prevent.stop="$store.pnWishlist.toggle(<?php echo (int) $pn_id; ?>, $el)"
		class="pn-heart-btn absolute right-3 top-3 z-10 inline-flex items-center justify-center h-9 w-9 rounded-full bg-white border border-pharma-teal/15 shadow-sm"
	>
		<?php pn_icon( 'heart', array( 'class' => 'h-4 w-4' ) ); ?>
	</button>

	<!-- Badges top-left (solo stato — il "-X%" sta accanto al prezzo) -->
	<div class="absolute left-3 top-3 z-10 flex flex-col gap-1.5">
		<?php if ( $pn_is_new ) : ?>
			<span class="inline-flex items-center justify-center rounded-md bg-emerald-50/80 border-2 border-emerald-500 px-2.5 py-0.5 text-xs font-bold text-emerald-700 shadow-sm">
				<?php esc_html_e( 'Nuovo', 'pharmanow' ); ?>
			</span>
		<?php endif; ?>
		<?php if ( ! $pn_in_stock ) : ?>
			<span class="inline-flex items-center justify-center rounded-md bg-gradient-to-br from-gray-600 to-gray-800 px-2.5 py-1 text-xs font-bold text-white shadow-sm">
				<?php esc_html_e( 'Esaurito', 'pharmanow' ); ?>
			</span>
		<?php endif; ?>
	</div>

	<a href="<?php echo esc_url( $pn_url ); ?>" class="relative aspect-square overflow-hidden bg-white p-5">
		<?php if ( $pn_thumb_id ) : ?>
			<?php
			// wp_get_attachment_image: genera srcset multi-resolution + width/height
			// espliciti + loading="lazy" attributes, evita CLS, browser sceglie la
			// resolution giusta. sizes="(card 158-200px viewport-relative)".
			echo wp_get_attachment_image(
				$pn_thumb_id,
				$pn_thumb_size,
				false,
				array(
					'alt'      => esc_attr( $pn_title ),
					'loading'  => 'lazy',
					'decoding' => 'async',
					'class'    => 'absolute inset-0 h-full w-full object-contain p-5 transition-transform duration-500 ease-out group-hover:scale-[1.06]',
					'sizes'    => '(max-width: 640px) 50vw, (max-width: 1024px) 33vw, 200px',
				)
			);
			?>
		<?php elseif ( $pn_thumb ) : ?>
			<img
				src="<?php echo esc_url( $pn_thumb ); ?>"
				alt="<?php echo esc_attr( $pn_title ); ?>"
				loading="lazy"
				decoding="async"
				width="600"
				height="600"
				class="absolute inset-0 h-full w-full object-contain p-5 transition-transform duration-500 ease-out group-hover:scale-[1.06]"
			>
		<?php else : ?>
			<div class="flex h-full items-center justify-center text-xs text-muted-foreground">
				<?php esc_html_e( 'Nessuna immagine', 'pharmanow' ); ?>
			</div>
		<?php endif; ?>
	</a>

	<div class="flex flex-1 flex-col gap-2 p-4">
		<!-- Brand sempre renderizzato (con &nbsp; se mancante) per uniformità altezza -->
		<span class="uppercase tracking-wide text-gray-500 text-[11px] font-medium leading-tight line-clamp-1 min-h-[14px]">
			<?php echo $pn_brand ? esc_html( $pn_brand ) : '&nbsp;'; ?>
		</span>

		<a href="<?php echo esc_url( $pn_url ); ?>" class="flex-1">
			<h3 class="line-clamp-2 font-bold uppercase tracking-wide leading-snug text-pharma-teal-dark text-sm transition-colors group-hover:text-pharma-teal min-h-[2.5em]">
				<?php echo esc_html( $pn_title ); ?>
			</h3>
		</a>

		<!-- Prezzo -->
		<div class="mt-1 flex items-center gap-2 flex-wrap">
			<?php if ( $pn_sale > 0 ) : ?>
				<!-- Sale price: gradient emerald→teal (allineato al Next), font-semibold, tracking stretto -->
				<span class="text-2xl font-semibold tabular-nums tracking-tight bg-gradient-to-r from-emerald-500 to-teal-600 bg-clip-text text-transparent">
					<?php echo wp_kses_post( wc_price( $pn_sale ) ); ?>
				</span>
				<span class="text-sm text-gray-400 line-through tabular-nums tracking-tight">
					<?php echo wp_kses_post( wc_price( $pn_regular ) ); ?>
				</span>
				<span class="inline-flex items-center justify-center rounded-md bg-gradient-to-r from-emerald-500 to-teal-600 px-1.5 py-0.5 text-xs font-bold text-white shadow-sm">
					-<?php echo (int) $pn_savings; ?>%
				</span>
			<?php else : ?>
				<span class="text-xl font-semibold text-pharma-teal tabular-nums tracking-tight">
					<?php echo wp_kses_post( wc_price( $pn_regular ) ); ?>
				</span>
			<?php endif; ?>
		</div>

		<!-- Bottone Aggiungi al carrello — AJAX Woo (intercept globale in main.js) -->
		<a
			href="<?php echo esc_url( '?add-to-cart=' . $pn_id ); ?>"
			data-slot="button"
			data-product_id="<?php echo esc_attr( (string) $pn_id ); ?>"
			data-product_sku="<?php echo esc_attr( $pn_product->get_sku() ); ?>"
			data-quantity="1"
			rel="nofollow"
			aria-label="<?php echo esc_attr( $pn_btn_label ); ?>"
			class="<?php echo $pn_in_stock ? 'add_to_cart_button ajax_add_to_cart pn-atc-btn' : 'pointer-events-none opacity-50'; ?> mt-2 w-full h-9 rounded-md bg-pharma-teal text-white text-sm font-semibold hover:bg-pharma-teal-dark"
			onclick="event.stopPropagation();"
		>
			<span class="pn-atc-default">
				<?php pn_icon( 'shopping-cart', array( 'class' => 'h-4 w-4' ) ); ?>
				<span><?php echo esc_html( $pn_btn_label ); ?></span>
			</span>
			<span class="pn-atc-success">
				<?php pn_icon( 'check-circle-2', array( 'class' => 'h-4 w-4' ) ); ?>
				<span><?php esc_html_e( 'Aggiunto!', 'pharmanow' ); ?></span>
			</span>
		</a>
	</div>
</article>
