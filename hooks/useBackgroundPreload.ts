import { useEffect } from 'react'

export function useBackgroundPreload() {
  useEffect(() => {
    // Preload second carousel images in background after initial load
    const secondCarouselImages = [
      '/IMG_9920.JPG',
      '/IMG_9927.JPG',
      '/IMG_9924.JPG',
      '/IMG_9915.JPG',
      '/IMG_9917.JPG',
      '/IMG_9914.JPG',
      '/IMG_9918.JPG',
      '/IMG_9923.JPG',
      '/IMG_9916.JPG',
      '/IMG_9926.JPG',
      '/IMG_9919.JPG',
      '/IMG_9925.JPG',
      '/IMG_9921.JPG',
      '/IMG_9922.JPG',
    ]

    const guestImages = [
      '/immagini-guest-cropped/guestmisterioso.webp',
      '/immagini-guest-cropped/twolate.webp',
      '/immagini-guest-cropped/peppe-citarella.webp',
      '/immagini-guest-cropped/Marco Lys at Il Muretto 3.webp',
      '/immagini-guest-cropped/Pieropirupa.webp',
      '/immagini-guest-cropped/grossomoddo-new.webp',
      '/immagini-guest-cropped/peaty.webp',
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

