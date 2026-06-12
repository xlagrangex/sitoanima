---
name: framer-replica
description: Use when the user asks to replicate, clone, or rebuild an existing website inside a Framer project open in the editor (Framer MCP connected) — triggers like "replica questo sito su Framer", "clona su Framer", "porta questo sito in Framer", "rifai questo sito nel mio progetto Framer", especially when faithful animations, scroll effects, or appear/entrance transitions are required.
---

# Framer Replica

## Overview

Replica un sito web esistente direttamente nel progetto Framer aperto, via Framer MCP. **Framer-first: niente Figma come step intermedio** — l'import Figma→Framer perde animazioni e responsive constraints; se serve il file Figma, si esporta DA Framer alla fine. I valori delle animazioni si **estraggono dal codice sorgente, mai a occhio**.

## When to Use

- Replica fedele di un sito (proprio o di riferimento) in Framer
- NON per: cloni in codice puro (HTML/React senza Framer), redesign da zero

## Vincolo IP

Struttura, layout e motion si replicano; testi, immagini e loghi proprietari entrano come placeholder "da sostituire" — salvo il sito sia dell'utente.

## Fasi (in ordine, con checkpoint)

### 1. Analisi sorgente → `replica-spec`

Produce un JSON/markdown con: token (colori, scala tipografica, spacing, radius, ombre), section map (layout, misure da `getBoundingClientRect`), inventario animazioni.

- `browser_network_requests`: font reali (woff2) e librerie animazione (GSAP/ScrollTrigger, Lenis, AOS, Swiper, Lottie) — determina la strategia motion
- CSS **dichiarato** via curl/WebFetch (keyframes, `transition`, `cubic-bezier` esatti) + computed styles via `browser_evaluate`
- `document.getAnimations({subtree:true})` → `effect.getKeyframes()` + `effect.getTiming()`
- Reveal/stagger: MutationObserver su class/style + scroll a step (~80vh) → from/to, delay, stagger (delta timestamp tra fratelli)
- Se GSAP: dump `ScrollTrigger.getAll()` con timeline e vars
- Hover: `browser_hover` + diff computed style
- Screenshot full-page a 1200/810/390px (i breakpoint nativi Framer)

### 2. Rebuild statico (Framer-first)

1. `getProjectXml` per lo stato del progetto; **prima di scrivere XML, leggere con `getNodeXml` nodi esistenti analoghi per imparare lo schema reale** — mai inventare attributi
2. Styles prima dei layer: `searchFonts` → `manageColorStyle` → `manageTextStyle` (valori per breakpoint)
3. Sezione per sezione: `createPage`/`updateXmlForNode` con i valori esatti della section map; dopo ogni sezione `getNodeXml` + `zoomIntoView` per controllo
4. Blog/listing nel sorgente → CMS collection (`createCMSCollection`/`upsertCMSItem`), non pagine statiche

### 3. Motion: nativo vs codice

| Animazione | Strada |
|---|---|
| Fade/slide/scale/stagger standard, hover, scroll transform semplice | **Effetto nativo Framer** (resta editabile nel canvas) — preferire sempre quando copre il caso |
| Split-text, scrub legato allo scroll, pin/sticky scene, marquee, cursore custom, counter | **Code component** (`createCodeFile`) con framer-motion |

Tradurre i valori estratti 1:1 (duration, delay, easing, soglia viewport). Se un effetto nativo non è impostabile via XML, dare all'utente i passi esatti da fare nel pannello (operazione di secondi).

### 4. Transizioni a comparsa custom (fase obbligatoria, non opzionale)

Non ricreare appear effects one-off per ogni sezione: costruire **un sistema riusabile** — un code component `Appear` con property controls, da usare ovunque servano entrance transitions. Così il cliente le regola dal canvas senza toccare codice.

```tsx
import { motion } from "framer-motion"
import { addPropertyControls, ControlType } from "framer"

export default function Appear({ children, direction, distance, duration, delay, easing, once }) {
    const offset = { up: { y: distance }, down: { y: -distance }, left: { x: distance }, right: { x: -distance }, none: {} }[direction]
    return (
        <motion.div
            initial={{ opacity: 0, ...offset }}
            whileInView={{ opacity: 1, x: 0, y: 0 }}
            viewport={{ once, amount: 0.3 }}
            transition={{ duration, delay, ease: easing.split(",").map(Number) }}
            style={{ width: "100%", height: "100%" }}
        >
            {children}
        </motion.div>
    )
}

addPropertyControls(Appear, {
    children: { type: ControlType.ComponentInstance },
    direction: { type: ControlType.Enum, options: ["up", "down", "left", "right", "none"], defaultValue: "up" },
    distance: { type: ControlType.Number, defaultValue: 40, min: 0, max: 200 },
    duration: { type: ControlType.Number, defaultValue: 0.8, step: 0.05 },
    delay: { type: ControlType.Number, defaultValue: 0, step: 0.05 },
    easing: { type: ControlType.String, defaultValue: "0.16,1,0.3,1" },
    once: { type: ControlType.Boolean, defaultValue: true },
})
```

Default easing/duration = i valori estratti dal sorgente. Se il sorgente non ha appear transitions (o l'utente le vuole migliori), proporre un set custom coerente col brand e farlo approvare al checkpoint.

### 5. Review — due livelli, nell'ordine

1. **Automatica**: `getProjectWebsiteUrl` → preview in Playwright, screenshot agli stessi 3 breakpoint, diff vs sorgente (pixelmatch/ImageMagick); rieseguire l'instrumentazione dinamica (getAnimations + scroll log) sul preview e diffare le spec animazioni, tolleranza ±10%
2. **Checkpoint utente (obbligatorio, mai saltare)**: presentare confronto side-by-side + tabella discrepanze (cosa è fedele, cosa approssimato e perché, cosa richiede azione manuale nell'editor) e **fermarsi ad aspettare feedback** prima di dichiarare finito. Iterare sui feedback.

### 6. Consegna + Figma opzionale

Report finale: sezioni replicate, styles creati, animazioni native vs code component, placeholder da sostituire, compromessi tecnici. **Solo ora**, se l'utente vuole il file Figma: plugin "Framer to Figma" dall'editor (azione utente, indicare i passi). Mai proporlo come step iniziale.

## Common Mistakes

| Errore | Fix |
|---|---|
| Scrivere XML Framer inventando attributi | Leggere prima `getNodeXml` di nodi esistenti |
| Stimare easing/durate guardando il sito | Estrarli da CSS dichiarato / `getTiming()` / dump GSAP |
| Passare da Figma per la parte statica | Framer-first; Figma solo come export finale opzionale |
| Appear effects ricreati a mano ovunque | Un solo component `Appear` riusabile con property controls |
| Dichiarare finito dopo la sola verifica automatica | Il checkpoint utente della fase 5 è obbligatorio |
| Copiare testi/immagini proprietari senza dirlo | Placeholder + lista "da sostituire" nel report |
