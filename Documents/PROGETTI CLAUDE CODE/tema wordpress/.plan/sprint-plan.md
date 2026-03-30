# BizStudio Theme — Sprint Plan

> Ultimo aggiornamento: 2026-03-30
> Stato: Sprint 1, 2, 3 e 4 COMPLETATI (implementazione), test sprint da fare

---

## Legenda

Ogni task ha due checkbox:
- `[x]` / `[ ]` = **Implementato** (codice scritto)
- `[T]` / `[ ]` = **Testato** (verificato funzionante nel browser)

Al termine di ogni sprint si fa un **test completo** di tutte le funzionalita dello sprint.

---

## Ambiente di Sviluppo

- **URL locale**: http://localhost:8080/
- **Admin**: http://localhost:8080/wp-admin/ (admin / admin)
- **WordPress**: 6.9.4 + SQLite (no MySQL)
- **WooCommerce**: 10.6.1
- **PHP**: 8.5.3
- **Tema path**: `wp-local/wp-content/themes/bizstudio/`
- **Server**: `cd wp-local && php -d memory_limit=256M -S localhost:8080 router.php`
- **IMPORTANTE**: usare `router.php` per i pretty permalinks

---

## Sprint 1 — Fondazione [IMPLEMENTATO]

### Obiettivo
Tema attivabile con header, footer, homepage, product cards, mini-cart AJAX, dark mode.

### Task
| # | Funzionalita | Impl | Test |
|---|---|---|---|
| 1.1 | WordPress + SQLite + WooCommerce installato | [x] | [ ] |
| 1.2 | 4 categorie + 8 prodotti di test + menu 6 voci | [x] | [ ] |
| 1.3 | `theme.json` design tokens (colori, font, spacing) | [x] | [ ] |
| 1.4 | Header: logo, nav, icone (dark mode, search, account, cart), sticky | [x] | [ ] |
| 1.5 | Footer: 4 colonne widget, copyright, menu | [x] | [ ] |
| 1.6 | Homepage: hero, categorie grid, featured products, on sale | [x] | [ ] |
| 1.7 | Card prodotto: image swap, badges sale/new, quick actions hover | [x] | [ ] |
| 1.8 | Mini-cart: drawer laterale AJAX con prodotti, totale, CTA | [x] | [ ] |
| 1.9 | AJAX add-to-cart dalle card prodotto (homepage/shop) | [x] | [ ] |
| 1.10 | Dark mode toggle con persistenza localStorage | [x] | [ ] |
| 1.11 | Scroll reveal animations con stagger | [x] | [ ] |
| 1.12 | Mobile responsive: hamburger menu, drawer, grid 2 colonne | [x] | [ ] |

### Sprint 1 — Test completo: [ ]

---

## Sprint 2 — WooCommerce Core [IMPLEMENTATO]

### Obiettivo
Shop page con filtri, pagina prodotto con gallery, prodotto variabile, 404.

### Task
| # | Funzionalita | Impl | Test |
|---|---|---|---|
| 2.1 | Immagini placeholder per 8 prodotti (primary + secondary) | [x] | [ ] |
| 2.2 | Prodotto variabile: Felpa Essential (3 colori x 4 taglie = 12 variazioni) | [x] | [ ] |
| 2.3 | Shop page: toolbar (count risultati + ordinamento + toggle griglia/lista) | [x] | [ ] |
| 2.4 | Shop page: sidebar filtri (categorie, prezzo min/max, disponibilita, offerte) | [x] | [ ] |
| 2.5 | Shop page: sidebar drawer su mobile + bottone "Filtri" | [x] | [ ] |
| 2.6 | Pagina prodotto: gallery con thumbnails cliccabili + zoom hover + fade | [x] | [ ] |
| 2.7 | Pagina prodotto: info (titolo, prezzo, rating, SKU, categorie, tags) | [x] | [ ] |
| 2.8 | Pagina prodotto: tabs (descrizione, info aggiuntive, recensioni) | [x] | [ ] |
| 2.9 | Pagina prodotto: prodotti correlati griglia 4 colonne | [x] | [ ] |
| 2.10 | Pagina prodotto: share buttons (Facebook, X, WhatsApp, copia link) | [x] | [ ] |
| 2.11 | Breadcrumbs stilizzati | [x] | [ ] |
| 2.12 | Pagina 404 personalizzata | [x] | [ ] |
| 2.13 | Vista lista prodotti (salvata in localStorage) | [x] | [ ] |

### Sprint 2 — Test completo: [ ]

---

## Sprint 3 — Interattivita [IMPLEMENTATO]

### Obiettivo
AJAX add-to-cart su pagina prodotto, quick view, live search, swatches, quantity +/-, traduzioni IT.

### Task
| # | Funzionalita | Impl | Test |
|---|---|---|---|
| 3.1 | Add-to-cart AJAX su pagina prodotto (no reload) + apre mini-cart | [x] | [ ] |
| 3.2 | Bottone add-to-cart: stati loading/success + testo italiano | [x] | [ ] |
| 3.3 | Supporto variazioni nell'AJAX (variation_id + attributes) | [x] | [ ] |
| 3.4 | Quick view modale (icona occhio su card): immagine, info, add-to-cart | [x] | [ ] |
| 3.5 | Live search AJAX: modal overlay, debounce 300ms, thumbnail/prezzo | [x] | [ ] |
| 3.6 | CMD/CTRL+K per aprire la ricerca | [x] | [ ] |
| 3.7 | Quantita +/- buttons stilizzati (nasconde spinner nativo) | [x] | [ ] |
| 3.8 | Swatches colore: pallini colorati al posto del dropdown select | [x] | [ ] |
| 3.9 | Swatches: admin color picker per termine colore | [x] | [ ] |
| 3.10 | Swatches: active/disabled states, sync con WC variations | [x] | [ ] |
| 3.11 | Toast notification per conferme | [x] | [ ] |
| 3.12 | Traduzioni IT complete: tutte le stringhe WC (filtro gettext 100+ stringhe) | [x] | [ ] |
| 3.13 | Fix: prodotti catalogo invisibili (data-reveal-item standalone) | [x] | [ ] |
| 3.14 | Fix: quantity + bottone sulla stessa riga, altezze allineate | [x] | [ ] |

### Sprint 3 — Test completo: [ ]

### File creati in Sprint 3
- `inc/swatches.php` — sistema swatches colore con admin color picker
- `inc/translations.php` — traduzione completa stringhe WC in italiano (100+ stringhe)
- `assets/src/js/modules/single-add-to-cart.js` — AJAX add-to-cart pagina prodotto
- `assets/src/js/modules/quick-view.js` — modale quick view
- `assets/src/js/modules/search.js` — live search con debounce
- `assets/src/js/modules/quantity.js` — +/- buttons
- `assets/src/js/modules/swatches.js` — sync pallini con WC select
- `template-parts/product/quick-view-modal.php` — shell modale

---

## Sprint 4 — Checkout + Account + Wishlist [IMPLEMENTATO]

### Obiettivo
Checkout fluido, my account restyled, wishlist funzionante, promo bar, shipping bar, sticky bar.

### Task
| # | Funzionalita | Impl | Test |
|---|---|---|---|
| 4.1 | Block checkout CSS styling (colori, font, layout 2 colonne) | [x] | [ ] |
| 4.2 | Block cart CSS styling | [x] | [ ] |
| 4.3 | My Account: dashboard restyled | [x] | [ ] |
| 4.4 | My Account: lista ordini con stati colorati | [x] | [ ] |
| 4.5 | My Account: form login/registrazione side-by-side | [x] | [ ] |
| 4.6 | Wishlist: logica cookie (guest) + user meta (loggato) | [x] | [ ] |
| 4.7 | Wishlist: pagina dedicata (/wishlist/) | [x] | [ ] |
| 4.8 | Wishlist: counter nell'header | [x] | [ ] |
| 4.9 | Wishlist: toggle animato (cuore beat) | [x] | [ ] |
| 4.10 | Form styling universale (input, select, textarea, focus, error) | [x] | [ ] |
| 4.11 | Promo bar: barra fissa sopra l'header con testo promozionale, dismissibile, colore custom | [x] | [ ] |
| 4.12 | Promo bar: configurabile da Customizer (testo, colore sfondo, colore testo, on/off) | [x] | [ ] |
| 4.13 | Shipping progress bar multi-step nel mini-cart e pagina carrello | [x] | [ ] |
| 4.14 | Shipping bar: soglie configurabili da Customizer (es. 29EUR=sconto 50%, 49EUR=gratis) | [x] | [ ] |
| 4.15 | Shipping bar: gratificazione visiva al raggiungimento soglia (confetti/checkmark + messaggio) | [x] | [ ] |
| 4.16 | Shipping bar: progress bar animata con colori per ogni step | [x] | [ ] |
| 4.17 | Shipping bar: aggiornamento live AJAX quando cambia il carrello | [x] | [ ] |
| 4.18 | Sticky add-to-cart bottom bar su pagina prodotto (mobile + desktop) | [x] | [ ] |
| 4.19 | Sticky bar: appare allo scroll sotto il form originale, mostra immagine/nome/prezzo/bottone | [x] | [ ] |

### Sprint 4 — Test completo: [ ]

### File creati in Sprint 4
- `page.php` — template pagina generica (per cart, checkout, my account)
- `inc/promo-bar.php` — barra promozionale + Customizer settings
- `inc/shipping-bar.php` — barra spedizione multi-step + Customizer + cart fragments
- `inc/wishlist.php` — sistema wishlist completo (cookie/user meta, AJAX, shortcode, render)
- `inc/sticky-bar.php` — sticky add-to-cart bottom bar
- `assets/src/css/woocommerce/cart-checkout.css` — stili block cart/checkout
- `assets/src/css/woocommerce/account.css` — stili My Account + login/register
- `assets/src/css/components/forms.css` — stili form universali
- `assets/src/css/components/promo-bar.css` — stili promo bar
- `assets/src/css/components/shipping-bar.css` — stili shipping bar + confetti
- `assets/src/css/components/wishlist.css` — stili wishlist (heart, page, badge)
- `assets/src/css/components/sticky-bar.css` — stili sticky bar
- `assets/src/js/modules/shipping-bar.js` — JS shipping bar (confetti, step tracking)
- `assets/src/js/modules/wishlist.js` — JS wishlist (AJAX toggle, heart beat, badge update)
- `assets/src/js/modules/sticky-bar.js` — JS sticky bar (IntersectionObserver, variation sync)

---

## Sprint 5 — Animazioni [DA FARE]

### Obiettivo
Effetti premium: parallax, hover avanzati, page transitions, micro-interazioni.

### Task
| # | Funzionalita | Impl | Test |
|---|---|---|---|
| 5.1 | Parallax su hero section | [ ] | [ ] |
| 5.2 | Parallax leggero su banner promozionali | [ ] | [ ] |
| 5.3 | Hover effects avanzati sulle card (lift + shadow) | [ ] | [ ] |
| 5.4 | Image zoom on hover migliorato | [ ] | [ ] |
| 5.5 | Link underline animation (left-to-right) | [ ] | [ ] |
| 5.6 | Page transition (fade out/in) | [ ] | [ ] |
| 5.7 | Loading bar top durante caricamento | [ ] | [ ] |
| 5.8 | Add to cart: checkmark animato | [ ] | [ ] |
| 5.9 | Wishlist: heart beat animation | [ ] | [ ] |
| 5.10 | Skeleton loading per contenuti AJAX | [ ] | [ ] |
| 5.11 | Smooth scroll per anchor links | [ ] | [ ] |
| 5.12 | Back to top button animato | [ ] | [ ] |

### Sprint 5 — Test completo: [ ]

---

## Sprint 6 — Header/Footer Builder + Admin [DA FARE]

### Obiettivo
Builder visuali nel Customizer, Customizer completo, Schema.org.

### Task — Header Builder
| # | Funzionalita | Impl | Test |
|---|---|---|---|
| 6.1 | Pannello Customizer con 3 righe: Top Bar, Main Header, Bottom Bar | [ ] | [ ] |
| 6.2 | Ogni riga 3 zone: Sinistra, Centro, Destra | [ ] | [ ] |
| 6.3 | Elementi trascinabili (Logo, Menu, Ricerca, Cart, Account, ecc.) | [ ] | [ ] |
| 6.4 | 5 preset header (Default, Centered, Minimal, Transparent, E-commerce) | [ ] | [ ] |
| 6.5 | Top bar promo dismissibile | [ ] | [ ] |
| 6.6 | Opzioni per riga (altezza, sfondo, bordi, sticky, visibilita device) | [ ] | [ ] |
| 6.7 | Header mobile separato | [ ] | [ ] |
| 6.8 | Preview live nel Customizer | [ ] | [ ] |

### Task — Footer Builder
| # | Funzionalita | Impl | Test |
|---|---|---|---|
| 6.9 | 3 sezioni: Pre-Footer, Widgets, Bottom Bar | [ ] | [ ] |
| 6.10 | Layout colonne selezionabile (1/2/3/4 + asimmetrici) | [ ] | [ ] |
| 6.11 | Elementi (widget, logo, social, newsletter, payment icons, copyright) | [ ] | [ ] |
| 6.12 | 5 preset footer | [ ] | [ ] |
| 6.13 | Colori custom footer | [ ] | [ ] |
| 6.14 | Preview live nel Customizer | [ ] | [ ] |

### Task — Customizer + SEO
| # | Funzionalita | Impl | Test |
|---|---|---|---|
| 6.15 | Customizer: sezione Colori | [ ] | [ ] |
| 6.16 | Customizer: sezione Tipografia | [ ] | [ ] |
| 6.17 | Customizer: sezione Shop | [ ] | [ ] |
| 6.18 | Customizer: sezione Performance | [ ] | [ ] |
| 6.19 | Schema.org: Product + BreadcrumbList + Organization + WebSite | [ ] | [ ] |
| 6.20 | Open Graph meta tags | [ ] | [ ] |
| 6.21 | Form contatto basico via shortcode | [ ] | [ ] |

### Sprint 6 — Test completo: [ ]

---

## Sprint 7 — Performance [DA FARE]

### Obiettivo
Lighthouse 90+, critical CSS, ottimizzazione caricamento.

### Task
| # | Funzionalita | Impl | Test |
|---|---|---|---|
| 7.1 | Critical CSS inline per above-the-fold | [ ] | [ ] |
| 7.2 | Async loading per CSS non-critical | [ ] | [ ] |
| 7.3 | Preload: font Inter, hero image | [ ] | [ ] |
| 7.4 | Prefetch: link hover | [ ] | [ ] |
| 7.5 | Lazy loading immagini (nativo + placeholder) | [ ] | [ ] |
| 7.6 | JS modulare: carica solo su pagine rilevanti | [ ] | [ ] |
| 7.7 | Dequeue script WC non necessari | [ ] | [ ] |
| 7.8 | Image srcset e sizes ottimizzati | [ ] | [ ] |
| 7.9 | Vite build pipeline (bundle CSS + JS produzione) | [ ] | [ ] |
| 7.10 | Audit Lighthouse e fix | [ ] | [ ] |

### Sprint 7 — Test completo: [ ]

---

## Sprint 8 — Polish + Release [DA FARE]

### Obiettivo
Tema pronto per la vendita: responsive perfetto, i18n, documentazione.

### Task
| # | Funzionalita | Impl | Test |
|---|---|---|---|
| 8.1 | Test responsive: mobile, tablet, desktop (ogni pagina) | [ ] | [ ] |
| 8.2 | Test cross-browser: Chrome, Safari, Firefox | [ ] | [ ] |
| 8.3 | RTL CSS (rtl.css) | [ ] | [ ] |
| 8.4 | File .pot per traduzioni (wp i18n make-pot) | [ ] | [ ] |
| 8.5 | wpml-config.xml per WPML | [ ] | [ ] |
| 8.6 | Compatibilita plugin form (CF7, WPForms) | [ ] | [ ] |
| 8.7 | Compatibilita Yoast/Rank Math (disabilita schema duplicato) | [ ] | [ ] |
| 8.8 | Screenshot.png (1200x900) | [ ] | [ ] |
| 8.9 | README.md con documentazione | [ ] | [ ] |
| 8.10 | Pulizia codice, console.log, commenti inutili | [ ] | [ ] |
| 8.11 | Versionamento finale | [ ] | [ ] |
| 8.12 | Zip del tema per distribuzione | [ ] | [ ] |

### Sprint 8 — Test completo: [ ]

---

## Bug Fix Log

| Data | Bug | Causa | Fix | Sprint |
|---|---|---|---|---|
| 2026-03-30 | Prodotti invisibili nella pagina /shop/ | `data-reveal-item` orfani non osservati da IntersectionObserver | Osserva anche `data-reveal-item` standalone | 3 |
| 2026-03-30 | Add-to-cart non funzionava su pagina prodotto | `FormData` non include submit button value | Preso `productId` da `submitBtn.value` | 3 |
| 2026-03-30 | Quantita e bottone su righe separate | CSS `flex-wrap: wrap` senza `align-items: stretch` | Flexbox con `stretch` + altezze uniformi 52px | 3 |
| 2026-03-30 | Stringhe WC in inglese (Choose an option, Clear, ecc.) | WC non ha language pack IT installato | Filtro `gettext` con 100+ traduzioni nel tema | 3 |
| 2026-03-30 | Card prodotto altezze diverse nella griglia | Card non flex, prezzo non ancorato in fondo | `display:flex; flex-direction:column; height:100%` + `margin-top:auto` sul prezzo | 3 |

---

## Note Tecniche

### Come avviare il server locale
```bash
cd "/Users/vincenzopetrone/Documents/PROGETTI CLAUDE CODE/tema wordpress/wp-local"
php -d memory_limit=256M -S localhost:8080 router.php
```
**IMPORTANTE**: usare `router.php` per i pretty permalinks.

### File principali del tema
```
wp-local/wp-content/themes/bizstudio/
├── theme.json              → Design tokens
├── style.css               → Metadata tema
├── functions.php           → Bootstrap (include tutti i moduli)
├── header.php              → Header + wishlist + search modal + mobile menu + mini-cart
├── footer.php              → Footer + quick view modal
├── front-page.php          → Homepage e-commerce
├── page.php                → Template pagina (cart, checkout, my account)
├── index.php               → Fallback
├── 404.php                 → Pagina errore
├── inc/
│   ├── setup.php           → Theme supports, menus, sidebar
│   ├── enqueue.php         → CSS/JS loading condizionale
│   ├── woocommerce.php     → WC support, HPOS, custom card, fragments
│   ├── ajax-handlers.php   → AJAX: add-to-cart, mini-cart, quick view, live search
│   ├── template-tags.php   → SVG icons, star rating, sale percentage
│   ├── swatches.php        → Color swatches + admin color picker
│   ├── translations.php    → Traduzione 130+ stringhe WC in italiano
│   ├── promo-bar.php       → Barra promozionale + Customizer
│   ├── shipping-bar.php    → Barra spedizione multi-step + Customizer + AJAX
│   ├── wishlist.php        → Sistema wishlist (cookie/meta, AJAX, shortcode)
│   └── sticky-bar.php      → Sticky add-to-cart bottom bar
├── template-parts/
│   ├── product/card-product.php      → Card prodotto (shop + homepage)
│   ├── product/quick-view-modal.php  → Shell modale quick view
│   └── global/mini-cart.php          → Drawer mini-cart + shipping bar
├── woocommerce/
│   ├── archive-product.php → Shop/categoria con sidebar + toolbar
│   └── single-product.php  → Pagina prodotto con gallery + info + tabs
└── assets/src/
    ├── css/
    │   ├── main.css                       → Design system (~850 righe)
    │   ├── components/forms.css           → Stili form universali
    │   ├── components/promo-bar.css       → Stili barra promozionale
    │   ├── components/shipping-bar.css    → Stili barra spedizione + confetti
    │   ├── components/wishlist.css        → Stili wishlist (heart, page, badge)
    │   ├── components/sticky-bar.css      → Stili sticky bar
    │   ├── woocommerce/shop.css           → Stili shop + sidebar + filtri
    │   ├── woocommerce/product.css        → Stili prodotto + swatches + qty
    │   ├── woocommerce/cart-checkout.css   → Stili block cart + checkout
    │   └── woocommerce/account.css        → Stili My Account + login/register
    └── js/
        ├── main.js                    → Header, dark mode, scroll reveal, AJAX cart
        └── modules/
            ├── mini-cart.js           → Open/close mini-cart
            ├── shop.js               → Filtri sidebar, view toggle, price filter
            ├── gallery.js            → Thumbnails switch, copy link
            ├── single-add-to-cart.js  → AJAX add-to-cart pagina prodotto
            ├── quick-view.js          → Modale quick view
            ├── search.js             → Live search AJAX
            ├── quantity.js           → +/- buttons
            ├── swatches.js           → Sync pallini colore con WC select
            ├── shipping-bar.js       → Confetti, step tracking, AJAX update
            ├── wishlist.js           → Toggle AJAX, heart beat, badge update
            └── sticky-bar.js         → IntersectionObserver, variation sync
```

### Convenzioni codice
- Prefix CSS: `biz-` (es. `.biz-card`, `.biz-header`)
- Prefix PHP: `bizstudio_` (es. `bizstudio_icon()`, `bizstudio_star_rating()`)
- Text domain: `bizstudio`
- BEM-like: `.biz-card__title`, `.biz-card--out-of-stock`
- JS: vanilla ES2024+, no jQuery, event delegation
- Data attributes per JS: `data-reveal`, `data-product-id`, `data-view`
