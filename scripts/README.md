# 📸 Scripts Instagram

Scripts per estrarre e aggiornare i post Instagram sul sito.

## 🚀 Quick Start

### Metodo Veloce (Consigliato)

1. **Copia l'HTML** da Instagram:
   - Vai su https://www.instagram.com/anima.ent/
   - Scrolla per caricare i post
   - F12 > Elements > Clicca destro su `<html>` > Copy outerHTML
   - Salva in `scripts/instagram-page.html`

2. **Esegui lo script unificato:**
   ```bash
   node scripts/scrape-and-update-instagram.js
   ```

3. **Fatto!** I post sono aggiornati.

---

### Metodo Manuale (2 Passaggi)

1. **Estrai i dati:**
   ```bash
   node scripts/extract-instagram-data.js
   ```
   - Copia il codice generato
   - Incollalo in `update-instagram-latest.js` (sostituisci l'array `posts`)

2. **Scarica e processa:**
   ```bash
   node scripts/update-instagram-latest.js
   ```

---

## 📚 Documentazione Completa

Vedi `GUIDA-SCRAPING-INSTAGRAM.md` nella root del progetto per la guida dettagliata.

---

## 📁 File

- `instagram-page.html` - HTML copiato da Instagram (da creare manualmente)
- `extract-instagram-data.js` - Estrae i dati dall'HTML
- `update-instagram-latest.js` - Scarica e processa le immagini
- `scrape-and-update-instagram.js` - Script unificato (fa tutto automaticamente)

