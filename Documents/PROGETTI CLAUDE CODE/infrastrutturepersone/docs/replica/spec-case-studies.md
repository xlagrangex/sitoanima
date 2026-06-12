# Spec — Case Studies: List (`/case-studies`) + Detail Template (`/case-studies/[slug]`)

Sources:
- List: https://first-effect-565679.framer.app/case-studies
- Detail: https://first-effect-565679.framer.app/case-studies/driving-impact-a-healthcare-platform-s-rebuild (template verified identical across all 4 slugs — only CMS fields + related cards change)

CMS content lives in `docs/replica/cms-case-studies.json`. Fields per item: `slug, title, description, tag, image, mainContentHtml, inlineImage, subContentHtml, clientName, duration, solution`. **JSON array order matters** — it is the collection order used for the "Other Case Study" rule (see below).

Design tokens, fonts, typography presets, Navigation, Primary/Secondary buttons, Eyebrow badge, Pre-footer CTA banner ("Ready to Elevate? / Proceed Toward Your Next Achievement"), Footer and Back-to-top are the SAME shared components documented in `spec-services.md` — not re-described here except where this page differs.

---

## LIST PAGE — `/case-studies`

`<title>Flexio</title>`; meta description = generic site description ("Flexio is a sleek Framer template for service-based businesses. Ideal for marketing agencies, business consultants, growth strategies, digital solutions and service providers."). OG image: `https://framerusercontent.com/images/VEohya2wX3vlba21WjmLwpybww.png`.

### Page section order
1. Navigation (shared, fixed)
2. Hero + cards grid (one section — the cards are INSIDE the hero section)
3. Pre-footer CTA banner (shared)
4. Footer (shared)

No filters, no pagination, no search, no back-to-top on this page. There is NO per-card description on the list — cards show only image + tag + title.

### 1. Hero section
- Outer `<section data-framer-name="Hero">` bg orange `#fe6037`, overflow hidden.
- Inner Container: bg cream `#fefaf6`, padding `180px 30px 140px` (tablet `160px 40px 100px`, phone `144px 16px 64px`), full width.
- **Page-load entrance**: the whole cream Container starts `translateY(1070px)` → springs to 0, spring `{damping:60, stiffness:400, mass:1.2, delay:0}` (orange shows behind during the slide-up). Same pattern as all other pages.
- Content column: max-width **1140px** (phone max-width 500px), column, **gap 40px**, items start-aligned but text centered:
  1. **Title Block** (column, centered, gap 12px):
     - Eyebrow badge, Primary variant (orange `#fe6037` bg, radius 4px, padding 4px 8px): text **"case study"** (rendered UPPERCASE by preset: 14px / lh 16px / 500 / letter-spacing 1px / white).
     - `<h2>` preset `1cqcawc` (Clash Grotesk 500, 60/48/40px, lh 1em, color `#262121`), centered, max-width 800px (700px on phone): **"Our Solutions Drive Success for Clients"**
  2. **Cards container** (column, centered, **gap 40px** between rows).

### 2. Cards grid — 2×2
Container holds **two "Cards" rows**; each row: `display:flex; flex-direction:row; gap:24px; width:100%` → 2 cards per row. On phone the row becomes `flex-direction:column; gap:30px`.

Row 1: healthcare card, startup card. Row 2: nonprofit card, real-estate card.
**Display order on list page**: healthcare → startup → nonprofit → real-estate (note: this differs from the JSON/collection order).

#### Card component (whole card is one `<a href="/case-studies/{slug}">`)
- Block link, `cursor:pointer`, column, ~558px wide at desktop (fluid `width:100%` of its flex cell), gap between image and text block: **24px** (rendered via 12px margins).
- **Image wrapper**: width 100%, height **385px** fixed at desktop (mobile variant: aspect-ratio **1.44935** instead of fixed height), border-radius **20px** (16px mobile), `overflow:hidden`.
  - Inner image layer: rendered at `perspective(1200px) scale(1.15)` initial; settles to scale 1 when in view (spring damping 64 / stiffness 250 / delay 0.1).
  - **Hover**: image layer grows to `width:115%; height:115%` (desktop variant; mobile variant 110%) → classic zoom-on-hover inside the rounded mask. Replicate as: img scale 1 → 1.1/1.15 on `a:hover` with a springy ease, `object-fit:cover`.
  - `<img>` = CMS `image`, alt text (verbatim per slug): healthcare "people discussing", startup "team using laptop", nonprofit "business women discussing", real-estate "Business team presenting chart".
- **Text Block** below image (column, gap 8px):
  - Eyebrow badge, Primary variant (orange, radius 4, padding 4px 8px, uppercase white 14px): CMS `tag` (e.g. "Healthcare").
  - `<h4>` preset `f4bszl` (Clash Grotesk 500, **32px** desktop / 26px mobile, lh 1.2em, `#262121`): CMS `title`.

### 3 + 4. CTA banner and Footer — byte-identical to the shared versions in `spec-services.md`.

---

## DETAIL TEMPLATE — `/case-studies/[slug]`

`<title>{title} - Flexio</title>`; meta description = CMS `description`. OG image: same site-wide `VEohya2wX3vlba21WjmLwpybww.png`.

This is the same "blog" template as the service detail (`spec-services.md`) with three differences: (a) hero eyebrow = CMS `tag` instead of fixed text, (b) the sticky sidebar is a project-meta card instead of the "Transform Your Business" card, (c) the related section is "Other Case Study" with an "Explore All" button and uses the list-page card style (image + tag + title), not the dark-overlay service cards.

### Page section order
1. Navigation (shared, fixed)
2. `<main>` (bg **orange `#fe6037`**) containing:
   - `hero trigger`: invisible 1px-high absolute div at top with **`id="hero"`** (anchor target for back-to-top)
   - One big `<section>` bg cream `#fefaf6`, padding `180px 30px 140px` (tablet `160px 40px 100px`, phone `144px 16px 64px`) containing EVERYTHING: hero, article+sidebar, Other Case Study
3. Pre-footer CTA banner (shared)
4. Footer (shared)
5. **Back-to-top** floating button (shared component; `position:fixed; bottom:0; right:0; padding:32px 32px 68px` desktop / 24px mobile; white circle radius 80px, shadow `0 2px 4px rgba(0,0,0,.1)`; initial `opacity:0; translateY(48px)`, appears after scroll; `href="#hero"`).

### Page-load entrance
The entire cream `<section>` starts `translateY(1070px)` → 0, spring `{damping:60, stiffness:400, mass:1.2}` (orange `<main>` bg revealed behind).

### Inner Container
max-width **1140px**, column, **gap 100px** (tablet 80px, phone: max-width 500px, gap 64px). Children: [Top hero block] → [Blog Container] → [Other Case Study Container].

### A. Hero block ("Top", column, gap 40px, overflow hidden)
1. **Header Section** (column, centered, gap 24px):
   - Title Block (gap 12px): Eyebrow badge Primary (orange) = **CMS `tag`** (uppercase, e.g. "HEALTHCARE").
   - `<h2>` preset `1cqcawc` (60/48/40px, lh 1em), centered, max-width 700px = **CMS `title`**.
   - Header Description `<p>` preset `1044c9w` (Clash Grotesk 500, 18px/lh 28px desktop; 16px/26px mobile), centered, max-width 600px = **CMS `description`**.
2. **Hero image** = CMS `image`. Wrapper: width 100%, aspect-ratio **2.07273**, border-radius **24px** (20px tablet / 16px phone), bg placeholder `#faebdc`, overflow hidden, object-fit cover.
   - **Entrance**: `opacity:0.001; perspective(1200px) scale(2)` → `opacity:1; scale:1`, spring `{damping:64, stiffness:250, mass:1}` — dramatic zoom-settle on load.

### B. Blog Container — article + sticky meta sidebar
`display:flex; flex-direction:row; gap:100px` (tablet gap 40px; phone `flex-direction:column; gap:60px` — sidebar drops BELOW the article).

**B1. Left article column** — `flex:2`, column, gap 40px:
1. **Main-Content**: render CMS `mainContentHtml` as rich text. `h3` → preset `1j87fkn` (Clash Grotesk 500, 44/40/36px, lh 1.2em); `p`/`li` → preset `1e7rr0f` (Clash Grotesk 400, 16px, lh 26px, `#262121`); `<ul><li><p>` bulleted lists; `<br>` line breaks preserved.
2. **Quote block** (FIXED template text, identical on all 4 slugs): full-width, bg **`#f9eadb`**, border-radius **8px**, padding **24px**, centered; one `<p>` preset `1044c9w` (18px/28px/500):
   > "Effective marketing is not just about reaching people—it's about connecting with them in meaningful ways."
3. **Inline image** = CMS `inlineImage`: width 100%, aspect-ratio **1.71391**, border-radius **20px** (16px mobile), overflow hidden, object-fit cover. Alt (healthcare): "People using laptop and discusing" (sic).
4. **Sub-Content**: render CMS `subContentHtml` (same presets as Main-Content; h3 "Transformational Results" + paragraph).

**B2. Right sticky META sidebar ("CTA")** — `flex:1`, **`position:sticky; top:60px`**, `z-index:1`, bg **`#f9eadb`**, border-radius **16px**, padding **30px**, column, **gap 40px** (on phone: `flex:none; width:100%`, not sticky in practice since it's after the content). Four "Text Block"s, each column gap 8px:

| # | Heading (preset `1044c9w`, 18px/28px/500 — label text verbatim) | Value (preset `1e7rr0f`, 16px/26px/400) |
|---|---|---|
| 1 | `Client's Name` | CMS `clientName` (e.g. "CareBridge Health") |
| 2 | `Duration` | CMS `duration` (e.g. "2 months") |
| 3 | `Sulution` ⚠️ **typo is verbatim in the original — keep or fix consciously** | CMS `solution` |
| 4 | `Share Case Study` | row of 4 social icon buttons, gap 8px |

Share icon buttons ("Large" variant): `<a>` 40×40px, bg **white**, border-radius 99px (perfect circles), dark glyph (#262121) centered; `target="_blank"`. Links (generic, verbatim): `https://facebook.com`, `https://twitter.com`, `https://Linkedin.com`, `https://instagram.com`. Glyphs: Facebook, Twitter/X, LinkedIn, Instagram.

There is NO industry tag, no shape, no button inside the sidebar — the industry tag lives in the hero eyebrow.

### C. "Other Case Study" section
Column, gap 40px (tablet 60px, phone 32px):

1. **Heading row**: `flex-direction:row; justify-content:space-between; align-items:flex-end` (phone: column, centered, gap 24px):
   - `<h2>` preset `1cqcawc`, left-aligned, max-width 700px: **"Other Case Study"**
   - **Secondary button "Explore All"** → `/case-studies` (pill radius 99px, cream bg, orange `#fe6037` label, text-swap hover — same Secondary component as banner "Learn More").
2. **Cards row**: same card row as the list page (`flex; gap:24px`, phone column gap 30px) with exactly **2 cards**, using the SAME card component as the list page (image radius 20/16, hover zoom 115%, tag pill + h4 title below, scroll-in image scale 1.1→1 spring damping 64/stiffness 250/delay .1).

**Related-cards rule (verified by fetching all 4 detail pages)**: show the **first 2 items of the CMS collection order excluding the current item**. Collection order = the JSON array order: `[driving-impact-…healthcare, innovating-spaces-…real-estate, scaling-success-…nonprofit, optimizing-conversions-…startup]`.
- healthcare → [real-estate, nonprofit]
- real-estate → [healthcare, nonprofit]
- nonprofit → [healthcare, real-estate]
- startup → [healthcare, real-estate]

### Fixed vs CMS (verified)
Per-slug changes ONLY: `<title>`/meta description, hero tag/title/description/image, `mainContentHtml`, `inlineImage`, `subContentHtml`, sidebar values (clientName/duration/solution), related cards. Fixed: quote text, sidebar labels (incl. the "Sulution" typo), "Other Case Study" heading, Explore All button, CTA banner, footer, back-to-top.

### Animations summary (detail)
1. Page load: cream section slides up from `y:1070px` (spring 400/60/1.2).
2. Hero image: fade + `scale 2 → 1` with perspective 1200 (spring 250/64/1).
3. Card images (related + list): `scale 1.1 → 1` on appear (spring 250/64, delay 0.1) + hover zoom to 110–115%.
4. Sticky sidebar: `position:sticky; top:60px`.
5. Buttons: dual-label text-swap hover.
6. Back-to-top: fade/rise in after scrolling; anchors to `#hero`.
7. No marquees, no parallax, no scroll-pinning on these pages.
