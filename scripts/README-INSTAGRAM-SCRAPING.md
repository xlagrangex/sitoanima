# 📸 Guida: Scraping Instagram per aggiornare la griglia

## 🎯 Cosa serve

Per aggiornare automaticamente la griglia Instagram, hai bisogno di:

1. **L'HTML della pagina Instagram** del profilo `@anima.ent`
2. **Node.js** (già installato nel progetto)
3. **Lo script di estrazione** (già creato: `extract-instagram-data.js`)

## 📋 Metodo 1: Estrazione automatica dall'HTML (CONSIGLIATO)

### Passo 1: Ottieni l'HTML della pagina Instagram

1. Apri il browser e vai su: **https://www.instagram.com/anima.ent/**
2. **Scrolla la pagina** per caricare almeno i primi 9-12 post
3. Premi **F12** (o clic destro > "Ispeziona elemento")
4. Vai nella tab **"Elements"** (o "Elementi" in italiano)
5. Clicca con il tasto destro su `<html>` (il primo elemento)
6. Seleziona **"Copy"** > **"Copy outerHTML"**
7. Oppure: premi **Ctrl+A** (seleziona tutto) e poi **Ctrl+C** (copia)

### Passo 2: Salva l'HTML in un file

1. Crea un file chiamato `instagram-page.html` nella cartella `scripts/`
2. Incolla tutto l'HTML copiato nel file
3. Salva il file

### Passo 3: Esegui lo script di estrazione

```bash
node scripts/extract-instagram-data.js
```

Lo script:
- ✅ Legge l'HTML
- ✅ Estrae automaticamente i dati dei post (ID, immagine, testo, URL, tipo)
- ✅ Genera il codice JavaScript da inserire
- ✅ Salva i dati in `extracted-posts.json`

### Passo 4: Aggiorna lo script principale

1. Apri `scripts/update-instagram-latest.js`
2. Sostituisci l'array `posts` (righe 105-169) con il codice generato
3. Salva il file

### Passo 5: Esegui lo script di aggiornamento

```bash
node scripts/update-instagram-latest.js
```

Questo script:
- ✅ Scarica le immagini
- ✅ Le ritaglia in formato 3:4
- ✅ Aggiorna `data/instagram-posts.json`

### Passo 6: Commit e push

```bash
git add .
git commit -m "Aggiorna griglia Instagram"
git push origin main
```

---

## 🔧 Metodo 2: Estrazione manuale (se il metodo 1 non funziona)

Se lo script automatico non estrae correttamente i dati, puoi estrarli manualmente:

1. Apri la pagina Instagram
2. Per ogni post, clicca con il tasto destro sull'immagine > "Copia indirizzo immagine"
3. Clicca sul post per vedere l'URL completo
4. Copia il testo della caption

Poi inserisci manualmente i dati nell'array `posts` in `update-instagram-latest.js`:

```javascript
const posts = [
  {
    id: "ID_DEL_POST",           // Es: "DQukSqwjOXU"
    image: "URL_IMMAGINE",        // URL completo dell'immagine
    alt: "TESTO_CAPTION",         // Testo del post
    url: "URL_POST",              // Es: "https://www.instagram.com/anima.ent/reel/ID/"
    type: "reel"                  // "reel" o "carousel"
  },
  // ... altri post
];
```

---

## 🚨 Problemi comuni

### ❌ "File non trovato: scripts/instagram-page.html"
**Soluzione**: Crea il file `instagram-page.html` nella cartella `scripts/` e incolla l'HTML copiato.

### ❌ "Nessun post trovato"
**Soluzione**: 
- Assicurati di aver scrollato la pagina per caricare i post
- Verifica che l'HTML contenga i link ai post
- Prova a copiare l'HTML di nuovo

### ❌ "Errore nel download delle immagini"
**Soluzione**: 
- Verifica che gli URL delle immagini siano validi
- Controlla la connessione internet
- Gli URL Instagram potrebbero essere scaduti (hanno un timestamp)

---

## 💡 Suggerimenti

- **Aggiorna regolarmente**: Gli URL delle immagini Instagram scadono dopo qualche tempo
- **Mantieni i 9 post più recenti**: Lo script mostra sempre i primi 9 post dell'array
- **Ordine**: I post devono essere ordinati dal più recente al più vecchio

---

## 🔄 Workflow completo

```
1. Vai su Instagram → Copia HTML
2. Salva in scripts/instagram-page.html
3. node scripts/extract-instagram-data.js
4. Copia il codice generato → Incolla in update-instagram-latest.js
5. node scripts/update-instagram-latest.js
6. git add . && git commit -m "..." && git push
```

---

## 📞 Supporto

Se hai problemi, verifica:
- ✅ Node.js installato
- ✅ Dipendenze installate (`npm install`)
- ✅ File HTML salvato correttamente
- ✅ Connessione internet attiva











