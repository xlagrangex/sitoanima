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
              // After 8 seconds, if iframe hasn't loaded properly, show fallback
              errorTimeoutRef.current = setTimeout(() => {
                // Check if iframe exists and has dimensions
                if (iframeRef.current) {
                  const rect = iframeRef.current.getBoundingClientRect()
                  // If iframe has no visible dimensions or is very small, might be blocked
                  if (rect.width < 10 || rect.height < 10) {
                    setHasError(true)
                  } else {
                    // Double check after another 2 seconds to be sure
                    setTimeout(() => {
                      // If still not working, show fallback
                      // This is a fallback for cases where iframe loads but content is blocked
                      if (!hasError) {
                        // Try to detect if content is actually loaded
                        // Since we can't access cross-origin content, we'll rely on user interaction
                        // But for now, we'll keep the iframe unless explicitly errored
                      }
                    }, 2000)
                  }
                }
              }, 8000) // 8 seconds timeout - if Spotify embed doesn't load, show fallback
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
      <div className={`flex flex-col items-center justify-center gap-4 ${className}`}>
        <div className="text-center text-gray-400 mb-2">
          <p className="text-sm">Impossibile caricare l'anteprima</p>
        </div>
        <a
          href={fallbackUrl}
          target="_blank"
          rel="noopener noreferrer"
          className="flex items-center gap-3 px-6 py-4 bg-[#1DB954] hover:bg-[#1ed760] text-white font-semibold rounded-full shadow-lg transition-all duration-200 transform hover:scale-105"
        >
          <Music className="w-6 h-6" />
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
          <div className="mt-4 flex justify-center">
            <a
              href={fallbackUrl}
              target="_blank"
              rel="noopener noreferrer"
              className="flex items-center gap-3 px-6 py-4 bg-[#1DB954] hover:bg-[#1ed760] text-white font-semibold rounded-full shadow-lg transition-all duration-200 transform hover:scale-105"
            >
              <Music className="w-5 h-5" />
              <span>Ascolta su Spotify</span>
            </a>
          </div>
        </>
      )}
    </div>
  )
}


