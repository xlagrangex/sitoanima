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
      >
        <source src="/video2.mp4" type="video/mp4" />
      </video>
      
      {/* Overlay semitrasparente */}
      <div className="absolute inset-0 bg-black/50"></div>

      {/* Content */}
      <div className="relative z-10 text-center text-white px-4 max-w-2xl">
        <div className="flex justify-center mb-6">
          <Image
            src="/anima-complete-white.png"
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

        <div>
          <Button
            onClick={scrollToFormat}
            size="lg"
            className="btn-primary bg-red-900 hover:bg-red-800 text-white px-8 py-4 text-lg shadow-lg"
          >
            {t('hero.cta')}
          </Button>
        </div>
      </div>

      <div className="absolute left-4 top-1/2 -translate-y-1/2 w-1 h-32 bg-white/30 hidden lg:block z-10" />
      <div className="absolute right-4 top-1/2 -translate-y-1/2 w-1 h-32 bg-white/30 hidden lg:block z-10" />
    </section>
  )
}
