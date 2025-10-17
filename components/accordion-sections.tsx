"use client"

import React from "react"
import Image from "next/image"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Textarea } from "@/components/ui/textarea"
import { useState, useEffect } from "react"
import { useLanguage } from "@/contexts/LanguageContext"
import { motion } from "framer-motion"
import { GlitchText, GlitchContainer, GlitchButton, staggerContainer, staggerItem } from "@/components/glitch-animations"
import { ThreeDPhotoCarousel } from "@/components/ui/3d-carousel"
import { CircularGallery, type GalleryItem } from "@/components/ui/circular-gallery"
import { InstagramGrid } from "@/components/instagram-grid"
import { AnimatedTestimonials } from "@/components/ui/animated-testimonials"
import { Mail, MessageCircle, MapPin, Send, Music, Instagram } from "lucide-react"

export function AccordionSections() {
  const { t } = useLanguage()
  const [formData, setFormData] = useState({
    name: "",
    email: "",
    message: "",
  })
  const [isMobile, setIsMobile] = useState(false)

  useEffect(() => {
    const checkMobile = () => {
      setIsMobile(window.innerWidth < 768)
    }
    checkMobile()
    window.addEventListener('resize', checkMobile)
    return () => window.removeEventListener('resize', checkMobile)
  }, [])

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    console.log("Form submitted:", formData)
  }

  const getButtonIcon = (href?: string) => {
    if (!href) return null
    if (href.includes('mailto:')) return <Mail className="w-4 h-4 mr-2" />
    if (href.includes('wa.me') || href.includes('whatsapp')) return <MessageCircle className="w-4 h-4 mr-2" />
    if (href.includes('maps.google') || href.includes('google.com/maps')) return <MapPin className="w-4 h-4 mr-2" />
    if (href.includes('t.me') || href.includes('telegram')) return <Send className="w-4 h-4 mr-2" />
    if (href.includes('spotify')) return <Music className="w-4 h-4 mr-2" />
    return null
  }

  const galleryItems: GalleryItem[] = [
    {
      common: "Moment",
      binomial: "01",
      photo: {
        url: "/immagini/IMG_9662.JPG",
        text: "ANIMA Event - Moment 01",
        by: "ANIMA Events"
      }
    },
    {
      common: "Moment",
      binomial: "02",
      photo: {
        url: "/immagini/IMG_9663.JPG",
        text: "ANIMA Event - Moment 02",
        by: "ANIMA Events"
      }
    },
    {
      common: "Moment",
      binomial: "03",
      photo: {
        url: "/immagini/IMG_9664.JPG",
        text: "ANIMA Event - Moment 03",
        by: "ANIMA Events"
      }
    },
    {
      common: "Moment",
      binomial: "04",
      photo: {
        url: "/immagini/IMG_9665.JPG",
        text: "ANIMA Event - Moment 04",
        by: "ANIMA Events"
      }
    },
    {
      common: "Moment",
      binomial: "05",
      photo: {
        url: "/immagini/IMG_9667.JPG",
        text: "ANIMA Event - Moment 05",
        by: "ANIMA Events"
      }
    },
    {
      common: "Moment",
      binomial: "06",
      photo: {
        url: "/immagini/IMG_9668.JPG",
        text: "ANIMA Event - Moment 06",
        by: "ANIMA Events"
      }
    },
    {
      common: "Moment",
      binomial: "07",
      photo: {
        url: "/immagini/IMG_9669.JPG",
        text: "ANIMA Event - Moment 07",
        by: "ANIMA Events"
      }
    },
    {
      common: "Moment",
      binomial: "08",
      photo: {
        url: "/immagini/IMG_9670.JPG",
        text: "ANIMA Event - Moment 08",
        by: "ANIMA Events"
      }
    },
    {
      common: "Moment",
      binomial: "09",
      photo: {
        url: "/immagini/IMG_9671.JPG",
        text: "ANIMA Event - Moment 09",
        by: "ANIMA Events"
      }
    },
    {
      common: "Moment",
      binomial: "10",
      photo: {
        url: "/immagini/IMG_9672.JPG",
        text: "ANIMA Event - Moment 10",
        by: "ANIMA Events"
      }
    },
    {
      common: "Moment",
      binomial: "11",
      photo: {
        url: "/immagini/IMG_9673.JPG",
        text: "ANIMA Event - Moment 11",
        by: "ANIMA Events"
      }
    },
    {
      common: "Moment",
      binomial: "12",
      photo: {
        url: "/immagini/IMG_9674.JPG",
        text: "ANIMA Event - Moment 12",
        by: "ANIMA Events"
      }
    },
    {
      common: "Moment",
      binomial: "13",
      photo: {
        url: "/immagini/IMG_9675.JPG",
        text: "ANIMA Event - Moment 13",
        by: "ANIMA Events"
      }
    }
  ]

  const guestDJs = [
    {
      quote: "Electronic music artist bringing fresh sounds and innovative beats to the underground scene.",
      name: "Twolate",
      designation: "Electronic Artist",
      src: "/IMG_5628.JPG"
    },
    {
      quote: "Techno producer and DJ known for his powerful sets and underground sound.",
      name: "Peppe Citarella",
      designation: "Techno DJ & Producer",
      src: "/c_EPbQeA.jpeg"
    },
    {
      quote: "A special surprise guest to be revealed soon. Stay tuned for an unforgettable performance.",
      name: "???",
      designation: "Announcing soon",
      src: "/pexels-khanshaheb-17214950.jpg"
    },
    {
      quote: "Electronic music artist with a unique style blending techno and house elements.",
      name: "Marco Lys",
      designation: "Electronic Artist",
      src: "/Marco Lys at Il Muretto 3.jpg"
    },
    {
      quote: "House music producer and DJ bringing energy and groove to every performance.",
      name: "Piero Pirupa",
      designation: "House DJ & Producer",
      src: "/Screenshot 2025-10-17 at 11.58.26.png"
    },
    {
      quote: "Electronic music artist known for innovative sounds and captivating performances.",
      name: "Grossomoddo",
      designation: "Electronic Artist",
      src: "/IMG_9690.JPG"
    },
    {
      quote: "Techno DJ and producer delivering deep, hypnotic beats to the underground scene.",
      name: "Peaty",
      designation: "Techno DJ & Producer",
      src: "/4.jpeg"
    }
  ]

  const sections = [
    {
      id: "format",
      subtitleKey: "section1.subtitle",
      titleKey: "section1.title",
      contentKey: "section1.content",
      cta: null,
    },
    {
      id: "venue",
      subtitleKey: "section2.subtitle",
      titleKey: "section2.title",
      contentKey: "section2.content",
      cta: {
        textKey: "section2.cta",
        action: () => document.getElementById("map")?.scrollIntoView({ behavior: "smooth" }),
      },
    },
    {
      id: "shows",
      subtitleKey: "section3.subtitle",
      titleKey: "section3.title",
      contentKey: "section3.content",
      cta: null,
    },
    {
      id: "guests",
      subtitleKey: "section4.subtitle",
      titleKey: "section4.title",
      contentKey: "section4.content",
      cta: null,
    },
    {
      id: "media",
      subtitleKey: "section6.subtitle",
      titleKey: "section6.title",
      contentKey: "section6.content",
      cta: {
        textKey: "section6.cta",
        href: "https://t.me/UNTILTHESUNRISES",
      },
    },
    {
      id: "playlist",
      subtitleKey: "section7.subtitle",
      titleKey: "section7.title",
      contentKey: "section7.content",
      cta: {
        textKey: "section7.cta",
        href: "https://open.spotify.com/playlist/5WpQ7y1j9EhRMQlMKPJqSx?si=aa8b855a6bdc432e",
      },
    },
    {
      id: "collaborations",
      subtitleKey: "section8.subtitle",
      titleKey: "section8.title",
      contentKey: "section8.content",
      cta: {
        textKey: "section8.cta",
        href: "mailto:info.animaent@gmail.com",
      },
    },
    {
      id: "liste",
      subtitleKey: "section9.subtitle",
      titleKey: "section9.title",
      contentKey: "section9.content",
      cta: {
        textKey: "section9.cta",
        href: "https://wa.me/393389040714",
      },
    },
    {
      id: "instagram",
      subtitleKey: "section11.subtitle",
      titleKey: "section11.title",
      contentKey: "section11.content",
      hasInstagram: true,
      cta: {
        textKey: "section11.cta",
        href: "https://www.instagram.com/anima.ent",
      },
    },
    {
      id: "map",
      subtitleKey: "section10.subtitle",
      titleKey: "section10.title",
      contentKey: "section10.content",
      cta: {
        textKey: "section10.cta",
        href: "https://www.google.com/maps/place/HBTOO/@40.8011031,14.1726738,17z/data=!3m1!4b1!4m6!3m5!1s0x133b0c1e75bca2d7:0xe08e8f4f3a917d24",
      },
    },
  ]

  const colorSchemes = [
    { bg: 'bg-black', text: 'text-white', accent: 'text-red-600' },
    { bg: 'bg-red-700', text: 'text-white', accent: 'text-black' },
    { bg: 'bg-white', text: 'text-black', accent: 'text-red-600' },
    { bg: 'bg-[#E78E00]', text: 'text-white', accent: 'text-white' },
  ]

  return (
    <div className="w-full overflow-x-hidden">
      {sections.map((section, index) => {
        const scheme = colorSchemes[index % colorSchemes.length]
        
        return (
        <div 
          key={section.id}
          id={section.id}
          className={`w-full min-h-screen flex items-center justify-center ${'hasCarousel' in section && section.hasCarousel ? 'py-1 md:py-2' : 'py-12 md:py-16'} px-4 text-center ${scheme.bg}`}
        >
          <div className="max-w-[90%] md:max-w-[60%] mx-auto w-full">
            {section.subtitleKey && (
              <p className={`text-xs md:text-sm uppercase tracking-wider mb-3 md:mb-4 ${scheme.text} opacity-70 font-avenir`}>
                {t(section.subtitleKey)}
              </p>
            )}
            <GlitchText 
              className={`title-primary ${'hasCarousel' in section && section.hasCarousel ? 'mb-1 md:mb-2' : 'mb-8 md:mb-12'} ${scheme.text} leading-[0.9] block`}
              style={{ fontSize: 'clamp(2rem, 4.5vw, 4.5rem)' }}
              delay={index * 0.1}
            >
              {t(section.titleKey)}
            </GlitchText>

            {section.id === "media" ? (
              <div className="w-full max-w-6xl mx-auto">
                <p className={`text-base md:text-lg lg:text-xl leading-relaxed ${scheme.text} mb-8 md:mb-12 text-center`}>
                  {t(section.contentKey)}
                </p>
                <div className="w-full h-[500px] md:h-[600px]">
                  <CircularGallery 
                    items={galleryItems} 
                    radius={isMobile ? 500 : 680} 
                    autoRotateSpeed={0.015} 
                  />
                </div>
                {section.cta && section.cta.href && (
                  <div className="mt-8 flex justify-center">
                    <Button
                      asChild
                      className={`btn-primary ${scheme.accent === 'text-red-600' ? 'bg-red-600 hover:bg-red-700' : scheme.accent === 'text-black' ? 'bg-black hover:bg-gray-900' : 'bg-white hover:bg-gray-100'} ${scheme.accent === 'text-white' ? 'text-black' : 'text-white'} px-3 md:px-8 py-3 md:py-4 text-sm md:text-base shadow-lg max-w-full`}
                    >
                      <a href={section.cta.href} target="_blank" rel="noopener noreferrer" className="flex items-center justify-center">
                        {getButtonIcon(section.cta.href)}
                        {t(section.cta.textKey)}
                      </a>
                    </Button>
                  </div>
                )}
              </div>
            ) : section.id === "shows" ? (
              <div className="max-w-4xl mx-auto">
                <p className={`text-base md:text-lg lg:text-xl leading-relaxed ${scheme.text} mb-6 md:mb-8 text-center`}>
                  {t(section.contentKey)}
                </p>
                <div className="w-full max-w-2xl mx-auto">
                  <Image
                    src="/anima-meets-dolcevita-flyer.png"
                    alt="ANIMA MEETS DOLCEVITA - Event Flyer"
                    width={800}
                    height={1131}
                    className="w-full h-auto rounded-lg shadow-2xl"
                    priority
                  />
                </div>
              </div>
            ) : section.id === "guests" ? (
              <div className="w-full max-w-6xl mx-auto">
                <p className={`text-base md:text-lg lg:text-xl leading-relaxed ${scheme.text} mb-8 md:mb-12 text-center`}>
                  {t(section.contentKey)}
                </p>
                <AnimatedTestimonials testimonials={guestDJs} autoplay={true} />
              </div>
            ) : section.id === "playlist" ? (
              <div className="max-w-4xl mx-auto">
                <p className={`text-base md:text-lg lg:text-xl leading-relaxed ${scheme.text} mb-6 md:mb-8 text-center`}>
                  {t(section.contentKey)}
                </p>
                <iframe 
                  data-testid="embed-iframe" 
                  style={{borderRadius: "12px"}} 
                  src="https://open.spotify.com/embed/playlist/5WpQ7y1j9EhRMQlMKPJqSx?utm_source=generator" 
                  width="100%" 
                  height="352" 
                  frameBorder="0" 
                  allowFullScreen={true}
                  allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" 
                  loading="lazy"
                ></iframe>
              </div>
            ) : section.id === "instagram" ? (
              <div className="w-full max-w-6xl mx-auto">
                <p className={`text-base md:text-lg lg:text-xl leading-relaxed ${scheme.text} mb-8 md:mb-12 text-center`}>
                  {t(section.contentKey)}
                </p>
                <InstagramGrid />
                {section.cta && section.cta.href && (
                  <div className="mt-8 flex justify-center">
                    <Button
                      asChild
                      className={`btn-primary ${scheme.accent === 'text-red-600' ? 'bg-red-600 hover:bg-red-700' : scheme.accent === 'text-black' ? 'bg-black hover:bg-gray-900' : 'bg-white hover:bg-gray-100'} ${scheme.accent === 'text-white' ? 'text-black' : 'text-white'} px-3 md:px-8 py-3 md:py-4 text-sm md:text-base shadow-lg max-w-full`}
                    >
                      <a href={section.cta.href} target="_blank" rel="noopener noreferrer" className="flex items-center justify-center">
                        <Instagram className="w-4 h-4 mr-2" />
                        {t(section.cta.textKey)}
                      </a>
                    </Button>
                  </div>
                )}
              </div>
            ) : section.id === "map" ? (
              <div className="w-full max-w-5xl mx-auto">
                <p className={`text-base md:text-lg lg:text-xl leading-relaxed ${scheme.text} mb-6 md:mb-8 text-center`}>
                  {t(section.contentKey)}
                </p>
                <div className="w-full aspect-[4/3] md:aspect-[16/9] rounded-lg overflow-hidden shadow-2xl">
                  <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3020.2225497639356!2d14.172673776545675!3d40.80110307138039!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133b0c1e75bca2d7%3A0xe08e8f4f3a917d24!2sHBTOO!5e0!3m2!1sit!2sit!4v1760536364942!5m2!1sit!2sit" 
                    width="100%" 
                    height="100%" 
                    style={{border:0}} 
                    allowFullScreen={true}
                    loading="lazy" 
                    referrerPolicy="no-referrer-when-downgrade"
                  ></iframe>
                </div>
                {section.cta && section.cta.href && (
                  <div className="mt-6 flex justify-center">
                    <Button
                      asChild
                      className={`btn-primary ${scheme.accent === 'text-red-600' ? 'bg-red-600 hover:bg-red-700' : scheme.accent === 'text-black' ? 'bg-black hover:bg-gray-900' : 'bg-white hover:bg-gray-100'} ${scheme.accent === 'text-white' ? 'text-black' : 'text-white'} px-3 md:px-8 py-3 md:py-4 text-sm md:text-base shadow-lg max-w-full`}
                    >
                      <a href={section.cta.href} target="_blank" rel="noopener noreferrer" className="flex items-center justify-center">
                        {getButtonIcon(section.cta.href)}
                        {t(section.cta.textKey)}
                      </a>
                    </Button>
                  </div>
                )}
              </div>
            ) : 'hasCarousel' in section && section.hasCarousel ? (
              <div className="max-w-7xl mx-auto">
                <p className={`text-base md:text-lg lg:text-xl leading-relaxed ${scheme.text} mb-1 md:mb-2 text-center`}>
                  {t(section.contentKey)}
                </p>
                <div className="-my-24 md:-my-32">
                  <ThreeDPhotoCarousel />
                </div>
              </div>
            ) : (
              <>
                <div className="max-w-4xl mx-auto space-y-4">
                  {t(section.contentKey).split('\n\n').map((paragraph: string, i: number) => (
                    <p key={i} className={`text-base md:text-lg lg:text-xl leading-relaxed ${scheme.text} text-center`}>
                      {paragraph}
                    </p>
                  ))}
          </div>

                {section.cta && (
                  <div className="mt-6 flex justify-center">
                    {section.cta.href ? (
                      <Button
                        asChild
                        className={`btn-primary ${scheme.accent === 'text-red-600' ? 'bg-red-600 hover:bg-red-700' : scheme.accent === 'text-black' ? 'bg-black hover:bg-gray-900' : 'bg-white hover:bg-gray-100'} ${scheme.accent === 'text-white' ? 'text-black' : 'text-white'} px-3 md:px-8 py-3 md:py-4 text-sm md:text-base shadow-lg max-w-full`}
                      >
                        <a href={section.cta.href} target="_blank" rel="noopener noreferrer" className="flex items-center justify-center">
                          {getButtonIcon(section.cta.href)}
                          {t(section.cta.textKey)}
                        </a>
                      </Button>
                    ) : (
                      <Button
                        onClick={section.cta.action}
                        className={`btn-primary ${scheme.accent === 'text-red-600' ? 'bg-red-600 hover:bg-red-700' : scheme.accent === 'text-black' ? 'bg-black hover:bg-gray-900' : 'bg-white hover:bg-gray-100'} ${scheme.accent === 'text-white' ? 'text-black' : 'text-white'} px-3 md:px-8 py-3 md:py-4 text-sm md:text-base shadow-lg flex items-center justify-center max-w-full`}
                      >
                        <MapPin className="w-4 h-4 mr-2" />
                        {t(section.cta.textKey)}
                  </Button>
                    )}
              </div>
                )}
            </>
          )}
          </div>
        </div>
        )
      })}
    </div>
  )
}
