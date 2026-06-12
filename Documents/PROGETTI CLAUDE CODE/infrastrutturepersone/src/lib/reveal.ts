import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
import SplitType from "split-type";

gsap.registerPlugin(ScrollTrigger);

type Direction = "up" | "down" | "left" | "right" | "none";

const HEADING_SELECTOR = "h1, h2, h3, h4, [data-reveal-text]";
const BLOCK_SELECTOR = ".reveal, [data-reveal], .kicker";
// Auto-animated content blocks *inside groups only* (opt-out with data-no-reveal)
const AUTO_BLOCK_SELECTOR = "p, blockquote, figure";
const GROUP_SELECTOR = "section, [data-reveal-group]";

// Fixed cascade step between consecutive items in a group (sibling order)
const CASCADE_STEP = 0.12;

const dirOffset = (dir: Direction, amount: number) => {
  switch (dir) {
    case "up": return { x: 0, y: amount };
    case "down": return { x: 0, y: -amount };
    case "left": return { x: amount, y: 0 };
    case "right": return { x: -amount, y: 0 };
    default: return { x: 0, y: 0 };
  }
};

const isHeading = (el: Element) => el.matches(HEADING_SELECTOR);

function splitHeading(el: HTMLElement) {
  if (!el.textContent?.trim()) return null;
  const split = new SplitType(el, { types: "lines,words,chars", tagName: "span" });
  const chars = (split.chars ?? []) as HTMLElement[];
  chars.forEach((c) => {
    c.style.display = "inline-block";
    c.style.willChange = "transform, filter, opacity";
  });
  return chars;
}

function animateHeading(tl: gsap.core.Timeline, el: HTMLElement, at: number) {
  const chars = splitHeading(el);
  if (!chars || !chars.length) {
    tl.fromTo(el, { opacity: 0 }, { opacity: 1, duration: 0.6 }, at);
    return 0.6;
  }

  // Major headings (h1/h2/[data-reveal-text]) get the expressive letter rise;
  // minor ones (h3/h4) animate much faster so they don't steal the scene.
  const tag = el.tagName.toLowerCase();
  const isMajor = tag === "h1" || tag === "h2" || el.hasAttribute("data-reveal-text");

  const cfg = isMajor
    ? { y: "0.9em", blur: 12, duration: 0.9, stagger: 0.055, ease: "power3.out" }
    : { y: "0.45em", blur: 6, duration: 0.4, stagger: 0.018, ease: "power2.out" };

  tl.fromTo(
    chars,
    { opacity: 0, y: cfg.y, filter: `blur(${cfg.blur}px)` },
    {
      opacity: 1,
      y: 0,
      filter: "blur(0px)",
      duration: cfg.duration,
      ease: cfg.ease,
      stagger: cfg.stagger,
    },
    at,
  );
  // effective duration for cascading the next item
  return cfg.duration + chars.length * cfg.stagger;
}

function animateBlock(tl: gsap.core.Timeline, el: HTMLElement, at: number) {
  const dir = (el.dataset.revealDir as Direction) || "up";
  const amount = Number(el.dataset.revealDistance ?? 24);
  const blurAmount = Number(el.dataset.revealBlur ?? 16);
  const duration = Number(el.dataset.revealDuration ?? 1.2);
  const { x, y } = dirOffset(dir, amount);

  el.style.willChange = "transform, filter, opacity";

  tl.fromTo(
    el,
    { opacity: 0, x, y, scale: 0.985, filter: `blur(${blurAmount}px)` },
    {
      opacity: 1,
      x: 0,
      y: 0,
      scale: 1,
      filter: "blur(0px)",
      duration,
      ease: "power2.out",
      onComplete: () => { el.style.willChange = "auto"; },
    },
    at,
  );
  return duration;
}

function collectItems(group: Element): HTMLElement[] {
  const all = Array.from(
    group.querySelectorAll<HTMLElement>(
      `${HEADING_SELECTOR}, ${BLOCK_SELECTOR}, ${AUTO_BLOCK_SELECTOR}`,
    ),
  );
  return all.filter((el) => {
    if (el.dataset.revealInit === "1") return false;
    if (el.closest("[data-no-reveal]")) return false;
    // Skip items that live inside a nested reveal-group (they'll be handled by that group)
    const nearestGroup = el.closest(GROUP_SELECTOR);
    if (nearestGroup && nearestGroup !== group) return false;
    // Ignore empty auto-blocks (e.g. empty <p> used as spacer)
    if (el.matches(AUTO_BLOCK_SELECTOR) && !el.textContent?.trim()) return false;
    return true;
  });
}

function initGroup(group: HTMLElement) {
  if (group.dataset.revealGroupInit === "1") return;
  group.dataset.revealGroupInit = "1";

  const items = collectItems(group);
  if (!items.length) return;

  items.forEach((el) => (el.dataset.revealInit = "1"));

  const tl = gsap.timeline({
    scrollTrigger: {
      trigger: group,
      start: "top 82%",
      once: true,
    },
  });

  items.forEach((el, i) => {
    const at = i * CASCADE_STEP;
    if (isHeading(el)) animateHeading(tl, el, at);
    else animateBlock(tl, el, at);
  });
}

function initStandalone() {
  // Elements that are not inside any group get a per-element ScrollTrigger,
  // preserving the original behavior for one-offs.
  const loose = document.querySelectorAll<HTMLElement>(
    `${HEADING_SELECTOR}, ${BLOCK_SELECTOR}`,
  );
  loose.forEach((el) => {
    if (el.dataset.revealInit === "1") return;
    if (el.closest("[data-no-reveal]")) return;
    if (el.closest(GROUP_SELECTOR)) return;
    el.dataset.revealInit = "1";

    const tl = gsap.timeline({
      scrollTrigger: { trigger: el, start: "top 85%", once: true },
    });
    if (isHeading(el)) animateHeading(tl, el, 0);
    else animateBlock(tl, el, 0);
  });
}

export function initReveal() {
  if (typeof window === "undefined") return;
  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

  document.querySelectorAll<HTMLElement>(GROUP_SELECTOR).forEach(initGroup);
  initStandalone();
}
