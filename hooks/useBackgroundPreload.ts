import { useEffect } from 'react'

export function useBackgroundPreload() {
  useEffect(() => {
    // Preload second carousel images in background after initial load
    const secondCarouselImages = [
      '/immagini-optimized/IMG_9920.webp',
      '/immagini-optimized/IMG_9927.webp',
      '/immagini-optimized/IMG_9924.webp',
      '/immagini-optimized/IMG_9915.webp',
      '/immagini-optimized/IMG_9917.webp',
      '/immagini-optimized/IMG_9914.webp',
      '/immagini-optimized/IMG_9918.webp',
      '/immagini-optimized/IMG_9923.webp',
      '/immagini-optimized/IMG_9916.webp',
      '/immagini-optimized/IMG_9926.webp',
      '/immagini-optimized/IMG_9919.webp',
      '/immagini-optimized/IMG_9925.webp',
      '/immagini-optimized/IMG_9921.webp',
      '/immagini-optimized/IMG_9922.webp',
    ]

    const guestImages = [
      '/immagini-guest-cropped/guestmisterioso.webp',
      '/immagini-guest-cropped/twolate.webp',
      '/immagini-guest-cropped/peppe-citarella.webp',
      '/immagini-guest-cropped/Marco Lys at Il Muretto 3.webp',
      '/immagini-guest-cropped/Pieropirupa.webp',
      '/immagini-guest-cropped/grossomoddo-new.webp',
      '/immagini-guest-cropped/peaty.webp',
      '/immagini guest/piccaemars.jpg',
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

