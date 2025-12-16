// Script per estrarre dati dall'HTML fornito
const fs = require('fs');
const path = require('path');

// HTML fornito dall'utente (prima parte)
const html = `<div class="x9f619 x1n2onr6 x1ja2u2z" style="">...`; // L'HTML completo è troppo grande, lo processiamo direttamente

// Funzione per estrarre i post
function extractPosts(htmlContent) {
  const posts = [];
  
  // Pattern per trovare i link ai post
  const linkPattern = /href="\/anima\.ent\/(reel|p)\/([^"\/]+)\/"/g;
  const links = [];
  let match;
  
  while ((match = linkPattern.exec(htmlContent)) !== null) {
    const type = match[1] === 'reel' ? 'reel' : 'carousel';
    const id = match[2];
    const url = `https://www.instagram.com/anima.ent/${match[1]}/${id}/`;
    
    if (!links.find(l => l.id === id)) {
      links.push({ id, url, type });
    }
  }
  
  console.log(`Trovati ${links.length} link ai post\n`);
  
  // Per ogni link, cerca immagine e testo
  links.slice(0, 9).forEach((link, index) => {
    try {
      // Cerca l'immagine associata
      const postSection = htmlContent.substring(
        Math.max(0, htmlContent.indexOf(`/anima.ent/${link.type === 'reel' ? 'reel' : 'p'}/${link.id}/`) - 2000),
        htmlContent.indexOf(`/anima.ent/${link.type === 'reel' ? 'reel' : 'p'}/${link.id}/`) + 5000
      );
      
      // Pattern per URL immagine
      const imagePattern = /(https:\/\/scontent-[^"'\s]+\.jpg[^"'\s]*|https:\/\/instagram\.fnap3-[^"'\s]+\.jpg[^"'\s]*)/g;
      const imageMatches = postSection.match(imagePattern);
      
      if (imageMatches && imageMatches.length > 0) {
        const imageUrl = imageMatches[0];
        
        // Cerca il testo alternativo
        const altPattern = new RegExp(`alt="([^"]*${link.id.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}[^"]*)"`, 'i');
        const altMatch = htmlContent.match(altPattern);
        
        let altText = '';
        if (altMatch && altMatch[1]) {
          altText = altMatch[1].trim();
        } else {
          // Cerca il testo vicino al link
          const contextMatch = htmlContent.match(new RegExp(`href="/anima\\.ent/${link.type === 'reel' ? 'reel' : 'p'}/${link.id.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}/"[^>]*>([^<]+)`, 'i'));
          if (contextMatch) {
            altText = contextMatch[1].trim();
          } else {
            altText = `Instagram post ${link.id}`;
          }
        }
        
        altText = altText.replace(/\n+/g, ' ').replace(/\s+/g, ' ').trim();
        
        posts.push({
          id: link.id,
          image: imageUrl,
          alt: altText,
          url: link.url,
          type: link.type
        });
        
        console.log(`[${index + 1}] ✓ ${link.id} (${link.type})`);
      } else {
        console.log(`[${index + 1}] ⚠ Immagine non trovata per: ${link.id}`);
      }
    } catch (error) {
      console.log(`[${index + 1}] ✗ Errore: ${link.id} - ${error.message}`);
    }
  });
  
  return posts;
}

// Leggi l'HTML dal file se esiste, altrimenti usa quello fornito
const htmlPath = path.join(__dirname, 'instagram-page.html');
let htmlContent = '';

if (fs.existsSync(htmlPath)) {
  htmlContent = fs.readFileSync(htmlPath, 'utf-8');
  console.log('📖 Leggendo da file...\n');
} else {
  console.log('⚠️ File instagram-page.html non trovato.');
  console.log('💡 Salva l\'HTML completo in scripts/instagram-page.html e riesegui lo script.\n');
  process.exit(1);
}

const posts = extractPosts(htmlContent);

if (posts.length === 0) {
  console.error('❌ Nessun post trovato!');
  process.exit(1);
}

// Prendi solo i primi 9
const latestPosts = posts.slice(0, 9);

console.log(`\n✅ Estratti ${latestPosts.length} post\n`);

// Genera il codice JavaScript
const jsCode = `  const posts = [\n${latestPosts.map((post, index) => {
  const comma = index < latestPosts.length - 1 ? ',' : '';
  return `    {\n      id: "${post.id}",\n      image: "${post.image}",\n      alt: "${post.alt.replace(/"/g, '\\"')}",\n      url: "${post.url}",\n      type: "${post.type}"\n    }${comma}`;
}).join('\n')}\n  ];`;

console.log('📋 CODICE DA INSERIRE IN update-instagram-latest.js:\n');
console.log('─'.repeat(80));
console.log(jsCode);
console.log('─'.repeat(80));

// Salva anche in JSON
const jsonPath = path.join(__dirname, 'extracted-posts.json');
fs.writeFileSync(jsonPath, JSON.stringify(latestPosts, null, 2));
console.log(`\n💾 Dati salvati in: ${jsonPath}\n`);









