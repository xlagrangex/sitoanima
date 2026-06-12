# Spec — Service Detail Template (`/services/[slug]`)

Source: https://first-effect-565679.framer.app/services/business-consulting (verified identical template on `/services/growth-strategy` and `/services/marketing-strategy` — only CMS fields change: title, description, hero image, related-cards content, meta).

CMS fields per service live in `docs/replica/cms-services.json` (slug, title, description, image, mainContentHtml, inlineImage, subContentHtml). Everything below describes the FIXED template around them.

---

## Design tokens (site-wide, defined on `body`)

| Token | Value | Use |
|---|---|---|
| Black | `#262121` | default text color |
| White | `#fff` | text on dark/orange |
| Cream | `#fefaf6` | page/section background |
| Peach | `#faebdc` | nav pill bg, image placeholder bg, quote/CTA card bg is `#f9eadb` (slightly different, see below) |
| Orange | `#fe6037` | accent / primary buttons / hero outer bg / banner bg |
| Blue | `#3861f9` | decorative shape |
| Teal | `#76c6b3` | (palette, unused on these pages) |
| Near-black | `#0d0d0d` | (palette) |
| Light gray | `#f1f1ef` | (palette) |
| Card/quote bg | `#f9eadb` | quote block + sticky CTA card (hardcoded, not a token) |

Fonts (self-hosted/fontshare):
- **Clash Grotesk** (weights 400/500/700) — ALL headings, body copy, buttons, labels. This is the main typeface.
- **Cabinet Grotesk** (400) — form inputs only (contact page).
- Inter loaded but effectively unused in visible text.

Typography presets (desktop / tablet / mobile where they differ):
- **Display H1 style** (`13d02to`, used on other pages): Clash Grotesk 500, 82/66/48px, line-height .9em.
- **Section title** (`1cqcawc`): Clash Grotesk 500, **60px desktop / 48px / 40px mobile**, line-height 1em, color #262121. Used for hero title + "Check Other Services" + banner title.
- **H3 in article** (`1j87fkn`): Clash Grotesk 500, 40/36/44px(variant order: desktop 40, others 36/44 by breakpoint), line-height 1.2em.
- **Card title** (`ga5uq`): Clash Grotesk 500, 24px (20px mobile), lh 1.2em.
- **CTA card heading** (`f4bszl`): Clash Grotesk 500, 32px (26px mobile), lh 1.2em.
- **Lead/description** (`1044c9w`): Clash Grotesk 500, 18px lh 28px (16px/26px mobile).
- **Body text** (`1e7rr0f`): Clash Grotesk 400, 16px, lh 26px.
- **Card description** (`11gaimf`): Clash Grotesk 400, 18px lh 28px (16px/26px).
- **Button label** (`amoww1`): Clash Grotesk 500, 16px lh 26px.
- **Eyebrow badge** (`1u1yh6c`): Clash Grotesk 500, 14px, lh 16px, letter-spacing 1px, UPPERCASE, white.

Breakpoints: desktop ≥1200px, tablet 810–1199.98px, phone ≤809.98px.

---

## Shared components

### Navigation (fixed, all pages)
- `position:fixed; top:0; left/right:0; z-index:5; padding:20px 24px 0` (mobile `16px 16px 0`); content centered.
- Pill bar: width 1140px, height 64px, padding `6px 6px 6px 24px`, bg `#faebdc`, border 1px solid `#fefaf6`, border-radius **32px**.
- Layout row: [Logo 134x40 (inline SVG "Flexio" wordmark, fills white/#FE6037/#262121, viewBox 0 0 134 40)] — gap 40px — links — right CTA.
- Links (Clash Grotesk): **"Services"** (dropdown trigger with chevron-down icon; on click/hover opens panel listing the 3 services — on mobile menu it renders as image cards, see below), **"About Us"** → `/about-us`, **"Case Studies"** → `/case-studies`.
- CTA: Primary button **"Let's Talk"** → `/contact` (140x50 in nav).
- Mobile nav: logo + hamburger (two bars "Top"/"Bottom"); full menu panel contains: "Home" link, "Service" accordion that expands to 3 **image cards** (each = service image, white card title bottom-left, dark gradient overlay, links to `/services/<slug>`; entrance animation scale 1.1→1, spring damping 64 stiffness 250 delay 0.1), then links About Us / Case Study / Contact, then Primary "Let's Talk" button.

### Primary button (used everywhere)
- Pill (radius 99px), bg `#fe6037`, size ~130x50, label Clash Grotesk 500 16px white.
- Hover microinteraction: two stacked copies of the label ("Text 1" visible, "Text 2" hidden); on hover the visible one slides up/out and the duplicate slides in (classic Framer text-swap). Replicate with overflow-hidden 24px-high wrapper + translateY transition.

### Secondary button (banner only)
- Pill radius 99px, bg `#fefaf6`, border 1px solid `#fff`, label orange `#fe6037`, same size + text-swap hover. Label "Learn More" → `/about-us`.

### Eyebrow badge
- Small rounded rect (radius 4px), padding ~6px 10px, UPPERCASE 14px/1px tracking.
- "Primary" variant: bg `#fe6037`, white text (hero: "our service").
- "Secondary" variant: bg `#fff`, orange text (banner: "Ready to Elevate?").

### Back-to-top (service pages)
- Fixed bottom-right (padding 32px 32px 68px desktop; 24px mobile), white circle 64x64, radius 80px, shadow `0 2px 4px rgba(0,0,0,.1)`, arrow-up icon. Hidden initially (`opacity:0; translateY(48px)`), fades/slides in after scrolling. Links to `#hero`.

### Pre-footer CTA banner ("Proceed Toward...") — shared section, also on contact
- Full-width `<section>`, bg **`#fe6037`**, inner padding-top 120px (64px mobile), content max-width 1140px, column, gap 60px.
- Content: Secondary badge **"Ready to Elevate?"** → title (preset `1cqcawc`, **white** override) **"Proceed Toward Your Next Achievement"** (centered, max-width 700px) → CTA row: Primary **"Let's Talk"** (→ `/contact`) + Secondary **"Learn More"** (→ `/about-us`).
- Below: decorative collage row, all tiles 300px tall, overflow hidden:
  - **01 Elements**: SVG half-circle/leaf shape, fill `#262121` (300x300, two quarter-circle paths).
  - **02 Elements**: SVG 301x301 — top arc fill `#3861F9`, bottom half-circle fill `#FFD37D`.
  - **Image with Overlay**: photo `https://framerusercontent.com/images/XHpUkHWKP4ZHwwcTpsF5ImDEY8.png` (alt "business people discussing about business"), 606x300, aspect-ratio 2.02.
  - **04 Elements**: SVG 309x300 — three vertical bars (each 85.4px wide, gaps ~26px), fill `#262121`.
  - (Mobile variant adds "03 Elements" tiles and reorders.)
- **"Radious" strip**: 40px-tall (30px mobile) strip at the section bottom that draws the cream `#fefaf6` footer top corners as a rounded cut-out over the orange (rounded-corner transition between banner and footer).

### Footer (shared) — bg `#fefaf6`
Inner max-width 1140px. Top row "Footer Container":
- Left "Contact Info": label **"Email Us"** (18px/500) + link **"Hello@flexio.co"** (`mailto:someone@yoursite.com`) ; below: **"© 2024  Design & Developed "** + link **"Amani"** (`https://x.com/hello_amani`) + link **"Privacy Policy"** (`/privacy-policy`).
- Column **"Page"**: Home `/`, Service `/services/business-consulting`, About Us `/about-us`, Case Study `/case-studies`, Contact `/contact`, 404 `/404`.
- Column **"Social"**: Instagram `https://Instagram.com`, Linkedin `https://Linkedin.com`, Twitter `https://twitter.com`, Facebook `https://facebook.com`.
- Column **"Studio"**: text "201M Suite, N Broad St 651, Middletown, Delaware, USA".
- Bottom: **giant full-width "Flexio" wordmark SVG** (viewBox 0 0 1155 223, black fill) spanning the footer width.

---

## Page structure — Service detail (in order)

`<title>{Title} - Flexio</title>`; meta description = CMS `description`.

### 1. Hero (`id`/anchor `hero`)
- Outer `<section>` bg **`#fe6037`** (orange), overflow hidden.
- Inner Container: bg **`#fefaf6`**, padding `180px 30px 100px` (tablet `160px 40px 100px`, mobile `144px 16px 64px`), full width — the cream panel IS the visible hero; the orange only shows during the entrance animation.
- **Entrance animation (page load)**: the entire cream Container starts at `translateY(1070px)` and springs to 0 — spring `{ damping:60, stiffness:400, mass:1.2, delay:0 }`. (The orange section bg is revealed behind it while it slides up.)
- Content column (max-width 1140px desktop / 800px tablet / 500px mobile, gap 40px, centered):
  1. Eyebrow badge (orange/Primary): **"our service"** (uppercase, fixed text — same on all 3 slugs).
  2. `<h2>` — CMS **title** (preset `1cqcawc` 60px, centered, max-width 700px).
  3. `<p>` — CMS **description** (18px/28px, centered, max-width 700px).
  4. **Hero image** — CMS `image`. Wrapper: aspect-ratio **2.07273**, border-radius **24px** (20px tablet / 16px mobile), bg `#faebdc`, full content width (≤1140px), object-fit cover.
     - **Image entrance**: starts `opacity:0; perspective(1200px) scale(2)` → animates to scale 1 opacity 1, spring `{ damping:64, stiffness:250, mass:1 }` (all breakpoints).

### 2. Body section — bg cream `#fefaf6`
Container max-width 1140px, column, gap 100px (80 tablet / 64 mobile). Two children:

**2a. Blog Container** — `display:flex; flex-direction:row; gap:100px` (40 tablet; column + 60 gap mobile):

- **Left: article column** (`flex:2`), column gap 40px:
  1. **Main-Content** — render CMS `mainContentHtml` as rich text. h3 → preset `1j87fkn` (40px/500), p → `1e7rr0f` (16px/26px/400), ul/li bulleted, paragraph spacing.
  2. **Quote block** (FIXED, identical on all slugs): bg **`#f9eadb`**, border-radius 8px, padding 24px, contains one `<p>` 18px/28px Clash Grotesk 500:
     > "Effective marketing is not just about reaching people—it's about connecting with them in meaningful ways."
  3. **Inline image** — CMS `inlineImage`, full column width, aspect ~1.5 (source 1071x714), rounded corners (inherit ~8–12px), alt "Group of people using laptop".
  4. **Sub-content** — render CMS `subContentHtml` (h3 + p, same presets as Main-Content).

- **Right: sticky CTA card** (`flex:1`), **`position:sticky; top:60px`**, bg **`#f9eadb`**, border-radius **16px**, padding 30px, column gap 40px (FIXED text, identical all slugs):
  - `<h4>` preset `f4bszl` (32px/500): **"Transform Your Business Today"**
  - `<p>` 16px/26px: **"Partner with us to achieve your business goals and unlock your full potential. Start your journey toward success today!"**
  - Primary button **"Let's Talk"** → `/contact` (with text-swap hover).
  - Decorative **Shape** SVG below the button: container aspect-ratio 1.09, w 100%; SVG viewBox `0 0 300 300`, two stacked half-circles — bottom-half-circle fill `#FE6037` (y 150–300) and top-half-circle fill `#262121` (y 0–150):
    ```svg
    <svg viewBox="0 0 300 300" fill="none">
      <path d="M0 300C0 260.2 15.8 222.07 43.93 193.93C72.06 165.8 110.22 150 150 150C189.78 150 227.94 165.8 256.07 193.93C284.2 222.07 300 260.22 300 300H0Z" fill="#FE6037"/>
      <path d="M0 150C0 110.22 15.8 72.06 43.93 43.93C72.06 15.8 110.22 0 150 0C189.78 0 227.94 15.8 256.07 43.93C284.2 72.06 300 110.22 300 150H0Z" fill="#262121"/>
    </svg>
    ```

**2b. "Check Other Services"** — column, gap 40px (60 tablet / 24 mobile), centered:
  - `<h2>` preset `1cqcawc`, centered: **"Check Other Services"**
  - Cards row: gap 24px, each card `flex:1; height:480px` (column stack on mobile, full width). Shows the **other 2 services** (CMS: all services minus current slug), each card:
    - `<a href="/services/{slug}">`, border-radius **20px**, overflow hidden, relative.
    - Background image = CMS `image` (cover; image layer rendered at 115% scale: `width:115%; height:115%; top:-7.5%; left:-7.5%` with `perspective(1200px) scale(1.15)` initial — hover/scroll settles toward scale 1; replicate as image zoom-on-hover).
    - Top row (z-3): `<h4>` CMS **title**, white, 24px/500 — and a **plus button**: circle, bg `rgba(255,255,255,0.25)`, radius 999px, white plus icon (18x18 SVG, thin cross path), aria-label "view more button".
    - Bottom (z-3): `<p>` CMS **description**, white, 18px/28px.
    - Two gradient overlays: "Top Overlay" = `linear-gradient(180deg, rgba(38,33,33,0) 0%, rgba(38,32,32,1) 100%)` rotated 180deg (darkens top), and "Overlay" = same gradient un-rotated (darkens bottom).

### 3. Pre-footer CTA banner — see shared section above.

### 4. Footer — see shared section above.

### 5. Back-to-top button — see shared component above (href `#hero`).

---

## Per-slug differences (verified by diffing all 3 slugs)
ONLY these change: `<title>`/og:title/meta description, hero h2 + description + hero image, the rich-text CMS fields, and the 2 related cards (always "the other two", order: the remaining items in CMS order). Eyebrow "our service", quote, sticky CTA card, "Check Other Services" heading, banner and footer are byte-identical fixed template.

## Animations summary
1. Hero cream container: slide-up from `y:1070px`, spring damping 60 / stiffness 400 / mass 1.2, on load.
2. Hero image: `scale 2 → 1` with `perspective(1200px)` + fade `0 → 1`, spring damping 64 / stiffness 250.
3. Mobile-menu service cards: `scale 1.1 → 1`, spring damping 64 / stiffness 250, delay 0.1s.
4. Buttons: text-swap on hover (two stacked labels, translateY in overflow-hidden wrapper).
5. Related cards: image at scale 1.15 with zoom interaction; plus-button affordance.
6. Sticky CTA card: `position:sticky; top:60px` while scrolling the article.
7. Back-to-top: appears (fade + rise from 48px) after scroll.
8. No marquees/tickers/keyframes on this template.

## Asset URLs (template-fixed)
- Banner collage photo: `https://framerusercontent.com/images/XHpUkHWKP4ZHwwcTpsF5ImDEY8.png` (911x451 source)
- OG image (business-consulting): `https://framerusercontent.com/images/VEohya2wX3vlba21WjmLwpybww.png`
- Logo: inline SVG wordmark (nav 134x40; footer giant version viewBox 0 0 1155 223). Recreate or trace from site.
- All CMS images: see `cms-services.json`.
- Fonts: Clash Grotesk + Cabinet Grotesk via Fontshare; Inter via framerusercontent (use Fontshare/Google equivalents in Astro).
