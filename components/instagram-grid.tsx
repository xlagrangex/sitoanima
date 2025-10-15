"use client"

import { useEffect, useState } from "react"
import Image from "next/image"
import { Instagram, Heart, MessageCircle } from "lucide-react"

interface JuicerPost {
  id: string
  image: string
  external_url: string
}

export function InstagramGrid() {
  const [posts, setPosts] = useState<JuicerPost[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    // Load Juicer script
    const script = document.createElement('script')
    script.src = 'https://www.juicer.io/embed/anima-ent/embed-code.js'
    script.async = true
    script.defer = true
    
    // Create hidden container for Juicer
    const hiddenContainer = document.createElement('div')
    hiddenContainer.style.display = 'none'
    hiddenContainer.innerHTML = '<ul class="juicer-feed" data-feed-id="anima-ent" data-per="9"></ul>'
    document.body.appendChild(hiddenContainer)
    document.body.appendChild(script)

    // Wait for Juicer to load and extract data
    const checkJuicerLoaded = setInterval(() => {
      const juicerItems = hiddenContainer.querySelectorAll('.feed-item')
      
      if (juicerItems.length > 0) {
        clearInterval(checkJuicerLoaded)
        
        const extractedPosts: JuicerPost[] = []
        
        juicerItems.forEach((item, index) => {
          if (index >= 9) return
          
          const img = item.querySelector('img')
          const link = item.querySelector('a')
          
          if (img && link) {
            extractedPosts.push({
              id: `juicer-${index}`,
              image: img.src,
              external_url: link.href,
            })
          }
        })
        
        if (extractedPosts.length > 0) {
          setPosts(extractedPosts)
        }
        setLoading(false)
      }
    }, 500)

    // Timeout dopo 10 secondi
    setTimeout(() => {
      clearInterval(checkJuicerLoaded)
      setLoading(false)
    }, 10000)

    return () => {
      clearInterval(checkJuicerLoaded)
      if (script.parentNode) script.parentNode.removeChild(script)
      if (hiddenContainer.parentNode) hiddenContainer.parentNode.removeChild(hiddenContainer)
    }
  }, [])

  // Placeholder mentre carica o se fallisce
  const placeholderPosts = [
    { id: "1", image: "/crowd-dancing-at-underground-techno-party.jpg", external_url: "https://www.instagram.com/anima.ent" },
    { id: "2", image: "/dj-performing-at-electronic-music-event-with-red-l.jpg", external_url: "https://www.instagram.com/anima.ent" },
    { id: "3", image: "/electronic-music-crowd-dancing-purple-lights.jpg", external_url: "https://www.instagram.com/anima.ent" },
    { id: "4", image: "/charlotte-de-witte-dj-poster-dark-techno.jpg", external_url: "https://www.instagram.com/anima.ent" },
    { id: "5", image: "/professional-dj-booth-with-cdj-and-mixer-purple-li.jpg", external_url: "https://www.instagram.com/anima.ent" },
    { id: "6", image: "/techno-party-crowd-with-hands-up-dancing.jpg", external_url: "https://www.instagram.com/anima.ent" },
    { id: "7", image: "/industrial-venue-interior-with-stage-and-purple-li.jpg", external_url: "https://www.instagram.com/anima.ent" },
    { id: "8", image: "/dj-mixing-on-cdj-turntables-with-neon-lights.jpg", external_url: "https://www.instagram.com/anima.ent" },
    { id: "9", image: "/amelie-lens-dj-poster-techno-event.jpg", external_url: "https://www.instagram.com/anima.ent" },
  ]

  const displayPosts = posts.length > 0 ? posts : placeholderPosts

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
    <div className="grid grid-cols-3 gap-1 md:gap-2 w-full">
      {displayPosts.map((post, index) => {
        const likes = `${(Math.random() * 4 + 1).toFixed(1)}K`
        const comments = `${Math.floor(Math.random() * 400 + 100)}`
        
        return (
          <a
            key={post.id}
            href={post.external_url}
            target="_blank"
            rel="noopener noreferrer"
            className="aspect-[3/4] relative group overflow-hidden bg-gray-900 cursor-pointer rounded-lg"
          >
            {/* Image */}
            <Image
              src={post.image}
              alt="ANIMA Instagram post"
              fill
              className="object-cover transition-all duration-500 group-hover:scale-110 group-hover:brightness-75"
              sizes="(max-width: 768px) 33vw, 25vw"
              unoptimized={posts.length > 0} // Per immagini esterne da Juicer
            />
            
            {/* Hover Overlay */}
            <div className="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all duration-300 flex items-center justify-center">
              <div className="opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-4 group-hover:translate-y-0">
                {/* Instagram Icon */}
                <div className="flex flex-col items-center gap-3">
                  <Instagram className="w-10 h-10 md:w-12 md:h-12 text-white drop-shadow-lg" />
                  
                  {/* Stats */}
                  <div className="flex items-center gap-4 text-white text-sm md:text-base font-semibold">
                    <div className="flex items-center gap-1.5">
                      <Heart className="w-4 h-4 md:w-5 md:h-5 fill-white" />
                      <span>{likes}</span>
                    </div>
                    <div className="flex items-center gap-1.5">
                      <MessageCircle className="w-4 h-4 md:w-5 md:h-5 fill-white" />
                      <span>{comments}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            {/* Gradient Bottom */}
            <div className="absolute bottom-0 left-0 right-0 h-20 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
          </a>
        )
      })}
    </div>
  )
}

