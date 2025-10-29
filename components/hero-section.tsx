"use client"

import { Button } from "@/components/ui/button"
import Image from "next/image"
import { useLanguage } from "@/contexts/LanguageContext"
import { useEffect, useRef } from "react"

export function HeroSection() {
  const { t } = useLanguage()
  const videoRef = useRef<HTMLVideoElement>(null)
  
  useEffect(() => {
    // Delay video loading to improve FCP
    const timer = setTimeout(() => {
      if (videoRef.current) {
        videoRef.current.load()
      }
    }, 1000) // Start loading video after 1 second
    
    return () => clearTimeout(timer)
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
        preload="none"
        className="absolute top-0 left-0 w-full h-full object-cover"
      >
        <source src="/video2-optimized.mp4" type="video/mp4" />
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
