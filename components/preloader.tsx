"use client"

import React, { useState, useEffect } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import Image from 'next/image'

export function Preloader() {
  const [isLoading, setIsLoading] = useState(true)
  const [progress, setProgress] = useState(0)

  useEffect(() => {
    let progressInterval: NodeJS.Timeout | null = null
    let allImagesLoaded = false

    // Start progress animation
    progressInterval = setInterval(() => {
      setProgress((prev) => {
        // Slow down at 90% to wait for images
        if (prev >= 90 && !allImagesLoaded) {
          return prev + 0.5
        }
        if (prev >= 100) {
          if (progressInterval) clearInterval(progressInterval)
          return 100
        }
        return prev + 2
      })
    }, 30)

    // Load all carousel images
    const carouselImages = [
      '/immagini/IMG_9662.JPG',
      '/immagini/IMG_9663.JPG',
      '/immagini/IMG_9664.JPG',
      '/immagini/IMG_9665.JPG',
      '/immagini/IMG_9667.JPG',
      '/immagini/IMG_9668.JPG',
      '/immagini/IMG_9669.JPG',
      '/immagini/IMG_9670.JPG',
      '/immagini/IMG_9671.JPG',
      '/immagini/IMG_9672.JPG',
      '/immagini/IMG_9673.JPG',
      '/immagini/IMG_9674.JPG',
      '/immagini/IMG_9675.JPG',
    ]

    const preloadImages = async () => {
      const imagePromises = carouselImages.map((src) => {
        return new Promise((resolve, reject) => {
          const img = document.createElement('img')
          img.onload = resolve
          img.onerror = resolve // Continue even if an image fails
          img.src = src
        })
      })

      await Promise.all(imagePromises)
      allImagesLoaded = true
      setProgress(100)
      setTimeout(() => setIsLoading(false), 300)
    }

    preloadImages()

    return () => {
      if (progressInterval) clearInterval(progressInterval)
    }
  }, [])

  return (
    <AnimatePresence>
      {isLoading && (
        <motion.div
          initial={{ opacity: 1 }}
          exit={{ opacity: 0 }}
          transition={{ duration: 0.3 }}
          className="fixed inset-0 z-[99999] bg-red-900 flex flex-col items-center justify-center"
        >
          {/* Rotating favicon */}
          <motion.div
            animate={{ rotate: 360 }}
            transition={{
              duration: 2,
              repeat: Infinity,
              ease: "linear"
            }}
            className="mb-12"
          >
            <Image
              src="/anima-logo-white.png"
              alt="ANIMA Logo"
              width={80}
              height={80}
              className="w-20 h-20"
            />
          </motion.div>

          {/* Loading bar container */}
          <div className="w-64 md:w-96 h-1 bg-white/20 rounded-full overflow-hidden">
            {/* Loading bar */}
            <motion.div
              initial={{ width: 0 }}
              animate={{ width: `${progress}%` }}
              transition={{ duration: 0.2 }}
              className="h-full bg-white rounded-full"
            />
          </div>

          {/* Optional percentage text */}
          {/* <p className="text-white text-sm font-light mt-4">{progress}%</p> */}
        </motion.div>
      )}
    </AnimatePresence>
  )
}

