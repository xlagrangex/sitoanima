/**
 * Live Search Module
 *
 * @package BizStudio
 */

(function () {
	'use strict';

	const toggle = document.getElementById('biz-search-toggle');
	const modal = document.getElementById('biz-search-modal');
	const overlay = document.getElementById('biz-search-overlay');
	const input = document.getElementById('biz-search-input');
	const closeBtn = document.getElementById('biz-search-close');
	const results = document.getElementById('biz-search-results');

	if (!modal || !input) return;

	let debounceTimer = null;

	function openSearch() {
		modal.classList.add('is-open');
		overlay.classList.add('is-open');
		document.body.style.overflow = 'hidden';
		setTimeout(() => input.focus(), 100);
	}

	function closeSearch() {
		modal.classList.remove('is-open');
		overlay.classList.remove('is-open');
		document.body.style.overflow = '';
		input.value = '';
		results.innerHTML = '';
	}

	toggle?.addEventListener('click', openSearch);
	closeBtn?.addEventListener('click', closeSearch);
	overlay?.addEventListener('click', closeSearch);
	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape' && modal.classList.contains('is-open')) closeSearch();
		// CMD/CTRL + K to open search
		if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
			e.preventDefault();
			openSearch();
		}
	});

	// Live search with debounce
	input.addEventListener('input', () => {
		clearTimeout(debounceTimer);
		const query = input.value.trim();

		if (query.length < 2) {
			results.innerHTML = '';
			return;
		}

		// Show loading
		results.innerHTML = '<div class="biz-search-results__loading"><div class="biz-spinner"></div></div>';

		debounceTimer = setTimeout(() => {
			fetch(`${window.bizstudio?.ajaxUrl || '/wp-admin/admin-ajax.php'}?action=bizstudio_live_search&q=${encodeURIComponent(query)}`, {
				credentials: 'same-origin',
			})
				.then((res) => res.json())
				.then((data) => {
					if (data.success) {
						results.innerHTML = data.data.html;
						if (data.data.count > 0) {
							results.innerHTML += `<a href="/?s=${encodeURIComponent(query)}&post_type=product" class="biz-search-results__all">Vedi tutti i risultati &rarr;</a>`;
						}
					}
				})
				.catch(() => {
					results.innerHTML = '<div class="biz-search-results__empty">Errore di ricerca.</div>';
				});
		}, 300);
	});
})();
