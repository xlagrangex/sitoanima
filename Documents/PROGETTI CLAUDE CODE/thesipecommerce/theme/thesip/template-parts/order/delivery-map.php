<?php
/**
 * Delivery map (Shopify-style) — card full-width con mappa Leaflet (OpenStreetMap),
 * pin animato sulla destinazione + pin warehouse, overlay con indirizzo.
 *
 * Stack tutto FREE, no API key:
 *  - Leaflet (BSD-2 license)
 *  - Tiles: OpenStreetMap standard layer
 *  - Geocoding: Nominatim (cached come order meta — chiamata 1 volta)
 *
 * Variabili attese: $args['order'] (WC_Order), $args['ship_addr'] (string).
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pn        = is_array( $args ) ? $args : array();
$order     = $pn['order'] ?? null;
$ship_addr = $pn['ship_addr'] ?? '';

if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
	return;
}

$pn_geo = pn_get_order_shipping_geo( $order );
if ( ! $pn_geo ) {
	return; // Geocoding fallito → niente mappa.
}

$pn_warehouse = pn_get_warehouse_geo();
$pn_show_origin = ! empty( $pn_warehouse['lat'] ) && ! empty( $pn_warehouse['lng'] );

$pn_city = trim( (string) ( $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city() ) );
$pn_zip  = trim( (string) ( $order->get_shipping_postcode() ? $order->get_shipping_postcode() : $order->get_billing_postcode() ) );

$pn_map_id = 'pn-map-' . wp_unique_id();
?>

<section class="bg-white rounded-2xl shadow-sm overflow-hidden mb-6">
	<div class="relative">
		<div
			id="<?php echo esc_attr( $pn_map_id ); ?>"
			class="w-full aspect-[16/9] sm:aspect-[21/9] bg-gradient-to-br from-[#0B8894]/5 to-[#43CCB1]/10"
			role="img"
			aria-label="<?php esc_attr_e( 'Mappa indirizzo di consegna', 'pharmanow' ); ?>"
		></div>

		<?php // Address overlay card. ?>
		<div class="absolute left-4 sm:left-6 bottom-4 sm:bottom-6 right-4 sm:right-auto sm:max-w-sm pointer-events-none">
			<div class="bg-white rounded-xl shadow-lg p-4 sm:p-5 border border-gray-100 pointer-events-auto">
				<div class="flex items-start gap-3">
					<div class="w-10 h-10 shrink-0 rounded-lg bg-gradient-to-br from-[#0B8894] to-[#43CCB1] text-white flex items-center justify-center">
						<?php pn_icon( 'map-pin', array( 'class' => 'w-5 h-5' ) ); ?>
					</div>
					<div class="min-w-0">
						<p class="text-xs font-medium uppercase tracking-wider text-gray-500"><?php esc_html_e( 'Consegna a', 'pharmanow' ); ?></p>
						<?php if ( $pn_city || $pn_zip ) : ?>
							<p class="font-bold text-gray-900 mt-0.5">
								<?php echo esc_html( trim( $pn_city . ' ' . $pn_zip ) ); ?>
							</p>
						<?php endif; ?>
						<address class="not-italic text-sm text-gray-600 leading-snug mt-1">
							<?php echo wp_kses_post( $ship_addr ); ?>
						</address>
					</div>
				</div>
			</div>
		</div>

		<?php // Loading skeleton. ?>
		<div data-pn-map-skeleton="<?php echo esc_attr( $pn_map_id ); ?>" class="absolute inset-0 flex items-center justify-center text-gray-400 pointer-events-none">
			<div class="inline-flex items-center gap-2 bg-white/80 backdrop-blur-sm rounded-full px-4 py-2 text-sm">
				<?php pn_icon( 'map-pin', array( 'class' => 'w-4 h-4' ) ); ?>
				<span><?php esc_html_e( 'Caricamento mappa…', 'pharmanow' ); ?></span>
			</div>
		</div>
	</div>
</section>

<script>
(function () {
	var cfg = <?php echo wp_json_encode( array(
		'mapId'       => $pn_map_id,
		'dest'        => array( 'lat' => $pn_geo['lat'], 'lng' => $pn_geo['lng'] ),
		'origin'      => $pn_show_origin ? array( 'lat' => $pn_warehouse['lat'], 'lng' => $pn_warehouse['lng'] ) : null,
		'destLabel'   => trim( $pn_city . ( $pn_zip ? ' ' . $pn_zip : '' ) ) ?: __( 'Destinazione', 'pharmanow' ),
		'originLabel' => __( 'Pharmanow · Vigano (MI)', 'pharmanow' ),
		'attrib'      => __( 'Tiles &copy; OpenStreetMap contributors', 'pharmanow' ),
	), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?>;

	function ensureCss(href) {
		return new Promise(function (res) {
			if (document.querySelector('link[data-pn-leaflet]')) return res();
			var l = document.createElement('link');
			l.rel = 'stylesheet'; l.href = href; l.dataset.pnLeaflet = '1';
			l.onload = res; l.onerror = res;
			document.head.appendChild(l);
		});
	}
	function ensureJs(src) {
		return new Promise(function (res, rej) {
			if (window.L) return res();
			var existing = document.querySelector('script[data-pn-leaflet]');
			if (existing) {
				existing.addEventListener('load', res);
				existing.addEventListener('error', rej);
				return;
			}
			var s = document.createElement('script');
			s.src = src; s.async = true; s.dataset.pnLeaflet = '1';
			s.onload = res; s.onerror = rej;
			document.head.appendChild(s);
		});
	}

	function destPinSvg() {
		return '<div class="pn-marker-dest-wrap">' +
			'<svg width="36" height="44" viewBox="0 0 36 44" xmlns="http://www.w3.org/2000/svg">' +
				'<defs><linearGradient id="pmg-' + cfg.mapId + '" x1="0" y1="0" x2="0" y2="1">' +
					'<stop offset="0%" stop-color="#43CCB1"/><stop offset="100%" stop-color="#0B8894"/>' +
				'</linearGradient></defs>' +
				'<path d="M18 0C8.06 0 0 8.06 0 18c0 13 18 26 18 26s18-13 18-26C36 8.06 27.94 0 18 0z" fill="url(#pmg-' + cfg.mapId + ')"/>' +
				'<circle cx="18" cy="18" r="7" fill="#fff"/>' +
			'</svg></div>';
	}
	function origPinSvg() {
		return '<div class="pn-marker-orig-wrap">' +
			'<svg width="28" height="28" viewBox="0 0 28 28" xmlns="http://www.w3.org/2000/svg">' +
				'<circle cx="14" cy="14" r="12" fill="#fff" stroke="#0B8894" stroke-width="3"/>' +
				'<circle cx="14" cy="14" r="5" fill="#0B8894"/>' +
			'</svg></div>';
	}

	function init() {
		var el = document.getElementById(cfg.mapId);
		if (!el) return;

		Promise.all([
			ensureCss('https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'),
			ensureJs('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'),
		]).then(function () {
			if (!window.L) return;
			var L = window.L;

			var map = L.map(el, {
				zoomControl: true,
				scrollWheelZoom: false,        // evita scroll-jack
				dragging: !L.Browser.mobile,
				tap: false,
			}).setView([cfg.dest.lat, cfg.dest.lng], 13);

			L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
				maxZoom: 19,
				attribution: cfg.attrib,
			}).addTo(map);

			var sk = document.querySelector('[data-pn-map-skeleton="' + cfg.mapId + '"]');
			if (sk) sk.style.display = 'none';

			var destIcon = L.divIcon({
				className: 'pn-marker pn-marker-dest',
				html: destPinSvg(),
				iconSize: [36, 44],
				iconAnchor: [18, 44],
				popupAnchor: [0, -42],
			});
			L.marker([cfg.dest.lat, cfg.dest.lng], { icon: destIcon })
				.addTo(map)
				.bindPopup(cfg.destLabel);

			if (cfg.origin) {
				var origIcon = L.divIcon({
					className: 'pn-marker pn-marker-orig',
					html: origPinSvg(),
					iconSize: [28, 28],
					iconAnchor: [14, 14],
					popupAnchor: [0, -16],
				});
				L.marker([cfg.origin.lat, cfg.origin.lng], { icon: origIcon })
					.addTo(map)
					.bindPopup(cfg.originLabel);

				L.polyline(
					[[cfg.origin.lat, cfg.origin.lng], [cfg.dest.lat, cfg.dest.lng]],
					{ color: '#0B8894', weight: 3, opacity: 0.55, dashArray: '6, 6', lineCap: 'round' }
				).addTo(map);

				var bounds = L.latLngBounds(
					[cfg.origin.lat, cfg.origin.lng],
					[cfg.dest.lat, cfg.dest.lng]
				);
				map.fitBounds(bounds, { padding: [60, 60], maxZoom: 12 });
			}

			// Invalidate size dopo render iniziale (per ResizeObserver / layout shift).
			setTimeout(function () { map.invalidateSize(); }, 100);
		}).catch(function () {
			var sk = document.querySelector('[data-pn-map-skeleton="' + cfg.mapId + '"] span');
			if (sk) sk.textContent = '<?php echo esc_js( __( 'Mappa non disponibile', 'pharmanow' ) ); ?>';
		});
	}

	if ('IntersectionObserver' in window) {
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) {
				if (e.isIntersecting) { io.disconnect(); init(); }
			});
		}, { rootMargin: '200px' });
		var target = document.getElementById(cfg.mapId);
		if (target) io.observe(target);
	} else {
		init();
	}
})();
</script>

<style>
.pn-marker { background: transparent !important; border: 0 !important; }
.pn-marker-dest-wrap { animation: pn-marker-drop .6s cubic-bezier(.34,1.56,.64,1) both; transform-origin: bottom center; }
.pn-marker-orig-wrap { border-radius: 9999px; animation: pn-marker-pulse 2s ease-out infinite; }
@keyframes pn-marker-drop { 0% { transform: translateY(-30px) scale(.85); opacity: 0 } 100% { transform: translateY(0) scale(1); opacity: 1 } }
@keyframes pn-marker-pulse { 0% { box-shadow: 0 0 0 0 rgba(11,136,148,.45) } 70% { box-shadow: 0 0 0 14px rgba(11,136,148,0) } 100% { box-shadow: 0 0 0 0 rgba(11,136,148,0) } }
.leaflet-popup-content-wrapper { border-radius: 12px !important; }
.leaflet-popup-content { font-family: inherit !important; font-size: 13px !important; margin: 10px 14px !important; }
@media (prefers-reduced-motion: reduce) {
	.pn-marker-dest-wrap, .pn-marker-orig-wrap { animation: none !important; }
}
</style>
