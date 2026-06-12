import { gsap } from "gsap";

/**
 * Marquee GSAP: elementi `.marquee` con figlio `.marquee-track`.
 * data-marquee-speed: px/s (default 40)
 * data-marquee-hover: fattore di rallentamento on hover (es. 0.2); assente = nessun effetto
 * data-marquee-scroll: se presente, somma un offset legato allo scroll (hero Flexio, fino a -3500px)
 * Il contenuto della track viene clonato finché copre 2× la viewport, poi trasla con wrap modulare.
 */
export function initMarquees() {
  if (typeof window === "undefined") return;
  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

  document.querySelectorAll<HTMLElement>(".marquee").forEach((root) => {
    if (root.dataset.marqueeInit === "1") return;
    root.dataset.marqueeInit = "1";

    const track = root.querySelector<HTMLElement>(".marquee-track");
    if (!track) return;

    const speed = Number(root.dataset.marqueeSpeed ?? 40);
    const hoverFactor = root.dataset.marqueeHover ? Number(root.dataset.marqueeHover) : null;
    const scrollLinked = root.dataset.marqueeScroll !== undefined;

    const originals = Array.from(track.children) as HTMLElement[];
    const baseWidth = () => originals.reduce((w, el) => w + el.getBoundingClientRect().width, 0)
      + parseFloat(getComputedStyle(track).gap || "0") * originals.length;

    let unit = baseWidth();
    while (track.scrollWidth < window.innerWidth * 2 + unit) {
      originals.forEach((el) => track.appendChild(el.cloneNode(true)));
      if (track.children.length > 200) break;
    }
    unit = baseWidth();

    let x = 0;
    let factor = 1;
    let scrollOffset = 0;

    if (hoverFactor !== null) {
      root.addEventListener("mouseenter", () => { factor = hoverFactor; });
      root.addEventListener("mouseleave", () => { factor = 1; });
    }

    if (scrollLinked) {
      window.addEventListener("scroll", () => {
        scrollOffset = -Math.min(window.scrollY * 1.2, 3500);
      }, { passive: true });
    }

    gsap.ticker.add((_t, delta) => {
      if (!track.isConnected) return;
      x -= (speed * factor * delta) / 1000;
      const total = x + scrollOffset;
      track.style.transform = `translateX(${((total % unit) - (total % unit > 0 ? unit : 0))}px)`;
    });
  });
}
