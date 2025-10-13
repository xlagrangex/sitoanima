"use client"

import type React from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Textarea } from "@/components/ui/textarea"
import { useState } from "react"
import { useLanguage } from "@/contexts/LanguageContext"
import { motion } from "framer-motion"

export function AccordionSections() {
  const { t } = useLanguage()
  const [formData, setFormData] = useState({
    name: "",
    email: "",
    message: "",
  })

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    console.log("Form submitted:", formData)
  }

  const sections = [
    {
      id: "format",
      titleKey: "section1.title",
      contentKey: "section1.content",
      cta: null,
    },
    {
      id: "venue",
      titleKey: "section2.title",
      contentKey: "section2.content",
      cta: {
        textKey: "section2.cta",
        action: () => document.getElementById("map")?.scrollIntoView({ behavior: "smooth" }),
      },
    },
    {
      id: "shows",
      titleKey: "section3.title",
      contentKey: "section3.content",
      cta: null,
    },
    {
      id: "guests",
      titleKey: "section4.title",
      contentKey: "section4.content",
      cta: null,
    },
    {
      id: "stage",
      titleKey: "section5.title",
      contentKey: "section5.content",
      cta: null,
    },
    {
      id: "media",
      titleKey: "section6.title",
      contentKey: "section6.content",
      cta: {
        textKey: "section6.cta",
        href: "https://t.me/UNTILTHESUNRISES",
      },
    },
    {
      id: "playlist",
      titleKey: "section7.title",
      contentKey: "section7.content",
      cta: {
        textKey: "section7.cta",
        href: "https://open.spotify.com/playlist/5WpQ7y1j9EhRMQlMKPJqSx?si=aa8b855a6bdc432e",
      },
    },
    {
      id: "collaborations",
      titleKey: "section8.title",
      contentKey: "section8.content",
      cta: {
        textKey: "section8.cta",
        href: "mailto:info.animaent@gmail.com",
      },
    },
    {
      id: "liste",
      titleKey: "section9.title",
      contentKey: "section9.content",
      cta: {
        textKey: "section9.cta",
        href: "https://wa.me/393389040714",
      },
    },
    {
      id: "map",
      titleKey: "section10.title",
      contentKey: "section10.content",
      cta: {
        textKey: "section10.cta",
        href: "https://maps.google.com",
      },
    },
  ]

  const backgrounds = [
    'bg-white',
    'bg-orange-50/30',
    'bg-gray-50',
    'bg-red-50/20',
    'bg-amber-50/40',
    'bg-stone-50',
  ]

  return (
    <div className="w-full overflow-x-hidden">
      {sections.map((section, index) => (
        <div 
          key={section.id} 
          className={`w-full py-12 md:py-16 px-4 text-center ${backgrounds[index % backgrounds.length]}`}
        >
          <div className="max-w-[90%] md:max-w-[60%] mx-auto">
            <h2 className="title-primary mb-8 md:mb-12 text-black leading-[0.9]" style={{ fontSize: 'clamp(2rem, 4.5vw, 4.5rem)' }}>
              {t(section.titleKey)}
          </h2>

            {section.id === "playlist" ? (
              <div className="max-w-4xl mx-auto">
                <p className="text-base md:text-lg lg:text-xl leading-relaxed text-gray-700 mb-6 md:mb-8 text-left md:text-center">
                  {t(section.contentKey)}
                </p>
                
                {/* Spotify Carousel Container */}
                <div className="relative bg-gradient-to-br from-green-600 via-green-500 to-emerald-600 rounded-2xl p-6 md:p-8 overflow-hidden">
                  {/* Decorative Elements */}
                  <div className="absolute top-4 right-4 w-20 h-20 bg-white/10 rounded-full blur-xl"></div>
                  <div className="absolute bottom-4 left-4 w-16 h-16 bg-white/5 rounded-full blur-lg"></div>
                  
                  {/* Header */}
                  <div className="text-center mb-6">
                    <div className="flex items-center justify-center space-x-3 mb-2">
                      <svg className="w-8 h-8 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.42 1.56-.299.421-1.02.599-1.559.3z"/>
                      </svg>
                      <h3 className="text-2xl md:text-3xl font-bold text-white">ANIMA Official Playlist</h3>
                    </div>
                    <p className="text-green-100 text-sm">Swipe per navigare • Tap per riprodurre</p>
                  </div>

                  {/* Spotify Player with Carousel Styling */}
                  <div className="relative">
                    {/* Glow Effect */}
                    <div className="absolute -inset-1 bg-gradient-to-r from-white/20 to-transparent rounded-xl blur-sm"></div>
                    
                    {/* Main Player Container */}
                    <div className="relative bg-black/20 backdrop-blur-sm rounded-xl p-4 border border-white/10">
                      <iframe 
                        data-testid="embed-iframe" 
                        style={{
                          borderRadius: "12px",
                          boxShadow: "0 25px 50px -12px rgba(0, 0, 0, 0.25)",
                          transform: "perspective(1000px) rotateX(2deg)",
                          transition: "all 0.3s ease"
                        }} 
                        src="https://open.spotify.com/embed/playlist/5WpQ7y1j9EhRMQlMKPJqSx?utm_source=generator&theme=0" 
                        width="100%" 
                        height="352" 
                        frameBorder="0" 
                        allowFullScreen={true}
                        allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" 
                        loading="lazy"
                        className="hover:transform hover:scale-[1.02] hover:rotate-0 transition-all duration-300"
                      ></iframe>
                    </div>

                    {/* Navigation Dots */}
                    <div className="flex justify-center space-x-2 mt-4">
                      <div className="w-2 h-2 bg-white/60 rounded-full animate-pulse"></div>
                      <div className="w-2 h-2 bg-white/40 rounded-full"></div>
                      <div className="w-2 h-2 bg-white/40 rounded-full"></div>
                      <div className="w-2 h-2 bg-white/40 rounded-full"></div>
                      <div className="w-2 h-2 bg-white/40 rounded-full"></div>
                    </div>
                  </div>

                  {/* Action Buttons */}
                  <div className="flex justify-center space-x-4 mt-6">
                    <motion.button
                      whileHover={{ scale: 1.05 }}
                      whileTap={{ scale: 0.95 }}
                      className="bg-white/20 backdrop-blur-sm text-white px-6 py-3 rounded-full border border-white/20 hover:bg-white/30 transition-all"
                    >
                      <span className="text-sm font-medium">🎧 Ascolta</span>
                    </motion.button>
                    <motion.button
                      whileHover={{ scale: 1.05 }}
                      whileTap={{ scale: 0.95 }}
                      className="bg-white text-green-600 px-6 py-3 rounded-full font-semibold hover:bg-green-50 transition-all"
                    >
                      <span className="text-sm">▶️ Play</span>
                    </motion.button>
                  </div>
                </div>
              </div>
            ) : (
              <>
                <div className="max-w-4xl mx-auto">
                <p className="text-base md:text-lg lg:text-xl leading-relaxed text-gray-700 mb-6 md:mb-8 text-left md:text-center">
                    {t(section.contentKey)}
            </p>
          </div>

                {section.cta && (
                  <div className="mt-6">
                    {section.cta.href ? (
                      <Button
                        asChild
                        className="btn-primary bg-primary hover:bg-primary/90 text-white px-8 py-4 text-base shadow-lg"
                      >
                        <a href={section.cta.href} target="_blank" rel="noopener noreferrer">
                          {t(section.cta.textKey)}
                        </a>
                      </Button>
                    ) : (
                      <Button
                        onClick={section.cta.action}
                        className="btn-primary bg-primary hover:bg-primary/90 text-white px-8 py-4 text-base shadow-lg"
                      >
                        {t(section.cta.textKey)}
                  </Button>
                    )}
                  </div>
                )}
              </>
            )}
          </div>
              </div>
      ))}
    </div>
  )
}
