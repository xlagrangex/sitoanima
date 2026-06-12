# Code components del progetto Framer (da reimplementare come isole React)

## 1. Counter (Number/Counter.tsx)

Count-up numerico usato nella sezione numeri/statistiche.

Comportamento: parte da `start`, incrementa di 1 (o 0.1 se decimal) ogni `speed` ms fino a `end`; IntersectionObserver per partire quando entra in viewport (`startOnViewport`), opzionale restart al rientro; prefix/suffix testuali (es. "+", "%"); separatore migliaia configurabile.

Reimplementazione Astro: isola React `src/components/react/Counter.tsx`, rAF-based con easing-out per fluidità, props `{ end, duration?, prefix?, suffix?, className? }`, IntersectionObserver once. I valori usati sul sito vanno letti dalla sezione numeri della home (vedi spec-home.md).

## 2. Physics (Physics.tsx)

Contenitore Matter.js: i figli cadono con gravità dentro il box, rimbalzano sulle pareti e sono trascinabili col mouse (MouseConstraint, touch incluso). Attivato quando entra in viewport (threshold 0.5). Usato per pill/tag che cadono (tipicamente about o 404).

Config originale: gravity y=1, walls su tutti i lati, friction 0.1, frictionAir 0.01, density 0.001, mouse stiffness 0.2.

Reimplementazione Astro: isola React `src/components/react/PhysicsTags.tsx` con dipendenza `matter-js`; children = array di etichette (string) renderizzate come pill; visibilità nascosta finché i corpi non sono posizionati (stile originale: visibility hidden → visible al primo frame).
