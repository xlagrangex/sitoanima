import { useState } from 'react';
import { Plus } from 'lucide-react';
import type { Faq } from '../../lib/content';

export default function FaqAccordion({ items }: { items: Faq[] }) {
  const [open, setOpen] = useState<number | null>(0);

  return (
    <div className="divide-y divide-line border-y border-line">
      {items.map((item, i) => {
        const isOpen = open === i;
        return (
          <div key={i}>
            <button
              onClick={() => setOpen(isOpen ? null : i)}
              aria-expanded={isOpen}
              className="group flex w-full items-center justify-between gap-6 py-6 text-left"
            >
              <span
                className={`font-heading text-[1.3rem] leading-snug font-semibold transition-colors sm:text-[1.45rem] ${
                  isOpen ? 'text-gold-dark' : 'text-ink group-hover:text-brown'
                }`}
              >
                {item.q}
              </span>
              <span
                className={`grid size-10 shrink-0 place-items-center rounded-full border transition-all duration-300 ${
                  isOpen
                    ? 'rotate-45 border-gold bg-gold text-ink'
                    : 'border-line text-ink group-hover:border-gold'
                }`}
              >
                <Plus size={17} />
              </span>
            </button>
            <div
              className={`grid transition-[grid-template-rows] duration-400 ease-out ${
                isOpen ? 'grid-rows-[1fr] pb-6' : 'grid-rows-[0fr]'
              }`}
            >
              <div className="overflow-hidden">
                <p className="max-w-3xl text-[0.97rem] leading-relaxed text-ink-soft">{item.a}</p>
              </div>
            </div>
          </div>
        );
      })}
    </div>
  );
}
