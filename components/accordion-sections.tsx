"use client"

import type React from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Textarea } from "@/components/ui/textarea"
import { useState, useEffect } from "react"
import { motion } from "framer-motion"

const AnimatedText = ({ text, delay = 0, className = "" }: { text: string; delay?: number; className?: string }) => {
  const letters = text.split("")

  return (
    <span className={className}>
      {letters.map((letter, index) => (
        <motion.span
          key={index}
          className={`inline-block ${className}`}
          initial={{ opacity: 0, y: 10 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{
            duration: 0.05,
            delay: delay + index * 0.02,
            ease: "easeOut",
          }}
        >
          {letter === " " ? "\u00A0" : letter}
        </motion.span>
      ))}
    </span>
  )
}

const UpcomingShowsCarousel = () => {
  const [currentIndex, setCurrentIndex] = useState(0)

  const shows = [
    {
      id: 1,
      artist: "ANYMA",
      event: "QUANTUM DUBAI",
      date: "FRIDAY 31ST OCTOBER 2025",
      location: "USHUAIA DUBAI",
      image: "/anyma-quantum-event-poster.jpg",
      ticketLink: "#",
    },
    {
      id: 2,
      artist: "ADRIATIQUE",
      event: "PRESENTS DUBAI",
      date: "SATURDAY NOVEMBER 15TH 2025",
      location: "DUBAI, UNITED ARAB EMIRATES",
      image: "/adriatique-dubai-event-poster.jpg",
      ticketLink: "#",
    },
    {
      id: 3,
      artist: "CALVIN HARRIS",
      event: "DUBAI",
      date: "SAT 29 NOV",
      location: "USHUAIA DUBAI",
      image: "/calvin-harris-dubai-event-poster.jpg",
      ticketLink: "#",
    },
    {
      id: 4,
      artist: "CHARLOTTE DE WITTE",
      event: "KNTXT DUBAI",
      date: "FRIDAY 15 FEBRUARY 2025",
      location: "USHUAIA DUBAI",
      image: "/charlotte-de-witte-event-poster.jpg",
      ticketLink: "#",
    },
    {
      id: 5,
      artist: "AMELIE LENS",
      event: "LENSKE DUBAI",
      date: "SATURDAY 22 MARCH 2025",
      location: "USHUAIA DUBAI",
      image: "/amelie-lens-event-poster.jpg",
      ticketLink: "#",
    },
  ]

  useEffect(() => {
    const interval = setInterval(() => {
      setCurrentIndex((prevIndex) => (prevIndex + 1) % shows.length)
    }, 4000) // Change every 4 seconds

    return () => clearInterval(interval)
  }, [shows.length])

  return (
    <div className="w-full max-w-7xl mx-auto mt-12">
      <div className="relative py-12">
        <div
          className="flex transition-transform duration-700 ease-in-out"
          style={{ transform: `translateX(-${currentIndex * (100 / 3)}%)` }}
        >
          {shows.concat(shows).map((show, index) => (
            <div key={`${show.id}-${index}`} className="flex-shrink-0 w-1/3 px-10">
              <div className="relative group cursor-pointer">
                <div className="aspect-square bg-gradient-to-br from-orange-400 via-purple-500 to-pink-400 rounded-3xl overflow-hidden shadow-xl transform scale-[1.3]">
                  <img
                    src={
                      show.image || `/placeholder.svg?height=400&width=400&query=${show.artist} ${show.event} poster`
                    }
                    alt={`${show.artist} - ${show.event}`}
                    className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent" />

                  {/* Event Info Overlay */}
                  <div className="absolute inset-0 p-8 flex flex-col justify-between text-white">
                    <div className="text-right">
                      <p className="text-sm font-medium opacity-90">{show.location}</p>
                    </div>

                    <div className="text-center">
                      <h3 className="text-3xl md:text-4xl font-black mb-2 tracking-wider">{show.artist}</h3>
                      <p className="text-base font-bold mb-3">{show.event}</p>
                      <p className="text-sm font-medium opacity-90">{show.date}</p>
                    </div>

                    <div className="text-center">
                      <Button
                        className="bg-white/20 hover:bg-white/30 text-white border border-white/30 backdrop-blur-sm font-bold text-sm px-6 py-3 rounded-full transition-all duration-300"
                        onClick={() => window.open(show.ticketLink, "_blank")}
                      >
                        ACQUISTA TICKET
                      </Button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Dots indicator */}
      <div className="flex justify-center mt-10 space-x-3">
        {shows.map((_, index) => (
          <button
            key={index}
            onClick={() => setCurrentIndex(index)}
            className={`w-3 h-3 rounded-full transition-colors duration-300 ${
              index === currentIndex ? "bg-primary" : "bg-gray-300"
            }`}
          />
        ))}
      </div>
    </div>
  )
}

const InstagramReelCarousel = () => {
  const [currentIndex, setCurrentIndex] = useState(0)

  const reels = [
    {
      id: 1,
      thumbnail: "/dj-performing-at-electronic-music-event-with-red-l.jpg",
      title: "Live Set @ ANIMA",
    },
    {
      id: 2,
      thumbnail: "/crowd-dancing-at-underground-techno-party.jpg",
      title: "Underground Vibes",
    },
    {
      id: 3,
      thumbnail: "/dj-mixing-on-cdj-turntables-with-neon-lights.jpg",
      title: "Behind the Decks",
    },
    {
      id: 4,
      thumbnail: "/electronic-music-festival-stage-with-laser-lights.jpg",
      title: "Festival Energy",
    },
    {
      id: 5,
      thumbnail: "/techno-party-crowd-with-hands-up-dancing.jpg",
      title: "Pure Energy",
    },
  ]

  useEffect(() => {
    const interval = setInterval(() => {
      setCurrentIndex((prevIndex) => (prevIndex + 1) % reels.length)
    }, 3000) // Change every 3 seconds

    return () => clearInterval(interval)
  }, [reels.length])

  return (
    <div className="w-full max-w-6xl mx-auto mt-16">
      <h3 className="text-2xl md:text-3xl font-black text-center mb-8 text-black tracking-wide">
        FOLLOW US ON INSTAGRAM
      </h3>

      <div className="relative overflow-hidden">
        <div
          className="flex transition-transform duration-500 ease-in-out"
          style={{ transform: `translateX(-${currentIndex * (100 / 3)}%)` }}
        >
          {reels.concat(reels).map((reel, index) => (
            <div key={`${reel.id}-${index}`} className="flex-shrink-0 w-1/3 px-2">
              <div className="relative group cursor-pointer">
                <div className="aspect-[9/16] bg-gray-100 rounded-lg overflow-hidden">
                  <img
                    src={reel.thumbnail || "/placeholder.svg"}
                    alt={reel.title}
                    className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                  />
                  <div className="absolute inset-0 bg-black/20 group-hover:bg-black/10 transition-colors duration-300" />
                  <div className="absolute bottom-4 left-4 right-4">
                    <p className="text-white font-bold text-sm">{reel.title}</p>
                  </div>
                  {/* Instagram play icon */}
                  <div className="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                    <div className="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
                      <div className="w-0 h-0 border-l-[8px] border-l-white border-t-[6px] border-t-transparent border-b-[6px] border-b-transparent ml-1" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Dots indicator */}
      <div className="flex justify-center mt-6 space-x-2">
        {reels.map((_, index) => (
          <button
            key={index}
            onClick={() => setCurrentIndex(index)}
            className={`w-2 h-2 rounded-full transition-colors duration-300 ${
              index === currentIndex ? "bg-primary" : "bg-gray-300"
            }`}
          />
        ))}
      </div>
    </div>
  )
}

const StageCarousel = () => {
  const [currentIndex, setCurrentIndex] = useState(0)

  const stageFeatures = [
    {
      id: 1,
      title: "FUNKTION-ONE SOUND SYSTEM",
      description: "Sistema audio di livello mondiale per un'esperienza sonora immersiva",
      image: "/funktion-one-sound-system.jpg",
      category: "AUDIO",
    },
    {
      id: 2,
      artist: "PIONEER CDJ-3000 DJ BOOTH",
      description: "Setup professionale per i migliori DJ internazionali",
      image: "/pioneer-cdj-dj-booth.jpg",
      category: "EQUIPMENT",
    },
    {
      id: 3,
      title: "LED MOVING HEADS LIGHTING",
      description: "Luci professionali che creano atmosfere uniche",
      image: "/led-moving-heads-lighting.jpg",
      category: "LIGHTING",
    },
    {
      id: 4,
      title: "4K VISUAL PROJECTIONS",
      description: "Proiezioni visual ad alta definizione per un'esperienza totale",
      image: "/4k-visual-projections.jpg",
      category: "VISUALS",
    },
  ]

  useEffect(() => {
    const interval = setInterval(() => {
      setCurrentIndex((prevIndex) => (prevIndex + 1) % stageFeatures.length)
    }, 4000)

    return () => clearInterval(interval)
  }, [stageFeatures.length])

  return (
    <div className="w-full max-w-7xl mx-auto mt-12">
      <div className="relative py-32">
        <div
          className="flex transition-transform duration-700 ease-in-out"
          style={{ transform: `translateX(-${currentIndex * (100 / 3)}%)` }}
        >
          {stageFeatures.concat(stageFeatures).map((feature, index) => (
            <div key={`${feature.id}-${index}`} className="flex-shrink-0 w-1/3 px-40">
              <div className="relative group cursor-pointer">
                <div className="aspect-[4/3] bg-gradient-to-br from-gray-900 to-black rounded-3xl overflow-hidden shadow-xl transform scale-[1.8]">
                  <img
                    src={
                      feature.image || `/placeholder.svg?height=300&width=400&query=${feature.title} stage equipment`
                    }
                    alt={feature.title}
                    className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent" />

                  {/* Feature Info Overlay */}
                  <div className="absolute inset-0 p-8 flex flex-col justify-between text-white">
                    <div className="text-left">
                      <span className="inline-block bg-primary/80 text-white text-sm font-bold px-4 py-2 rounded-full">
                        {feature.category}
                      </span>
                    </div>

                    <div className="text-left">
                      <h3 className="text-2xl md:text-3xl font-black mb-3 tracking-wide">{feature.title}</h3>
                      <p className="text-base opacity-90 leading-relaxed">{feature.description}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Navigation arrows */}
      <div className="flex justify-center mt-8 space-x-6">
        <button
          onClick={() => setCurrentIndex((prev) => (prev - 1 + stageFeatures.length) % stageFeatures.length)}
          className="w-12 h-12 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition-colors duration-300"
        >
          <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <button
          onClick={() => setCurrentIndex((prev) => (prev + 1) % stageFeatures.length)}
          className="w-12 h-12 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition-colors duration-300"
        >
          <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>
    </div>
  )
}

const MediaCarousel = () => {
  const [currentIndex, setCurrentIndex] = useState(0)

  const mediaItems = [
    {
      id: 1,
      title: "WHAT'S ON THIS WEEK",
      description: "Gli eventi imperdibili della settimana",
      image: "/whats-on-this-week-media.jpg",
      category: "EDITORIAL",
      type: "news",
    },
    {
      id: 2,
      title: "FROM IBIZA TO DUBAI: CALVIN HARRIS RETURNS",
      description: "Il ritorno del superstar DJ per un'esperienza unica",
      image: "/calvin-harris-returns-media.jpg",
      category: "EDITORIAL",
      type: "news",
    },
    {
      id: 3,
      title: "YOUR LAST CHANCE TO SEE MARTIN GARRIX",
      description: "L'ultima opportunità di vedere il DJ olandese questa stagione",
      image: "/martin-garrix-last-chance-media.jpg",
      category: "EDITORIAL",
      type: "news",
    },
    {
      id: 4,
      title: "ADRIATIQUE RETURNS TO DUBAI",
      description: "Il duo tedesco torna per una serata indimenticabile",
      image: "/adriatique-returns-media.jpg",
      category: "EDITORIAL",
      type: "news",
    },
    {
      id: 5,
      title: "AFTERMOVIE: CLOSING PARTY 2024",
      description: "Rivivi i momenti migliori della festa di chiusura",
      image: "/aftermovie-closing-party-media.jpg",
      category: "VIDEO",
      type: "aftermovie",
    },
  ]

  useEffect(() => {
    const interval = setInterval(() => {
      setCurrentIndex((prevIndex) => (prevIndex + 1) % mediaItems.length)
    }, 5000)

    return () => clearInterval(interval)
  }, [mediaItems.length])

  return (
    <div className="w-full max-w-7xl mx-auto mt-12">
      <div className="relative py-32">
        <div
          className="flex transition-transform duration-700 ease-in-out"
          style={{ transform: `translateX(-${currentIndex * (100 / 3)}%)` }}
        >
          {mediaItems.concat(mediaItems).map((item, index) => (
            <div key={`${item.id}-${index}`} className="flex-shrink-0 w-1/3 px-40">
              <div className="relative group cursor-pointer">
                <div className="aspect-[4/3] bg-gradient-to-br from-purple-900 via-blue-900 to-black rounded-3xl overflow-hidden shadow-xl transform scale-[1.8]">
                  <img
                    src={item.image || `/placeholder.svg?height=300&width=400&query=${item.title} event media`}
                    alt={item.title}
                    className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent" />

                  {/* Media Info Overlay */}
                  <div className="absolute inset-0 p-8 flex flex-col justify-between text-white">
                    <div className="text-left">
                      <span className="inline-block bg-white/20 text-white text-sm font-bold px-4 py-2 rounded-full backdrop-blur-sm">
                        {item.category}
                      </span>
                    </div>

                    <div className="text-left">
                      <h3 className="text-xl md:text-2xl font-black mb-3 tracking-wide leading-tight">{item.title}</h3>
                      <p className="text-sm opacity-90 leading-relaxed">{item.description}</p>
                    </div>
                  </div>

                  {/* Play button for videos */}
                  {item.type === "aftermovie" && (
                    <div className="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                      <div className="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm group-hover:bg-white/30 transition-colors duration-300">
                        <div className="w-0 h-0 border-l-[16px] border-l-white border-t-[10px] border-t-transparent border-b-[10px] border-b-transparent ml-1" />
                      </div>
                    </div>
                  )}
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Navigation arrows */}
      <div className="flex justify-center mt-8 space-x-6">
        <button
          onClick={() => setCurrentIndex((prev) => (prev - 1 + mediaItems.length) % mediaItems.length)}
          className="w-12 h-12 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition-colors duration-300"
        >
          <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <button
          onClick={() => setCurrentIndex((prev) => (prev + 1) % mediaItems.length)}
          className="w-12 h-12 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition-colors duration-300"
        >
          <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>
    </div>
  )
}

export function AccordionSections() {
  const [formData, setFormData] = useState({
    name: "",
    email: "",
    message: "",
  })

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    console.log("Form submitted:", formData)
  }

  const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: {
        staggerChildren: 0.3,
        delayChildren: 0.2,
      },
    },
  }

  const itemVariants = {
    hidden: {
      opacity: 0,
      y: 30,
    },
    visible: {
      opacity: 1,
      y: 0,
      transition: {
        duration: 0.8,
        ease: [0.25, 0.46, 0.45, 0.94],
      },
    },
  }

  const sections = [
    {
      id: "format",
      title: "WHERE MUSIC MEETS MAGIC",
      content:
        "Enter a musical wonderland, unlike anything you've ever experienced before. Ushuaia Ibiza has been the destination of choice for international thrill-seekers arriving on the island since its inception.",
    },
    {
      id: "venue",
      title: "CLOSING PARTY",
      content:
        "Siamo un collettivo di appassionati con oltre 10 anni di esperienza nella scena underground italiana. Il nostro spazio può ospitare fino a 800 persone in un ambiente unico e coinvolgente.",
    },
    {
      id: "shows",
      title: "UPCOMING SHOWS",
      content:
        "I prossimi eventi che non puoi perdere. Scopri i migliori artisti internazionali che calcheranno il nostro palco nei prossimi mesi.",
    },
    {
      id: "guests",
      title: "GUEST WALL",
      content:
        "I migliori DJ internazionali hanno calcato il nostro palco: Nina Kraviz, Richie Hawtin, Carl Cox, Paula Temple e molti altri artisti di fama mondiale.",
    },
    {
      id: "stage",
      title: "THE STAGE",
      content:
        "Setup tecnico di livello mondiale con sistema audio Funktion-One Resolution 6, DJ booth Pioneer CDJ-3000, luci moving heads LED e proiezioni visual 4K.",
    },
    {
      id: "media",
      title: "MEDIA",
      content:
        "Rivivi le emozioni delle nostre serate attraverso foto e video professionali che catturano l'energia unica di ogni evento ANIMA.",
    },
    {
      id: "contacts",
      title: "CONTATTI",
      content:
        "Entra in contatto con noi per informazioni, collaborazioni o semplicemente per condividere la tua passione per la musica elettronica.",
    },
  ]

  return (
    <motion.div
      className="max-w-4xl mx-auto px-4 py-20"
      variants={containerVariants}
      initial="hidden"
      whileInView="visible"
      viewport={{ once: true, margin: "-100px" }}
    >
      {sections.map((section, index) => (
        <motion.div key={section.id} variants={itemVariants} className="w-full mb-20 text-center">
          <h2 className="text-4xl md:text-5xl font-black mb-8 text-black tracking-wide">
            <AnimatedText text={section.title} className="text-black" />
          </h2>

          <div className="max-w-2xl mx-auto">
            <p className="text-lg md:text-xl leading-relaxed text-gray-700 mb-8">
              <AnimatedText text={section.content} delay={0.5} className="text-gray-700" />
            </p>
          </div>

          {section.id === "shows" && <UpcomingShowsCarousel />}

          {section.id === "stage" && <StageCarousel />}

          {section.id === "media" && <MediaCarousel />}

          {section.id === "contacts" && (
            <>
              <div className="max-w-md mx-auto mt-8">
                <form onSubmit={handleSubmit} className="space-y-4">
                  <Input
                    placeholder="Nome"
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                    className="border-gray-300 text-center"
                  />
                  <Input
                    placeholder="Email"
                    type="email"
                    value={formData.email}
                    onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                    className="border-gray-300 text-center"
                  />
                  <Textarea
                    placeholder="Messaggio"
                    value={formData.message}
                    onChange={(e) => setFormData({ ...formData, message: e.target.value })}
                    className="border-gray-300 text-center"
                  />
                  <Button type="submit" className="w-full bg-primary hover:bg-primary/90 text-white font-bold">
                    INVIA
                  </Button>
                </form>
              </div>

              <InstagramReelCarousel />
            </>
          )}

          {index < sections.length - 1 && <div className="w-full h-px bg-gray-300 mt-16"></div>}
        </motion.div>
      ))}
    </motion.div>
  )
}
