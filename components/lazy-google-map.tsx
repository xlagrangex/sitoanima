"use client"

import { useState, useRef, useEffect } from 'react'

interface LazyGoogleMapProps {
  src: string
  width?: string
  height?: string
  className?: string
}

export function LazyGoogleMap({ 
  src, 
  width = "100%", 
  height = "100%", 
  className = "" 
}: LazyGoogleMapProps) {
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
            }, 300)
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
          style={{ height: height }}
        >
          <div className="text-center text-white">
            <div className="animate-pulse rounded-full h-12 w-12 bg-gray-600 mx-auto mb-4"></div>
            <p className="text-sm opacity-70">Loading map...</p>
          </div>
        </div>
      ) : (
        // Actual Google Maps iframe
        <iframe 
          src={src}
          width={width}
          height={height}
          style={{border:0}} 
          allowFullScreen={true}
          loading="lazy" 
          referrerPolicy="no-referrer-when-downgrade"
        />
      )}
    </div>
  )
}

