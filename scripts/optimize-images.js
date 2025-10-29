const sharp = require('sharp');
const fs = require('fs');
const path = require('path');

// Directory paths
const publicDir = path.join(__dirname, '..', 'public');
const optimizedDir = path.join(publicDir, 'immagini-optimized');

// Ensure optimized directory exists
if (!fs.existsSync(optimizedDir)) {
  fs.mkdirSync(optimizedDir, { recursive: true });
}

// Gallery images to optimize (JPG -> WebP)
const galleryImages = [
  'IMG_9914.JPG',
  'IMG_9915.JPG',
  'IMG_9916.JPG',
  'IMG_9917.JPG',
  'IMG_9918.JPG',
  'IMG_9919.JPG',
  'IMG_9920.JPG',
  'IMG_9921.JPG',
  'IMG_9922.JPG',
  'IMG_9923.JPG',
  'IMG_9924.JPG',
  'IMG_9925.JPG',
  'IMG_9926.JPG',
  'IMG_9927.JPG',
  'IMG_9928.JPG',
];

// Event poster to optimize
const eventPoster = 'immagini/IMG_9929.JPEG';

// Guest images to optimize (already WebP but need resizing)
const guestImages = [
  'immagini-guest-cropped/guestmisterioso.webp',
  'immagini-guest-cropped/twolate.webp',
  'immagini-guest-cropped/peppe-citarella.webp',
  'immagini-guest-cropped/Marco Lys at Il Muretto 3.webp',
  'immagini-guest-cropped/Pieropirupa.webp',
  'immagini-guest-cropped/grossomoddo-new.webp',
  'immagini-guest-cropped/peaty.webp',
];

async function optimizeImage(inputPath, outputPath, maxWidth = 1200, quality = 85) {
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

    if (width > maxWidth) {
      newWidth = maxWidth;
      newHeight = Math.round((height * maxWidth) / width);
    }

    console.log(`Processing: ${inputPath}`);
    console.log(`  Original: ${width}x${height} → Optimized: ${newWidth}x${newHeight}`);

    // Save original size before processing
    const originalSize = fs.statSync(input).size;
    
    // Use temp file if input and output are the same
    const isSameFile = path.resolve(input) === path.resolve(output);
    const tempOutput = isSameFile ? output + '.tmp' : output;

    await sharp(input)
      .resize(newWidth, newHeight, {
        fit: 'inside',
        withoutEnlargement: true,
      })
      .webp({ quality })
      .toFile(tempOutput);

    // Replace original with optimized if same file
    if (isSameFile) {
      fs.unlinkSync(input);
      fs.renameSync(tempOutput, output);
    }

    const optimizedSize = fs.statSync(output).size;
    const savings = ((originalSize - optimizedSize) / originalSize * 100).toFixed(1);

    console.log(`  ✓ Saved: ${(originalSize / 1024).toFixed(1)}KB → ${(optimizedSize / 1024).toFixed(1)}KB (${savings}% reduction)\n`);

    return true;
  } catch (error) {
    console.error(`❌ Error processing ${inputPath}:`, error.message);
    return false;
  }
}

async function optimizeGalleryImages() {
  console.log('🖼️  Optimizing gallery images (JPG -> WebP)...\n');
  
  // Carousel displays at ~525px, so 700px is enough for retina displays
  for (const image of galleryImages) {
    const inputPath = image;
    const outputName = image.replace(/\.JPG$/i, '.webp');
    const outputPath = `immagini-optimized/${outputName}`;
    
    await optimizeImage(inputPath, outputPath, 700, 85);
  }
}

async function optimizeEventPoster() {
  console.log('📅 Optimizing event poster...\n');
  
  // Poster displays at ~599px, so 800px is enough
  const outputPath = 'immagini-optimized/IMG_9929.webp';
  await optimizeImage(eventPoster, outputPath, 800, 85);
}

async function optimizeGuestImages() {
  console.log('👥 Optimizing guest images...\n');
  
  // Guest carousel displays at ~525px, so 700px is enough for retina displays
  for (const image of guestImages) {
    // Keep in same directory but optimize
    await optimizeImage(image, image, 700, 85);
  }
}

async function main() {
  console.log('🚀 Starting image optimization...\n');
  console.log('='.repeat(50) + '\n');

  await optimizeGalleryImages();
  await optimizeEventPoster();
  await optimizeGuestImages();

  console.log('='.repeat(50));
  console.log('✅ Image optimization complete!');
}

main().catch(console.error);

