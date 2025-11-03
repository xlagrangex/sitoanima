const fs = require('fs');
const path = require('path');
const sharp = require('sharp');

const inputDir = path.join(__dirname, '../public/immagini guest');
const outputDir = path.join(__dirname, '../public/immagini-guest-cropped');

const images = [
  'guestmisterioso.webp',
  'twolate.webp',
  'peppe-citarella.webp',
  'Marco Lys at Il Muretto 3.webp',
  'Pieropirupa.webp',
  'grossomoddo-new.webp',
  'peaty.webp',
  'piccaemars.jpg'
];

async function cropImageTo23(inputPath, outputPath) {
  try {
    const metadata = await sharp(inputPath).metadata();
    const { width, height } = metadata;
    
    // Calcola le dimensioni per ritagliare in 2:3
    let cropWidth, cropHeight, left, top;
    
    const targetAspectRatio = 2 / 3;
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
    
    // Ritaglia e converte in WebP se l'output è .webp
    const sharpInstance = sharp(inputPath)
      .extract({ left, top, width: cropWidth, height: cropHeight })
      .resize(700, null, { withoutEnlargement: true });
    
    if (outputPath.endsWith('.webp')) {
      await sharpInstance.webp({ quality: 85 }).toFile(outputPath);
    } else {
      await sharpInstance.toFile(outputPath);
    }
    
    console.log(`✓ Ritagliata e ottimizzata: ${path.basename(inputPath)} → ${cropWidth}x${cropHeight}`);
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
  
  console.log(`Ritaglio di ${images.length} immagini guest in formato 2:3...\n`);
  
  for (let i = 0; i < images.length; i++) {
    const imageName = images[i];
    const inputPath = path.join(inputDir, imageName);
    // Converti .jpg in .webp per l'output
    const outputName = imageName.replace(/\.(jpg|jpeg)$/i, '.webp');
    const outputPath = path.join(outputDir, outputName);
    
    if (!fs.existsSync(inputPath)) {
      console.log(`⚠ Immagine non trovata: ${imageName}`);
      continue;
    }
    
    // Forza la ri-generazione (rimuovi il file esistente se c'è)
    if (fs.existsSync(outputPath)) {
      fs.unlinkSync(outputPath);
    }
    
    await cropImageTo23(inputPath, outputPath);
  }
  
  console.log(`\n✓ Ritaglio completato!`);
}

cropAllImages().catch(console.error);

