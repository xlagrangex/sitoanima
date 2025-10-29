"use client"

import React, { useState, useEffect } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import { X, ChevronLeft, ChevronRight } from 'lucide-react'
import type { GalleryItem } from './circular-gallery'

interface LightboxProps {
  images: GalleryItem[]
  initialIndex: number
  isOpen: boolean
  onClose: () => void
  hideText?: boolean
  aspectRatio?: string
}

export function Lightbox({ images, initialIndex, isOpen, onClose, hideText = false, aspectRatio }: LightboxProps) {
  const [currentIndex, setCurrentIndex] = useState(initialIndex)

  useEffect(() => {
    setCurrentIndex(initialIndex)
  }, [initialIndex])

  useEffect(() => {
    if (isOpen) {
      document.body.style.overflow = 'hidden'
    } else {
      document.body.style.overflow = 'unset'
    }
    return () => {
      document.body.style.overflow = 'unset'
    }
  }, [isOpen])

  const handlePrevious = () => {
    setCurrentIndex((prev) => (prev === 0 ? images.length - 1 : prev - 1))
  }

  const handleNext = () => {
    setCurrentIndex((prev) => (prev === images.length - 1 ? 0 : prev + 1))
  }

  const handleKeyDown = (e: KeyboardEvent) => {
    if (!isOpen) return
    if (e.key === 'Escape') onClose()
    if (e.key === 'ArrowLeft') handlePrevious()
    if (e.key === 'ArrowRight') handleNext()
  }

  useEffect(() => {
    window.addEventListener('keydown', handleKeyDown)
    return () => window.removeEventListener('keydown', handleKeyDown)
  }, [isOpen])

  if (!isOpen) return null

  return (
    <AnimatePresence>
      <motion.div
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        exit={{ opacity: 0 }}
        className="fixed inset-0 z-[9999] bg-black/95 backdrop-blur-sm flex items-center justify-center"
        onClick={onClose}
      >
        {/* Close button */}
        <button
          onClick={onClose}
          className="absolute top-4 right-4 z-[10000] text-white hover:text-gray-300 transition-colors p-2 rounded-full bg-white/10 hover:bg-white/20"
          aria-label="Close lightbox"
        >
          <X className="w-8 h-8" />
        </button>

        {/* Previous button */}
        <button
          onClick={(e) => {
            e.stopPropagation()
            handlePrevious()
          }}
          className="absolute left-4 top-1/2 -translate-y-1/2 z-[10000] text-white hover:text-gray-300 transition-colors p-3 rounded-full bg-white/10 hover:bg-white/20"
          aria-label="Previous image"
        >
          <ChevronLeft className="w-10 h-10" />
        </button>

        {/* Next button */}
        <button
          onClick={(e) => {
            e.stopPropagation()
            handleNext()
          }}
          className="absolute right-4 top-1/2 -translate-y-1/2 z-[10000] text-white hover:text-gray-300 transition-colors p-3 rounded-full bg-white/10 hover:bg-white/20"
          aria-label="Next image"
        >
          <ChevronRight className="w-10 h-10" />
        </button>

        {/* Image container */}
        <div
          className="relative w-full h-full flex items-center justify-center"
          onClick={(e) => e.stopPropagation()}
        >
          <AnimatePresence mode="wait">
            <motion.div
              key={currentIndex}
              initial={{ opacity: 0, scale: 0.9 }}
              animate={{ opacity: 1, scale: 1 }}
              exit={{ opacity: 0, scale: 0.9 }}
              transition={{ duration: 0.3 }}
              className="relative bg-black"
              style={{ 
                aspectRatio: '4/3',
                width: 'min(calc(100vh * 4 / 3), 100vw)',
                height: 'min(100vh, calc(100vw * 3 / 4))',
                margin: '0 auto'
              }}
            >
              <img
                src={images[currentIndex].photo.url}
                alt={images[currentIndex].photo.text}
                className="absolute inset-0 w-full h-full object-cover shadow-2xl"
                style={{ objectPosition: 'center' }}
              />
            </motion.div>
          </AnimatePresence>
        </div>

        {/* Image info and counter */}
        <div className="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-3">
          {/* Artist name and role - hide if hideText is true */}
          {!hideText && (
            <div className="text-center text-white">
              <h3 className="text-2xl md:text-3xl font-sequel font-black tracking-wider uppercase mb-1">
                {images[currentIndex].common}
              </h3>
              <p className="text-sm md:text-base italic opacity-80">
                {images[currentIndex].binomial}
              </p>
            </div>
          )}
          {/* Counter */}
          <div className="text-white text-lg font-medium bg-white/10 px-6 py-2 rounded-full backdrop-blur-sm">
            {currentIndex + 1} / {images.length}
          </div>
        </div>
      </motion.div>
    </AnimatePresence>
  )
}

