"use client"

import Image from "next/image"
import { Instagram, Heart, MessageCircle } from "lucide-react"

export function InstagramGrid() {
  // Placeholder posts - sostituisci con post reali quando hai l'API token
  const placeholderPosts = [
    {
      id: "1",
      image: "/crowd-dancing-at-underground-techno-party.jpg",
      likes: "2.4K",
      comments: "156",
    },
    {
      id: "2",
      image: "/dj-performing-at-electronic-music-event-with-red-l.jpg",
      likes: "3.1K",
      comments: "203",
    },
    {
      id: "3",
      image: "/electronic-music-crowd-dancing-purple-lights.jpg",
      likes: "1.8K",
      comments: "89",
    },
    {
      id: "4",
      image: "/charlotte-de-witte-dj-poster-dark-techno.jpg",
      likes: "5.2K",
      comments: "421",
    },
    {
      id: "5",
      image: "/professional-dj-booth-with-cdj-and-mixer-purple-li.jpg",
      likes: "2.9K",
      comments: "178",
    },
    {
      id: "6",
      image: "/techno-party-crowd-with-hands-up-dancing.jpg",
      likes: "3.7K",
      comments: "245",
    },
    {
      id: "7",
      image: "/industrial-venue-interior-with-stage-and-purple-li.jpg",
      likes: "2.1K",
      comments: "134",
    },
    {
      id: "8",
      image: "/dj-mixing-on-cdj-turntables-with-neon-lights.jpg",
      likes: "4.3K",
      comments: "312",
    },
    {
      id: "9",
      image: "/amelie-lens-dj-poster-techno-event.jpg",
      likes: "6.5K",
      comments: "502",
    },
  ]

  return (
    <div className="grid grid-cols-3 gap-1 md:gap-2 w-full">
      {placeholderPosts.map((post) => (
        <a
          key={post.id}
          href="https://www.instagram.com/anima.ent"
          target="_blank"
          rel="noopener noreferrer"
          className="aspect-square relative group overflow-hidden bg-gray-900 cursor-pointer"
        >
          {/* Image */}
          <Image
            src={post.image}
            alt="ANIMA Instagram post"
            fill
            className="object-cover transition-all duration-500 group-hover:scale-110 group-hover:brightness-75"
            sizes="(max-width: 768px) 33vw, 25vw"
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
                    <span>{post.likes}</span>
                  </div>
                  <div className="flex items-center gap-1.5">
                    <MessageCircle className="w-4 h-4 md:w-5 md:h-5 fill-white" />
                    <span>{post.comments}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          {/* Gradient Bottom */}
          <div className="absolute bottom-0 left-0 right-0 h-20 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
        </a>
      ))}
    </div>
  )
}

