/* Central site content — every fact here comes from the approved brand brief
   or the client's live landing page. Do not add unverified claims. */

export interface Testimonial {
  quote: string;
  name: string;
}

/** Real reviews from the client's Trustpilot profile (never invent new ones). */
export const TESTIMONIALS: Testimonial[] = [
  {
    quote:
      "From start to finish, the actual time I spent on it was under three hours, split into small chunks across two weeks. The team worked around my pace, instead of expecting me to work around theirs.",
    name: 'Daniele',
  },
  {
    quote:
      "SmartWills opened a WhatsApp group on day one with the Legacy Guide. Every question got an answer in hours, not days. I don't think I ever had to write the words 'any update on this?'.",
    name: 'Gian Marco',
  },
  {
    quote:
      'Fixed price declared on the first call, fully remote process, zero surprises on the final invoice. What you are told on day one is what you pay.',
    name: 'Flavio',
  },
  {
    quote:
      'I tried to save money at first with a cheap online template. Months later I realised I had no idea whether that document would actually be accepted by the UAE courts. The specialist flagged two issues with my original setup that would have caused real problems.',
    name: 'Emanuela',
  },
  {
    quote:
      'A single Protection Assessment for both of us, two parallel will documents prepared at the same time, registered together. Done in a fraction of the time I had expected.',
    name: 'Simona',
  },
  {
    quote:
      'Protection Assessment on Saturday morning, draft in my WhatsApp group by Wednesday exactly as promised. Every single deadline the team committed to on the first call, they hit.',
    name: 'Vittorio',
  },
  {
    quote:
      "The thing I valued most wasn't the price or even the timeline: it was that I never had to chase anyone. When something needs chasing, they chase it. When something needs your input, they ask once.",
    name: 'Andrea',
  },
  {
    quote:
      'Two certified legal translators, the second independently verifies the first, word by word. Our will was submitted once and accepted first time — no revisions, no delays.',
    name: 'Luigi',
  },
];

export interface Faq {
  q: string;
  a: string;
}

export const FAQS: Faq[] = [
  {
    q: 'Do I really need a UAE will if I already have one back home?',
    a: 'Very likely yes. A will from your home country may not work automatically in the UAE. Validation can involve notarisation, attestation, certified Arabic translation and court approval — and could take 6 to 12 months, while your UAE assets could remain inaccessible. The clean solution is one will per jurisdiction: your home-country will covers your assets there, your UAE will covers your assets here.',
  },
  {
    q: 'How much of my time does it actually take?',
    a: 'Two to three hours in total, spread across a few weeks: one guided session, a draft review, and a short government video call in English. We coordinate everything in between — drafting, certified translation, submission and every follow-up until the will is registered.',
  },
  {
    q: 'Do I need to visit any office or court?',
    a: 'No. The process is remote for all three registries. The only live step is a short government video call in English, and we prepare you for exactly what to expect before it starts.',
  },
  {
    q: 'I am Muslim. Can I register a will?',
    a: 'There are specific options for Muslim expats, and the details matter. The right registry and structure depend on your situation — that is exactly what the free Risk Profile Audit clarifies, with no obligation.',
  },
  {
    q: 'What if my situation changes later?',
    a: 'Percentage-based wills cover assets you acquire in the future, so a new bank account or property does not require a new will. And once a year we check in with you: if life has changed — a child, a move, a new executor — we tell you whether an update makes sense.',
  },
  {
    q: 'How much does it cost?',
    a: 'Government fees are public and paid directly to the registry: ADJD 950 AED (single) or 1,900 AED (mirror), Dubai Courts around 2,000 / 4,000 AED, DIFC around 10,000 / 15,000 AED. Our service fee is transparent and discussed on the free call, before you decide anything.',
  },
];

export interface Registry {
  id: string;
  name: string;
  emirate: string;
  tagline: string;
  language: string;
  who: string;
  witnesses: string;
  process: string;
  feesSingle: string;
  feesMirror: string;
  note: string;
  image: string;
}

export const REGISTRIES: Registry[] = [
  {
    id: 'difc',
    name: 'DIFC',
    emirate: 'Dubai',
    tagline: 'The English-language registry inside Dubai’s international financial centre.',
    language: 'English only — no translation needed',
    who: 'Non-Muslims only',
    witnesses: '2 witnesses, who join the video appointment',
    process: 'Online',
    feesSingle: '~10,000 AED',
    feesMirror: '~15,000 AED',
    note: 'Often considered by internationally mobile families and holders of complex asset structures. The higher government fee buys an English-only process.',
    image: '/images/article-difc.jpg',
  },
  {
    id: 'adjd',
    name: 'ADJD',
    emirate: 'Abu Dhabi',
    tagline: 'The Abu Dhabi Judicial Department registry — open to the widest audience.',
    language: 'English + Arabic (certified translation)',
    who: 'Muslim and non-Muslim expats',
    witnesses: 'None',
    process: 'Online, fully remote',
    feesSingle: '950 AED',
    feesMirror: '1,900 AED',
    note: 'The lowest government fee of the three and open to both Muslim and non-Muslim expats. Requires a certified Arabic translation — which we handle.',
    image: '/images/reg-adjd.jpg',
  },
  {
    id: 'dc',
    name: 'Dubai Courts',
    emirate: 'Dubai',
    tagline: 'The onshore Dubai registry, with a fully remote process.',
    language: 'Arabic (certified translation)',
    who: 'Non-Muslims (declaration required)',
    witnesses: 'None',
    process: 'Online, fully remote',
    feesSingle: '~2,000 AED',
    feesMirror: '~4,000 AED',
    note: 'A middle path on government fees, with a remote process and certified Arabic translation — which we handle for you.',
    image: '/images/reg-dc.jpg',
  },
];

export interface ArticleMeta {
  slug: string;
  title: string;
  excerpt: string;
  image: string;
  minutes: number;
}

export const ARTICLES: ArticleMeta[] = [
  {
    slug: 'uae-inheritance-law-without-a-will',
    title: 'Without a registered will in the UAE: what the default rules actually say',
    excerpt:
      'Frozen accounts, a fixed distribution formula, and a court-chosen guardian. The system works — it simply works on its assumptions, not yours.',
    image: '/images/article-signing.jpg',
    minutes: 6,
  },
  {
    slug: 'difc-adjd-dubai-courts-compared',
    title: 'DIFC vs ADJD vs Dubai Courts: an honest comparison',
    excerpt:
      'Three registries, three sets of rules and fees. What actually differs, and how to think about which one fits your situation.',
    image: '/images/article-difc.jpg',
    minutes: 7,
  },
  {
    slug: 'home-country-will-in-the-uae',
    title: 'You already have a will back home. Does it protect your UAE assets?',
    excerpt:
      'A foreign will may not work automatically in the UAE. What validation really involves, and why the clean answer is one will per jurisdiction.',
    image: '/images/article-passport.jpg',
    minutes: 5,
  },
];

export const ADJD_QUOTE = {
  text: 'The surviving spouse shall receive half of the inheritance and the other half shall be distributed equally between the children of the deceased (no difference between males and females).',
  source: 'ADJD official FAQ, based on Article 11 (2) of Law No. 14 of 2021',
};

export const GUARANTEE =
  'If the court rejects the application, we fix it and resubmit at our own expense.';
