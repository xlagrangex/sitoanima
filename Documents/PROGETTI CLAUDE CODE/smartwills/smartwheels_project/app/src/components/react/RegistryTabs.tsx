import { useState } from 'react';
import { Check } from 'lucide-react';
import type { Registry } from '../../lib/content';

const petals = ['petal-tl', 'petal-tr', 'petal-br'];

export default function RegistryTabs({ items }: { items: Registry[] }) {
  const [active, setActive] = useState(1); // ADJD first — widest audience
  const r = items[active];

  const rows: Array<[string, string]> = [
    ['Language', r.language],
    ['Who can use it', r.who],
    ['Witnesses', r.witnesses],
    ['Process', r.process],
    ['Government fee — single will', r.feesSingle],
    ['Government fee — mirror wills', r.feesMirror],
  ];

  return (
    <div>
      <div className="flex flex-wrap gap-2.5" role="tablist" aria-label="Registries">
        {items.map((item, i) => (
          <button
            key={item.id}
            role="tab"
            aria-selected={i === active}
            onClick={() => setActive(i)}
            className={`rounded-full px-6 py-3 font-heading text-lg font-semibold transition-all duration-300 ${
              i === active
                ? 'bg-ink text-cream shadow-[0_10px_30px_-12px_rgba(26,22,18,0.6)]'
                : 'border border-line bg-white/70 text-ink hover:border-gold'
            }`}
          >
            {item.name}
          </button>
        ))}
      </div>

      <div key={r.id} className="mt-8 grid items-stretch gap-6 lg:grid-cols-[0.9fr_1.1fr]">
        <div className={`${petals[active]} relative min-h-[16rem] overflow-hidden`}>
          <img
            src={r.image}
            alt={`${r.name} registry district`}
            className="absolute inset-0 h-full w-full object-cover"
          />
          <div className="absolute inset-0 bg-gradient-to-t from-ink/55 via-transparent to-transparent" />
          <div className="absolute bottom-5 left-5">
            <span className="rounded-full bg-cream/90 px-3.5 py-1.5 text-xs font-medium text-brown">{r.emirate}</span>
            <h3 className="mt-2.5 font-heading text-3xl font-semibold text-cream">{r.name}</h3>
          </div>
        </div>

        <div className="petal-br-sm border border-line bg-white/80 p-7 md:p-9">
          <p className="leading-relaxed text-ink-soft">{r.tagline}</p>
          <dl className="mt-5 divide-y divide-line">
            {rows.map(([dt, dd]) => (
              <div key={dt} className="flex items-start justify-between gap-6 py-3 text-[0.93rem]">
                <dt className="shrink-0 text-ink-soft">{dt}</dt>
                <dd className="text-right font-medium text-ink">{dd}</dd>
              </div>
            ))}
          </dl>
          <p className="mt-5 rounded-2xl bg-cream-deep/70 p-4 text-[0.9rem] leading-relaxed text-ink-soft">
            {r.note}
          </p>
        </div>
      </div>

      <p className="mt-6 flex items-start gap-2 text-sm text-ink-soft">
        <Check size={16} className="mt-0.5 shrink-0 text-gold-dark" />
        Government fees are public information and are paid directly to the
        registry. Timelines depend on the registry — we tell you exactly what to
        expect for yours.
      </p>
    </div>
  );
}
