<?php
/**
 * Colonna info prodotto: live indicator, brand, titolo, badges, prezzo, qty+ATC,
 * countdown, free shipping bar, trust box, MinSan.
 *
 * Replica `components/shop/product/ProductInfo.tsx` del Next.
 *
 * @var array $args { product: WC_Product, meta: array }
 * @package Pharmanow
 */

defined( 'ABSPATH' ) || exit;

$pn_p = $args['product'] ?? null;
if ( ! $pn_p instanceof WC_Product ) {
	return;
}
$pn_meta = $args['meta'] ?? pn_pharma_meta( $pn_p );

$pn_id        = $pn_p->get_id();
$pn_in_stock  = $pn_p->is_in_stock();
$pn_regular   = (float) $pn_p->get_regular_price();
$pn_sale      = $pn_p->is_on_sale() ? (float) $pn_p->get_sale_price() : 0;
$pn_savings   = ( $pn_sale && $pn_regular > 0 ) ? (int) round( ( ( $pn_regular - $pn_sale ) / $pn_regular ) * 100 ) : 0;
$pn_max_qty   = $pn_p->get_stock_quantity();
$pn_max_qty   = ( $pn_max_qty && $pn_max_qty > 0 ) ? min( $pn_max_qty, 99 ) : 99;
$pn_btn_label = $pn_in_stock ? __( 'Aggiungi al Carrello', 'pharmanow' ) : __( 'Esaurito', 'pharmanow' );
?>
<div class="flex flex-col gap-5">

	<!-- Live indicator + sale badge -->
	<div class="flex flex-wrap items-center gap-3">
		<?php get_template_part( 'template-parts/product/live-indicator', null, array( 'product_id' => (string) $pn_id ) ); ?>
		<?php if ( $pn_savings > 0 ) : ?>
			<span class="rounded-md bg-rose-100 px-2 py-0.5 text-xs font-bold text-rose-700">
				-<?php echo (int) $pn_savings; ?>%
			</span>
		<?php endif; ?>
	</div>

	<!-- Brand -->
	<?php
	if ( '' !== $pn_meta['brand_name'] ) :
		// Preferito: archivio tassonomia /marchi/{slug}/ (filtra correttamente).
		// Fallback: catalogo con filtro brand[] (notazione array, richiesta dal pre_get_posts).
		if ( '' !== $pn_meta['brand_slug'] ) {
			$pn_brand_url = home_url( '/marchi/' . $pn_meta['brand_slug'] . '/' );
		} else {
			$pn_catalog   = get_permalink( wc_get_page_id( 'shop' ) ) ?: home_url( '/catalogo/' );
			$pn_brand_url = add_query_arg( array( 'brand' => array( $pn_meta['brand_name'] ) ), $pn_catalog );
		}
		?>
		<div>
			<p class="text-[10px] font-semibold uppercase tracking-wider text-muted-foreground"><?php esc_html_e( 'Brand', 'pharmanow' ); ?></p>
			<p class="text-lg font-bold"><?php echo esc_html( $pn_meta['brand_name'] ); ?></p>
			<a href="<?php echo esc_url( $pn_brand_url ); ?>" class="inline-flex items-center gap-1 text-xs font-medium text-pharma-teal hover:underline">
				<?php esc_html_e( 'Vedi tutti i prodotti', 'pharmanow' ); ?> <?php pn_icon( 'arrow-right', array( 'class' => 'h-3 w-3' ) ); ?>
			</a>
		</div>
	<?php endif; ?>

	<!-- Titolo -->
	<h1 class="text-2xl font-bold uppercase leading-tight md:text-3xl lg:text-4xl"><?php the_title(); ?></h1>

	<!-- Badges pharma -->
	<?php get_template_part( 'template-parts/product/badges', null, array( 'tags' => $pn_meta['tags'] ) ); ?>

	<!-- Prezzo + savings + disponibilità -->
	<div class="flex flex-wrap items-start justify-between gap-3 border-t pt-4">
		<div>
			<?php if ( $pn_sale > 0 ) : ?>
				<div class="flex items-baseline gap-3">
					<span class="text-3xl font-bold tabular-nums tracking-tight bg-gradient-to-r from-emerald-500 to-teal-600 bg-clip-text text-transparent">
						<?php echo wp_kses_post( wc_price( $pn_sale ) ); ?>
					</span>
					<span class="text-base text-gray-400 line-through tabular-nums tracking-tight">
						<?php echo wp_kses_post( wc_price( $pn_regular ) ); ?>
					</span>
				</div>
				<?php
				$pn_saved = $pn_regular - $pn_sale;
				if ( $pn_saved > 0 ) :
					?>
					<p class="text-sm font-semibold text-emerald-600">
						<?php
						/* translators: 1: amount saved, 2: percentage */
						printf(
							esc_html__( 'Risparmi %1$s (%2$d%%)', 'pharmanow' ),
							wp_kses_post( wc_price( $pn_saved ) ),
							(int) $pn_savings
						);
						?>
					</p>
				<?php endif; ?>
			<?php else : ?>
				<span class="text-3xl font-bold text-pharma-teal tabular-nums tracking-tight">
					<?php echo wp_kses_post( wc_price( $pn_regular ) ); ?>
				</span>
			<?php endif; ?>
		</div>
		<div class="flex items-center gap-2 text-sm">
			<span class="h-2.5 w-2.5 rounded-full <?php echo $pn_in_stock ? 'bg-emerald-500' : 'bg-rose-500'; ?>"></span>
			<span class="font-semibold <?php echo $pn_in_stock ? 'text-emerald-600' : 'text-rose-600'; ?>">
				<?php echo $pn_in_stock ? esc_html__( 'Disponibile', 'pharmanow' ) : esc_html__( 'Esaurito', 'pharmanow' ); ?>
			</span>
		</div>
	</div>

	<!-- Quantity + ATC. Form `.cart` viene intercettato dal listener universale
	     in main.js → AJAX add-to-cart, fragments refresh, drawer auto-open. -->
	<form
		class="cart flex flex-col gap-3 sm:flex-row sm:items-stretch"
		method="post"
		enctype="multipart/form-data"
		x-data="{ qty: 1, max: <?php echo (int) $pn_max_qty; ?> }"
	>
		<div class="flex h-12 items-center rounded-md border bg-background">
			<button type="button" @click="qty = Math.max(1, qty - 1); $refs.qtyInput.value = qty" :disabled="qty <= 1 || !<?php echo $pn_in_stock ? 'true' : 'false'; ?>" aria-label="<?php esc_attr_e( 'Diminuisci quantità', 'pharmanow' ); ?>" class="flex h-full w-11 items-center justify-center text-muted-foreground transition-colors hover:bg-muted disabled:opacity-40">
				<?php pn_icon( 'minus', array( 'class' => 'h-4 w-4' ) ); ?>
			</button>
			<span class="flex h-full w-11 items-center justify-center text-sm font-semibold tabular-nums" x-text="qty"></span>
			<button type="button" @click="qty = Math.min(max, qty + 1); $refs.qtyInput.value = qty" :disabled="qty >= max || !<?php echo $pn_in_stock ? 'true' : 'false'; ?>" aria-label="<?php esc_attr_e( 'Aumenta quantità', 'pharmanow' ); ?>" class="flex h-full w-11 items-center justify-center text-muted-foreground transition-colors hover:bg-muted disabled:opacity-40">
				<?php pn_icon( 'plus', array( 'class' => 'h-4 w-4' ) ); ?>
			</button>
			<input type="hidden" name="quantity" value="1" x-ref="qtyInput">
		</div>

		<button
			type="submit"
			name="add-to-cart"
			value="<?php echo (int) $pn_id; ?>"
			data-pdp-cta
			<?php disabled( ! $pn_in_stock, true ); ?>
			class="pn-atc-btn h-12 flex-1 rounded-md bg-pharma-teal text-white text-base font-semibold hover:bg-pharma-teal-dark disabled:opacity-60 disabled:cursor-not-allowed"
		>
			<span class="pn-atc-default">
				<?php pn_icon( 'shopping-cart', array( 'class' => 'h-5 w-5' ) ); ?>
				<?php echo esc_html( $pn_btn_label ); ?>
			</span>
			<span class="pn-atc-success">
				<?php pn_icon( 'check-circle-2', array( 'class' => 'h-5 w-5' ) ); ?>
				<?php esc_html_e( 'Aggiunto al carrello!', 'pharmanow' ); ?>
			</span>
		</button>
	</form>

	<!-- Wishlist + share -->
	<div class="flex items-center gap-2">
		<button
			type="button"
			x-data
			:class="$store.pnWishlist?.has(<?php echo (int) $pn_id; ?>) ? 'is-active' : ''"
			:aria-pressed="$store.pnWishlist?.has(<?php echo (int) $pn_id; ?>) ? 'true' : 'false'"
			:aria-label="$store.pnWishlist?.has(<?php echo (int) $pn_id; ?>) ? 'Rimuovi dai preferiti' : 'Aggiungi ai preferiti'"
			@click="$store.pnWishlist.toggle(<?php echo (int) $pn_id; ?>, $el)"
			class="pn-heart-btn flex h-10 w-10 items-center justify-center rounded-full border bg-background"
		>
			<?php pn_icon( 'heart', array( 'class' => 'h-4 w-4' ) ); ?>
		</button>
		<?php get_template_part( 'template-parts/product/share', null, array( 'title' => $pn_p->get_name() ) ); ?>
	</div>

	<!-- Delivery countdown -->
	<?php get_template_part( 'template-parts/product/delivery-countdown' ); ?>

	<!-- Free shipping progress -->
	<?php get_template_part( 'template-parts/product/free-shipping-progress' ); ?>

	<!-- Checkout trust box -->
	<?php get_template_part( 'template-parts/product/checkout-trust-box' ); ?>

	<!-- Codice articolo -->
	<?php if ( '' !== $pn_meta['minsan'] || '' !== $pn_meta['ean'] ) : ?>
		<p class="border-t pt-3 text-[11px] text-muted-foreground">
			<?php if ( '' !== $pn_meta['minsan'] ) : ?>
				<?php esc_html_e( 'Cod. articolo:', 'pharmanow' ); ?> <span class="font-medium text-foreground/80"><?php echo esc_html( $pn_meta['minsan'] ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $pn_meta['ean'] ) : ?>
				<?php echo '' !== $pn_meta['minsan'] ? '· ' : ''; ?>EAN: <span class="font-medium text-foreground/80"><?php echo esc_html( $pn_meta['ean'] ); ?></span>
			<?php endif; ?>
		</p>
	<?php endif; ?>
</div>
