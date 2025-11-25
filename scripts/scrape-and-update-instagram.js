/**
 * Script unificato per scraping e aggiornamento Instagram
 * 
 * Questo script automatizza tutto il processo:
 * 1. Legge l'HTML da instagram-page.html
 * 2. Estrae i dati dei post
 * 3. Aggiorna update-instagram-latest.js automaticamente
 * 4. Scarica e processa le immagini
 * 5. Aggiorna data/instagram-posts.json
 * 
 * USO:
 * 1. Salva l'HTML di Instagram in scripts/instagram-page.html
 * 2. Esegui: node scripts/scrape-and-update-instagram.js
 */

const fs = require('fs');
const path = require('path');
const https = require('https');
const http = require('http');
const sharp = require('sharp');

// ============================================================================
// FUNZIONI DI ESTRAZIONE
// ============================================================================

function extractInstagramPosts(html) {
  const posts = [];
  
  // Pattern per trovare i link ai post (reel o post normale)
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
  
  console.log(`📋 Trovati ${links.length} link ai post\n`);
  
  // Prendi solo i primi 9
  const topLinks = links.slice(0, 9);
  
  // Per ogni link, cerca l'immagine e il testo associato
  topLinks.forEach((link, index) => {
    try {
      // Cerca l'immagine associata a questo post
      const postSection = html.substring(
        Math.max(0, html.indexOf(`/anima.ent/${link.type === 'reel' ? 'reel' : 'p'}/${link.id}/`) - 2000),
        html.indexOf(`/anima.ent/${link.type === 'reel' ? 'reel' : 'p'}/${link.id}/`) + 5000
      );
      
      // Pattern multipli per trovare l'URL dell'immagine
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
        // Filtra URL validi
        const validImages = imageMatch.filter(url => url.length > 50 && url.includes('instagram'));
        const imageUrl = validImages.length > 0 ? validImages[0] : imageMatch[0];
        
        // Cerca il testo alternativo (alt text)
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
        
        // Pulisci il testo
        altText = altText.replace(/\n+/g, ' ').replace(/\s+/g, ' ').trim();
        
        posts.push({
          id: link.id,
          image: imageUrl,
          alt: altText,
          url: link.url,
          type: link.type
        });
        
        console.log(`[${index + 1}/9] ✓ Estratto: ${link.id} (${link.type})`);
      } else {
        console.log(`[${index + 1}/9] ⚠ Immagine non trovata per: ${link.id}`);
      }
    } catch (error) {
      console.log(`[${index + 1}/9] ✗ Errore estraendo ${link.id}:`, error.message);
    }
  });
  
  return posts;
}

// ============================================================================
// FUNZIONI DI DOWNLOAD E PROCESSAMENTO
// ============================================================================

function downloadImage(url, filepath) {
  return new Promise((resolve, reject) => {
    const protocol = url.startsWith('https') ? https : http;
    const file = fs.createWriteStream(filepath);
    
    const request = protocol.get(url, (response) => {
      if (response.statusCode === 302 || response.statusCode === 301) {
        file.close();
        return downloadImage(response.headers.location, filepath).then(resolve).catch(reject);
      }
      
      if (response.statusCode !== 200) {
        file.close();
        if (fs.existsSync(filepath)) {
          fs.unlinkSync(filepath);
        }
        return reject(new Error(`Failed to download: ${response.statusCode}`));
      }
      
      response.pipe(file);
      
      file.on('finish', () => {
        file.close();
        resolve();
      });
    });
    
    request.on('error', (err) => {
      file.close();
      if (fs.existsSync(filepath)) {
        fs.unlinkSync(filepath);
      }
      reject(err);
    });
    
    file.on('error', (err) => {
      file.close();
      if (fs.existsSync(filepath)) {
        fs.unlinkSync(filepath);
      }
      reject(err);
    });
  });
}

async function cropImageTo34(inputPath, outputPath) {
  try {
    const metadata = await sharp(inputPath).metadata();
    const { width, height } = metadata;
    
    const targetAspectRatio = 3 / 4;
    const imageAspectRatio = width / height;
    
    let cropWidth, cropHeight, left, top;
    
    if (imageAspectRatio > targetAspectRatio) {
      cropHeight = height;
      cropWidth = Math.round(height * targetAspectRatio);
      left = Math.round((width - cropWidth) / 2);
      top = 0;
    } else {
      cropWidth = width;
      cropHeight = Math.round(width / targetAspectRatio);
      left = 0;
      top = Math.round((height - cropHeight) / 2);
    }
    
    await sharp(inputPath)
      .extract({ left, top, width: cropWidth, height: cropHeight })
      .resize(600, 800, { fit: 'cover' })
      .jpeg({ quality: 85 })
      .toFile(outputPath);
    
    return true;
  } catch (error) {
    console.error(`Error cropping ${path.basename(inputPath)}:`, error.message);
    return false;
  }
}

// ============================================================================
// FUNZIONE PRINCIPALE
// ============================================================================

async function main() {
  console.log('🚀 Avvio processo completo di scraping Instagram...\n');
  
  // Step 1: Leggi l'HTML
  const htmlPath = path.join(__dirname, 'instagram-page.html');
  if (!fs.existsSync(htmlPath)) {
    console.error('❌ File non trovato: scripts/instagram-page.html');
    console.log('\n📋 ISTRUZIONI:');
    console.log('1. Vai su https://www.instagram.com/anima.ent/');
    console.log('2. Scrolla per caricare i post');
    console.log('3. Premi F12 > Elements > Clicca destro su <html> > Copy outerHTML');
    console.log('4. Salva l\'HTML in scripts/instagram-page.html');
    console.log('5. Riesegui questo script\n');
    process.exit(1);
  }
  
  console.log('📖 Leggendo file HTML...\n');
  const html = fs.readFileSync(htmlPath, 'utf-8');
  
  // Step 2: Estrai i dati
  console.log('🔍 Estraendo dati dai post Instagram...\n');
  const posts = extractInstagramPosts(html);
  
  if (posts.length === 0) {
    console.error('❌ Nessun post trovato!');
    console.log('\n💡 SUGGERIMENTI:');
    console.log('- Assicurati di aver scrollato la pagina prima di copiare l\'HTML');
    console.log('- Verifica che la pagina contenga i post visibili');
    console.log('- Prova a copiare l\'HTML di nuovo\n');
    process.exit(1);
  }
  
  // Prendi solo i primi 9
  const latestPosts = posts.slice(0, 9);
  console.log(`\n✅ Estratti ${latestPosts.length} post\n`);
  
  // Step 3: Aggiorna update-instagram-latest.js
  console.log('📝 Aggiornando update-instagram-latest.js...\n');
  const updateScriptPath = path.join(__dirname, 'update-instagram-latest.js');
  let updateScriptContent = fs.readFileSync(updateScriptPath, 'utf-8');
  
  // Genera il codice JavaScript per l'array posts
  const jsCode = `  const posts = [\n${latestPosts.map((post, index) => {
    const comma = index < latestPosts.length - 1 ? ',' : '';
    return `    {\n      id: "${post.id}",\n      image: "${post.image}",\n      alt: "${post.alt.replace(/"/g, '\\"')}",\n      url: "${post.url}",\n      type: "${post.type}"\n    }${comma}`;
  }).join('\n')}\n  ];`;
  
  // Sostituisci l'array posts nello script
  const postsRegex = /const posts = \[[\s\S]*?\];/;
  if (postsRegex.test(updateScriptContent)) {
    updateScriptContent = updateScriptContent.replace(postsRegex, jsCode);
    fs.writeFileSync(updateScriptPath, updateScriptContent);
    console.log('✓ Aggiornato update-instagram-latest.js\n');
  } else {
    console.log('⚠️ Non sono riuscito a trovare l\'array posts nello script. Aggiorna manualmente.\n');
  }
  
  // Step 4: Scarica e processa le immagini
  console.log('📥 Scaricando e processando le immagini...\n');
  const outputDir = path.join(__dirname, '../public/instagram-posts');
  const croppedDir = path.join(__dirname, '../public/instagram-posts-cropped');
  const jsonPath = path.join(__dirname, '../data/instagram-posts.json');
  
  // Crea le directory se non esistono
  if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
  }
  if (!fs.existsSync(croppedDir)) {
    fs.mkdirSync(croppedDir, { recursive: true });
  }
  
  const updatedPosts = [];
  
  for (let i = 0; i < latestPosts.length; i++) {
    const post = latestPosts[i];
    try {
      const filename = `${post.id}.jpg`;
      const filepath = path.join(outputDir, filename);
      const croppedFilePath = path.join(croppedDir, filename);
      
      // Scarica l'immagine solo se non esiste già
      if (!fs.existsSync(filepath)) {
        console.log(`[${i + 1}/${latestPosts.length}] 📥 Downloading ${post.id}...`);
        await downloadImage(post.image, filepath);
        console.log(`[${i + 1}/${latestPosts.length}] ✓ Downloaded ${post.id}`);
      } else {
        console.log(`[${i + 1}/${latestPosts.length}] ⊙ Skipping download ${post.id} (already exists)`);
      }
      
      // Croppa l'immagine in formato 3:4 se non esiste già
      if (!fs.existsSync(croppedFilePath)) {
        console.log(`[${i + 1}/${latestPosts.length}] ✂️ Cropping ${post.id}...`);
        await cropImageTo34(filepath, croppedFilePath);
        console.log(`[${i + 1}/${latestPosts.length}] ✓ Cropped ${post.id}`);
      } else {
        console.log(`[${i + 1}/${latestPosts.length}] ⊙ Skipping crop ${post.id} (already exists)`);
      }
      
      updatedPosts.push({
        id: post.id,
        image: `/instagram-posts-cropped/${filename}`,
        alt: post.alt.replace(/\n/g, ' ').replace(/\s+/g, ' ').trim(),
        url: post.url,
        type: post.type,
        originalImage: `/instagram-posts/${filename}`
      });
    } catch (error) {
      console.error(`[${i + 1}/${latestPosts.length}] ✗ Error processing ${post.id}:`, error.message);
      // In caso di errore, prova a usare l'immagine originale se esiste
      const filename = `${post.id}.jpg`;
      const originalExists = fs.existsSync(path.join(outputDir, filename));
      const croppedExists = fs.existsSync(path.join(croppedDir, filename));
      
      updatedPosts.push({
        id: post.id,
        image: croppedExists ? `/instagram-posts-cropped/${filename}` : (originalExists ? `/instagram-posts/${filename}` : post.image),
        alt: post.alt.replace(/\n/g, ' ').replace(/\s+/g, ' ').trim(),
        url: post.url,
        type: post.type,
        originalImage: originalExists ? `/instagram-posts/${filename}` : undefined
      });
    }
  }
  
  // Step 5: Aggiorna il JSON finale
  console.log('\n💾 Aggiornando data/instagram-posts.json...\n');
  fs.writeFileSync(jsonPath, JSON.stringify(updatedPosts, null, 2));
  
  console.log('─'.repeat(80));
  console.log('✨ PROCESSO COMPLETATO!');
  console.log('─'.repeat(80));
  console.log(`✓ Aggiornati ${updatedPosts.length} post in ${jsonPath}`);
  console.log(`✓ Immagini salvate in ${outputDir}`);
  console.log(`✓ Immagini ritagliate salvate in ${croppedDir}`);
  console.log('\n🎉 I nuovi post Instagram sono pronti!');
  console.log('💡 Esegui "npm run dev" per vedere le modifiche.\n');
}

// Esegui
main().catch(console.error);

