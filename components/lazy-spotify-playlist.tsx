"use client"

import { useState, useRef, useEffect } from 'react'
import { Music } from 'lucide-react'

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

  // If there's an error, show only the button
  if (hasError) {
    return (
      <div className={`flex flex-col items-center justify-center gap-4 ${className}`} style={{ height: `${height}px` }}>
        <div className="text-center text-gray-400 mb-2">
          <p className="text-sm mb-1">Impossibile caricare l'anteprima</p>
          <p className="text-xs opacity-70">Problema temporaneo con Spotify</p>
        </div>
        <a
          href={fallbackUrl}
          target="_blank"
          rel="noopener noreferrer"
          className="btn-primary bg-black hover:bg-gray-900 text-white px-3 md:px-8 py-3 md:py-4 text-sm md:text-base shadow-lg transition-all duration-200 flex items-center justify-center gap-2"
        >
          <Music className="w-4 h-4 md:w-5 md:h-5" />
          <span>Ascolta su Spotify</span>
        </a>
      </div>
    )
  }

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
        // Show iframe and always show fallback button below
        <>
          <iframe 
            ref={iframeRef}
            data-testid="embed-iframe" 
            style={{borderRadius: "12px"}} 
            src={src}
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
          {/* Always show fallback button below iframe in case Spotify shows errors */}
          <div className="mt-2 flex justify-center">
            <a
              href={fallbackUrl}
              target="_blank"
              rel="noopener noreferrer"
              className="btn-primary bg-black hover:bg-gray-900 text-white px-3 md:px-8 py-3 md:py-4 text-sm md:text-base shadow-lg transition-all duration-200 flex items-center justify-center gap-2"
            >
              <Music className="w-4 h-4 md:w-5 md:h-5" />
              <span>Ascolta su Spotify</span>
            </a>
          </div>
        </>
      )}
    </div>
  )
}


