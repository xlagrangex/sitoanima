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
    const startTime = Date.now()
    const MIN_DISPLAY_TIME = 1500 // 1.5 secondi minimi

    // Start progress animation
    progressInterval = setInterval(() => {
      setProgress((prev) => {
        if (prev >= 100) {
          if (progressInterval) clearInterval(progressInterval)
          return 100
        }
        // Progress più graduale per raggiungere 100% in circa 1.5 secondi
        return prev + 6.67 // ~100% in 1.5 secondi (100/15 = 6.67 per step)
      })
    }, 100) // Intervallo di 100ms

    // Load only critical assets for LCP optimization
    const criticalImages = [
      // Only the hero logo - this is the LCP element
      '/anima-complete-white-optimized.webp',
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

      // Wait for critical assets
      await Promise.all(imagePromises)
      allImagesLoaded = true
      setProgress(100)
      
      // Calcola quanto tempo è passato
      const elapsed = Date.now() - startTime
      const remainingTime = Math.max(0, MIN_DISPLAY_TIME - elapsed)
      
      // Aspetta almeno 1.5 secondi totali prima di nascondere
      setTimeout(() => {
        setIsLoading(false)
      }, remainingTime)
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
              src="/anima-logo-white-optimized.webp"
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

