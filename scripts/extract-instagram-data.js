/**
 * Script per estrarre automaticamente i dati dei post Instagram dall'HTML
 * 
 * COME USARE:
 * 1. Vai su https://www.instagram.com/anima.ent/
 * 2. Apri gli strumenti per sviluppatori (F12)
 * 3. Copia tutto l'HTML della pagina (Ctrl+A nella console, poi copia)
 * 4. Salva l'HTML in un file chiamato "instagram-page.html" nella cartella scripts/
 * 5. Esegui: node scripts/extract-instagram-data.js
 * 
 * Lo script estrae automaticamente:
 * - ID del post
 * - URL dell'immagine
 * - Testo alternativo (caption)
 * - URL del post
 * - Tipo (reel o carousel)
 */

const fs = require('fs');
const path = require('path');

function extractInstagramPosts(html) {
  const posts = [];
  
  // Pattern per trovare i link ai post (reel o post normale)
  // I link hanno formato: /anima.ent/reel/ID/ o /anima.ent/p/ID/
  const linkPattern = /href="\/anima\.ent\/(reel|p)\/([^"\/]+)\/"/g;
  const links = [];
  let match;
  
  while ((match = linkPattern.exec(html)) !== null) {
    const type = match[1] === 'reel' ? 'reel' : 'carousel';
    const id = match[2];
    const url = `https://www.instagram.com/anima.ent/${match[1]}/${id}/`;
    
    // Evita duplicati
    if (!links.find(l => l.id === id)) {
      links.push({ id, url, type });
    }
  }
  
  console.log(`Trovati ${links.length} link ai post\n`);
  
  // Per ogni link, cerca l'immagine e il testo associato
  links.forEach((link, index) => {
    try {
      // Cerca l'immagine associata a questo post
      // Le immagini Instagram hanno pattern specifici nell'HTML
      const postSection = html.substring(
        html.indexOf(`/anima.ent/${link.type === 'reel' ? 'reel' : 'p'}/${link.id}/`),
        html.indexOf(`/anima.ent/${link.type === 'reel' ? 'reel' : 'p'}/${link.id}/`) + 5000
      );
      
      // Pattern per trovare l'URL dell'immagine
      // Instagram usa vari CDN, proviamo tutti i pattern comuni
      const imagePatterns = [
        /(https:\/\/scontent-[^"'\s]+\.cdninstagram\.com\/[^"'\s]+\.jpg[^"'\s]*)/g,
        /(https:\/\/instagram\.fnap3-[12]\.fna\.fbcdn\.net\/[^"'\s]+\.jpg[^"'\s]*)/g,
        /(https:\/\/scontent-[^"'\s]+\.fbcdn\.net\/[^"'\s]+\.jpg[^"'\s]*)/g,
        /(https:\/\/[^"'\s]*cdninstagram[^"'\s]+\.jpg[^"'\s]*)/g
      ];
      
      let imageMatch = null;
      for (const pattern of imagePatterns) {
        const matches = postSection.match(pattern);
        if (matches && matches.length > 0) {
          imageMatch = matches;
          break;
        }
      }
      
      if (imageMatch && imageMatch.length > 0) {
        // Prendi la prima immagine trovata (di solito è quella principale)
        // Filtra URL troppo corti o invalidi
        const validImages = imageMatch.filter(url => url.length > 50 && url.includes('instagram'));
        const imageUrl = validImages.length > 0 ? validImages[0] : imageMatch[0];
        
        // Cerca il testo alternativo (alt text)
        // Il pattern cerca l'attributo alt nelle img tag
        const altPattern = new RegExp(`alt="([^"]*${link.id.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}[^"]*)"`, 'i');
        const altMatch = html.match(altPattern);
        
        // Oppure cerca il testo vicino al link
        const contextPattern = new RegExp(`href="/anima\\.ent/${link.type === 'reel' ? 'reel' : 'p'}/${link.id.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}/"[^>]*>([^<]+)`, 'i');
        const contextMatch = html.match(contextPattern);
        
        let altText = '';
        if (altMatch) {
          altText = altMatch[1].trim();
        } else if (contextMatch) {
          altText = contextMatch[1].trim();
        } else {
          altText = `Instagram post ${link.id}`;
        }
        
        // Pulisci il testo (rimuovi newline eccessivi)
        altText = altText.replace(/\n+/g, ' ').replace(/\s+/g, ' ').trim();
        
        posts.push({
          id: link.id,
          image: imageUrl,
          alt: altText,
          url: link.url,
          type: link.type
        });
        
        console.log(`[${index + 1}] ✓ Estratto: ${link.id} (${link.type})`);
      } else {
        console.log(`[${index + 1}] ⚠ Immagine non trovata per: ${link.id}`);
      }
    } catch (error) {
      console.log(`[${index + 1}] ✗ Errore estraendo ${link.id}:`, error.message);
    }
  });
  
  return posts;
}

// Leggi il file HTML
const htmlPath = path.join(__dirname, 'instagram-page.html');

if (!fs.existsSync(htmlPath)) {
  console.error('❌ File non trovato: scripts/instagram-page.html');
  console.log('\n📋 ISTRUZIONI:');
  console.log('1. Vai su https://www.instagram.com/anima.ent/');
  console.log('2. Premi F12 per aprire gli strumenti per sviluppatori');
  console.log('3. Vai nella tab "Elements" o "Elementi"');
  console.log('4. Clicca con il tasto destro su <html> e seleziona "Copy" > "Copy outerHTML"');
  console.log('5. Oppure premi Ctrl+A e poi Ctrl+C per copiare tutto');
  console.log('6. Incolla il contenuto in un file chiamato "instagram-page.html" nella cartella scripts/');
  console.log('7. Riesegui questo script\n');
  process.exit(1);
}

console.log('📖 Leggendo file HTML...\n');
const html = fs.readFileSync(htmlPath, 'utf-8');

console.log('🔍 Estraendo dati dai post Instagram...\n');
const posts = extractInstagramPosts(html);

if (posts.length === 0) {
  console.error('❌ Nessun post trovato!');
  console.log('\n💡 SUGGERIMENTI:');
  console.log('- Assicurati di aver copiato tutto l\'HTML della pagina');
  console.log('- Verifica che la pagina contenga i post visibili');
  console.log('- Prova a scrollare la pagina prima di copiare l\'HTML\n');
  process.exit(1);
}

// Prendi solo i primi 9 post (i più recenti)
const latestPosts = posts.slice(0, 9);

console.log(`\n✅ Estratti ${latestPosts.length} post (mostrando i 9 più recenti)\n`);

// Genera il codice JavaScript da inserire nello script update-instagram-latest.js
const jsCode = `  const posts = [\n${latestPosts.map((post, index) => {
  const comma = index < latestPosts.length - 1 ? ',' : '';
  return `    {\n      id: "${post.id}",\n      image: "${post.image}",\n      alt: "${post.alt.replace(/"/g, '\\"')}",\n      url: "${post.url}",\n      type: "${post.type}"\n    }${comma}`;
}).join('\n')}\n  ];`;

console.log('📋 CODICE DA INSERIRE IN update-instagram-latest.js:\n');
console.log('─'.repeat(80));
console.log(jsCode);
console.log('─'.repeat(80));

// Salva anche in un file JSON per riferimento
const jsonPath = path.join(__dirname, 'extracted-posts.json');
fs.writeFileSync(jsonPath, JSON.stringify(latestPosts, null, 2));
console.log(`\n💾 Dati salvati anche in: ${jsonPath}`);

console.log('\n✨ FATTO! Ora puoi:');
console.log('1. Copiare il codice sopra');
console.log('2. Incollarlo nello script update-instagram-latest.js (sostituendo l\'array posts esistente)');
console.log('3. Eseguire: node scripts/update-instagram-latest.js\n');






