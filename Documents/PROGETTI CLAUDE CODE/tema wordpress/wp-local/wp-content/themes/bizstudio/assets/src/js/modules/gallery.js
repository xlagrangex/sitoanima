/**
 * Product Gallery — thumbnail switching + copy link
 *
 * @package BizStudio
 */

(function () {
	'use strict';

	/* -------------------------------------------------------
	   Thumbnail click → change main image
	   ------------------------------------------------------- */
	const thumbs = document.querySelectorAll('.biz-product-gallery__thumb');
	const mainImage = document.getElementById('biz-main-image');

	thumbs.forEach((thumb) => {
		thumb.addEventListener('click', () => {
			const fullUrl = thumb.dataset.full;
			if (!fullUrl || !mainImage) return;

			// Update main image with fade
			mainImage.style.opacity = '0';
			setTimeout(() => {
				mainImage.src = fullUrl;
				mainImage.style.opacity = '1';
			}, 200);

			// Update active thumb
			thumbs.forEach((t) => t.classList.remove('biz-product-gallery__thumb--active'));
			thumb.classList.add('biz-product-gallery__thumb--active');
		});
	});

	/* -------------------------------------------------------
	   Copy link button
	   ------------------------------------------------------- */
	document.querySelectorAll('.biz-copy-link').forEach((btn) => {
		btn.addEventListener('click', async () => {
			const url = btn.dataset.url;
			if (!url) return;

			try {
				await navigator.clipboard.writeText(url);
				// Brief visual feedback
				btn.style.color = 'var(--biz-success)';
				btn.style.borderColor = 'var(--biz-success)';
				setTimeout(() => {
					btn.style.color = '';
					btn.style.borderColor = '';
				}, 1500);
			} catch {
				// Fallback
				const input = document.createElement('input');
				input.value = url;
				document.body.appendChild(input);
				input.select();
				document.execCommand('copy');
				document.body.removeChild(input);
			}
		});
	});
})();
