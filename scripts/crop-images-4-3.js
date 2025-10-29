const fs = require('fs');
const path = require('path');
const sharp = require('sharp');

const inputDir = path.join(__dirname, '../public/instagram-posts');
const outputDir = path.join(__dirname, '../public/instagram-posts-cropped');
const jsonPath = path.join(__dirname, '../data/instagram-posts.json');

async function cropImageTo43(inputPath, outputPath) {
  try {
    const metadata = await sharp(inputPath).metadata();
    const { width, height } = metadata;
    
    // Calcola le dimensioni per ritagliare in 4:3
    let cropWidth, cropHeight, left, top;
    
    const targetAspectRatio = 4 / 3;
    const imageAspectRatio = width / height;
    
    if (imageAspectRatio > targetAspectRatio) {
      // L'immagine è più larga → ritaglia i lati
      cropHeight = height;
      cropWidth = Math.round(height * targetAspectRatio);
      left = Math.round((width - cropWidth) / 2);
      top = 0;
    } else {
      // L'immagine è più alta → ritaglia sopra e sotto
      cropWidth = width;
      cropHeight = Math.round(width / targetAspectRatio);
      left = 0;
      top = Math.round((height - cropHeight) / 2);
    }
    
    await sharp(inputPath)
      .extract({ left, top, width: cropWidth, height: cropHeight })
      .toFile(outputPath);
    
    console.log(`✓ Ritagliata: ${path.basename(inputPath)} → ${cropWidth}x${cropHeight}`);
    return true;
  } catch (error) {
    console.error(`✗ Errore ritaglio ${path.basename(inputPath)}:`, error.message);
    return false;
  }
}

async function cropAllImages() {
  if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
  }
  
  // Leggi il JSON per vedere quali immagini ritagliare
  const posts = JSON.parse(fs.readFileSync(jsonPath, 'utf-8'));
  const updatedPosts = [];
  
  console.log(`Ritaglio di ${posts.length} immagini in formato 4:3...\n`);
  
  for (let i = 0; i < posts.length; i++) {
    const post = posts[i];
    const originalPath = path.join(__dirname, '..', 'public', post.image);
    
    if (!fs.existsSync(originalPath)) {
      console.log(`⚠ Immagine non trovata: ${post.image}`);
      updatedPosts.push(post);
      continue;
    }
    
    const filename = path.basename(originalPath);
    const croppedFilename = filename;
    const croppedPath = path.join(outputDir, croppedFilename);
    
    const success = await cropImageTo43(originalPath, croppedPath);
    
    if (success) {
      updatedPosts.push({
        ...post,
        image: `/instagram-posts-cropped/${croppedFilename}`,
        originalImage: post.image // Mantieni riferimento all'originale se necessario
      });
    } else {
      // In caso di errore, mantieni l'immagine originale
      updatedPosts.push(post);
    }
  }
  
  // Salva il JSON aggiornato
  fs.writeFileSync(jsonPath, JSON.stringify(updatedPosts, null, 2));
  console.log(`\n✓ Ritaglio completato!`);
  console.log(`✓ Aggiornato ${jsonPath} con ${updatedPosts.length} post`);
}

cropAllImages().catch(console.error);

