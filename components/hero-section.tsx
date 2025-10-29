"use client"

import { Button } from "@/components/ui/button"
import Image from "next/image"
import { useLanguage } from "@/contexts/LanguageContext"
import { useEffect, useRef } from "react"

export function HeroSection() {
  const { t } = useLanguage()
  const videoRef = useRef<HTMLVideoElement>(null)
  
  // Force video to play as soon as it's ready - aggressive for mobile
  useEffect(() => {
    const video = videoRef.current
    if (!video) return

    // Ensure video is muted and has playsInline for mobile
    video.muted = true
    video.setAttribute('playsinline', 'true')
    video.setAttribute('webkit-playsinline', 'true')
    video.setAttribute('x5-playsinline', 'true') // For Android WeChat
    video.removeAttribute('controls') // Explicitly remove controls
    
    const attemptPlay = () => {
      video.play().catch(() => {
        // Silently fail - browser policy may prevent autoplay
      })
    }

    // Multiple event listeners to catch video ready at different stages
    const handleCanPlay = attemptPlay
    const handleLoadedMetadata = attemptPlay
    const handleLoadedData = attemptPlay
    const handleTimeUpdate = () => {
      // If video is playing, remove this listener
      if (!video.paused) {
        video.removeEventListener('timeupdate', handleTimeUpdate)
      }
    }

    video.addEventListener('canplay', handleCanPlay, { once: true })
    video.addEventListener('canplaythrough', attemptPlay, { once: true })
    video.addEventListener('loadedmetadata', handleLoadedMetadata, { once: true })
    video.addEventListener('loadeddata', handleLoadedData, { once: true })
    video.addEventListener('timeupdate', handleTimeUpdate)

    // Try to play immediately if video is already loaded
    if (video.readyState >= 2) {
      attemptPlay()
    }

    // Force play after a short delay (helps with mobile browsers)
    const timeoutId = setTimeout(() => {
      if (video.paused) {
        attemptPlay()
      }
    }, 100)

    return () => {
      clearTimeout(timeoutId)
      video.removeEventListener('canplay', handleCanPlay)
      video.removeEventListener('canplaythrough', attemptPlay)
      video.removeEventListener('loadedmetadata', handleLoadedMetadata)
      video.removeEventListener('loadeddata', handleLoadedData)
      video.removeEventListener('timeupdate', handleTimeUpdate)
    }
  }, [])
  
  const scrollToFormat = () => {
    const element = document.getElementById("format")
    if (!element) return
    
    const isMobile = window.innerWidth < 768
    
    if (isMobile) {
      // On mobile, calculate offset to account for browser address bar
      const headerHeight = 80 // Approximate height of browser bar + offset
      const elementPosition = element.getBoundingClientRect().top + window.pageYOffset
      const offsetPosition = elementPosition - headerHeight
      
      window.scrollTo({
        top: offsetPosition,
        behavior: "auto"
      })
    } else {
      element.scrollIntoView({ behavior: "smooth" })
    }
  }

  return (
    <section id="hero" className="relative flex items-center justify-center overflow-hidden w-screen" style={{ height: '80vh' }}>
      {/* Video Background - Loads after FCP */}
      <video
        ref={videoRef}
        autoPlay
        loop
        muted
        playsInline
        preload="auto"
        controls={false}
        disablePictureInPicture
        className="absolute top-0 left-0 w-full h-full object-cover"
        style={{ pointerEvents: 'none' }}
      >
        <source src="/video2.mp4" type="video/mp4" />
      </video>
      
      {/* Overlay semitrasparente */}
      <div className="absolute inset-0 bg-black/50"></div>

      {/* Content */}
      <div className="relative z-10 text-center text-white px-4 max-w-2xl">
        <div className="flex justify-center mb-6">
          <Image
            src="/anima-complete-white-optimized.webp"
            alt="ANIMA - Until the Sunrise"
            width={400}
            height={150}
            className="w-full max-w-md h-auto drop-shadow-2xl"
            priority
          />
        </div>

        <p className="text-base md:text-lg mb-6 leading-relaxed max-w-xl mx-auto">
          {t('hero.tagline')}<br />
          {t('hero.tagline2')}
        </p>
      </div>

      <div className="absolute left-4 top-1/2 -translate-y-1/2 w-1 h-32 bg-white/30 hidden lg:block z-10" />
      <div className="absolute right-4 top-1/2 -translate-y-1/2 w-1 h-32 bg-white/30 hidden lg:block z-10" />
    </section>
  )
}
