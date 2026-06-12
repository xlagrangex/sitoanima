import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
import { getLenis } from "./lenis";

let registered = false;

export function setupGsap() {
  if (typeof window === "undefined") return { gsap, ScrollTrigger };
  if (registered) return { gsap, ScrollTrigger };

  gsap.registerPlugin(ScrollTrigger);

  const lenis = getLenis();
  if (lenis) {
    lenis.on("scroll", ScrollTrigger.update);
  }

  registered = true;
  return { gsap, ScrollTrigger };
}

export { gsap, ScrollTrigger };
