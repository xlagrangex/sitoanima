/**
 * Single Product — AJAX Add to Cart
 * Intercepts the WooCommerce form submit, sends via AJAX,
 * updates mini-cart, shows toast, opens mini-cart drawer.
 *
 * @package BizStudio
 */

(function () {
	'use strict';

	const form = document.querySelector('.biz-product-info__cart form.cart');
	if (!form) return;

	form.addEventListener('submit', function (e) {
		e.preventDefault();

		const submitBtn = form.querySelector('[type="submit"]');
		if (!submitBtn || submitBtn.classList.contains('is-loading')) return;

		// Get form data
		const formData = new FormData(form);
		// The submit button's value contains the product ID but FormData doesn't include it
		const productId = submitBtn.value || formData.get('add-to-cart') || formData.get('product_id');
		const quantity = formData.get('quantity') || 1;

		if (!productId) return;

		// Loading state
		const originalText = submitBtn.textContent;
		submitBtn.classList.add('is-loading');
		submitBtn.textContent = 'Aggiunta in corso...';
		submitBtn.disabled = true;

		// Build AJAX data
		const ajaxData = new FormData();
		ajaxData.append('action', 'bizstudio_add_to_cart');
		ajaxData.append('product_id', productId);
		ajaxData.append('quantity', quantity);
		ajaxData.append('nonce', window.bizstudio?.nonce || '');

		// Include variation data if present
		const variationId = formData.get('variation_id');
		if (variationId) {
			ajaxData.append('variation_id', variationId);
			// Add all variation attributes
			for (const [key, value] of formData.entries()) {
				if (key.startsWith('attribute_')) {
					ajaxData.append(key, value);
				}
			}
		}

		fetch(window.bizstudio?.ajaxUrl || '/wp-admin/admin-ajax.php', {
			method: 'POST',
			body: ajaxData,
			credentials: 'same-origin',
		})
			.then((res) => res.json())
			.then((data) => {
				// Reset button
				submitBtn.classList.remove('is-loading');
				submitBtn.disabled = false;

				if (data.success) {
					// Success animation on button
					submitBtn.textContent = 'Aggiunto!';
					submitBtn.classList.add('is-success');

					setTimeout(() => {
						submitBtn.textContent = originalText;
						submitBtn.classList.remove('is-success');
					}, 2000);

					// Update cart count everywhere
					document.querySelectorAll('.biz-cart-count').forEach((el) => {
						el.textContent = data.data.cartCount;
					});

					// Update mini-cart HTML
					if (data.data.miniCart) {
						const miniCart = document.getElementById('biz-mini-cart');
						if (miniCart) {
							miniCart.outerHTML = data.data.miniCart;
						}
					}

					// Open mini-cart drawer
					window.openMiniCart?.();

					// Toast notification
					showToast(data.data.message);
				} else {
					submitBtn.textContent = originalText;
					showToast(data.data?.message || 'Errore', true);
				}
			})
			.catch(() => {
				submitBtn.classList.remove('is-loading');
				submitBtn.disabled = false;
				submitBtn.textContent = originalText;
				showToast('Errore di connessione', true);
			});
	});

	function showToast(message, isError = false) {
		document.querySelector('.biz-toast')?.remove();
		const toast = document.createElement('div');
		toast.className = 'biz-toast';
		if (isError) toast.style.background = 'var(--biz-error)';
		toast.textContent = message;
		document.body.appendChild(toast);
		requestAnimationFrame(() => toast.classList.add('is-visible'));
		setTimeout(() => {
			toast.classList.remove('is-visible');
			setTimeout(() => toast.remove(), 300);
		}, 3000);
	}
})();
