import { useEffect, useRef, useState } from "react";

interface CounterProps {
  end: number;
  start?: number;
  suffix?: string;
  duration?: number;
  className?: string;
}

export default function Counter({ end, start = 0, suffix = "", duration = 1.6, className }: CounterProps) {
  const [value, setValue] = useState(start);
  const ref = useRef<HTMLSpanElement>(null);
  const started = useRef(false);

  useEffect(() => {
    const el = ref.current;
    if (!el) return;
    const io = new IntersectionObserver((entries) => {
      if (!entries[0].isIntersecting || started.current) return;
      started.current = true;
      io.disconnect();
      const t0 = performance.now();
      const tick = (t: number) => {
        const p = Math.min((t - t0) / (duration * 1000), 1);
        const eased = 1 - Math.pow(1 - p, 3);
        setValue(Math.round(start + (end - start) * eased));
        if (p < 1) requestAnimationFrame(tick);
      };
      requestAnimationFrame(tick);
    }, { threshold: 0.5 });
    io.observe(el);
    return () => io.disconnect();
  }, [start, end, duration]);

  return (
    <span ref={ref} className={className}>
      {value}
      {suffix}
    </span>
  );
}
