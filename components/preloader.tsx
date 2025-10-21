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

    // Start progress animation - faster and more responsive
    progressInterval = setInterval(() => {
      setProgress((prev) => {
        // Slow down at 85% to wait for critical images only
        if (prev >= 85 && !allImagesLoaded) {
          return prev + 1
        }
        if (prev >= 100) {
          if (progressInterval) clearInterval(progressInterval)
          return 100
        }
        return prev + 4 // Faster progress
      })
    }, 20) // Faster interval

    // Load only critical assets for first scroll (hero + first carousel)
    const criticalImages = [
      // Hero section
      '/anima-complete-white.webp',
      '/anima-logo-white.webp',
      // First carousel (3D carousel)
      '/charlotte-de-witte-dj-poster-dark-techno.webp',
      '/amelie-lens-dj-poster-techno-event.webp',
      '/ben-klock-dj-poster-underground-techno.webp',
      '/dj-performing-at-electronic-music-event-with-red-l.webp',
      '/electronic-music-event-crowd-with-purple-lights.webp',
      '/dj-mixing-on-cdj-turntables-with-neon-lights.webp',
      '/crowd-dancing-at-underground-techno-party.webp',
      '/electronic-music-crowd-dancing-purple-lights.webp',
      '/professional-dj-booth-with-cdj-and-mixer-purple-li.webp',
      '/dj-performing-electronic-music-purple-lighting.webp',
      '/techno-party-crowd-with-hands-up-dancing.webp',
      '/electronic-music-stage-with-led-visuals.webp',
      '/industrial-venue-interior-with-stage-and-purple-li.webp',
      '/concert-stage-with-professional-lighting-and-sound.webp',
    ]

    const preloadAssets = async () => {
      // Preload only critical images
      const imagePromises = criticalImages.map((src) => {
        return new Promise((resolve) => {
          const img = document.createElement('img')
          img.onload = resolve
          img.onerror = resolve // Continue even if an image fails
          img.src = src
        })
      })

      // Wait for critical assets only
      await Promise.all(imagePromises)
      allImagesLoaded = true
      setProgress(100)
      setTimeout(() => setIsLoading(false), 200) // Faster exit
    }

    preloadAssets()

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
          className="fixed inset-0 z-[99999] bg-red-800 flex flex-col items-center justify-center"
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
              src="/anima-logo-white.webp"
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

