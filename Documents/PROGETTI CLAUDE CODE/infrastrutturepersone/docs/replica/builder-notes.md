# Note condivise per i builder delle pagine (replica Flexio)

Progetto Astro 5 + Tailwind v4 CSS-first. Root: `/Users/vincenzopetrone/Documents/PROGETTI CLAUDE CODE/infrastrutturepersone`. NON eseguire pnpm/build — solo scrivere file. TypeScript strict.

## Componenti condivisi (già pronti, NON ricrearli)

- `src/layouts/Layout.astro` — props `{ title?, description? }`. Include già Header (nav fissa) e Footer. Boot automatico di Lenis, marquee e animazioni.
- `src/components/ui/Eyebrow.astro` — `<Eyebrow>label</Eyebrow>` pill arancio; `variant="light"` = pill bianca testo arancio (per sfondi arancio).
- `src/components/ui/Button.astro` — `<Button href variant>label</Button>`, variant: `primary` (arancio) | `secondary` (cream, bordo arancio) | `dark` (#262121). Hover text-swap integrato.
- `src/components/CtaBanner.astro` — banner arancio "Ready to Elevate?" completo di marquee forme + strip `radius-top`. Usalo come penultima sezione dove previsto dalla spec.
- `src/components/react/Counter.tsx` — `<Counter client:visible end={20} start={0} suffix="+" />` count-up in viewport.
- `src/components/react/PhysicsTags.tsx` — `<PhysicsTags client:visible tags={[{label,bg,color}]} height={420} />` pill con gravità matter.js (solo desktop: nascondi sotto 1200px e mostra fallback statico).

## Token e utility CSS (global.css)

- Colori Tailwind: `bg-orange`, `bg-cream`, `bg-peach`, `bg-quote`, `bg-ink`, `text-ink`, `text-orange`, `bg-tag-blue|teal|darkblue|yellow|pink|lightblue|lightorange`.
- Tipografia: classi `t-h1`, `t-h2`, `t-h3`, `t-h4`, `t-large`, `t-body`, `t-body-sm` (responsive clamp già incluse). Font default Clash Grotesk; form in Cabinet Grotesk → `font-form` via `font-family: var(--font-form)`.
- `container-page` = max-width 1140 centrato (aggiungi `px-4 md:px-7` sui wrapper).
- `radius-card` 20px / `radius-panel` 24px / `radius-pill` 99px → `rounded-[20px]` ecc.

## Hook animazioni (già gestiti dal layout, basta marcare l'HTML)

- **Curtain hero**: classe `curtain-panel` sul pannello cream del hero (slide-up da fuori viewport al load).
- **Zoom foto**: attributo `data-scale-in` su un wrapper con `overflow-hidden` → scale 1.15→1 in viewport.
- **Marquee**: `<div class="marquee" data-marquee-speed="40" data-marquee-hover="0.7" [data-marquee-scroll]><div class="marquee-track gap-X">…items…</div></div>`. Il JS clona e anima. `data-marquee-scroll` solo per la strip foto hero home.
- **Sezione video pinnata**: `<section data-video-section>` con altezza grande (es. `h-[2280px]`), figlio sticky `top-0 h-screen`; video con `data-video-scale`; due headline con `data-video-headline` (transizione opacity/translate gestita dal JS, dai loro `transition` CSS e posizionale sovrapposte); due div trigger invisibili `data-video-trigger="0"` / `data-video-trigger="1"` piazzati a metà sezione.
- **Footer parallax**: già nel Footer condiviso.

## Asset locali

Tutte le immagini framerusercontent sono in `public/images/<basename>` (stesso nome file dell'URL: es. `https://framerusercontent.com/images/Xup44lT05fI7ULikDpDUcdet5M.png` → `/images/Xup44lT05fI7ULikDpDUcdet5M.png`). Video in `/videos/<basename>.mp4`. USA SOLO percorsi locali.

## Dati CMS

- `src/data/services.json` — array `{slug,title,description,image,mainContentHtml,inlineImage,subContentHtml}`.
- `src/data/case-studies.json` — array `{slug,title,description,tag,image,mainContentHtml,inlineImage,subContentHtml,clientName,duration,solution}`.
- Render dei campi HTML con `set:html`. Per le route dinamiche usa `getStaticPaths` sui JSON.

## Regole

- Pagine in inglese, contenuti VERBATIM dalle spec (questa è una replica fedele, contenuti inclusi).
- Niente "Promo Card" né badge Framer.
- Sezioni `<section>` con padding della spec (usa valori arbitrari Tailwind es. `pt-[140px]`).
- Componenti di sezione specifici della pagina vanno in `src/components/sections/<pagina>/...` solo se riusati; altrimenti inline nella pagina.
