"use client"

import { Button } from "@/components/ui/button"
import Image from "next/image"
import { useLanguage } from "@/contexts/LanguageContext"
import { useEffect, useRef } from "react"

export function HeroSection() {
  const { t } = useLanguage()
  const videoRef = useRef<HTMLVideoElement>(null)
  
  useEffect(() => {
    const video = videoRef.current
    if (!video) return

    // Funzione per forzare la riproduzione
    const attemptPlay = () => {
      const playPromise = video.play()
      if (playPromise !== undefined) {
        playPromise
          .then(() => {
            console.log("Video playing successfully")
          })
          .catch((error) => {
            console.log("Autoplay prevented:", error)
            // Prova di nuovo dopo un breve delay
            setTimeout(() => {
              video.play().catch(() => {
                console.log("Second autoplay attempt failed")
              })
            }, 100)
          })
      }
    }

    // Prova a far partire il video quando è pronto
    if (video.readyState >= 2) {
      // Video già caricato abbastanza
      attemptPlay()
    } else {
      // Aspetta che il video sia pronto
      video.addEventListener('loadeddata', attemptPlay, { once: true })
      video.addEventListener('canplay', attemptPlay, { once: true })
      video.addEventListener('canplaythrough', attemptPlay, { once: true })
    }

    return () => {
      video.removeEventListener('loadeddata', attemptPlay)
      video.removeEventListener('canplay', attemptPlay)
      video.removeEventListener('canplaythrough', attemptPlay)
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
    <section id="hero" className="relative flex items-center justify-center overflow-hidden w-screen bg-black" style={{ height: '80vh' }}>
      {/* Video Background */}
      <video
        ref={videoRef}
        autoPlay
        muted
        loop
        playsInline
        controls={false}
        preload="auto"
        className="absolute inset-0 w-full h-full object-cover pointer-events-none"
        style={{ zIndex: 0 }}
        onLoadedData={(e) => {
          // Forza la riproduzione quando i dati sono caricati
          e.currentTarget.play().catch(() => {})
        }}
        onCanPlay={(e) => {
          // Forza la riproduzione quando può essere riprodotto
          e.currentTarget.play().catch(() => {})
        }}
      >
        <source src="/video2-optimized.mp4" type="video/mp4" />
      </video>

      {/* Overlay semitrasparente */}
      <div className="absolute inset-0 bg-black/50 z-[1]"></div>

      {/* Content */}
      <div className="relative z-[2] text-center text-white px-4 max-w-2xl">
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

      <div className="absolute left-4 top-1/2 -translate-y-1/2 w-1 h-32 bg-white/30 hidden lg:block z-[2]" />
      <div className="absolute right-4 top-1/2 -translate-y-1/2 w-1 h-32 bg-white/30 hidden lg:block z-[2]" />
    </section>
  )
}
