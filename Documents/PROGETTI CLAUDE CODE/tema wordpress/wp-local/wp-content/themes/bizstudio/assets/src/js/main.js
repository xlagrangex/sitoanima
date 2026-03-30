/**
 * BizStudio — Main JS
 * Zero dependencies, vanilla ES2024+
 *
 * @package BizStudio
 */

(function () {
	'use strict';

	/* -------------------------------------------------------
	   Header scroll effect
	   ------------------------------------------------------- */
	const header = document.getElementById('biz-header');
	if (header) {
		let lastScroll = 0;
		window.addEventListener('scroll', () => {
			const y = window.scrollY;
			header.classList.toggle('is-scrolled', y > 10);
			lastScroll = y;
		}, { passive: true });
	}

	/* -------------------------------------------------------
	   Mobile menu
	   ------------------------------------------------------- */
	const menuToggle = document.getElementById('biz-menu-toggle');
	const mobileMenu = document.getElementById('biz-mobile-menu');
	const mobileOverlay = document.getElementById('biz-mobile-overlay');
	const mobileClose = document.getElementById('biz-mobile-close');

	function openMobile() {
		mobileMenu?.classList.add('is-open');
		mobileOverlay?.classList.add('is-open');
		document.body.style.overflow = 'hidden';
	}
	function closeMobile() {
		mobileMenu?.classList.remove('is-open');
		mobileOverlay?.classList.remove('is-open');
		document.body.style.overflow = '';
	}

	menuToggle?.addEventListener('click', openMobile);
	mobileClose?.addEventListener('click', closeMobile);
	mobileOverlay?.addEventListener('click', closeMobile);

	/* -------------------------------------------------------
	   Dark mode
	   ------------------------------------------------------- */
	const darkToggle = document.getElementById('biz-dark-toggle');
	const html = document.documentElement;

	// Check saved preference or system preference
	const savedTheme = localStorage.getItem('bizstudio-theme');
	if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
		html.classList.add('dark');
	}

	darkToggle?.addEventListener('click', () => {
		html.classList.toggle('dark');
		localStorage.setItem('bizstudio-theme', html.classList.contains('dark') ? 'dark' : 'light');
	});

	/* -------------------------------------------------------
	   Scroll reveal (IntersectionObserver)
	   ------------------------------------------------------- */
	if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		const revealObserver = new IntersectionObserver(
			(entries) => {
				entries.forEach((entry) => {
					if (entry.isIntersecting) {
						entry.target.classList.add('is-visible');

						// Stagger child items
						const items = entry.target.querySelectorAll('[data-reveal-item]');
						items.forEach((item, i) => {
							setTimeout(() => {
								item.classList.add('is-visible');
							}, i * 80);
						});

						revealObserver.unobserve(entry.target);
					}
				});
			},
			{ threshold: 0.15 }
		);

		document.querySelectorAll('[data-reveal]').forEach((el) => {
			revealObserver.observe(el);
		});

		// Also observe standalone reveal-items (not inside a [data-reveal] parent)
		document.querySelectorAll('[data-reveal-item]').forEach((el) => {
			if (!el.closest('[data-reveal]')) {
				revealObserver.observe(el);
			}
		});
	} else {
		// Show everything immediately for users who prefer reduced motion
		document.querySelectorAll('[data-reveal], [data-reveal-item]').forEach((el) => {
			el.classList.add('is-visible');
		});
	}

	/* -------------------------------------------------------
	   AJAX Add to Cart (from product cards)
	   ------------------------------------------------------- */
	document.addEventListener('click', (e) => {
		const btn = e.target.closest('.biz-add-to-cart-btn');
		if (!btn) return;

		e.preventDefault();
		const productId = btn.dataset.productId;
		if (!productId || btn.classList.contains('is-loading')) return;

		btn.classList.add('is-loading');

		const formData = new FormData();
		formData.append('action', 'bizstudio_add_to_cart');
		formData.append('product_id', productId);
		formData.append('quantity', 1);
		formData.append('nonce', window.bizstudio?.nonce || '');

		fetch(window.bizstudio?.ajaxUrl || '/wp-admin/admin-ajax.php', {
			method: 'POST',
			body: formData,
			credentials: 'same-origin',
		})
			.then((res) => res.json())
			.then((data) => {
				btn.classList.remove('is-loading');

				if (data.success) {
					// Update cart count
					document.querySelectorAll('.biz-cart-count').forEach((el) => {
						el.textContent = data.data.cartCount;
					});

					// Update mini-cart HTML
					if (data.data.miniCart) {
						const miniCartContainer = document.getElementById('biz-mini-cart');
						if (miniCartContainer) {
							miniCartContainer.outerHTML = data.data.miniCart;
						}
					}

					// Open mini-cart
					openMiniCart();

					// Show toast
					showToast(data.data.message);
				} else {
					showToast(data.data?.message || 'Errore', true);
				}
			})
			.catch(() => {
				btn.classList.remove('is-loading');
				showToast('Errore di connessione', true);
			});
	});

	/* -------------------------------------------------------
	   Toast notification
	   ------------------------------------------------------- */
	function showToast(message, isError = false) {
		// Remove existing toast
		document.querySelector('.biz-toast')?.remove();

		const toast = document.createElement('div');
		toast.className = 'biz-toast';
		if (isError) toast.style.background = 'var(--biz-error)';
		toast.textContent = message;
		document.body.appendChild(toast);

		requestAnimationFrame(() => {
			toast.classList.add('is-visible');
		});

		setTimeout(() => {
			toast.classList.remove('is-visible');
			setTimeout(() => toast.remove(), 300);
		}, 3000);
	}

	/* -------------------------------------------------------
	   Mini Cart open/close (exposed globally for mini-cart.js)
	   ------------------------------------------------------- */
	window.openMiniCart = openMiniCart;
	window.closeMiniCart = closeMiniCart;

	function openMiniCart() {
		const cart = document.getElementById('biz-mini-cart');
		const overlay = document.getElementById('biz-mini-cart-overlay');
		cart?.classList.add('is-open');
		overlay?.classList.add('is-open');
		document.body.style.overflow = 'hidden';
	}

	function closeMiniCart() {
		const cart = document.getElementById('biz-mini-cart');
		const overlay = document.getElementById('biz-mini-cart-overlay');
		cart?.classList.remove('is-open');
		overlay?.classList.remove('is-open');
		document.body.style.overflow = '';
	}
})();
