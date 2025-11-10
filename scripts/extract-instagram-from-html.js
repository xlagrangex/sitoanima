const fs = require('fs');
const path = require('path');

// HTML fornito dall'utente (solo la parte rilevante)
const htmlContent = process.argv[2] || '';

if (!htmlContent) {
  console.error('Please provide HTML content as argument');
  process.exit(1);
}

// Estrai i post dall'HTML
function extractPostsFromHTML(html) {
  const posts = [];
  
  // Pattern per trovare i link ai post
  const linkPattern = /href="(\/anima\.ent\/(?:p|reel)\/([^\/"]+)\/)"[^>]*>/g;
  const imagePattern = /<img[^>]*alt="([^"]*)"[^>]*src="([^"]*)"[^>]*>/g;
  const reelPattern = /aria-label="Clip"/;
  const carouselPattern = /aria-label="Carosello"/;
  
  // Trova tutti i link ai post
  const links = [];
  let linkMatch;
  while ((linkMatch = linkPattern.exec(html)) !== null) {
    const url = linkMatch[1];
    const id = linkMatch[2];
    links.push({ url, id });
  }
  
  // Per ogni link, trova l'immagine e il tipo corrispondente
  links.forEach((link, index) => {
    // Cerca l'immagine associata a questo post
    // L'immagine dovrebbe essere vicina al link nel DOM
    const linkIndex = html.indexOf(link.url);
    if (linkIndex === -1) return;
    
    // Cerca l'immagine prima o dopo il link
    const searchStart = Math.max(0, linkIndex - 2000);
    const searchEnd = Math.min(html.length, linkIndex + 2000);
    const context = html.substring(searchStart, searchEnd);
    
    // Trova l'immagine in questo contesto
    const imgMatch = context.match(/<img[^>]*alt="([^"]*)"[^>]*src="([^"]*)"[^>]*>/);
    if (!imgMatch) return;
    
    const alt = imgMatch[1];
    const imageUrl = imgMatch[2];
    
    // Determina il tipo
    let type = 'post';
    if (link.url.includes('/reel/')) {
      type = 'reel';
    } else if (context.includes('aria-label="Carosello"') || context.includes('Carosello')) {
      type = 'carousel';
    } else if (context.includes('aria-label="Clip"') || context.includes('Clip')) {
      type = 'reel';
    }
    
    // Costruisci l'URL completo
    const fullUrl = `https://www.instagram.com${link.url}`;
    
    posts.push({
      id: link.id,
      image: imageUrl,
      alt: alt,
      url: fullUrl,
      type: type
    });
  });
  
  return posts;
}

// Esegui l'estrazione
const posts = extractPostsFromHTML(htmlContent);

// Rimuovi duplicati basati sull'ID
const uniquePosts = [];
const seenIds = new Set();
posts.forEach(post => {
  if (!seenIds.has(post.id)) {
    seenIds.add(post.id);
    uniquePosts.push(post);
  }
});

// Prendi solo i primi 9 post più recenti
const latestPosts = uniquePosts.slice(0, 9);

// Output JSON
console.log(JSON.stringify(latestPosts, null, 2));

