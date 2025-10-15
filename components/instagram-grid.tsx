"use client"

import { useEffect, useState } from "react"
import { Instagram, Heart, MessageCircle } from "lucide-react"

export function InstagramGrid() {
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    // Load Juicer script
    const script = document.createElement('script')
    script.src = 'https://www.juicer.io/embed/anima-ent/embed-code.js'
    script.async = true
    script.defer = true
    
    // Add custom CSS for Juicer styling
    const style = document.createElement('style')
    style.textContent = `
      /* Hide Juicer branding and default styling */
      .juicer-feed .j-logo,
      .juicer-feed .j-powered-by,
      .juicer-feed .j-meta,
      .juicer-feed .j-text,
      .juicer-feed .j-date,
      .juicer-feed .j-social,
      .juicer-feed .j-overlay {
        display: none !important;
      }
      
      /* Custom grid layout */
      .juicer-feed {
        display: grid !important;
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 4px !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
      }
      
      /* Custom feed item styling */
      .juicer-feed .feed-item {
        position: relative !important;
        aspect-ratio: 3/4 !important;
        overflow: hidden !important;
        background: #1f2937 !important;
        border-radius: 8px !important;
        cursor: pointer !important;
        transition: all 0.5s ease !important;
      }
      
      /* Image styling */
      .juicer-feed .feed-item img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        transition: all 0.5s ease !important;
      }
      
      /* Hover effects */
      .juicer-feed .feed-item:hover img {
        transform: scale(1.1) !important;
        filter: brightness(0.75) !important;
      }
      
      /* Custom hover overlay */
      .juicer-feed .feed-item::before {
        content: '' !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        background: rgba(0, 0, 0, 0) !important;
        transition: all 0.3s ease !important;
        z-index: 2 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
      }
      
      .juicer-feed .feed-item:hover::before {
        background: rgba(0, 0, 0, 0.4) !important;
      }
      
      /* Instagram icon and stats overlay */
      .juicer-feed .feed-item::after {
        content: '📷' !important;
        position: absolute !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) translateY(16px) !important;
        opacity: 0 !important;
        transition: all 0.3s ease !important;
        z-index: 3 !important;
        font-size: 2rem !important;
        color: white !important;
        text-shadow: 0 2px 4px rgba(0,0,0,0.5) !important;
      }
      
      .juicer-feed .feed-item:hover::after {
        opacity: 1 !important;
        transform: translate(-50%, -50%) translateY(0) !important;
      }
      
      /* Stats simulation */
      .juicer-feed .feed-item .j-stats {
        position: absolute !important;
        bottom: 20px !important;
        left: 50% !important;
        transform: translateX(-50%) translateY(16px) !important;
        opacity: 0 !important;
        transition: all 0.3s ease !important;
        z-index: 3 !important;
        display: flex !important;
        gap: 16px !important;
        color: white !important;
        font-size: 14px !important;
        font-weight: 600 !important;
      }
      
      .juicer-feed .feed-item:hover .j-stats {
        opacity: 1 !important;
        transform: translateX(-50%) translateY(0) !important;
      }
      
      /* Gradient bottom */
      .juicer-feed .feed-item .j-gradient {
        position: absolute !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        height: 80px !important;
        background: linear-gradient(to top, rgba(0,0,0,0.5), transparent) !important;
        opacity: 0 !important;
        transition: opacity 0.3s ease !important;
        z-index: 1 !important;
      }
      
      .juicer-feed .feed-item:hover .j-gradient {
        opacity: 1 !important;
      }
      
      /* Mobile responsive */
      @media (max-width: 768px) {
        .juicer-feed {
          gap: 2px !important;
        }
        
        .juicer-feed .feed-item::after {
          font-size: 1.5rem !important;
        }
        
        .juicer-feed .feed-item .j-stats {
          font-size: 12px !important;
          gap: 12px !important;
        }
      }
    `
    document.head.appendChild(style)
    
    // Create Juicer container
    const juicerContainer = document.createElement('div')
    juicerContainer.innerHTML = '<ul class="juicer-feed" data-feed-id="anima-ent" data-per="9"></ul>'
    
    // Add stats elements to each feed item after they load
    const addStatsToItems = () => {
      const items = juicerContainer.querySelectorAll('.feed-item')
      items.forEach((item, index) => {
        if (!item.querySelector('.j-stats')) {
          const stats = document.createElement('div')
          stats.className = 'j-stats'
          const likes = (Math.random() * 4 + 1).toFixed(1) + 'K'
          const comments = Math.floor(Math.random() * 400 + 100)
          stats.innerHTML = `
            <span>❤️ ${likes}</span>
            <span>💬 ${comments}</span>
          `
          item.appendChild(stats)
          
          const gradient = document.createElement('div')
          gradient.className = 'j-gradient'
          item.appendChild(gradient)
        }
      })
    }
    
    // Monitor for new items
    const observer = new MutationObserver(() => {
      addStatsToItems()
      if (juicerContainer.querySelectorAll('.feed-item').length > 0) {
        setLoading(false)
      }
    })
    
    observer.observe(juicerContainer, { childList: true, subtree: true })
    
    // Append to the container div
    const container = document.getElementById('juicer-container')
    if (container) {
      container.appendChild(juicerContainer)
    }
    document.body.appendChild(script)
    
    // Initial check
    setTimeout(addStatsToItems, 1000)
    
    // Timeout
    setTimeout(() => {
      setLoading(false)
    }, 10000)

    return () => {
      observer.disconnect()
      if (script.parentNode) script.parentNode.removeChild(script)
      if (juicerContainer.parentNode) juicerContainer.parentNode.removeChild(juicerContainer)
      if (style.parentNode) style.parentNode.removeChild(style)
    }
  }, [])

  if (loading) {
    return (
      <div className="grid grid-cols-3 gap-1 md:gap-2 w-full">
        {Array.from({ length: 9 }).map((_, i) => (
          <div
            key={i}
            className="aspect-[3/4] bg-gray-800 animate-pulse rounded-lg"
          />
        ))}
      </div>
    )
  }

  return (
    <div className="w-full">
      {/* Juicer feed will be injected here by the script */}
      <div id="juicer-container" className="w-full"></div>
    </div>
  )
}

