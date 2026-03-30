/**
 * Color Swatches Module
 *
 * Syncs the visual swatch buttons with the hidden WooCommerce
 * <select> element for variation attribute selection.
 *
 * @package BizStudio
 */

(function () {
	'use strict';

	document.querySelectorAll('.biz-swatches').forEach((container) => {
		const attributeName = container.dataset.attribute;
		const hiddenSelect = container.querySelector('.biz-swatches__select-hidden');
		if (!hiddenSelect) return;

		const swatches = container.querySelectorAll('.biz-swatch');

		swatches.forEach((swatch) => {
			swatch.addEventListener('click', () => {
				const value = swatch.dataset.value;

				// If already active, deselect (reset)
				if (swatch.classList.contains('biz-swatch--active')) {
					swatch.classList.remove('biz-swatch--active');
					hiddenSelect.value = '';
					hiddenSelect.dispatchEvent(new Event('change', { bubbles: true }));
					return;
				}

				// Deactivate all, activate clicked
				swatches.forEach((s) => s.classList.remove('biz-swatch--active'));
				swatch.classList.add('biz-swatch--active');

				// Update hidden select
				hiddenSelect.value = value;
				hiddenSelect.dispatchEvent(new Event('change', { bubbles: true }));
			});
		});

		// Listen for WC resetting variations (e.g. "Clear" link)
		hiddenSelect.addEventListener('change', () => {
			const currentValue = hiddenSelect.value;
			swatches.forEach((s) => {
				s.classList.toggle('biz-swatch--active', s.dataset.value === currentValue);
			});
		});

		// Observe disabled options — grey out unavailable swatches
		const observer = new MutationObserver(() => {
			swatches.forEach((swatch) => {
				const option = hiddenSelect.querySelector(`option[value="${swatch.dataset.value}"]`);
				if (option && option.disabled) {
					swatch.classList.add('biz-swatch--disabled');
				} else {
					swatch.classList.remove('biz-swatch--disabled');
				}
			});
		});

		observer.observe(hiddenSelect, {
			childList: true,
			subtree: true,
			attributes: true,
			attributeFilter: ['disabled'],
		});
	});
})();
