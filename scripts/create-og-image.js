const sharp = require('sharp');
const fs = require('fs');
const path = require('path');

const publicDir = path.join(__dirname, '..', 'public');

async function createOGImage() {
  try {
    // Dimensioni standard per Open Graph: 1200x630px
    const width = 1200;
    const height = 630;
    
    // Crea uno sfondo scuro (nero o arancione scuro come tema ANIMA)
    // Usiamo un gradiente o colore solido nero con logo bianco sopra
    const backgroundColor = { r: 0, g: 0, b: 0 }; // Nero
    
    // Carica il logo ANIMA
    const logoPath = path.join(publicDir, 'anima-complete-white-optimized.webp');
    
    if (!fs.existsSync(logoPath)) {
      console.error('Logo not found:', logoPath);
      return;
    }
    
    const logoMetadata = await sharp(logoPath).metadata();
    const logoWidth = logoMetadata.width;
    const logoHeight = logoMetadata.height;
    
    // Calcola la dimensione del logo per centrarlo (circa 60% della larghezza)
    const targetLogoWidth = Math.round(width * 0.6);
    const targetLogoHeight = Math.round((logoHeight / logoWidth) * targetLogoWidth);
    
    // Crea l'immagine Open Graph
    const outputPath = path.join(publicDir, 'og-image.webp');
    
    // Crea uno sfondo nero
    const background = sharp({
      create: {
        width: width,
        height: height,
        channels: 3,
        background: backgroundColor
      }
    })
    .png();
    
    // Ridimensiona il logo
    const resizedLogo = await sharp(logoPath)
      .resize(targetLogoWidth, targetLogoHeight, {
        fit: 'inside',
        withoutEnlargement: false
      })
      .toBuffer();
    
    // Composa: sfondo nero + logo centrato
    const logoX = Math.round((width - targetLogoWidth) / 2);
    const logoY = Math.round((height - targetLogoHeight) / 2);
    
    await sharp({
      create: {
        width: width,
        height: height,
        channels: 3,
        background: backgroundColor
      }
    })
    .composite([
      {
        input: resizedLogo,
        left: logoX,
        top: logoY
      }
    ])
    .webp({ quality: 90 })
    .toFile(outputPath);
    
    console.log(`✓ Created Open Graph image: ${outputPath}`);
    console.log(`  Size: ${width}x${height}px`);
    console.log(`  Logo size: ${targetLogoWidth}x${targetLogoHeight}px`);
  } catch (error) {
    console.error('Error creating OG image:', error);
  }
}

createOGImage().catch(console.error);

