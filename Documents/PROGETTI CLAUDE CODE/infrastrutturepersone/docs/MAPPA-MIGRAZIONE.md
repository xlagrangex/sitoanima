# Mappa di migrazione GHL → Sito Astro (Infrastruttura Persone)

> Fonte di verità per il piano. Censimento completo via API GHL (location `8ItWj4qKDokr2jFJsySV`) + scrape delle pagine pubblicate, 12/06/2026. Dati grezzi in `docs/ghl/*.json`.
> **Contenuti estratti** (copy integrale pagine, schemi survey/form, 30 trascrizioni video ~117k parole) → **`docs/cervellone/`**.
>
> **Perimetro deciso**: tutto il front-end si rifà in Astro; GHL resta SOLO per contatti, form/submission e automazioni. I multistep condizionali li costruiamo nativi. I video si trascrivono per generare le pagine caso studio.

## Domini attivi

| Dominio | Contenuto | Destino |
|---|---|---|
| `assunzionichiaviinmano.com` (con la **i**) | funnel VSL, TSL, Quiz | resta entry Ads → punterà alle pagine equivalenti del nuovo sito |
| `infrastrutturapersone.it` | vecchio sito GHL (5 pagine) + chatbot | sostituito dal nuovo sito Astro (DNS → Cloudflare → Vercel) |
| `assunzioni-cim-production.up.railway.app` | chatbot questionario (app custom su Railway) | da valutare: integrare/linkare o pensionare |

## Asset da migrare, uno per uno

### 1. Funnel VSL — "FunnelRecruitingPerfetto" (principale)

| Step | URL attuale | Contenuto | → Nel nuovo sito |
|---|---|---|---|
| VSL Cold | `/vsl-funnelrecruitingperfetto` | Headline "Come attrarre, filtrare ed educare centinaia di candidati qualificati…", **video Wistia `65xsvg7xoh`** (la VSL), social proof "imprenditori con cui ho lavorato", 3 video YouTube casi studio (`65RJ739qv3c`, `CAaOsm7NawU`, `EqgkqFGRo1Y`), sezione "cosa è incluso" | Landing AChiM nel sito (fuori menu), stesso copy, player video, design system nuovo |
| Book A Call | `/prenota-la-chiamata-vsl-frp` | "Scegli un orario", cosa succede in consulenza (Analisi del Ruolo / Metodo / Prossimi Passi), FAQ, **widget calendario `gF1UjuH7LzWl61KAucVE`** | Pagina prenotazione: embed stesso widget booking GHL |
| Fit Sbagliato | `/fit-sbagliato-frp` | Disqualifica → opportunità freelancer ("Zero costi, 5 minuti, match mirato"), **survey `IJGcext1ZDa0vwpaVYQE`** (Survey Freelancer) | Step finale del multistep nativo: ramo "non in target" → pagina freelancer con form nativo → webhook GHL |
| Thank You | `/thank-you-page-frp` | "La chiamata è prenotata" + 3 step (email, preparati, ci vediamo) | TYP unica riusabile nel sito |

### 2. Funnel TSL (variante testuale)

Stessa struttura del VSL (book/fit/typ condivisi nei contenuti). Lo step 1 (`/tsl-frp-733561`) è una **sales letter** con storytelling forte: *"Due lunedì mattina. Due realtà diverse."* — vecchio modo vs nuovo modo — e presenta "Il team di Assunzioni Chiavi In Mano". Contiene embed della **survey `UkxjlTAQKDfFtlqugde1`** (Survey VSL-FRP = l'application/multistep attuale).
→ Diventa la variante testuale della landing AChiM; il copy "due lunedì mattina" è ottimo anche per una sezione narrativa del sito.

### 3. Funnel Quiz — "Riassumeresti l'ultimo collaboratore?" (×2: Ads + Outreach)

Pagina quiz con domande già note (dall'HTML):
1. Quante persone lavorano con te oggi?
2. In questo momento, hai bisogno di inserire qualcuno nel team?
3. Quando ti serve una persona nuova, qual è la prima cosa che fai?
4. Dove cerchi i candidati di solito?
5. Come decidi chi portare avanti nella selezione?
6. (+ domanda finale "L'ultima volta che hai assunto, com'è andata davvero?") → profilo su 4 aree, scaricabile, CTA book a call.

→ **Quiz nativo in Astro** (stessa logica a punteggi, UX migliore), submit a GHL con tag per risposta/ramo. La pagina `/bookacallquiz` (con video YouTube + calendario) si fonde con la pagina prenotazione del sito.

### 4. Sito IP attuale (5 pagine) → nuovo sito

| Pagina | Copy da salvare |
|---|---|
| Home | **"Il sistema di selezione che la tua azienda merita."** · Profilazione con AI · Selezione strutturata |
| About | "Costruiamo Sistemi per Assumere le persone Giuste" · "Metodo, non improvvisazione" · processo: Analisi ruolo → Campagne mirate → Screening avanzato → Candidati pronti |
| Services | AChiM servizio di punta: Profilazione AI **+5 documenti**, campagne Meta/LinkedIn/Indeed, test attitudinali, screening AI, **"Il sistema resta tuo per sempre"**, **"Pronto in 10 giorni, servono solo 5h del tuo tempo"** · ricorrenti: Gestione Campagne |
| Contact | form contatto + "perché sceglierci" |
| **Lavora Con Noi** | **"Non cerchiamo CV. Cerchiamo persone."** · framework persone A: *moltiplicatori / colonna portante / freno silenzioso* |

### 5. Form, survey e calendari (restano in GHL, si integrano)

| Asset GHL | ID | Integrazione nel sito |
|---|---|---|
| Form Contatto Sito Web IP | `JoKiMWpHUxIVNBVlFkfl` | rifatto nativo → webhook GHL (UTM hidden) |
| Form Calendario Sito Web IP | `12y43LMUHgP5ltX8XgdV` | assorbito dalla pagina prenotazione |
| Form Contatto/Calendario Affiliate + Standard | `wPwAZi8e9GabQK8dp8Pv`, `6ZM8BMph0SZTEngKWURr` | pagina partner (fase successiva) |
| Survey Freelancer | `IJGcext1ZDa0vwpaVYQE` | form nativo "Per i professionisti" → webhook |
| Survey VSL-FRP (application) | `UkxjlTAQKDfFtlqugde1` | base del **multistep condizionale nativo** |
| Calendario Conoscitiva AChiM | `gF1UjuH7LzWl61KAucVE` | embed widget booking nelle pagine prenotazione |
| Calendario Conoscitiva IP (60') | `8fvUTVF2bP8PNzv3ZIDS` | pagina contatti sito |
| Chiamata Telefonica con Chiara (20') | `6nryeFyfY8uURoqKvTbO` | uso interno/follow-up (no sito per ora) |
| Calendario AICM—Partner | `HHjMlvoIzd3DfAQYKDn6` | pagina partner (fase successiva) |

### 6. Video censiti (da trascrivere → pagine caso studio)

| Video | Dove | Uso |
|---|---|---|
| Wistia `65xsvg7xoh` | VSL Cold | la VSL — resta video, il copy attorno si migra |
| YouTube `65RJ739qv3c`, `CAaOsm7NawU`, `EqgkqFGRo1Y` | VSL + bookacallquiz | casi studio → trascrizione → pagine caso studio strutturate (problema → soluzione → processo → risultati) |
| Canale @chiara_divaio | YouTube | altri casi studio in arrivo (copywriter, customer success, video editor, setter, tutor) |

## Cosa NON si migra

- Survey/funnel GHL come rendering front-end (tutto rifatto in Astro)
- Chatbot Railway: decisione rimandata (non bloccante)
- Funnel Quiz-Outreach: stessa pagina del quiz Ads → un solo quiz nativo con UTM diversi

## Ordine di migrazione consigliato (guida per le fasi del piano)

1. **Sito core** (home, AChiM, casi d'uso, about, contatti+calendario, lavora-con-noi) — copy migrato e riscritto
2. **Landing AChiM** (VSL + TSL) fuori menu + TYP + pagina prenotazione — il funnel Ads punta qui
3. **Multistep nativo** (da Survey VSL-FRP) con ramo disqualifica → freelancer
4. **Quiz nativo** (da Quiz-Riassunzioni)
5. **Casi studio** da trascrizioni video
6. Partner/affiliate + chatbot (decisioni rimandate)
