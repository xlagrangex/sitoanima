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
      id: "DQukSqwjOXU",
      image: "https://instagram.fnap3-2.fna.fbcdn.net/v/t51.2885-15/572783675_1500811864498570_841119763539006715_n.jpg?stp=dst-jpg_e15_tt6&_nc_ht=instagram.fnap3-2.fna.fbcdn.net&_nc_cat=110&_nc_oc=Q6cZ2QE0kOFMzKtS28QrN88V5rOFlfrxiIaYaj5N-6U2V6rxrKsquiJXh-AGXsbyRK9JRSY&_nc_ohc=VGITnqnmVfgQ7kNvwHWAjMe&_nc_gid=w07hdN8ghNX8iUFUEASMeQ&edm=AOQ1c0wBAAAA&ccb=7-5&oh=00_AfilG51zlxmNa08XqYgOghHvioisIBwuyhpCzj3UCqKEEw&oe=6917A900&_nc_sid=8b3546",
      alt: "A short view on who @piccaemars are 👀 Tomorrow is the night and we are so ready to party with you at @hbtoo.official Until the Sun Rises ☀️",
      url: "https://www.instagram.com/anima.ent/reel/DQukSqwjOXU/",
      type: "reel"
    },
    {
      id: "DQr87zuDWbB",
      image: "https://instagram.fnap3-1.fna.fbcdn.net/v/t51.2885-15/573608511_1517178756267738_1413521016259122972_n.jpg?stp=dst-jpg_e15_tt6&_nc_ht=instagram.fnap3-1.fna.fbcdn.net&_nc_cat=106&_nc_oc=Q6cZ2QE0kOFMzKtS28QrN88V5rOFlfrxiIaYaj5N-6U2V6rxrKsquiJXh-AGXsbyRK9JRSY&_nc_ohc=wB41gMKD7RYQ7kNvwEBAtK3&_nc_gid=w07hdN8ghNX8iUFUEASMeQ&edm=AOQ1c0wBAAAA&ccb=7-5&oh=00_Afg4E7Oa9BaudsvjJ6xZQnnhS6Hmerq04t6GGHLsfwj6TA&oe=69179946&_nc_sid=8b3546",
      alt: "Anima official website ☀️ now online Check the bio or search \"animaent.club\"",
      url: "https://www.instagram.com/anima.ent/reel/DQr87zuDWbB/",
      type: "reel"
    },
    {
      id: "DQm1jvSje7N",
      image: "https://instagram.fnap3-1.fna.fbcdn.net/v/t51.2885-15/574348684_1482981756318255_4099326794748306032_n.jpg?stp=dst-jpg_e15_tt6&_nc_ht=instagram.fnap3-1.fna.fbcdn.net&_nc_cat=103&_nc_oc=Q6cZ2QE0kOFMzKtS28QrN88V5rOFlfrxiIaYaj5N-6U2V6rxrKsquiJXh-AGXsbyRK9JRSY&_nc_ohc=o1yswSiZtK8Q7kNvwHsbMFX&_nc_gid=w07hdN8ghNX8iUFUEASMeQ&edm=AOQ1c0wBAAAA&ccb=7-5&oh=00_Afj_OHTfJrsjc1HDHOpoY_XE3vcjgL16gqAn2-8aUxt-1Q&oe=69178C66&_nc_sid=8b3546",
      alt: "Here again to shine bright, together ☀️ Season 3 — Act IV Until the Sun rises ☀️ SPECIAL GUEST @piccaemars from @soundsvalley_ Anima Dj Booth | A-Z order @alexsilvestrimusic @marenna.music 🏠 @hbtoo.official",
      url: "https://www.instagram.com/anima.ent/reel/DQm1jvSje7N/",
      type: "reel"
    },
    {
      id: "DQkRNyFjSdP",
      image: "https://instagram.fnap3-2.fna.fbcdn.net/v/t51.2885-15/572690095_17940879552084668_4842062469339281980_n.jpg?stp=dst-jpg_e35_p640x640_sh0.08_tt6&_nc_ht=instagram.fnap3-2.fna.fbcdn.net&_nc_cat=108&_nc_oc=Q6cZ2QE0kOFMzKtS28QrN88V5rOFlfrxiIaYaj5N-6U2V6rxrKsquiJXh-AGXsbyRK9JRSY&_nc_ohc=66nX_n_Y48AQ7kNvwGvUTqS&_nc_gid=w07hdN8ghNX8iUFUEASMeQ&edm=AOQ1c0wBAAAA&ccb=7-5&oh=00_AfjxZctzVxpUosd_IfIdmn5guz2jn7w-RoFp3n2BS_nnnA&oe=6917AECE&_nc_sid=8b3546",
      alt: "Anima introduces the first Guest of the year: ladies and gentlemen, please welcome to @piccaemars ☀️ Groovy basslines, hypnotic melodies, and unstoppable energy. A fusion of electronic, house, afro & indie dance that make every crowd dance Picca & Mars are not just DJs: they're a whole, huge, Made in Italy movement. Now they are ready to make YOU dance on Friday 7th November at @hbtoo.official Until the Sun Rises ☀️",
      url: "https://www.instagram.com/anima.ent/p/DQkRNyFjSdP/",
      type: "carousel"
    },
    {
      id: "DQZ-CTgDUUH",
      image: "https://instagram.fnap3-2.fna.fbcdn.net/v/t51.2885-15/573083345_17940452799084668_7653385790377702239_n.jpg?stp=dst-jpg_e15_s640x640_tt6&_nc_ht=instagram.fnap3-2.fna.fbcdn.net&_nc_cat=108&_nc_oc=Q6cZ2QE0kOFMzKtS28QrN88V5rOFlfrxiIaYaj5N-6U2V6rxrKsquiJXh-AGXsbyRK9JRSY&_nc_ohc=O6joyHTM_cEQ7kNvwE-Hm4K&_nc_gid=w07hdN8ghNX8iUFUEASMeQ&edm=AOQ1c0wBAAAA&ccb=7-5&oh=00_AfguK1TTnB0vKYN7U3GS-BsvUlVWptxfa3pNzhmZXNRilA&oe=6917B0CB&_nc_sid=8b3546",
      alt: "Slow dancing in the Sun ☀️ See you next week at home 🏠 @hbtoo.official 📹 @panu.mov",
      url: "https://www.instagram.com/anima.ent/p/DQZ-CTgDUUH/",
      type: "carousel"
    },
    {
      id: "DQU2v5hjfzd",
      image: "https://instagram.fnap3-2.fna.fbcdn.net/v/t51.2885-15/572107578_17940247449084668_4365046759141024048_n.jpg?stp=dst-jpg_e35_p640x640_sh0.08_tt6&_nc_ht=instagram.fnap3-2.fna.fbcdn.net&_nc_cat=108&_nc_oc=Q6cZ2QE0kOFMzKtS28QrN88V5rOFlfrxiIaYaj5N-6U2V6rxrKsquiJXh-AGXsbyRK9JRSY&_nc_ohc=ZbzLKNCZBmgQ7kNvwGir8Hj&_nc_gid=w07hdN8ghNX8iUFUEASMeQ&edm=AOQ1c0wBAAAA&ccb=7-5&oh=00_AfgtXtiHiQvqRgVL6XrJO1OV2NunL4f6klmX7McDSpx3GQ&oe=69179C2D&_nc_sid=8b3546",
      alt: "Fantastic people, fantastic moments ☀️ See you on November 7th with a special surprise 👀 Until the Sun Rises ☀️ 🏠 @hbtoo.official - Unreleased pics on Telegram, link in bio 🔗",
      url: "https://www.instagram.com/anima.ent/p/DQU2v5hjfzd/",
      type: "carousel"
    },
    {
      id: "DQKYuIHDcgm",
      image: "https://instagram.fnap3-2.fna.fbcdn.net/v/t51.2885-15/569933904_17939818266084668_4269708733286495843_n.jpg?stp=dst-jpg_e35_p640x640_sh0.08_tt6&_nc_ht=instagram.fnap3-2.fna.fbcdn.net&_nc_cat=108&_nc_oc=Q6cZ2QE0kOFMzKtS28QrN88V5rOFlfrxiIaYaj5N-6U2V6rxrKsquiJXh-AGXsbyRK9JRSY&_nc_ohc=Juqx284ySMcQ7kNvwGEqj26&_nc_gid=w07hdN8ghNX8iUFUEASMeQ&edm=AOQ1c0wBAAAA&ccb=7-5&oh=00_AfgpW_E4kTMPr_Uw-hPT3OKKJoQcJhSwID-RZaq8yp2HoQ&oe=6917A131&_nc_sid=8b3546",
      alt: "This is the Vision of Anima ☀️ See you tomorrow at home 🏠 @hbtoo.official 📹 @panu.mov",
      url: "https://www.instagram.com/anima.ent/reel/DQKYuIHDcgm/",
      type: "reel"
    },
    {
      id: "DQHFiV6DZlG",
      image: "https://instagram.fnap3-2.fna.fbcdn.net/v/t51.2885-15/569956739_17939671992084668_4996891839792711689_n.jpg?stp=dst-jpg_e35_p640x640_sh0.08_tt6&_nc_ht=instagram.fnap3-2.fna.fbcdn.net&_nc_cat=108&_nc_oc=Q6cZ2QE0kOFMzKtS28QrN88V5rOFlfrxiIaYaj5N-6U2V6rxrKsquiJXh-AGXsbyRK9JRSY&_nc_ohc=wO3_x6WdKjgQ7kNvwG5Ojbd&_nc_gid=w07hdN8ghNX8iUFUEASMeQ&edm=AOQ1c0wBAAAA&ccb=7-5&oh=00_AfjbjvIw7DAWZLfBYLteXpBEA03D6T0gr7LNtZmJablTnA&oe=69179847&_nc_sid=8b3546",
      alt: "Moments from Friday ☀️ thanks to our friends from @dolcevita__rome for joining us for a fantastic party Every Friday at @hbtoo.official Until the Sun Rises ☀️ - Unreleased pics on Telegram, link in bio 🔗",
      url: "https://www.instagram.com/anima.ent/p/DQHFiV6DZlG/",
      type: "carousel"
    },
    {
      id: "DQCq_v6Ddxs",
      image: "https://instagram.fnap3-1.fna.fbcdn.net/v/t51.71878-15/563916359_1076013237753036_4760748830051411946_n.jpg?stp=c0.248.640.640a_dst-jpg_e15_tt6&efg=eyJ2ZW5jb2RlX3RhZyI6ImltYWdlX3VybGdlbi42NDB4MTEzNi5zZHIuZjcxODc4Lm5mcmFtZV9jb3Zlcl9mcmFtZS5jMiJ9&_nc_ht=instagram.fnap3-1.fna.fbcdn.net&_nc_cat=100&_nc_oc=Q6cZ2QE0kOFMzKtS28QrN88V5rOFlfrxiIaYaj5N-6U2V6rxrKsquiJXh-AGXsbyRK9JRSY&_nc_ohc=kw4QOJ2uZ4kQ7kNvwFD5ahp&_nc_gid=w07hdN8ghNX8iUFUEASMeQ&edm=AOQ1c0wBAAAA&ccb=7-5&oh=00_AfhqhHb0vk6PtWqRLgfuI9NTHOoAIfKfdAMuZkCoQ_rSkw&oe=691790C7&_nc_sid=8b3546",
      alt: "Welcome to the Sunrise Society ☀️ Season 3 — Act III Until the Sun rises ☀️ Anima Dj Booth | A-Z order @alexsilvestrimusic @1xloco @mattia_scodellaro @monamii.music 🏠 @hbtoo.official",
      url: "https://www.instagram.com/anima.ent/reel/DQCq_v6Ddxs/",
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

