const fs = require('fs');
const path = require('path');

// Dati estratti manualmente dalla pagina Instagram di anima.ent
const instagramPosts = [
  {
    id: "DQErm0FDFUW",
    image: "https://scontent-fco2-1.cdninstagram.com/v/t51.82787-15/568781270_18075461702137838_6321427613533355128_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=104&ig_cache_key=Mzc0ODMxMjU3MzEzODQ1Nzg3ODE4MDc1NDYxNjk2MTM3ODM4.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjcyMHgxMjgwLnNkci5DMyJ9&_nc_ohc=zkgP9gnbmwoQ7kNvwFGFiar&_nc_oc=Adl1e78yHZzrEUaN1ahiSre2x6MF5pVDRsBKUf2YlzKVup1Cx_Ba6KfFKqVufUuwq5c&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=9r4TgSCtrbrc7ZFizhi5Uw&oh=00_Afd6wLDfqzbtBJm7Wb9zWbK4wIUt3i593GPLDditOT4ETA&oe=68FD8CB5",
    alt: "25.10 // Under the same sun, again☀️ Dolcevita meets @anima.ent at @theflatbymacan #Dolcevita #porscheitalia",
    url: "https://www.instagram.com/dolcevita__rome/reel/DQErm0FDFUW/",
    type: "reel"
  },
  {
    id: "DQCq_v6Ddxs",
    image: "https://scontent-fco2-1.cdninstagram.com/v/t51.71878-15/567353087_1219370173568255_3348238122376727574_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=108&ig_cache_key=Mzc0Nzc0NjkzODY0NjAyNzM3Mg%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjY0MHgxMTM2LnNkci5DMyJ9&_nc_ohc=DeoQeavbxVoQ7kNvwEcAhrn&_nc_oc=AdkVRt8JdTDRUI4hf-KmF4tJw2hQ4wNBKGkRzC1MlvE6JRpZ59kFPyRWjB8RHsw_bMQ&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=9r4TgSCtrbrc7ZFizhi5Uw&oh=00_Aff9VlI9uCEDb74iKHropFqjrLeAVV4NwAwbqIj_RRBmZA&oe=68FD74E3",
    alt: "Welcome to the Sunrise Society ☀️ Season 3 — Act III Until the Sun rises ☀️ Anima Dj Booth | A-Z order @alexsilvestrimusic @1xloco @mattia_scodellaro @monamii.music 🏠 @hbtoo.official",
    url: "https://www.instagram.com/anima.ent/reel/DQCq_v6Ddxs/",
    type: "reel"
  },
  {
    id: "DP4YVf3jcxT",
    image: "https://scontent-fco2-1.cdninstagram.com/v/t51.71878-15/566628690_1324336816037974_4379666572277382920_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=109&ig_cache_key=Mzc0NDg1MDEyMDYwMTg4MTY4Mw%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjY0MHgxMTM2LnNkci5DMyJ9&_nc_ohc=sLDZvenno_AQ7kNvwEm364w&_nc_oc=Adk7vwXL-AmNNn9525O6Uv3oQ_ygih3Mpx0bi3A96euAHOGAh2GkrY7zPtOHLVi7Mlk&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=9r4TgSCtrbrc7ZFizhi5Uw&oh=00_Afd6HbinNd-tQOQ_QHKtqbyJvV2YUHOR4AzXyuD-JwZUqA&oe=68FD677A",
    alt: "Flashes from an outstanding first night ☀️ Third Season has officially begun See you tomorrow at home 🏠 @hbtoo.official 📹 @panu.mov",
    url: "https://www.instagram.com/anima.ent/reel/DP4YVf3jcxT/",
    type: "reel"
  },
  {
    id: "DPyfZ9nDR9r",
    image: "https://scontent-fco2-1.cdninstagram.com/v/t51.82787-15/565824898_17938858926084668_7817380969821783136_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=108&ig_cache_key=Mzc0MzE5MjM0ODE5MzgyNjg1OA%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjE0NDB4MTkyMC5zZHIuQzMifQ%3D%3D&_nc_ohc=EcE2oYQSJekQ7kNvwHDpudT&_nc_oc=AdlmOVYP-CTT8SNWawKRYBIIteIdCvzHb3V3embaJDxDIxYmGRX85U5c_3R2RUGzPP4&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=9r4TgSCtrbrc7ZFizhi5Uw&oh=00_AfeS8tErRtRvxIAa7wWF6lkpDkpyBi_pkn0FXQ2ZOSQCTg&oe=68FD7D08",
    alt: "The faces of a new chapter ☀️ Every Friday at @hbtoo.official Until the Sun Rises ☀️ - Unreleased pics on Telegram, link in bio 🔗",
    url: "https://www.instagram.com/anima.ent/p/DPyfZ9nDR9r/",
    type: "post"
  },
  {
    id: "DPwsmaJjZrw",
    image: "https://scontent-fco2-1.cdninstagram.com/v/t51.71878-15/563916359_1076013237753036_4760748830051411946_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=108&ig_cache_key=Mzc0MjY4NzQ0MzgwOTA0OTMyOA%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjY0MHgxMTM2LnNkci5DMyJ9&_nc_ohc=79I3-daHUVoQ7kNvwFIUr4Z&_nc_oc=AdlDcWMywXydFiYXVfpQ9rfDG41x--0pvg_IhLtSD6kgQri45wRqOCumzb8k6dHjiwU&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=9r4TgSCtrbrc7ZFizhi5Uw&oh=00_Afc9RSfWUQdf3VZWhPoQsTEJMjaaAZVjGfyI98umpy7Kjw&oe=68FD6F06",
    alt: "Two worlds illuminated by the same Sun ☀️ Anima meets @dolcevita__rome for an unforgettable party 🤝🏻 Season 3 — Act II Until the Sun rises ☀️ @gianni_presutti from @dolcevita__rome @alexsilvestrimusic b2b @marenna.music @leolitterio 🏠 @hbtoo.official",
    url: "https://www.instagram.com/anima.ent/reel/DPwsmaJjZrw/",
    type: "reel"
  },
  {
    id: "DPmXRBPDZpQ",
    image: "https://scontent-fco2-1.cdninstagram.com/v/t51.82787-15/562537022_17938377252084668_1943177837770365742_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=102&ig_cache_key=MzczOTc3ODg1NzUxMjU5ODU0OA%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjE0NDB4MTgwMC5zZHIuQzMifQ%3D%3D&_nc_ohc=ZvH4jC2D-aIQ7kNvwEZBRoH&_nc_oc=AdnyakEOU7x65thfJ9isQg2EDPwvti1bO0RGiiUen8vgv9SyYQ8yCI3NdDr_C1zuDW4&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=9r4TgSCtrbrc7ZFizhi5Uw&oh=00_Afe3kvO6lgzEJL2nywZQrZgza6NjuEV7DiT7CUE-BIOzWg&oe=68FD754F",
    alt: "Community guidelines ☀ Learn how could you be part of Anima Until the Sun rises ☀ See you tomorrow at 🏠 @hbtoo.official",
    url: "https://www.instagram.com/anima.ent/p/DPmXRBPDZpQ/",
    type: "post"
  },
  {
    id: "DPerq-sDTNO",
    image: "https://scontent-fco2-1.cdninstagram.com/v/t51.71878-15/561648270_1969327337249313_8473843443143903905_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=111&ig_cache_key=MzczNzYxNjgxMDI5MzE0NjQ0Ng%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjY0MHgxMTM2LnNkci5DMyJ9&_nc_ohc=2fPJWW5aJ6sQ7kNvwEM5dgF&_nc_oc=Admgsfv0Sqt5a09mxIlNsbSVAErZ0-zSrLeXVrvESLeQzLfLt46skAFYz-Oy_fb4M_Q&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=9r4TgSCtrbrc7ZFizhi5Uw&oh=00_AfdfW9Cn_MHDIH6wQaChJ0mEzT2_dvJJHLGe7mhLsGlIsA&oe=68FD8977",
    alt: "We are back. For the third year in a row, Anima is here to shine once again ☀️ Season 3 — Act I Until the Sun rises ☀️ Anima Dj Booth | A-Z order @alexsilvestrimusic @carlogiorgetto_ @hoodiamusic @manuelguida 🏠 @hbtoo.official",
    url: "https://www.instagram.com/anima.ent/reel/DPerq-sDTNO/",
    type: "reel"
  },
  {
    id: "DPUUmuLDe04",
    image: "https://scontent-fco2-1.cdninstagram.com/v/t51.82787-15/558981942_17937663765084668_2580632001886427385_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=109&ig_cache_key=MzczNDcwMDYxMjg0NDkwNzgzMjE3OTM3NjYzNzU5MDg0NjY4.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjY2MngxMTc2LnNkci5DMyJ9&_nc_ohc=7wGDOhYe8ssQ7kNvwEWqVYv&_nc_oc=AdmXLqW126qfwKwJRKumhiJ-IGEn2HKgm4U7bI7g6v2xA5pwVcpsu8O0PW_vKlUvH5Q&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=9r4TgSCtrbrc7ZFizhi5Uw&oh=00_Afdk5Egf1rRm5eCCyTWnuI7z-nbKO9iT5__vqJFBoh1prw&oe=68FD80EB",
    alt: "Season 3 begins. ANIMA — Act I The journey continues soon Until the Sun rises ☀️ 🏠 @hbtoo.official Directed & recorded by @panu.mov",
    url: "https://www.instagram.com/anima.ent/reel/DPUUmuLDe04/",
    type: "reel"
  },
  {
    id: "DGvPNWUiuof",
    image: "https://scontent-fco2-1.cdninstagram.com/v/t51.75761-15/482485279_17913146148084668_746769597459307949_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=107&ig_cache_key=MzU4MDE0NzA4OTIwMjA5ODkzNA%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjE0NDB4MTc5Ni5zZHIuQzMifQ%3D%3D&_nc_ohc=UJ5mtQzg-fsQ7kNvwGp_TJ7&_nc_oc=AdlBLb7d-W5FHl19vTAVcqm9Mb-wElb57VsnKkxgKJnpS1vFsl2dFMsX93rRuXx-gH0&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent-fco2-1.cdninstagram.com&_nc_gid=9r4TgSCtrbrc7ZFizhi5Uw&oh=00_AfeRFZMXV4cZ7Xlo27uBJh_MdQrcKo9AyS52dodOsHa0kA&oe=68FD6138",
    alt: "Paint the club red 🚨 Every Friday at @hbtoo.official Until the Sun Rises ☀️ - Unreleased pics on Telegram, link in bio 🔗 #anima #club #clubbing #housemusic #techhouse #afrohouse",
    url: "https://www.instagram.com/anima.ent/p/DGvPNWUiuof/",
    type: "post"
  }
];

// Salva i dati in un file JSON
const outputPath = path.join(__dirname, '../data/instagram-posts.json');
const outputDir = path.dirname(outputPath);

if (!fs.existsSync(outputDir)) {
  fs.mkdirSync(outputDir, { recursive: true });
}

fs.writeFileSync(outputPath, JSON.stringify(instagramPosts, null, 2));

console.log('✅ Instagram posts salvati in:', outputPath);
console.log(`📊 Totale post estratti: ${instagramPosts.length}`);
console.log('\n📋 Lista post:');
instagramPosts.forEach((post, index) => {
  console.log(`${index + 1}. ${post.id} (${post.type})`);
  console.log(`   Alt: ${post.alt.substring(0, 50)}...`);
  console.log(`   URL: ${post.url}`);
  console.log('');
});

