/**
 * Script alternativo per estrarre post Instagram dalle immagini
 * Usa un approccio diverso: cerca le immagini e poi cerca gli ID nei dati embedded
 */

const fs = require('fs');
const path = require('path');

// Prova prima instagram-page.html, poi instagram-page-html
let htmlPath = path.join(__dirname, 'instagram-page.html');
if (!fs.existsSync(htmlPath)) {
  htmlPath = path.join(__dirname, 'instagram-page-html');
}

if (!fs.existsSync(htmlPath)) {
  console.error('❌ File non trovato: scripts/instagram-page.html o scripts/instagram-page-html');
  process.exit(1);
}

const html = fs.readFileSync(htmlPath, 'utf-8');

console.log('🔍 Cercando immagini e dati dei post...\n');

// Pattern per trovare immagini Instagram (gestisce anche entità HTML come &amp;)
const imagePattern = /(https:\/\/scontent-[^"'\s<>]+\.cdninstagram\.com\/[^"'\s<>]+\.jpg[^"'\s<>]*)/g;
const images = [];
let match;

while ((match = imagePattern.exec(html)) !== null) {
  const url = match[1].replace(/&amp;/g, '&');
  if (!images.find(img => img.url === url)) {
    images.push({ url });
  }
}

console.log(`📸 Trovate ${images.length} immagini\n`);

// Cerca gli ID dei post nei dati embedded JSON
const jsonScriptPattern = /<script[^>]*type="application\/json"[^>]*>([\s\S]*?)<\/script>/g;
const posts = [];
const seenIds = new Set();

// Cerca pattern per ID post (DR... o DQ...)
const postIdPattern = /(D[RQ][A-Za-z0-9_-]{9,})/g;
const foundIds = [];
let idMatch;

while ((idMatch = postIdPattern.exec(html)) !== null) {
  const id = idMatch[1];
  if (id.length >= 11 && id.length <= 15 && !seenIds.has(id)) {
    seenIds.add(id);
    foundIds.push(id);
  }
}

console.log(`🆔 Trovati ${foundIds.length} possibili ID post\n`);

// Per ogni ID, cerca l'immagine associata e costruisci i dati del post
foundIds.slice(0, 12).forEach((id, index) => {
  try {
    // Cerca l'immagine più vicina a questo ID
    const idIndex = html.indexOf(id);
    if (idIndex === -1) return;
    
    const contextStart = Math.max(0, idIndex - 5000);
    const contextEnd = Math.min(html.length, idIndex + 5000);
    const context = html.substring(contextStart, contextEnd);
    
    // Cerca immagini nel contesto
    const contextImages = context.match(imagePattern);
    if (!contextImages || contextImages.length === 0) return;
    
    // Prendi la prima immagine valida
    const imageUrl = contextImages[0].replace(/&amp;/g, '&');
    
    // Cerca il testo alternativo
    const altPattern = new RegExp(`alt="([^"]*${id.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}[^"]*)"`, 'i');
    const altMatch = html.match(altPattern);
    
    // Cerca anche nel contesto
    const contextAltPattern = /alt="([^"]{10,200})"/g;
    const contextAltMatches = context.match(contextAltPattern);
    
    let altText = '';
    if (altMatch && altMatch[1]) {
      altText = altMatch[1].trim();
    } else if (contextAltMatches && contextAltMatches.length > 0) {
      altText = contextAltMatches[0].replace(/alt="/, '').replace(/"$/, '').trim();
    } else {
      altText = `Instagram post ${id}`;
    }
    
    // Determina il tipo (cerca "reel" o "carousel" nel contesto)
    let type = 'carousel';
    if (context.toLowerCase().includes('reel') || context.toLowerCase().includes('clip')) {
      type = 'reel';
    }
    
    // Costruisci l'URL (prova prima come post normale, poi come reel)
    let url = `https://www.instagram.com/anima.ent/p/${id}/`;
    if (type === 'reel') {
      url = `https://www.instagram.com/anima.ent/reel/${id}/`;
    }
    
    // Pulisci il testo
    altText = altText.replace(/\n+/g, ' ').replace(/\s+/g, ' ').trim();
    
    if (!posts.find(p => p.id === id)) {
      posts.push({
        id,
        image: imageUrl,
        alt: altText,
        url,
        type
      });
      
      console.log(`[${posts.length}] ✓ Estratto: ${id} (${type})`);
    }
  } catch (error) {
    console.log(`[${index + 1}] ✗ Errore: ${error.message}`);
  }
});

// Prendi solo i primi 9
const latestPosts = posts.slice(0, 9);

if (latestPosts.length === 0) {
  console.error('\n❌ Nessun post trovato!');
  console.log('\n💡 Provo un approccio alternativo...\n');
  
  // Approccio alternativo: estrai direttamente dalle immagini
  const imagePosts = [];
  images.slice(0, 12).forEach((img, index) => {
    // Cerca un ID vicino all'immagine
    const imgIndex = html.indexOf(img.url);
    if (imgIndex === -1) return;
    
    const contextStart = Math.max(0, imgIndex - 3000);
    const contextEnd = Math.min(html.length, imgIndex + 3000);
    const context = html.substring(contextStart, contextEnd);
    
    // Cerca ID nel contesto
    const idMatch = context.match(postIdPattern);
    if (!idMatch) return;
    
    const id = idMatch[0];
    if (id.length < 11 || id.length > 15) return;
    
    // Cerca alt text
    const altMatch = context.match(/alt="([^"]{10,200})"/);
    const altText = altMatch ? altMatch[1].trim() : `Instagram post ${id}`;
    
    // Determina tipo
    let type = 'carousel';
    if (context.toLowerCase().includes('reel') || context.toLowerCase().includes('clip')) {
      type = 'reel';
    }
    
    let url = `https://www.instagram.com/anima.ent/p/${id}/`;
    if (type === 'reel') {
      url = `https://www.instagram.com/anima.ent/reel/${id}/`;
    }
    
    if (!imagePosts.find(p => p.id === id)) {
      imagePosts.push({
        id,
        image: img.url,
        alt: altText.replace(/\n+/g, ' ').replace(/\s+/g, ' ').trim(),
        url,
        type
      });
      
      console.log(`[${imagePosts.length}] ✓ Estratto: ${id} (${type})`);
    }
  });
  
  if (imagePosts.length > 0) {
    const finalPosts = imagePosts.slice(0, 9);
    console.log(`\n✅ Estratti ${finalPosts.length} post\n`);
    
    // Genera il codice
    const jsCode = `  const posts = [\n${finalPosts.map((post, index) => {
      const comma = index < finalPosts.length - 1 ? ',' : '';
      return `    {\n      id: "${post.id}",\n      image: "${post.image}",\n      alt: "${post.alt.replace(/"/g, '\\"')}",\n      url: "${post.url}",\n      type: "${post.type}"\n    }${comma}`;
    }).join('\n')}\n  ];`;
    
    console.log('📋 CODICE DA INSERIRE IN update-instagram-latest.js:\n');
    console.log('─'.repeat(80));
    console.log(jsCode);
    console.log('─'.repeat(80));
    
    // Salva in JSON
    const jsonPath = path.join(__dirname, 'extracted-posts.json');
    fs.writeFileSync(jsonPath, JSON.stringify(finalPosts, null, 2));
    console.log(`\n💾 Dati salvati in: ${jsonPath}\n`);
    
    process.exit(0);
  } else {
    console.error('❌ Nessun post trovato con nessun metodo!');
    process.exit(1);
  }
} else {
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
}

