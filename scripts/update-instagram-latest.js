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
      id: "DQkRNyFjSdP",
      image: "https://scontent.cdninstagram.com/v/t51.82787-15/572690095_17940879552084668_4842062469339281980_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=109&ig_cache_key=Mzc1NzIwMzY5Mjk1ODA5NzUzNg%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjE0NDB4MTgwMC5zZHIuQzMifQ%3D%3D&_nc_ohc=TNaV4HVOBtMQ7kNvwEs9RC9&_nc_oc=AdlmB3lTkDwITvWzV7mqz3DBt03shtta9avHHOZJptvSkUl9fQJSzxqwzlLbaMQ0DDw&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=x3qV-PxtlbTCTUnzHbGVMA&oh=00_AfjoqXYFX_Xhjf9wldQtG4TBU4FtJzOK06mPWxJjqQWFfA&oe=690E621C",
      alt: "Anima introduces the first Guest of the year: ladies and gentlemen, please welcome to @piccaemars ☀️ Groovy basslines, hypnotic melodies, and unstoppable energy. A fusion of electronic, house, afro & indie dance that make every crowd dance Picca & Mars are not just DJs: they're a whole, huge, Made in Italy movement. Now they are ready to make YOU dance on Friday 7th November at @hbtoo.official Until the Sun Rises ☀️",
      url: "https://www.instagram.com/anima.ent/p/DQkRNyFjSdP/",
      type: "carousel"
    },
    {
      id: "DQU2v5hjfzd",
      image: "https://scontent.cdninstagram.com/v/t51.82787-15/572107578_17940247449084668_4365046759141024048_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=104&ig_cache_key=Mzc1Mjg2NTE1Nzc0MDI5OTYwNg%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjE0NDB4MTkxOC5zZHIuQzMifQ%3D%3D&_nc_ohc=i2xSLOLU-S8Q7kNvwHaIriW&_nc_oc=Adko6tYaKbKVuCGElMxC9YDis2_WA87QxgmDneNPVAPsBbJTtt_Puq6nwzTBn_jjIZY&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=x3qV-PxtlbTCTUnzHbGVMA&oh=00_AfiwEKK8K66T2vSGwF5FDtNIT0SgV0DW3MYBIT22hMwAMA&oe=690E8B3B",
      alt: "Fantastic people, fantastic moments ☀️ See you on November 7th with a special surprise 👀 Until the Sun Rises ☀️ 🏠 @hbtoo.official - Unreleased pics on Telegram, link in bio 🔗",
      url: "https://www.instagram.com/anima.ent/p/DQU2v5hjfzd/",
      type: "carousel"
    },
    {
      id: "DQKYuIHDcgm",
      image: "https://scontent.cdninstagram.com/v/t51.82787-15/569933904_17939818266084668_4269708733286495843_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=104&ig_cache_key=Mzc0OTkxODM2MjY1OTgzMzg5NDE3OTM5ODE4MjYwMDg0NjY4.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjMzNzZ4NjAwMC5zZHIuQzMifQ%3D%3D&_nc_ohc=zBpC7IpWu9YQ7kNvwEs8xam&_nc_oc=AdmIhoYZHy469TRLuI_lyDWP0ifKp69HUXKOiiEr7-bLgQLFMI5M8-_9cvJlNeGZIG4&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=x3qV-PxtlbTCTUnzHbGVMA&oh=00_AfguORX69bUdvpH1SXL3L6MFESnoM9_mDqodiL2RRGEsvQ&oe=690E78A7",
      alt: "This is the Vision of Anima ☀️ See you tomorrow at home 🏠 @hbtoo.official 📹 @panu.mov",
      url: "https://www.instagram.com/anima.ent/reel/DQKYuIHDcgm/",
      type: "reel"
    },
    {
      id: "DQHFiV6DZlG",
      image: "https://scontent.cdninstagram.com/v/t51.82787-15/569956739_17939671992084668_4996891839792711689_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=102&ig_cache_key=Mzc0ODk4OTU0ODcyNzQ4NDY0MQ%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjE0NDB4MTkyMC5zZHIuQzMifQ%3D%3D&_nc_ohc=PlMd8XqNp7kQ7kNvwGGTnbB&_nc_oc=AdnuYxw_A2eHkUMBEkmYFsECEhE4Oj7o2f0ttd4BqyUxxOKxE7JUg7P2qiIUvfablq8&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=x3qV-PxtlbTCTUnzHbGVMA&oh=00_AfiBOUa3zcPO50LuB3q_E5rxp3U_8Gn7-KmXw28N6N0cBA&oe=690E8151",
      alt: "Moments from Friday ☀️ thanks to our friends from @dolcevita__rome for joining us for a fantastic party Every Friday at @hbtoo.official Until the Sun Rises ☀️ - Unreleased pics on Telegram, link in bio 🔗",
      url: "https://www.instagram.com/anima.ent/p/DQHFiV6DZlG/",
      type: "carousel"
    },
    {
      id: "DQCq_v6Ddxs",
      image: "https://scontent.cdninstagram.com/v/t51.71878-15/567353087_1219370173568255_3348238122376727574_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=108&ig_cache_key=Mzc0Nzc0NjkzODY0NjAyNzM3Mg%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjY0MHgxMTM2LnNkci5DMyJ9&_nc_ohc=qdByJbubgZQQ7kNvwEkI5Fv&_nc_oc=Adn7Oj8rs3RqRBVP2bQqNyLqkX0KLNHi-SaGDAKEy0sPHTPDM-99e-DcaqxMuUPdQK0&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=x3qV-PxtlbTCTUnzHbGVMA&oh=00_Afg6SskPiYdYRKkdQ6fw3-N7fYtizNPSwT4igyz3-fM1XA&oe=690E6023",
      alt: "Welcome to the Sunrise Society ☀️ Season 3 — Act III Until the Sun rises ☀️ Anima Dj Booth | A-Z order @alexsilvestrimusic @1xloco @mattia_scodellaro @monamii.music 🏠 @hbtoo.official",
      url: "https://www.instagram.com/anima.ent/reel/DQCq_v6Ddxs/",
      type: "reel"
    },
    {
      id: "DP4YVf3jcxT",
      image: "https://scontent.cdninstagram.com/v/t51.71878-15/566628690_1324336816037974_4379666572277382920_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=109&ig_cache_key=Mzc0NDg1MDEyMDYwMTg4MTY4Mw%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjY0MHgxMTM2LnNkci5DMyJ9&_nc_ohc=cuFidr-eza4Q7kNvwEExocw&_nc_oc=Adkk2TIRbU_rRIJ3Jc-zPntB7OM6rx0MUBdj_vld0bnd5LdEQlsW11igssDl5-pvt0Y&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=x3qV-PxtlbTCTUnzHbGVMA&oh=00_AfhGjYcYjWi8rHyM2gO9mOxSuuGOJktzr4-5hldckFhrMw&oe=690E8AFA",
      alt: "Flashes from an outstanding first night ☀️ Third Season has officially begun See you tomorrow at home 🏠 @hbtoo.official 📹 @panu.mov",
      url: "https://www.instagram.com/anima.ent/reel/DP4YVf3jcxT/",
      type: "reel"
    },
    {
      id: "DPyfZ9nDR9r",
      image: "https://scontent.cdninstagram.com/v/t51.82787-15/565824898_17938858926084668_7817380969821783136_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=108&ig_cache_key=Mzc0MzE5MjM0ODE5MzgyNjg1OA%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjE0NDB4MTkyMC5zZHIuQzMifQ%3D%3D&_nc_ohc=W7Gj4TwPDrgQ7kNvwHDX-KY&_nc_oc=AdkJR5BcCI3UrcznqF81K7Dr--Cm2R6R_UXBeKbDiFk3xZiRbmIduQmh_JWN-XuGNN8&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=x3qV-PxtlbTCTUnzHbGVMA&oh=00_AfijquQjeQKcWcxivRY54GOQG-aN_E8YlA1Afw3sQAdjFA&oe=690E6848",
      alt: "The faces of a new chapter ☀️ Every Friday at @hbtoo.official Until the Sun Rises ☀️ - Unreleased pics on Telegram, link in bio 🔗",
      url: "https://www.instagram.com/anima.ent/p/DPyfZ9nDR9r/",
      type: "carousel"
    },
    {
      id: "DPwsmaJjZrw",
      image: "https://scontent.cdninstagram.com/v/t51.71878-15/563916359_1076013237753036_4760748830051411946_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=108&ig_cache_key=Mzc0MjY4NzQ0MzgwOTA0OTMyOA%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjY0MHgxMTM2LnNkci5DMyJ9&_nc_ohc=Io9xL5vcdlMQ7kNvwH9prGV&_nc_oc=Adn45ZzsQKdWxyCzyF5WR88JS4MmXHS0OHlVN785xHGEJBqeg0q6ulqY0nkhkcus1iw&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=x3qV-PxtlbTCTUnzHbGVMA&oh=00_AfiO_uFeSxj5UipTKdmhyeAfCYEMibfOLJ4cUEZ0QlZj-Q&oe=690E9286",
      alt: "Two worlds illuminated by the same Sun ☀️ Anima meets @dolcevita__rome for an unforgettable party 🤝🏻 Season 3 — Act II Until the Sun rises ☀️ @gianni_presutti from @dolcevita__rome @alexsilvestrimusic b2b @marenna.music @leolitterio 🏠 @hbtoo.official",
      url: "https://www.instagram.com/anima.ent/reel/DPwsmaJjZrw/",
      type: "reel"
    },
    {
      id: "DPmXRBPDZpQ",
      image: "https://scontent.cdninstagram.com/v/t51.82787-15/562537022_17938377252084668_1943177837770365742_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=102&ig_cache_key=MzczOTc3ODg1NzUxMjU5ODU0OA%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjE0NDB4MTgwMC5zZHIuQzMifQ%3D%3D&_nc_ohc=47-wym24guIQ7kNvwH31LqC&_nc_oc=AdlBSRAgo0vkE1T3_e_TDwv-pTrSjzRfsgCs8qcZKrC0V2qPacXTh8H3DwVBToikMxo&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=x3qV-PxtlbTCTUnzHbGVMA&oh=00_AfibZ9-vHHFSYXTdnChC8kT4DfZan9LFwlwJ_sMQO4XoCA&oe=690E608F",
      alt: "Community guidelines ☀ Learn how could you be part of Anima Until the Sun rises ☀ See you tomorrow at 🏠 @hbtoo.official",
      url: "https://www.instagram.com/anima.ent/p/DPmXRBPDZpQ/",
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

