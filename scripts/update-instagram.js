const fs = require('fs');
const path = require('path');
const https = require('https');
const http = require('http');

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

// Aggiorna i post Instagram
async function updateInstagramPosts() {
  const outputDir = path.join(__dirname, '../public/instagram-posts');
  const jsonPath = path.join(__dirname, '../data/instagram-posts.json');
  
  // Dati estratti dall'HTML Instagram
  const posts = [
    {
      id: "DQU2v5hjfzd",
      image: "https://scontent.cdninstagram.com/v/t51.82787-15/572107578_17940247449084668_4365046759141024048_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=104&ig_cache_key=Mzc1Mjg2NTE1Nzc0MDI5OTYwNg%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjE0NDB4MTkxOC5zZHIuQzMifQ%3D%3D&_nc_ohc=lHthqVaWM3AQ7kNvwHBXXA0&_nc_oc=AdmuHhGCJmv3-hkQV5atlqzHg4FOmTXvkKbB8nOUJ77-zDp4ySQ9mBsSUAeMrDmCVec&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=_huofAksWA7tcKJU5Z0NfA&oh=00_Afc6VrYXrZkOupf2itCBXY5FoiOpxELTvTYU2pI4LOjdhA&oe=6907BB7B",
      alt: "Fantastic people, fantastic moments ☀️\n\nSee you on November 7th with a special surprise 👀\nUntil the Sun Rises ☀️\n\n🏠 @hbtoo.official \n-\nUnreleased pics on Telegram, link in bio 🔗",
      url: "https://www.instagram.com/anima.ent/p/DQU2v5hjfzd/",
      type: "carousel"
    },
    {
      id: "DQKYuIHDcgm",
      image: "https://scontent.cdninstagram.com/v/t51.82787-15/569933904_17939818266084668_4269708733286495843_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=104&ig_cache_key=Mzc0OTkxODM2MjY1OTgzMzg5NDE3OTM5ODE4MjYwMDg0NjY4.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjMzNzZ4NjAwMC5zZHIuQzMifQ%3D%3D&_nc_ohc=hddDbLMRd2oQ7kNvwG647xt&_nc_oc=Adli5WSxAYHqiL9hKh47fkzN2gnt3LGq_pZBrmCMg07p8a9UntRXoSw23F9WMj0vFQY&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=_huofAksWA7tcKJU5Z0NfA&oh=00_AfdltJ9QIMXfJN4TxJ0r1nLq55qcpglBmSaX1215qh6bug&oe=6907E127",
      alt: "This is the Vision of Anima ☀️\nSee you tomorrow at home 🏠 @hbtoo.official \n\n📹 @panu.mov",
      url: "https://www.instagram.com/anima.ent/reel/DQKYuIHDcgm/",
      type: "reel"
    },
    {
      id: "DQHFiV6DZlG",
      image: "https://scontent.cdninstagram.com/v/t51.82787-15/569956739_17939671992084668_4996891839792711689_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=102&ig_cache_key=Mzc0ODk4OTU0ODcyNzQ4NDY0MQ%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjE0NDB4MTkyMC5zZHIuQzMifQ%3D%3D&_nc_ohc=rXgvg-pzoLYQ7kNvwHe2dHb&_nc_oc=Adk-ts6GlsqT2kL_JZt-4qopaNvZjust0k_Iy1jya1wzKLLcHTJAonXpfxtoJy0A1NA&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=_huofAksWA7tcKJU5Z0NfA&oh=00_AfdV5Z0ZDAzgwpUp6SqRd5gt68JOni8IgUgbaGqys2iU9Q&oe=6907B191",
      alt: "Moments from Friday ☀️ thanks to our friends from @dolcevita__rome for joining us for a fantastic party \n\nEvery Friday at @hbtoo.official \nUntil the Sun Rises ☀️\n-\nUnreleased pics on Telegram, link in bio 🔗",
      url: "https://www.instagram.com/anima.ent/p/DQHFiV6DZlG/",
      type: "carousel"
    },
    {
      id: "DQCq_v6Ddxs",
      image: "https://scontent.cdninstagram.com/v/t51.71878-15/567353087_1219370173568255_3348238122376727574_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=108&ig_cache_key=Mzc0Nzc0NjkzODY0NjAyNzM3Mg%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjY0MHgxMTM2LnNkci5DMyJ9&_nc_ohc=ZzUqkelJ0iEQ7kNvwH2v1CB&_nc_oc=AdkdCnO375GqLBgkPfY6z6j96GR_NP5iAXQmT_6Hn5zjQOGSp6B5Od_5bG7uhl7mIpc&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=_huofAksWA7tcKJU5Z0NfA&oh=00_AfdnN5zBRYkMaFednTy74-Al63O5BaXf56Rg5W08j3KP4A&oe=6907C8A3",
      alt: "Welcome to the Sunrise Society ☀️\n\nSeason 3 — Act III\nUntil the Sun rises ☀️\n\nAnima Dj Booth | A-Z order\n\n@alexsilvestrimusic \n@1xloco \n@mattia_scodellaro \n@monamii.music \n\n🏠 @hbtoo.official",
      url: "https://www.instagram.com/anima.ent/reel/DQCq_v6Ddxs/",
      type: "reel"
    },
    {
      id: "DP4YVf3jcxT",
      image: "https://scontent.cdninstagram.com/v/t51.71878-15/566628690_1324336816037974_4379666572277382920_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=109&ig_cache_key=Mzc0NDg1MDEyMDYwMTg4MTY4Mw%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjY0MHgxMTM2LnNkci5DMyJ9&_nc_ohc=I3s-MAJbdeAQ7kNvwGjtzw9&_nc_oc=AdkJtzqlG380KKWvtpSQdvL2bdQ1MAKnPPNu5hQZzTCkfee61ICeWy-6SEm_leHUc5w&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=_huofAksWA7tcKJU5Z0NfA&oh=00_AfflrudEX21mdy7O4F_poLka7r_SNNgOWqVsycFOgvRY8g&oe=6907BB3A",
      alt: "Flashes from an outstanding first night ☀️\n\nThird Season has officially begun \nSee you tomorrow at home 🏠 @hbtoo.official \n\n📹 @panu.mov",
      url: "https://www.instagram.com/anima.ent/reel/DP4YVf3jcxT/",
      type: "reel"
    },
    {
      id: "DPyfZ9nDR9r",
      image: "https://scontent.cdninstagram.com/v/t51.82787-15/565824898_17938858926084668_7817380969821783136_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=108&ig_cache_key=Mzc0MzE5MjM0ODE5MzgyNjg1OA%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjE0NDB4MTkyMC5zZHIuQzMifQ%3D%3D&_nc_ohc=T57qL1wNWa4Q7kNvwHzcKWk&_nc_oc=Admq5sAQpH9B7BwL2oLh5gqOUbqvc8guhzVXn9_Ce2L1NbrA2VwTfaWvARMP_wF8avo&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=_huofAksWA7tcKJU5Z0NfA&oh=00_Afc5HeleuCVgtUhhfPxOUoYCA7ah6HM1QxxmiMPWYPcHYA&oe=6907D0C8",
      alt: "The faces of a new chapter ☀️\n\nEvery Friday at @hbtoo.official \nUntil the Sun Rises ☀️\n-\nUnreleased pics on Telegram, link in bio 🔗",
      url: "https://www.instagram.com/anima.ent/p/DPyfZ9nDR9r/",
      type: "carousel"
    },
    {
      id: "DPwsmaJjZrw",
      image: "https://scontent.cdninstagram.com/v/t51.71878-15/563916359_1076013237753036_4760748830051411946_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=108&ig_cache_key=Mzc0MjY4NzQ0MzgwOTA0OTMyOA%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjY0MHgxMTM2LnNkci5DMyJ9&_nc_ohc=UCSuwzC2POMQ7kNvwFmzBWH&_nc_oc=Adkm_vfoypkd61kOvigrbtFfK4JYg8cr0NCBmh1PPnUrdswTxAB5D6nuNfzjETTmUig&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=_huofAksWA7tcKJU5Z0NfA&oh=00_AfdLRCY_WK2EmQYCoLlDjf7bKKacmgknppJypwc0kz8fvA&oe=6907C2C6",
      alt: "Two worlds illuminated by the same Sun ☀️\nAnima meets @dolcevita__rome for an unforgettable party 🤝🏻 \n\nSeason 3 — Act II\nUntil the Sun rises ☀️\n\n@gianni_presutti from @dolcevita__rome \n@alexsilvestrimusic b2b @marenna.music \n@leolitterio \n\n🏠 @hbtoo.official",
      url: "https://www.instagram.com/anima.ent/reel/DPwsmaJjZrw/",
      type: "reel"
    },
    {
      id: "DPmXRBPDZpQ",
      image: "https://scontent.cdninstagram.com/v/t51.82787-15/562537022_17938377252084668_1943177837770365742_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=102&ig_cache_key=MzczOTc3ODg1NzUxMjU5ODU0OA%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjE0NDB4MTgwMC5zZHIuQzMifQ%3D%3D&_nc_ohc=T_IO9Cf2ppIQ7kNvwGAqf8b&_nc_oc=AdmkZ1Wxkh-pRmu32zY-d9A3Zs2H9ZUwAT6HUz3IsdfVA0mTEX2ZYrYk0BseKVNaE_Q&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=_huofAksWA7tcKJU5Z0NfA&oh=00_AfdaH2VW9PyEyFamNgPmEyOOcmNJN3AyQokWgYa6qcpOzw&oe=6907C90F",
      alt: "Community guidelines ☀\nLearn how could you be part of Anima\n\nUntil the Sun rises ☀\nSee you tomorrow at 🏠 @hbtoo.official",
      url: "https://www.instagram.com/anima.ent/p/DPmXRBPDZpQ/",
      type: "carousel"
    },
    {
      id: "DPerq-sDTNO",
      image: "https://scontent.cdninstagram.com/v/t51.71878-15/561648270_1969327337249313_8473843443143903905_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=111&ig_cache_key=MzczNzYxNjgxMDI5MzE0NjQ0Ng%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjY0MHgxMTM2LnNkci5DMyJ9&_nc_ohc=YG7hXi8086kQ7kNvwGJ2rgZ&_nc_oc=AdlJrDLvhSfwNwRCY9eAuKrLyCAGeMeZ42jhShfGKnw_qftEqMh3zPU9VSn42sTCUJw&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=_huofAksWA7tcKJU5Z0NfA&oh=00_Afd2niW-iyn15N02vZrQS8wz0TlBRy0Okirt1MFGFv2buA&oe=6907DD37",
      alt: "We are back. \nFor the third year in a row, Anima is here to shine once again ☀️\n\nSeason 3 — Act I\nUntil the Sun rises ☀️\n\nAnima Dj Booth | A-Z order\n\n@alexsilvestrimusic \n@carlogiorgetto_ \n@hoodiamusic \n@manuelguida \n\n🏠 @hbtoo.official",
      url: "https://www.instagram.com/anima.ent/reel/DPerq-sDTNO/",
      type: "reel"
    }
  ];
  
  if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
  }
  
  const updatedPosts = [];
  
  console.log(`Processing ${posts.length} posts...`);
  
  for (let i = 0; i < posts.length; i++) {
    const post = posts[i];
    try {
      const filename = `${post.id}.jpg`;
      const filepath = path.join(outputDir, filename);
      
      // Scarica l'immagine solo se non esiste già
      if (!fs.existsSync(filepath)) {
        console.log(`[${i + 1}/${posts.length}] Downloading ${post.id}...`);
        await downloadImage(post.image, filepath);
        console.log(`[${i + 1}/${posts.length}] ✓ Downloaded ${post.id}`);
      } else {
        console.log(`[${i + 1}/${posts.length}] ⊙ Skipping ${post.id} (already exists)`);
      }
      
      updatedPosts.push({
        id: post.id,
        image: `/instagram-posts/${filename}`,
        alt: post.alt.replace(/\n/g, ' ').replace(/\s+/g, ' ').trim(),
        url: post.url,
        type: post.type
      });
    } catch (error) {
      console.error(`[${i + 1}/${posts.length}] ✗ Error processing ${post.id}:`, error.message);
      // In caso di errore, usa l'URL originale
      updatedPosts.push({
        id: post.id,
        image: post.image,
        alt: post.alt.replace(/\n/g, ' ').replace(/\s+/g, ' ').trim(),
        url: post.url,
        type: post.type
      });
    }
  }
  
  // Salva il JSON aggiornato
  fs.writeFileSync(jsonPath, JSON.stringify(updatedPosts, null, 2));
  console.log(`\n✓ Updated ${updatedPosts.length} posts in ${jsonPath}`);
}

updateInstagramPosts().catch(console.error);
