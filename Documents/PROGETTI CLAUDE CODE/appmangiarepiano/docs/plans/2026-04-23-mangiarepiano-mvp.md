# MangiarePiano MVP — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Costruire l'MVP iOS nativo di MangiarePiano (habit tracker per mangiare lentamente) come descritto in `docs/PRD.md`, pronto per TestFlight.

**Architecture:** App SwiftUI single-target, iOS 17+. Persistenza locale con SwiftData. Notifiche locali (nessun backend). 3 fasi: (1) scaffold + logica (modelli + servizi + test, no UI), (2) design system + schermate SwiftUI, (3) integrazione end-to-end + polish + TestFlight.

**Tech Stack:** Swift 5.9+, SwiftUI, SwiftData, UserNotifications, Swift Testing, Xcode 15+, iOS 17+.

**Riferimento design:** sezione 8 del PRD (design tokens concreti).

---

## Fase 1 — Scaffold, Modelli, Servizi (no UI)

Questa fase può essere completata al 100% senza mockup. Tutta logica testabile.

### Task 1: Creazione progetto Xcode

**Files:**
- Create: `MangiarePiano.xcodeproj` + tutta la struttura base

- [ ] **Step 1: Creare il progetto Xcode**

Da terminale nella root del repo:
```bash
cd "/Users/vincenzopetrone/Documents/PROGETTI CLAUDE CODE/appmangiarepiano"
```

Apri Xcode → File → New → Project → iOS → App:
- Product Name: `MangiarePiano`
- Team: (tuo Apple ID)
- Organization Identifier: `com.bizstudio` (o il tuo)
- Bundle Identifier: `com.bizstudio.mangiarepiano`
- Interface: SwiftUI
- Language: Swift
- Storage: SwiftData
- Include Tests: ✓
- Minimum Deployment: iOS 17.0

Salva nella root del repo (non in una sottocartella).

- [ ] **Step 2: Configurare struttura cartelle**

Dentro il gruppo `MangiarePiano/` del progetto, crea i gruppi (cartelle):
- `App/`
- `Models/`
- `Services/`
- `Views/Onboarding/`
- `Views/Home/`
- `Views/Timer/`
- `Views/History/`
- `Views/Settings/`
- `Design/`

Sposta `MangiarePianoApp.swift` in `App/` e `ContentView.swift` in `Views/` (poi lo sostituiremo).

- [ ] **Step 3: Commit**

```bash
git add -A
git commit -m "chore: scaffold MangiarePiano Xcode project (SwiftUI + SwiftData, iOS 17)"
git push
```

---

### Task 2: Modello `MealKind` enum

**Files:**
- Create: `MangiarePiano/Models/MealKind.swift`
- Create: `MangiarePianoTests/MealKindTests.swift`

- [ ] **Step 1: Scrivere test fallente**

`MangiarePianoTests/MealKindTests.swift`:
```swift
import Testing
@testable import MangiarePiano

struct MealKindTests {
    @Test func allCasesHaveDisplayName() {
        for kind in MealKind.allCases {
            #expect(!kind.displayName.isEmpty)
        }
    }

    @Test func breakfastDisplayName() {
        #expect(MealKind.breakfast.displayName == "Colazione")
    }

    @Test func defaultDurationForBreakfast() {
        #expect(MealKind.breakfast.defaultDurationSeconds == 600)
    }

    @Test func defaultDurationForLunch() {
        #expect(MealKind.lunch.defaultDurationSeconds == 900)
    }

    @Test func defaultDurationForSnacks() {
        #expect(MealKind.morningSnack.defaultDurationSeconds == 300)
        #expect(MealKind.afternoonSnack.defaultDurationSeconds == 300)
    }
}
```

- [ ] **Step 2: Verificare test fallisce**

In Xcode ⌘U. Expected: FAIL con "Cannot find 'MealKind' in scope".

- [ ] **Step 3: Implementare `MealKind`**

`MangiarePiano/Models/MealKind.swift`:
```swift
import Foundation

enum MealKind: String, Codable, CaseIterable, Identifiable, Sendable {
    case breakfast
    case morningSnack
    case lunch
    case afternoonSnack
    case dinner

    var id: String { rawValue }

    var displayName: String {
        switch self {
        case .breakfast: return "Colazione"
        case .morningSnack: return "Spuntino mattina"
        case .lunch: return "Pranzo"
        case .afternoonSnack: return "Spuntino pomeriggio"
        case .dinner: return "Cena"
        }
    }

    var defaultDurationSeconds: Int {
        switch self {
        case .breakfast: return 600           // 10 min
        case .morningSnack: return 300        // 5 min
        case .lunch: return 900               // 15 min
        case .afternoonSnack: return 300      // 5 min
        case .dinner: return 900              // 15 min
        }
    }

    var defaultTypicalHour: Int {
        switch self {
        case .breakfast: return 8
        case .morningSnack: return 11
        case .lunch: return 13
        case .afternoonSnack: return 17
        case .dinner: return 20
        }
    }

    var sfSymbol: String {
        switch self {
        case .breakfast: return "sun.horizon.fill"
        case .morningSnack: return "leaf.fill"
        case .lunch: return "fork.knife"
        case .afternoonSnack: return "cup.and.saucer.fill"
        case .dinner: return "moon.stars.fill"
        }
    }
}
```

- [ ] **Step 4: Test passa**

⌘U → PASS.

- [ ] **Step 5: Commit**

```bash
git add MangiarePiano/Models/MealKind.swift MangiarePianoTests/MealKindTests.swift
git commit -m "feat(models): add MealKind enum with display names and defaults"
git push
```

---

### Task 3: Modello `MealType` (SwiftData)

**Files:**
- Create: `MangiarePiano/Models/MealType.swift`
- Create: `MangiarePianoTests/MealTypeTests.swift`

- [ ] **Step 1: Test fallente**

```swift
import Testing
import SwiftData
@testable import MangiarePiano

struct MealTypeTests {
    @Test func initWithDefaults() {
        let mt = MealType(kind: .breakfast)
        #expect(mt.kind == .breakfast)
        #expect(mt.durationSeconds == 600)
        #expect(mt.windowMinutes == 120)
        #expect(mt.isEnabled == true)
    }

    @Test func windowContainsTypicalTime() throws {
        let cal = Calendar.current
        let today = cal.startOfDay(for: Date())
        let typical = cal.date(bySettingHour: 13, minute: 0, second: 0, of: today)!
        let mt = MealType(kind: .lunch, typicalStartTime: typical)

        let (start, end) = mt.window(on: today, calendar: cal)
        #expect(start == cal.date(byAdding: .minute, value: -60, to: typical))
        #expect(end == cal.date(byAdding: .minute, value: 60, to: typical))
    }
}
```

- [ ] **Step 2: Fallisce** (MealType non esiste).

- [ ] **Step 3: Implementare**

`MangiarePiano/Models/MealType.swift`:
```swift
import Foundation
import SwiftData

@Model
final class MealType {
    @Attribute(.unique) var id: UUID
    var kindRaw: String
    var typicalHour: Int          // 0-23
    var typicalMinute: Int        // 0-59
    var windowMinutes: Int        // total window (es. 120 = ±60min)
    var durationSeconds: Int
    var isEnabled: Bool
    var createdAt: Date

    var kind: MealKind {
        get { MealKind(rawValue: kindRaw) ?? .breakfast }
        set { kindRaw = newValue.rawValue }
    }

    init(
        id: UUID = UUID(),
        kind: MealKind,
        typicalStartTime: Date? = nil,
        windowMinutes: Int = 120,
        durationSeconds: Int? = nil,
        isEnabled: Bool = true
    ) {
        self.id = id
        self.kindRaw = kind.rawValue
        let cal = Calendar.current
        if let t = typicalStartTime {
            self.typicalHour = cal.component(.hour, from: t)
            self.typicalMinute = cal.component(.minute, from: t)
        } else {
            self.typicalHour = kind.defaultTypicalHour
            self.typicalMinute = 0
        }
        self.windowMinutes = windowMinutes
        self.durationSeconds = durationSeconds ?? kind.defaultDurationSeconds
        self.isEnabled = isEnabled
        self.createdAt = Date()
    }

    func typicalTime(on day: Date, calendar: Calendar = .current) -> Date {
        calendar.date(bySettingHour: typicalHour, minute: typicalMinute, second: 0, of: day) ?? day
    }

    func window(on day: Date, calendar: Calendar = .current) -> (start: Date, end: Date) {
        let typical = typicalTime(on: day, calendar: calendar)
        let half = windowMinutes / 2
        let start = calendar.date(byAdding: .minute, value: -half, to: typical)!
        let end = calendar.date(byAdding: .minute, value: half, to: typical)!
        return (start, end)
    }

    func isInWindow(_ date: Date, calendar: Calendar = .current) -> Bool {
        let day = calendar.startOfDay(for: date)
        let (start, end) = window(on: day, calendar: calendar)
        return date >= start && date <= end
    }
}
```

- [ ] **Step 4: Test passa**

- [ ] **Step 5: Commit**

```bash
git add MangiarePiano/Models/MealType.swift MangiarePianoTests/MealTypeTests.swift
git commit -m "feat(models): add MealType SwiftData model with time window logic"
git push
```

---

### Task 4: Modelli `MealSession`, `DayRecord`, `StreakState`

**Files:**
- Create: `MangiarePiano/Models/SessionStatus.swift`
- Create: `MangiarePiano/Models/MealSession.swift`
- Create: `MangiarePiano/Models/DayRecord.swift`
- Create: `MangiarePiano/Models/StreakState.swift`
- Create: `MangiarePianoTests/SessionModelsTests.swift`

- [ ] **Step 1: Test fallente**

```swift
import Testing
import Foundation
@testable import MangiarePiano

struct SessionModelsTests {
    @Test func sessionDefaultsToPending() {
        let s = MealSession(mealTypeId: UUID(), date: Date())
        #expect(s.status == .pending)
        #expect(s.startedAt == nil)
        #expect(s.completedAt == nil)
    }

    @Test func dayRecordInitial() {
        let d = DayRecord(date: Date(), mealsTotal: 3)
        #expect(!d.ringClosed)
        #expect(d.mealsCompleted == 0)
        #expect(d.mealsTotal == 3)
        #expect(!d.freezeUsed)
    }

    @Test func streakStateInitial() {
        let s = StreakState()
        #expect(s.currentStreak == 0)
        #expect(s.bestStreak == 0)
        #expect(s.freezesAvailable == 1)
    }
}
```

- [ ] **Step 2: Fallisce**

- [ ] **Step 3: Implementare**

`MangiarePiano/Models/SessionStatus.swift`:
```swift
import Foundation

enum SessionStatus: String, Codable, Sendable {
    case pending      // finestra non ancora aperta
    case active       // finestra aperta, timer non avviato
    case running      // timer in corso
    case completed    // timer completato con successo
    case aborted      // utente ha annullato
    case missed       // finestra chiusa senza completamento
}
```

`MangiarePiano/Models/MealSession.swift`:
```swift
import Foundation
import SwiftData

@Model
final class MealSession {
    @Attribute(.unique) var id: UUID
    var mealTypeId: UUID
    var date: Date                 // start-of-day del giorno di riferimento
    var startedAt: Date?
    var completedAt: Date?
    var statusRaw: String

    var status: SessionStatus {
        get { SessionStatus(rawValue: statusRaw) ?? .pending }
        set { statusRaw = newValue.rawValue }
    }

    init(
        id: UUID = UUID(),
        mealTypeId: UUID,
        date: Date,
        status: SessionStatus = .pending
    ) {
        self.id = id
        self.mealTypeId = mealTypeId
        self.date = Calendar.current.startOfDay(for: date)
        self.statusRaw = status.rawValue
    }

    var effectiveDurationSeconds: Int? {
        guard let s = startedAt, let c = completedAt else { return nil }
        return Int(c.timeIntervalSince(s))
    }
}
```

`MangiarePiano/Models/DayRecord.swift`:
```swift
import Foundation
import SwiftData

@Model
final class DayRecord {
    @Attribute(.unique) var date: Date
    var ringClosed: Bool
    var freezeUsed: Bool
    var mealsCompleted: Int
    var mealsTotal: Int

    init(date: Date, mealsTotal: Int) {
        self.date = Calendar.current.startOfDay(for: date)
        self.ringClosed = false
        self.freezeUsed = false
        self.mealsCompleted = 0
        self.mealsTotal = mealsTotal
    }

    var ringProgress: Double {
        guard mealsTotal > 0 else { return 0 }
        return Double(mealsCompleted) / Double(mealsTotal)
    }
}
```

`MangiarePiano/Models/StreakState.swift`:
```swift
import Foundation
import SwiftData

@Model
final class StreakState {
    var currentStreak: Int
    var bestStreak: Int
    var freezesAvailable: Int
    var weekAnchor: Date            // monday 00:00 of current week
    var lastComputedDay: Date?      // start-of-day dell'ultimo giorno calcolato

    init(
        currentStreak: Int = 0,
        bestStreak: Int = 0,
        freezesAvailable: Int = 1,
        weekAnchor: Date = Date.mondayOfCurrentWeek()
    ) {
        self.currentStreak = currentStreak
        self.bestStreak = bestStreak
        self.freezesAvailable = freezesAvailable
        self.weekAnchor = weekAnchor
        self.lastComputedDay = nil
    }
}

extension Date {
    static func mondayOfCurrentWeek(calendar: Calendar = .iso8601Week) -> Date {
        let now = Date()
        let components = calendar.dateComponents([.yearForWeekOfYear, .weekOfYear], from: now)
        return calendar.date(from: components) ?? now
    }
}

extension Calendar {
    static let iso8601Week: Calendar = {
        var c = Calendar(identifier: .iso8601)
        c.firstWeekday = 2 // Monday
        return c
    }()
}
```

- [ ] **Step 4: Test passa**

- [ ] **Step 5: Commit**

```bash
git add MangiarePiano/Models/ MangiarePianoTests/SessionModelsTests.swift
git commit -m "feat(models): add MealSession, DayRecord, StreakState"
git push
```

---

### Task 5: `StreakEngine` — regole streak e freeze

**Files:**
- Create: `MangiarePiano/Services/StreakEngine.swift`
- Create: `MangiarePianoTests/StreakEngineTests.swift`

- [ ] **Step 1: Test fallenti**

```swift
import Testing
import Foundation
@testable import MangiarePiano

struct StreakEngineTests {
    @Test func ringClosedIncrementsStreak() {
        let state = StreakState(currentStreak: 3, bestStreak: 5, freezesAvailable: 1)
        let day = DayRecord(date: Date(), mealsTotal: 3)
        day.mealsCompleted = 3
        day.ringClosed = true

        StreakEngine.apply(dayResult: day, to: state)

        #expect(state.currentStreak == 4)
        #expect(state.bestStreak == 5)
        #expect(state.freezesAvailable == 1)
    }

    @Test func ringClosedUpdatesBestStreak() {
        let state = StreakState(currentStreak: 5, bestStreak: 5, freezesAvailable: 1)
        let day = DayRecord(date: Date(), mealsTotal: 3)
        day.ringClosed = true

        StreakEngine.apply(dayResult: day, to: state)

        #expect(state.currentStreak == 6)
        #expect(state.bestStreak == 6)
    }

    @Test func missedDayConsumesFreeze() {
        let state = StreakState(currentStreak: 10, bestStreak: 10, freezesAvailable: 1)
        let day = DayRecord(date: Date(), mealsTotal: 3)
        day.ringClosed = false

        StreakEngine.apply(dayResult: day, to: state)

        #expect(state.currentStreak == 10)        // preserved
        #expect(state.freezesAvailable == 0)
        #expect(day.freezeUsed == true)
    }

    @Test func missedDayNoFreezeBreaksStreak() {
        let state = StreakState(currentStreak: 10, bestStreak: 10, freezesAvailable: 0)
        let day = DayRecord(date: Date(), mealsTotal: 3)
        day.ringClosed = false

        StreakEngine.apply(dayResult: day, to: state)

        #expect(state.currentStreak == 0)
        #expect(state.bestStreak == 10)
        #expect(day.freezeUsed == false)
    }

    @Test func weeklyFreezeReset() {
        let cal = Calendar.iso8601Week
        let prevMonday = cal.date(byAdding: .weekOfYear, value: -1, to: Date.mondayOfCurrentWeek())!
        let state = StreakState(freezesAvailable: 0)
        state.weekAnchor = prevMonday

        StreakEngine.resetWeeklyFreezeIfNeeded(state: state, now: Date())

        #expect(state.freezesAvailable == 1)
        #expect(state.weekAnchor == Date.mondayOfCurrentWeek())
    }
}
```

- [ ] **Step 2: Test falliscono**

- [ ] **Step 3: Implementare**

`MangiarePiano/Services/StreakEngine.swift`:
```swift
import Foundation

enum StreakEngine {
    /// Applica il risultato di un giorno allo stato streak. Muta sia `state` che `day` (per marcare `freezeUsed`).
    static func apply(dayResult day: DayRecord, to state: StreakState) {
        if day.ringClosed {
            state.currentStreak += 1
            if state.currentStreak > state.bestStreak {
                state.bestStreak = state.currentStreak
            }
        } else if state.freezesAvailable > 0 {
            state.freezesAvailable -= 1
            day.freezeUsed = true
            // streak preserved
        } else {
            state.currentStreak = 0
        }
        state.lastComputedDay = day.date
    }

    /// Se la settimana corrente è diversa da quella memorizzata, ricarica 1 freeze.
    static func resetWeeklyFreezeIfNeeded(state: StreakState, now: Date, calendar: Calendar = .iso8601Week) {
        let currentMonday = Date.mondayOfCurrentWeek(calendar: calendar)
        if currentMonday != state.weekAnchor {
            state.freezesAvailable = 1
            state.weekAnchor = currentMonday
        }
    }
}
```

- [ ] **Step 4: Test passano**

- [ ] **Step 5: Commit**

```bash
git add MangiarePiano/Services/StreakEngine.swift MangiarePianoTests/StreakEngineTests.swift
git commit -m "feat(services): add StreakEngine with freeze and streak rules"
git push
```

---

### Task 6: `SessionManager` — avvio/completamento/annullamento timer

**Files:**
- Create: `MangiarePiano/Services/SessionManager.swift`
- Create: `MangiarePianoTests/SessionManagerTests.swift`

- [ ] **Step 1: Test fallenti**

```swift
import Testing
import Foundation
import SwiftData
@testable import MangiarePiano

@MainActor
struct SessionManagerTests {
    func makeContainer() throws -> ModelContainer {
        let config = ModelConfiguration(isStoredInMemoryOnly: true)
        return try ModelContainer(for: MealType.self, MealSession.self, DayRecord.self, StreakState.self, configurations: config)
    }

    @Test func startSessionCreatesRunning() throws {
        let c = try makeContainer()
        let ctx = c.mainContext
        let mt = MealType(kind: .lunch)
        ctx.insert(mt)
        let sm = SessionManager(context: ctx)

        let s = try sm.startSession(for: mt, now: Date())

        #expect(s.status == .running)
        #expect(s.startedAt != nil)
        #expect(s.mealTypeId == mt.id)
    }

    @Test func completeSessionSetsCompleted() throws {
        let c = try makeContainer()
        let ctx = c.mainContext
        let mt = MealType(kind: .lunch, durationSeconds: 60)
        ctx.insert(mt)
        let sm = SessionManager(context: ctx)
        let start = Date()
        let s = try sm.startSession(for: mt, now: start)

        sm.completeSession(s, now: start.addingTimeInterval(60))

        #expect(s.status == .completed)
        #expect(s.completedAt != nil)
        #expect(s.effectiveDurationSeconds == 60)
    }

    @Test func abortSessionSetsAborted() throws {
        let c = try makeContainer()
        let ctx = c.mainContext
        let mt = MealType(kind: .lunch)
        ctx.insert(mt)
        let sm = SessionManager(context: ctx)
        let s = try sm.startSession(for: mt, now: Date())

        sm.abortSession(s, now: Date())

        #expect(s.status == .aborted)
        #expect(s.completedAt == nil)
    }
}
```

- [ ] **Step 2: Falliscono**

- [ ] **Step 3: Implementare**

`MangiarePiano/Services/SessionManager.swift`:
```swift
import Foundation
import SwiftData

@MainActor
final class SessionManager {
    enum Error: Swift.Error { case alreadyRunning }

    private let context: ModelContext

    init(context: ModelContext) {
        self.context = context
    }

    @discardableResult
    func startSession(for mealType: MealType, now: Date = Date()) throws -> MealSession {
        let day = Calendar.current.startOfDay(for: now)
        let existing = try fetchSession(mealTypeId: mealType.id, day: day)

        if let s = existing, s.status == .running {
            throw Error.alreadyRunning
        }

        let session = existing ?? MealSession(mealTypeId: mealType.id, date: day)
        if existing == nil { context.insert(session) }
        session.startedAt = now
        session.completedAt = nil
        session.status = .running
        try context.save()
        return session
    }

    func completeSession(_ session: MealSession, now: Date = Date()) {
        session.completedAt = now
        session.status = .completed
        try? context.save()
    }

    func abortSession(_ session: MealSession, now: Date = Date()) {
        session.status = .aborted
        session.startedAt = nil
        try? context.save()
    }

    func markMissed(_ session: MealSession) {
        session.status = .missed
        try? context.save()
    }

    private func fetchSession(mealTypeId: UUID, day: Date) throws -> MealSession? {
        var descriptor = FetchDescriptor<MealSession>(
            predicate: #Predicate { $0.mealTypeId == mealTypeId && $0.date == day }
        )
        descriptor.fetchLimit = 1
        return try context.fetch(descriptor).first
    }
}
```

- [ ] **Step 4: Test passano**

- [ ] **Step 5: Commit**

```bash
git add MangiarePiano/Services/SessionManager.swift MangiarePianoTests/SessionManagerTests.swift
git commit -m "feat(services): add SessionManager for meal session lifecycle"
git push
```

---

### Task 7: `DayEvaluator` — calcola chiusura ring a fine giornata

**Files:**
- Create: `MangiarePiano/Services/DayEvaluator.swift`
- Create: `MangiarePianoTests/DayEvaluatorTests.swift`

- [ ] **Step 1: Test fallente**

```swift
import Testing
import Foundation
import SwiftData
@testable import MangiarePiano

@MainActor
struct DayEvaluatorTests {
    func makeContainer() throws -> ModelContainer {
        let config = ModelConfiguration(isStoredInMemoryOnly: true)
        return try ModelContainer(for: MealType.self, MealSession.self, DayRecord.self, StreakState.self, configurations: config)
    }

    @Test func allMealsCompletedClosesRing() throws {
        let c = try makeContainer()
        let ctx = c.mainContext
        let today = Calendar.current.startOfDay(for: Date())
        let mt1 = MealType(kind: .breakfast); ctx.insert(mt1)
        let mt2 = MealType(kind: .lunch); ctx.insert(mt2)
        let s1 = MealSession(mealTypeId: mt1.id, date: today, status: .completed); ctx.insert(s1)
        let s2 = MealSession(mealTypeId: mt2.id, date: today, status: .completed); ctx.insert(s2)

        let record = try DayEvaluator.evaluate(day: today, context: ctx)

        #expect(record.ringClosed)
        #expect(record.mealsCompleted == 2)
        #expect(record.mealsTotal == 2)
    }

    @Test func partialDayDoesNotCloseRing() throws {
        let c = try makeContainer()
        let ctx = c.mainContext
        let today = Calendar.current.startOfDay(for: Date())
        let mt1 = MealType(kind: .breakfast); ctx.insert(mt1)
        let mt2 = MealType(kind: .lunch); ctx.insert(mt2)
        let s1 = MealSession(mealTypeId: mt1.id, date: today, status: .completed); ctx.insert(s1)

        let record = try DayEvaluator.evaluate(day: today, context: ctx)

        #expect(!record.ringClosed)
        #expect(record.mealsCompleted == 1)
        #expect(record.mealsTotal == 2)
    }
}
```

- [ ] **Step 2: Fallisce**

- [ ] **Step 3: Implementare**

`MangiarePiano/Services/DayEvaluator.swift`:
```swift
import Foundation
import SwiftData

@MainActor
enum DayEvaluator {
    /// Valuta il giorno dato: crea/aggiorna DayRecord in base alle sessioni e ai meal types abilitati.
    @discardableResult
    static func evaluate(day: Date, context: ModelContext) throws -> DayRecord {
        let startOfDay = Calendar.current.startOfDay(for: day)

        let enabledTypes = try context.fetch(
            FetchDescriptor<MealType>(predicate: #Predicate { $0.isEnabled })
        )
        let sessions = try context.fetch(
            FetchDescriptor<MealSession>(predicate: #Predicate { $0.date == startOfDay })
        )

        let completedIds = Set(sessions.filter { $0.statusRaw == SessionStatus.completed.rawValue }.map { $0.mealTypeId })
        let total = enabledTypes.count
        let completed = enabledTypes.filter { completedIds.contains($0.id) }.count

        let existingRecord = try context.fetch(
            FetchDescriptor<DayRecord>(predicate: #Predicate { $0.date == startOfDay })
        ).first

        let record = existingRecord ?? DayRecord(date: startOfDay, mealsTotal: total)
        if existingRecord == nil { context.insert(record) }
        record.mealsTotal = total
        record.mealsCompleted = completed
        record.ringClosed = total > 0 && completed == total
        try context.save()
        return record
    }
}
```

- [ ] **Step 4: Test passano**

- [ ] **Step 5: Commit**

```bash
git add MangiarePiano/Services/DayEvaluator.swift MangiarePianoTests/DayEvaluatorTests.swift
git commit -m "feat(services): add DayEvaluator to compute ring closure"
git push
```

---

### Task 8: `NotificationService` — schedule/cancel notifiche locali

**Files:**
- Create: `MangiarePiano/Services/NotificationService.swift`

- [ ] **Step 1: Implementare**

Questo service è difficile da test-ddare completamente senza UIApplication; lo testiamo manualmente.

`MangiarePiano/Services/NotificationService.swift`:
```swift
import Foundation
import UserNotifications

actor NotificationService {
    static let shared = NotificationService()
    private let center = UNUserNotificationCenter.current()

    private init() {}

    func requestAuthorization() async throws -> Bool {
        try await center.requestAuthorization(options: [.alert, .sound, .badge])
    }

    func scheduleDailyMealReminders(for mealTypes: [MealType]) async {
        await cancelAllMealReminders()
        for mt in mealTypes where mt.isEnabled {
            await scheduleReminder(for: mt)
        }
    }

    private func scheduleReminder(for mt: MealType) async {
        let content = UNMutableNotificationContent()
        content.title = "È ora di \(mt.kind.displayName.lowercased())"
        content.body = "Avvia il timer quando inizi a mangiare."
        content.sound = nil
        content.userInfo = ["mealTypeId": mt.id.uuidString]

        var components = DateComponents()
        components.hour = mt.typicalHour
        components.minute = mt.typicalMinute

        let trigger = UNCalendarNotificationTrigger(dateMatching: components, repeats: true)
        let id = "meal.reminder.\(mt.id.uuidString)"
        let request = UNNotificationRequest(identifier: id, content: content, trigger: trigger)
        try? await center.add(request)
    }

    func scheduleTimerCompletion(for session: MealSession, mealType: MealType, startedAt: Date) async {
        let content = UNMutableNotificationContent()
        content.title = "\(mealType.kind.displayName) lento completato ✓"
        content.body = "Hai mangiato lentamente. Ben fatto."
        content.sound = nil
        content.userInfo = ["sessionId": session.id.uuidString]

        let trigger = UNTimeIntervalNotificationTrigger(
            timeInterval: TimeInterval(mealType.durationSeconds),
            repeats: false
        )
        let id = "timer.completion.\(session.id.uuidString)"
        let request = UNNotificationRequest(identifier: id, content: content, trigger: trigger)
        try? await center.add(request)
    }

    func cancelTimerCompletion(sessionId: UUID) async {
        center.removePendingNotificationRequests(withIdentifiers: ["timer.completion.\(sessionId.uuidString)"])
    }

    func cancelAllMealReminders() async {
        let pending = await center.pendingNotificationRequests()
        let ids = pending.map(\.identifier).filter { $0.hasPrefix("meal.reminder.") }
        center.removePendingNotificationRequests(withIdentifiers: ids)
    }
}
```

- [ ] **Step 2: Aggiungere `NSUserNotificationUsageDescription`**

In `Info.plist` aggiungi:
```
NSUserNotificationUsageDescription = "MangiarePiano ti avvisa solo all'inizio di ogni pasto e a completamento del timer."
```

- [ ] **Step 3: Commit**

```bash
git add MangiarePiano/Services/NotificationService.swift MangiarePiano/Info.plist
git commit -m "feat(services): add NotificationService for local meal notifications"
git push
```

---

### Task 9: `AppDatabase` — container SwiftData + seed

**Files:**
- Create: `MangiarePiano/Services/AppDatabase.swift`
- Modify: `MangiarePiano/App/MangiarePianoApp.swift`

- [ ] **Step 1: Implementare `AppDatabase`**

```swift
import Foundation
import SwiftData

@MainActor
enum AppDatabase {
    static let schema = Schema([
        MealType.self,
        MealSession.self,
        DayRecord.self,
        StreakState.self
    ])

    static func makeContainer() throws -> ModelContainer {
        let config = ModelConfiguration(schema: schema, isStoredInMemoryOnly: false)
        return try ModelContainer(for: schema, configurations: [config])
    }

    /// Crea i 3 pasti di default (colazione/pranzo/cena) se il DB è vuoto.
    static func seedDefaultMealsIfNeeded(context: ModelContext) throws {
        let existing = try context.fetch(FetchDescriptor<MealType>())
        guard existing.isEmpty else { return }
        for kind in [MealKind.breakfast, .lunch, .dinner] {
            context.insert(MealType(kind: kind))
        }
        // Stato streak iniziale
        if try context.fetch(FetchDescriptor<StreakState>()).isEmpty {
            context.insert(StreakState())
        }
        try context.save()
    }
}
```

- [ ] **Step 2: Aggiornare `MangiarePianoApp.swift`**

```swift
import SwiftUI
import SwiftData

@main
struct MangiarePianoApp: App {
    var body: some Scene {
        WindowGroup {
            RootView()
        }
        .modelContainer(sharedContainer)
    }

    let sharedContainer: ModelContainer = {
        do { return try AppDatabase.makeContainer() }
        catch { fatalError("SwiftData container: \(error)") }
    }()
}
```

Crea `RootView.swift` temporaneo in `Views/`:
```swift
import SwiftUI

struct RootView: View {
    var body: some View {
        VStack {
            Text("MangiarePiano")
                .font(.largeTitle.bold())
            Text("Scaffold OK")
                .foregroundStyle(.secondary)
        }
        .preferredColorScheme(.dark)
    }
}
```

- [ ] **Step 3: Build e run nel simulatore**

⌘R → simulatore mostra "MangiarePiano / Scaffold OK".

- [ ] **Step 4: Commit**

```bash
git add -A
git commit -m "feat(app): wire SwiftData container and RootView scaffold"
git push
```

---

## Fase 2 — Design System + Schermate SwiftUI

### Task 10: Design tokens (colori, font, spacing)

**Files:**
- Create: `MangiarePiano/Design/Tokens.swift`

- [ ] **Step 1: Implementare**

`MangiarePiano/Design/Tokens.swift`:
```swift
import SwiftUI

enum DS {
    enum Color {
        static let background = SwiftUI.Color.black
        static let surface = SwiftUI.Color(red: 0.11, green: 0.11, blue: 0.12)      // #1C1C1E
        static let surfaceElevated = SwiftUI.Color(red: 0.17, green: 0.17, blue: 0.18) // #2C2C2E
        static let textPrimary = SwiftUI.Color.white
        static let textSecondary = SwiftUI.Color.white.opacity(0.6)
        static let textTertiary = SwiftUI.Color.white.opacity(0.3)
        static let accentStart = SwiftUI.Color(red: 1.0, green: 0.18, blue: 0.33)   // #FF2D55
        static let accentEnd = SwiftUI.Color(red: 1.0, green: 0.58, blue: 0.0)      // #FF9500
        static let success = SwiftUI.Color(red: 0.19, green: 0.82, blue: 0.35)       // #30D158
        static let ice = SwiftUI.Color(red: 0.39, green: 0.82, blue: 1.0)            // #64D2FF
        static let error = SwiftUI.Color(red: 1.0, green: 0.27, blue: 0.23)          // #FF453A
    }

    enum Gradient {
        static let accent = LinearGradient(
            colors: [DS.Color.accentStart, DS.Color.accentEnd],
            startPoint: .topLeading, endPoint: .bottomTrailing
        )
        static let accentRing = AngularGradient(
            colors: [DS.Color.accentStart, DS.Color.accentEnd, DS.Color.accentStart],
            center: .center
        )
    }

    enum Spacing {
        static let xs: CGFloat = 4
        static let sm: CGFloat = 8
        static let md: CGFloat = 16
        static let lg: CGFloat = 24
        static let xl: CGFloat = 32
        static let xxl: CGFloat = 48
    }

    enum Radius {
        static let sm: CGFloat = 8
        static let md: CGFloat = 12
        static let lg: CGFloat = 16
        static let xl: CGFloat = 24
    }

    enum Font {
        static let displayLarge = SwiftUI.Font.system(size: 34, weight: .bold, design: .default)
        static let displayMedium = SwiftUI.Font.system(size: 28, weight: .semibold, design: .default)
        static let titleLarge = SwiftUI.Font.system(size: 22, weight: .semibold)
        static let titleMedium = SwiftUI.Font.system(size: 17, weight: .semibold)
        static let body = SwiftUI.Font.system(size: 17, weight: .regular)
        static let bodySecondary = SwiftUI.Font.system(size: 15, weight: .regular)
        static let caption = SwiftUI.Font.system(size: 13, weight: .regular)
        static let timerDigits = SwiftUI.Font.system(size: 110, weight: .bold, design: .rounded).monospacedDigit()
        static let editorialSerif = SwiftUI.Font.custom("NewYork-MediumItalic", size: 22)
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add MangiarePiano/Design/Tokens.swift
git commit -m "feat(design): add design tokens (colors, gradients, typography, spacing)"
git push
```

---

### Task 11: `ProgressRing` component

**Files:**
- Create: `MangiarePiano/Design/ProgressRing.swift`

- [ ] **Step 1: Implementare**

```swift
import SwiftUI

struct ProgressRing: View {
    var progress: Double                    // 0...1
    var lineWidth: CGFloat = 20
    var breathOnComplete: Bool = true
    @State private var scale: CGFloat = 1

    var body: some View {
        ZStack {
            Circle()
                .stroke(DS.Color.surfaceElevated, lineWidth: lineWidth)

            Circle()
                .trim(from: 0, to: CGFloat(min(max(progress, 0), 1)))
                .stroke(DS.Gradient.accentRing, style: StrokeStyle(lineWidth: lineWidth, lineCap: .round))
                .rotationEffect(.degrees(-90))
                .animation(.easeInOut(duration: 0.6), value: progress)
        }
        .scaleEffect(scale)
        .onChange(of: progress) { _, newValue in
            guard breathOnComplete, newValue >= 1.0 else { return }
            withAnimation(.spring(response: 0.6, dampingFraction: 0.5)) { scale = 1.06 }
            DispatchQueue.main.asyncAfter(deadline: .now() + 0.6) {
                withAnimation(.spring(response: 0.5, dampingFraction: 0.7)) { scale = 1.0 }
            }
        }
    }
}

#Preview {
    ProgressRing(progress: 0.66)
        .frame(width: 240, height: 240)
        .padding(40)
        .background(DS.Color.background)
}
```

- [ ] **Step 2: Visual check**

Apri preview in Xcode Canvas → verifica ring con gradient, animazione fluida.

- [ ] **Step 3: Commit**

```bash
git add MangiarePiano/Design/ProgressRing.swift
git commit -m "feat(design): add ProgressRing component with accent gradient and breath animation"
git push
```

---

### Task 12: `MealCard`, `StreakBadge`, `FreezeBadge`, `PrimaryButton`

**Files:**
- Create: `MangiarePiano/Design/MealCard.swift`
- Create: `MangiarePiano/Design/Badges.swift`
- Create: `MangiarePiano/Design/PrimaryButton.swift`

- [ ] **Step 1: `MealCard.swift`**

```swift
import SwiftUI

enum MealCardState {
    case pending, active, running, completed, missed

    var label: String {
        switch self {
        case .pending: return "In attesa"
        case .active: return "Ora"
        case .running: return "In corso"
        case .completed: return "Completato"
        case .missed: return "Saltato"
        }
    }

    var color: Color {
        switch self {
        case .pending: return DS.Color.textTertiary
        case .active: return DS.Color.accentEnd
        case .running: return DS.Color.accentStart
        case .completed: return DS.Color.success
        case .missed: return DS.Color.textTertiary
        }
    }
}

struct MealCard: View {
    let kind: MealKind
    let timeLabel: String
    let state: MealCardState
    var onTap: (() -> Void)? = nil

    var body: some View {
        HStack(spacing: DS.Spacing.md) {
            Image(systemName: kind.sfSymbol)
                .font(.title2)
                .foregroundStyle(DS.Gradient.accent)
                .frame(width: 44, height: 44)
                .background(DS.Color.surfaceElevated, in: RoundedRectangle(cornerRadius: DS.Radius.md))

            VStack(alignment: .leading, spacing: 2) {
                Text(kind.displayName)
                    .font(DS.Font.titleMedium)
                    .foregroundStyle(DS.Color.textPrimary)
                Text(timeLabel)
                    .font(DS.Font.caption)
                    .foregroundStyle(DS.Color.textSecondary)
            }

            Spacer()

            Text(state.label)
                .font(DS.Font.caption)
                .foregroundStyle(state.color)
        }
        .padding(DS.Spacing.md)
        .background(DS.Color.surface, in: RoundedRectangle(cornerRadius: DS.Radius.lg))
        .contentShape(Rectangle())
        .onTapGesture { onTap?() }
    }
}
```

- [ ] **Step 2: `Badges.swift`**

```swift
import SwiftUI

struct StreakBadge: View {
    let count: Int
    var body: some View {
        HStack(spacing: 4) {
            Image(systemName: "flame.fill")
            Text("\(count)")
        }
        .font(DS.Font.titleMedium)
        .foregroundStyle(count > 0 ? DS.Gradient.accent : AnyShapeStyle(DS.Color.textTertiary))
        .padding(.horizontal, DS.Spacing.md)
        .padding(.vertical, DS.Spacing.sm)
        .background(DS.Color.surface, in: Capsule())
    }
}

struct FreezeBadge: View {
    let available: Int
    var body: some View {
        HStack(spacing: 4) {
            Image(systemName: "shield.fill")
            Text("\(available)")
        }
        .font(DS.Font.caption)
        .foregroundStyle(available > 0 ? DS.Color.ice : DS.Color.textTertiary)
        .padding(.horizontal, DS.Spacing.sm)
        .padding(.vertical, 6)
        .background(DS.Color.surface, in: Capsule())
    }
}
```

- [ ] **Step 3: `PrimaryButton.swift`**

```swift
import SwiftUI

struct PrimaryButton: View {
    let title: String
    var systemImage: String? = nil
    let action: () -> Void

    var body: some View {
        Button(action: action) {
            HStack {
                if let sys = systemImage { Image(systemName: sys) }
                Text(title).font(DS.Font.titleMedium.weight(.semibold))
            }
            .frame(maxWidth: .infinity)
            .frame(height: 56)
            .background(DS.Gradient.accent, in: RoundedRectangle(cornerRadius: DS.Radius.lg))
            .foregroundStyle(.white)
        }
        .buttonStyle(.plain)
    }
}
```

- [ ] **Step 4: Commit**

```bash
git add MangiarePiano/Design/MealCard.swift MangiarePiano/Design/Badges.swift MangiarePiano/Design/PrimaryButton.swift
git commit -m "feat(design): add MealCard, badges, PrimaryButton components"
git push
```

---

### Task 13: Home screen — `HomeView`

**Files:**
- Create: `MangiarePiano/Views/Home/HomeView.swift`
- Create: `MangiarePiano/Views/Home/HomeViewModel.swift`

- [ ] **Step 1: `HomeViewModel.swift`**

```swift
import Foundation
import SwiftData

@MainActor
@Observable
final class HomeViewModel {
    var mealTypes: [MealType] = []
    var todaySessions: [MealSession] = []
    var streakState: StreakState?
    var todayRecord: DayRecord?

    private let context: ModelContext

    init(context: ModelContext) {
        self.context = context
    }

    func refresh(now: Date = Date()) {
        do {
            mealTypes = try context.fetch(FetchDescriptor<MealType>(
                predicate: #Predicate { $0.isEnabled },
                sortBy: [SortDescriptor(\.typicalHour), SortDescriptor(\.typicalMinute)]
            ))
            let today = Calendar.current.startOfDay(for: now)
            todaySessions = try context.fetch(FetchDescriptor<MealSession>(
                predicate: #Predicate { $0.date == today }
            ))
            streakState = try context.fetch(FetchDescriptor<StreakState>()).first
            todayRecord = try context.fetch(FetchDescriptor<DayRecord>(
                predicate: #Predicate { $0.date == today }
            )).first
            if let state = streakState {
                StreakEngine.resetWeeklyFreezeIfNeeded(state: state, now: now)
                try context.save()
            }
        } catch {
            print("HomeViewModel refresh error: \(error)")
        }
    }

    func session(for mealType: MealType) -> MealSession? {
        todaySessions.first { $0.mealTypeId == mealType.id }
    }

    func state(for mealType: MealType, now: Date = Date()) -> MealCardState {
        let session = session(for: mealType)
        if let s = session {
            switch s.status {
            case .running: return .running
            case .completed: return .completed
            case .missed, .aborted: return .missed
            default: break
            }
        }
        let (start, end) = mealType.window(on: Calendar.current.startOfDay(for: now))
        if now < start { return .pending }
        if now > end { return .missed }
        return .active
    }

    var progress: Double {
        guard !mealTypes.isEmpty else { return 0 }
        let completed = todaySessions.filter { $0.status == .completed }.count
        return Double(completed) / Double(mealTypes.count)
    }

    func activeMealType(now: Date = Date()) -> MealType? {
        mealTypes.first { state(for: $0, now: now) == .active }
    }
}
```

- [ ] **Step 2: `HomeView.swift`**

```swift
import SwiftUI
import SwiftData

struct HomeView: View {
    @Environment(\.modelContext) private var context
    @State private var vm: HomeViewModel?
    @State private var showTimer: MealType?

    var body: some View {
        NavigationStack {
            ZStack {
                DS.Color.background.ignoresSafeArea()
                if let vm {
                    content(vm: vm)
                } else {
                    ProgressView().tint(.white)
                }
            }
            .navigationTitle("Oggi")
            .navigationBarTitleDisplayMode(.large)
            .toolbarColorScheme(.dark, for: .navigationBar)
            .toolbar {
                ToolbarItem(placement: .topBarTrailing) {
                    NavigationLink { SettingsView() } label: {
                        Image(systemName: "gearshape")
                            .foregroundStyle(DS.Color.textPrimary)
                    }
                }
            }
        }
        .task {
            if vm == nil { vm = HomeViewModel(context: context) }
            vm?.refresh()
        }
        .fullScreenCover(item: $showTimer) { mt in
            TimerView(mealType: mt) {
                showTimer = nil
                vm?.refresh()
            }
        }
        .preferredColorScheme(.dark)
    }

    @ViewBuilder
    private func content(vm: HomeViewModel) -> some View {
        ScrollView {
            VStack(spacing: DS.Spacing.xl) {
                VStack(spacing: DS.Spacing.md) {
                    ProgressRing(progress: vm.progress)
                        .frame(width: 240, height: 240)
                        .padding(.top, DS.Spacing.lg)
                    HStack(spacing: DS.Spacing.sm) {
                        StreakBadge(count: vm.streakState?.currentStreak ?? 0)
                        FreezeBadge(available: vm.streakState?.freezesAvailable ?? 0)
                    }
                    if vm.todayRecord?.freezeUsed == true {
                        Text("🛡 Freeze usato ieri")
                            .font(DS.Font.caption)
                            .foregroundStyle(DS.Color.ice)
                    }
                }

                VStack(spacing: DS.Spacing.sm) {
                    ForEach(vm.mealTypes) { mt in
                        MealCard(
                            kind: mt.kind,
                            timeLabel: timeLabel(for: mt),
                            state: vm.state(for: mt)
                        ) {
                            if vm.state(for: mt) == .active {
                                showTimer = mt
                            }
                        }
                    }
                }
                .padding(.horizontal, DS.Spacing.md)

                if let active = vm.activeMealType() {
                    PrimaryButton(title: "Avvia timer — \(active.kind.displayName)", systemImage: "play.fill") {
                        showTimer = active
                    }
                    .padding(.horizontal, DS.Spacing.md)
                }
            }
            .padding(.bottom, DS.Spacing.xl)
        }
    }

    private func timeLabel(for mt: MealType) -> String {
        String(format: "%02d:%02d", mt.typicalHour, mt.typicalMinute)
    }
}
```

- [ ] **Step 3: Aggiornare `RootView` temporaneamente**

```swift
struct RootView: View {
    var body: some View {
        HomeView()
    }
}
```

- [ ] **Step 4: Build + run simulatore**

⌘R. Simulatore: home con ring vuoto, 3 pasti (dopo seed), streak 0, freeze 1.

- [ ] **Step 5: Commit**

```bash
git add MangiarePiano/Views/Home/
git commit -m "feat(views): add HomeView with ring, meal cards, streak and freeze badges"
git push
```

---

### Task 14: Timer screen — `TimerView`

**Files:**
- Create: `MangiarePiano/Views/Timer/TimerView.swift`
- Create: `MangiarePiano/Views/Timer/TimerViewModel.swift`

- [ ] **Step 1: `TimerViewModel.swift`**

```swift
import Foundation
import SwiftData
import SwiftUI

@MainActor
@Observable
final class TimerViewModel {
    let mealType: MealType
    var remainingSeconds: Int
    var isRunning: Bool = false
    var isCompleted: Bool = false
    private var session: MealSession?
    private var timer: Timer?
    private let context: ModelContext
    private let sessionManager: SessionManager
    private let startedAt: Date

    init(mealType: MealType, context: ModelContext) {
        self.mealType = mealType
        self.remainingSeconds = mealType.durationSeconds
        self.context = context
        self.sessionManager = SessionManager(context: context)
        self.startedAt = Date()
    }

    func start() {
        do {
            session = try sessionManager.startSession(for: mealType, now: startedAt)
        } catch {
            print("start error: \(error)")
            return
        }
        isRunning = true
        Task { await NotificationService.shared.scheduleTimerCompletion(for: session!, mealType: mealType, startedAt: startedAt) }
        timer = Timer.scheduledTimer(withTimeInterval: 1.0, repeats: true) { [weak self] _ in
            Task { @MainActor in self?.tick() }
        }
    }

    private func tick() {
        remainingSeconds -= 1
        if remainingSeconds <= 0 {
            complete()
        }
    }

    func complete() {
        timer?.invalidate()
        timer = nil
        isRunning = false
        isCompleted = true
        if let s = session {
            sessionManager.completeSession(s)
            try? DayEvaluator.evaluate(day: s.date, context: context)
            let haptic = UINotificationFeedbackGenerator()
            haptic.notificationOccurred(.success)
        }
    }

    func abort() {
        timer?.invalidate()
        timer = nil
        isRunning = false
        if let s = session {
            Task { await NotificationService.shared.cancelTimerCompletion(sessionId: s.id) }
            sessionManager.abortSession(s)
        }
    }

    var formattedTime: String {
        let m = remainingSeconds / 60
        let s = remainingSeconds % 60
        return String(format: "%02d:%02d", m, s)
    }
}
```

- [ ] **Step 2: `TimerView.swift`**

```swift
import SwiftUI

struct TimerView: View {
    let mealType: MealType
    let onDismiss: () -> Void
    @Environment(\.modelContext) private var context
    @State private var vm: TimerViewModel?
    @State private var phraseIndex: Int = 0
    @State private var breathScale: CGFloat = 1.0

    private let phrases = [
        "respira",
        "un altro boccone",
        "nessuna fretta",
        "assapora",
        "rallenta",
        "presente",
        "mastica piano",
        "goditelo"
    ]

    var body: some View {
        ZStack {
            DS.Color.background.ignoresSafeArea()
            if let vm {
                if vm.isCompleted {
                    CompletionView(mealKind: mealType.kind, onDismiss: onDismiss)
                } else {
                    running(vm: vm)
                }
            }
        }
        .preferredColorScheme(.dark)
        .task {
            if vm == nil {
                let new = TimerViewModel(mealType: mealType, context: context)
                new.start()
                vm = new
            }
            startBreathAnimation()
            startPhraseRotation()
        }
    }

    @ViewBuilder
    private func running(vm: TimerViewModel) -> some View {
        VStack(spacing: DS.Spacing.xxl) {
            Spacer()
            Text(mealType.kind.displayName)
                .font(DS.Font.titleMedium)
                .foregroundStyle(DS.Color.textSecondary)

            ZStack {
                Circle()
                    .stroke(DS.Color.surfaceElevated, lineWidth: 2)
                    .scaleEffect(breathScale)
                    .frame(width: 320, height: 320)
                Text(vm.formattedTime)
                    .font(DS.Font.timerDigits)
                    .foregroundStyle(DS.Gradient.accent)
            }

            Text(phrases[phraseIndex])
                .font(DS.Font.editorialSerif)
                .italic()
                .foregroundStyle(DS.Color.textSecondary)
                .id(phraseIndex)
                .transition(.opacity)

            Spacer()

            Button("Annulla") {
                vm.abort()
                onDismiss()
            }
            .font(DS.Font.bodySecondary)
            .foregroundStyle(DS.Color.textTertiary)
            .padding(.bottom, DS.Spacing.xl)
        }
    }

    private func startBreathAnimation() {
        withAnimation(.easeInOut(duration: 4.0).repeatForever(autoreverses: true)) {
            breathScale = 1.08
        }
    }

    private func startPhraseRotation() {
        Timer.scheduledTimer(withTimeInterval: 5.0, repeats: true) { _ in
            withAnimation(.easeInOut(duration: 0.5)) {
                phraseIndex = (phraseIndex + 1) % phrases.count
            }
        }
    }
}

struct CompletionView: View {
    let mealKind: MealKind
    let onDismiss: () -> Void

    var body: some View {
        VStack(spacing: DS.Spacing.xl) {
            Spacer()
            Image(systemName: "checkmark.circle.fill")
                .font(.system(size: 80))
                .foregroundStyle(DS.Gradient.accent)
            Text("\(mealKind.displayName) lento completato")
                .font(DS.Font.displayMedium)
                .foregroundStyle(DS.Color.textPrimary)
                .multilineTextAlignment(.center)
            Text("Ben fatto.")
                .font(DS.Font.editorialSerif)
                .italic()
                .foregroundStyle(DS.Color.textSecondary)
            Spacer()
            PrimaryButton(title: "Torna alla home") { onDismiss() }
                .padding(.horizontal, DS.Spacing.md)
                .padding(.bottom, DS.Spacing.xl)
        }
    }
}
```

- [ ] **Step 3: Build + run**

⌘R → home → tap meal card attiva → timer parte, cerchio respira, frasi ruotano. (Per testing rapido abbassa temporaneamente `durationSeconds` a 15s modificando defaults).

- [ ] **Step 4: Commit**

```bash
git add MangiarePiano/Views/Timer/
git commit -m "feat(views): add TimerView with breathing ring and rotating editorial phrases"
git push
```

---

### Task 15: History — `HistoryView` e `DayDetailView`

**Files:**
- Create: `MangiarePiano/Views/History/HistoryView.swift`
- Create: `MangiarePiano/Views/History/DayDetailView.swift`

- [ ] **Step 1: `HistoryView.swift`** — vista calendario mensile

```swift
import SwiftUI
import SwiftData

struct HistoryView: View {
    @Environment(\.modelContext) private var context
    @State private var monthOffset: Int = 0
    @State private var records: [DayRecord] = []

    var body: some View {
        ZStack {
            DS.Color.background.ignoresSafeArea()
            ScrollView {
                VStack(spacing: DS.Spacing.lg) {
                    header
                    calendarGrid
                }
                .padding(.horizontal, DS.Spacing.md)
                .padding(.top, DS.Spacing.md)
            }
        }
        .navigationTitle("Storico")
        .navigationBarTitleDisplayMode(.large)
        .toolbarColorScheme(.dark, for: .navigationBar)
        .task(id: monthOffset) { await loadRecords() }
        .preferredColorScheme(.dark)
    }

    private var header: some View {
        HStack {
            Button { monthOffset -= 1 } label: {
                Image(systemName: "chevron.left").foregroundStyle(DS.Color.textPrimary)
            }
            Spacer()
            Text(currentMonthLabel)
                .font(DS.Font.titleMedium)
                .foregroundStyle(DS.Color.textPrimary)
            Spacer()
            Button { monthOffset += 1 } label: {
                Image(systemName: "chevron.right").foregroundStyle(DS.Color.textPrimary)
            }
            .disabled(monthOffset >= 0)
        }
        .padding(.horizontal, DS.Spacing.md)
    }

    private var calendarGrid: some View {
        let days = daysOfMonth()
        return LazyVGrid(columns: Array(repeating: GridItem(.flexible(), spacing: 8), count: 7), spacing: 8) {
            ForEach(days, id: \.self) { day in
                if let day {
                    NavigationLink { DayDetailView(day: day) } label: {
                        dayDot(for: day)
                    }
                } else {
                    Color.clear.frame(height: 40)
                }
            }
        }
    }

    private func dayDot(for day: Date) -> some View {
        let record = records.first { Calendar.current.isDate($0.date, inSameDayAs: day) }
        let isFuture = day > Date()
        let isToday = Calendar.current.isDateInToday(day)

        let fill: Color = {
            if isFuture { return .clear }
            if let r = record {
                if r.ringClosed { return DS.Color.success }
                if r.freezeUsed { return DS.Color.ice }
                return DS.Color.textTertiary.opacity(0.4)
            }
            if isToday { return DS.Color.textTertiary.opacity(0.3) }
            return DS.Color.textTertiary.opacity(0.4)
        }()

        return VStack(spacing: 4) {
            Text("\(Calendar.current.component(.day, from: day))")
                .font(DS.Font.caption)
                .foregroundStyle(isFuture ? DS.Color.textTertiary : DS.Color.textPrimary)
            Circle()
                .fill(fill)
                .frame(width: 10, height: 10)
        }
        .frame(height: 40)
    }

    private var currentMonthLabel: String {
        let cal = Calendar.current
        let date = cal.date(byAdding: .month, value: monthOffset, to: Date()) ?? Date()
        let fmt = DateFormatter()
        fmt.locale = Locale(identifier: "it_IT")
        fmt.dateFormat = "LLLL yyyy"
        return fmt.string(from: date).capitalized
    }

    private func daysOfMonth() -> [Date?] {
        let cal = Calendar(identifier: .iso8601)
        var c = cal
        c.firstWeekday = 2
        let base = c.date(byAdding: .month, value: monthOffset, to: Date()) ?? Date()
        let comps = c.dateComponents([.year, .month], from: base)
        guard let first = c.date(from: comps), let range = c.range(of: .day, in: .month, for: first) else { return [] }
        let weekdayOfFirst = c.component(.weekday, from: first)   // 1=Sun in gregorian…
        let offset = (weekdayOfFirst + 5) % 7                     // convert to Mon=0 index
        var result: [Date?] = Array(repeating: nil, count: offset)
        for day in range {
            if let d = c.date(byAdding: .day, value: day - 1, to: first) {
                result.append(d)
            }
        }
        while result.count % 7 != 0 { result.append(nil) }
        return result
    }

    private func loadRecords() async {
        do {
            records = try context.fetch(FetchDescriptor<DayRecord>())
        } catch { print(error) }
    }
}
```

- [ ] **Step 2: `DayDetailView.swift`**

```swift
import SwiftUI
import SwiftData

struct DayDetailView: View {
    let day: Date
    @Environment(\.modelContext) private var context
    @State private var sessions: [MealSession] = []
    @State private var mealTypes: [MealType] = []

    var body: some View {
        ZStack {
            DS.Color.background.ignoresSafeArea()
            ScrollView {
                VStack(spacing: DS.Spacing.md) {
                    ForEach(mealTypes) { mt in
                        let s = sessions.first { $0.mealTypeId == mt.id }
                        HStack {
                            Image(systemName: mt.kind.sfSymbol)
                                .foregroundStyle(DS.Gradient.accent)
                            VStack(alignment: .leading) {
                                Text(mt.kind.displayName)
                                    .font(DS.Font.titleMedium)
                                    .foregroundStyle(DS.Color.textPrimary)
                                Text(statusLabel(session: s))
                                    .font(DS.Font.caption)
                                    .foregroundStyle(DS.Color.textSecondary)
                            }
                            Spacer()
                            if let secs = s?.effectiveDurationSeconds {
                                Text("\(secs / 60):\(String(format: "%02d", secs % 60))")
                                    .font(DS.Font.caption.monospacedDigit())
                                    .foregroundStyle(DS.Color.textSecondary)
                            }
                        }
                        .padding(DS.Spacing.md)
                        .background(DS.Color.surface, in: RoundedRectangle(cornerRadius: DS.Radius.lg))
                    }
                }
                .padding(DS.Spacing.md)
            }
        }
        .navigationTitle(dateLabel)
        .navigationBarTitleDisplayMode(.inline)
        .toolbarColorScheme(.dark, for: .navigationBar)
        .task { await load() }
        .preferredColorScheme(.dark)
    }

    private var dateLabel: String {
        let fmt = DateFormatter()
        fmt.locale = Locale(identifier: "it_IT")
        fmt.dateStyle = .long
        return fmt.string(from: day)
    }

    private func statusLabel(session: MealSession?) -> String {
        guard let s = session else { return "Non registrato" }
        switch s.status {
        case .completed: return "Completato"
        case .running: return "In corso"
        case .aborted: return "Annullato"
        case .missed: return "Saltato"
        case .pending, .active: return "In attesa"
        }
    }

    private func load() async {
        let startOfDay = Calendar.current.startOfDay(for: day)
        do {
            sessions = try context.fetch(FetchDescriptor<MealSession>(
                predicate: #Predicate { $0.date == startOfDay }
            ))
            mealTypes = try context.fetch(FetchDescriptor<MealType>(
                sortBy: [SortDescriptor(\.typicalHour)]
            ))
        } catch { print(error) }
    }
}
```

- [ ] **Step 3: Commit**

```bash
git add MangiarePiano/Views/History/
git commit -m "feat(views): add HistoryView calendar and DayDetailView"
git push
```

---

### Task 16: Settings — `SettingsView`, `MealEditView`

**Files:**
- Create: `MangiarePiano/Views/Settings/SettingsView.swift`
- Create: `MangiarePiano/Views/Settings/MealEditView.swift`

- [ ] **Step 1: `SettingsView.swift`**

```swift
import SwiftUI
import SwiftData

struct SettingsView: View {
    @Environment(\.modelContext) private var context
    @State private var mealTypes: [MealType] = []

    var body: some View {
        ZStack {
            DS.Color.background.ignoresSafeArea()
            List {
                Section("Pasti") {
                    ForEach(mealTypes) { mt in
                        NavigationLink { MealEditView(mealType: mt) } label: {
                            HStack {
                                Image(systemName: mt.kind.sfSymbol).foregroundStyle(DS.Gradient.accent)
                                Text(mt.kind.displayName)
                                Spacer()
                                if !mt.isEnabled {
                                    Text("Disattivato").foregroundStyle(DS.Color.textTertiary)
                                }
                            }
                        }
                    }
                }
                Section("Info") {
                    HStack { Text("Versione"); Spacer(); Text(appVersion).foregroundStyle(DS.Color.textSecondary) }
                }
            }
            .scrollContentBackground(.hidden)
        }
        .navigationTitle("Impostazioni")
        .navigationBarTitleDisplayMode(.inline)
        .toolbarColorScheme(.dark, for: .navigationBar)
        .task { await load() }
        .preferredColorScheme(.dark)
    }

    private var appVersion: String {
        Bundle.main.infoDictionary?["CFBundleShortVersionString"] as? String ?? "—"
    }

    private func load() async {
        do {
            mealTypes = try context.fetch(FetchDescriptor<MealType>(
                sortBy: [SortDescriptor(\.typicalHour)]
            ))
        } catch { print(error) }
    }
}
```

- [ ] **Step 2: `MealEditView.swift`**

```swift
import SwiftUI
import SwiftData

struct MealEditView: View {
    @Bindable var mealType: MealType
    @Environment(\.modelContext) private var context

    var body: some View {
        Form {
            Section("Attivo") {
                Toggle("Tracciamento attivo", isOn: $mealType.isEnabled)
            }
            Section("Orario tipico") {
                DatePicker("Ora", selection: Binding(
                    get: {
                        Calendar.current.date(bySettingHour: mealType.typicalHour, minute: mealType.typicalMinute, second: 0, of: Date()) ?? Date()
                    },
                    set: { newValue in
                        let cal = Calendar.current
                        mealType.typicalHour = cal.component(.hour, from: newValue)
                        mealType.typicalMinute = cal.component(.minute, from: newValue)
                    }
                ), displayedComponents: .hourAndMinute)
            }
            Section("Durata timer") {
                Stepper(value: Binding(
                    get: { mealType.durationSeconds / 60 },
                    set: { mealType.durationSeconds = $0 * 60 }
                ), in: 3...45, step: 1) {
                    Text("\(mealType.durationSeconds / 60) minuti")
                }
            }
            Section("Finestra oraria") {
                Stepper(value: $mealType.windowMinutes, in: 60...240, step: 30) {
                    Text("±\(mealType.windowMinutes / 2) minuti")
                }
            }
        }
        .scrollContentBackground(.hidden)
        .background(DS.Color.background)
        .navigationTitle(mealType.kind.displayName)
        .navigationBarTitleDisplayMode(.inline)
        .toolbarColorScheme(.dark, for: .navigationBar)
        .onDisappear {
            try? context.save()
            Task { await NotificationService.shared.scheduleDailyMealReminders(for: [mealType]) }
        }
        .preferredColorScheme(.dark)
    }
}
```

- [ ] **Step 3: Commit**

```bash
git add MangiarePiano/Views/Settings/
git commit -m "feat(views): add Settings and MealEdit views"
git push
```

---

### Task 17: Onboarding flow

**Files:**
- Create: `MangiarePiano/Views/Onboarding/OnboardingCoordinator.swift`
- Create: `MangiarePiano/Views/Onboarding/WelcomeStep.swift`
- Create: `MangiarePiano/Views/Onboarding/MealsSelectionStep.swift`
- Create: `MangiarePiano/Views/Onboarding/TimesStep.swift`
- Create: `MangiarePiano/Views/Onboarding/DurationsStep.swift`
- Create: `MangiarePiano/Views/Onboarding/NotificationsStep.swift`
- Modify: `MangiarePiano/Views/RootView.swift`

- [ ] **Step 1: Coordinator con stato persistito**

```swift
import SwiftUI
import SwiftData

@MainActor
@Observable
final class OnboardingCoordinator {
    enum Step: Int, CaseIterable { case welcome, meals, times, durations, notifications }
    var current: Step = .welcome
    var selectedKinds: Set<MealKind> = [.breakfast, .lunch, .dinner]
    var typicalTimes: [MealKind: (Int, Int)] = [:]
    var durations: [MealKind: Int] = [:]

    func next() {
        if let idx = Step.allCases.firstIndex(of: current), idx + 1 < Step.allCases.count {
            current = Step.allCases[idx + 1]
        }
    }

    func finish(context: ModelContext) async {
        try? context.fetch(FetchDescriptor<MealType>()).forEach { context.delete($0) }
        for kind in selectedKinds {
            let (h, m) = typicalTimes[kind] ?? (kind.defaultTypicalHour, 0)
            let dur = durations[kind] ?? kind.defaultDurationSeconds
            let date = Calendar.current.date(bySettingHour: h, minute: m, second: 0, of: Date())
            context.insert(MealType(kind: kind, typicalStartTime: date, durationSeconds: dur))
        }
        if try? context.fetch(FetchDescriptor<StreakState>()).isEmpty ?? true {
            context.insert(StreakState())
        }
        try? context.save()
        UserDefaults.standard.set(true, forKey: "onboarding.completed")
        let mts = (try? context.fetch(FetchDescriptor<MealType>())) ?? []
        await NotificationService.shared.scheduleDailyMealReminders(for: mts)
    }
}
```

- [ ] **Step 2: WelcomeStep**

```swift
import SwiftUI

struct WelcomeStep: View {
    let onContinue: () -> Void
    var body: some View {
        VStack(spacing: DS.Spacing.xl) {
            Spacer()
            Image(systemName: "hourglass")
                .font(.system(size: 80))
                .foregroundStyle(DS.Gradient.accent)
            Text("Mangia lentamente,\nun pasto alla volta.")
                .font(DS.Font.displayLarge)
                .foregroundStyle(DS.Color.textPrimary)
                .multilineTextAlignment(.center)
            Text("Un rituale quotidiano per rallentare.")
                .font(DS.Font.editorialSerif)
                .italic()
                .foregroundStyle(DS.Color.textSecondary)
            Spacer()
            PrimaryButton(title: "Inizia", action: onContinue)
                .padding(.horizontal, DS.Spacing.md)
                .padding(.bottom, DS.Spacing.xl)
        }
        .padding(.horizontal, DS.Spacing.md)
    }
}
```

- [ ] **Step 3: MealsSelectionStep**

```swift
import SwiftUI

struct MealsSelectionStep: View {
    @Binding var selected: Set<MealKind>
    let onContinue: () -> Void

    var body: some View {
        VStack(alignment: .leading, spacing: DS.Spacing.md) {
            Text("Quali pasti fai?")
                .font(DS.Font.displayMedium)
                .foregroundStyle(DS.Color.textPrimary)
                .padding(.top, DS.Spacing.lg)

            Text("Tutti quelli che scegli andranno completati per chiudere il ring.")
                .font(DS.Font.bodySecondary)
                .foregroundStyle(DS.Color.textSecondary)

            VStack(spacing: DS.Spacing.sm) {
                ForEach(MealKind.allCases) { kind in
                    row(for: kind)
                }
            }

            Spacer()
            PrimaryButton(title: "Continua", action: onContinue)
                .disabled(selected.isEmpty)
        }
        .padding(.horizontal, DS.Spacing.md)
        .padding(.bottom, DS.Spacing.xl)
    }

    private func row(for kind: MealKind) -> some View {
        let isOn = selected.contains(kind)
        return HStack {
            Image(systemName: kind.sfSymbol)
                .foregroundStyle(isOn ? AnyShapeStyle(DS.Gradient.accent) : AnyShapeStyle(DS.Color.textTertiary))
            Text(kind.displayName)
                .foregroundStyle(DS.Color.textPrimary)
            Spacer()
            Image(systemName: isOn ? "checkmark.circle.fill" : "circle")
                .foregroundStyle(isOn ? DS.Color.accentEnd : DS.Color.textTertiary)
        }
        .padding(DS.Spacing.md)
        .background(DS.Color.surface, in: RoundedRectangle(cornerRadius: DS.Radius.lg))
        .contentShape(Rectangle())
        .onTapGesture {
            if isOn { selected.remove(kind) } else { selected.insert(kind) }
        }
    }
}
```

- [ ] **Step 4: TimesStep**

```swift
import SwiftUI

struct TimesStep: View {
    let kinds: [MealKind]
    @Binding var times: [MealKind: (Int, Int)]
    let onContinue: () -> Void

    var body: some View {
        VStack(alignment: .leading, spacing: DS.Spacing.md) {
            Text("A che ora, tipicamente?")
                .font(DS.Font.displayMedium)
                .foregroundStyle(DS.Color.textPrimary)
                .padding(.top, DS.Spacing.lg)

            ScrollView {
                VStack(spacing: DS.Spacing.sm) {
                    ForEach(kinds) { kind in
                        pickerRow(for: kind)
                    }
                }
            }

            PrimaryButton(title: "Continua", action: onContinue)
        }
        .padding(.horizontal, DS.Spacing.md)
        .padding(.bottom, DS.Spacing.xl)
    }

    private func pickerRow(for kind: MealKind) -> some View {
        let (h, m) = times[kind] ?? (kind.defaultTypicalHour, 0)
        let date = Calendar.current.date(bySettingHour: h, minute: m, second: 0, of: Date()) ?? Date()
        return HStack {
            Image(systemName: kind.sfSymbol).foregroundStyle(DS.Gradient.accent)
            Text(kind.displayName).foregroundStyle(DS.Color.textPrimary)
            Spacer()
            DatePicker("", selection: Binding(
                get: { date },
                set: { new in
                    let cal = Calendar.current
                    times[kind] = (cal.component(.hour, from: new), cal.component(.minute, from: new))
                }
            ), displayedComponents: .hourAndMinute)
            .labelsHidden()
            .colorScheme(.dark)
        }
        .padding(DS.Spacing.md)
        .background(DS.Color.surface, in: RoundedRectangle(cornerRadius: DS.Radius.lg))
    }
}
```

- [ ] **Step 5: DurationsStep**

```swift
import SwiftUI

struct DurationsStep: View {
    let kinds: [MealKind]
    @Binding var durations: [MealKind: Int]
    let onContinue: () -> Void

    var body: some View {
        VStack(alignment: .leading, spacing: DS.Spacing.md) {
            Text("Quanto vuoi durino?")
                .font(DS.Font.displayMedium)
                .foregroundStyle(DS.Color.textPrimary)
                .padding(.top, DS.Spacing.lg)

            ScrollView {
                VStack(spacing: DS.Spacing.sm) {
                    ForEach(kinds) { kind in stepperRow(for: kind) }
                }
            }

            PrimaryButton(title: "Continua", action: onContinue)
        }
        .padding(.horizontal, DS.Spacing.md)
        .padding(.bottom, DS.Spacing.xl)
    }

    private func stepperRow(for kind: MealKind) -> some View {
        let minutes = (durations[kind] ?? kind.defaultDurationSeconds) / 60
        return HStack {
            Image(systemName: kind.sfSymbol).foregroundStyle(DS.Gradient.accent)
            VStack(alignment: .leading) {
                Text(kind.displayName).foregroundStyle(DS.Color.textPrimary)
                Text("\(minutes) minuti").font(DS.Font.caption).foregroundStyle(DS.Color.textSecondary)
            }
            Spacer()
            Stepper("", value: Binding(
                get: { minutes },
                set: { durations[kind] = $0 * 60 }
            ), in: 3...45)
            .labelsHidden()
        }
        .padding(DS.Spacing.md)
        .background(DS.Color.surface, in: RoundedRectangle(cornerRadius: DS.Radius.lg))
    }
}
```

- [ ] **Step 6: NotificationsStep**

```swift
import SwiftUI

struct NotificationsStep: View {
    let onDone: () -> Void
    @State private var isRequesting = false

    var body: some View {
        VStack(spacing: DS.Spacing.lg) {
            Spacer()
            Image(systemName: "bell.badge.fill")
                .font(.system(size: 60))
                .foregroundStyle(DS.Gradient.accent)
            Text("Attiva le notifiche")
                .font(DS.Font.displayMedium)
                .foregroundStyle(DS.Color.textPrimary)
            Text("Ti avvisiamo solo all'inizio di ogni pasto e a completamento del timer. Niente altro.")
                .font(DS.Font.bodySecondary)
                .foregroundStyle(DS.Color.textSecondary)
                .multilineTextAlignment(.center)
                .padding(.horizontal, DS.Spacing.lg)
            Spacer()
            PrimaryButton(title: isRequesting ? "..." : "Attiva notifiche") {
                Task {
                    isRequesting = true
                    _ = try? await NotificationService.shared.requestAuthorization()
                    onDone()
                }
            }
            .disabled(isRequesting)
            Button("Più tardi") { onDone() }
                .font(DS.Font.bodySecondary)
                .foregroundStyle(DS.Color.textTertiary)
                .padding(.bottom, DS.Spacing.xl)
        }
        .padding(.horizontal, DS.Spacing.md)
    }
}
```

- [ ] **Step 7: Aggiornare `RootView`**

`MangiarePiano/Views/RootView.swift`:
```swift
import SwiftUI
import SwiftData

struct RootView: View {
    @Environment(\.modelContext) private var context
    @AppStorage("onboarding.completed") private var onboardingCompleted = false
    @State private var coordinator = OnboardingCoordinator()

    var body: some View {
        Group {
            if onboardingCompleted {
                HomeView()
            } else {
                onboardingView
            }
        }
        .preferredColorScheme(.dark)
    }

    @ViewBuilder
    private var onboardingView: some View {
        ZStack {
            DS.Color.background.ignoresSafeArea()
            switch coordinator.current {
            case .welcome:
                WelcomeStep { coordinator.next() }
            case .meals:
                MealsSelectionStep(selected: $coordinator.selectedKinds) { coordinator.next() }
            case .times:
                TimesStep(
                    kinds: MealKind.allCases.filter { coordinator.selectedKinds.contains($0) },
                    times: $coordinator.typicalTimes
                ) { coordinator.next() }
            case .durations:
                DurationsStep(
                    kinds: MealKind.allCases.filter { coordinator.selectedKinds.contains($0) },
                    durations: $coordinator.durations
                ) { coordinator.next() }
            case .notifications:
                NotificationsStep {
                    Task {
                        await coordinator.finish(context: context)
                        onboardingCompleted = true
                    }
                }
            }
        }
    }
}
```

- [ ] **Step 8: Build + run**

⌘R su simulatore pulito (Erase All Content and Settings) → deve partire dal welcome → 5 step → arrivo in home con i pasti scelti.

- [ ] **Step 9: Commit**

```bash
git add MangiarePiano/Views/Onboarding/ MangiarePiano/Views/RootView.swift
git commit -m "feat(views): add 5-step onboarding flow"
git push
```

---

## Fase 3 — Integrazione, Polish, TestFlight

### Task 18: Gestione stati edge (permesso notifiche negato, fuori finestra)

**Files:**
- Modify: `MangiarePiano/Views/Home/HomeView.swift`

- [ ] **Step 1: Aggiungere banner permesso notifiche negato**

In `HomeView.content`, prima del ring aggiungi:
```swift
if notificationsDenied {
    HStack {
        Image(systemName: "bell.slash.fill").foregroundStyle(DS.Color.error)
        VStack(alignment: .leading) {
            Text("Notifiche disattivate").font(DS.Font.bodySecondary).foregroundStyle(DS.Color.textPrimary)
            Text("Non riceverai i reminder.").font(DS.Font.caption).foregroundStyle(DS.Color.textSecondary)
        }
        Spacer()
        Button("Apri") {
            if let url = URL(string: UIApplication.openSettingsURLString) {
                UIApplication.shared.open(url)
            }
        }
        .foregroundStyle(DS.Color.accentEnd)
    }
    .padding(DS.Spacing.md)
    .background(DS.Color.surface, in: RoundedRectangle(cornerRadius: DS.Radius.lg))
    .padding(.horizontal, DS.Spacing.md)
}
```

Aggiungi `@State private var notificationsDenied = false` e `.task`:
```swift
let settings = await UNUserNotificationCenter.current().notificationSettings()
notificationsDenied = settings.authorizationStatus == .denied
```

- [ ] **Step 2: Commit**

```bash
git add MangiarePiano/Views/Home/HomeView.swift
git commit -m "feat(home): add notifications-denied banner with deeplink to iOS Settings"
git push
```

---

### Task 19: Background day rollover

**Files:**
- Modify: `MangiarePiano/App/MangiarePianoApp.swift`
- Create: `MangiarePiano/Services/DayRolloverCoordinator.swift`

- [ ] **Step 1: Implementare rollover all'apertura app**

```swift
import Foundation
import SwiftData

@MainActor
enum DayRolloverCoordinator {
    static func runIfNeeded(context: ModelContext, now: Date = Date()) throws {
        guard let state = try context.fetch(FetchDescriptor<StreakState>()).first else { return }
        StreakEngine.resetWeeklyFreezeIfNeeded(state: state, now: now)

        let cal = Calendar.current
        let today = cal.startOfDay(for: now)
        let lastComputed = state.lastComputedDay ?? cal.date(byAdding: .day, value: -1, to: today)!
        var cursor = cal.date(byAdding: .day, value: 1, to: lastComputed)!
        while cursor < today {
            let record = try DayEvaluator.evaluate(day: cursor, context: context)
            StreakEngine.apply(dayResult: record, to: state)
            cursor = cal.date(byAdding: .day, value: 1, to: cursor)!
        }
        try context.save()
    }
}
```

- [ ] **Step 2: Invocare all'avvio**

In `MangiarePianoApp.swift` aggiungi `.onAppear` su `RootView`:
```swift
RootView()
    .task {
        try? DayRolloverCoordinator.runIfNeeded(context: sharedContainer.mainContext)
    }
```

- [ ] **Step 3: Commit**

```bash
git add MangiarePiano/
git commit -m "feat(app): add day rollover to compute streaks on app launch"
git push
```

---

### Task 20: Deep link da notifica → home con pasto evidenziato

**Files:**
- Modify: `MangiarePiano/App/MangiarePianoApp.swift`

- [ ] **Step 1: Notification delegate**

In `MangiarePianoApp`:
```swift
import UserNotifications

final class NotificationDelegate: NSObject, UNUserNotificationCenterDelegate {
    static let shared = NotificationDelegate()
    func userNotificationCenter(_ center: UNUserNotificationCenter, willPresent notification: UNNotification, withCompletionHandler completionHandler: @escaping (UNNotificationPresentationOptions) -> Void) {
        completionHandler([.banner])
    }
}

@main
struct MangiarePianoApp: App {
    init() {
        UNUserNotificationCenter.current().delegate = NotificationDelegate.shared
    }
    // ...
}
```

(Deep link completo con pasto evidenziato: se vuoi elaborarlo meglio, aggiungi in post-MVP — per MVP basta aprire l'app sulla home.)

- [ ] **Step 2: Commit**

```bash
git add MangiarePiano/App/
git commit -m "feat(notifications): add UNUserNotificationCenter delegate for in-app banners"
git push
```

---

### Task 21: Polish finale e testing manuale

- [ ] **Step 1: Checklist smoke test manuale**

Su simulatore nuovo:
1. Onboarding completo (5 step) → arrivo home.
2. Ring vuoto + streak 0 + freeze 1.
3. Modificare temporaneamente `defaultDurationSeconds` a `20` per test rapido → avvia timer pranzo → aspetta → notifica + ring +1/N.
4. Annulla un timer → stato "Annullato".
5. Aprire storico → mese corrente con pallino verde per oggi se completato.
6. Dettaglio giorno → durate effettive.
7. Impostazioni → cambia orario/durata di un pasto → salva.
8. Forza "skip" giornata → avanzare data simulatore di 1 giorno → freeze consumato.
9. Revoca notifiche → vedere banner in home.

- [ ] **Step 2: Ripristinare durations ai valori reali**

Rimuovi modifiche di test.

- [ ] **Step 3: Archive + TestFlight**

In Xcode:
- Product → Destination → "Any iOS Device"
- Product → Archive
- Organizer → Distribute App → App Store Connect → Upload
- Attendere processamento e distribuire via TestFlight al tuo account.

(Prerequisiti: App Store Connect app creata, certificati/profili OK.)

- [ ] **Step 4: Commit finale**

```bash
git add -A
git commit -m "chore: MVP ready for TestFlight"
git push
git tag v1.0.0-mvp
git push --tags
```

---

## Riepilogo

- **Fase 1 (Task 1-9):** scaffold + modelli + servizi logici. Tutto testabile senza UI. ~3-4 giorni.
- **Fase 2 (Task 10-17):** design tokens + componenti + 19 schermate raggruppate. ~5-7 giorni.
- **Fase 3 (Task 18-21):** integrazione + polish + TestFlight. ~2-3 giorni.

**Totale: 21 task, ~10-14 giorni di lavoro focalizzato.**

**Copertura schermate PRD:**
- Onboarding (5): Task 17
- Home (4 stati): Task 13 + Task 18
- Timer (2): Task 14
- Storico (2): Task 15
- Impostazioni (4): Task 16
- Edge (2): Task 18
