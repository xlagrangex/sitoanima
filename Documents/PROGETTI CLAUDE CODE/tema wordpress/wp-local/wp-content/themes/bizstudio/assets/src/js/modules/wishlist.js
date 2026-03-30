/**
 * Wishlist Module
 *
 * Handles heart-button toggling, AJAX persistence, header badge updates,
 * and wishlist-page remove actions. Zero dependencies, vanilla ES2024+.
 *
 * Expects `bizstudioWishlist` to be localized on the page with:
 *   - ajax_url  {string}
 *   - nonce     {string}
 *   - ids       {number[]}  initial wishlist product IDs
 *   - count     {number}    initial wishlist count
 *
 * @package BizStudio
 */

(function () {
	'use strict';

	const config = window.bizstudioWishlist;
	if (!config) return;

	/** @type {Set<number>} Live set of wishlisted product IDs */
	const wishlistIds = new Set((config.ids || []).map(Number));

	/* -------------------------------------------------------
	   Init: mark buttons that are already active
	   ------------------------------------------------------- */
	function initButtons() {
		document.querySelectorAll('.biz-wishlist-btn').forEach((btn) => {
			const pid = Number(btn.dataset.productId);
			if (!pid) return;

			if (wishlistIds.has(pid)) {
				btn.classList.add('biz-wishlist-btn--active');
				btn.setAttribute('aria-label', btn.dataset.labelRemove || '');
			} else {
				btn.classList.remove('biz-wishlist-btn--active');
				btn.setAttribute('aria-label', btn.dataset.labelAdd || '');
			}
		});
		updateHeaderBadge();
	}

	initButtons();

	/* -------------------------------------------------------
	   Event delegation: heart button clicks
	   ------------------------------------------------------- */
	document.addEventListener('click', (e) => {
		const btn = e.target.closest('.biz-wishlist-btn');
		if (!btn) return;

		// Ignore if this is a remove button on the wishlist page (handled separately below)
		if (btn.classList.contains('biz-wishlist-page__remove')) {
			handleWishlistPageRemove(btn);
			return;
		}

		e.preventDefault();
		toggleWishlist(btn);
	});

	/* -------------------------------------------------------
	   Toggle: AJAX request + UI update
	   ------------------------------------------------------- */
	function toggleWishlist(btn) {
		const pid = Number(btn.dataset.productId);
		if (!pid || btn.classList.contains('is-loading')) return;

		btn.classList.add('is-loading');

		const body = new FormData();
		body.append('action', 'bizstudio_toggle_wishlist');
		body.append('product_id', pid);
		body.append('nonce', config.nonce);

		fetch(config.ajax_url, {
			method: 'POST',
			body,
			credentials: 'same-origin',
		})
			.then((res) => res.json())
			.then((data) => {
				btn.classList.remove('is-loading');

				if (!data.success) return;

				const isNowActive = data.data.in_wishlist;

				// Update local set
				if (isNowActive) {
					wishlistIds.add(pid);
				} else {
					wishlistIds.delete(pid);
				}

				// Update ALL buttons for this product (there may be multiples)
				syncButtonsForProduct(pid, isNowActive);

				// Beat animation
				triggerBeat(btn);

				// Header badge
				updateHeaderBadge(data.data.count);
			})
			.catch(() => {
				btn.classList.remove('is-loading');
			});
	}

	/* -------------------------------------------------------
	   Wishlist page: remove card with animation
	   ------------------------------------------------------- */
	function handleWishlistPageRemove(btn) {
		const pid  = Number(btn.dataset.productId);
		const card = btn.closest('.biz-wishlist-page__item');
		if (!pid) return;

		btn.classList.add('is-loading');

		const body = new FormData();
		body.append('action', 'bizstudio_toggle_wishlist');
		body.append('product_id', pid);
		body.append('nonce', config.nonce);

		fetch(config.ajax_url, {
			method: 'POST',
			body,
			credentials: 'same-origin',
		})
			.then((res) => res.json())
			.then((data) => {
				btn.classList.remove('is-loading');

				if (!data.success) return;

				wishlistIds.delete(pid);
				updateHeaderBadge(data.data.count);

				// Animate card out
				if (card) {
					card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
					card.style.opacity = '0';
					card.style.transform = 'scale(0.95)';
					card.addEventListener('transitionend', () => {
						card.remove();
						// If grid is now empty, show empty state
						checkEmptyState();
					}, { once: true });
				}
			})
			.catch(() => {
				btn.classList.remove('is-loading');
			});
	}

	/**
	 * If the wishlist grid is empty after removal, replace with empty state.
	 */
	function checkEmptyState() {
		const grid = document.querySelector('.biz-wishlist-page__grid');
		if (!grid) return;

		if (grid.children.length === 0) {
			const page = grid.closest('.biz-wishlist-page');
			if (!page) return;

			const shopUrl = window.bizstudio?.cartUrl
				? new URL('..', window.bizstudio.cartUrl).href
				: '/shop/';

			page.innerHTML = `
				<div class="biz-wishlist-page__empty">
					<svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:.35;margin-bottom:1rem">
						<path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
					</svg>
					<h2>La tua wishlist è vuota</h2>
					<p>Esplora il nostro catalogo e salva i prodotti che ti piacciono.</p>
					<a href="${shopUrl}" class="biz-btn biz-btn--primary">Vai allo shop</a>
				</div>`;
		}
	}

	/* -------------------------------------------------------
	   Helpers
	   ------------------------------------------------------- */

	/**
	 * Sync all wishlist buttons for a given product ID.
	 */
	function syncButtonsForProduct(pid, isActive) {
		document.querySelectorAll(`.biz-wishlist-btn[data-product-id="${pid}"]`).forEach((b) => {
			b.classList.toggle('biz-wishlist-btn--active', isActive);
			b.setAttribute(
				'aria-label',
				isActive ? (b.dataset.labelRemove || '') : (b.dataset.labelAdd || '')
			);
		});
	}

	/**
	 * Trigger the beat animation on a button.
	 */
	function triggerBeat(btn) {
		btn.classList.remove('biz-wishlist-btn--beat');
		// Force reflow so the animation restarts
		void btn.offsetWidth;
		btn.classList.add('biz-wishlist-btn--beat');
		btn.addEventListener('animationend', () => {
			btn.classList.remove('biz-wishlist-btn--beat');
		}, { once: true });
	}

	/**
	 * Update the header wishlist badge count.
	 *
	 * @param {number} [count] If omitted, uses the local set size.
	 */
	function updateHeaderBadge(count) {
		const c = count ?? wishlistIds.size;
		document.querySelectorAll('.biz-header__wishlist-count').forEach((el) => {
			el.textContent = c;
			el.dataset.count = c;
		});
	}
})();
