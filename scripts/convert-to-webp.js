const path = require("path")
const sharp = require("sharp")

const [, , inputArg, outputArg] = process.argv

if (!inputArg || !outputArg) {
  console.error("Usage: node scripts/convert-to-webp.js <input> <output>")
  process.exit(1)
}

const inputPath = path.resolve(process.cwd(), inputArg)
const outputPath = path.resolve(process.cwd(), outputArg)

async function run() {
  try {
    console.log(`Converting JPG to WEBP...`)
    console.log(`Input:  ${inputPath}`)
    console.log(`Output: ${outputPath}`)

    await sharp(inputPath)
      .webp({ quality: 85 })
      .toFile(outputPath)

    console.log("Conversion completed successfully.")
  } catch (err) {
    console.error("Error during WEBP conversion:")
    console.error(err)
    process.exit(1)
  }
}

run()

