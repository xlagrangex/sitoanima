"use client"

import { Button } from "@/components/ui/button"
import Image from "next/image"
import { useLanguage } from "@/contexts/LanguageContext"

export function HeroSection() {
  const { t } = useLanguage()
  
  const scrollToFormat = () => {
    document.getElementById("format")?.scrollIntoView({ behavior: "smooth" })
  }

  return (
    <section id="hero" className="relative flex items-center justify-center overflow-hidden w-screen" style={{ height: '80vh' }}>
      {/* Video Background */}
      <video
        autoPlay
        loop
        muted
        playsInline
        className="absolute top-0 left-0 w-full h-full object-cover"
        onError={(e) => {
          console.log('Video failed to load, using fallback');
          e.currentTarget.style.display = 'none';
        }}
      >
        <source src="/video2.mp4" type="video/mp4" />
      </video>
      
      {/* Fallback background image */}
      <div className="absolute top-0 left-0 w-full h-full object-cover bg-gradient-to-br from-purple-900 via-black to-red-900">
        <Image
          src="/electronic-music-event-poster.webp"
          alt="ANIMA Background"
          fill
          className="object-cover opacity-50"
          priority
        />
      </div>
      
      {/* Overlay semitrasparente */}
      <div className="absolute inset-0 bg-black/50"></div>

      {/* Content */}
      <div className="relative z-10 text-center text-white px-4 max-w-2xl">
        <div className="flex justify-center mb-6">
          <Image
            src="/anima-complete-white.webp"
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
