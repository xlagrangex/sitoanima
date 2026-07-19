import { useCallback, useEffect, useState } from 'react';
import { ArrowLeft, ArrowRight, Star, Quote } from 'lucide-react';
import type { Testimonial } from '../../lib/content';

export default function TestimonialSlider({
  items,
  dark = false,
}: {
  items: Testimonial[];
  dark?: boolean;
}) {
  const [index, setIndex] = useState(0);
  const [paused, setPaused] = useState(false);

  const go = useCallback(
    (dir: number) => setIndex((i) => (i + dir + items.length) % items.length),
    [items.length],
  );

  useEffect(() => {
    if (paused) return;
    const t = setInterval(() => go(1), 7000);
    return () => clearInterval(t);
  }, [go, paused]);

  return (
    <div
      onMouseEnter={() => setPaused(true)}
      onMouseLeave={() => setPaused(false)}
      className="relative mx-auto max-w-3xl text-center"
    >
      <Quote
        className={`mx-auto size-12 ${dark ? 'text-gold/60' : 'text-gold/40'}`}
        aria-hidden
      />

      <div className="relative mt-6 min-h-[15rem] sm:min-h-[11rem]">
        {items.map((t, i) => (
          <figure
            key={t.name + i}
            aria-hidden={i !== index}
            className={`absolute inset-0 transition-all duration-700 ${
              i === index
                ? 'translate-y-0 opacity-100'
                : 'pointer-events-none translate-y-4 opacity-0'
            }`}
          >
            <blockquote
              className={`font-heading text-[1.5rem] leading-snug font-medium sm:text-[1.8rem] ${
                dark ? 'text-cream' : 'text-ink'
              }`}
            >
              “{t.quote}”
            </blockquote>
            <figcaption className="mt-6 flex items-center justify-center gap-3">
              <span
                className={`grid size-10 place-items-center rounded-full font-heading text-lg font-semibold ${
                  dark ? 'bg-gold text-ink' : 'bg-gold text-ink'
                }`}
              >
                {t.name.charAt(0)}
              </span>
              <span className="text-left">
                <span
                  className={`block text-[0.95rem] font-semibold ${dark ? 'text-cream' : 'text-ink'}`}
                >
                  {t.name}
                </span>
                <span
                  className={`mt-0.5 flex items-center gap-1.5 text-xs ${
                    dark ? 'text-cream/60' : 'text-ink-soft'
                  }`}
                >
                  <span className="flex gap-0.5 text-trust">
                    {Array.from({ length: 5 }).map((_, s) => (
                      <Star key={s} size={11} fill="currentColor" strokeWidth={0} />
                    ))}
                  </span>
                  Trustpilot review
                </span>
              </span>
            </figcaption>
          </figure>
        ))}
      </div>

      <div className="mt-10 flex items-center justify-center gap-6">
        <button
          onClick={() => go(-1)}
          aria-label="Previous review"
          className={`grid size-12 place-items-center rounded-full border transition-colors ${
            dark
              ? 'border-cream/20 text-cream hover:border-gold hover:bg-gold/10'
              : 'border-line text-ink hover:border-gold hover:bg-gold/10'
          }`}
        >
          <ArrowLeft size={17} />
        </button>
        <div className="flex gap-2">
          {items.map((_, i) => (
            <button
              key={i}
              onClick={() => setIndex(i)}
              aria-label={`Go to review ${i + 1}`}
              className={`h-1.5 rounded-full transition-all duration-300 ${
                i === index
                  ? 'w-8 bg-gold'
                  : dark
                    ? 'w-3 bg-cream/25 hover:bg-gold/60'
                    : 'w-3 bg-line hover:bg-gold'
              }`}
            />
          ))}
        </div>
        <button
          onClick={() => go(1)}
          aria-label="Next review"
          className="grid size-12 place-items-center rounded-full bg-gold text-ink transition-colors hover:bg-gold-dark"
        >
          <ArrowRight size={17} />
        </button>
      </div>
    </div>
  );
}
