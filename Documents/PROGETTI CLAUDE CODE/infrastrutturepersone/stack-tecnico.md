# Stack tecnico — BizStudio Astro

Stack condiviso dei siti BizStudio (bizstudioastro, vincenzopetronebizsito, BIZpharma, viclafuture, bhtsrl). Questo progetto ne è uno scaffold pulito: **tutti i contenuti sono placeholder**.

## Core

| Componente | Scelta |
|---|---|
| Framework | Astro 5, output `static` (adapter `@astrojs/vercel` solo se servono route SSR) |
| UI interattiva | React 19 come isole (`client:load` / `client:visible`), solo in `src/components/react/` |
| Styling | Tailwind v4 CSS-first via `@tailwindcss/vite` — **nessun tailwind.config**, tokens in `@theme` dentro `src/styles/global.css` |
| Animazioni | GSAP + ScrollTrigger, Lenis (smooth scroll), SplitType (char reveal) |
| State cross-isola | nanostores + @nanostores/react |
| Font | Self-hosted via Fontsource, import in `global.css` |
| Icone | lucide-react |
| Package manager | pnpm, Node >= 22.12 (`.nvmrc`) |
| Deploy | Vercel (preset Astro, build `pnpm build`, output `dist/`) |

## Struttura

```
src/
├── layouts/Layout.astro      # head, ClientRouter, boot Lenis/reveal, cleanup VT
├── components/
│   ├── Header.astro, Footer.astro
│   ├── sections/             # sezioni di pagina (.astro, PascalCase)
│   ├── ui/                   # primitive (.astro)
│   └── react/                # isole interattive (.tsx)
├── pages/                    # route kebab-case in italiano
├── lib/
│   ├── cn.ts                 # clsx + tailwind-merge
│   ├── lenis.ts              # singleton idempotente, guard reduced-motion
│   ├── gsap.ts               # setupGsap(): registra ScrollTrigger, sync con Lenis
│   └── reveal.ts             # sistema dichiarativo di scroll-reveal (vedi sotto)
└── styles/global.css         # UNICO file di design tokens + base + utilities
```

## Sistema reveal (data-attributes)

Il reveal è automatico e dichiarativo, gestito da `lib/reveal.ts`:

- **h1/h2** → char-reveal espressivo (SplitType), **h3/h4** → versione rapida
- **p, blockquote, figure dentro `<section>`** → fade+blur automatico in cascata (step 0.12s)
- `data-reveal` → forza il reveal su qualsiasi elemento; opzioni: `data-reveal-dir` (up/down/left/right/none), `data-reveal-distance`, `data-reveal-blur`, `data-reveal-duration`
- `data-reveal-group` → tratta un wrapper non-section come gruppo cascata
- `data-no-reveal` → esclude un elemento e i suoi figli (usarlo su Header/Footer)
- FOUC guard: script inline setta `html.has-js-reveal`; il CSS nasconde i target solo dentro `@media (prefers-reduced-motion: no-preference)`

## Convenzioni

- Astro components = zero-JS di default; React solo dove serve interattività reale
- View Transitions attive (`<ClientRouter />`); ogni boot va dentro `astro:page-load` con init idempotenti; `astro:before-swap` killa gli ScrollTrigger
- Immagini in `public/images/`, referenziate per URL (niente `astro:assets`)
- Design tokens: prima si aggiorna `style.md` (se presente), poi `global.css` lo rispecchia
- Pagine legali: `privacy-policy`, `cookie-policy` in kebab-case
