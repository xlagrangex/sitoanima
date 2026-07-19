import Lenis from 'lenis';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import SplitType from 'split-type';

gsap.registerPlugin(ScrollTrigger);

const REVEAL_FROM: Record<string, gsap.TweenVars> = {
  up: { y: 36, opacity: 0 },
  left: { x: -48, opacity: 0 },
  right: { x: 48, opacity: 0 },
  scale: { scale: 0.88, opacity: 0 },
};

/* Which single corner a petal element rounds, read from its class. */
const CORNERS = ['tl', 'tr', 'bl', 'br'] as const;
type Corner = (typeof CORNERS)[number];

const CORNER_PROP: Record<Corner, string> = {
  tl: 'borderTopLeftRadius',
  tr: 'borderTopRightRadius',
  bl: 'borderBottomLeftRadius',
  br: 'borderBottomRightRadius',
};

function petalCorner(el: HTMLElement): Corner | null {
  for (const c of CORNERS) {
    if (el.classList.contains(`petal-${c}`) || el.classList.contains(`petal-${c}-sm`)) return c;
  }
  return null;
}

/* The one rule: the rounded corner is a CIRCULAR quarter whose radius is half
   the element's shorter side — so it occupies half the width and half the
   height. Computed in JS because CSS can't express min(w,h)/2 as a circular
   radius on content-sized boxes. Re-run on resize. */
function petalRadius(el: HTMLElement): number {
  const r = el.getBoundingClientRect();
  return Math.min(r.width, r.height) / 2;
}

function roundString(corner: Corner, r: number): string {
  const v: Record<Corner, string> = {
    tl: `${r}px 0 0 0`,
    tr: `0 ${r}px 0 0`,
    bl: `0 0 0 ${r}px`,
    br: `0 0 ${r}px 0`,
  };
  return v[corner];
}

function setupPetals() {
  const petals = Array.from(
    document.querySelectorAll<HTMLElement>(
      '[class*="petal-tl"],[class*="petal-tr"],[class*="petal-bl"],[class*="petal-br"]',
    ),
  ).filter((el) => petalCorner(el));

  const apply = () => {
    for (const el of petals) {
      const corner = petalCorner(el)!;
      (el.style as unknown as Record<string, string>)[CORNER_PROP[corner]] = `${petalRadius(el)}px`;
    }
  };

  apply();

  let raf = 0;
  window.addEventListener('resize', () => {
    cancelAnimationFrame(raf);
    raf = requestAnimationFrame(apply);
  });
}

export default function initAnimations() {
  /* Petal radii are geometry, not motion — set them even under reduced motion. */
  setupPetals();

  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduced) return;

  /* Smooth scroll — Lenis, synced with GSAP's ticker */
  const lenis = new Lenis({ lerp: 0.11, smoothWheel: true });
  lenis.on('scroll', ScrollTrigger.update);
  gsap.ticker.add((time) => lenis.raf(time * 1000));
  gsap.ticker.lagSmoothing(0);

  /* Varied reveals: data-reveal="up|left|right|scale" */
  gsap.utils.toArray<HTMLElement>('[data-reveal]').forEach((el) => {
    const kind = el.dataset.reveal || 'up';
    const from = REVEAL_FROM[kind] ?? REVEAL_FROM.up;
    gsap.fromTo(el, from, {
      x: 0,
      y: 0,
      scale: 1,
      opacity: 1,
      duration: 1.05,
      ease: 'power3.out',
      delay: parseFloat(el.dataset.delay || '0'),
      scrollTrigger: { trigger: el, start: 'top 88%' },
    });
  });

  /* Image mask wipes: data-mask="tl|tr|bl|br" keeps the petal corner.
     The rounded corner uses the same half-shorter-side radius as card petals;
     border-radius + overflow-hidden supplies the shape, so on complete we drop
     the clip-path (keeping it responsive to resize). */
  gsap.utils.toArray<HTMLElement>('[data-mask]').forEach((el) => {
    const corner = (el.dataset.mask || 'tl') as Corner;
    const r = petalRadius(el);
    (el.style as unknown as Record<string, string>)[CORNER_PROP[corner]] = `${r}px`;
    const round = roundString(corner, r);
    gsap.fromTo(
      el,
      { clipPath: `inset(100% 0 0 0 round ${round})` },
      {
        clipPath: `inset(0% 0 0 0 round ${round})`,
        duration: 1.25,
        ease: 'power4.out',
        delay: parseFloat(el.dataset.delay || '0'),
        scrollTrigger: { trigger: el, start: 'top 86%' },
        onComplete: () => {
          el.style.clipPath = 'none';
        },
      },
    );
  });

  /* Petal bloom for collage pieces (hero, fires on load) */
  const blooms = gsap.utils.toArray<HTMLElement>('[data-bloom]');
  if (blooms.length) {
    gsap.fromTo(
      blooms,
      { scale: 0.55, opacity: 0, rotation: (i) => (i % 2 ? 5 : -5) },
      {
        scale: 1,
        opacity: 1,
        rotation: 0,
        duration: 1.15,
        ease: 'back.out(1.5)',
        stagger: 0.11,
        delay: 0.2,
        clearProps: 'rotation',
      },
    );
  }

  /* Word-by-word heading reveals */
  document.querySelectorAll<HTMLElement>('[data-split]').forEach((el) => {
    const split = new SplitType(el, { types: 'words' });
    gsap.fromTo(
      split.words,
      { y: '0.6em', opacity: 0 },
      {
        y: 0,
        opacity: 1,
        duration: 0.9,
        ease: 'power4.out',
        stagger: 0.045,
        delay: parseFloat(el.dataset.delay || '0'),
        scrollTrigger: { trigger: el, start: 'top 86%' },
      },
    );
  });

  /* Line-by-line reveals for intros (clipped slide) */
  document.querySelectorAll<HTMLElement>('[data-lines]').forEach((el) => {
    const split = new SplitType(el, { types: 'lines' });
    split.lines?.forEach((line) => {
      (line as HTMLElement).style.overflow = 'hidden';
    });
    gsap.fromTo(
      split.lines,
      { yPercent: 110 },
      {
        yPercent: 0,
        duration: 0.95,
        ease: 'power4.out',
        stagger: 0.09,
        scrollTrigger: { trigger: el, start: 'top 86%' },
      },
    );
  });

  /* Scroll-scrubbed word highlight (signature effect) */
  document.querySelectorAll<HTMLElement>('[data-highlight]').forEach((el) => {
    const split = new SplitType(el, { types: 'words' });
    gsap.fromTo(
      split.words,
      { opacity: 0.16 },
      {
        opacity: 1,
        ease: 'none',
        stagger: 0.06,
        scrollTrigger: {
          trigger: el,
          start: 'top 78%',
          end: 'bottom 48%',
          scrub: 0.4,
        },
      },
    );
  });

  /* Gentle parallax on tagged media */
  gsap.utils.toArray<HTMLElement>('[data-parallax]').forEach((el) => {
    const amt = parseFloat(el.dataset.parallax || '8');
    gsap.fromTo(
      el,
      { yPercent: -amt },
      {
        yPercent: amt,
        ease: 'none',
        scrollTrigger: {
          trigger: el.closest('[data-parallax-scope]') || el.parentElement || el,
          start: 'top bottom',
          end: 'bottom top',
          scrub: 0.5,
        },
      },
    );
  });

  /* Number tickers */
  gsap.utils.toArray<HTMLElement>('[data-count]').forEach((el) => {
    const target = parseFloat(el.dataset.count || '0');
    const obj = { v: 0 };
    gsap.to(obj, {
      v: target,
      duration: 1.6,
      ease: 'power2.out',
      scrollTrigger: { trigger: el, start: 'top 90%' },
      onUpdate: () => {
        el.textContent = String(Math.round(obj.v));
      },
    });
  });
}
