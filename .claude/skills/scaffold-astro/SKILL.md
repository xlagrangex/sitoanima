---
name: scaffold-astro
description: Use when the user asks to scaffold or start a new website project with the BizStudio Astro stack — triggers like "scaffolda astro", "scaffolda un nuovo sito", "nuovo progetto BizStudio Astro", "crea lo stack bizstudio", "parti con lo stack dei nostri siti". For new/empty directories only, never to retrofit existing projects.
---

# Scaffold Astro (stack BizStudio)

## Overview

Scaffolda lo stack condiviso dei siti BizStudio (bizstudioastro, vincenzopetronebizsito, BIZpharma, viclafuture, bhtsrl) in una directory vuota. **Regola assoluta: solo contenuti placeholder neutri** — mai copiare testi, immagini, palette brand o contenuti dai progetti sorgente; di loro si replica SOLO lo stack.

## Stack (versioni minime, non inventare alternative)

Astro `^5.18`, output `static` · `@astrojs/react ^4.4` + React 19 (isole in `components/react/`) · Tailwind **v4 CSS-first** via `@tailwindcss/vite` (NO tailwind.config, NO `@astrojs/tailwind`) · GSAP `^3.14` + ScrollTrigger · Lenis `^1.3` · split-type `^0.3` · nanostores + `@nanostores/react` · clsx + tailwind-merge · lucide-react · font self-hosted via `@fontsource-variable/*` · **pnpm** + Node `>=22.12` · deploy Vercel (nessun adapter se statico). Niente sitemap/MDX/altre integration di default.

## Template di riferimento

Scaffold canonico pulito: `~/Documents/PROGETTI CLAUDE CODE/infrastrutturepersone` (commit `feat(infrastrutturepersone): scaffold BizStudio Astro stack`). Se ancora allo stato placeholder, copia i file da lì adattando solo il `name` nel package.json. Se è evoluto in sito reale, recupera quel commit da git oppure copia dai sorgenti originali:

- `viclafuture/src/lib/{lenis.ts, gsap.ts, cn.ts}` — al lenis.ts aggiungi il guard `prefers-reduced-motion` se assente
- `bhtsrl/src/lib/reveal.ts` — il sistema di scroll-reveal dichiarativo, copialo VERBATIM (non riscriverlo "simile")

## File da creare (ordine consigliato)

1. `package.json` — name del progetto, `"type": "module"`, `engines.node >=22.12.0`, scripts standard astro (`dev/build/preview/astro`), dipendenze dello stack + i 2 fontsource del brand (default neutri: `@fontsource-variable/inter` + `@fontsource-variable/outfit`)
2. `pnpm-workspace.yaml` — **gotcha pnpm ≥10**: senza questo la build fallisce con `ERR_PNPM_IGNORED_BUILDS`:
   ```yaml
   allowBuilds:
     esbuild: true
     sharp: true
   ```
3. `.nvmrc` = `22` · `.gitignore` (node_modules, dist, .astro, .vercel, .env*, .DS_Store)
4. `astro.config.mjs`:
   ```js
   import { defineConfig } from 'astro/config';
   import react from '@astrojs/react';
   import tailwindcss from '@tailwindcss/vite';
   export default defineConfig({
     output: 'static',
     integrations: [react()],
     vite: { plugins: [tailwindcss()] },
   });
   ```
5. `tsconfig.json` — `extends: "astro/tsconfigs/strict"`, `jsx: "react-jsx"`, `jsxImportSource: "react"`, alias `"@/*": ["src/*"]`
6. `src/lib/` — cn.ts, lenis.ts, gsap.ts, reveal.ts (vedi Template di riferimento)
7. `src/styles/global.css` nell'ordine: `@import "tailwindcss"` → import fontsource → `@theme` (font, colori placeholder, `--radius-card/button/pill`, `--container-page/wide`, `--shadow-*`) → regole `html.lenis` → **FOUC guard dentro `@media (prefers-reduced-motion: no-preference)`** (target `:not([data-reveal-init])`, classe `html.has-js-reveal`) → `@layer base` reset → `@layer components` (`.container-page`, `.eyebrow`, `.btn-pill`, scala `.t-hero/.t-h2/.t-body-lg`)
8. `src/layouts/Layout.astro` — `lang="it"`, Props title/description con default placeholder, `<ClientRouter />`, script inline `document.documentElement.classList.add('has-js-reveal')`, keyframes view-transition (ethereal-in/out) con `prefers-reduced-motion → animation: none`, body con Header/main slot/Footer, script finale: `initLenis()` subito, poi `boot()` (= `initReveal()` dopo `document.fonts.ready`) su DOMContentLoaded E `astro:page-load`, e `astro:before-swap → ScrollTrigger.getAll().forEach(t => t.kill())`
9. `src/components/Header.astro` e `Footer.astro` con **`data-no-reveal`** (la chrome non deve animarsi), contenuti placeholder ("Logo", "Nome Azienda", "P.IVA 00000000000")
10. Cartelle `src/components/{sections,ui,react}`, `public/images/`, favicon placeholder
11. `src/pages/index.astro` — UNA pagina di esempio con 3 sezioni placeholder che dimostrano il reveal: hero (h1 char-reveal automatico + p cascata + `data-reveal` sul CTA), griglia card (`data-reveal data-reveal-distance="32"`), sezione chiusura. Testi generici tipo "Titolo placeholder", "Card 1/2/3" — nulla che assomigli a un sito esistente
12. `stack-tecnico.md` — doc didattico dello stack (copia-adatta dal template)

## Verifica (obbligatoria prima di dichiarare finito)

```bash
pnpm install && pnpm build   # deve compilare senza errori
pnpm preview                  # + screenshot Playwright della pagina
```

Controlla nello screenshot: font display sui titoli, card con radius/shadow, bottoni pill. Poi commit granulare + push (regola globale utente).

## Common Mistakes

| Errore | Fix |
|---|---|
| Usare npm perché "default delle convenzioni" | Questo stack è pnpm — `.nvmrc` 22 + `pnpm-workspace.yaml` allowBuilds |
| Inventare un sistema `data-animate` generico | Copiare `reveal.ts` verbatim: char-split h1/h2, cascata sezioni, `data-reveal/-dir/-distance/-blur/-duration`, `data-no-reveal` |
| Saltare `<ClientRouter />` e il re-boot su `astro:page-load` | View Transitions sono parte dello stack; init idempotenti + kill ScrollTrigger su before-swap |
| FOUC guard fuori dalla media query | Senza `@media (prefers-reduced-motion: no-preference)` gli utenti reduced-motion vedono pagina vuota |
| Aggiungere @astrojs/sitemap, MDX, tailwind.config | Non fanno parte dello stack: Tailwind v4 è CSS-first, niente integration extra |
| Copiare palette/testi da un sito BizStudio esistente | Solo placeholder neutri; i token brand si definiscono dopo, per progetto |
| Google Fonts o `astro:assets` | Font self-hosted Fontsource in global.css; immagini in `public/images/` per URL |
