/**
 * Shipping Progress Bar Module
 *
 * Listens for WooCommerce cart updates (cart fragments + custom events)
 * and triggers confetti animations when a new threshold is reached.
 *
 * @package BizStudio
 */

(function () {
	'use strict';

	const STORAGE_KEY = 'bizstudio-shipping-step';
	const CONFETTI_COUNT = 8;

	/* -------------------------------------------------------
	   State
	   ------------------------------------------------------- */

	/**
	 * Read the previous active step from sessionStorage.
	 * Returns -2 on first visit (no prior state), so we don't
	 * fire confetti on initial page load.
	 */
	function getPreviousStep() {
		const stored = sessionStorage.getItem(STORAGE_KEY);
		return stored !== null ? parseInt(stored, 10) : -2;
	}

	function saveCurrentStep(step) {
		sessionStorage.setItem(STORAGE_KEY, String(step));
	}

	/* -------------------------------------------------------
	   Read data from the DOM
	   ------------------------------------------------------- */

	function getBarData() {
		const wrap = document.querySelector('.biz-shipping-bar-wrap');
		if (!wrap) return null;

		const raw = wrap.dataset.shippingBar;
		if (!raw) return null;

		try {
			return JSON.parse(raw);
		} catch {
			return null;
		}
	}

	/* -------------------------------------------------------
	   Confetti burst
	   ------------------------------------------------------- */

	function triggerConfetti(barEl) {
		const container = barEl?.querySelector('.biz-shipping-bar__confetti');
		if (!container) return;

		// Clear previous confetti
		container.innerHTML = '';

		// Position confetti near the fill tip
		const fill = barEl.querySelector('.biz-shipping-bar__fill');
		const track = barEl.querySelector('.biz-shipping-bar__track');
		if (!fill || !track) return;

		const fillWidth = fill.offsetWidth;
		const trackRect = track.getBoundingClientRect();
		const barRect = barEl.getBoundingClientRect();

		// Calculate confetti origin relative to the bar container
		const originX = (trackRect.left - barRect.left) + fillWidth;
		const originY = (trackRect.top - barRect.top) + (trackRect.height / 2);

		// Create dots
		for (let i = 0; i < CONFETTI_COUNT; i++) {
			const dot = document.createElement('span');
			dot.className = 'biz-shipping-bar__confetti-dot';
			dot.style.left = originX + 'px';
			dot.style.top = originY + 'px';
			container.appendChild(dot);
		}

		// Trigger animation on next frame
		requestAnimationFrame(() => {
			container.querySelectorAll('.biz-shipping-bar__confetti-dot').forEach((dot) => {
				dot.classList.add('is-active');
			});
		});

		// Clean up after animation
		setTimeout(() => {
			container.innerHTML = '';
		}, 1200);
	}

	/* -------------------------------------------------------
	   Check for step changes and animate
	   ------------------------------------------------------- */

	function processBarUpdate() {
		const data = getBarData();
		if (!data) return;

		const currentStep = data.allComplete
			? data.activeStep
			: data.activeStep;

		const previousStep = getPreviousStep();

		// Detect if we've moved UP to a new step
		if (previousStep !== -2 && currentStep > previousStep) {
			const barEl = document.querySelector('.biz-shipping-bar');
			if (barEl) {
				// Small delay so the fill animation is visible first
				setTimeout(() => {
					triggerConfetti(barEl);
				}, 400);
			}
		}

		// Always save the current step
		saveCurrentStep(currentStep);
	}

	/* -------------------------------------------------------
	   Observe DOM changes for cart fragment replacements
	   ------------------------------------------------------- */

	function observeFragments() {
		// WooCommerce replaces .biz-shipping-bar-wrap via fragments
		// We need a MutationObserver on the parent to detect when
		// the element is swapped.
		const observer = new MutationObserver((mutations) => {
			for (const mutation of mutations) {
				for (const node of mutation.addedNodes) {
					if (node.nodeType !== Node.ELEMENT_NODE) continue;
					if (
						node.classList?.contains('biz-shipping-bar-wrap') ||
						node.querySelector?.('.biz-shipping-bar-wrap')
					) {
						processBarUpdate();
						return;
					}
				}
			}
		});

		observer.observe(document.body, {
			childList: true,
			subtree: true,
		});
	}

	/* -------------------------------------------------------
	   Event listeners
	   ------------------------------------------------------- */

	function bindEvents() {
		// WooCommerce triggers these after cart updates
		document.body.addEventListener('updated_cart_totals', () => {
			processBarUpdate();
		});

		// jQuery-based WC events (cart fragments)
		if (typeof jQuery !== 'undefined') {
			jQuery(document.body).on('wc_fragments_refreshed wc_fragments_loaded added_to_cart removed_from_cart', () => {
				// Fragments may not be in DOM yet, slight delay
				setTimeout(() => {
					processBarUpdate();
				}, 50);
			});
		}

		// Also listen for our custom AJAX add-to-cart (from main.js)
		// which replaces miniCart outerHTML directly
		document.addEventListener('bizstudio:cart-updated', () => {
			processBarUpdate();
		});
	}

	/* -------------------------------------------------------
	   Init
	   ------------------------------------------------------- */

	function init() {
		// Process initial state (don't trigger confetti)
		const data = getBarData();
		if (data) {
			const prev = getPreviousStep();
			// On first load, just save current step without confetti
			if (prev === -2) {
				saveCurrentStep(data.activeStep);
			} else {
				// Page reload with possibly different cart — check
				processBarUpdate();
			}
		}

		bindEvents();
		observeFragments();
	}

	// Run when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
