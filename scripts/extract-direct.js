/**
 * Script per estrarre post Instagram da HTML passato come parametro
 * Uso: node scripts/extract-direct.js "<HTML_CONTENT>"
 * Oppure: incolla l'HTML qui e salva come instagram-page.html
 */

const fs = require('fs');
const path = require('path');

// Leggi HTML da file o da parametro
let html = '';

if (process.argv[2]) {
  // HTML passato come parametro
  html = process.argv[2];
} else {
  // Prova a leggere da file - controlla entrambi i file
  let htmlPath = path.join(__dirname, 'instagram-page-html');
  let htmlPath2 = path.join(__dirname, 'instagram-page.html');
  
  // Prova prima instagram-page-html, poi instagram-page.html
  if (fs.existsSync(htmlPath)) {
    const content = fs.readFileSync(htmlPath, 'utf-8');
    if (content && content.length > 1000) {
      html = content;
      console.log(`📖 Letto file: ${htmlPath} (${html.length} caratteri)`);
    }
  }
  
  if (!html && fs.existsSync(htmlPath2)) {
    const content = fs.readFileSync(htmlPath2, 'utf-8');
    if (content && content.length > 1000) {
      html = content;
      console.log(`📖 Letto file: ${htmlPath2} (${html.length} caratteri)`);
    }
  }
  
  if (!html || html.length < 1000) {
    console.error(`❌ HTML troppo corto o vuoto! (lunghezza: ${html ? html.length : 0})`);
    console.log('💡 Assicurati di aver copiato tutto l\'HTML della pagina Instagram');
    console.log(`💡 File cercati: ${htmlPath}, ${htmlPath2}`);
    console.error('\n❌ Fornisci l\'HTML come parametro o salva in instagram-page.html');
    console.log('\nUso: node scripts/extract-direct.js "<HTML_CONTENT>"');
    process.exit(1);
  }
}

console.log(`📖 HTML caricato: ${html.length} caratteri\n`);
console.log('🔍 Cercando post Instagram...\n');

// Pattern multipli per trovare link ai post
const linkPatterns = [
  /href="\/anima\.ent\/(reel|p)\/([^"\/]+)\/"/g,
  /href=['"]\/anima\.ent\/(reel|p)\/([^"'\/]+)\//g,
  /\/anima\.ent\/(reel|p)\/([A-Za-z0-9_-]{10,})\//g
];

const links = [];
const seenIds = new Set();

linkPatterns.forEach(pattern => {
  let match;
  while ((match = pattern.exec(html)) !== null) {
    const type = match[1] === 'reel' ? 'reel' : 'carousel';
    const id = match[2];
    
    if (id && id.length >= 10 && id.length <= 15 && !seenIds.has(id)) {
      seenIds.add(id);
      links.push({
        id,
        url: `https://www.instagram.com/anima.ent/${match[1]}/${id}/`,
        type
      });
    }
  }
});

console.log(`📋 Trovati ${links.length} link ai post\n`);

if (links.length === 0) {
  console.log('⚠️ Nessun link trovato con i pattern standard.');
  console.log('🔍 Provo a cercare immagini e ID direttamente...\n');
  
  // Cerca immagini
  const imagePatterns = [
    /(https:\/\/scontent-[^"'\s<>]+\.cdninstagram\.com\/[^"'\s<>]+\.jpg[^"'\s<>]*)/g,
    /src=["'](https:\/\/[^"'\s<>]*cdninstagram[^"'\s<>]*\.jpg[^"'\s<>]*)["']/g
  ];
  
  const images = [];
  imagePatterns.forEach(pattern => {
    let match;
    while ((match = pattern.exec(html)) !== null) {
      const url = match[1].replace(/&amp;/g, '&');
      if (!images.find(img => img.url === url)) {
        images.push({ url });
      }
    }
  });
  
  console.log(`📸 Trovate ${images.length} immagini\n`);
  
  // Cerca ID post (pattern DR... o DQ...)
  const idPattern = /(D[RQ][A-Za-z0-9_-]{9,14})/g;
  const foundIds = [];
  let idMatch;
  
  while ((idMatch = idPattern.exec(html)) !== null) {
    const id = idMatch[1];
    if (id.length >= 11 && id.length <= 15 && !seenIds.has(id)) {
      seenIds.add(id);
      foundIds.push(id);
    }
  }
  
  console.log(`🆔 Trovati ${foundIds.length} possibili ID post\n`);
  
  // Associa immagini e ID
  foundIds.slice(0, 12).forEach((id, index) => {
    const idIndex = html.indexOf(id);
    if (idIndex === -1) return;
    
    const contextStart = Math.max(0, idIndex - 5000);
    const contextEnd = Math.min(html.length, idIndex + 5000);
    const context = html.substring(contextStart, contextEnd);
    
    const contextImages = context.match(imagePatterns[0]);
    if (!contextImages || contextImages.length === 0) return;
    
    const imageUrl = contextImages[0].replace(/&amp;/g, '&');
    const altMatch = context.match(/alt=["']([^"']{10,200})["']/);
    const altText = altMatch ? altMatch[1].trim() : `Instagram post ${id}`;
    
    let type = 'carousel';
    if (context.toLowerCase().includes('reel') || context.toLowerCase().includes('clip')) {
      type = 'reel';
    }
    
    let url = `https://www.instagram.com/anima.ent/p/${id}/`;
    if (type === 'reel') {
      url = `https://www.instagram.com/anima.ent/reel/${id}/`;
    }
    
    if (!links.find(p => p.id === id)) {
      links.push({
        id,
        image: imageUrl,
        alt: altText.replace(/\n+/g, ' ').replace(/\s+/g, ' ').trim(),
        url,
        type
      });
      
      console.log(`[${links.length}] ✓ Estratto: ${id} (${type})`);
    }
  });
}

if (links.length === 0) {
  console.error('\n❌ Nessun post trovato!');
  console.log('\n💡 SUGGERIMENTI:');
  console.log('1. Assicurati di aver scrollato la pagina Instagram prima di copiare');
  console.log('2. Copia tutto l\'HTML (Ctrl+A, Ctrl+C)');
  console.log('3. Verifica che l\'HTML contenga riferimenti a "anima.ent"');
  process.exit(1);
}

// Per ogni link, trova l'immagine associata
const posts = [];
links.slice(0, 9).forEach((link, index) => {
  try {
    const linkIndex = html.indexOf(`/anima.ent/${link.type === 'reel' ? 'reel' : 'p'}/${link.id}/`);
    if (linkIndex === -1 && !link.image) return;
    
    let imageUrl = link.image;
    let altText = link.alt || '';
    
    if (!imageUrl) {
      const contextStart = Math.max(0, linkIndex - 5000);
      const contextEnd = Math.min(html.length, linkIndex + 5000);
      const context = html.substring(contextStart, contextEnd);
      
      const imagePattern = /(https:\/\/scontent-[^"'\s<>]+\.cdninstagram\.com\/[^"'\s<>]+\.jpg[^"'\s<>]*)/g;
      const images = context.match(imagePattern);
      if (images && images.length > 0) {
        imageUrl = images[0].replace(/&amp;/g, '&');
      }
      
      if (!altText) {
        const altMatch = context.match(/alt=["']([^"']{10,200})["']/);
        altText = altMatch ? altMatch[1].trim() : `Instagram post ${link.id}`;
      }
    }
    
    if (imageUrl) {
      posts.push({
        id: link.id,
        image: imageUrl,
        alt: altText.replace(/\n+/g, ' ').replace(/\s+/g, ' ').trim(),
        url: link.url,
        type: link.type
      });
      
      console.log(`[${posts.length}/9] ✓ ${link.id} (${link.type})`);
    }
  } catch (error) {
    console.log(`[${index + 1}] ✗ Errore: ${error.message}`);
  }
});

if (posts.length === 0) {
  console.error('\n❌ Nessun post completo trovato!');
  process.exit(1);
}

// Prendi solo i primi 9
const latestPosts = posts.slice(0, 9);

console.log(`\n✅ Estratti ${latestPosts.length} post\n`);

// Genera il codice
const jsCode = `  const posts = [\n${latestPosts.map((post, index) => {
  const comma = index < latestPosts.length - 1 ? ',' : '';
  return `    {\n      id: "${post.id}",\n      image: "${post.image}",\n      alt: "${post.alt.replace(/"/g, '\\"')}",\n      url: "${post.url}",\n      type: "${post.type}"\n    }${comma}`;
}).join('\n')}\n  ];`;

console.log('📋 CODICE DA INSERIRE IN update-instagram-latest.js:\n');
console.log('─'.repeat(80));
console.log(jsCode);
console.log('─'.repeat(80));

// Salva in JSON
const jsonPath = path.join(__dirname, 'extracted-posts.json');
fs.writeFileSync(jsonPath, JSON.stringify(latestPosts, null, 2));
console.log(`\n💾 Dati salvati in: ${jsonPath}\n`);

console.log('✨ FATTO! Ora puoi:');
console.log('1. Copiare il codice sopra');
console.log('2. Incollarlo in update-instagram-latest.js (sostituendo l\'array posts)');
console.log('3. Eseguire: node scripts/update-instagram-latest.js\n');

