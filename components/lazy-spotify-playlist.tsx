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

  // Show Spotify button above hidden embed
  return (
    <div ref={containerRef} className={`relative ${className}`}>
      {/* Spotify button with green background and black text */}
      <div className="mb-4 flex justify-center">
        <a
          href={fallbackUrl}
          target="_blank"
          rel="noopener noreferrer"
          className="btn-primary bg-[#1DB954] hover:bg-[#1ed760] text-black px-3 md:px-8 py-3 md:py-4 text-sm md:text-base shadow-lg transition-all duration-200 flex items-center justify-center gap-2 rounded-md"
        >
          <svg className="w-5 h-5 md:w-6 md:h-6" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.42 1.56-.299.421-1.02.599-1.559.3z"/>
          </svg>
          <span>{t('section7.cta')}</span>
        </a>
      </div>
      
      {/* Hidden iframe for now */}
      {false && (
        <>
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
              style={{borderRadius: "12px", display: 'none'}} 
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
          )}
        </>
      )}
    </div>
  )
}


