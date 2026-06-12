# Piano definitivo — Sito Infrastruttura Persone

> **v2 — riscritto sul cervellone completo.** Fondamenta: `docs/MAPPA-MIGRAZIONE.md` (censimento GHL) · `docs/cervellone/` (30 trascrizioni ~117k parole, copy integrale 17 pagine, schemi survey) · `docs/cervellone/brief-narrativo.md` (hook, dolori, offerta, obiezioni, prove, tone of voice, frasi d'oro).
> Base tecnica già pronta: Astro 5 + design system Flexio replicato, palette viola #6248B0, logo IP, deploy Vercel.

---

## 1. Brand, posizionamento, voce

- **Infrastruttura Persone** = il sito corporate ("Sistemi di Selezione del Personale per PMI"). **Assunzioni Chiavi In Mano (AChiM)** = servizio di punta con funnel proprio su `assunzionichiaviinmano.com` (entry Ads, resta separato, stesso design system).
- **Frame centrale del brand**: *"Due lunedì mattina. Due realtà diverse."* — vecchio modo (CV spazzatura, colloqui a sensazione, "la sera, dopo cena, dal divano") vs nuovo modo ("43 candidature già filtrate. Tutto il resto è successo SENZA di te").
- **Promessa**: sistema attivo in **10 giorni**, **5 ore** del tuo tempo, garanzia "paghi solo se ti portiamo almeno un colloquio qualificato", **"il sistema resta tuo per sempre"** (anti-agenzia).
- **Meccanismo (4 Pilastri)**: Fondamentali (CHI) → Attrazione (DOVE, "il 70-85% dei migliori non sta cercando lavoro") → Scrematura (COME, AI screening) → Sistema (COSA resta).
- **Tone of voice** (dal brief, 7 tratti): diretta in seconda persona, anti-agenzia, mai un claim senza numero, trasparente fino all'autocritica, colloquiale-ironica, frasi staccate, anti-pressione. Tutto il copy nuovo DEVE suonare come le trascrizioni.
- **Verticale forte**: venditori (caso bandiera SmartWills: *"4 venditori su 3.024 candidati in 32 giorni — venti colloqui su tremila"*), senza rinunciare al posizionamento generalista.
- **Voce del sito**: da decidere con Chiara — "io" (VSL) vs "noi" (TSL). Proposta: **"noi" sul corporate, "io Chiara" su AChiM/VSL e Chi Siamo** (dove vive l'origin story del motorino).

## 2. Architettura del sito

```text
infrastrutturapersone.it (Astro su Vercel)
├── /                              Home
├── /servizi                       (mega menu "Servizi")
│   ├── /servizi/assunzioni-chiavi-in-mano    ← flagship
│   ├── /servizi/employer-branding
│   ├── /servizi/hr-in-affitto
│   └── /servizi/gestione-campagne
├── /per-chi                       (mega menu "Casi d'uso", stile SaaS)
│   ├── /per-chi/venditori         ← in evidenza
│   ├── /per-chi/customer-care
│   ├── /per-chi/copywriter · /video-editor · /setter · /tutor · /tecnici-operatori
├── /casi-studio                   indice filtrabile per ruolo
│   └── /casi-studio/[slug]        video + trascrizione strutturata
├── /quiz                          "Riassumeresti l'ultima persona che hai inserito?"
├── /candidati                     multistep application (3 rami)
├── /prenota                       calendario GHL + "cosa succede in chiamata" + FAQ
├── /grazie                        thank you page unica
├── /chi-siamo                     Chiara, origin story, valori, metodo
├── /lavora-con-noi                professionisti/freelancer ("Non cerchiamo CV. Cerchiamo persone.")
├── /contatti                      form + calendario conoscitiva IP
├── /privacy-policy · /cookie-policy · /404
└── (fuori menu) /vsl · /tsl       landing funnel AChiM — gemelle su assunzionichiaviinmano.com
```

**Nav**: Logo IP · Servizi (mega menu 4 card) · Per chi (mega menu ruoli) · Casi studio · Chi siamo · CTA "Prenota una chiamata". Footer: contatti, colonne pagine/social/legal, logo gigante IP (già fatto).

## 3. Pagine — struttura e copy direction

### 3.1 Home `/`

Mapping diretto sulle sezioni Flexio già costruite:

| # | Sezione (componente esistente) | Contenuto |
| --- | --- | --- |
| 1 | Hero curtain | Eyebrow "Selezione del personale per PMI" · H1 **"Il sistema di selezione che la tua azienda merita."** · sub "Attivo in 10 giorni, con 5 ore del tuo tempo. Il resto lo facciamo noi." · CTA: Prenota una chiamata + Fai il quiz · marquee foto (asset da sostituire con foto/visual di Chiara) |
| 2 | Banda ticker (ex logo ticker) | Numeri aggregati: 7.500+ candidature · 107+ assunzioni · Trustpilot 4.5/5 · settori serviti |
| 3 | Problema (testo+foto) | "Due lunedì mattina" condensato lato vecchio modo: 3 pain ("CV spazzatura", "colloqui a sensazione", "'Bravo' non è una definizione. È una speranza.") |
| 4 | Card servizi → **4 Pilastri** | Fondamentali / Attrazione / Scrematura / Sistema — hover con dettaglio, link a /servizi/assunzioni-chiavi-in-mano |
| 5 | Process | Timeline 10 giorni (g1-2 … g9-10) + badge "5 ore del tuo tempo" |
| 6 | Video pinnato | Estratto VSL o video manifesto; headline swap: "Tutto il resto è successo senza di te." / "Il sistema è tuo, per sempre." |
| 7 | Counters | 3.024→4 venditori in 32 giorni · 1.202€→111€ costo per assunzione · 20 colloqui su 3.000 candidature |
| 8 | Physics tags → **ruoli** | Venditori, Customer care, Copywriter, Video editor, Setter, Tutor, Tecnici, Operatori (link a /per-chi/*) |
| 9 | Testimonianze "Netflix" (NUOVO componente) | carosello video: card centrale si espande, laterali si stringono; video testimonianze YouTube |
| 10 | Casi studio (2 card) | SmartWills venditori + Metodo Toddler |
| 11 | CTA banner full-width | "Ogni settimana senza un sistema è una settimana in cui i migliori accettano l'offerta di qualcun altro." → Prenota / Fai il quiz |

### 3.2 AChiM `/servizi/assunzioni-chiavi-in-mano` (+ gemella /vsl fuori menu)

Pagina di vendita completa, fonte: pagina ACIM + VSL + TSL del cervellone.
Sezioni: hero con video VSL (Wistia) · "due lunedì mattina" integrale (versione TSL, lo storytelling scritto) · 4 Pilastri approfonditi con i blocchi "fallo da solo" (value-first) · cosa è incluso (lista esatta) · 2 bonus (Masterclass Colloqui + Onboarding 90 giorni) · timeline 10 giorni · garanzia ("non ci devi un centesimo… il sistema resta tuo comunque") · casi e numeri · FAQ (12 obiezioni dal brief) · scarcity onesta ("15 aziende al mese") · CTA prenota. La variante `/tsl` è la stessa pagina senza video.

### 3.3 Altri servizi (3 pagine, template unico)

Employer Branding (career page stile lunagagroup, "idea di un'azienda più grande") · HR in affitto (screening e colloqui preliminari gestiti, "il colloquio finale resta tuo") · Gestione campagne (ricorrente). Struttura: hero → per chi è → cosa facciamo → integrazione con AChiM → CTA.

### 3.4 Casi d'uso `/per-chi/[ruolo]` (template unico declinato)

Hero "Assumere [ruolo] senza perdere mesi" · pain specifici del ruolo · come il sistema si adatta (test pratici specifici, esempi di annunci) · caso studio del ruolo (link) · numeri del ruolo · CTA. Si parte con **venditori** (il più forte), poi gli altri man mano.

### 3.5 Casi studio `/casi-studio` + `/casi-studio/[slug]`

Indice: card con video thumb + tag ruolo + numeri chiave. Dettaglio (dalle trascrizioni): video in alto → problema iniziale → perché questa soluzione → cosa abbiamo fatto → processo → risultati con numeri → CTA. Primi 3: SmartWills (venditori), Metodo Toddler (28 venditori/15 mesi), il caso "4 venditori su 3.024 in 32 giorni" già trascritto.

### 3.6 Quiz `/quiz`

Nativo Astro/React (UX migliore di GHL survey), 7 domande con la logica esatta del brief (lezione per ogni risposta), lead-gate prima del risultato, profilo su 4 aree + PDF, CTA prenota. Submit → GHL con tag per risposta (lead scoring: urgenza da domanda 2).

### 3.7 Multistep `/candidati` (application)

Replica dell'albero Survey VSL-FRP (`docs/cervellone/survey/UkxjlTAQKDfFtlqugde1.md`): anagrafica+consensi → ramo (azienda / consulente-agenzia / freelancer) → dettagli per ramo → submit GHL con UTM. Ramo freelancer → redirect a /lavora-con-noi (ex "Fit Sbagliato").

### 3.8 Lavora con noi `/lavora-con-noi`

Hero **"Non cerchiamo CV. Cerchiamo persone."** · chi è IP · framework persone A (moltiplicatori / colonna portante / freno silenzioso) · form freelancer nativo (campi dalla Survey Freelancer: specializzazione, esperienza, tariffa, proposte sì/no, CV upload, LinkedIn, consenso) → lista GHL.

### 3.9 Chi siamo `/chi-siamo`

Origin story in prima persona (motorino → 8 ore in pronto soccorso → versione 1.0) · "Metodo, non improvvisazione" · processo 4 step · foto/numeri · CTA.

### 3.10 Prenota `/prenota` + Grazie `/grazie`

Widget booking GHL `gF1UjuH7LzWl61KAucVE` · "Cosa succede durante la consulenza" (Analisi del Ruolo / Il Nostro Metodo / Prossimi Passi) · FAQ · anti-pressione ("Zero obbligo. Zero pressione."). TYP: 3 step (controlla email, preparati, ci vediamo).

## 4. Funnel e integrazioni GHL

- **Form nativi** → webhook GHL con UTM in hidden field (persistenza localStorage cross-pagina).
- **Quiz e multistep** → submit API/webhook con tag per ramo/risposta → automazioni e nurturing esistenti invariati.
- **Calendari**: AChiM `gF1UjuH7…` su /prenota e landing; Conoscitiva IP `8fvUTVF2…` su /contatti.
- **Domini**: `assunzionichiaviinmano.com` serve /vsl /tsl /prenota /grazie (stesse pagine, dominio dedicato per Ads); `infrastrutturapersone.it` → DNS su Cloudflare → Vercel (call con Chiara per la migrazione).
- Link tracciati per social → redirect con UTM.

## 5. Design system (base Flexio → IP)

Già pronto: nav pill con mega menu, curtain hero, marquee GSAP, physics tags, counters, hover bottoni, CTA banner full-width, footer con logo gigante, cursor custom, palette #6248B0, Clash Grotesk.
Da costruire: **carosello testimonianze "Netflix"** (card video che si espande al centro) · componente FAQ accordion · componente timeline 10 giorni · template caso studio · quiz engine · multistep engine.
Da decidere: foto reali (Chiara/clienti) vs placeholder; eventuale sostituzione font.

## 6. Dati da uniformare con Chiara (prima del copy definitivo)

1. Assunzioni totali: 58 vs 60+ vs 107+ → un numero ufficiale
2. Costo/assunzione Toddler: →111€ (scritto) vs →20€ (parlato)
3. Voce: io/noi per ogni area del sito (proposta in §1)
4. Loghi clienti utilizzabili pubblicamente (SmartWills, Toddler, Nuudetape, Petzon…)
5. Foto disponibili di Chiara/team per hero e chi-siamo

## 7. Fasi operative

| Fase | Deliverable | Materia prima |
| --- | --- | --- |
| **1 — Core** | Home + /prenota + /grazie + /chi-siamo + /contatti con copy vero; nav/footer IP; staging Vercel | brief narrativo, copy vecchio sito |
| **2 — AChiM** | /servizi/assunzioni-chiavi-in-mano + /vsl + /tsl; redirect dominio AChiM | pagina ACIM + VSL/TSL trascritte |
| **3 — Conversione** | Quiz nativo + multistep /candidati + /lavora-con-noi; webhook+UTM | schemi survey, logica quiz dal brief |
| **4 — Prova sociale** | /casi-studio (3 iniziali) + /per-chi/venditori + carosello Netflix | trascrizioni YouTube |
| **5 — Completamento** | Altri servizi, altri ruoli, Cloudflare DNS, go-live, ownership Vercel a Chiara | — |

## 8. Punti aperti residui

1. Hero: confermare "Il sistema di selezione che la tua azienda merita." (proposta: sì)
2. Multistep: replicare 1:1 o evolvere l'albero?
3. I 5 dati da uniformare (§6)
4. Physics tags ruoli: sì/no (proposta: sì, è il pezzo distintivo)
5. Chatbot Railway: integrare o pensionare
6. Font: Clash Grotesk ok? (proposta: sì, già caricato e moderno)
