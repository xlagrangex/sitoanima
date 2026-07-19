export const SITE_NAME = 'SmartWills';
export const SITE_DOMAIN = 'smartwills.ae';
export const EMAIL = 'info@smartwills.ae';
export const TRUSTPILOT_URL = 'https://www.trustpilot.com/review/smartwills.ae';
export const BOOK_URL = 'https://smartwills.ae/book-a-call-3073';

/** Primary CTA link with per-placement UTM (per brand brief). */
export const ctaUrl = (placement: string) =>
  `${BOOK_URL}?utm_source=website&utm_medium=organic&utm_content=${placement}`;

export const NAV_LINKS = [
  { label: 'How it works', href: '/how-it-works' },
  { label: 'Registries', href: '/registries' },
  { label: 'About', href: '/about' },
  { label: 'Resources', href: '/resources' },
  { label: '7-Day Challenge', href: '/7-day-will-challenge' },
];

export const LEGAL_ENTITY =
  'Mardef Consulting FZCO · License No. 18873 · Dubai Silicon Oasis, Dubai, UAE';

export const DISCLAIMER =
  'SmartWills is a corporate service provider, not a law firm. Content on this website is educational and does not constitute legal advice. Legal drafting and review are performed by our partner law firm. Rules, fees and government timelines can change: always verify current requirements with the relevant registry.';
