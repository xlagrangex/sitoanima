# Spec — Privacy Policy (`/privacy-policy`) + 404 page

Sources:
- https://first-effect-565679.framer.app/privacy-policy
- https://first-effect-565679.framer.app/xyz-not-found (server returns **HTTP 404** and serves the 404 page; the same page also exists as route `/404`, linked from the footer "Page" column)

Shared components (Navigation, buttons, eyebrow badge, CTA banner, Footer, tokens/typography presets) are documented in `spec-services.md`.

---

## PRIVACY POLICY — `/privacy-policy`

`<title>Flexio</title>` (generic, not "Privacy Policy - Flexio"); meta description = generic site description.

### Section order
1. Navigation (shared, fixed)
2. `<main>` — single content section, bg cream `#fefaf6`, padding `180px 30px 140px` (tablet `160px 40px 100px`, phone `144px 16px 64px`)
3. Pre-footer CTA banner (shared)
4. Footer (shared)

**No page-load slide-up animation on this page** (the appear-animations payload contains only the mobile-nav card scale effect). Content renders statically. No back-to-top button.

### Layout
Container: max-width **1140px** (phone 500px), column, centered, **gap 100px** (40px on smaller breakpoints) between the title block and the content list.

**1. Title block ("Top")** — column, centered, gap 32px (24px mobile):
- Eyebrow badge, Primary variant (orange `#fe6037`, radius 4px, padding 4px 8px, uppercase white 14px/1px tracking): **"Privacy Policy"**
- `<h2>` preset `1cqcawc` (Clash Grotesk 500, 60/48/40px, lh 1em, `#262121`), centered: **"Privacy Policy"**
- `<p>` preset `1e7rr0f` (Clash Grotesk 400, 16px, lh 26px), centered: **"Last Updated: November 15, 2024"**

**2. Content list ("Container")** — column, **gap 60px**; five "Content" blocks, each column **gap 24px**, full width. Headings are `<h4>` preset `f4bszl` (Clash Grotesk 500, 32px desktop / 26px mobile, lh 1.2em, color `#262121`); body `<p>` preset `1e7rr0f` (400, 16px, lh 26px). Text is left-aligned (no center override on content blocks).

### FULL TEXT VERBATIM

**h4: Information We Collect**

> We collect data to ensure seamless interactions and personalized experiences. When you use our services, we may collect personal details such as your name, email, and phone number. Additionally, we track your interactions with our website, including the pages you visit, the device you use, and the time spent on specific sections. Cookies are also used to store preferences and enhance functionality. All data collected is handled responsibly to maintain transparency and build trust.

**h4: How We Use Your Data**

> The information you share with us is used to improve and personalize your experience. It helps us communicate effectively, optimize our services, and understand user preferences. Whether we’re tailoring recommendations, sending updates, or improving website functionality, your data remains secure and confidential.

**h4: Your Rights**

> We believe in empowering users to manage their personal data. You have the right to request access, update inaccuracies, or delete your information when necessary. If you no longer wish to receive updates or want to manage your cookie preferences, you can do so easily. Our commitment is to give you full control over your data while ensuring you remain informed about how it’s used.

**h4: Data Protection**

> We employ state-of-the-art measures to protect the information entrusted to us. From encryption to secure servers, your data is stored and managed with industry-leading security protocols. We also conduct regular system checks to prevent unauthorized access. While no system is completely immune to threats, our continuous efforts minimize risks and provide you with a safe online environment.

**h4: Contact Us**

> If you have questions, need assistance, or want to know more about our data practices, we encourage you to contact us. Transparency is integral to our approach, and we are here to provide clarity whenever needed. Whether it’s a simple query or a detailed concern, our team is ready to address your inquiries promptly and professionally.

(Each block is exactly one heading + one paragraph; no lists, no links, no sub-sections.)

---

## 404 PAGE

Served with **HTTP status 404** for any unknown path; also reachable at `/404`. `<title>Flexio</title>`; generic meta description.

### Section order
1. Navigation (shared, fixed)
2. `<main data-framer-name="Hero">` containing:
   - `<section data-framer-name="Main">` — bg cream `#fefaf6`, **`height:100vh`**, padding `80px 30px 0` (tablet `0 40px`, phone `144px 16px 0`), content centered vertically+horizontally
   - Footer (shared) immediately after the hero section
3. **NO pre-footer CTA banner** on this page.

### Hero content
Container: max-width 1140px, column, centered, **gap 32px** (phone: gap 24px, width 100%):

1. **Title block** (column, centered, gap 12px):
   - Eyebrow badge, Primary variant (orange bg, white uppercase 14px): **"404"**
   - `<h1>` preset `13d02to` (Clash Grotesk 500, **82px desktop / 66px tablet / 48px phone**, line-height **0.9em**, `#262121`), centered, max-width 700px: **"OOPS, Something went wrong."**
2. `<p>` Header Description (preset `1044c9w`, 18px/lh 28px, 500; 16/26 mobile), centered, max-width 700px: **"We couldn't find the page you were looking for"**
3. **Primary button "Go back home"** → `/` (orange `#fe6037` pill, radius 99px, white 16px label, dual-label text-swap hover — standard shared Primary button).

### Animations
- **Page-load slide-up**: the cream `<section Main>` starts `translateY(1070px)` → 0, spring `{damping:60, stiffness:400, mass:1.2}` — same entrance as other pages (orange shows behind during the spring).
- Mobile-nav service cards: scale 1.1 → 1 (spring 250/64, delay 0.1) — shared nav behavior.
- **There is NO physics / falling-tags / Matter.js animation on this 404 page** (checked scripts and markup; nothing beyond the standard entrance spring). It is a static centered hero.

### Notes for builder
- The 404 hero fills the viewport (`100vh`) so the footer sits below the fold.
- Both pages use the standard fixed pill Navigation over the cream background.
- Footer "404" link in the "Page" column points to `/404` — keep that route in Astro in addition to the catch-all 404.
