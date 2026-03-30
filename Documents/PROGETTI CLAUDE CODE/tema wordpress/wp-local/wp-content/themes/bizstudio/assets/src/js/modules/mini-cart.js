/**
 * Mini Cart Module
 *
 * @package BizStudio
 */

(function () {
	'use strict';

	// Cart toggle button
	const cartToggle = document.getElementById('biz-cart-toggle');
	const cartOverlay = document.getElementById('biz-mini-cart-overlay');

	cartToggle?.addEventListener('click', () => {
		window.openMiniCart?.();
	});

	// Close button (uses event delegation since mini-cart HTML gets replaced)
	document.addEventListener('click', (e) => {
		if (e.target.closest('#biz-mini-cart-close')) {
			window.closeMiniCart?.();
		}
	});

	// Close on overlay click
	cartOverlay?.addEventListener('click', () => {
		window.closeMiniCart?.();
	});

	// Close on Escape
	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape') {
			window.closeMiniCart?.();
		}
	});
})();
