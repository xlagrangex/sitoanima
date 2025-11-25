/**
 * Script migliorato per estrarre post Instagram - VERSIONE 2
 * Migliore associazione tra immagini e post
 */

const fs = require('fs');
const path = require('path');

// Leggi HTML da file
let htmlPath = path.join(__dirname, 'instagram-page-html');
let htmlPath2 = path.join(__dirname, 'instagram-page.html');

let html = '';
if (fs.existsSync(htmlPath)) {
  const content = fs.readFileSync(htmlPath, 'utf-8');
  if (content && content.length > 1000) {
    html = content;
  }
}

if (!html && fs.existsSync(htmlPath2)) {
  const content = fs.readFileSync(htmlPath2, 'utf-8');
  if (content && content.length > 1000) {
    html = content;
  }
}

if (!html || html.length < 1000) {
  console.error('❌ HTML non trovato o vuoto!');
  process.exit(1);
}

console.log(`📖 HTML caricato: ${html.length} caratteri\n`);
console.log('🔍 Estraendo post con associazione precisa immagini...\n');

// Pattern per trovare link ai post
const linkPattern = /href="\/anima\.ent\/(reel|p)\/([A-Za-z0-9_-]{10,15})\/"/g;
const postsData = [];
let match;

// Prima passata: trova tutti i link
while ((match = linkPattern.exec(html)) !== null) {
  const type = match[1] === 'reel' ? 'reel' : 'carousel';
  const id = match[2];
  const fullUrl = `https://www.instagram.com/anima.ent/${match[1]}/${id}/`;
  
  if (!postsData.find(p => p.id === id)) {
    postsData.push({
      id,
      type,
      url: fullUrl,
      linkIndex: match.index,
      linkEnd: match.index + match[0].length
    });
  }
}

console.log(`📋 Trovati ${postsData.length} link ai post\n`);

// Prendi solo i primi 9
const topPosts = postsData.slice(0, 9);

// Seconda passata: per ogni post, trova l'immagine associata nel contesto specifico
const imagePattern = /(https:\/\/scontent-[^"'\s<>]+\.cdninstagram\.com\/[^"'\s<>]+\.jpg[^"'\s<>]*)/g;

topPosts.forEach((post, index) => {
  // Crea un contesto più ampio attorno al link (10000 caratteri prima e dopo)
  const contextStart = Math.max(0, post.linkIndex - 10000);
  const contextEnd = Math.min(html.length, post.linkEnd + 10000);
  const context = html.substring(contextStart, contextEnd);
  
  // Cerca tutte le immagini nel contesto
  const images = [];
  let imgMatch;
  while ((imgMatch = imagePattern.exec(context)) !== null) {
    const imgUrl = imgMatch[1].replace(/&amp;/g, '&');
    const imgIndex = contextStart + imgMatch.index;
    
    // Evita duplicati
    if (!images.find(img => img.url === imgUrl)) {
      images.push({
        url: imgUrl,
        index: imgIndex,
        distance: Math.abs(imgIndex - post.linkIndex)
      });
    }
  }
  
  // Ordina per distanza dal link (più vicino = più probabile che sia l'immagine corretta)
  images.sort((a, b) => a.distance - b.distance);
  
  // Prendi l'immagine più vicina che non è già stata usata
  let selectedImage = null;
  const usedImages = topPosts.slice(0, index).map(p => p.imageUrl).filter(Boolean);
  
  for (const img of images) {
    if (!usedImages.includes(img.url) && img.url.length > 100) {
      selectedImage = img.url;
      break;
    }
  }
  
  // Se non trovata, usa la più vicina
  if (!selectedImage && images.length > 0) {
    selectedImage = images[0].url;
  }
  
  // Cerca il testo alternativo - cerca solo dopo il link (non prima)
  const altContext = html.substring(
    post.linkEnd,
    Math.min(html.length, post.linkEnd + 5000)
  );
  
  // Cerca il primo alt text valido dopo il link
  const altPattern = /alt="([^"]{20,300})"/g;
  let altText = '';
  let altMatch;
  
  while ((altMatch = altPattern.exec(altContext)) !== null) {
    const candidate = altMatch[1].trim();
    // Evita testi troppo generici o che contengono "alt="
    if (candidate.length > 20 && !candidate.toLowerCase().includes('alt=') && !candidate.startsWith('Instagram')) {
      altText = candidate;
      break;
    }
  }
  
  if (!altText) {
    altText = `Instagram post ${post.id}`;
  }
  
  // Pulisci il testo (rimuovi caratteri strani, normalize spazi)
  altText = altText
    .replace(/alt="?/g, '')
    .replace(/^["']|["']$/g, '')
    .replace(/\n+/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();
  
  post.imageUrl = selectedImage || '';
  post.alt = altText;
  
  if (selectedImage) {
    console.log(`[${index + 1}/9] ✓ ${post.id} (${post.type}) - Immagine trovata`);
  } else {
    console.log(`[${index + 1}/9] ⚠ ${post.id} (${post.type}) - Nessuna immagine trovata`);
  }
});

// Filtra post senza immagine
const validPosts = topPosts.filter(p => p.imageUrl && p.imageUrl.length > 0);

if (validPosts.length === 0) {
  console.error('\n❌ Nessun post completo trovato!');
  process.exit(1);
}

console.log(`\n✅ Estratti ${validPosts.length} post completi\n`);

// Genera il codice
const jsCode = `  const posts = [\n${validPosts.map((post, index) => {
  const comma = index < validPosts.length - 1 ? ',' : '';
  return `    {\n      id: "${post.id}",\n      image: "${post.imageUrl}",\n      alt: "${post.alt.replace(/"/g, '\\"')}",\n      url: "${post.url}",\n      type: "${post.type}"\n    }${comma}`;
}).join('\n')}\n  ];`;

console.log('📋 CODICE DA INSERIRE IN update-instagram-latest.js:\n');
console.log('─'.repeat(80));
console.log(jsCode);
console.log('─'.repeat(80));

// Salva in JSON
const jsonPath = path.join(__dirname, 'extracted-posts-v2.json');
fs.writeFileSync(jsonPath, JSON.stringify(validPosts.map(p => ({
  id: p.id,
  image: p.imageUrl,
  alt: p.alt,
  url: p.url,
  type: p.type
})), null, 2));
console.log(`\n💾 Dati salvati in: ${jsonPath}\n`);

// Mostra un riepilogo delle immagini per verificare che siano tutte diverse
console.log('🔍 Verifica immagini (devono essere tutte diverse):\n');
validPosts.forEach((post, index) => {
  const imageHash = post.imageUrl.split('/').pop().split('?')[0];
  console.log(`[${index + 1}] ${post.id}: ${imageHash.substring(0, 30)}...`);
});

const uniqueImages = new Set(validPosts.map(p => p.imageUrl));
if (uniqueImages.size < validPosts.length) {
  console.log(`\n⚠️ ATTENZIONE: ${validPosts.length - uniqueImages.size} immagini duplicate trovate!`);
  console.log('Lo script potrebbe aver associato male alcune immagini.\n');
} else {
  console.log(`\n✅ Tutte le ${validPosts.length} immagini sono uniche!\n`);
}

