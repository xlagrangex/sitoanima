const fs = require('fs');
const path = require('path');
const https = require('https');
const http = require('http');
const sharp = require('sharp');

const APIFY_TOKEN = process.env.APIFY_TOKEN;
const INSTAGRAM_HANDLE = 'https://www.instagram.com/anima.ent/';
const POSTS_LIMIT = 12; // Fetch extra in case some fail
const DISPLAY_LIMIT = 9;

if (!APIFY_TOKEN) {
  console.error('ERROR: APIFY_TOKEN environment variable is required');
  process.exit(1);
}

// Fetch JSON from URL
function fetchJSON(url) {
  return new Promise((resolve, reject) => {
    const protocol = url.startsWith('https') ? https : http;
    protocol.get(url, (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => {
        try {
          resolve(JSON.parse(data));
        } catch (e) {
          reject(new Error(`Failed to parse JSON: ${data.slice(0, 200)}`));
        }
      });
    }).on('error', reject);
  });
}

// POST request
function postJSON(url, body) {
  return new Promise((resolve, reject) => {
    const urlObj = new URL(url);
    const options = {
      hostname: urlObj.hostname,
      path: urlObj.pathname + urlObj.search,
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    };
    const req = https.request(options, (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => {
        try {
          resolve(JSON.parse(data));
        } catch (e) {
          reject(new Error(`Failed to parse JSON: ${data.slice(0, 200)}`));
        }
      });
    });
    req.on('error', reject);
    req.write(JSON.stringify(body));
    req.end();
  });
}

// Download image to file
function downloadImage(url, filepath) {
  return new Promise((resolve, reject) => {
    const protocol = url.startsWith('https') ? https : http;
    const file = fs.createWriteStream(filepath);
    const request = protocol.get(url, {
      headers: {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'Accept': 'image/webp,image/apng,image/*,*/*;q=0.8'
      }
    }, (response) => {
      if (response.statusCode === 301 || response.statusCode === 302) {
        file.close();
        return downloadImage(response.headers.location, filepath).then(resolve).catch(reject);
      }
      if (response.statusCode !== 200) {
        file.close();
        if (fs.existsSync(filepath)) fs.unlinkSync(filepath);
        return reject(new Error(`HTTP ${response.statusCode} for ${url.slice(0, 80)}`));
      }
      response.pipe(file);
      file.on('finish', () => { file.close(); resolve(); });
    });
    request.on('error', (err) => {
      file.close();
      if (fs.existsSync(filepath)) fs.unlinkSync(filepath);
      reject(err);
    });
  });
}

// Crop image to 3:4 aspect ratio
async function cropImageTo34(inputPath, outputPath) {
  const metadata = await sharp(inputPath).metadata();
  const { width, height } = metadata;
  const targetRatio = 3 / 4;
  const imageRatio = width / height;

  let cropWidth, cropHeight, left, top;
  if (imageRatio > targetRatio) {
    cropHeight = height;
    cropWidth = Math.round(height * targetRatio);
    left = Math.round((width - cropWidth) / 2);
    top = 0;
  } else {
    cropWidth = width;
    cropHeight = Math.round(width / targetRatio);
    left = 0;
    top = Math.round((height - cropHeight) / 2);
  }

  await sharp(inputPath)
    .extract({ left, top, width: cropWidth, height: cropHeight })
    .resize(600, 800, { fit: 'cover' })
    .jpeg({ quality: 85 })
    .toFile(outputPath);
}

// Wait for Apify run to finish
async function waitForRun(runId) {
  const maxWait = 5 * 60 * 1000; // 5 minutes
  const interval = 5000;
  const start = Date.now();

  while (Date.now() - start < maxWait) {
    const run = await fetchJSON(
      `https://api.apify.com/v2/actor-runs/${runId}?token=${APIFY_TOKEN}`
    );
    console.log(`  Run status: ${run.data.status}`);
    if (run.data.status === 'SUCCEEDED') return run.data;
    if (run.data.status === 'FAILED' || run.data.status === 'ABORTED') {
      throw new Error(`Apify run ${run.data.status}`);
    }
    await new Promise(r => setTimeout(r, interval));
  }
  throw new Error('Apify run timed out after 5 minutes');
}

async function main() {
  const outputDir = path.join(__dirname, '../public/instagram-posts');
  const croppedDir = path.join(__dirname, '../public/instagram-posts-cropped');
  const jsonPath = path.join(__dirname, '../data/instagram-posts.json');
  const publicJsonPath = path.join(__dirname, '../public/data/instagram-posts.json');

  fs.mkdirSync(outputDir, { recursive: true });
  fs.mkdirSync(croppedDir, { recursive: true });
  fs.mkdirSync(path.dirname(publicJsonPath), { recursive: true });

  // 1. Start Apify Instagram Scraper
  console.log('Starting Apify Instagram Scraper for @anima.ent...');
  const runResult = await postJSON(
    `https://api.apify.com/v2/acts/apify~instagram-scraper/runs?token=${APIFY_TOKEN}`,
    {
      directUrls: [INSTAGRAM_HANDLE],
      resultsType: 'posts',
      resultsLimit: POSTS_LIMIT,
      searchType: 'hashtag',
      searchLimit: 1,
      addParentData: false
    }
  );

  const runId = runResult.data.id;
  const datasetId = runResult.data.defaultDatasetId;
  console.log(`Run started: ${runId}`);

  // 2. Wait for completion
  console.log('Waiting for scraper to finish...');
  await waitForRun(runId);
  console.log('Scraper finished!');

  // 3. Fetch results from dataset
  console.log('Fetching results...');
  const dataset = await fetchJSON(
    `https://api.apify.com/v2/datasets/${datasetId}/items?token=${APIFY_TOKEN}&limit=${POSTS_LIMIT}`
  );

  if (!dataset || dataset.length === 0) {
    console.error('No posts returned from Apify');
    process.exit(1);
  }

  console.log(`Got ${dataset.length} posts from Apify`);

  // 4. Process each post
  const updatedPosts = [];

  for (let i = 0; i < Math.min(dataset.length, POSTS_LIMIT); i++) {
    const item = dataset[i];
    const postId = item.shortCode || item.id;
    if (!postId) continue;

    // Determine post type
    let type = 'post';
    if (item.type === 'Video' || item.videoUrl) type = 'reel';
    else if (item.type === 'Sidecar' || (item.childPosts && item.childPosts.length > 0)) type = 'carousel';

    // Get image URL - prefer displayUrl or first image
    const imageUrl = item.displayUrl || item.imageUrl ||
      (item.images && item.images[0]) ||
      (item.childPosts && item.childPosts[0] && item.childPosts[0].displayUrl);

    if (!imageUrl) {
      console.log(`  [${i + 1}] Skipping ${postId} - no image URL`);
      continue;
    }

    const caption = (item.caption || `Instagram post ${postId}`).replace(/\n/g, ' ').replace(/\s+/g, ' ').trim();
    const postUrl = item.url || `https://www.instagram.com/anima.ent/${type === 'reel' ? 'reel' : 'p'}/${postId}/`;

    const filename = `${postId}.jpg`;
    const filepath = path.join(outputDir, filename);
    const croppedPath = path.join(croppedDir, filename);

    try {
      // Download image
      console.log(`  [${i + 1}/${dataset.length}] Downloading ${postId}...`);
      await downloadImage(imageUrl, filepath);

      // Crop to 3:4
      console.log(`  [${i + 1}/${dataset.length}] Cropping ${postId}...`);
      await cropImageTo34(filepath, croppedPath);

      updatedPosts.push({
        id: postId,
        image: `/instagram-posts-cropped/${filename}`,
        alt: caption.slice(0, 300),
        url: postUrl,
        type: type,
        originalImage: `/instagram-posts/${filename}`
      });

      console.log(`  [${i + 1}/${dataset.length}] OK ${postId}`);
    } catch (err) {
      console.error(`  [${i + 1}/${dataset.length}] FAILED ${postId}: ${err.message}`);
    }
  }

  if (updatedPosts.length === 0) {
    console.error('No posts were processed successfully');
    process.exit(1);
  }

  // 5. Save JSON (max 9 posts)
  const finalPosts = updatedPosts.slice(0, DISPLAY_LIMIT);
  const jsonContent = JSON.stringify(finalPosts, null, 2);
  fs.writeFileSync(jsonPath, jsonContent);
  fs.writeFileSync(publicJsonPath, jsonContent);
  console.log(`\nDone! Updated ${finalPosts.length} posts in ${jsonPath} and ${publicJsonPath}`);
}

main().catch(err => {
  console.error('Fatal error:', err.message);
  process.exit(1);
});
