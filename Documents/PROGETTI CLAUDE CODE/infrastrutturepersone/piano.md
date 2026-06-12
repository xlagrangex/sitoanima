# Piano — Sito Infrastruttura Persone (Chiara)

> Bozza da discutere. Nessuna implementazione finché non validiamo questo documento.
> **Fondamento: la mappa di migrazione** → `docs/MAPPA-MIGRAZIONE.md` (censimento completo GHL via API + scrape: 6 funnel, 4 form, 2 survey, 4 calendari, 2 domini, video). Il piano deriva da lì.
> Altre fonti: call di brief con Chiara + base design già pronta (replica Flexio in Astro, palette viola #6248B0, logo IP).

---

## 1. Contesto e architettura del brand

- **Infrastruttura Persone (IP)** = il brand/azienda (in costituzione). È il SITO: vetrina di tutto ciò che l'azienda offre, non solo la VSL.
- **Assunzioni Chiave in Mano (AChiM)** = servizio di punta / front-end dell'offerta. Ha **dominio separato** (su GoDaddy, usato per Google Ads) che resta il punto d'ingresso indipendente del funnel → atterra su landing in stile sito, collegata/risalibile al sito IP.
- Doppio pubblico:
  - **B2B (primario)**: PMI italiane. Cliente ideale: mondo formazione/info-business. Verticale più forte: **venditori** (3 anni di ricerca quotidiana) → da far emergere ovunque senza rinunciare al posizionamento generalista.
  - **B2C/professionisti (secondario)**: freelancer e candidati → lista interna (futuro long-term: piattaforma stile Fiverr).
- Offerta: acconto iniziale a copertura spese + saldo alla prima candidatura idonea. Filtro morbido: aziende ~500k+ di fatturato. Downsell: singola figura dalla lista interna quando non ha senso costruire la macchina completa.

## 2. Posizionamento e messaggi chiave

- **Hero corto e d'impatto (max 2 righe)** — il vecchio sito ha già un'ottima base: **"Il sistema di selezione che la tua azienda merita."** Alternative da validare:
  1. "Assumi i migliori. Noi costruiamo il sistema."
  2. "Il sistema di assunzione che attrae i top talent, chiavi in mano"
  3. Tenere/raffinare quella esistente
- Stile GenFlow: una frase → capisci subito cosa fanno; numeri e brand in evidenza; pulito.
- **"Perché con noi e non da soli"**: va nella VSL (deciso), non come sezione del sito. Sul sito restano le leve indirette: "il colloquio finale resta tuo", "il sistema resta tuo per sempre", metodo vs improvvisazione.
- Tono: via di mezzo tra il formale (credibilità corporate, i siti recruiting sono "brutti e super formali") e il fresco/dinamico. Less is more.

## 3. Architettura del sito (sitemap)

```
Home
├── Servizi (mega menu)
│   ├── Assunzioni Chiave in Mano  ← flagship (pagina dedicata, gemella della landing su dominio AChiM)
│   ├── Employer Branding / Brand Identity HR  (career page stile lunagagroup, "idea di azienda più grande")
│   ├── HR in affitto  (screening e colloqui preliminari gestiti da noi; ricorrente)
│   └── Gestione campagne recruiting  (ads, ricorrente)
├── Casi d'uso (mega menu — stile SaaS)
│   ├── Venditori  ← in evidenza (verticale più forte)
│   ├── Customer care / Customer success
│   ├── Copywriter, Video editor, Setter, Tutor…  (stessa pagina-template declinata per ruolo)
│   └── [Per aziende / Per professionisti come viste alternative]
├── Casi studio
│   ├── Indice (filtrabile per ruolo, venditori in primo piano)
│   └── Dettaglio = video testimonianza (20-30') + trascritto ristrutturato:
│       problema iniziale → perché questa soluzione (e non altre) → cosa abbiamo fatto → processo → risultati
├── Per i professionisti  ("Trova lavoro con noi": form freelancer, CV, entra in lista)
├── Chi siamo  (Chiara, visione, filosofia del colloquio)
├── Prenota una chiamata  (calendario GHL)
├── Quiz: "Che imprenditore sei nell'assunzione?"  (lead magnet, vedi §5)
├── Privacy / Cookie
└── Landing funnel NON in menu (sostituiscono il copy-paste su GHL, stesso design system)
```

## 4. Punti d'ingresso e funnel (3 + 1)

1. **Prenotazione diretta** — calendario GHL embeddato nella pagina "Prenota una chiamata" e nelle CTA di fondo pagina.
2. **Multistep form condizionale** (stile quiz, branching):
   - Step base: tipologia azienda → settore → dimensione team → esperienza pregressa con recruiting online.
   - Ramificazione: il set di domande successivo dipende dalle risposte (far percepire "ti ho capito meglio di te" + autorevolezza).
   - UTM in campi nascosti, POST via webhook a GHL.
3. **Quiz lead magnet "Che imprenditore sei nell'assunzione?"** — già costruito da Chiara su GHL (6-7 domande, ogni risposta smonta un'obiezione/costruisce una credenza, profilo finale su 4 aree + consigli, scaricabile, CTA: prenota chiamata / scopri AChiM; nurturing email se non prenota).
   - **Proposta**: ricrearlo NATIVO nel sito (la survey GHL è limitata graficamente — parole di Chiara) con la stessa logica, e inviare risposte+esito a GHL via webhook/API. Riutilizzabile anche su LinkedIn/social come link tracciato.
4. **Canale professionisti** — landing freelancer (specializzazione, CV, consensi) → lista GHL separata.

## 4-bis. Inventario asset esistenti — SPOSTATO

> Censimento completo e mappa per-asset in **`docs/MAPPA-MIGRAZIONE.md`**. Qui sotto restano solo le note storiche di prima ricognizione (superate dalla mappa).

<details>
<summary>Prima ricognizione (superata)</summary>

### Vecchio sito — infrastrutturapersone.it (GHL-hosted, pubblico)
Pagine: `/home`, `/about-us`, `/services`, `/contact-us`. Copy da salvare e riusare:

- **Hero**: "Il sistema di selezione che la tua azienda merita."
- **Pilastri AChiM** (pagina servizi): Profilazione AI **+ 5 documenti personalizzati** · Campagne multipiattaforma (Meta, LinkedIn, Indeed) · Test attitudinali su misura · Prove pratiche con **screening AI automatici** · Funnel di selezione · Guida ai colloqui · **"Il sistema resta tuo per sempre"**
- **Promessa forte**: "**Pronto in 10 giorni, servono solo 5h del tuo tempo**" → ottimo per hero/sub-hero o sezione "come funziona"
- **Processo** (about): Analisi del ruolo → Campagne mirate → Screening avanzato → Candidati pronti
- **Valori**: "Metodo, non improvvisazione" · "Tecnologia al servizio della selezione"
- Servizi ricorrenti: Gestione Campagne (+ HR in affitto dalla call)

### Video / casi studio
- Canale YouTube: **@chiara_divaio** (video 20-30', casi: venditori SmartWheels, metodo Toddler, copywriter, customer success, video editor, setter, tutor). Da lì: trascrizione → pagine caso studio.

### API GoHighLevel — accesso OK
- Base URL: `https://services.msgsndr.com` (`services.leadconnector.com` non risolve più). Header: `Authorization: Bearer <PIT>` + `Version: 2021-07-28`.
- **locationId**: `8ItWj4qKDokr2jFJsySV` (sub-account Chiara).

### Inventario GHL (via API, 12/06/2026)

**Funnel (6):**
| Funnel | Step | Note migrazione |
|---|---|---|
| **VSL - FunnelRecruitingPerfetto** | VSL Cold → Book A Call → **Fit Sbagliato** → Thank You | il funnel principale AChiM; "Fit Sbagliato" = pagina disqualifica (→ downsell lista?) |
| **TSL - FunnelRecruitingPerfetto** | Sales letter testuale → Book → Fit Sbagliato → TY | variante testuale della VSL |
| **Quiz-Riassunzioni-Ads** | quiz "riassumeresti l'ultimo collaboratore" → book a call → TY | è il lead magnet quiz della call → da rifare nativo |
| **Quiz-Riassunzioni-Outreach** | 1 step | variante outreach dello stesso quiz |
| **Chatbot - Questionario** | /chat | da capire se vivo o esperimento |
| **Sito Web IP standard** | home, about-us, services, contact-us, **lavora-con-noi** | il vecchio sito; c'è anche la pagina freelancer |

**Form (4):** Contatto Calendario Affiliate · Calendario Standard · Contatto Sito Web IP · Calendario Sito Web IP
**Survey (2):** Survey Freelancer · Survey VSL-FRP
**Calendari (4):** Chiamata Telefonica con Chiara · Conoscitiva Infrastruttura Persone · Conoscitiva AICM—Partner · Conoscitiva Assunzioni Chiavi In Mano

**Mappa migrazione → sito Astro:**
- VSL/TSL funnel → landing AChiM nel sito (fuori menu), form → webhook GHL, calendario "Conoscitiva AChiM" embeddato
- Quiz-Riassunzioni → quiz nativo Astro (stessa logica) → tag/custom field in GHL
- Pagina "Fit Sbagliato" → replicare come step di disqualifica del multistep
- Sito IP standard → sostituito integralmente dal nuovo sito
- Survey Freelancer → form "Per i professionisti" nel sito
- Calendario "Partner/Affiliate" → eventuale pagina partner (fase 4)

</details>

## 5. Integrazione GoHighLevel e tracking

> Perimetro confermato: **tutto il sito è Astro**; GHL resta SOLO per contatti, form e automazioni (CRM). I multistep condizionali li costruiamo noi nel sito. I video si prendono dalle landing GHL/YouTube e si trascrivono.
> ID e widget esatti per ogni integrazione: vedi tabella §5 della mappa di migrazione.

- Form HTML nativi nel sito → webhook GHL (pattern già usato da Chiara), con **UTM in hidden fields** (persistenza in sessionStorage/localStorage per chi naviga più pagine prima di convertire).
- Calendario: embed GHL/iCal nella pagina dedicata.
- Quiz e multistep: logica client-side in Astro/React island → submit a GHL (tag/custom fields per ramo e risposta → segmentazione nurturing).
- Link tracciati per i contenuti social (redirect con UTM verso le pagine target).
- Lettura via API GHL (quando partiremo): inventario funnel/form/quiz esistenti da migrare nel sito.

## 6. Design system

- **Base già pronta**: replica Flexio in Astro 5 (questo repo) — è il template che Chiara ha scelto come riferimento estetico ("al momento è il primo come scelta", "il marquee del banner è fighissimo, lo voglio").
- Palette: **viola #6248B0** primario (già fatto), sfondi bianchi/chiari (NO dark — richiesta esplicita), accenti dalla palette esistente.
- Logo IP in nav e footer (già fatto). Font attuali Clash/Cabinet Grotesk — da confermare o cambiare.
- Effetti da mantenere/aggiungere:
  - Marquee CTA banner (c'è già) — quello che le piaceva.
  - Reveal on scroll, curtain hero, hover bottoni (ci sono già).
  - **Testimonianze "stile Netflix"**: carosello auto-scorrevole dove la card centrale si allarga e le laterali si stringono; al passaggio si ferma; card = video testimonianza. → da costruire (sezione home che rimanda ai casi studio).
  - Physics tags: riutilizzabile per i RUOLI che ricerchiamo (venditori, customer care, copywriter…) invece delle industry.
- Contenuti: tutto il copy attuale è il template inglese di Flexio → si riscrive in **italiano** sulla base del vecchio sito IP + landing GHL esistenti + questo piano.

## 7. Domini e infrastruttura

- Sito IP su **Vercel** (account fatto da Chiara, primo deploy sul mio → trasferimento ownership). Staging su dominio Vercel finché non approvato.
- Domini su GoDaddy → migrazione gestione DNS a **Cloudflare** (call insieme per il passaggio; attenzione ai record Workspace/mail già attivi).
- Dominio AChiM: resta separato come entry dei funnel/Ads → o landing autonoma stesso stile, o redirect a pagina dedicata del sito (da decidere, vedi §9).
- GHL resta il backend di automation/CRM. Nessun database nostro (eventuale futuro: Railway, già pagato da Chiara).

## 8. Fasi di lavoro proposte

Allineate all'ordine di migrazione della mappa (§"Ordine di migrazione consigliato"):

| Fase | Contenuto | Asset GHL coinvolti |
|---|---|---|
| **1 — Sito core** | Home, pagina AChiM, about, contatti + calendario, lavora-con-noi; copy migrato dal vecchio sito e riscritto | calendario Conoscitiva IP; copy 5 pagine sito |
| **2 — Funnel AChiM** | Landing VSL + TSL fuori menu, pagina prenotazione (widget `gF1Ujuh…`), TYP, redirect dominio assunzionichiaviinmano.com | funnel VSL/TSL, calendario AChiM, Wistia VSL |
| **3 — Conversione nativa** | Multistep condizionale (da Survey VSL-FRP) con ramo "Fit Sbagliato" → freelancer; quiz nativo (da Quiz-Riassunzioni); webhook + UTM | survey ×2, form contatto |
| **4 — Prova sociale** | Casi studio da trascrizioni video (3 YouTube già censiti + canale), casi d'uso per ruolo, testimonianze Netflix | video YouTube/Wistia |
| **5 — Estensioni** | Pagina partner/affiliate, chatbot Railway (decisione), DNS Cloudflare + go-live domini | form/calendari affiliate |

## 9. Punti aperti da discutere

**Risolti dalla mappa/decisioni:**
- ~~Domini~~ → `infrastrutturapersone.it` (sito) + `assunzionichiaviinmano.com` (entry Ads, **chiavi** con la i). AChiM = landing nel sito, dominio in redirect.
- ~~Quiz nativo vs embed~~ → nativo (deciso); domande già censite nella mappa.
- ~~Vecchio sito: cosa salvare~~ → tutto schedato in mappa §4 (incluso "Non cerchiamo CV. Cerchiamo persone." e il framework moltiplicatori/colonna/freno).
- ~~"Perché con noi"~~ → va in VSL.
- ~~Casi studio: dove sono i video~~ → 3 su YouTube già censiti + canale @chiara_divaio; trascrizione nostra.

**Ancora aperti:**
1. **Hero copy**: tenere "Il sistema di selezione che la tua azienda merita." o variante più corta?
2. **Multistep**: l'albero attuale è nella Survey VSL-FRP — va scaricato il dettaglio domande/rami e validato con Chiara se cambiarlo o replicarlo 1:1.
3. **Social proof**: loghi aziende utilizzabili? Numeri dichiarabili (anni, ruoli piazzati, settori)?
4. **Chi siamo**: Chiara in prima persona (foto/video) o brand impersonale?
5. **Font**: teniamo Clash Grotesk o cerchiamo qualcosa di più "corporate fresco"?
6. **Physics tags**: riusarli per i ruoli (venditori, customer care…) o togliere (less is more)?
7. **Chatbot Railway**: si integra nel nuovo sito, resta separato o si pensiona?
8. **URL**: struttura italiana confermata? (es. `/servizi/assunzioni-chiavi-in-mano`, `/casi-studio/...`)

---

*Prossimo passo dopo la discussione: congelo il piano, poi (con le API GHL che mi darai) inventario di funnel/form/quiz esistenti e si parte con la Fase 1.*
