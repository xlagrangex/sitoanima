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
    
    const request = protocol.get(url, (response) => {
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
  const posts = [
    {
      id: "DRfYok2DXvc",
      image: "https://scontent-fco2-1.cdninstagram.com/v/t51.71878-15/586818758_1958045071530906_1667758422342862296_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=103&ig_cache_key=Mzc3Mzg0MzM1NDIxNjY1OTkzMg%3D%3D.3-ccb7-5&ccb=7-5&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjY0MHgxMTM2LnNkci5DMyJ9&_nc_ohc=DZg5kUiyxdIQ7kNvwFLgYW1&_nc_oc=AdlF1y1hhNLlH0uwydEnj0EjS2qWLJgGgf_qyplvPURPXQvSkBBFaa2JRa4FCSEQrQY&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=BsNBtFfQcQ1aokAg73Dyfg&oh=00_AfhbT3dYcP2eDMTztXbhqBZgMhoNW0s5mKNMurX1sVJTgQ&oe=692BCB98",
      alt: "A warm Sun on a cold friday night ☀️ This is Anima. Every Friday at @hbtoo.official Until the Sun Rises ☀️ - Unreleased pics on Telegram, link in bio 🔗",
      url: "https://www.instagram.com/anima.ent/reel/DRfYok2DXvc/",
      type: "reel"
    },
    {
      id: "DRcOI3qjbCP",
      image: "https://scontent-fco2-1.cdninstagram.com/v/t51.71878-15/586818758_1958045071530906_1667758422342862296_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=103&ig_cache_key=Mzc3Mzg0MzM1NDIxNjY1OTkzMg%3D%3D.3-ccb7-5&ccb=7-5&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjY0MHgxMTM2LnNkci5DMyJ9&_nc_ohc=DZg5kUiyxdIQ7kNvwFLgYW1&_nc_oc=AdlF1y1hhNLlH0uwydEnj0EjS2qWLJgGgf_qyplvPURPXQvSkBBFaa2JRa4FCSEQrQY&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=BsNBtFfQcQ1aokAg73Dyfg&oh=00_AfhbT3dYcP2eDMTztXbhqBZgMhoNW0s5mKNMurX1sVJTgQ&oe=692BCB98",
      alt: "A warm Sun on a cold friday night ☀️ This is Anima. Every Friday at @hbtoo.official Until the Sun Rises ☀️ - Unreleased pics on Telegram, link in bio 🔗",
      url: "https://www.instagram.com/anima.ent/p/DRcOI3qjbCP/",
      type: "carousel"
    },
    {
      id: "DRUbqmCjajx",
      image: "https://scontent-fco2-1.cdninstagram.com/v/t51.82787-15/587115799_17943089001084668_5051626036256834773_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=105&ig_cache_key=Mzc3Mjk1Mjc1NTQ2MDQ1ODI0Ng%3D%3D.3-ccb7-5&ccb=7-5&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjE0NDB4MTkyMC5zZHIuQzMifQ%3D%3D&_nc_ohc=Tsj2NnzVNyIQ7kNvwFOiDkM&_nc_oc=AdnyRpvmYpjxx89i3OM-qp_FictbkUnL938ekl1xQAULF9fOo-oNOe21Esba17rkQTk&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=BsNBtFfQcQ1aokAg73Dyfg&oh=00_AfhlMCTJzuNBXbAFcNw2QduxeIgh-Wgsur8etX7mRI959A&oe=692BD75B",
      alt: "A warm Sun on a cold friday night ☀️ This is Anima. Every Friday at @hbtoo.official Until the Sun Rises ☀️ - Unreleased pics on Telegram, link in bio 🔗",
      url: "https://www.instagram.com/anima.ent/reel/DRUbqmCjajx/",
      type: "reel"
    },
    {
      id: "DRK4buVjdVH",
      image: "https://scontent-fco2-1.cdninstagram.com/v/t51.82787-15/586924580_17942764188084668_5789162566159447488_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=110&ig_cache_key=Mzc3MDc2MDQ2MjMzNDc5ODA2NTE3OTQyNzY0MTgyMDg0NjY4.3-ccb7-5&ccb=7-5&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjkwNngxNjA4LnNkci5DMyJ9&_nc_ohc=cR0ElGIAN3YQ7kNvwGmNole&_nc_oc=Adko7PHUXEQrwdiEXXXyKZJq89NDd-1cq5nScAMUdZdbFdcdn6ph-Gk8pxlyDkmly9w&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=BsNBtFfQcQ1aokAg73Dyfg&oh=00_AfgYynJv5OqpeR8_GlslEuveH0QBsnQz33U0Q-HHQQPjmA&oe=692BED9A",
      alt: "We dance, we love, we vibe Until the Sun Rises ☀️ See you tonight at home 🏠 @hbtoo.official A vision by @panu.mov",
      url: "https://www.instagram.com/anima.ent/reel/DRK4buVjdVH/",
      type: "reel"
    },
    {
      id: "DRNd80qjfFS",
      image: "https://scontent-fco2-1.cdninstagram.com/v/t51.71878-15/583006320_1807144879801652_5274163750106289592_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=110&ig_cache_key=Mzc2ODA3MjIzNDAzMzA3NTUyNw%3D%3D.3-ccb7-5&ccb=7-5&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjY0MHgxMTM2LnNkci5DMyJ9&_nc_ohc=OsO1B9T8wG4Q7kNvwGkl920&_nc_oc=AdnJNhjRQ4CVc-WaG56311-KkQwsIa66egueksgFZMBbKXNVti9qK-2DutQivoxHYb8&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=BsNBtFfQcQ1aokAg73Dyfg&oh=00_AfjaEim1dPO12-gqXFNxZ4stV7_Wb0q0mRV5rCNOFvUzLg&oe=692BBFBC",
      alt: "The Tribe of Dawn☀️ Season 3 — Act VI Until the Sun rises ☀️ Anima Dj Booth | B3B all night long @alexsilvestrimusic @marenna.music @manuelguida 🏠 @hbtoo.official",
      url: "https://www.instagram.com/anima.ent/p/DRNd80qjfFS/",
      type: "carousel"
    },
    {
      id: "DRAlWc-Dd1f",
      image: "https://scontent-fco2-1.cdninstagram.com/v/t51.82787-15/583341279_17942501463084668_2241294759618184312_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=107&ig_cache_key=Mzc2ODgwMDE2ODg1NTMwMzA5Mg%3D%3D.3-ccb7-5&ccb=7-5&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjE0NDB4MTkyMC5zZHIuQzMifQ%3D%3D&_nc_ohc=t_6JD0KZCxQQ7kNvwGZi-Il&_nc_oc=Admp_CT4aKR4ooA0MnKcEuirlj8KjPl3fTP60NoCLOP0ROz35TCIjL43OFj9lwbazKE&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=BsNBtFfQcQ1aokAg73Dyfg&oh=00_AfidG5bmJ0Un-LmwewQIIqOw9wvFHrL25eF-FZ2qkqKgSA&oe=692BD0D0",
      alt: "Friday we had a HUGE party for our 3rd Anniversary ☀️ thank you Anima People, we love you❤️ Every Friday at @hbtoo.official Until the Sun Rises ☀️ - Unreleased pics on Telegram, link in bio 🔗",
      url: "https://www.instagram.com/anima.ent/reel/DRAlWc-Dd1f/",
      type: "reel"
    },
    {
      id: "DQ4zF8mDRwE",
      image: "https://scontent-fco2-1.cdninstagram.com/v/t51.82787-15/583753215_17942001120084668_1068487550465267971_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=110&ig_cache_key=Mzc2NTE3MzU1OTEzNzAwMDc5OTE3OTQyMDAxMTE3MDg0NjY4.3-ccb7-5&ccb=7-5&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjIyMTZ4MzkzNi5zZHIuQzMifQ%3D%3D&_nc_ohc=I1H5Bkz9b3cQ7kNvwHXPhpG&_nc_oc=AdkvygqNbjEmFLQONO6dvnEPA0BnLh5-xcpGGQ0qaZEm4Bw466oHn0MPfUYYSHhbqG8&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=BsNBtFfQcQ1aokAg73Dyfg&oh=00_Afi5wpX9qhHmOQlQgZR736_57AusseWieUi9XI-cVcvjjg&oe=692BEA8E",
      alt: "A short vision from last Friday w/ @piccaemars ☀️ See you tomorrow at home 🏠 @hbtoo.official 📹 @panu.mov",
      url: "https://www.instagram.com/anima.ent/reel/DQ4zF8mDRwE/",
      type: "reel"
    },
    {
      id: "DQ7cX5Pjeao",
      image: "https://scontent-fco2-1.cdninstagram.com/v/t51.82787-15/581020381_17941709064084668_3872717643711848369_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=102&ig_cache_key=Mzc2Mjk4MjE5NzcwMDQwMjE4MDE3OTQxNzA5MDYxMDg0NjY4.3-ccb7-5&ccb=7-5&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjcyMHgxMjgwLnNkci5DMyJ9&_nc_ohc=vuhuEVDJ4ckQ7kNvwHH_h4v&_nc_oc=Adlh7h7OTQquYqxFhASgaOPhkUKVPAhc_FV2MvBxplRGaopDKqeH9PPk7N2o1KTdgqA&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=BsNBtFfQcQ1aokAg73Dyfg&oh=00_Afg02nzLIOtdU5c7C_aoJjpB2Zb0aRECg3kVcNc2y-95Uw&oe=692BD4CD",
      alt: "The Sunrise awaits you ☀️ Season 3 — Act V Until the Sun rises ☀️ Anima dj booth | A-Z order ANIMA DJ BOOTH @hoodiamusic @leolitterio @manuelguida @morea_____ 🏠 @hbtoo.official",
      url: "https://www.instagram.com/anima.ent/p/DQ7cX5Pjeao/",
      type: "carousel"
    },
    {
      id: "DQm1jvSje7N",
      image: "https://scontent-fco2-1.cdninstagram.com/v/t51.82787-15/580506361_17941804509084668_2046923381424085964_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=101&ig_cache_key=Mzc2MzcyNjY4NTgzODM3MTU2Nw%3D%3D.3-ccb7-5&ccb=7-5&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjE0NDB4MTkyMC5zZHIuQzMifQ%3D%3D&_nc_ohc=ovGWr-27yPIQ7kNvwG9R3fx&_nc_oc=AdnMyuIZtWw0E0GEkKF5DYQ-mDQxuPuVdnIQT7HiFMMJX1CcX0rzQzxtRItyMjzTAjY&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=BsNBtFfQcQ1aokAg73Dyfg&oh=00_AfidM6eeb1puC8hZj4SAQbS5UB-yw3sRUI91BUckuH_Fnw&oe=692BDE00",
      alt: "This is all you need ☀️ Every Friday at @hbtoo.official Until the Sun Rises ☀️ - Unreleased pics on Telegram, link in bio 🔗",
      url: "https://www.instagram.com/anima.ent/reel/DQm1jvSje7N/",
      type: "reel"
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
      
      // Scarica l'immagine solo se non esiste già
      if (!fs.existsSync(filepath)) {
        console.log(`[${i + 1}/${posts.length}] Downloading ${post.id}...`);
        await downloadImage(post.image, filepath);
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
      
      updatedPosts.push({
        id: post.id,
        image: `/instagram-posts-cropped/${filename}`,
        alt: post.alt.replace(/\n/g, ' ').replace(/\s+/g, ' ').trim(),
        url: post.url,
        type: post.type,
        originalImage: `/instagram-posts/${filename}`
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
  
  // Salva il JSON aggiornato con solo i 9 post più recenti
  fs.writeFileSync(jsonPath, JSON.stringify(updatedPosts, null, 2));
  console.log(`\n✓ Updated ${updatedPosts.length} posts in ${jsonPath}`);
  console.log(`✓ All images saved to ${outputDir}`);
  console.log(`✓ All cropped images saved to ${croppedDir}`);
}

updateInstagramPosts().catch(console.error);

