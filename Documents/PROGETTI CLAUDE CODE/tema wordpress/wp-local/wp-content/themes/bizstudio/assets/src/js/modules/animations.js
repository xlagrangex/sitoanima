/**
 * BizStudio — Premium Animations Module
 * Sprint 5: parallax, page transitions, loading bar, smooth scroll, back-to-top
 *
 * @package BizStudio
 */

(function () {
	'use strict';

	const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	/* -------------------------------------------------------
	   5.1 + 5.2 PARALLAX — Hero + Banner sections
	   ------------------------------------------------------- */
	if (!reducedMotion) {
		const parallaxElements = document.querySelectorAll('[data-parallax]');

		if (parallaxElements.length) {
			let ticking = false;

			function updateParallax() {
				const scrollY = window.scrollY;
				const viewportH = window.innerHeight;

				parallaxElements.forEach((el) => {
					const rect = el.getBoundingClientRect();
					const speed = parseFloat(el.dataset.parallax) || 0.15;

					// Only update when element is near the viewport
					if (rect.bottom < -100 || rect.top > viewportH + 100) return;

					const offset = scrollY * speed;
					el.style.transform = `translate3d(0, ${offset}px, 0)`;
				});

				ticking = false;
			}

			window.addEventListener('scroll', () => {
				if (!ticking) {
					requestAnimationFrame(updateParallax);
					ticking = true;
				}
			}, { passive: true });

			// Initial position
			updateParallax();
		}

		// Hero decorative blob parallax (the ::before pseudo-element is CSS-only,
		// so we parallax the hero content slightly for depth)
		const hero = document.querySelector('.biz-hero');
		if (hero) {
			let heroTicking = false;

			window.addEventListener('scroll', () => {
				if (heroTicking) return;
				heroTicking = true;

				requestAnimationFrame(() => {
					const scrollY = window.scrollY;
					const heroH = hero.offsetHeight;

					if (scrollY < heroH * 1.5) {
						const content = hero.querySelector('.biz-hero__content');
						if (content) {
							content.style.transform = `translate3d(0, ${scrollY * 0.08}px, 0)`;
						}
					}
					heroTicking = false;
				});
			}, { passive: true });
		}
	}

	/* -------------------------------------------------------
	   5.6 PAGE TRANSITION — Fade out on navigate
	   ------------------------------------------------------- */
	if (!reducedMotion) {
		const main = document.querySelector('.biz-main');
		if (main) {
			main.classList.add('biz-page-transition');
		}

		document.addEventListener('click', (e) => {
			const link = e.target.closest('a[href]');
			if (!link) return;

			const href = link.getAttribute('href');
			// Skip: external, anchor, new tab, special
			if (!href ||
				href.startsWith('#') ||
				href.startsWith('mailto:') ||
				href.startsWith('tel:') ||
				href.startsWith('javascript:') ||
				link.target === '_blank' ||
				link.hasAttribute('download') ||
				e.ctrlKey || e.metaKey || e.shiftKey) {
				return;
			}

			// Skip same-page links
			try {
				const url = new URL(href, window.location.origin);
				if (url.origin !== window.location.origin) return;
				if (url.pathname === window.location.pathname && url.hash) return;
			} catch {
				return;
			}

			e.preventDefault();

			const mainEl = document.querySelector('.biz-page-transition');
			if (mainEl) {
				mainEl.classList.add('biz-page-transition--leaving');
				setTimeout(() => {
					window.location.href = href;
				}, 200);
			} else {
				window.location.href = href;
			}
		});
	}

	/* -------------------------------------------------------
	   5.7 LOADING BAR — Top progress indicator
	   ------------------------------------------------------- */
	(function () {
		let bar = null;

		function createBar() {
			bar = document.createElement('div');
			bar.className = 'biz-loading-bar';
			document.body.appendChild(bar);
			return bar;
		}

		function startLoadingBar() {
			if (!bar) createBar();
			bar.className = 'biz-loading-bar';
			bar.style.width = '0';
			// Force reflow
			bar.offsetWidth;
			bar.classList.add('biz-loading-bar--active');
		}

		function finishLoadingBar() {
			if (!bar) return;
			bar.classList.remove('biz-loading-bar--active');
			bar.classList.add('biz-loading-bar--done');

			setTimeout(() => {
				bar.classList.add('biz-loading-bar--hide');
				setTimeout(() => {
					bar.className = 'biz-loading-bar';
					bar.style.width = '0';
				}, 400);
			}, 200);
		}

		// Start on page-leaving navigation
		window.addEventListener('beforeunload', startLoadingBar);

		// Finish bar when page loads (for back/forward navigation)
		window.addEventListener('pageshow', (e) => {
			if (e.persisted && bar) {
				finishLoadingBar();
			}
		});

		// Expose for AJAX usage
		window.bizLoadingBar = { start: startLoadingBar, finish: finishLoadingBar };
	})();

	/* -------------------------------------------------------
	   5.8 ADD TO CART — Checkmark animation
	   ------------------------------------------------------- */
	// Intercept successful add-to-cart to show checkmark
	document.addEventListener('click', (e) => {
		const btn = e.target.closest('.biz-add-to-cart-btn');
		if (!btn || btn.classList.contains('is-added')) return;

		// We don't prevent default — the existing AJAX handler does the work.
		// We watch for the 'is-loading' class to be removed (= done).
		const observer = new MutationObserver((mutations) => {
			for (const m of mutations) {
				if (m.attributeName === 'class' &&
					!btn.classList.contains('is-loading') &&
					!btn.classList.contains('is-added')) {
					// AJAX completed
					observer.disconnect();

					// Only show checkmark if the button didn't get an error
					if (!btn.classList.contains('is-error')) {
						btn.classList.add('is-added');
						setTimeout(() => {
							btn.classList.remove('is-added');
						}, 2000);
					}
					break;
				}
			}
		});

		observer.observe(btn, { attributes: true, attributeFilter: ['class'] });
	});

	/* -------------------------------------------------------
	   5.11 SMOOTH SCROLL — Anchor links
	   ------------------------------------------------------- */
	document.addEventListener('click', (e) => {
		const link = e.target.closest('a[href^="#"]');
		if (!link) return;

		const hash = link.getAttribute('href');
		if (hash === '#' || hash.length < 2) return;

		const target = document.querySelector(hash);
		if (!target) return;

		e.preventDefault();

		const headerH = parseInt(
			getComputedStyle(document.documentElement).getPropertyValue('--biz-header-h') || '72',
			10
		);

		const top = target.getBoundingClientRect().top + window.scrollY - headerH - 16;

		window.scrollTo({
			top,
			behavior: reducedMotion ? 'auto' : 'smooth',
		});

		// Update URL hash without jumping
		history.pushState(null, '', hash);
	});

	/* -------------------------------------------------------
	   5.12 BACK TO TOP BUTTON
	   ------------------------------------------------------- */
	(function () {
		const btn = document.createElement('button');
		btn.className = 'biz-back-to-top';
		btn.setAttribute('aria-label', 'Torna su');
		btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 15l-6-6-6 6"/></svg>';
		document.body.appendChild(btn);

		let visible = false;
		const THRESHOLD = 400;

		window.addEventListener('scroll', () => {
			const shouldShow = window.scrollY > THRESHOLD;
			if (shouldShow !== visible) {
				visible = shouldShow;
				btn.classList.toggle('is-visible', visible);
			}
		}, { passive: true });

		btn.addEventListener('click', () => {
			window.scrollTo({
				top: 0,
				behavior: reducedMotion ? 'auto' : 'smooth',
			});
		});
	})();
})();
