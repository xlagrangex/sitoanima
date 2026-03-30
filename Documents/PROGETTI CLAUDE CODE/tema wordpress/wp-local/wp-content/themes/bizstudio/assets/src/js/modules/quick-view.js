/**
 * Quick View Module
 *
 * @package BizStudio
 */

(function () {
	'use strict';

	const overlay = document.getElementById('biz-qv-overlay');
	const modal = document.getElementById('biz-qv');
	const body = document.getElementById('biz-qv-body');
	const closeBtn = document.getElementById('biz-qv-close');

	if (!modal || !overlay) return;

	function open() {
		modal.classList.add('is-open');
		overlay.classList.add('is-open');
		document.body.style.overflow = 'hidden';
	}

	function close() {
		modal.classList.remove('is-open');
		overlay.classList.remove('is-open');
		document.body.style.overflow = '';
		// Reset body after animation
		setTimeout(() => {
			body.innerHTML = '<div class="biz-qv__loading"><div class="biz-spinner"></div></div>';
		}, 300);
	}

	closeBtn?.addEventListener('click', close);
	overlay?.addEventListener('click', close);
	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape' && modal.classList.contains('is-open')) close();
	});

	// Event delegation for quick view buttons (eye icon on cards)
	document.addEventListener('click', (e) => {
		const eyeBtn = e.target.closest('.biz-card__action[aria-label*="Vedi prodotto"]');
		if (!eyeBtn) return;

		// Only intercept if it's a button or anchor without direct href navigation needed
		const productId = eyeBtn.closest('.biz-card')?.dataset.productId;
		if (!productId) return;

		e.preventDefault();
		open();

		fetch(`${window.bizstudio?.ajaxUrl || '/wp-admin/admin-ajax.php'}?action=bizstudio_quick_view&product_id=${productId}`, {
			credentials: 'same-origin',
		})
			.then((res) => res.json())
			.then((data) => {
				if (data.success) {
					body.innerHTML = data.data.html;
				} else {
					body.innerHTML = '<p style="padding:2rem;text-align:center;color:var(--biz-text-muted)">Prodotto non trovato.</p>';
				}
			})
			.catch(() => {
				body.innerHTML = '<p style="padding:2rem;text-align:center;color:var(--biz-error)">Errore di connessione.</p>';
			});
	});
})();
