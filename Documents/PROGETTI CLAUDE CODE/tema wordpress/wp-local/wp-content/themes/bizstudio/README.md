# BizStudio

**Version:** 1.0.0
**Author:** [Biz Studio](https://bizstudio.it)
**License:** GPL v2 or later
**Requires WordPress:** 6.5+
**Requires WooCommerce:** 8.0+
**Requires PHP:** 8.1+

Premium WooCommerce theme with modern design, fluid animations, and exceptional performance. Built for serious e-commerce.

---

## Requirements

| Requirement | Minimum Version |
|-------------|-----------------|
| WordPress   | 6.5+            |
| WooCommerce | 8.0+            |
| PHP         | 8.1+            |

---

## Installation

1. Download the theme `.zip` file.
2. In WordPress admin, go to **Appearance > Themes > Add New > Upload Theme**.
3. Upload the `.zip` file and click **Install Now**.
4. Activate the theme.
5. Install and activate **WooCommerce** if not already installed.
6. Go to **Appearance > Customize** to configure the theme.

---

## Features

### Core
- WooCommerce-first design with full shop, cart, checkout, and account pages
- Responsive, mobile-first layout
- Dark mode toggle (automatic and manual)
- RTL language support (`rtl.css` included)
- Translation-ready with `.pot` file and WPML configuration

### Header
- 5 header presets: Default, Centered, Minimal, Transparent, E-commerce
- 3-row layout: Top Bar, Main Header, Bottom Bar
- Sticky header with scroll detection
- Live AJAX search with instant results
- Mobile menu drawer
- Mini-cart slide-in drawer

### Shop
- Configurable grid (2-5 columns)
- Product card styles: Default, Minimal, Bordered
- Quick View modal
- Image hover swap (second gallery image on hover)
- Scroll reveal animations
- AJAX add-to-cart with instant feedback
- Category filters sidebar with price range and availability
- Grid/List view toggle

### Single Product
- Modern product gallery with thumbnail navigation
- Color/size swatches for variable products
- Sticky add-to-cart bottom bar
- Social sharing (Facebook, Twitter/X, WhatsApp, copy link)
- Related products carousel

### Cart & Checkout
- Mini-cart drawer with real-time updates via WC fragments
- Shipping progress bar with multi-step thresholds and confetti effects
- Full-page checkout with minimal chrome
- Coupon code support

### Wishlist
- Built-in wishlist system (no plugin needed)
- Cookie-based for guests, user meta for logged-in users
- AJAX toggle from product cards and single product
- Dedicated wishlist page via `[bizstudio_wishlist]` shortcode

### Footer
- 5 footer presets: Default (4 col), Centered, 2 col, 3 col, Asymmetric
- Optional pre-footer features section (shipping, returns, security)
- Payment method icons
- Social media links
- Customizable copyright text with `{year}` and `{site_name}` tokens

### SEO
- Built-in Schema.org JSON-LD (Organization, WebSite, Product, BreadcrumbList)
- Open Graph and Twitter Card meta tags
- Automatic deactivation when Yoast SEO, Rank Math, or All in One SEO is detected

### Performance
- Configurable lazy loading
- Google Fonts preloading
- WordPress head cleanup (emoji, embeds, query strings removed)
- Dequeued default WooCommerce styles (custom styles throughout)
- CSS custom properties for zero-cost theme customization
- Minimal JS footprint with deferred loading

---

## Customizer Options

All theme options are found under **Appearance > Customize > BizStudio**.

| Section              | Options |
|----------------------|---------|
| Barra Promozionale   | Enable, text, background color, text color |
| Header               | Preset (5), top bar, main header, bottom bar, sticky, transparent |
| Barra Spedizione     | Enable, 2 configurable steps (amount, label, color), completion message |
| Colori               | 10 color settings (primary, text, bg, surface, border, success, error, rating) |
| Tipografia           | Body font, heading font (7 choices + system), size, line height, weight |
| Shop                 | Products per page, columns, card style, rating/category/quick view/wishlist toggles, sale badge style |
| Performance          | Scroll reveal, image hover swap, font preloading, lazy load |
| Footer               | Preset (5), pre-footer features (3 boxes), bg/text color, copyright, payment icons, social links |

---

## Shortcodes

### `[bizstudio_contact_form]`

Renders a built-in AJAX contact form with honeypot spam protection and rate limiting.

**Attributes:**
- `to` -- Recipient email address (defaults to admin email)
- `subject_prefix` -- Email subject prefix (defaults to `[BizStudio]`)

**Example:**
```
[bizstudio_contact_form to="info@example.com" subject_prefix="[My Site]"]
```

### `[bizstudio_wishlist]`

Renders the wishlist page with all saved products. Typically placed on a dedicated `/wishlist/` page.

**Example:**
```
[bizstudio_wishlist]
```

---

## Plugin Compatibility

BizStudio includes built-in CSS compatibility for the following form plugins:

- **Contact Form 7** -- Forms inherit BizStudio button, input, and validation styles
- **WPForms** -- Full input and submit button style mapping
- **Gravity Forms** -- Input, button, validation, and progress bar styling

SEO plugin compatibility:

- **Yoast SEO** -- BizStudio's Schema.org and Open Graph output is automatically disabled
- **Rank Math** -- Same as Yoast
- **All in One SEO** -- Same as Yoast

WPML compatibility:

- `wpml-config.xml` is included for automatic Customizer string registration

---

## File Structure

```
bizstudio/
├── assets/src/css/           # Stylesheets
│   ├── main.css              # Core styles
│   ├── components/           # Component styles
│   └── woocommerce/          # WooCommerce-specific styles
├── assets/src/js/            # JavaScript
│   ├── main.js               # Core JS
│   └── modules/              # Feature modules
├── inc/                      # PHP includes
│   ├── setup.php             # Theme setup (supports, menus, widgets)
│   ├── enqueue.php           # Asset enqueueing
│   ├── customizer.php        # Customizer settings
│   ├── header-builder.php    # Header Customizer + rendering
│   ├── footer-builder.php    # Footer Customizer + rendering
│   ├── promo-bar.php         # Promotional bar
│   ├── shipping-bar.php      # Shipping progress bar
│   ├── sticky-bar.php        # Sticky add-to-cart bar
│   ├── seo.php               # Schema.org + Open Graph
│   ├── contact-form.php      # Built-in contact form
│   ├── wishlist.php          # Wishlist system
│   ├── swatches.php          # Color/size swatches
│   ├── woocommerce.php       # WooCommerce overrides
│   ├── ajax-handlers.php     # AJAX endpoints
│   ├── template-tags.php     # Template helper functions
│   ├── translations.php      # WooCommerce string translations
│   ├── compat.php            # Plugin compatibility (CF7, WPForms, etc.)
│   └── cleanup.php           # WordPress head cleanup
├── languages/                # Translation files
│   └── bizstudio.pot         # Translation template
├── template-parts/           # Template partials
├── woocommerce/              # WooCommerce template overrides
├── functions.php             # Theme entry point
├── style.css                 # Theme metadata
├── rtl.css                   # RTL language support
├── wpml-config.xml           # WPML configuration
├── theme.json                # Block editor config
└── screenshot.png            # Theme screenshot (1200x900)
```

---

## Changelog

### 1.0.0 (2026-03-30)
- Initial release
- Full WooCommerce integration
- 5 header presets, 5 footer presets
- Built-in wishlist, contact form, shipping bar
- Schema.org + Open Graph SEO
- Dark mode support
- RTL support
- WPML configuration
- Plugin compatibility (CF7, WPForms, Gravity Forms)
- WordPress head cleanup and security hardening

---

## Credits

- **Font:** [Inter](https://rsms.me/inter/) by Rasmus Andersson (SIL Open Font License)
- **Icons:** Custom SVG icon system
- Built with WordPress and WooCommerce

---

## License

BizStudio WordPress Theme, (C) 2026 Biz Studio.

This theme is distributed under the terms of the GNU General Public License v2 or later.

See [LICENSE](http://www.gnu.org/licenses/gpl-2.0.html) for full license text.
