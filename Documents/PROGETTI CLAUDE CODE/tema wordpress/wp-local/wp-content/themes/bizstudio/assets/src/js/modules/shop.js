/**
 * Shop Page Module — filter sidebar toggle, view toggle, price filter
 *
 * @package BizStudio
 */

(function () {
	'use strict';

	/* -------------------------------------------------------
	   Filter sidebar toggle (mobile)
	   ------------------------------------------------------- */
	const sidebar = document.getElementById('biz-shop-sidebar');
	const filterToggle = document.getElementById('biz-filter-toggle');
	const filterClose = document.getElementById('biz-filter-close');

	if (sidebar && filterToggle) {
		// Create overlay
		let overlay = document.createElement('div');
		overlay.className = 'biz-mobile-overlay';
		overlay.id = 'biz-filter-overlay';
		document.body.appendChild(overlay);

		function openFilters() {
			sidebar.classList.add('is-open');
			overlay.classList.add('is-open');
			document.body.style.overflow = 'hidden';
		}

		function closeFilters() {
			sidebar.classList.remove('is-open');
			overlay.classList.remove('is-open');
			document.body.style.overflow = '';
		}

		filterToggle.addEventListener('click', openFilters);
		filterClose?.addEventListener('click', closeFilters);
		overlay.addEventListener('click', closeFilters);
	}

	/* -------------------------------------------------------
	   Grid / List view toggle
	   ------------------------------------------------------- */
	const viewButtons = document.querySelectorAll('.biz-view-toggle__btn');
	const productList = document.querySelector('ul.products');

	viewButtons.forEach((btn) => {
		btn.addEventListener('click', () => {
			const view = btn.dataset.view;

			// Update button states
			viewButtons.forEach((b) => b.classList.remove('biz-view-toggle__btn--active'));
			btn.classList.add('biz-view-toggle__btn--active');

			// Toggle list view class
			if (productList) {
				productList.classList.toggle('biz-list-view', view === 'list');
			}

			// Save preference
			localStorage.setItem('bizstudio-shop-view', view);
		});
	});

	// Restore saved view
	const savedView = localStorage.getItem('bizstudio-shop-view');
	if (savedView === 'list' && productList) {
		productList.classList.add('biz-list-view');
		viewButtons.forEach((b) => {
			b.classList.toggle('biz-view-toggle__btn--active', b.dataset.view === 'list');
		});
	}

	/* -------------------------------------------------------
	   Price filter
	   ------------------------------------------------------- */
	const priceApply = document.getElementById('biz-price-apply');
	if (priceApply) {
		priceApply.addEventListener('click', () => {
			const min = document.getElementById('biz-price-min')?.value;
			const max = document.getElementById('biz-price-max')?.value;
			const url = new URL(window.location);

			if (min) url.searchParams.set('min_price', min);
			else url.searchParams.delete('min_price');

			if (max) url.searchParams.set('max_price', max);
			else url.searchParams.delete('max_price');

			window.location = url.toString();
		});
	}

	// Pre-fill price inputs from URL
	const urlParams = new URLSearchParams(window.location.search);
	const minInput = document.getElementById('biz-price-min');
	const maxInput = document.getElementById('biz-price-max');
	if (minInput && urlParams.has('min_price')) minInput.value = urlParams.get('min_price');
	if (maxInput && urlParams.has('max_price')) maxInput.value = urlParams.get('max_price');
})();
