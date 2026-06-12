import Lenis from "lenis";

let lenis: Lenis | null = null;

export function initLenis() {
  if (typeof window === "undefined") return null;
  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return null;
  if (lenis) return lenis;

  lenis = new Lenis({
    // lerp basso = scroll piu setoso/floaty.
    // Default 0.1, qui 0.07 → frame muove del 7% verso il target.
    lerp: 0.07,
    smoothWheel: true,
    // Lieve riduzione velocita rotella per dare piu spazio all'interpolazione.
    wheelMultiplier: 0.95,
    // Touch resta naturale, non sync (su mobile l'inertia nativa e' gia ottima).
    syncTouch: false,
    touchMultiplier: 1.5,
  });

  function raf(time: number) {
    lenis?.raf(time);
    requestAnimationFrame(raf);
  }
  requestAnimationFrame(raf);

  return lenis;
}

export function getLenis() {
  return lenis;
}
