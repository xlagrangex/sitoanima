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
      id: "DQ7cX5Pjeao",
      image: "https://scontent-fco2-1.cdninstagram.com/v/t51.82787-15/580506361_17941804509084668_2046923381424085964_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=101&ig_cache_key=Mzc2MzcyNjY4NTgzODM3MTU2Nw%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjE0NDB4MTkyMC5zZHIuQzMifQ%3D%3D&_nc_ohc=ssfu4xaYJugQ7kNvwFhxMYB&_nc_oc=AdnAyTCvvjehmwGEd5hflou2t-pDTsHAb-5w3yMEtzYYFIY7vAzYoD2P000uSnNbNAw&_nc_ad=z-m&_nc_cid=1093&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=Yc-rb7oonp449LW5-p8e_Q&oh=00_AfjlmIMjT7QOAFUEcxEO9BnVRYHe9fxemLUxaSX7KhIgCQ&oe=691BD3C0",
      alt: "This is all you need ☀️ Every Friday at @hbtoo.official Until the Sun Rises ☀️ - Unreleased pics on Telegram, link in bio 🔗",
      url: "https://www.instagram.com/anima.ent/p/DQ7cX5Pjeao/",
      type: "carousel"
    },
    {
      id: "DQ4zF8mDRwE",
      image: "https://scontent-fco2-1.cdninstagram.com/v/t51.82787-15/581020381_17941709064084668_3872717643711848369_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=102&ig_cache_key=Mzc2Mjk4MjE5NzcwMDQwMjE4MDE3OTQxNzA5MDYxMDg0NjY4.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjcyMHgxMjgwLnNkci5DMyJ9&_nc_ohc=EBpUoyh2RM0Q7kNvwGvAaUY&_nc_oc=Adkt14Rl0iFEXyPV6iQ192duLVSxdnu7RoYGd7F3_P2ReIBPKAB3sr_7R87pzImaMoM&_nc_ad=z-m&_nc_cid=1093&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=akcd34cA-M2REppeepaTJQ&oh=00_AfiOR54yE0BL-pHvdbFxbe1goL4oBrVyzDYC6kcZXd98bQ&oe=691BCA8D",
      alt: "The Sunrise awaits you ☀️ Season 3 — Act V Until the Sun rises ☀️ Anima dj booth | A-Z order ANIMA DJ BOOTH @hoodiamusic @leolitterio @manuelguida @morea_____ 🏠 @hbtoo.official",
      url: "https://www.instagram.com/anima.ent/reel/DQ4zF8mDRwE/",
      type: "reel"
    },
    {
      id: "DQr87zuDWbB",
      image: "https://scontent-fco2-1.cdninstagram.com/v/t51.71878-15/573608511_1517178756267738_1413521016259122972_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=109&ig_cache_key=Mzc1OTM2NjMwNjc0NDA2Nzc3Nw%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjY0MHgxMTM2LnNkci5DMyJ9&_nc_ohc=xBMwx6dYQGUQ7kNvwE0CPvz&_nc_oc=Adm-Vz8qKTJf-D8VgHIuihFRcFQWuOVhvyyj0i5Ad4FI8aB9wJlywtU1eKn8yCV1cFI&_nc_ad=z-m&_nc_cid=1093&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=akcd34cA-M2REppeepaTJQ&oh=00_AfgYkDmv_lxiy485HQJ1pKm4Pr_LetaTw2AvOqwnvF58Ng&oe=691BDD47",
      alt: "Anima official website ☀️ now online Check the bio or search \"animaent.club\"",
      url: "https://www.instagram.com/anima.ent/reel/DQr87zuDWbB/",
      type: "reel"
    },
    {
      id: "DQm1jvSje7N",
      image: "https://scontent-fco2-1.cdninstagram.com/v/t51.71878-15/574348684_1482981756318255_4099326794748306032_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=110&ig_cache_key=Mzc1NzkyNjQ5MTUxMTE4OTE5Nw%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjY0MHgxMTM2LnNkci5DMyJ9&_nc_ohc=1JP3mrnZttcQ7kNvwGblBfj&_nc_oc=Adn33oF3vzyzpSvuB48tvLzUzeWkp6GlChwmBQ6dI2B8qDncm6UkAa1iwNKZ86g16RQ&_nc_ad=z-m&_nc_cid=1093&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=akcd34cA-M2REppeepaTJQ&oh=00_AfgnlchJcN_hl3UohsIkjKuds6C9Jv4BLKSTyYVvYncdsw&oe=691BE767",
      alt: "Here again to shine bright, together ☀️ Season 3 — Act IV Until the Sun rises ☀️ SPECIAL GUEST @piccaemars from @soundsvalley_ Anima Dj Booth | A-Z order @alexsilvestrimusic @marenna.music 🏠 @hbtoo.official",
      url: "https://www.instagram.com/anima.ent/reel/DQm1jvSje7N/",
      type: "reel"
    },
    {
      id: "DQkRNyFjSdP",
      image: "https://scontent-fco2-1.cdninstagram.com/v/t51.82787-15/572690095_17940879552084668_4842062469339281980_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=109&ig_cache_key=Mzc1NzIwMzY5Mjk1ODA5NzUzNg%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjE0NDB4MTgwMC5zZHIuQzMifQ%3D%3D&_nc_ohc=Gdi4i7jO23AQ7kNvwHFAtdD&_nc_oc=AdmVcfUY1GqbSTwIxeMMbzFdnXyD74MRfqFbOTiEEeB8o8PLekPOiXY2mWgf8icHaoU&_nc_ad=z-m&_nc_cid=1093&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=akcd34cA-M2REppeepaTJQ&oh=00_Afj4MDH3zfRkiVGXHfUfsbQqd2zds_P2EkMyCigGlWsXGQ&oe=691BC95C",
      alt: "Anima introduces the first Guest of the year: ladies and gentlemen, please welcome to @piccaemars ☀️ Groovy basslines, hypnotic melodies, and unstoppable energy. A fusion of electronic, house, afro & indie dance that make every crowd dance Picca & Mars are not just DJs: they're a whole, huge, Made in Italy movement. Now they are ready to make YOU dance on Friday 7th November at @hbtoo.official Until the Sun Rises ☀️",
      url: "https://www.instagram.com/anima.ent/p/DQkRNyFjSdP/",
      type: "carousel"
    },
    {
      id: "DQZ-CTgDUUH",
      image: "https://scontent-fco2-1.cdninstagram.com/v/t51.82787-15/573083345_17940452799084668_7653385790377702239_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=107&ig_cache_key=Mzc1NDMwMzkxMTI1MjMwODExMQ%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjcyMHg0MDUuc2RyLkMzIn0%3D&_nc_ohc=aIia0VVpNjgQ7kNvwHaRqvv&_nc_oc=Admab_9hoY_EVLzsWGAC3sp4Z7Oea1e3OgbOxl76asbWFb2XjToWHHxFes2PQsyXcwA&_nc_ad=z-m&_nc_cid=1093&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=akcd34cA-M2REppeepaTJQ&oh=00_AfgrbiaG3-VfRsBPKs7bE9MIPKGrk2NIiDjQgu-DqlligA&oe=691BD89D",
      alt: "Slow dancing in the Sun ☀️ See you next week at home 🏠 @hbtoo.official 📹 @panu.mov",
      url: "https://www.instagram.com/anima.ent/p/DQZ-CTgDUUH/",
      type: "carousel"
    },
    {
      id: "DQU2v5hjfzd",
      image: "https://scontent-fco2-1.cdninstagram.com/v/t51.82787-15/572107578_17940247449084668_4365046759141024048_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=104&ig_cache_key=Mzc1Mjg2NTE1Nzc0MDI5OTYwNg%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjE0NDB4MTkxOC5zZHIuQzMifQ%3D%3D&_nc_ohc=nwQcJUxaf44Q7kNvwGJ6f9M&_nc_oc=Adltu-yss3QRqkeeKuYvIg0cihs2xsldS0U5nSt3jPZOG4Ljs_xPJ7_-3J_fv1Hntf8&_nc_ad=z-m&_nc_cid=1093&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=akcd34cA-M2REppeepaTJQ&oh=00_Afh9yzErsMyLKi6UcsqEcWGYTzSRgU3c_EdBCTgXYAolhA&oe=691BF27B",
      alt: "Fantastic people, fantastic moments ☀️ See you on November 7th with a special surprise 👀 Until the Sun Rises ☀️ 🏠 @hbtoo.official - Unreleased pics on Telegram, link in bio 🔗",
      url: "https://www.instagram.com/anima.ent/p/DQU2v5hjfzd/",
      type: "carousel"
    },
    {
      id: "DQKYuIHDcgm",
      image: "https://scontent-fco2-1.cdninstagram.com/v/t51.82787-15/569933904_17939818266084668_4269708733286495843_n.jpg?stp=dst-jpg_e35_p1080x1080_sh0.08_tt6&_nc_cat=104&ig_cache_key=Mzc0OTkxODM2MjY1OTgzMzg5NDE3OTM5ODE4MjYwMDg0NjY4.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjMzNzZ4NjAwMC5zZHIuQzMifQ%3D%3D&_nc_ohc=IcalF9EpbAEQ7kNvwFMF26I&_nc_oc=AdlD-PeZ2c8mFrJOZh6uCOsKCvKnEHlDxJBiuDGPDO9et17YDWz7h2FtbxqteCeHAuk&_nc_ad=z-m&_nc_cid=1093&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=akcd34cA-M2REppeepaTJQ&oh=00_AfiiWuj8Eq0yCS4QhQ949GDrj7diC-LlP4dG2asdeGxzdA&oe=691BDFE7",
      alt: "This is the Vision of Anima ☀️ See you tomorrow at home 🏠 @hbtoo.official 📹 @panu.mov",
      url: "https://www.instagram.com/anima.ent/reel/DQKYuIHDcgm/",
      type: "reel"
    },
    {
      id: "DQHFiV6DZlG",
      image: "https://scontent-fco2-1.cdninstagram.com/v/t51.82787-15/569956739_17939671992084668_4996891839792711689_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=102&ig_cache_key=Mzc0ODk4OTU0ODcyNzQ4NDY0MQ%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjE0NDB4MTkyMC5zZHIuQzMifQ%3D%3D&_nc_ohc=-xEAkXlkdDsQ7kNvwES0NGr&_nc_oc=AdlCtQu7T2BT8nupRGQeqcnP3M569zopidvnG0K5Fir9-SP0y0bhXTMi8IIr-NqY7YY&_nc_ad=z-m&_nc_cid=1093&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=akcd34cA-M2REppeepaTJQ&oh=00_Afjs8JH2OKdmWSMIbwGSG2hcRtZj3jFaLMaRbStiw1j5bg&oe=691BE891",
      alt: "Moments from Friday ☀️ thanks to our friends from @dolcevita__rome for joining us for a fantastic party Every Friday at @hbtoo.official Until the Sun Rises ☀️ - Unreleased pics on Telegram, link in bio 🔗",
      url: "https://www.instagram.com/anima.ent/p/DQHFiV6DZlG/",
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

