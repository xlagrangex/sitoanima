"use client"

import { useEffect } from "react"

export function InstagramGrid() {
  useEffect(() => {
    // Load Juicer script dynamically
    const script = document.createElement('script')
    script.src = 'https://www.juicer.io/embed/anima-ent/embed-code.js'
    script.async = true
    script.defer = true
    document.body.appendChild(script)

    return () => {
      // Cleanup script on unmount
      if (script.parentNode) {
        script.parentNode.removeChild(script)
      }
    }
  }, [])

  return (
    <div className="instagram-juicer-container w-full">
      {/* Juicer Feed */}
      <ul className="juicer-feed" data-feed-id="anima-ent" data-per="9" data-columns="3"></ul>
      
      <style jsx global>{`
        /* Override Juicer styles - formato 4:3 e hover effects */
        .juicer-feed {
          list-style: none !important;
          padding: 0 !important;
          margin: 0 !important;
          display: grid !important;
          grid-template-columns: repeat(3, 1fr) !important;
          gap: 0.25rem !important;
        }

        @media (min-width: 768px) {
          .juicer-feed {
            gap: 0.5rem !important;
          }
        }

        .juicer-feed li {
          margin: 0 !important;
          padding: 0 !important;
          list-style: none !important;
          aspect-ratio: 3/4 !important;
          position: relative !important;
          overflow: hidden !important;
          border-radius: 0.5rem !important;
        }

        .juicer-feed li a {
          display: block !important;
          width: 100% !important;
          height: 100% !important;
          position: relative !important;
          overflow: hidden !important;
        }

        .juicer-feed li img,
        .juicer-feed li video {
          width: 100% !important;
          height: 100% !important;
          object-fit: cover !important;
          transition: transform 0.5s ease, filter 0.3s ease !important;
        }

        /* Hover Effects - come prima */
        .juicer-feed li:hover img,
        .juicer-feed li:hover video {
          transform: scale(1.1) !important;
          filter: brightness(0.75) !important;
        }

        .juicer-feed li::after {
          content: '' !important;
          position: absolute !important;
          inset: 0 !important;
          background: rgba(0, 0, 0, 0) !important;
          transition: background 0.3s ease !important;
          pointer-events: none !important;
        }

        .juicer-feed li:hover::after {
          background: rgba(0, 0, 0, 0.4) !important;
        }

        /* Hide Juicer branding/watermark */
        .juicer-feed .j-paginate,
        .juicer-feed .juicer-button,
        .j-meta,
        .j-text,
        .j-poster {
          display: none !important;
        }

        /* Remove default padding/margins */
        .juicer-feed * {
          box-sizing: border-box !important;
        }

        /* Nascondi tutto tranne le immagini */
        .juicer-feed .j-stack,
        .juicer-feed .j-message,
        .juicer-feed .j-meta-group {
          display: none !important;
        }
      `}</style>
    </div>
  )
}

