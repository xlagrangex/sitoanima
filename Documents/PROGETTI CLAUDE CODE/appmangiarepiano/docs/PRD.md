# PRD — MangiarePiano (nome provvisorio)

**Versione:** 1.0 MVP
**Data:** 2026-04-23
**Autore:** Vincenzo
**Piattaforma:** iOS nativo (SwiftUI, iOS 17+)
**Target utente:** single user (self-use)

---

## 1. Vision

App iOS che aiuta a costruire l'abitudine di **mangiare lentamente** attraverso un habit tracker classico (ring giornaliero + streak + freeze) con un **timer "invisibile"** per ogni pasto: l'utente avvia il timer, blocca il telefono, lo appoggia, e riceve una sola notifica a completamento.

**Principio di design chiave:** zero comportamenti "cringe" a tavola. Nessun feedback durante il pasto, nessuna interazione continua, nessun rumore. L'app deve essere invisibile mentre si mangia.

---

## 2. Obiettivi

- **Primario:** consolidare l'abitudine di non mangiare in fretta, con un feedback oggettivo (tempo minimo per pasto) e rinforzo positivo (streak + ring).
- **Secondario:** creare un rituale quotidiano sostenibile, con tutela da giorni storti (1 freeze/settimana).
- **Non-obiettivi:** tracking nutrizionale, diario alimentare, feature social, coaching conversazionale.

---

## 3. User flow principale

### 3.1 Primo avvio (Onboarding)

1. **Welcome** — 1 schermata con claim + "Inizia".
2. **Scelta pasti tracciati** — checkbox multipli: Colazione, Spuntino mattina, Pranzo, Spuntino pomeriggio, Cena. Default: Colazione + Pranzo + Cena.
3. **Finestra oraria per ogni pasto scelto** — time picker per ora tipica di inizio (es. colazione 07:30, pranzo 13:00, cena 20:00). La finestra è ±1h attorno a quell'ora (2h totali, es. pranzo 12:00–14:00). Dentro la finestra l'utente può avviare il timer; fuori, il pasto viene marcato "mancato" a chiusura della finestra.
4. **Durata timer per ogni pasto scelto** — stepper (minuti). Default sensati: Colazione 10', Spuntini 5', Pranzo 15', Cena 15'.
5. **Permessi notifiche** — richiesta nativa iOS con spiegazione ("Ti avvisiamo solo a inizio pasto e a completamento, max 2 notifiche al giorno per pasto").
6. **Fine onboarding** → Home.

### 3.2 Uso quotidiano

1. Arriva la finestra del pasto → notifica push: *"È ora del pranzo. Avvia il timer quando inizi a mangiare."*
2. Utente apre app → tap grosso sul pasto corrente in home → **timer parte**.
3. Utente blocca il telefono e lo appoggia. Zero feedback durante.
4. Allo scadere del timer → notifica silenziosa: *"✓ Pranzo lento completato. Ring al [X/N]."* (dove N = pasti tracciati totali del giorno, X = completati).
5. Se l'utente chiude l'app o riapre il timer prima dello scadere → il pasto **non** è completato (stato "interrotto"). Può riavviarlo.
6. A fine giornata, ring chiuso = streak +1. Altrimenti → freeze usato (se disponibile e settimana ha freeze) o streak si rompe.

---

## 4. Funzionalità MVP

### 4.1 Home
- **Ring giornaliero** (stile Apple Fitness, 1 solo anello): si riempie proporzionalmente ai pasti completati nel giorno.
- **Streak counter** con numero giorni consecutivi + icona "fiamma".
- **Freeze disponibili** per la settimana corrente (badge piccolo, es. "🛡 1").
- **Lista pasti di oggi** con stato per ciascuno: *da fare* (finestra non ancora aperta), *attivo* (finestra aperta, timer non avviato), *in corso* (timer running), *completato*, *mancato* (finestra chiusa senza completamento).
- **Tap grosso sul pasto "attivo"** (pasto la cui finestra oraria è aperta adesso e non ancora completato) → parte il timer.

### 4.2 Timer pasto
- Schermata fullscreen con countdown grande e un solo pulsante "Annulla".
- Nessun suono, nessuna vibrazione durante il countdown.
- Continua a girare anche con telefono bloccato (background task / local notification a scadenza).
- Al completamento: UNA notifica + 1 haptic leggero alla riapertura dell'app.
- Annullamento = pasto interrotto, timer azzerato, possibile riavviare nella finestra.

### 4.3 Streak & Freeze (regole "medie")
- Ring chiuso = giorno completato, streak +1.
- Giorno non completato: se c'è **freeze disponibile** nella settimana → freeze consumato **automaticamente** (no conferma utente), streak **congelato** (non +1, non si rompe). L'utente viene informato il giorno dopo in home con badge "🛡 Freeze usato ieri".
- Niente freeze rimasto → streak si rompe e riparte da 0.
- **1 freeze per settimana**, rigenera ogni lunedì 00:00 (timezone locale).
- "Best streak" salvato come trofeo anche dopo rottura.

### 4.4 Notifiche push (locali, no server)
Massimo 2 per pasto al giorno:
1. **Reminder inizio finestra** — all'inizio della finestra oraria del pasto, se non ancora avviato. Testo: *"È ora del [pasto]."*
2. **Conferma completamento** — a scadenza timer con successo. Testo: *"✓ [Pasto] lento completato."*

Niente reminder aggressivi, niente "stai mangiando troppo veloce", niente notifiche generiche.

### 4.5 Calendario storico
- Vista mensile con un pallino per giorno, colorato in base allo stato:
  - Verde pieno: ring chiuso (giorno completato, streak +1)
  - Azzurro/scudo: giorno con freeze usato (ring non chiuso ma streak preservato)
  - Grigio pieno: giorno rotto (streak si è spezzato)
  - Grigio chiaro: oggi non ancora concluso
  - Bianco: futuro
- Tap sul giorno → dettaglio: quali pasti completati, quali no, durate effettive.

### 4.6 Impostazioni
- Modifica pasti tracciati (aggiungi/rimuovi).
- Modifica orari tipici (e quindi finestre).
- Modifica durate timer per pasto.
- Toggle notifiche.
- Info / versione.

---

## 5. Fuori scope MVP

Queste funzionalità NON sono nell'MVP ma sono compatibili architetturalmente per aggiunte future:

- Apple Watch companion app
- Widget home screen con ring
- Live Activity / Dynamic Island durante timer
- Statistiche avanzate (media durata, grafici trend)
- Note/diario per pasto
- HealthKit integration
- Temi multipli / dark mode personalizzato (dark mode sistema sì, ma un solo tema curato)
- Freeze "premium" acquistabili
- Sync iCloud/CloudKit
- Account / backend

---

## 6. Architettura tecnica

### 6.1 Stack
- **Linguaggio:** Swift 5.9+
- **UI:** SwiftUI (iOS 17+)
- **Persistenza:** SwiftData (on-device, locale)
- **Notifiche:** UserNotifications framework (tutte locali, nessun server push)
- **Stato:** @Observable / @Model (SwiftData)
- **Testing:** Swift Testing framework

### 6.2 Modello dati (SwiftData)

```swift
@Model MealType {
  id: UUID
  kind: MealKind // enum: breakfast, morningSnack, lunch, afternoonSnack, dinner
  typicalStartTime: Date // solo ora:minuti
  windowMinutes: Int // default 120 (=2h totali, ±1h attorno a typicalStartTime)
  durationSeconds: Int // durata timer
  isEnabled: Bool
}

@Model MealSession {
  id: UUID
  mealTypeId: UUID
  date: Date // giorno
  startedAt: Date?
  completedAt: Date?
  status: SessionStatus // enum: pending, active, completed, aborted, missed
}

@Model DayRecord {
  date: Date // giorno
  ringClosed: Bool
  freezeUsed: Bool
  mealsCompleted: Int
  mealsTotal: Int
}

@Model StreakState {
  currentStreak: Int
  bestStreak: Int
  freezesAvailable: Int // corrente
  weekAnchor: Date // lunedì della settimana corrente
  lastComputedAt: Date
}
```

### 6.3 Logica streak/freeze
- Compute giornaliero alla mezzanotte locale (BackgroundTasks framework) o lazy all'apertura app.
- Ogni lunedì 00:00 reset `freezesAvailable = 1`.
- Al calcolo fine giornata: se ring non chiuso e freezesAvailable > 0 → decrementa freeze, streak invariato. Altrimenti streak = 0.

### 6.4 Notifiche
- Schedule di notifiche locali all'avvio app e dopo ogni modifica impostazioni.
- Per ogni pasto abilitato, schedule ricorrente giornaliero:
  - Notifica "inizio finestra" all'ora tipica (cancellata se pasto completato/attivo).
- Notifica "completamento timer" schedulata al momento dell'avvio del timer, con delay = durationSeconds.
- Al tap sulla notifica di inizio → deep link sulla home con pasto corrente evidenziato.

### 6.5 Struttura cartelle

```
MangiarePiano/
├── App/
│   └── MangiarePianoApp.swift
├── Models/
│   ├── MealType.swift
│   ├── MealSession.swift
│   ├── DayRecord.swift
│   └── StreakState.swift
├── Views/
│   ├── Onboarding/
│   ├── Home/
│   ├── Timer/
│   ├── History/
│   └── Settings/
├── Services/
│   ├── NotificationService.swift
│   ├── StreakEngine.swift
│   └── SessionManager.swift
├── Design/
│   ├── Colors.swift
│   ├── Typography.swift
│   └── Ring.swift
└── Tests/
```

---

## 7. Schermate (per Claude Design)

Elenco completo schermate da prototipare, con contenuti chiave:

### Onboarding (5 schermate)
1. **Welcome** — logo/illustrazione, claim "Mangia lentamente, un pasto alla volta.", CTA "Inizia".
2. **Quali pasti fai?** — 5 righe selezionabili con icona, 3 preselezionate.
3. **A che ora, tipicamente?** — un time picker per ogni pasto scelto, impilati.
4. **Quanto vuoi durino?** — uno stepper minuti per ogni pasto scelto.
5. **Notifiche** — spiegazione + CTA "Attiva notifiche" (trigger permesso iOS).

### Home (1 schermata, stati multipli)
6. **Home — stato base** — ring vuoto, streak 0, lista pasti di oggi tutti "da fare".
7. **Home — pasto attivo** — ring parziale, pulsante grosso "Avvia timer pranzo".
8. **Home — giornata completa** — ring pieno, animazione di chiusura, streak +1.
9. **Home — con freeze usato** — ring parziale ma streak preservato, badge "Freeze usato".

### Timer (2 schermate)
10. **Timer attivo** — countdown grande, nome pasto, pulsante "Annulla" discreto in basso.
11. **Timer completato** — schermata celebrativa minimal, "✓ Pasto lento completato", "Torna alla home".

### Storico (2 schermate)
12. **Calendario mese** — grid mensile con pallini colorati, switcher mese.
13. **Dettaglio giorno** — lista pasti di quel giorno con durate e stati.

### Impostazioni (4 schermate)
14. **Impostazioni — home** — lista sezioni: Pasti, Notifiche, Info.
15. **Gestione pasti** — toggle per ogni tipo pasto + accesso a durate/orari.
16. **Modifica pasto singolo** — orario tipico, finestra, durata timer.
17. **Info / about** — versione, link credits (minimal).

### Stati di errore / edge
18. **Permesso notifiche negato** — banner in home con CTA per aprire Impostazioni iOS.
19. **Fuori finestra** — pasto marcato come "mancato" visualmente in home.

**Totale: 19 schermate**

---

## 8. Design system (tokens concreti)

**Mood:** ispirazione Apple Fitness / Health. Dark mode only (l'app si usa spesso di sera/a tavola). Rituale calmo, non clinico.

### Colori
- **Background primario:** `#000000` (true black)
- **Superficie secondaria:** `#1C1C1E` (card, sheet)
- **Superficie terziaria:** `#2C2C2E` (elementi in evidenza su card)
- **Testo primario:** `#FFFFFF`
- **Testo secondario:** `rgba(255,255,255,0.6)`
- **Testo terziario:** `rgba(255,255,255,0.3)`
- **Accent gradient (ring, CTA principali):** `#FF2D55` → `#FF9500` (magenta → amber, Move ring Apple)
- **Success (freeze badge, giorno chiuso):** `#30D158`
- **Freeze/ice (badge freeze usato):** `#64D2FF`
- **Error:** `#FF453A`

### Tipografia
- **Titoli grandi:** SF Pro Display (iOS nativo, equivalente Inter Display)
- **Body / UI:** SF Pro Text
- **Accenti editoriali (claim onboarding, frasi rotanti timer):** New York (serif iOS nativo, equivalente Instrument Serif)
- **Numeri timer:** SF Pro Rounded, dimensione molto grande (100pt+), tabular numbers per stabilità countdown

### Componenti chiave
- **Ring giornaliero:** single ring, stroke spesso (~20pt), gradient accent lungo il cerchio, rotazione -90° (parte dall'alto), animazione "breath" al completamento (scale 1 → 1.05 → 1, 1.5s ease).
- **Meal card:** rounded 16pt, background `#1C1C1E`, icona + nome pasto + stato sulla destra.
- **CTA primario (avvia timer):** fullwidth, altezza 56pt, corner radius 16pt, background gradient accent.
- **Badge streak:** pill con icona fiamma SF Symbol + numero, sfondo `#1C1C1E`.
- **Badge freeze:** pill con SF Symbol `shield.fill` + numero, sfondo ice blue.

### Timer screen
- Countdown enorme al centro (SF Pro Rounded, tabular).
- Cerchio "respirante": stroke sottile attorno al countdown, animazione scale 1 → 1.08 → 1 su durata 4s (ritmo respiro lento).
- Micro-frasi editoriali in New York che ruotano ogni 5s con fade: "respira", "un altro boccone", "nessuna fretta", "assapora", "rallenta", "presente".
- Pulsante "Annulla" discreto in basso (testo grigio secondario, no bordo).

### Interazione
- **Aptico:** solo 1 tap leggero (`.impact(.soft)`) al completamento timer, e un `.success` notification al +1 streak. Zero haptic durante countdown.
- **Suoni:** nessuno di default.
- **Transizioni:** fade + scale 0.98→1 sulle card, spring naturale sui sheet. Nessuna animazione appariscente.

---

## 9. Metriche di successo (post-lancio, uso personale)

- Ring chiuso ≥ 5 giorni/settimana dopo 2 settimane d'uso.
- Streak medio ≥ 14 giorni nel primo mese.
- Durata media effettiva dei pasti (rilevata dai timer) stabilmente ≥ durata configurata.

---

## 10. Roadmap post-MVP (indicativa)

- v1.1: Widget home screen con ring
- v1.2: Apple Watch app (timer al polso, anello sul watch face)
- v1.3: Statistiche avanzate + grafici
- v1.4: iCloud sync (CloudKit)
- v2.0: eventuale feature sociale (condivisione streak con amici)

---

## 11. Complessità stimata MVP

**Media.** 2–4 settimane di sviluppo focalizzato per un singolo dev SwiftUI competente.

Ripartizione:
- Onboarding: 2 giorni
- Home + Ring: 3 giorni
- Timer + notifiche: 3 giorni
- Streak engine + freeze: 2 giorni
- Calendario storico: 2 giorni
- Impostazioni: 2 giorni
- Polish + test + TestFlight: 3 giorni
