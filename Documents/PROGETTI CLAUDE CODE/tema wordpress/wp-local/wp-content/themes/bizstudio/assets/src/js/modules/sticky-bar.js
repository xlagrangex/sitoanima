/**
 * Sticky Add-to-Cart Bottom Bar
 *
 * Uses IntersectionObserver to detect when the original product
 * form scrolls out of view, then shows a fixed bottom bar with
 * product info and an add-to-cart button.
 *
 * For variable products, syncs variation selects between the
 * sticky bar and the main form.
 *
 * @package BizStudio
 */

(function () {
	'use strict';

	/* ───────────────────────────────────────────
	   DOM references
	   ─────────────────────────────────────────── */

	const stickyBar = document.getElementById('biz-sticky-bar');
	if (!stickyBar) return;

	// The original add-to-cart form inside the product info section
	const mainForm = document.querySelector('.biz-product-info__cart form.cart');
	if (!mainForm) return;

	const stickyBtn     = document.getElementById('biz-sticky-bar-btn');
	const stickyPrice   = document.getElementById('biz-sticky-bar-price');
	const stickySelects = stickyBar.querySelectorAll('.biz-sticky-bar__select');

	/* ───────────────────────────────────────────
	   IntersectionObserver — show/hide bar
	   ─────────────────────────────────────────── */

	const observer = new IntersectionObserver(
		(entries) => {
			for (const entry of entries) {
				if (entry.isIntersecting) {
					// Form is visible — hide sticky bar
					stickyBar.classList.remove('biz-sticky-bar--visible');
					stickyBar.classList.add('biz-sticky-bar--hidden');
				} else {
					// Form is NOT visible (scrolled past) — show sticky bar
					stickyBar.classList.remove('biz-sticky-bar--hidden');
					stickyBar.classList.add('biz-sticky-bar--visible');
				}
			}
		},
		{
			root: null,
			rootMargin: '0px',
			threshold: 0,
		}
	);

	observer.observe(mainForm);

	/* ───────────────────────────────────────────
	   Add-to-cart click — trigger main form submit
	   ─────────────────────────────────────────── */

	if (stickyBtn) {
		stickyBtn.addEventListener('click', () => {
			const mainSubmit = mainForm.querySelector('[type="submit"], .single_add_to_cart_button');
			if (mainSubmit) {
				mainSubmit.click();
			}
		});
	}

	/* ───────────────────────────────────────────
	   Variable products — sync variation selects
	   ─────────────────────────────────────────── */

	if (stickySelects.length > 0) {
		// Sticky bar select → update main form
		stickySelects.forEach((stickySelect) => {
			const attributeName = stickySelect.dataset.attribute;

			stickySelect.addEventListener('change', () => {
				const mainSelect = mainForm.querySelector(`select[name="${attributeName}"], .biz-swatches__select-hidden[name="${attributeName}"]`);
				if (mainSelect && mainSelect.value !== stickySelect.value) {
					mainSelect.value = stickySelect.value;
					mainSelect.dispatchEvent(new Event('change', { bubbles: true }));
				}
			});
		});

		// Main form selects → update sticky bar (bidirectional sync)
		const mainSelects = mainForm.querySelectorAll('select[name^="attribute_"]');
		mainSelects.forEach((mainSelect) => {
			const attributeName = mainSelect.getAttribute('name');

			mainSelect.addEventListener('change', () => {
				const stickySelect = stickyBar.querySelector(`.biz-sticky-bar__select[data-attribute="${attributeName}"]`);
				if (stickySelect && stickySelect.value !== mainSelect.value) {
					stickySelect.value = mainSelect.value;
				}
			});
		});

		// Also sync options availability: observe the main selects for disabled option changes
		mainSelects.forEach((mainSelect) => {
			const attributeName = mainSelect.getAttribute('name');
			const stickySelect = stickyBar.querySelector(`.biz-sticky-bar__select[data-attribute="${attributeName}"]`);
			if (!stickySelect) return;

			const syncOptions = () => {
				const mainOptions = mainSelect.querySelectorAll('option');
				const stickyOptions = stickySelect.querySelectorAll('option');

				// Build a map of available values from the main select
				const availableValues = new Set();
				mainOptions.forEach((opt) => {
					if (opt.value && !opt.disabled) {
						availableValues.add(opt.value);
					}
				});

				// Enable/disable sticky options accordingly
				stickyOptions.forEach((opt) => {
					if (!opt.value) return; // skip placeholder
					opt.disabled = !availableValues.has(opt.value);
				});

				// Sync the selected value
				if (stickySelect.value !== mainSelect.value) {
					stickySelect.value = mainSelect.value;
				}
			};

			// Observe changes to the main select's children and attributes
			const mutationObserver = new MutationObserver(syncOptions);
			mutationObserver.observe(mainSelect, {
				childList: true,
				subtree: true,
				attributes: true,
				attributeFilter: ['disabled'],
			});

			// Initial sync
			syncOptions();
		});
	}

	/* ───────────────────────────────────────────
	   Update price on variation change
	   ─────────────────────────────────────────── */

	if (stickyPrice) {
		// WooCommerce triggers 'found_variation' with jQuery on the form
		// when a valid variation is selected. We listen via a native wrapper.
		const jq = window.jQuery;

		if (jq) {
			jq(mainForm).on('found_variation', function (_event, variation) {
				if (variation.price_html) {
					stickyPrice.innerHTML = variation.price_html;
				}
			});

			// When variation is reset, restore the original price
			jq(mainForm).on('reset_data', function () {
				const originalPrice = document.querySelector('.biz-product-info__price');
				if (originalPrice) {
					stickyPrice.innerHTML = originalPrice.innerHTML;
				}
			});
		} else {
			// Fallback: observe the main price element for changes
			const mainPriceEl = document.querySelector('.biz-product-info__price');
			if (mainPriceEl) {
				const priceObserver = new MutationObserver(() => {
					stickyPrice.innerHTML = mainPriceEl.innerHTML;
				});
				priceObserver.observe(mainPriceEl, {
					childList: true,
					subtree: true,
					characterData: true,
				});
			}
		}
	}

})();
