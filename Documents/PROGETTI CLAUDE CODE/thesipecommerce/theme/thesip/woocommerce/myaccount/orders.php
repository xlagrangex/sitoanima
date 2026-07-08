<?php
/**
 * Lista ordini. Replica `app/(shop)/account/ordini/page.tsx` del Next.
 *
 * @package Pharmanow
 *
 * @var bool $has_orders
 * @var WC_Order[] $customer_orders
 */

defined( 'ABSPATH' ) || exit;

$pn_user_id = get_current_user_id();
$pn_per_page = 10;
$pn_paged    = max( 1, isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1 );
$pn_query    = wc_get_orders(
	array(
		'customer_id' => $pn_user_id,
		'limit'       => $pn_per_page,
		'paged'       => $pn_paged,
		'paginate'    => true,
		'orderby'     => 'date',
		'order'       => 'DESC',
		'status'      => array_keys( wc_get_order_statuses() ),
	)
);
$pn_orders     = $pn_query->orders;
$pn_total      = (int) $pn_query->total;
$pn_max_pages  = (int) $pn_query->max_num_pages;
?>
<div class="space-y-6">
	<div class="flex items-end justify-between gap-3">
		<div>
			<h2 class="text-2xl font-bold"><?php esc_html_e( 'I miei ordini', 'pharmanow' ); ?></h2>
			<p class="mt-1 text-sm text-muted-foreground">
				<?php
				/* translators: %d: count */
				printf( esc_html( _n( '%d ordine totale', '%d ordini totali', $pn_total, 'pharmanow' ) ), (int) $pn_total );
				?>
			</p>
		</div>
	</div>

	<?php if ( empty( $pn_orders ) ) : ?>
		<div class="rounded-xl border bg-card p-10 text-center">
			<div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-muted text-muted-foreground"><?php pn_icon( 'package', array( 'class' => 'h-6 w-6' ) ); ?></div>
			<h3 class="text-base font-semibold"><?php esc_html_e( 'Non hai ancora effettuato ordini', 'pharmanow' ); ?></h3>
			<p class="mt-1 text-sm text-muted-foreground"><?php esc_html_e( 'I tuoi ordini appariranno qui dopo il primo acquisto.', 'pharmanow' ); ?></p>
			<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ?: home_url( '/' ) ); ?>" class="mt-4 inline-flex h-10 items-center gap-2 rounded-md bg-pharma-teal px-5 text-sm font-semibold text-white hover:bg-pharma-teal-dark"><?php esc_html_e( 'Inizia a comprare', 'pharmanow' ); ?></a>
		</div>
	<?php else : ?>
		<div class="divide-y rounded-xl border bg-card">
			<?php foreach ( $pn_orders as $pn_o ) :
				$pn_status = pn_order_status_tone( $pn_o->get_status() );
				$pn_count  = $pn_o->get_item_count();
				$pn_url    = $pn_o->get_view_order_url();
				?>
				<a href="<?php echo esc_url( $pn_url ); ?>" class="group flex items-center gap-4 p-4 transition-colors hover:bg-muted/30">
					<div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-700"><?php pn_icon( 'package', array( 'class' => 'h-5 w-5' ) ); ?></div>
					<div class="min-w-0 flex-1">
						<div class="flex flex-wrap items-center gap-2">
							<span class="text-sm font-semibold">
								<?php
								/* translators: %s: order number */
								printf( esc_html__( 'Ordine #%s', 'pharmanow' ), esc_html( $pn_o->get_order_number() ) );
								?>
							</span>
							<span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium <?php echo esc_attr( pn_order_status_classes( $pn_status['tone'] ) ); ?>">
								<?php echo esc_html( $pn_status['label'] ); ?>
							</span>
						</div>
						<p class="mt-0.5 text-xs text-muted-foreground">
							<?php echo esc_html( wc_format_datetime( $pn_o->get_date_created(), 'j M Y' ) ); ?> ·
							<?php
							/* translators: %d: count */
							printf( esc_html( _n( '%d articolo', '%d articoli', $pn_count, 'pharmanow' ) ), (int) $pn_count );
							?>
						</p>
					</div>
					<div class="text-right shrink-0">
						<p class="text-sm font-bold tabular-nums"><?php echo wp_kses_post( $pn_o->get_formatted_order_total() ); ?></p>
					</div>
					<span class="ml-2 text-muted-foreground transition-transform group-hover:translate-x-1"><?php pn_icon( 'chevron-right', array( 'class' => 'h-4 w-4' ) ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>

		<?php if ( $pn_max_pages > 1 ) : ?>
			<nav class="flex items-center justify-center gap-2">
				<?php for ( $i = 1; $i <= $pn_max_pages; $i++ ) :
					$pn_active = $i === $pn_paged;
					$pn_url    = $i === 1 ? remove_query_arg( 'paged' ) : add_query_arg( 'paged', $i );
					?>
					<a href="<?php echo esc_url( $pn_url ); ?>" class="flex h-9 w-9 items-center justify-center rounded-md border text-sm transition-colors <?php echo $pn_active ? 'bg-pharma-teal border-pharma-teal text-white font-semibold' : 'bg-card hover:bg-muted'; ?>">
						<?php echo (int) $i; ?>
					</a>
				<?php endfor; ?>
			</nav>
		<?php endif; ?>
	<?php endif; ?>
</div>
