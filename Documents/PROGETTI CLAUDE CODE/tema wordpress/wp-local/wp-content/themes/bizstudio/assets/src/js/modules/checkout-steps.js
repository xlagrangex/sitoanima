/**
 * Checkout Steps Module — Shopify-style multi-step checkout
 *
 * Manages a 3-step flow on top of WooCommerce Checkout Blocks:
 *   1. Informazioni (contact + shipping address)
 *   2. Spedizione  (shipping methods)
 *   3. Pagamento   (payment + place order)
 *
 * WC blocks are React-rendered and may not exist in the DOM on load.
 * We use a MutationObserver to wait for them, then wrap each group
 * of blocks into step containers and manage visibility.
 *
 * @package BizStudio
 */

(function () {
	'use strict';

	/* -------------------------------------------------------
	   Constants
	   ------------------------------------------------------- */
	const STORAGE_KEY = 'bizstudio-checkout-step';
	const MAX_WAIT = 15000; // ms to wait for WC blocks before giving up
	const POLL_INTERVAL = 200;

	const STEP_LABELS = {
		1: 'Informazioni',
		2: 'Spedizione',
		3: 'Pagamento',
	};

	const CONTINUE_LABELS = {
		1: 'Continua con la spedizione',
		2: 'Continua con il pagamento',
	};

	const SUMMARY_LABELS = {
		show: 'Mostra riepilogo ordine',
		hide: 'Nascondi riepilogo ordine',
	};

	/* Block selectors for each step */
	const STEP_BLOCK_SELECTORS = {
		1: [
			'.wp-block-woocommerce-checkout-contact-information-block',
			'.wp-block-woocommerce-checkout-shipping-address-block',
			'.wp-block-woocommerce-checkout-billing-address-block',
		],
		2: [
			'.wp-block-woocommerce-checkout-shipping-method-block',
			'.wp-block-woocommerce-checkout-shipping-methods-block',
			'.wp-block-woocommerce-checkout-pickup-options-block',
		],
		3: [
			'.wp-block-woocommerce-checkout-payment-block',
			'.wp-block-woocommerce-checkout-order-note-block',
			'.wp-block-woocommerce-checkout-terms-block',
			'.wp-block-woocommerce-checkout-actions-block',
			'.wp-block-woocommerce-checkout-additional-information-block',
		],
	};

	/* -------------------------------------------------------
	   State
	   ------------------------------------------------------- */
	let currentStep = 1;
	let initialized = false;
	let stepContainers = {};
	let stepButtons = {};
	let continueButtons = {};

	/* -------------------------------------------------------
	   Utility helpers
	   ------------------------------------------------------- */

	/** Shorthand querySelector / querySelectorAll */
	const $ = (sel, ctx = document) => ctx.querySelector(sel);
	const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

	/** Restore step from session storage (clamp to 1-3) */
	function restoreStep() {
		const saved = parseInt(sessionStorage.getItem(STORAGE_KEY), 10);
		if (saved >= 1 && saved <= 3) return saved;
		return 1;
	}

	/** Persist step to session storage */
	function saveStep(step) {
		sessionStorage.setItem(STORAGE_KEY, step);
	}

	/* -------------------------------------------------------
	   Wait for WC Blocks to render
	   ------------------------------------------------------- */

	/**
	 * Returns a promise that resolves when the checkout form block
	 * and at least one of the step-1 blocks are present in the DOM.
	 */
	function waitForBlocks() {
		return new Promise((resolve, reject) => {
			const startTime = Date.now();

			function check() {
				const checkoutBlock = $('.wp-block-woocommerce-checkout');
				if (!checkoutBlock) {
					if (Date.now() - startTime > MAX_WAIT) {
						reject(new Error('Checkout blocks not found within timeout'));
						return;
					}
					setTimeout(check, POLL_INTERVAL);
					return;
				}

				/* WC blocks renders an outer element first, then fills children.
				   Wait until we see the form element inside. */
				const form = $('form.wc-block-checkout__form, .wc-block-checkout__form', checkoutBlock);
				const fieldsBlock = $('.wp-block-woocommerce-checkout-fields-block', checkoutBlock);

				if (!form && !fieldsBlock) {
					if (Date.now() - startTime > MAX_WAIT) {
						reject(new Error('Checkout form not rendered within timeout'));
						return;
					}
					setTimeout(check, POLL_INTERVAL);
					return;
				}

				/* Wait for at least one step-1 block to appear (contact info) */
				const hasStep1 = STEP_BLOCK_SELECTORS[1].some((sel) => $(sel, checkoutBlock));
				if (!hasStep1) {
					if (Date.now() - startTime > MAX_WAIT) {
						/* Proceed anyway — blocks might have different selectors */
						resolve(checkoutBlock);
						return;
					}
					setTimeout(check, POLL_INTERVAL);
					return;
				}

				resolve(checkoutBlock);
			}

			check();
		});
	}

	/* -------------------------------------------------------
	   Build Step Containers
	   ------------------------------------------------------- */

	/**
	 * Wraps matching block elements into step container divs.
	 * Non-matched blocks (express payment, etc.) stay in place above the steps.
	 */
	function buildStepContainers(checkoutBlock) {
		const fieldsBlock = $('.wp-block-woocommerce-checkout-fields-block', checkoutBlock);
		if (!fieldsBlock) return false;

		const form = $('form.wc-block-checkout__form, .wc-block-checkout__form', fieldsBlock) || fieldsBlock;

		/* Collect all child nodes that should be categorized */
		for (let step = 1; step <= 3; step++) {
			const container = document.createElement('div');
			container.className = 'biz-checkout__step-content';
			container.dataset.step = step;

			let hasBlocks = false;

			STEP_BLOCK_SELECTORS[step].forEach((sel) => {
				const blocks = $$(sel, form);
				blocks.forEach((block) => {
					container.appendChild(block);
					hasBlocks = true;
				});
			});

			/* Only insert non-empty containers */
			if (hasBlocks) {
				/* Add "Continue" button for steps 1 and 2 */
				if (step < 3) {
					const btn = document.createElement('button');
					btn.type = 'button';
					btn.className = 'biz-checkout__continue';
					btn.textContent = CONTINUE_LABELS[step];
					btn.dataset.nextStep = step + 1;
					btn.addEventListener('click', () => handleContinue(step));
					container.appendChild(btn);
					continueButtons[step] = btn;
				}

				form.appendChild(container);
				stepContainers[step] = container;
			}
		}

		return Object.keys(stepContainers).length > 0;
	}

	/* -------------------------------------------------------
	   Step Navigation
	   ------------------------------------------------------- */

	/** Activate a specific step */
	function goToStep(step) {
		if (step < 1 || step > 3) return;

		currentStep = step;
		saveStep(step);

		/* Update step containers visibility */
		for (let s = 1; s <= 3; s++) {
			const container = stepContainers[s];
			if (!container) continue;

			if (s === step) {
				container.classList.add('biz-checkout__step-content--active');
			} else {
				container.classList.remove('biz-checkout__step-content--active');
			}
		}

		/* Update breadcrumb buttons */
		updateBreadcrumbs(step);

		/* Update body class for place-order button visibility */
		const checkoutWrapper = $('.biz-checkout');
		if (checkoutWrapper) {
			checkoutWrapper.classList.remove('biz-checkout--step-1', 'biz-checkout--step-2', 'biz-checkout--step-3');
			checkoutWrapper.classList.add(`biz-checkout--step-${step}`);
		}

		/* Update step summaries for completed steps */
		updateStepSummaries(step);

		/* Scroll to top of form */
		const header = $('.biz-checkout__header');
		if (header) {
			header.scrollIntoView({ behavior: 'smooth', block: 'start' });
		}
	}

	/** Update breadcrumb button states */
	function updateBreadcrumbs(activeStep) {
		for (let s = 1; s <= 3; s++) {
			const btn = stepButtons[s];
			if (!btn) continue;

			btn.classList.remove('biz-checkout__step--active', 'biz-checkout__step--completed');
			btn.disabled = true;

			if (s === activeStep) {
				btn.classList.add('biz-checkout__step--active');
				btn.disabled = false;
			} else if (s < activeStep) {
				btn.classList.add('biz-checkout__step--completed');
				btn.disabled = false;
			}
		}
	}

	/** Create/update step summaries for completed steps */
	function updateStepSummaries(activeStep) {
		/* Remove existing summaries */
		$$('.biz-checkout__step-summary').forEach((el) => el.remove());

		const activeContainer = stepContainers[activeStep];
		if (!activeContainer) return;

		/* Show summaries for steps before the active one */
		for (let s = 1; s < activeStep; s++) {
			const summary = document.createElement('div');
			summary.className = 'biz-checkout__step-summary biz-checkout__step-summary--visible';
			summary.dataset.summaryStep = s;

			const text = document.createElement('span');
			text.className = 'biz-checkout__step-summary-text';
			text.textContent = getSummaryText(s);

			const editBtn = document.createElement('button');
			editBtn.type = 'button';
			editBtn.className = 'biz-checkout__step-summary-edit';
			editBtn.textContent = 'Modifica';
			editBtn.addEventListener('click', () => goToStep(s));

			summary.appendChild(text);
			summary.appendChild(editBtn);

			/* Insert before the active container */
			activeContainer.parentNode.insertBefore(summary, activeContainer);
		}
	}

	/** Extract summary text from completed step blocks */
	function getSummaryText(step) {
		if (step === 1) {
			/* Try to get email and address from the form fields */
			const emailInput = $('input[id*="email"]');
			const email = emailInput?.value || '';

			const addressParts = [];
			const firstNameInput = $('input[id*="first-name"], input[id*="first_name"]');
			const lastNameInput = $('input[id*="last-name"], input[id*="last_name"]');
			const address1Input = $('input[id*="address_1"], input[id*="address-1"]');
			const cityInput = $('input[id*="city"]');

			if (firstNameInput?.value) addressParts.push(firstNameInput.value);
			if (lastNameInput?.value) addressParts.push(lastNameInput.value);
			if (address1Input?.value) addressParts.push(address1Input.value);
			if (cityInput?.value) addressParts.push(cityInput.value);

			const parts = [];
			if (email) parts.push(email);
			if (addressParts.length > 0) parts.push(addressParts.join(' '));

			return parts.join(' — ') || STEP_LABELS[1];
		}

		if (step === 2) {
			/* Try to get selected shipping method */
			const selectedRadio = $('input[type="radio"]:checked', stepContainers[2]);
			if (selectedRadio) {
				const label = selectedRadio.closest('.wc-block-components-radio-control__option');
				const labelText = label?.querySelector('.wc-block-components-radio-control__label');
				const priceText = label?.querySelector('.wc-block-components-radio-control__secondary-label');
				const parts = [];
				if (labelText?.textContent) parts.push(labelText.textContent.trim());
				if (priceText?.textContent) parts.push(priceText.textContent.trim());
				return parts.join(' — ') || STEP_LABELS[2];
			}
			return STEP_LABELS[2];
		}

		return STEP_LABELS[step] || '';
	}

	/* -------------------------------------------------------
	   Validation
	   ------------------------------------------------------- */

	/**
	 * Validate current step's required fields before advancing.
	 * Returns true if valid, false otherwise.
	 */
	function validateStep(step) {
		const container = stepContainers[step];
		if (!container) return true;

		/* Gather all visible required inputs */
		const inputs = $$(
			'input[required], input[aria-required="true"], textarea[required], select[required]',
			container
		);

		let valid = true;
		let firstInvalid = null;

		inputs.forEach((input) => {
			/* Skip hidden inputs */
			if (input.offsetParent === null) return;

			const wrapper = input.closest('.wc-block-components-text-input, .wc-block-components-combobox');

			if (!input.value || input.value.trim() === '') {
				valid = false;
				if (wrapper) wrapper.classList.add('has-error');
				if (!firstInvalid) firstInvalid = input;
			} else {
				/* Basic email validation for step 1 */
				if (input.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value)) {
					valid = false;
					if (wrapper) wrapper.classList.add('has-error');
					if (!firstInvalid) firstInvalid = input;
				}
			}
		});

		/* Also trigger native WC validation if available */
		const wcValidationErrors = $$('.wc-block-components-validation-error', container);
		if (wcValidationErrors.some((err) => err.textContent.trim() !== '')) {
			valid = false;
		}

		if (!valid && firstInvalid) {
			firstInvalid.focus();
			firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
		}

		return valid;
	}

	/** Handle "Continue" button click */
	function handleContinue(step) {
		const btn = continueButtons[step];

		/* Trigger WC block validation by focusing/blurring fields */
		triggerWCValidation(step);

		/* Brief delay to let React update validation states */
		if (btn) {
			btn.classList.add('biz-checkout__continue--loading');
			btn.disabled = true;
		}

		setTimeout(() => {
			if (btn) {
				btn.classList.remove('biz-checkout__continue--loading');
				btn.disabled = false;
			}

			if (validateStep(step)) {
				goToStep(step + 1);
			}
		}, 300);
	}

	/**
	 * Trigger WC block-level validation by dispatching blur events
	 * on required inputs. This forces React to show its own errors.
	 */
	function triggerWCValidation(step) {
		const container = stepContainers[step];
		if (!container) return;

		const inputs = $$('input, select, textarea', container);
		inputs.forEach((input) => {
			if (input.offsetParent === null) return;
			input.dispatchEvent(new Event('blur', { bubbles: true }));
		});
	}

	/* -------------------------------------------------------
	   Mobile Order Summary Toggle
	   ------------------------------------------------------- */
	function initMobileSummaryToggle() {
		const toggleWrapper = $('#biz-checkout-summary-toggle');
		if (!toggleWrapper) return;

		const btn = $('.biz-checkout__summary-btn', toggleWrapper);
		if (!btn) return;

		const totalsBlock = $('.wp-block-woocommerce-checkout-totals-block');
		if (!totalsBlock) return;

		const labelSpan = $('span', btn);

		btn.addEventListener('click', () => {
			const isOpen = toggleWrapper.classList.toggle('is-open');
			totalsBlock.classList.toggle('is-visible', isOpen);

			if (labelSpan) {
				labelSpan.textContent = isOpen ? SUMMARY_LABELS.hide : SUMMARY_LABELS.show;
			}
		});
	}

	/* -------------------------------------------------------
	   Observe for React re-renders
	   ------------------------------------------------------- */

	/**
	 * WC Blocks (React) can re-render and destroy our DOM modifications.
	 * We watch for this and re-initialize if our step containers vanish.
	 */
	function watchForReRenders(checkoutBlock) {
		const observer = new MutationObserver((mutations) => {
			/* Check if our step containers are still in the DOM */
			const step1Container = stepContainers[1];
			if (step1Container && !document.body.contains(step1Container)) {
				/* Our containers were removed by a React re-render */
				observer.disconnect();
				stepContainers = {};
				continueButtons = {};
				initialized = false;

				/* Re-initialize after a brief delay */
				setTimeout(() => init(), 500);
			}
		});

		const fieldsBlock = $('.wp-block-woocommerce-checkout-fields-block', checkoutBlock);
		if (fieldsBlock) {
			observer.observe(fieldsBlock, { childList: true, subtree: true });
		}
	}

	/* -------------------------------------------------------
	   Initialize
	   ------------------------------------------------------- */
	function init() {
		if (initialized) return;

		/* Only run on checkout page */
		if (!document.body.classList.contains('biz-checkout-page') &&
			!document.body.classList.contains('woocommerce-checkout')) {
			return;
		}

		waitForBlocks()
			.then((checkoutBlock) => {
				/* Cache breadcrumb step buttons */
				$$('.biz-checkout__step[data-step]').forEach((btn) => {
					const step = parseInt(btn.dataset.step, 10);
					stepButtons[step] = btn;

					btn.addEventListener('click', () => {
						const btnStep = parseInt(btn.dataset.step, 10);
						/* Only allow navigating to completed steps or current step */
						if (btn.classList.contains('biz-checkout__step--completed')) {
							goToStep(btnStep);
						}
					});
				});

				/* Build step containers */
				const built = buildStepContainers(checkoutBlock);
				if (!built) {
					console.warn('[BizStudio Checkout] Could not group blocks into steps.');
					return;
				}

				initialized = true;

				/* Restore or default to step 1 */
				currentStep = restoreStep();

				/* Make sure restored step is valid (don't skip steps on fresh load) */
				goToStep(currentStep);

				/* Mobile summary toggle */
				initMobileSummaryToggle();

				/* Watch for React re-renders */
				watchForReRenders(checkoutBlock);

				/* Update the mobile summary total dynamically */
				observeTotalChanges();
			})
			.catch((err) => {
				console.warn('[BizStudio Checkout]', err.message);
			});
	}

	/* -------------------------------------------------------
	   Keep mobile summary total in sync
	   ------------------------------------------------------- */
	function observeTotalChanges() {
		const totalEl = $('.biz-checkout__summary-total');
		if (!totalEl) return;

		const footerTotal = $('.wc-block-components-totals-footer-item .wc-block-components-totals-item__value');
		if (!footerTotal) return;

		const observer = new MutationObserver(() => {
			const newTotal = footerTotal.textContent?.trim();
			if (newTotal) {
				totalEl.innerHTML = newTotal;
			}
		});

		observer.observe(footerTotal, { childList: true, subtree: true, characterData: true });
	}

	/* -------------------------------------------------------
	   Bootstrap
	   ------------------------------------------------------- */
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
