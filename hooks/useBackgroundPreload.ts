import { useEffect } from 'react'

export function useBackgroundPreload() {
  useEffect(() => {
    // Preload second carousel images in background after initial load
    const secondCarouselImages = [
      '/immagini-optimized/IMG_9662.webp',
      '/immagini-optimized/IMG_9663.webp',
      '/immagini-optimized/IMG_9664.webp',
      '/immagini-optimized/IMG_9665.webp',
      '/immagini-optimized/IMG_9667.webp',
      '/immagini-optimized/IMG_9668.webp',
      '/immagini-optimized/IMG_9669.webp',
      '/immagini-optimized/IMG_9670.webp',
      '/immagini-optimized/IMG_9671.webp',
      '/immagini-optimized/IMG_9672.webp',
      '/immagini-optimized/IMG_9673.webp',
      '/immagini-optimized/IMG_9674.webp',
      '/immagini-optimized/IMG_9675.webp',
    ]

    const guestImages = [
      '/IMG_5628-optimized.webp',
      '/c_EPbQeA.webp',
      '/pexels-khanshaheb-optimized.webp',
      '/Marco-Lys-optimized.webp',
      '/Screenshot-optimized.webp',
      '/IMG_9690.webp',
      '/4-optimized.webp',
    ]

    const allBackgroundImages = [...secondCarouselImages, ...guestImages]

    // Start preloading after a short delay to not interfere with critical assets
    const timeoutId = setTimeout(() => {
      allBackgroundImages.forEach((src) => {
        const img = new Image()
        img.src = src
      })
    }, 1000) // Start after 1 second

    return () => clearTimeout(timeoutId)
  }, [])
}

