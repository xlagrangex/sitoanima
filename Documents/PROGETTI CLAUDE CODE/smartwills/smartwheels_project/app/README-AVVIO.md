# SmartWills.ae — Sito istituzionale

Sito statico multi-pagina per SmartWills.ae (registrazione testamenti UAE per expat).

## Stack

- **Astro 6** + isole **React 19** (menu mobile, FAQ, slider testimonianze, tab registri)
- **Tailwind CSS 4** (via `@tailwindcss/vite`)
- **GSAP 3** + ScrollTrigger + **SplitType** (animazioni allo scroll)
- **Lenis** (smooth scroll)
- Font self-hosted: Crimson Pro + DM Sans (Fontsource)
- Immagini AI in `public/images`

## Requisiti

- **Node.js 22.12 o superiore** (consigliato: 22 LTS). Verifica con `node -v`.

## Avvio in locale

```bash
npm install        # installa le dipendenze
npm run dev        # sviluppo → http://localhost:4321
```

## Build e anteprima della build

```bash
npm run build      # genera la cartella dist/ (il sito statico finale)
npm run preview    # serve la build → http://localhost:4321
```

La cartella `dist/` **è già inclusa e pronta**: puoi caricarla così com'è su
Netlify, Vercel, Cloudflare Pages o qualsiasi hosting statico (anche cPanel).

## Struttura

```
src/
├── layouts/      Base.astro (shell), Article.astro (articoli)
├── components/
│   ├── ui/       Logo, Button, SectionTag
│   ├── sections/ Hero, Problem, RegistriesTeaser, StepsTeaser, WhyUs,
│   │             Manifesto, Testimonials, Faq, FinalCta, Marquee
│   └── react/    MobileMenu, FaqAccordion, TestimonialSlider, RegistryTabs
├── pages/        index, how-it-works, registries, about, resources/*,
│                 7-day-will-challenge, book-a-call
├── lib/          site.ts (link CTA+UTM), content.ts (testi/fatti), animate.ts
└── styles/       global.css (palette, forme "petalo", prosa articoli)
```

## Note operative

- **CTA**: tutte puntano a `https://smartwills.ae/book-a-call-3073` con UTM per
  placement — si gestiscono da `src/lib/site.ts` (`ctaUrl`).
- **Form 7-Day Challenge**: c'è un segnaposto stilato in
  `src/pages/7-day-will-challenge.astro` con un commento `GHL FORM EMBED GOES
  HERE` — incollare lì l'embed GoHighLevel.
- **Contenuti**: fatti, fee governative, testimonianze e FAQ sono centralizzati
  in `src/lib/content.ts`. Non aggiungere claim non verificati (vedi brief).
