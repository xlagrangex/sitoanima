# 📸 Guida Completa: Scraping Instagram per Aggiornare la Griglia

Questa guida ti mostra come estrarre i primi 9 post da Instagram e aggiornarli nel sito.

---

## 🎯 Panoramica del Processo

Il processo consiste in 3 passaggi principali:

1. **Estrai l'HTML** della pagina Instagram
2. **Estrai i dati** dei post (immagini, URL, testi)
3. **Scarica e processa** le immagini, aggiornando il sito

---

## 📋 Metodo Consigliato: Estrazione Automatica

### ⚠️ IMPORTANTE: Prima di iniziare

Instagram protegge i suoi contenuti, quindi devi:
- Essere **loggato** su Instagram nel browser
- Aprire la pagina del profilo **@anima.ent**
- Scrollare per caricare almeno i primi 9-12 post

---

### Passo 1: Ottieni l'HTML della Pagina Instagram

#### Opzione A: Chrome/Edge (CONSIGLIATO)

1. **Apri Chrome o Edge** e vai su: https://www.instagram.com/anima.ent/
2. **Fai login** se necessario
3. **Scrolla la pagina** per caricare almeno i primi 9-12 post (importante!)
4. Premi **F12** (o clic destro > "Ispeziona")
5. Vai nella tab **"Elements"** (o "Elementi")
6. Clicca con il tasto destro su **`<html>`** (il primo elemento nella lista)
7. Seleziona **"Copy"** > **"Copy outerHTML"**
8. **Incolla** tutto in un file di testo

#### Opzione B: Firefox

1. Apri Firefox e vai su: https://www.instagram.com/anima.ent/
2. Fai login e scrolla per caricare i post
3. Premi **F12** (o clic destro > "Ispeziona elemento")
4. Vai nella tab **"Inspector"**
5. Clicca con il tasto destro su **`<html>`**
6. Seleziona **"Copy"** > **"Copy Outer HTML"**
7. **Incolla** tutto in un file di testo

#### Opzione C: Safari

1. Apri Safari e vai su: https://www.instagram.com/anima.ent/
2. Abilita il menu sviluppatore: Preferenze > Avanzate > "Mostra menu Sviluppo"
3. Fai login e scrolla per caricare i post
4. Menu **Sviluppo** > **"Mostra Web Inspector"**
5. Clicca con il tasto destro su **`<html>`**
6. Seleziona **"Copy"** > **"Copy Outer HTML"**
7. **Incolla** tutto in un file di testo

---

### Passo 2: Salva l'HTML nel Progetto

1. Crea un file chiamato **`instagram-page.html`** nella cartella **`scripts/`**
2. **Incolla** tutto l'HTML copiato nel file
3. **Salva** il file

**Percorso completo:** `scripts/instagram-page.html`

---

### Passo 3: Esegui lo Script di Estrazione

Apri il terminale nella cartella del progetto ed esegui:

```bash
node scripts/extract-instagram-data.js
```

**Cosa fa lo script:**
- ✅ Legge l'HTML salvato
- ✅ Estrae automaticamente i dati dei post (ID, immagine, testo, URL, tipo)
- ✅ Genera il codice JavaScript pronto da usare
- ✅ Salva i dati in `scripts/extracted-posts.json`

**Output atteso:**
```
📖 Leggendo file HTML...

🔍 Estraendo dati dai post Instagram...

Trovati X link ai post

[1] ✓ Estratto: DQ7cX5Pjeao (carousel)
[2] ✓ Estratto: DQ4zF8mDRwE (reel)
...

✅ Estratti 9 post (mostrando i 9 più recenti)

📋 CODICE DA INSERIRE IN update-instagram-latest.js:
────────────────────────────────────────────────────────
  const posts = [
    {
      id: "...",
      image: "...",
      ...
    },
    ...
  ];
────────────────────────────────────────────────────────

💾 Dati salvati anche in: scripts/extracted-posts.json
```

---

### Passo 4: Aggiorna lo Script Principale

1. **Apri** il file `scripts/update-instagram-latest.js`
2. **Trova** l'array `posts` (circa riga 105)
3. **Sostituisci** tutto l'array con il codice generato nello step precedente
4. **Salva** il file

**Esempio:**
```javascript
// Sostituisci questa sezione (righe ~105-169):
const posts = [
  {
    id: "DQ7cX5Pjeao",
    image: "https://...",
    // ... vecchi dati
  },
  // ...
];

// Con il nuovo codice generato:
const posts = [
  {
    id: "NUOVO_ID",
    image: "https://...",
    // ... nuovi dati
  },
  // ...
];
```

---

### Passo 5: Scarica e Processa le Immagini

Esegui lo script che scarica le immagini e le processa:

```bash
node scripts/update-instagram-latest.js
```

**Cosa fa lo script:**
- ✅ Scarica tutte le immagini dai post
- ✅ Le salva in `public/instagram-posts/`
- ✅ Le ritaglia in formato 3:4 (per la griglia)
- ✅ Le salva in `public/instagram-posts-cropped/`
- ✅ Aggiorna `data/instagram-posts.json` con i nuovi dati

**Output atteso:**
```
Processing 9 posts...

[1/9] Downloading DQ7cX5Pjeao...
[1/9] ✓ Downloaded DQ7cX5Pjeao
[1/9] Cropping DQ7cX5Pjeao...
[1/9] ✓ Cropped DQ7cX5Pjeao
...

✓ Updated 9 posts in data/instagram-posts.json
✓ All images saved to public/instagram-posts
✓ All cropped images saved to public/instagram-posts-cropped
```

---

### Passo 6: Verifica e Testa

1. **Avvia il server di sviluppo:**
   ```bash
   npm run dev
   ```

2. **Apri** http://localhost:3000 nel browser

3. **Scrolla** fino alla sezione Instagram

4. **Verifica** che i 9 post siano aggiornati correttamente

---

### Passo 7: Commit e Deploy

Se tutto funziona:

```bash
git add .
git commit -m "Aggiorna griglia Instagram con i nuovi post"
git push origin main
```

---

## 🔧 Metodo Alternativo: Script Unificato

Se preferisci automatizzare tutto in un unico passaggio, puoi usare lo script unificato:

```bash
node scripts/scrape-and-update-instagram.js
```

Questo script:
1. Legge l'HTML da `scripts/instagram-page.html`
2. Estrae i dati automaticamente
3. Aggiorna `update-instagram-latest.js` automaticamente
4. Scarica e processa le immagini
5. Aggiorna `data/instagram-posts.json`

**⚠️ Nota:** Assicurati di aver salvato l'HTML in `scripts/instagram-page.html` prima di eseguire questo script.

---

## 🚨 Risoluzione Problemi

### ❌ "File non trovato: scripts/instagram-page.html"

**Soluzione:**
1. Verifica che il file esista nella cartella `scripts/`
2. Controlla che il nome del file sia esattamente `instagram-page.html` (case-sensitive)
3. Assicurati di essere nella cartella root del progetto quando esegui lo script

---

### ❌ "Nessun post trovato"

**Possibili cause:**
- L'HTML non contiene i post (non hai scrollato abbastanza)
- Instagram ha cambiato la struttura HTML
- Non sei loggato su Instagram

**Soluzioni:**
1. **Scrolla di più** la pagina Instagram prima di copiare l'HTML
2. **Ricarica** la pagina e scrolla di nuovo
3. **Verifica** che l'HTML contenga stringhe come `/anima.ent/reel/` o `/anima.ent/p/`
4. **Prova** a copiare l'HTML di nuovo

---

### ❌ "Errore nel download delle immagini"

**Possibili cause:**
- Gli URL delle immagini sono scaduti (Instagram usa URL temporanei)
- Problemi di connessione
- L'immagine non esiste più

**Soluzioni:**
1. **Riesegui** lo script di estrazione per ottenere URL freschi
2. **Verifica** la connessione internet
3. **Controlla** che gli URL siano validi aprendoli nel browser

---

### ❌ "Immagine non trovata per: [ID]"

**Causa:** Lo script non riesce a trovare l'URL dell'immagine nell'HTML.

**Soluzioni:**
1. **Scrolla di più** la pagina prima di copiare l'HTML
2. **Prova** a copiare l'HTML di nuovo
3. **Verifica** manualmente che l'HTML contenga le immagini

---

### ❌ "Error cropping [filename]"

**Causa:** Problema con la libreria Sharp o formato immagine non supportato.

**Soluzioni:**
1. **Installa** le dipendenze: `npm install`
2. **Verifica** che Sharp sia installato: `npm list sharp`
3. **Riprova** a scaricare l'immagine

---

## 💡 Suggerimenti e Best Practices

### ✅ Fai così:

1. **Aggiorna regolarmente** (ogni settimana o quando pubblichi nuovi post)
2. **Scrolla sempre** la pagina prima di copiare l'HTML
3. **Verifica** che i post siano visibili nella pagina prima di copiare
4. **Testa** sempre localmente prima di fare commit
5. **Mantieni** solo i 9 post più recenti nell'array

### ❌ Evita:

1. **Non copiare** l'HTML senza aver scrollato (i post potrebbero non essere caricati)
2. **Non usare** URL di immagini vecchi (scadono dopo qualche tempo)
3. **Non modificare** manualmente il file JSON senza aggiornare le immagini
4. **Non committare** immagini non processate

---

## 📊 Struttura File

Dopo l'esecuzione, avrai questa struttura:

```
sito-anima-vincenzo/
├── scripts/
│   ├── instagram-page.html          # HTML copiato da Instagram
│   ├── extract-instagram-data.js    # Script di estrazione
│   ├── update-instagram-latest.js   # Script di download/processamento
│   └── extracted-posts.json         # Dati estratti (temporaneo)
├── public/
│   ├── instagram-posts/             # Immagini originali
│   │   ├── DQ7cX5Pjeao.jpg
│   │   └── ...
│   └── instagram-posts-cropped/     # Immagini ritagliate 3:4
│       ├── DQ7cX5Pjeao.jpg
│       └── ...
└── data/
    └── instagram-posts.json         # Dati finali usati dal sito
```

---

## 🔄 Workflow Completo (Riepilogo)

```bash
# 1. Copia HTML da Instagram → salva in scripts/instagram-page.html

# 2. Estrai i dati
node scripts/extract-instagram-data.js

# 3. Copia il codice generato → incolla in update-instagram-latest.js

# 4. Scarica e processa le immagini
node scripts/update-instagram-latest.js

# 5. Testa localmente
npm run dev

# 6. Commit e push
git add .
git commit -m "Aggiorna griglia Instagram"
git push
```

---

## 📞 Supporto

Se hai problemi:

1. **Verifica** che Node.js sia installato: `node --version`
2. **Installa** le dipendenze: `npm install`
3. **Controlla** che Sharp sia installato: `npm list sharp`
4. **Leggi** i messaggi di errore nello script
5. **Verifica** che l'HTML sia completo e contenga i post

---

## 🎉 Fatto!

Dopo aver completato tutti i passaggi, i nuovi post Instagram saranno visibili sul sito nella griglia 3x3!

**Ricorda:** Instagram cambia spesso la struttura HTML, quindi se lo script smette di funzionare, potrebbe essere necessario aggiornare i pattern di estrazione.

