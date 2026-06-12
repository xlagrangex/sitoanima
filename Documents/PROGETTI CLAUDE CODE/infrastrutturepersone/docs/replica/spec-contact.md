# Spec — Contact page (`/contact`)

Source: https://first-effect-565679.framer.app/contact
Shares tokens, fonts, typography presets, Navigation, Primary/Secondary buttons, eyebrow badges, pre-footer CTA banner and Footer with the service template — see `spec-services.md` ("Design tokens" + "Shared components"). Below: page-specific content.

`<title>Flexio</title>`; meta description: "Flexio is a sleek Framer template for service-based businesses. Ideal for marketing agencies, business consultants, growth strategies, digit…" (template boilerplate — replace with real copy in rebuild).

## Page structure (in order)
1. Navigation (fixed pill — shared)
2. Main contact section
3. Pre-footer CTA banner "Ready to Elevate? / Proceed Toward Your Next Achievement" (shared, identical)
4. Footer (shared, identical)

(No back-to-top observed on this page.)

---

## 2. Main contact section

- `<section>` bg **`#fefaf6`**, padding `180px 30px 140px` desktop (tablet `160px 40px 100px`, mobile `144px 16px 64px`).
- **Entrance animation (page load)**: whole section content slides up from `translateY(1070px)` → 0, spring `{ damping:60, stiffness:400, mass:1.2 }` (same as service hero).
- Wrapper: row, **gap 120px** (30px tablet; column gap 40px mobile, max-width 500px), max-width **1140px**, overflow hidden. Two columns, each `flex:1`:

### Left column (column, gap 32px; centered/full-width on mobile)
1. Eyebrow badge (Primary variant: bg `#fe6037`, white, uppercase 14px): **"Contact Us"**
2. `<h2>` preset `1cqcawc` (60/48/40px Clash Grotesk 500, left-aligned; centered on mobile): **"Contact Us"**
3. `<p>` 18px/28px: **"For inquiries or support related to Whelp, please contact us using the form on this page or use the provided email address and phone number to contact us directly."** (note: source text literally says "Whelp" — template leftover.)
4. **Vertical Steps Block** — column, gap 20px; three "Step Block" rows (icon + text block, row gap ~16px, text block column gap 4px):

| Icon (52x52) | Step Title (18px/500) | Step Description (16px/400) |
|---|---|---|
| Orange circle (`#FE6037`, rx 26) + white **envelope** glyph (20%-opacity fill detail) | **Email** | link **"hello@flexio.com"** → `mailto:someone@yoursite.com` |
| Orange circle + white **phone handset** glyph | **Get support** | link **"Chat with us"** → `https://slack.com/` |
| Orange circle + white **map-pin** glyph | **Address** | text **"New York, United State"** (sic) |

### Right column — Contact form (`flex:1`)
`<form>` card: bg **`#faebdc`**, border-radius **16px**, padding **24px** (16px + gap 16px on mobile), column, **gap 20px**.

Field pattern: `<label>` = column gap 8px → label text (Clash Grotesk 500 16px, color `#262121`) + input wrapper.

Input styling (all fields): bg **`#fff`**, border-radius **8px**, height **48px**, padding **12px**, font **Cabinet Grotesk 400 16px**, text + placeholder color `#262121`, no default border; **focus: 1px solid `#fe6037`**.

Fields in order:
1. Row (2-up desktop, gap 16px; stacked column on mobile):
   - Label **"First Name"** — `<input type="text" name="Name" placeholder="First name">`
   - Label **"Last Name"** — `<input type="text" name="Name" placeholder="Last name">`
2. Label **"Email"** — `<input type="email" name="Name" placeholder="Your email">`
3. Label **"Phone Number"** — `<input type="tel" name="Name" placeholder="Your phone">`
4. Label **"Message"** — `<textarea name="Email" placeholder="Write your message">`, height **140px**, same styling.
5. Submit block (column, gap 12px):
   - `<button type="submit">` — full-width, height **48px**, pill radius 999px, bg **`#fe6037`**, label **"Submit"** (Clash Grotesk 500 16px, **white** — verified `--framer-text-color: #fff`), with the same text-swap hover as Primary buttons.
   - Disclaimer `<p>` 14px / lh 1.6em, centered, color `#262121`: **"By submitting this form you agree to our friendly "** + link **"Privacy Policy"** → `/privacy-policy` (underlined link preset).

Notes for the builder:
- No `required` attributes and no `action` in source — Framer handles submission via JS to its own endpoint. In Astro wire your own handler (e.g. POST endpoint / Formspree / Resend) and fix the duplicated `name="Name"` attributes (give each field a proper unique name).
- The source includes ~11 hidden honeypot text inputs (`name="website"|"company"|"message"|"subject"|"title"|"description"|"feedback"|"notes"|"details"|"remarks"|"comments"`, `tabindex="-1"`, `autocomplete="one-time-code"`, visually hidden) used for spam protection. Optionally replicate one honeypot field.

## Animations
1. Section content slide-up on load (y:1070 → 0, spring damping 60 / stiffness 400 / mass 1.2).
2. Input focus transition to 1px orange border.
3. Submit/CTA buttons: text-swap hover.
4. Mobile menu cards: scale 1.1 → 1 spring (shared nav).
5. No tickers/marquees/keyframes.

## Images / assets
- No photos in the main section; only the 3 inline SVG step icons (orange circle + white glyph: envelope, phone, map-pin) and shared banner/footer assets (see spec-services.md).
- Banner collage photo (shared): `https://framerusercontent.com/images/XHpUkHWKP4ZHwwcTpsF5ImDEY8.png`.
