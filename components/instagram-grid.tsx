"use client"

import { useEffect, useState } from "react"
import Image from "next/image"
import { Instagram } from "lucide-react"

interface InstagramPost {
  id: string
  media_type: 'IMAGE' | 'VIDEO' | 'CAROUSEL_ALBUM'
  media_url: string
  permalink: string
  caption?: string
  timestamp: string
}

export function InstagramGrid() {
  const [posts, setPosts] = useState<InstagramPost[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    const fetchInstagramPosts = async () => {
      try {
        const response = await fetch('/api/instagram')
        
        if (!response.ok) {
          throw new Error('Failed to fetch Instagram posts')
        }
        
        const data = await response.json()
        setPosts(data.data?.slice(0, 9) || [])
      } catch (err) {
        console.error('Error fetching Instagram posts:', err)
        setError('Unable to load Instagram posts')
      } finally {
        setLoading(false)
      }
    }

    fetchInstagramPosts()
  }, [])

  if (loading) {
    return (
      <div className="grid grid-cols-3 gap-1 md:gap-2 w-full">
        {Array.from({ length: 9 }).map((_, i) => (
          <div
            key={i}
            className="aspect-square bg-gray-200 dark:bg-gray-800 animate-pulse"
          />
        ))}
      </div>
    )
  }

  if (error || posts.length === 0) {
    return (
      <div className="w-full py-12 text-center">
        <Instagram className="w-16 h-16 mx-auto mb-4 text-gray-400" />
        <p className="text-gray-600 dark:text-gray-400">
          {error || 'No Instagram posts available'}
        </p>
        <p className="text-sm text-gray-500 mt-2">
          Follow us @anima_events for updates
        </p>
      </div>
    )
  }

  return (
    <div className="grid grid-cols-3 gap-1 md:gap-2 w-full">
      {posts.map((post) => (
        <a
          key={post.id}
          href={post.permalink}
          target="_blank"
          rel="noopener noreferrer"
          className="aspect-square relative group overflow-hidden bg-gray-100 dark:bg-gray-900"
        >
          <Image
            src={post.media_url}
            alt={post.caption?.slice(0, 100) || 'Instagram post'}
            fill
            className="object-cover transition-transform duration-300 group-hover:scale-110"
            sizes="(max-width: 768px) 33vw, 25vw"
          />
          <div className="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors duration-300 flex items-center justify-center">
            <Instagram className="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
          </div>
        </a>
      ))}
    </div>
  )
}

