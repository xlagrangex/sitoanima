"use client"

import { useState, useRef, useEffect } from 'react'
import { useLanguage } from '@/contexts/LanguageContext'

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
  const { t } = useLanguage()
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
            // Delay loading to improve initial page performance
            setTimeout(() => {
              setIsLoaded(true)
              // Set timeout to detect if iframe fails to load
              // After 5 seconds (reduced from 8), if iframe hasn't loaded properly, show fallback
              // This catches upstream timeout errors from Spotify/CDN
              errorTimeoutRef.current = setTimeout(() => {
                // Check if iframe exists and has dimensions
                if (iframeRef.current) {
                  const rect = iframeRef.current.getBoundingClientRect()
                  // If iframe has no visible dimensions or is very small, might be blocked
                  if (rect.width < 10 || rect.height < 10) {
                    setHasError(true)
                  } else {
                    // Try to detect if content actually loaded by checking after a short delay
                    // If Spotify shows upstream errors, the iframe might be visible but broken
                    setTimeout(() => {
                      // If still having issues, show fallback button prominently
                      // Spotify upstream errors can't be detected directly due to CORS,
                      // but timeout suggests there's a problem
                      if (!hasError) {
                        // Keep iframe but user can use fallback button if needed
                      }
                    }, 2000)
                  }
                } else {
                  // Iframe doesn't exist, show error
                  setHasError(true)
                }
              }, 5000) // Reduced to 5 seconds to catch upstream timeout errors faster
            }, 500)
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

  // Show Spotify iframe directly
  return (
    <div ref={containerRef} className={`relative ${className}`}>
      <iframe 
        data-testid="embed-iframe" 
        style={{ borderRadius: "12px" }} 
        src="https://open.spotify.com/embed/playlist/5WpQ7y1j9EhRMQlMKPJqSx?utm_source=generator&theme=0" 
        width="100%" 
        height="352" 
        frameBorder="0" 
        allowFullScreen={true}
        allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" 
        loading="lazy"
      />
    </div>
  )
}


