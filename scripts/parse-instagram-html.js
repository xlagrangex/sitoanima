const fs = require('fs');
const path = require('path');

// Leggi l'HTML da un file o da stdin
const htmlFile = process.argv[2];
let htmlContent = '';

if (htmlFile && fs.existsSync(htmlFile)) {
  htmlContent = fs.readFileSync(htmlFile, 'utf-8');
} else if (process.argv[2]) {
  // HTML passato come argomento
  htmlContent = process.argv[2];
} else {
  console.error('Usage: node parse-instagram-html.js <html-file-or-content>');
  process.exit(1);
}

// Estrai i post dall'HTML
function extractPostsFromHTML(html) {
  const posts = [];
  
  // Pattern per trovare i link ai post Instagram
  // Cerca href="/anima.ent/p/..." o href="/anima.ent/reel/..."
  const postLinks = [];
  const linkRegex = /href="(\/anima\.ent\/(?:p|reel)\/([A-Za-z0-9_-]+)\/)"/g;
  let match;
  
  while ((match = linkRegex.exec(html)) !== null) {
    const fullUrl = match[1];
    const postId = match[2];
    
    if (!postLinks.find(p => p.id === postId)) {
      postLinks.push({
        id: postId,
        url: fullUrl
      });
    }
  }
  
  // Per ogni post, trova l'immagine e le informazioni associate
  postLinks.forEach(link => {
    // Trova la posizione del link nell'HTML
    const linkIndex = html.indexOf(`href="${link.url}"`);
    if (linkIndex === -1) return;
    
    // Cerca nel contesto intorno al link (circa 3000 caratteri prima e dopo)
    const searchStart = Math.max(0, linkIndex - 3000);
    const searchEnd = Math.min(html.length, linkIndex + 3000);
    const context = html.substring(searchStart, searchEnd);
    
    // Trova l'immagine più vicina
    const imgRegex = /<img[^>]*alt="([^"]*)"[^>]*src="([^"]*)"[^>]*>/g;
    let imgMatch;
    let closestImg = null;
    let closestDistance = Infinity;
    
    while ((imgMatch = imgRegex.exec(context)) !== null) {
      const imgIndex = searchStart + imgMatch.index;
      const distance = Math.abs(imgIndex - linkIndex);
      if (distance < closestDistance) {
        closestDistance = distance;
        closestImg = {
          alt: imgMatch[1],
          src: imgMatch[2]
        };
      }
    }
    
    if (!closestImg) return;
    
    // Determina il tipo di post
    let type = 'post';
    if (link.url.includes('/reel/')) {
      type = 'reel';
    } else if (context.includes('aria-label="Carosello"') || 
               context.includes('title="Carosello"') ||
               context.includes('Carosello')) {
      type = 'carousel';
    } else if (context.includes('aria-label="Clip"') || 
               context.includes('title="Clip"') ||
               context.includes('Clip')) {
      type = 'reel';
    }
    
    // Costruisci l'URL completo
    const fullUrl = `https://www.instagram.com${link.url}`;
    
    posts.push({
      id: link.id,
      image: closestImg.src,
      alt: closestImg.alt,
      url: fullUrl,
      type: type
    });
  });
  
  return posts;
}

// Esegui l'estrazione
const allPosts = extractPostsFromHTML(htmlContent);

// Rimuovi duplicati basati sull'ID (mantieni il primo)
const uniquePosts = [];
const seenIds = new Set();
allPosts.forEach(post => {
  if (!seenIds.has(post.id)) {
    seenIds.add(post.id);
    uniquePosts.push(post);
  }
});

// Prendi solo i primi 9 post più recenti
const latestPosts = uniquePosts.slice(0, 9);

// Output JSON formattato
console.log(JSON.stringify(latestPosts, null, 2));

