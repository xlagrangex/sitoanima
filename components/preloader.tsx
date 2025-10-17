"use client"

import React, { useState, useEffect } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import Image from 'next/image'

export function Preloader() {
  const [isLoading, setIsLoading] = useState(true)
  const [progress, setProgress] = useState(0)

  useEffect(() => {
    // Simulate loading progress
    const interval = setInterval(() => {
      setProgress((prev) => {
        if (prev >= 100) {
          clearInterval(interval)
          setTimeout(() => setIsLoading(false), 500)
          return 100
        }
        return prev + 2
      })
    }, 30)

    // Also listen for actual page load
    const handleLoad = () => {
      setProgress(100)
      setTimeout(() => setIsLoading(false), 500)
    }

    if (document.readyState === 'complete') {
      handleLoad()
    } else {
      window.addEventListener('load', handleLoad)
    }

    return () => {
      clearInterval(interval)
      window.removeEventListener('load', handleLoad)
    }
  }, [])

  return (
    <AnimatePresence>
      {isLoading && (
        <motion.div
          initial={{ opacity: 1 }}
          exit={{ opacity: 0 }}
          transition={{ duration: 0.5 }}
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

