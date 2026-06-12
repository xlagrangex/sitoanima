# Replica Spec — About Us Page ("Flexio" Framer template)

Source: https://first-effect-565679.framer.app/about-us
Global design system, navigation and footer: see `spec-home.md` §1–§3 (identical on this page).

---

## 1. Section list (in order)

1. Navigation (global, fixed)
2. Hero (orange bg + cream sliding panel + 2 photos + 3 stat cards)
3. About Us / Our story (cream)
4. Video (identical scroll-pinned video as home)
5. "Body" wrapper (z-index 2, 40px rounded top edge sliding over video) containing:
   - Mission / values (cream, 2×2 grid)
   - Team (cream, 4 cards)
   - CTA banner (orange — identical to home §4.11)
   - Footer (global)

Page background: cream `#FEFAF6`.

---

## 2. Hero

- `<section>` bg orange `#FE6037`, overflow hidden.
- Inner "Container" panel: cream `#FEFAF6`, padding **180px 30px 0** (tablet 160/40/0, phone 144/16/0). Page-load animation: **translateY 1070px → 0** spring (damping 60, stiffness 400, mass 1.2) — same curtain reveal as home.
- Content column max-width 1140 (tablet 800), gap 80; heading block left-aligned, gap 60 (tablet 40):
  - Eyebrow (Primary, orange pill, white uppercase): **`About us`**
  - H1 (82/66/48px): **`Everything We Do, Founded on Our Core Values`**
  - Paragraph (18px, max-width ~700): **`Discover our story, principles, and the fervor that prompts us to provide outstanding services for companies akin to yours.`**
- **Image row** (2 columns, gap ~24, stacks on phone):
  - Container 1 (flex 2, aspect-ratio 1.4745, radius 24px, placeholder bg peach `#FAEBDC`): `https://framerusercontent.com/images/zISQJkl1KYD1zgYs1DCNHiN9IGM.png` (1119×765, "Professional Work Discussion")
  - Container 2 (flex 1, aspect-ratio 0.7373, radius 24px, placeholder bg peach): `https://framerusercontent.com/images/wwPGvpGH4OAoQM7wDWZkvyfazLo.png` (546×765, "Group of Young Adults in Smart Casual")
  - **Page-load animation on BOTH images: opacity 0 + scale 2 → opacity 1 + scale 1, spring (damping 64, stiffness 250), with perspective 1200** — dramatic zoom-out entrance.
- **Stats row** below images (3 equal columns, gap 24, ~194px tall, stack on phone). Orange cards `#FE6037`, radius 16px, cream text, count-up in view (number 60px + small description):
  1. **100%** — counts **90 → 100**, suffix `%` — description **`Client Satisfaction`**
  2. **3+** — counts 0 → 3, suffix `+` — description **`Years on Market`**
  3. **$4m** — prefix `$`, counts 0 → 4, suffix `m` — description **`in pure Profits`**

## 3. About Us / Our story

- Cream bg, padding **140px 30px** (tablet 100/40, phone 64/16). Content column max-width ~700-1140, left-aligned, gap 24.
- Title block (gap 12):
  - Eyebrow (Primary orange): **`Our story`**
  - H2: **`Story of Passion, Growth, and Dedication`**
- Two paragraphs side by side in a row (gap 30px; stacks in a column gap 24 on phone), body 16-18px:
  - Paragraph 1: **`Our journey started as a humble undertaking with colossal aspirations, evolving into a flourishing establishment committed to fortifying businesses. From the outset, our mission centered around devising insightful strategies that address actual challenges and facilitate our clients' triumph. During our progression, we've welcomed innovation and remained unwavering in our principles.`**
  - Paragraph 2: **`We've forged enduring partnerships that motivate us constantly. Our path is ignited by enthusiasm, propelled by proficiency, and directed by the conviction that victory is a mutual accomplishment. In unison with our clientele, we've converted obstacles into possibilities, one endeavor at a time.`**
- **Quote card** below: bg `#F9EADB`, radius 8px, padding 24px, full width; H4 quote style (24/20px, weight 500):
  - **`Impressive feats are erected on solid confidence, integrated teamwork, and the unyielding quest for superiority which triggers advancement.`**

## 4. Video (scroll-pinned)

Identical to home §4.6:
- Section height 2280px desktop / 100vh below; pinned background video autoplay/loop/muted cover: `https://framerusercontent.com/assets/dcvnnvkeNKmgN1qxCAM2MNUiZM.mp4`
- Video scale 1.5 → 1 in view; white centered H2 overlay swapping at scroll triggers between:
  - **`Expert advice to streamline operations and accelerate growth.`**
  - **`Tailored to your brand's needs & designed to enhance your business`**
- Followed by "Body" wrapper (z-index 2) opening with the 40px-radius rounded-top cream strip sliding over the video.

## 5. Mission / values

- Cream bg `#FEFAF6`, padding **100px 30px 40px** (tablet 100/40/40, phone 34/16/64). Centered column, max-width 1140, gap 40 (tablet 60).
- Title block centered:
  - Eyebrow (Primary orange): **`our mission`**
  - H2: **`Delivering Trust, Reliability, and Excellence`**
  - Paragraph (centered, max-width ~700): **`Our mission is to provide tailored solutions with a focus on transparency, dependability, and client satisfaction. We strive to build lasting relationships by prioritizing the unique needs of every business we serve.`**
- **Values grid**: desktop/tablet `grid 2×2`, columns `repeat(2, 1fr)`, gap 40, max-width 800; phone 1 column gap 24. Each cell centered column (gap 24):
  - Icon: orange `#FE6037` starburst/splat blob SVG (~140×143) with a white stroked line-icon inside (different glyph per value).
  - H4 title + centered paragraph (16px):
  1. **`Reliability`** — `Count on us for dependable, consistent service, ensuring results that build trust at every step.`
  2. **`Innovation`** — `We craft forward-thinking solutions, keeping your business ahead of evolving challenges.`
  3. **`Integrity`** — `In every action we undertake, openness and sincerity are paramount, guaranteeing moral delivery.`
  4. **`Security`** — `Your data's safety is our top priority, using robust measures to keep it protected.`

## 6. Team

- Cream bg, padding **140px 30px** (tablet 100/40, phone 64/16), max-width 1140.
- Header row: left — eyebrow (Primary orange) **`Meet our team`** + H2 **`Our Dedicated Team`**; right — Primary button **`Join Us`** → `/contact` (moves below the grid on phone).
- **Team grid**: 4 cards in a row (2×2 tablet, 1 col phone). Card structure:
  - Portrait photo (474×606, rounded ~20px) with hover "Overlay".
  - H4 name + role line ("Service Text", 16px) + social icons row: 2 small circular icon links → `https://twitter.com` and `https://Linkedin.com` (the name/role/socials sit in a panel revealed/overlaid on the image; hover darkens with overlay).
  1. **`Alex Turner`** — **`CEO & Founder`** — img `https://framerusercontent.com/images/WOTqOFUP4mjdZ8WDBbfwDPWZqLU.png` ("Man smiling")
  2. **`Sophia Lee`** — **`Chief Marketing Officer`** — img `https://framerusercontent.com/images/CyAZkzNqP6wAbzFd5fOxnqOALk.png` ("Businesswoman smiling")
  3. **`Emma Clark`** — **`Head of Operations`** — img `https://framerusercontent.com/images/XT0Z2yP6Hg99e8ukbPh9p9xkUk.png` ("Young woman smiling at someone")
  4. **`John Miller`** — **`Lead Developer`** — img `https://framerusercontent.com/images/JKVDpXkRnYMMJo8USflSIYDiGs.png` ("African man talking on phone")

## 7. CTA banner

Identical to home §4.11 (orange rounded card):
- Eyebrow (Secondary white pill, orange text): **`Ready to Elevate?`**
- H2 white (H3 phone): **`Proceed Toward Your Next Achievement`**
- Primary dark button **`Let's Talk`** → `/contact` · Secondary **`Learn More`** → `/about-us`
- Bottom decorative shape marquee (left, speed 50) with image `https://framerusercontent.com/images/XHpUkHWKP4ZHwwcTpsF5ImDEY8.png` + abstract circle/arc/pill shapes.

## 8. Footer — global, see spec-home.md §3.

---

## 9. Animation summary (about)

| Element | Animation |
|---|---|
| Hero cream panel | Page-load: translateY 1070px → 0, spring (damping 60, stiffness 400, mass 1.2) |
| Hero images (×2) | Page-load: opacity 0 + scale 2 → 1, spring (damping 64, stiffness 250), perspective 1200 |
| Hero stat counters | Count-up in view: 90→100 (%), 0→3 (+), 0→4 ($…m) |
| Video section | Pinned, video scale 1.5→1, headline swap at scroll triggers, rounded panel slides over |
| Team cards | Hover overlay revealing socials |
| Footer | Scroll parallax reveal (y -100 → 0 / inner y 50 → 0) |
| Buttons | Hover vertical text-swap |

## 10. Page meta
Same as home: title `Flexio`, same description/og:image.
