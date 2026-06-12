# Replica Spec — Home Page ("Flexio" Framer template)

Source: https://first-effect-565679.framer.app/
This file also contains the GLOBAL design system, navigation and footer (shared by all pages).

---

## 1. Global Design System

### Color tokens
| Token | Hex | Usage |
|---|---|---|
| Orange (primary) | `#FE6037` | Hero/Logo section bg, eyebrow pills, primary buttons, stat cards, testimonial cards, CTA banner, logo icon |
| Cream (page bg) | `#FEFAF6` | Page background, hero panel, light text on orange |
| Peach (secondary bg) | `#FAEBDC` | Nav bar bg, Industry section bg, image placeholders |
| Quote bg | `#F9EADB` | About quote card |
| Dark (text) | `#262121` | Default text color, dark CTA button, logo bottom shape |
| White | `#FFFFFF` | Text on orange, eyebrow text, white eyebrow pill |
| Blue | `#3861F9` | Industry tag |
| Teal | `#76C6B3` | Industry tag / CTA shapes |
| Dark blue | `#3758D3` | Industry tag |
| Yellow | `#FFD37D` | Industry tag |
| Pink | `#FFBAB9` | Industry tag |
| Light blue | `#70A2E1` | Industry tag / CTA shapes |
| Light orange | `#FFA37C` | Industry tag |

### Typography (all "Clash Grotesk", fallback sans-serif; weight 500 headings / 400 body)
| Style | Desktop / Tablet / Phone | Line-height | Notes |
|---|---|---|---|
| H1 (hero) | 82px / 66px / 48px | 0.9em | weight 500, color #262121 |
| H2 (section titles) | 60px / 48px / 40px | 1em | weight 500 |
| H3 | 44px / 40px / 36px | 1.2em | weight 500 |
| H4 (card titles) | 32px / 26px | 1.2em | weight 500 |
| Large text / quote | 24px / 20px | 1.2em | weight 500 |
| Body | 18px / 16px | 28px / 26px | weight 400 |
| Body small | 16px | 26px | weight 400 |
| Button text | 18px / 16px | 28px / 26px | weight 500 |
| Eyebrow / tag | 14px | 16px | weight 500, letter-spacing 1px, UPPERCASE, white |

### Breakpoints
- Desktop ≥ 1200px · Tablet 810–1199px · Phone ≤ 809px
- Content max-width: 1140px

### Shared components
**Eyebrow pill ("Primary" variant)**: orange `#FE6037` bg, border-radius 4px, padding ~6px 10px, uppercase white 14px text. Used above every section title.
**Eyebrow pill ("Secondary" variant)**: white `#FFF` bg, radius 4px, ORANGE `#FE6037` uppercase text. Used on orange backgrounds ("Our client's" in logo section, "Ready to Elevate?" in CTA banner).

**Primary button**: 130×50px, pill radius 99px, orange `#FE6037` bg, white text 16-18px. Hover micro-interaction: two stacked text copies (Text 1 visible, Text 2 hidden at translate(-50%,-50%) opacity 0) — on hover text 1 slides up/out and text 2 slides in (vertical text-swap). Dark variant: `#262121` bg + white text (used inside orange CTA banner).
**Secondary button**: same geometry, cream `#FEFAF6` bg, 1px solid orange border, ORANGE text, same hover text-swap.

**Stat counter component** (used in "Numbers" sections): card with orange bg `#FE6037`, radius 16px, centered cream `#FEFAF6` content: big number 60px Clash Grotesk 500 with optional prefix/suffix, optional word (e.g. "Years") below at h2/h4 size, then small description. Number counts up when scrolled into view (count-up animation from a start value to an end value).

---

## 2. Global Navigation (all pages)

- `position: fixed; top: 0`, full width, outer padding 20px 24px 0 (phone: 16px 16px 0), z-index 5.
- Bar: full-width rounded pill, **border-radius 32px**, bg peach `#FAEBDC`, 1px solid cream `#FEFAF6` border. Inner padding ~12-16px 24px. Subtle slide-down/fade on load (logo fades in y+20 → 0, duration 0.4s, delay ~2.2s after the hero curtain animation).
- Layout: logo left · links center-left · CTA right.

### Logo (SVG, viewBox 134×40)
Icon (40×40): two stacked half-circles — top half: orange `#FE6037` dome (semicircle, flat side down), bottom half: dark `#262121` dome. Wordmark: "flexio" lowercase, dark `#262121`, geometric sans.

### Menu items (desktop/tablet)
1. **Services** — dropdown trigger (label + chevron icon). Dropdown content = 3 service cards (image with dark overlay + white card title), linking to:
   - Business Consulting → `/services/business-consulting` (img `https://framerusercontent.com/images/R8gxZoLunVxfFAUNkxMZhYBWg.png`)
   - Growth Strategy → `/services/growth-strategy` (img `https://framerusercontent.com/images/b9ELbfDT5kcphXutNcyv2CLCDSQ.png`)
   - Marketing Strategy → `/services/marketing-strategy` (img `https://framerusercontent.com/images/DSM43FoamJ3WOY4EAFxMEVGeKTY.png`)
2. **About Us** → `/about-us`
3. **Case Studies** → `/case-studies`
4. CTA button (Primary, orange): **"Let's Talk"** → `/contact`

### Mobile nav (≤809px)
Hamburger (2 lines, "Top"/"Bottom") toggles a panel containing: Home → `/`, "Service" accordion (opens the same 3 service cards, images scale 1.1→1 spring on open), About Us → `/about-us`, Case Study → `/case-studies`, Contact → `/contact`, plus full-width "Let's Talk" Primary button → `/contact`.

---

## 3. Global Footer (all pages)

Background cream `#FEFAF6`. Scroll reveal: footer container parallax (outer translateY(-100px) → 0, inner content translateY(50px) → 0 while scrolling into view). Max-width 1140, inner gap 50px.

### Row 1 — two zones
**Left (Contact Info)**
- Label: `Email Us` (16-18px, weight 500)
- Email as H3: **`Hello@flexio.co`** → link `mailto:someone@yoursite.com`
- Copyright line: `© 2024  Design & Developed ` + link **`Amani`** → `https://x.com/hello_amani`
- Link: `Privacy Policy` → `/privacy-policy`

**Right (3 link columns)**
| Page | Social | Studio |
|---|---|---|
| Home → `/` | Instagram → `https://Instagram.com` | `201M Suite, N Broad St 651, Middletown, Delaware, USA` (plain text) |
| Service → `/services/business-consulting` | Linkedin → `https://Linkedin.com` | |
| About Us → `/about-us` | Twitter → `https://twitter.com` | |
| Case Study → `/case-studies` | Facebook → `https://facebook.com` | |
| Contact → `/contact` | | |
| 404 → `/404` | | |

### Row 2
Giant **"flexio" wordmark SVG** (viewBox 1155×223) spanning ~full content width at the very bottom, dark `#262121`.

> NOTE: the live site also renders a "Promo Card (Delete This)" floating widget (Fynest template ad) and the "Made in Framer" badge — DO NOT replicate these.

---

## 4. Home — Section list (in order)

1. Navigation (fixed)
2. Hero (orange bg + cream sliding panel + photo marquee)
3. Logo ticker (orange bg)
4. Problem (cream, text + image)
5. Solution / Services (cream, 3 cards)
6. Process (cream, title+CTA left, 4 steps right)
7. Video (full-bleed scroll video, z-index 1)
8. "Body" wrapper (z-index 2, rounded 40px top edge sliding over the video) containing:
   - Numbers / stats (cream)
   - Industry tags (peach, physics animation)
   - Testimonials (cream, ticker)
   - Case Study (cream)
   - CTA banner (orange card)
   - Footer

Page background: cream `#FEFAF6`.

---

### 4.1 Hero

- `<section>` bg orange `#FE6037`, overflow hidden, padding 0.
- Inner "Main" panel: bg **cream `#FEFAF6`**, padding **180px 30px 120px** (tablet 160/40/100, phone 144/16/64). On page load the whole panel **slides up from translateY(1070px) → 0** (spring: damping 60, stiffness 400, mass 1.2) revealing the orange behind it — curtain effect.
- Content column max-width 1140, gap 80 (tablet 60, phone 40). Heading block left-aligned on desktop (centered on phone), gap 32/24:
  - Eyebrow (Primary, orange pill): **`business & solution`**
  - H1 (82px, max-width 800, left): **`Flexible Solutions for Modern Business`**
  - Paragraph (18px, max-width 700): **`Delivering bespoke, outcome-focused solutions that enhance workflows, augment productivity and expedite corporate expansion.`**
  - CTA row (gap 16):
    - Primary button **`Let's Talk`** → `/contact`
    - Secondary button **`Learn More`** → `https://www.framer.com?via=amani_design` (in the replica point it to `/about-us` or keep external)
- Below: **photo marquee/ticker** (full width, leftward, gap 10px, speed 40 desktop / 20 mobile, slows to 0.7× on hover). On desktop the strip is ALSO scroll-linked: translateX 0 → -3500px tied to page scroll (it accelerates left while scrolling down). Images (rounded corners ~16-20px, mixed widths, height ~375-533px), loop order:
  1. `https://framerusercontent.com/images/Xup44lT05fI7ULikDpDUcdet5M.png` (687×533, "Man and woman discussing project")
  2. `https://framerusercontent.com/images/3TWrH1LgWz28bycoINqeqegsxw4.png` (533×533, "Man and woman discussing books")
  3. `https://framerusercontent.com/images/eWSFN4vEE5WYVdCDBVEtEV2oX0.png` (461×514, "African man smiling on phone")
  4. `https://framerusercontent.com/images/muPZCrML4kWDHgdZz284LoRPNY.png` (533×533, "Girl smiling with coffee mug")
  5. `https://framerusercontent.com/images/GADo4cDx3P81nDG7pb7yA6hjkzw.png` (426×533, "Man browsing on tablet")

### 4.2 Logo ticker

- `<section>` bg orange `#FE6037`, padding 80px 30px (tablet 60/40, phone 64/16), centered column.
- Eyebrow (Secondary, WHITE pill, orange text): **`Our client's`**
- H3 (44px, WHITE): **`Trusted by High-Growth Startups Across Industries`**
- **Logo marquee**: leftward, gap 60px, speed 30, total strip ~3200px wide. Items alternate: white abstract client logomark SVG → thin vertical divider (1px, white, opacity 0.3) → next logo... 6-7 distinct white "logoipsum-style" logomarks (inline SVGs in the original; use any 6 white placeholder logos ~120-160px wide, ~32-40px tall).

### 4.3 Problem

- Cream bg (page), padding **140px 30px 0** (tablet 100/40/0, phone 64/16/0). Two columns (text left, image right; stacks on phone).
- Left column:
  - Eyebrow (orange): **`Facing Challenges`**
  - H2: **`Overcoming These Key Barriers Starts Here Today`**
  - Paragraph: **`Identify the barriers that prevent your business from reaching its full potential. Addressing these issues can transform your path to success.`**
  - Features list (3 items, each with a "verify"/checkmark icon + bold lead + normal text):
    1. **`Inefficient Processes –`** `Wasting valuable time and resources.`
    2. **`Inconsistent Growth –`** `Struggling to reach new milestones.`
    3. **`Limited Support –`** `Wasting valuable time and resources.`
- Right column:
  - Photo: `https://framerusercontent.com/images/sUrLpU40X4baNonFR9IhFAxsYk8.png` (692×801, "girl using laptop and drinking coffee"), rounded ~24px. **Scroll-in zoom: scale 1.15 → 1 (spring damping 64, stiffness 250) when entering viewport.**
  - Floating sticker overlapping the photo: `https://framerusercontent.com/images/WfQGykkqhhzNFSv5xGnuq4m0P8Q.png` (177×182, "Tag of problem statement" — round badge graphic).

### 4.4 Solution / Services

- Cream bg, padding **140px 30px 0** (tablet 100/40/0, phone 64/16/0), centered.
- Eyebrow (orange): **`our service`** · H2 centered: **`Our Expert Services to Drive Growth`**
- 3 service cards in a row (stack on phone). Card = `<a>`, border-radius **20px**, full-bleed photo with gradient overlays ("Top Overlay" + bottom "Overlay"); top row inside card: white H4 title + frosted circular "+" button (bg rgba(255,255,255,0.25), pill); description appears on hover (card has hover variant revealing the "Service Text" paragraph; the "+" rotates/turns into close):
  1. **Business Consulting** → `/services/business-consulting` — img `https://framerusercontent.com/images/R8gxZoLunVxfFAUNkxMZhYBWg.png` (1710×1071) — hover text: **`Expert advice to streamline operations and accelerate growth.`**
  2. **Growth Strategy** → `/services/growth-strategy` — img `https://framerusercontent.com/images/b9ELbfDT5kcphXutNcyv2CLCDSQ.png` (1710×1107) — hover text: **`Design strategies to scale your business and seize new opportunities.`**
  3. **Marketing Strategy** → `/services/marketing-strategy` — img `https://framerusercontent.com/images/DSM43FoamJ3WOY4EAFxMEVGeKTY.png` (1710×1140) — hover text: **`Create data-driven strategies to boost brand visibility and engagement.`**

### 4.5 Process

- Cream bg, padding **140px 30px** (tablet 100/40, phone 64/16). Two columns: text/CTA left (sticky feel), step list right; stacks on phone.
- Left:
  - Eyebrow (orange): **`Our Approach`**
  - H2: **`A Streamlined Process for Lasting Results`**
  - CTA row: Primary **`Let's Talk`** → `/contact` · Secondary **`Learn More`** → `/about-us`
- Right ("Card" column) — 4 steps, each row: circular **step number** badge (orange circle with white number) + H4 title + paragraph; separated by dividers:
  1. **`Discovery & Strategy`** — `We start by understanding your goals and creating a clear, actionable plan tailored to your needs.`
  2. **`Design & Planning`** — `We develop a detailed roadmap to ensure smooth, efficient execution at every stage.`
  3. **`Implementation`** — `Our team brings the plan to life, focusing on precision and alignment with your vision.`
  4. **`Optimization & Growth`** — `We track outcomes, refine strategies and support ongoing growth for lasting impact.`

### 4.6 Video (scroll-pinned)

- `<section>` height **2280px** desktop (100vh tablet/phone), z-index 1; the video block is sticky/pinned while the section scrolls.
- Background video, autoplay/loop/muted, object-fit cover: `https://framerusercontent.com/assets/dcvnnvkeNKmgN1qxCAM2MNUiZM.mp4` (alt source: `https://framerusercontent.com/assets/MLWPbW1dUQawJLhhun3dBwpgJak.mp4`).
- **Animations**: video scales **1.5 → 1** (spring damping 60, stiffness 400) when entering view. Big white centered H2 overlay; two invisible scroll triggers (at ~50% thresholds) swap the component variant so the headline changes while scrolling:
  - State A headline: **`Expert advice to streamline operations and accelerate growth.`**
  - State B headline: **`Tailored to your brand's needs & designed to enhance your business`**
- The next "Body" wrapper (z-index 2, cream, with a 40px-radius rounded top strip — "Radious" element, height 40px, radius 40px top corners; 24px/30px on phone) slides up OVER the pinned video.

### 4.7 Numbers / Stats

- Cream bg, padding **100px 30px 140px** (tablet 60/40/100, phone 44/16/64), centered.
- Eyebrow (orange): **`Who we are`** · H2 centered: **`Transforming Businesses with Expertise`**
- 3 equal columns (gap 24; stack on phone), each an orange stat card (radius 16px, cream text, centered, ~194px tall). Count-up on scroll into view:
  1. **3+** big number, word **`Years`**, description **`Proven Experience`** (counts 0 → 3, suffix "+")
  2. **20+**, word **`Clients`**, description **`Trusted Partnerships`** (counts 0 → 20, suffix "+")
  3. **50+**, no word, description **`Project Impactful Results`** (counts 30 → 50, suffix "+")
  - Number: 60px Clash Grotesk 500 cream.

### 4.8 Industry tags (physics)

- Peach bg `#FAEBDC`, padding **64px 30px** desktop (100/40 tablet, 64/16 phone).
- Title centered: eyebrow (orange) **`Industries We Serve`** · H2: **`Industry-Specific Expertise to Drive Your Success`**
- **Desktop (≥1200px)**: pill tags **drop with gravity physics** (matter.js-style) into the section container when scrolled into view, piling at the bottom (interactive/draggable feel). Tags (pill radius 999px, 16px text, white text on saturated colors / dark text on light colors):
  | Label | BG |
  |---|---|
  | Startup | `#3861F9` (blue, white text) |
  | Marketing Agency | `#FE6037` (orange, white text) |
  | Small Business | `#76C6B3` (teal, dark text) |
  | Tech Company | `#FFD37D` (yellow, dark text) |
  | Corporate | `#3758D3` (dark blue, white text) |
  | Nonprofit | `#FFBAB9` (pink, dark text) |
  | E-commerce Store | `#70A2E1` (light blue, dark/white text) |
  | Consulting Firm | `#FFA37C` (light orange, dark text) |
- **Tablet/phone fallback**: static centered wrap of the same pills, list: Startup, Small Business, Entrepreneur, Tech Company, Corporate, Nonprofit, E-commerce Store, Consulting Firm (note: "Entrepreneur" replaces "Marketing Agency" in the static variant).

### 4.9 Testimonials

- Cream bg, padding **140px 30px 0** (tablet 100/40/0, phone 64/16/0).
- Eyebrow (orange): **`Our client's`** · H2 centered: **`Relied on by Companies Globally`**
- **Testimonial marquee**: leftward; desktop gap 24px speed 40, card height ~280px; phone gap 30 speed 30 height ~375; **hoverFactor 0.2** (slows strongly on hover). 4 cards looped.
- Card: orange `#FE6037` bg, radius 24px, horizontal layout — left: quote icon (cream quote-mark SVG ~60×62) + quote text (cream) + author line; right: portrait photo (527×647, rounded).
  1. `"Their professionalism and insight were unparalleled. They provided us with a solution that not only solved our challenges but elevated our vision beyond what we imagined."` — **`– Emma C., Nonprofit Director`** — img `https://framerusercontent.com/images/P0TLTuFqzStOtgDppzxqwrSaEVc.png`
  2. `"The team's expertise turned our vague ideas into a comprehensive strategy. The project exceeded expectations, showcasing their commitment to excellence and innovation."` — **`– John M., Tech Lead`** — img `https://framerusercontent.com/images/j0jUAy0zXi2FNhuEc2HKNw4MhfU.png`
  3. `"Working with them was a game-changer for our agency. They delivered innovative solutions tailored to our needs, resulting in noticeable growth and a stronger online presence."` — **`– Sophia L., Agency Owner`** — img `https://framerusercontent.com/images/8Qamiprljd6whc05RWjpupi8fY.png`
  4. `"Their creativity and dedication were evident from day one. The final design not only looked stunning but also aligned perfectly with our brand values. A fantastic experience from start to finish!"` — **`– Alex T., Startup Founder`** — img `https://framerusercontent.com/images/Es96vC5U35Q8bITPI8n4QUuyw2k.png`

### 4.10 Case Study

- Cream bg, padding **140px 30px** (tablet 100/40, phone 64/16).
- Header row: left — eyebrow (orange) **`case study`** + H2 **`Client Success Through Our Solutions`**; right — Secondary button **`Explore All`** → `/case-studies` (on phone the button moves below the cards).
- 2 cards side by side (stack on phone). Card = `<a>`: image top (radius ~20px), then text block: eyebrow pill (orange) + H4 title:
  1. Tag **`Healthcare`** — H4 **`Making an Impact: Transforming a Healthcare Platform's Rebuild`** → `/case-studies/driving-impact-a-healthcare-platform-s-rebuild` — img `https://framerusercontent.com/images/xlBTpOvQYYNgtUnLjqhkg5cATI.png` (1500×851, "people discussing")
  2. Tag **`Startup`** — H4 **`Optimizing Conversions: A Tech Startup's Leap`** → `/case-studies/optimizing-conversions-a-tech-startup-s-leap` — img `https://framerusercontent.com/images/iFzZR1MGz8uwU4wkAqwwD9QQUDU.png` (1500×750, "team using laptop")

### 4.11 CTA banner ("Ready to Elevate?")

- Wrapper padding **0 30px** on cream; inner Container = **orange `#FE6037` card** (rounded ~24px) with generous vertical padding; below it a 40px "Radious" spacer (30px phone).
- Content centered:
  - Eyebrow (Secondary, WHITE pill, orange text): **`Ready to Elevate?`**
  - H2 (white, desktop; H3 phone): **`Proceed Toward Your Next Achievement`**
  - CTA row: Primary DARK button (`#262121` bg, white text) **`Let's Talk`** → `/contact` · Secondary (cream bg, orange border/text) **`Learn More`** → `/about-us`
- Bottom of banner: **decorative shape marquee**, leftward, speed 50: sequence of abstract flat shapes ("01/02/03/04 Elements": full circle, two quarter-arcs, pill/column shapes — in teal `#76C6B3`, light blue `#70A2E1`, cream, dark `#262121`) interleaved with a photo card with overlay: `https://framerusercontent.com/images/XHpUkHWKP4ZHwwcTpsF5ImDEY8.png` (911×451, "business people discussing about business"). Shapes ~200-300px tall.

### 4.12 Footer — see §3 (global).

---

## 5. Animation summary (home)

| Element | Animation |
|---|---|
| Hero cream panel | Page-load: translateY 1070px → 0, spring (damping 60, stiffness 400, mass 1.2) — curtain reveal over orange |
| Nav logo | Page-load: fade + y20 → 0, tween 0.4s, delay 2.2s |
| Hero photo strip | Marquee left (speed 40 desktop / 20 mobile, gap 10, hover slows ×0.7) + desktop scroll-linked translateX 0 → -3500px |
| Logo ticker | Marquee left, speed 30, gap 60 |
| Problem image | In-view: scale 1.15 → 1 spring (damping 64, stiffness 250) |
| Service cards | Hover: overlay darkens, description slides in, "+" button animates |
| Video section | Pinned; video scale 1.5 → 1 in-view; headline swaps between 2 lines at scroll triggers; content panel with 40px rounded top slides over it |
| Stat counters | Count-up on in-view (0→3, 0→20, 30→50) |
| Industry tags | Desktop: gravity-drop physics (matter.js) on in-view; static wrap otherwise |
| Testimonial strip | Marquee left, speed 40/30, hover slows to 0.2× |
| CTA shapes strip | Marquee left, speed 50 |
| Footer | Scroll parallax reveal (outer y -100 → 0, inner y 50 → 0) |
| Buttons | Hover vertical text-swap (two stacked labels) |

## 6. Page meta
- `<title>`: `Flexio`
- meta description: `Flexio is a sleek Framer template for service-based businesses. Ideal for marketing agencies, business consultants, growth experts...`
- og:image: `https://framerusercontent.com/images/VEohya2wX3vlba21WjmLwpybww.png`
