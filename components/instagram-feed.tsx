"use client"

import Image from "next/image"
import { useRef, useEffect, useState } from "react"

export function InstagramFeed() {
  const scrollRef = useRef<HTMLDivElement>(null)
  const [isScrolling, setIsScrolling] = useState(false)

  const images = [
    "/crowd-dancing-at-underground-techno-party.jpg",
    "/dj-performing-at-electronic-music-event-with-red-l.jpg",
    "/electronic-music-crowd-dancing-purple-lights.jpg",
    "/charlotte-de-witte-dj-poster-dark-techno.jpg",
    "/professional-dj-booth-with-cdj-and-mixer-purple-li.jpg",
    "/techno-party-crowd-with-hands-up-dancing.jpg",
    "/industrial-venue-interior-with-stage-and-purple-li.jpg",
    "/dj-mixing-on-cdj-turntables-with-neon-lights.jpg",
    "/electronic-music-event-crowd-with-purple-lights.jpg",
    "/amelie-lens-dj-poster-techno-event.jpg",
    "/ben-klock-dj-poster-underground-techno.jpg",
    "/dj-performing-electronic-music-purple-lighting.jpg",
  ]

  useEffect(() => {
    const scroll = scrollRef.current
    if (!scroll) return

    let animationFrameId: number
    let scrollPosition = 0
    const scrollSpeed = 0.5 // pixels per frame

    const autoScroll = () => {
      if (!isScrolling && scroll) {
        scrollPosition += scrollSpeed
        
        // Reset position when reaching the end
        if (scrollPosition >= scroll.scrollWidth - scroll.clientWidth) {
          scrollPosition = 0
        }
        
        scroll.scrollLeft = scrollPosition
      }
      
      animationFrameId = requestAnimationFrame(autoScroll)
    }

    animationFrameId = requestAnimationFrame(autoScroll)

    const handleMouseEnter = () => setIsScrolling(true)
    const handleMouseLeave = () => setIsScrolling(false)
    const handleTouchStart = () => setIsScrolling(true)
    const handleTouchEnd = () => setIsScrolling(false)

    scroll.addEventListener('mouseenter', handleMouseEnter)
    scroll.addEventListener('mouseleave', handleMouseLeave)
    scroll.addEventListener('touchstart', handleTouchStart)
    scroll.addEventListener('touchend', handleTouchEnd)

    return () => {
      cancelAnimationFrame(animationFrameId)
      scroll.removeEventListener('mouseenter', handleMouseEnter)
      scroll.removeEventListener('mouseleave', handleMouseLeave)
      scroll.removeEventListener('touchstart', handleTouchStart)
      scroll.removeEventListener('touchend', handleTouchEnd)
    }
  }, [isScrolling])

  return (
    <div className="w-full bg-black py-8 md:py-12 overflow-hidden">
      <div
        ref={scrollRef}
        className="flex gap-2 md:gap-4 overflow-x-auto scrollbar-hide"
        style={{
          scrollbarWidth: 'none',
          msOverflowStyle: 'none',
          WebkitOverflowScrolling: 'touch'
        }}
      >
        {/* Duplicate images for infinite scroll effect */}
        {[...images, ...images].map((image, index) => (
          <div
            key={index}
            className="flex-shrink-0 w-[280px] h-[280px] md:w-[350px] md:h-[350px] relative group"
          >
            <Image
              src={image}
              alt={`ANIMA event ${index + 1}`}
              fill
              className="object-cover rounded-lg transition-all duration-300 group-hover:scale-105 group-hover:brightness-110"
              sizes="(max-width: 768px) 280px, 350px"
            />
            <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-lg" />
          </div>
        ))}
      </div>
    </div>
  )
}

