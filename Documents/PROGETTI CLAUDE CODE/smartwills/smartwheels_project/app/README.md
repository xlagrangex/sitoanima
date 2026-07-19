# SmartWills.ae — Sito istituzionale

Sito istituzionale multi-pagina per **SmartWills.ae** (coordinamento della registrazione
testamentaria per expat negli UAE). Stack: Astro 6 statico + isole React 19, Tailwind CSS 4,
GSAP 3 + ScrollTrigger + SplitType, Lenis smooth scroll, Lucide icons, Nanostores.

## Requisiti

- **Node.js >= 22.12** (Astro 6 non gira su Node 20)
- npm >= 10

## Avvio rapido

```bash
npm install
npm run dev        # dev server su http://localhost:4321
npm run build      # build statico in dist/
npm run preview    # anteprima della build
```

> Le immagini in `public/images/` non sono tracciate in questa repo (binari):
> copiarle dallo zip di consegna nella stessa posizione prima del build.

## Struttura

```
src/
  layouts/        Base.astro (shell + animazioni), Article.astro
  components/
    ui/           Logo, Button, SectionTag
    react/        MobileMenu, FaqAccordion, TestimonialSlider, RegistryTabs (isole)
    sections/     Hero, Marquee, Problem, StepsTeaser, RegistriesTeaser, WhyUs,
                  Manifesto, Testimonials, Faq, FinalCta
  lib/            site.ts (CTA/UTM), content.ts (contenuti), animate.ts (GSAP/Lenis), utils.ts
  pages/          index, how-it-works, registries, about, book-a-call,
                  7-day-will-challenge, resources/ (indice + 3 articoli)
  styles/         global.css (token Tailwind 4 via @theme, petal system)
public/images/    asset generati (JPG)
```

## Convenzioni

- **Petal system**: `.petal-tl/tr/bl/br` (un angolo a quarto di cerchio, gli altri morbidi)
  come cifra grafica ricorrente; varianti `-sm` e `.disc` per i cerchi pieni.
- **CTA unica**: tutte le call-to-action puntano a `BOOKING_URL` con UTM per placement
  (vedi `src/lib/site.ts → ctaUrl()`).
- **Animazioni**: attributi `data-reveal`, `data-mask`, `data-bloom`, `data-split`,
  `data-highlight`, `data-parallax`, `data-count` gestiti da `src/lib/animate.ts`.
- **Palette**: ink `#1a1612`, brown `#8b6347`, gold `#c9a668`, cream `#faf6ef`.

## Compliance (da non rimuovere)

SmartWills è un corporate service provider, **non** uno studio legale: la bozza è dello
studio legale partner, la traduzione di un traduttore legale registrato MOJ. Footer con
ragione sociale (Mardef Consulting FZCO · License No. 18873), disclaimer obbligatorio,
linguaggio condizionale, niente prezzi di servizio (solo fee governative), testimonianze
solo reali da Trustpilot.
