const sharp = require('sharp');
const fs = require('fs');
const path = require('path');

const publicDir = path.join(__dirname, '..', 'public');

async function optimizeImage(inputPath, outputPath, maxWidth, maxHeight, quality = 80) {
  try {
    const input = path.join(publicDir, inputPath);
    
    if (!fs.existsSync(input)) {
      console.warn(`⚠️  File not found: ${input}`);
      return false;
    }

    const output = path.join(publicDir, outputPath);
    const outputDir = path.dirname(output);
    
    if (!fs.existsSync(outputDir)) {
      fs.mkdirSync(outputDir, { recursive: true });
    }

    const metadata = await sharp(input).metadata();
    const width = metadata.width;
    const height = metadata.height;

    // Calculate new dimensions maintaining aspect ratio
    let newWidth = width;
    let newHeight = height;

    if (maxWidth && width > maxWidth) {
      newWidth = maxWidth;
      newHeight = Math.round((height * maxWidth) / width);
    }

    if (maxHeight && newHeight > maxHeight) {
      newHeight = maxHeight;
      newWidth = Math.round((width * maxHeight) / height);
    }

    const originalSize = fs.statSync(input).size;

    console.log(`Processing: ${inputPath}`);
    console.log(`  Original: ${width}x${height} (${(originalSize / 1024).toFixed(1)}KB)`);
    console.log(`  Optimized: ${newWidth}x${newHeight}`);

    // Usa file temporaneo se input e output sono uguali
    const isSameFile = path.resolve(input) === path.resolve(output);
    const tempOutput = isSameFile ? output + '.tmp' : output;

    await sharp(input)
      .resize(newWidth, newHeight, {
        fit: 'inside',
        withoutEnlargement: true,
      })
      .webp({ quality })
      .toFile(tempOutput);

    // Sostituisci il file originale se input e output sono uguali
    if (isSameFile) {
      fs.unlinkSync(input);
      fs.renameSync(tempOutput, output);
    }

    const optimizedSize = fs.statSync(output).size;
    const savings = ((originalSize - optimizedSize) / originalSize * 100).toFixed(1);

    console.log(`  ✓ Saved: ${(optimizedSize / 1024).toFixed(1)}KB (${savings}% reduction)\n`);

    return true;
  } catch (error) {
    console.error(`❌ Error processing ${inputPath}:`, error.message);
    return false;
  }
}

async function optimizeScreenshot() {
  console.log('🖼️  Optimizing screenshot fallback image...\n');
  
  // Screenshot visualizzato a 1302x1152, ridimensioniamo a 1302x1152 per evitare ridimensionamento
  // Convertiamo PNG in WebP con qualità 80
  await optimizeImage(
    'Screenshot 2025-10-29 at 14.32.56.png',
    'Screenshot 2025-10-29 at 14.32.56.webp',
    1302,
    1152,
    80
  );
}

async function optimizeGuestImages() {
  console.log('👥 Optimizing guest images (525x788)...\n');
  
  // Guest carousel visualizzato a 525x788, creiamo immagini a 525px (non 700px)
  const guestImages = [
    'immagini-guest-cropped/guestmisterioso.webp',
    'immagini-guest-cropped/twolate.webp',
    'immagini-guest-cropped/peppe-citarella.webp',
    'immagini-guest-cropped/Marco Lys at Il Muretto 3.webp',
    'immagini-guest-cropped/Pieropirupa.webp',
    'immagini-guest-cropped/grossomoddo-new.webp',
    'immagini-guest-cropped/peaty.webp',
    'immagini-guest-cropped/piccaemars.webp',
  ];
  
  for (const image of guestImages) {
    // Ottimizza le immagini guest a 525px invece di 700px
    const inputPath = image;
    await optimizeImage(inputPath, image, 525, null, 80);
  }
}

async function optimizeGalleryImages() {
  console.log('🖼️  Optimizing gallery images (525x788)...\n');
  
  // Gallery carousel visualizzato a 525x788, creiamo immagini a 525px (non 700px)
  const galleryImages = [
    'immagini-optimized/IMG_9914.webp',
    'immagini-optimized/IMG_9915.webp',
    'immagini-optimized/IMG_9916.webp',
    'immagini-optimized/IMG_9917.webp',
    'immagini-optimized/IMG_9918.webp',
    'immagini-optimized/IMG_9919.webp',
    'immagini-optimized/IMG_9920.webp',
    'immagini-optimized/IMG_9921.webp',
    'immagini-optimized/IMG_9922.webp',
    'immagini-optimized/IMG_9923.webp',
    'immagini-optimized/IMG_9924.webp',
    'immagini-optimized/IMG_9925.webp',
    'immagini-optimized/IMG_9926.webp',
    'immagini-optimized/IMG_9927.webp',
  ];
  
  for (const image of galleryImages) {
    await optimizeImage(image, image, 525, null, 80);
  }
}

async function optimizeEventPoster() {
  console.log('📅 Optimizing event poster (599x599)...\n');
  
  // Poster visualizzato a 599x599, ottimizziamo a 599px invece di 800px
  await optimizeImage(
    'immagini-optimized/IMG_9929.webp',
    'immagini-optimized/IMG_9929.webp',
    599,
    599,
    80
  );
}

async function main() {
  console.log('🚀 Starting PageSpeed image optimization...\n');
  console.log('='.repeat(50) + '\n');

  await optimizeScreenshot();
  await optimizeGuestImages();
  await optimizeGalleryImages();
  await optimizeEventPoster();

  console.log('='.repeat(50));
  console.log('✅ PageSpeed image optimization complete!');
}

main().catch(console.error);

