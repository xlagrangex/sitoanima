"use client"

import { useEffect, useState } from "react"
import Image from "next/image"
import { Instagram, Heart, MessageCircle } from "lucide-react"
import instagramPostsData from "@/data/instagram-posts.json"

interface InstagramPost {
  id: string
  image: string
  alt: string
  url: string
  type: 'post' | 'reel' | 'carousel'
}

export function InstagramGrid() {
  const [posts, setPosts] = useState<InstagramPost[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    // Load posts from imported JSON data
    try {
      // Convert JSON format to component format
      const convertedPosts: InstagramPost[] = instagramPostsData.map((post: any) => ({
        id: post.id,
        image: post.image,
        alt: post.alt,
        url: post.url,
        type: post.type || 'post'
      }))
      
      // Mostra solo i primi 9 post (3 righe da 3)
      const allPosts = convertedPosts.slice(0, 9)
      setPosts(allPosts)
      setLoading(false)
    } catch (error) {
      console.error('Error loading Instagram posts:', error)
      setLoading(false)
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
    <div className="grid grid-cols-3 gap-1 md:gap-2 w-full">
      {posts.map((post, index) => {
        const likes = `${(Math.random() * 4 + 1).toFixed(1)}K`
        const comments = `${Math.floor(Math.random() * 400 + 100)}`
        
        return (
          <a
            key={post.id}
            href={post.url}
            target="_blank"
            rel="noopener noreferrer"
            className="aspect-[3/4] relative group overflow-hidden bg-gray-900 cursor-pointer rounded-lg"
          >
            {/* Image */}
            <Image
              src={post.image}
              alt={post.alt}
              fill
              className="object-cover transition-all duration-500 group-hover:scale-110 group-hover:brightness-75"
              sizes="(max-width: 768px) 33vw, 25vw"
              unoptimized={!post.id.startsWith('placeholder')} // Per immagini esterne da Instagram
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

