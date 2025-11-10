"use client"

import { useState, useRef, useEffect } from 'react'

interface LazySpotifyPlaylistProps {
  src: string
  width?: string
  height?: string
  className?: string
  fallbackUrl?: string
}

export function LazySpotifyPlaylist({ 
  src, 
  width = "100%", 
  height = "352", 
  className = "",
  fallbackUrl = "https://open.spotify.com/playlist/5WpQ7y1j9EhRMQlMKPJqSx?si=UO87sHUrQSOU5BDQYn6CQQ&pi=sQFh_xM3SnavA"
}: LazySpotifyPlaylistProps) {
  const [isLoaded, setIsLoaded] = useState(false)
  const [isVisible, setIsVisible] = useState(false)
  const [hasError, setHasError] = useState(false)
  const containerRef = useRef<HTMLDivElement>(null)
  const iframeRef = useRef<HTMLIFrameElement>(null)
  const errorTimeoutRef = useRef<NodeJS.Timeout | null>(null)

  useEffect(() => {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting && !isVisible) {
            setIsVisible(true)
            // Load iframe immediately when visible
            setIsLoaded(true)
            // Set timeout to detect if iframe fails to load
            // After 5 seconds, if iframe hasn't loaded properly, show error
            errorTimeoutRef.current = setTimeout(() => {
              // Check if iframe exists and has dimensions
              if (iframeRef.current) {
                const rect = iframeRef.current.getBoundingClientRect()
                // If iframe has no visible dimensions or is very small, might be blocked
                if (rect.width < 10 || rect.height < 10) {
                  setHasError(true)
                }
              } else {
                // Iframe doesn't exist, show error
                setHasError(true)
              }
            }, 5000)
          }
        })
      },
      { threshold: 0.1 }
    )

    if (containerRef.current) {
      observer.observe(containerRef.current)
    }

    return () => {
      observer.disconnect()
      if (errorTimeoutRef.current) {
        clearTimeout(errorTimeoutRef.current)
      }
    }
  }, [isVisible, hasError])

  const handleIframeError = () => {
    setHasError(true)
    if (errorTimeoutRef.current) {
      clearTimeout(errorTimeoutRef.current)
    }
  }

  // Reset error state when src changes
  useEffect(() => {
    setHasError(false)
  }, [src])

  // Show Spotify iframe embed
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
        <iframe 
          ref={iframeRef}
          data-testid="embed-iframe" 
          style={{borderRadius: "12px"}} 
          src="https://open.spotify.com/embed/playlist/5WpQ7y1j9EhRMQlMKPJqSx?utm_source=generator&theme=0"
          width={width}
          height={height}
          frameBorder="0" 
          allowFullScreen={true}
          allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" 
          loading="lazy"
          onError={handleIframeError}
          onLoad={() => {
            // Clear timeout if iframe loads successfully
            if (errorTimeoutRef.current) {
              clearTimeout(errorTimeoutRef.current)
              errorTimeoutRef.current = null
            }
          }}
        />
      )}
    </div>
  )
}


