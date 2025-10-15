# Instagram Feed Setup Guide

## Come configurare il feed Instagram reale

### 1. Creare un'App su Facebook Developers

1. Vai su https://developers.facebook.com/apps/
2. Clicca su "Create App"
3. Seleziona "Consumer" come tipo di app
4. Dai un nome all'app (es. "ANIMA Website")
5. Aggiungi la tua email

### 2. Configurare Instagram Basic Display API

1. Nel Dashboard della tua app, vai su "Add Product"
2. Trova "Instagram Basic Display" e clicca "Set Up"
3. Nella sezione "Basic Display", clicca su "Create New App"
4. Compila i campi:
   - **Display Name**: ANIMA Website
   - **Valid OAuth Redirect URIs**: `https://localhost/`
   - **Deauthorize Callback URL**: `https://localhost/`
   - **Data Deletion Request URL**: `https://localhost/`
5. Salva le modifiche

### 3. Ottenere il tuo Access Token

#### Metodo 1: Usando l'Instagram Graph API Explorer (Consigliato)

1. Vai su https://developers.facebook.com/tools/explorer/
2. Seleziona la tua app dal menu a tendina
3. In "User or Page" seleziona il tuo account Instagram Business
4. Richiedi questi permessi:
   - `instagram_basic`
   - `pages_show_list`
5. Clicca "Generate Access Token"
6. Copia il token generato

#### Metodo 2: Manualmente tramite OAuth

1. Costruisci questo URL (sostituisci `{app-id}` con il tuo App ID):
   ```
   https://api.instagram.com/oauth/authorize?client_id={app-id}&redirect_uri=https://localhost/&scope=user_profile,user_media&response_type=code
   ```
2. Apri l'URL nel browser e autorizza l'app
3. Verrai reindirizzato a `https://localhost/?code=XXXXX`
4. Copia il codice dall'URL
5. Usa questo codice per ottenere l'access token (API call POST)

### 4. Convertire in Long-Lived Token

Gli access token di default scadono dopo 1 ora. Per un token long-lived (60 giorni):

```bash
curl -i -X GET "https://graph.instagram.com/access_token
  ?grant_type=ig_exchange_token
  &client_secret={app-secret}
  &access_token={short-lived-token}"
```

### 5. Configurare le Variabili d'Ambiente

1. Crea un file `.env.local` nella root del progetto
2. Aggiungi:
   ```env
   INSTAGRAM_ACCESS_TOKEN=your_long_lived_token_here
   ```

### 6. Rinnovare il Token (Importante!)

I Long-Lived Token scadono dopo 60 giorni. Per rinnovarli prima della scadenza:

```bash
curl -i -X GET "https://graph.instagram.com/refresh_access_token
  ?grant_type=ig_refresh_token
  &access_token={long-lived-token}"
```

⚠️ **IMPORTANTE**: Imposta un reminder per rinnovare il token ogni 50 giorni!

## Alternative più semplici

### Opzione 1: Servizi Third-Party

- **Behold**: https://behold.so
- **SnapWidget**: https://snapwidget.com
- **EmbedSocial**: https://embedsocial.com

Questi servizi gestiscono automaticamente i token e forniscono widget pronti all'uso.

### Opzione 2: Instagram Business Account + Facebook Page

Se hai un Instagram Business Account collegato a una Facebook Page:

1. Usa Facebook Graph API invece di Instagram Basic Display
2. I token sono più stabili e durano più a lungo
3. Richiedi il permesso `instagram_basic` e `pages_show_list`

## Testare l'integrazione

1. Assicurati che il file `.env.local` sia creato con il token
2. Riavvia il server di sviluppo:
   ```bash
   pnpm run dev
   ```
3. Naviga alla sezione Instagram sul sito
4. Dovresti vedere una griglia 3x3 con gli ultimi 9 post

## Troubleshooting

### Errore: "Instagram access token not configured"
- Verifica che `.env.local` esista
- Controlla che la variabile sia scritta correttamente: `INSTAGRAM_ACCESS_TOKEN`
- Riavvia il server

### Errore: "Failed to fetch Instagram posts"
- Il token potrebbe essere scaduto
- Verifica che l'account Instagram sia Business o Creator
- Controlla i permessi dell'app

### I post non si caricano
- Apri la Console del browser (F12)
- Vai su Network tab
- Cerca la chiamata a `/api/instagram`
- Controlla l'errore specifico nella risposta

## Note di Sicurezza

⚠️ Non committare mai il file `.env.local` su Git!
- È già presente in `.gitignore`
- Non condividere mai il tuo access token pubblicamente
- Usa variabili d'ambiente in produzione (Vercel, Netlify, ecc.)

