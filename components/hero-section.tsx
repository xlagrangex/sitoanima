"use client"

import { Button } from "@/components/ui/button"
import { useState, useRef } from "react"
import { motion } from "framer-motion"
import Image from "next/image"

export function HeroSection() {
  const [isPlaying, setIsPlaying] = useState(true)
  const videoRef = useRef<HTMLVideoElement>(null)

  const toggleVideo = () => {
    if (videoRef.current) {
      if (isPlaying) {
        videoRef.current.pause()
      } else {
        videoRef.current.play()
      }
      setIsPlaying(!isPlaying)
    }
  }

  const scrollToFormat = () => {
    document.getElementById("format")?.scrollIntoView({ behavior: "smooth" })
  }

  return (
    <section id="hero" className="relative h-screen flex items-center justify-center overflow-hidden bg-primary w-full">
      {/* Content */}
      <motion.div
        className="relative z-10 text-center text-white px-4 max-w-4xl"
        initial={{ opacity: 0, y: 50 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 1, ease: "easeOut" }}
      >
        <motion.div
          className="flex justify-center mb-8"
          initial={{ opacity: 0, scale: 0.8 }}
          animate={{ opacity: 1, scale: 1 }}
          transition={{ duration: 1.2, delay: 0.3, ease: "easeOut" }}
        >
          <Image
            src="/anima-complete-white.png"
            alt="ANIMA - Until the Sun Rises"
            width={800}
            height={300}
            className="w-full max-w-4xl h-auto drop-shadow-2xl"
            priority
          />
        </motion.div>

        <motion.div
          initial={{ opacity: 0, y: 30 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, delay: 0.9, ease: "easeOut" }}
        >
          <Button
            onClick={scrollToFormat}
            size="lg"
            className="btn-primary bg-black hover:bg-black/90 text-white px-8 py-4 text-lg shadow-lg"
          >
            Scopri il format
          </Button>
        </motion.div>
      </motion.div>

      <motion.div
        className="absolute left-4 top-1/2 -translate-y-1/2 w-1 h-32 bg-white/30 hidden lg:block"
        initial={{ opacity: 0, x: -20 }}
        animate={{ opacity: 1, x: 0 }}
        transition={{ duration: 0.8, delay: 1.5, ease: "easeOut" }}
      />
      <motion.div
        className="absolute right-4 top-1/2 -translate-y-1/2 w-1 h-32 bg-white/30 hidden lg:block"
        initial={{ opacity: 0, x: 20 }}
        animate={{ opacity: 1, x: 0 }}
        transition={{ duration: 0.8, delay: 1.5, ease: "easeOut" }}
      />
    </section>
  )
}
