const sharp = require('sharp');
const fs = require('fs');
const path = require('path');

const sourceFile = path.join(__dirname, '..', 'public', 'FEED 19.12.jpg');
const destFile = path.join(__dirname, '..', 'public', 'immagini-optimized', 'FEED-19-12.webp');

// Assicurati che la cartella di destinazione esista
const destDir = path.dirname(destFile);
if (!fs.existsSync(destDir)) {
  fs.mkdirSync(destDir, { recursive: true });
}

async function convertImage() {
  if (!fs.existsSync(sourceFile)) {
    console.error(`✗ File non trovato: ${sourceFile}`);
    process.exit(1);
  }

  try {
    await sharp(sourceFile)
      .webp({ quality: 85 })
      .toFile(destFile);
    console.log(`✓ Convertito: FEED 19.12.jpg -> FEED-19-12.webp`);
    console.log(`✅ Conversione completata!`);
  } catch (error) {
    console.error(`✗ Errore durante la conversione:`, error.message);
    process.exit(1);
  }
}

convertImage().catch(console.error);


