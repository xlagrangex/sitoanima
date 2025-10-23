import { useEffect } from 'react'

export function useBackgroundPreload() {
  useEffect(() => {
    // Preload second carousel images in background after initial load
    const secondCarouselImages = [
      '/immagini/IMG_9662.webp',
      '/immagini/IMG_9663.webp',
      '/immagini/IMG_9664.webp',
      '/immagini/IMG_9665.webp',
      '/immagini/IMG_9667.webp',
      '/immagini/IMG_9668.webp',
      '/immagini/IMG_9669.webp',
      '/immagini/IMG_9670.webp',
      '/immagini/IMG_9671.webp',
      '/immagini/IMG_9672.webp',
      '/immagini/IMG_9673.webp',
      '/immagini/IMG_9674.webp',
      '/immagini/IMG_9675.webp',
    ]

    const guestImages = [
      '/IMG_5628.webp',
      '/c_EPbQeA.webp',
      '/pexels-khanshaheb-17214950.webp',
      '/Marco Lys at Il Muretto 3.webp',
      '/Screenshot 2025-10-17 at 11.58.26.webp',
      '/IMG_9690.webp',
      '/4.webp',
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

