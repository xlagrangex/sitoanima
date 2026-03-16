/**
 * Client-side image optimization utility.
 * Converts images to WebP and compresses them before upload.
 */

export interface OptimizedImage {
  blob: Blob
  width: number
  height: number
  originalSize: number
  optimizedSize: number
  fileName: string
}

const MAX_WIDTH = 2048
const MAX_HEIGHT = 2048
const QUALITY = 0.82

/**
 * Optimize an image file: resize if needed, convert to WebP, compress.
 */
export async function optimizeImage(
  file: File,
  options?: {
    maxWidth?: number
    maxHeight?: number
    quality?: number
    forceWebP?: boolean
  }
): Promise<OptimizedImage> {
  const maxWidth = options?.maxWidth ?? MAX_WIDTH
  const maxHeight = options?.maxHeight ?? MAX_HEIGHT
  const quality = options?.quality ?? QUALITY
  const forceWebP = options?.forceWebP ?? true

  // If it's a video, skip optimization
  if (file.type.startsWith("video/")) {
    return {
      blob: file,
      width: 0,
      height: 0,
      originalSize: file.size,
      optimizedSize: file.size,
      fileName: file.name,
    }
  }

  return new Promise((resolve, reject) => {
    const img = new Image()
    const url = URL.createObjectURL(file)

    img.onload = () => {
      URL.revokeObjectURL(url)

      let { width, height } = img

      // Resize if needed, maintaining aspect ratio
      if (width > maxWidth || height > maxHeight) {
        const ratio = Math.min(maxWidth / width, maxHeight / height)
        width = Math.round(width * ratio)
        height = Math.round(height * ratio)
      }

      // Draw onto canvas
      const canvas = document.createElement("canvas")
      canvas.width = width
      canvas.height = height
      const ctx = canvas.getContext("2d")
      if (!ctx) {
        reject(new Error("Could not get canvas context"))
        return
      }

      ctx.drawImage(img, 0, 0, width, height)

      // Check WebP support and convert
      const mimeType = forceWebP ? "image/webp" : file.type
      const extension = forceWebP ? "webp" : file.name.split(".").pop() || "webp"

      canvas.toBlob(
        (blob) => {
          if (!blob) {
            reject(new Error("Could not create blob"))
            return
          }

          // Generate clean filename
          const baseName = file.name
            .replace(/\.[^.]+$/, "")
            .replace(/[^a-zA-Z0-9-_]/g, "-")
            .toLowerCase()
          const fileName = `${baseName}.${extension}`

          resolve({
            blob,
            width,
            height,
            originalSize: file.size,
            optimizedSize: blob.size,
            fileName,
          })
        },
        mimeType,
        quality
      )
    }

    img.onerror = () => {
      URL.revokeObjectURL(url)
      reject(new Error("Could not load image"))
    }

    img.src = url
  })
}

/**
 * Format file size for display
 */
export function formatFileSize(bytes: number): string {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}
