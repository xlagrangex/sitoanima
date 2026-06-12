/**
 * Animazioni Flexio: scale-in immagini, scale video pinnato, swap headline video,
 * parallax footer. Idempotente, da richiamare su astro:page-load.
 */
export function initFlexioAnimations() {
  if (typeof window === "undefined") return;
  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

  // Foto con zoom-out all'ingresso in viewport (scale 1.15 → 1, CSS transition)
  const scaleEls = document.querySelectorAll<HTMLElement>("[data-scale-in]:not([data-anim-init])");
  if (scaleEls.length) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((e) => {
        if (e.isIntersecting) {
          e.target.classList.add("is-inview");
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.25 });
    scaleEls.forEach((el) => { el.dataset.animInit = "1"; io.observe(el); });
  }

  // Sezione video pinnata: swap delle headline ai trigger di scroll
  document.querySelectorAll<HTMLElement>("[data-video-section]:not([data-anim-init])").forEach((section) => {
    section.dataset.animInit = "1";
    const headlines = section.querySelectorAll<HTMLElement>("[data-video-headline]");
    const triggers = section.querySelectorAll<HTMLElement>("[data-video-trigger]");
    if (headlines.length < 2 || !triggers.length) return;

    const io = new IntersectionObserver((entries) => {
      entries.forEach((e) => {
        if (!e.isIntersecting) return;
        const idx = Number((e.target as HTMLElement).dataset.videoTrigger ?? 0);
        headlines.forEach((h, i) => {
          h.style.opacity = i === idx ? "1" : "0";
          h.style.transform = i === idx ? "translateY(0)" : "translateY(20px)";
        });
      });
    }, { threshold: 0.5 });
    triggers.forEach((t) => io.observe(t));

    // Video scale 1.5 → 1 all'ingresso
    const video = section.querySelector<HTMLElement>("[data-video-scale]");
    if (video) {
      video.style.transform = "scale(1.5)";
      video.style.transition = "transform 1.2s cubic-bezier(0.22, 1, 0.36, 1)";
      const vio = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting) {
          video.style.transform = "scale(1)";
          vio.disconnect();
        }
      }, { threshold: 0.2 });
      vio.observe(section);
    }
  });

  // Footer parallax reveal (outer -100 → 0, inner 50 → 0)
  document.querySelectorAll<HTMLElement>("[data-footer-parallax]:not([data-anim-init])").forEach((footer) => {
    footer.dataset.animInit = "1";
    const inner = footer.querySelector<HTMLElement>("[data-footer-inner]");
    const update = () => {
      const r = footer.getBoundingClientRect();
      const progress = Math.min(Math.max(1 - r.top / window.innerHeight, 0), 1);
      footer.style.transform = `translateY(${-100 + progress * 100}px)`;
      if (inner) inner.style.transform = `translateY(${50 - progress * 50}px)`;
    };
    window.addEventListener("scroll", update, { passive: true });
    update();
  });
}
