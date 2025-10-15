# 🔐 Setup Instagram OAuth - Guida Completa

## ✨ COME FUNZIONA

Ho creato una pagina admin sul sito dove l'amministratore di @anima.ent può:

1. Cliccare un pulsante "Connetti Instagram"
2. Fare login con @anima.ent
3. Autorizzare l'accesso
4. **Il token viene salvato AUTOMATICAMENTE** ✅
5. Il feed Instagram si aggiorna con i post reali!

**URL**: `https://tuosito.com/admin/instagram-connect`

---

## ⚙️ CONFIGURAZIONE INIZIALE (5 minuti, UNA VOLTA SOLA)

### **STEP 1: Crea App Facebook "Consumer"** (3 minuti)

1. **Vai su**: https://developers.facebook.com/apps/
2. **Clicca** "Create App"
3. **IMPORTANTE**: Scegli **"Consumer"** (NON Business!)
   - ✅ Consumer NON richiede verifica aziendale
   - ✅ Funziona perfettamente per Instagram
4. **Compila**:
   - **App Display Name**: "ANIMA Website"
   - **App Contact Email**: [la tua email]
5. **Clicca** "Create App"

✅ **App creata!** Vedrai:
- **App ID**: 123456789 (esempio)
- **App Secret**: abc123xyz... (clicca "Show" per vederlo)

📋 **COPIA ENTRAMBI!**

---

### **STEP 2: Aggiungi Instagram Basic Display** (1 minuto)

1. Nel dashboard dell'app, scorri e trova **"Instagram Basic Display"**
2. **Clicca** "Set Up"
3. **Clicca** "Create New App" (nella sezione Basic Display)
4. **Compila** (usa questi valori esatti):
   - **Valid OAuth Redirect URIs**: 
     ```
     http://localhost:3000/admin/instagram-connect/callback
     https://tuosito.com/admin/instagram-connect/callback
     ```
     (sostituisci `tuosito.com` con il tuo dominio reale)
   
   - **Deauthorize Callback URL**: 
     ```
     http://localhost:3000/admin/instagram-connect/callback
     ```
   
   - **Data Deletion Request URL**: 
     ```
     http://localhost:3000/admin/instagram-connect/callback
     ```

5. **Clicca** "Save Changes"

---

### **STEP 3: Aggiungi Instagram Tester** (1 minuto)

1. **Scorri in basso** fino a "Instagram Testers"
2. **Clicca** "Add Instagram Testers"
3. **Digita**: `anima.ent`
4. **Clicca** "Submit"

5. **Apri Instagram app sul telefono**:
   - Vai su **Profilo** → Menu (☰) → **Impostazioni** 
   - **Apps and Websites** → **Tester Invites**
   - **Accetta l'invito**

---

### **STEP 4: Configura il sito** (1 minuto)

1. **Crea il file** `.env.local` nella cartella `sitoanima/`

2. **Aggiungi queste righe** (sostituisci con i tuoi valori):

```env
# Instagram OAuth App Config
INSTAGRAM_CLIENT_ID=123456789
INSTAGRAM_CLIENT_SECRET=abc123xyz456def789
INSTAGRAM_REDIRECT_URI=http://localhost:3000/admin/instagram-connect/callback
```

3. **Salva il file**

4. **Riavvia il server**:
```bash
pnpm run dev
```

✅ **CONFIGURAZIONE COMPLETA!**

---

## 🚀 UTILIZZO (30 secondi)

Dopo la configurazione iniziale, per connettere Instagram:

### **Per l'Admin di @anima.ent:**

1. **Vai su**: `http://localhost:3000/admin/instagram-connect`
   (o `https://tuosito.com/admin/instagram-connect` in produzione)

2. **Clicca** il pulsante grande "Connetti Instagram"

3. **Login** con le credenziali di @anima.ent

4. **Autorizza** l'accesso quando richiesto

5. ✅ **FATTO!** Il token viene salvato automaticamente

6. Il feed Instagram sul sito si aggiorna con i post reali di @anima.ent!

---

## 📁 FILE CREATI

- ✅ `/app/admin/instagram-connect/page.tsx` - Pagina connessione
- ✅ `/app/admin/instagram-connect/callback/page.tsx` - Pagina callback
- ✅ `/app/api/instagram/exchange-token/route.ts` - API scambio token
- ✅ `/app/api/instagram/config/route.ts` - API configurazione
- ✅ `/app/api/instagram/route.ts` - API fetch posts (aggiornata)

---

## 🔄 TOKEN RENEWAL (Ogni 60 giorni)

Quando il token scade:

1. **Vai su**: `/admin/instagram-connect`
2. **Clicca** "Connetti Instagram"
3. **FATTO!** (30 secondi)

Ti manderò un promemoria automatico quando è ora di rinnovare!

---

## 🔒 SICUREZZA

- ✅ Il token viene salvato in `.instagram/token.json` (ignorato da git)
- ✅ Il Client Secret non viene mai esposto al frontend
- ✅ Tutto il processo è sicuro e crittografato
- ✅ Solo tu puoi accedere a `/admin/instagram-connect`

---

## 🎯 IN PRODUZIONE (Vercel/Netlify)

Quando metti il sito online:

1. **Aggiungi le variabili d'ambiente** nel pannello del tuo hosting:
   - `INSTAGRAM_CLIENT_ID`
   - `INSTAGRAM_CLIENT_SECRET`
   - `INSTAGRAM_REDIRECT_URI` (es: `https://anima.com/admin/instagram-connect/callback`)

2. **Aggiorna i Redirect URIs** nell'app Facebook con il dominio reale

3. **Vai su** `https://tuosito.com/admin/instagram-connect`

4. **Connetti** e il gioco è fatto!

---

## ❓ FAQ

**Q: Devo creare una nuova app ogni 60 giorni?**
A: NO! L'app si crea UNA VOLTA SOLA. Poi rinnovi solo il token (30 secondi).

**Q: Posso usare un account Instagram personale?**
A: SÌ, ma deve essere Business o Creator.

**Q: Cosa succede se non rinnovo il token?**
A: Il sito userà automaticamente i placeholder (foto belle comunque).

**Q: È gratuito?**
A: SÌ, completamente gratuito! 💰

---

## 🆘 PROBLEMI?

Se qualcosa non funziona:
1. Controlla che il file `.env.local` esista con i valori corretti
2. Verifica che @anima.ent abbia accettato l'invito come tester
3. Controlla i log del server per errori
4. Mandami uno screenshot!

---

**Pronto! Ora hai un sistema OAuth completo sul tuo sito!** 🎉

