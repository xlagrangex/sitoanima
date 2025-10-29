"use client"

import { useState, useRef, useEffect } from 'react'

interface LazySpotifyPlaylistProps {
  src: string
  width?: string
  height?: string
  className?: string
}

export function LazySpotifyPlaylist({ 
  src, 
  width = "100%", 
  height = "352", 
  className = "" 
}: LazySpotifyPlaylistProps) {
  const [isLoaded, setIsLoaded] = useState(false)
  const [isVisible, setIsVisible] = useState(false)
  const containerRef = useRef<HTMLDivElement>(null)

  useEffect(() => {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting && !isVisible) {
            setIsVisible(true)
            // Delay loading to improve initial page performance
            setTimeout(() => {
              setIsLoaded(true)
            }, 500)
          }
        })
      },
      { threshold: 0.1 }
    )

    if (containerRef.current) {
      observer.observe(containerRef.current)
    }

    return () => observer.disconnect()
  }, [isVisible])

  return (
    <div ref={containerRef} className={`relative ${className}`}>
      {!isLoaded ? (
        // Placeholder while loading
        <div 
          className="w-full bg-gray-800 rounded-lg flex items-center justify-center"
          style={{ height: `${height}px` }}
        >
          <div className="text-center text-white">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-white mx-auto mb-4"></div>
            <p className="text-sm opacity-70">Loading playlist...</p>
          </div>
        </div>
      ) : (
        // Actual Spotify iframe
        <iframe 
          data-testid="embed-iframe" 
          style={{borderRadius: "12px"}} 
          src={src}
          width={width}
          height={height}
          frameBorder="0" 
          allowFullScreen={true}
          allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" 
          loading="lazy"
        />
      )}
    </div>
  )
}


