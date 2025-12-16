const fs = require('fs');
const path = require('path');
const https = require('https');
const http = require('http');
const sharp = require('sharp');

// Funzione per scaricare un'immagine
function downloadImage(url, filepath) {
  return new Promise((resolve, reject) => {
    const protocol = url.startsWith('https') ? https : http;
    const file = fs.createWriteStream(filepath);
    
    const options = {
      headers: {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept': 'image/webp,image/apng,image/*,*/*;q=0.8',
        'Accept-Language': 'en-US,en;q=0.9',
        'Referer': 'https://www.instagram.com/'
      }
    };
    
    const request = protocol.get(url, options, (response) => {
      if (response.statusCode === 302 || response.statusCode === 301) {
        // Redirect
        file.close();
        return downloadImage(response.headers.location, filepath).then(resolve).catch(reject);
      }
      
      if (response.statusCode !== 200) {
        file.close();
        fs.unlinkSync(filepath);
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

// Funzione per croppare immagine in formato 3:4
async function cropImageTo34(inputPath, outputPath) {
  try {
    const metadata = await sharp(inputPath).metadata();
    const { width, height } = metadata;
    
    const targetAspectRatio = 3 / 4;
    const imageAspectRatio = width / height;
    
    let cropWidth, cropHeight, left, top;
    
    if (imageAspectRatio > targetAspectRatio) {
      // Immagine più larga → ritaglia i lati
      cropHeight = height;
      cropWidth = Math.round(height * targetAspectRatio);
      left = Math.round((width - cropWidth) / 2);
      top = 0;
    } else {
      // Immagine più alta → ritaglia sopra e sotto
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

// Aggiorna i post Instagram con gli ultimi 9 post
async function updateInstagramPosts() {
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
  
  // Dati estratti dall'HTML Instagram - ULTIMI 9 POST (dal più recente)
  // CORRETTI: immagini associate correttamente ai post
  const posts = [
    {
      id: "DRxhGa2jQ6P",
      image: "https://scontent-fco2-1.cdninstagram.com/v/t51.82787-15/590846400_17944053978084668_4929565981236409385_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=109&ig_cache_key=Mzc3ODk0NzEzOTAyNDc4NTAzOTE3OTQ0MDUzOTc1MDg0NjY4.3-ccb7-5&ccb=7-5&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjcyMHgxMjgwLnNkci5DMyJ9&_nc_ohc=ew9__L4ghc0Q7kNvwGtITF3&_nc_oc=AdlKf8qL9dFhcsS_yQfsrq3KoxNWI8a74dvUI2DVrKJKWt-TLnBIwUsbOoJ6NogiiMo&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=6qigxg0ppdLztyM5r-jI-g&oh=00_AfkpttPJPkNbps27fZNkZqpuVY7wetb021WwKSWpTwU4_g&oe=693644F4",
      alt: "Instagram post DRxhGa2jQ6P",
      url: "https://www.instagram.com/anima.ent/reel/DRxhGa2jQ6P/",
      type: "reel"
    },
    {
      id: "DRuMSBqjdcz",
      image: "https://scontent-fco2-1.cdninstagram.com/v/t51.82787-15/588230142_17943821844084668_7239906692868728125_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=104&ig_cache_key=Mzc3ODAxMTEzMjYxNjA4MTM1Ng%3D%3D.3-ccb7-5&ccb=7-5&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjE0NDB4MTkxOC5zZHIuQzMifQ%3D%3D&_nc_ohc=GRYpNe3whVkQ7kNvwG8Ci9B&_nc_oc=AdkdPLc5szdlo-O7mvQwhbK0v_nO3TK7KDqsji5yYjj6O376AQatK7C8wGZ0wluDs0I&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=6qigxg0ppdLztyM5r-jI-g&oh=00_AfnR_hy7-0OjSocq1Sg5TX-7Q1Z-PTNVqsEC03Ogr3qM1g&oe=69364AD2",
      alt: "Instagram post DRuMSBqjdcz",
      url: "https://www.instagram.com/anima.ent/p/DRuMSBqjdcz/",
      type: "carousel"
    },
    {
      id: "DRmdnbODdET",
      image: "https://scontent-fco2-1.cdninstagram.com/v/t51.82787-15/587487159_17943496221084668_7778603613422677422_n.jpg?stp=dst-jpg_e35_p1080x1080_sh0.08_tt6&_nc_cat=110&ig_cache_key=Mzc3NTgzNTU5MDIzMTk3MDA2NzE3OTQzNDk2MjE4MDg0NjY4.3-ccb7-5&ccb=7-5&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjMzNzZ4NjAwMC5zZHIuQzMifQ%3D%3D&_nc_ohc=4rT4Dtc8-mwQ7kNvwELA_i9&_nc_oc=Adn6la2AP0ttwLmoK-LSZ4LZ4UsJg2l8pklrNLylLq1pksfYwAqnMVCOIPB_-RkjCx8&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=6qigxg0ppdLztyM5r-jI-g&oh=00_AfnbT2EHY9peWeQdF70O1Hd-odCIjAYbFefL34wFMnlvGA&oe=69362371",
      alt: "Instagram post DRmdnbODdET",
      url: "https://www.instagram.com/anima.ent/reel/DRmdnbODdET/",
      type: "reel"
    },
    {
      id: "DRiEt37DZWs",
      image: "https://scontent-fco2-1.cdninstagram.com/v/t51.82787-15/588683543_17943326703084668_5288374357613991622_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=107&ig_cache_key=Mzc3NDU5OTk0NjgxMzk1MDgyMA%3D%3D.3-ccb7-5&ccb=7-5&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjcyMHg5MDAuc2RyLkMzIn0%3D&_nc_ohc=ouT5Ld0cPK8Q7kNvwHbkof9&_nc_oc=Adl_uRsSnJQM5jenO6GDQtt2hGnU8utOLmU-rXxJZfDOtIV0HJnFn2GuUt17cwsV8b4&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=6qigxg0ppdLztyM5r-jI-g&oh=00_AfnLDAu6cBYM6J0XwgeAAUOG1x7FWQCRUUIZwUfWoQD4ug&oe=693638A9",
      alt: "Instagram post DRiEt37DZWs",
      url: "https://www.instagram.com/anima.ent/p/DRiEt37DZWs/",
      type: "carousel"
    },
    {
      id: "DRfYok2DXvc",
      image: "https://scontent-fco2-1.cdninstagram.com/v/t51.71878-15/586818758_1958045071530906_1667758422342862296_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=103&ig_cache_key=Mzc3Mzg0MzM1NDIxNjY1OTkzMg%3D%3D.3-ccb7-5&ccb=7-5&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjY0MHgxMTM2LnNkci5DMyJ9&_nc_ohc=o6usonKs5mIQ7kNvwHSXtNP&_nc_oc=Adn-2EQ_kOUi33PeD_OE4o1mvjxSzKktHaNObMoDY_V2a_GWhWvCiZXr8dKR5vU09aU&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=6qigxg0ppdLztyM5r-jI-g&oh=00_AflKmmk7caG_bU90BLKM4_DbOqSYJ4w_l433WCu0TawxEg&oe=69361F58",
      alt: "Naples and Milan come together for the first time in a unique connection ☀ Anima 🫱🏼‍🫲🏼 @cleopeofficial here to make history Season 3 — Act VII Until the Sun rises ☀️ @kosmi_____ from @cleopeofficial @monamii.music @mattia_scodellaro 🏠 @hbtoo.official",
      url: "https://www.instagram.com/anima.ent/reel/DRfYok2DXvc/",
      type: "reel"
    },
    {
      id: "DRcOI3qjbCP",
      image: "https://scontent-fco2-1.cdninstagram.com/v/t51.82787-15/587115799_17943089001084668_5051626036256834773_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=105&ig_cache_key=Mzc3Mjk1Mjc1NTQ2MDQ1ODI0Ng%3D%3D.3-ccb7-5&ccb=7-5&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjE0NDB4MTkyMC5zZHIuQzMifQ%3D%3D&_nc_ohc=yuCa-Yc7auAQ7kNvwFYhIMf&_nc_oc=AdnAL953uqpxT669A_QbUzODU1Xwp9S7ydhd29or2EQPSiL5i4Q5AySe6-8cgMK5xZ4&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=6qigxg0ppdLztyM5r-jI-g&oh=00_Afms2ZP5yTZFGmexgKmGI_-1ZdJwUIYq44So9qaqAL7R6A&oe=69362B1B",
      alt: "A warm Sun on a cold friday night ☀️ This is Anima. Every Friday at @hbtoo.official Until the Sun Rises ☀️ - Unreleased pics on Telegram, link in bio 🔗",
      url: "https://www.instagram.com/anima.ent/p/DRcOI3qjbCP/",
      type: "carousel"
    },
    {
      id: "DRUbqmCjajx",
      image: "https://scontent-fco2-1.cdninstagram.com/v/t51.82787-15/586924580_17942764188084668_5789162566159447488_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=110&ig_cache_key=Mzc3MDc2MDQ2MjMzNDc5ODA2NTE3OTQyNzY0MTgyMDg0NjY4.3-ccb7-5&ccb=7-5&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjkwNngxNjA4LnNkci5DMyJ9&_nc_ohc=STDWkXmst6MQ7kNvwGKPGCh&_nc_oc=AdmDLCSlNUtX8vRXdrwSTol4fXU-mitMz2TQ7IZZ-UmLRwnjPbhTQ5rc4ohisn1g5As&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=6qigxg0ppdLztyM5r-jI-g&oh=00_AfneuVrpzdyqI__W2fnVJAapK4utIP0jY1Av3dSqGpvu_g&oe=6936415A",
      alt: "We dance, we love, we vibe Until the Sun Rises ☀️ See you tonight at home 🏠 @hbtoo.official A vision by @panu.mov",
      url: "https://www.instagram.com/anima.ent/reel/DRUbqmCjajx/",
      type: "reel"
    },
    {
      id: "DRK4buVjdVH",
      image: "https://scontent-fco2-1.cdninstagram.com/v/t51.71878-15/583006320_1807144879801652_5274163750106289592_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=110&ig_cache_key=Mzc2ODA3MjIzNDAzMzA3NTUyNw%3D%3D.3-ccb7-5&ccb=7-5&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjY0MHgxMTM2LnNkci5DMyJ9&_nc_ohc=RTW7oY6Z2o0Q7kNvwGXHg4x&_nc_oc=Adm2dNzy7qzZTRtEMk1aQxeGseZkfBbIWLU6fev0rqVPrYe6bnWd6YOPYhsbJc9DJ2w&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=6qigxg0ppdLztyM5r-jI-g&oh=00_AfmRYJR8QbMjgypWxiVgrNDnMMPpJ2YEij-IqpSEdLxvsg&oe=69364BBC",
      alt: "The Tribe of Dawn☀️ Season 3 — Act VI Until the Sun rises ☀️ Anima Dj Booth | B3B all night long @alexsilvestrimusic @marenna.music @manuelguida 🏠 @hbtoo.official",
      url: "https://www.instagram.com/anima.ent/reel/DRK4buVjdVH/",
      type: "reel"
    },
    {
      id: "DRNd80qjfFS",
      image: "https://scontent-fco2-1.cdninstagram.com/v/t51.82787-15/583341279_17942501463084668_2241294759618184312_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=107&ig_cache_key=Mzc2ODgwMDE2ODg1NTMwMzA5Mg%3D%3D.3-ccb7-5&ccb=7-5&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjE0NDB4MTkyMC5zZHIuQzMifQ%3D%3D&_nc_ohc=PDUff9E-byUQ7kNvwHBiTmv&_nc_oc=AdmPhG_iKR8-Y4MOGpqLB7vljDQGAUY2_nM1LDcjEGT98YBnxor8bCF7TIRsaDQ3B5A&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=6qigxg0ppdLztyM5r-jI-g&oh=00_Afn4kN9CPJiiB7B9FrJt4IPs6IdpFGJRUvI10mvfaE6cZw&oe=69362490",
      alt: "Friday we had a HUGE party for our 3rd Anniversary ☀️ thank you Anima People, we love you❤️ Every Friday at @hbtoo.official Until the Sun Rises ☀️ - Unreleased pics on Telegram, link in bio 🔗",
      url: "https://www.instagram.com/anima.ent/p/DRNd80qjfFS/",
      type: "carousel"
    }
  ];
  
  const updatedPosts = [];
  
  console.log(`Processing ${posts.length} posts...\n`);
  
  for (let i = 0; i < posts.length; i++) {
    const post = posts[i];
    try {
      const filename = `${post.id}.jpg`;
      const filepath = path.join(outputDir, filename);
      const croppedFilePath = path.join(croppedDir, filename);
      
      // Correggi gli URL HTML entities (&amp; -> &)
      const imageUrl = post.image.replace(/&amp;/g, '&');
      
      // Scarica l'immagine solo se non esiste già
      if (!fs.existsSync(filepath)) {
        console.log(`[${i + 1}/${posts.length}] Downloading ${post.id}...`);
        await downloadImage(imageUrl, filepath);
        console.log(`[${i + 1}/${posts.length}] ✓ Downloaded ${post.id}`);
      } else {
        console.log(`[${i + 1}/${posts.length}] ⊙ Skipping download ${post.id} (already exists)`);
      }
      
      // Croppa l'immagine in formato 3:4 se non esiste già
      if (!fs.existsSync(croppedFilePath)) {
        console.log(`[${i + 1}/${posts.length}] Cropping ${post.id}...`);
        await cropImageTo34(filepath, croppedFilePath);
        console.log(`[${i + 1}/${posts.length}] ✓ Cropped ${post.id}`);
      } else {
        console.log(`[${i + 1}/${posts.length}] ⊙ Skipping crop ${post.id} (already exists)`);
      }
      
      // Usa sempre i percorsi locali se l'immagine esiste
      const originalExists = fs.existsSync(filepath);
      const croppedExists = fs.existsSync(croppedFilePath);
      
      updatedPosts.push({
        id: post.id,
        image: croppedExists ? `/instagram-posts-cropped/${filename}` : (originalExists ? `/instagram-posts/${filename}` : post.image),
        alt: post.alt.replace(/\n/g, ' ').replace(/\s+/g, ' ').trim(),
        url: post.url,
        type: post.type,
        originalImage: originalExists ? `/instagram-posts/${filename}` : undefined
      });
    } catch (error) {
      console.error(`[${i + 1}/${posts.length}] ✗ Error processing ${post.id}:`, error.message);
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
  
  // Filtra solo i post che hanno immagini locali (non URL Instagram)
  const postsWithLocalImages = updatedPosts.filter(post => 
    post.image.startsWith('/instagram-posts')
  );
  
  // Salva il JSON aggiornato con solo i post che hanno immagini locali
  const finalPosts = postsWithLocalImages.slice(0, 9);
  fs.writeFileSync(jsonPath, JSON.stringify(finalPosts, null, 2));
  console.log(`\n✓ Updated ${finalPosts.length} posts in ${jsonPath} (filtered ${updatedPosts.length - postsWithLocalImages.length} posts without local images)`);
  console.log(`✓ All images saved to ${outputDir}`);
  console.log(`✓ All cropped images saved to ${croppedDir}`);
}

updateInstagramPosts().catch(console.error);

