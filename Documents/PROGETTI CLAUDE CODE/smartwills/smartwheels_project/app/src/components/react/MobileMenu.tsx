import { useEffect, useState } from 'react';
import { Menu, X, ArrowRight } from 'lucide-react';
import { NAV_LINKS, ctaUrl } from '../../lib/site';

export default function MobileMenu({ pathname }: { pathname: string }) {
  const [open, setOpen] = useState(false);

  useEffect(() => {
    document.body.style.overflow = open ? 'hidden' : '';
    return () => {
      document.body.style.overflow = '';
    };
  }, [open]);

  return (
    <>
      <button
        onClick={() => setOpen(true)}
        aria-label="Open menu"
        className="grid size-11 place-items-center rounded-full border border-line bg-white/70 text-ink"
      >
        <Menu size={20} />
      </button>

      <div
        className={`fixed inset-0 z-[60] flex flex-col bg-cream transition-all duration-300 ${
          open ? 'pointer-events-auto opacity-100' : 'pointer-events-none opacity-0'
        }`}
      >
        <div className="wrap flex items-center justify-between py-4">
          <span className="font-heading text-[1.55rem] font-semibold tracking-tight">
            <span className="text-ink">Smart</span>
            <span className="text-gold">Wills</span>
            <span className="text-ink">.ae</span>
          </span>
          <button
            onClick={() => setOpen(false)}
            aria-label="Close menu"
            className="grid size-11 place-items-center rounded-full border border-line bg-white/70 text-ink"
          >
            <X size={20} />
          </button>
        </div>

        <nav className="wrap mt-8 flex flex-col gap-2" aria-label="Mobile">
          {NAV_LINKS.map((link, i) => {
            const active = pathname.startsWith(link.href);
            return (
              <a
                key={link.href}
                href={link.href}
                onClick={() => setOpen(false)}
                className={`flex items-center justify-between border-b border-line py-5 font-heading text-3xl font-medium transition-colors ${
                  active ? 'text-gold-dark' : 'text-ink'
                }`}
                style={{ transitionDelay: `${i * 30}ms` }}
              >
                {link.label}
                <ArrowRight size={22} className="text-gold" />
              </a>
            );
          })}
        </nav>

        <div className="wrap mt-auto pb-10">
          <a
            href={ctaUrl('mobile-menu')}
            target="_blank"
            rel="noopener"
            className="group flex items-center justify-between rounded-full bg-gold py-3 pl-6 pr-3 text-base font-medium text-ink"
          >
            Book your free Risk Profile Audit
            <span className="grid size-10 place-items-center rounded-full bg-ink text-cream">
              <ArrowRight size={16} />
            </span>
          </a>
          <p className="mt-4 text-center text-sm text-ink-soft">
            30 minutes, no obligation — we clarify your situation.
          </p>
        </div>
      </div>
    </>
  );
}
