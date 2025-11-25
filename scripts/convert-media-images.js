const sharp = require('sharp');
const fs = require('fs');
const path = require('path');

const sourceDir = path.join(__dirname, '..', 'public', 'pics.new');
const destDir = path.join(__dirname, '..', 'public', 'immagini-optimized');

// Assicurati che la cartella di destinazione esista
if (!fs.existsSync(destDir)) {
  fs.mkdirSync(destDir, { recursive: true });
}

// Converti le immagini da 1 a 15
async function convertImages() {
  const images = [];
  
  for (let i = 1; i <= 15; i++) {
    const sourceFile = path.join(sourceDir, `${i}.jpg`);
    const destFile = path.join(destDir, `media-${i}.webp`);
    
    if (fs.existsSync(sourceFile)) {
      try {
        await sharp(sourceFile)
          .webp({ quality: 85 })
          .toFile(destFile);
        console.log(`✓ Convertito: ${i}.jpg -> media-${i}.webp`);
        images.push(`media-${i}.webp`);
      } catch (error) {
        console.error(`✗ Errore convertendo ${i}.jpg:`, error.message);
      }
    } else {
      console.warn(`⚠ File non trovato: ${sourceFile}`);
    }
  }
  
  console.log(`\n✅ Conversione completata! ${images.length} immagini convertite.`);
  return images;
}

convertImages().catch(console.error);

