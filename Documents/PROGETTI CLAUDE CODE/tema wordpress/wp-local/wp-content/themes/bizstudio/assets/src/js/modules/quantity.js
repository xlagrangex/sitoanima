/**
 * Quantity +/- Buttons
 * Wraps WooCommerce quantity inputs with styled buttons.
 *
 * @package BizStudio
 */

(function () {
	'use strict';

	function wrapQuantityInputs() {
		document.querySelectorAll('.quantity:not(.biz-qty-wrapped)').forEach((wrapper) => {
			const input = wrapper.querySelector('input[type="number"]');
			if (!input) return;

			wrapper.classList.add('biz-qty-wrapped', 'biz-qty');

			// Create minus button
			const minus = document.createElement('button');
			minus.type = 'button';
			minus.className = 'biz-qty__btn biz-qty__btn--minus';
			minus.textContent = '-';
			minus.setAttribute('aria-label', 'Diminuisci quantita');

			// Create plus button
			const plus = document.createElement('button');
			plus.type = 'button';
			plus.className = 'biz-qty__btn biz-qty__btn--plus';
			plus.textContent = '+';
			plus.setAttribute('aria-label', 'Aumenta quantita');

			input.classList.add('biz-qty__input');

			// Insert buttons
			wrapper.insertBefore(minus, input);
			wrapper.appendChild(plus);

			const min = parseInt(input.min) || 1;
			const max = parseInt(input.max) || 999;
			const step = parseInt(input.step) || 1;

			minus.addEventListener('click', () => {
				const current = parseInt(input.value) || min;
				const newVal = Math.max(min, current - step);
				if (newVal !== current) {
					input.value = newVal;
					input.dispatchEvent(new Event('change', { bubbles: true }));
				}
			});

			plus.addEventListener('click', () => {
				const current = parseInt(input.value) || min;
				const newVal = Math.min(max, current + step);
				if (newVal !== current) {
					input.value = newVal;
					input.dispatchEvent(new Event('change', { bubbles: true }));
				}
			});
		});
	}

	// Run on load
	wrapQuantityInputs();

	// Re-run after AJAX updates (e.g., cart fragments)
	const observer = new MutationObserver(() => wrapQuantityInputs());
	observer.observe(document.body, { childList: true, subtree: true });
})();
