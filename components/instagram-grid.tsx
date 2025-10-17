"use client"

import { useEffect, useState } from "react"
import Image from "next/image"
import { Instagram, Heart, MessageCircle } from "lucide-react"

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
    // Estrai i dati dall'HTML che hai fornito
    const extractedPosts: InstagramPost[] = [
      {
        id: "DPyfZ9nDR9r",
        image: "https://scontent.cdninstagram.com/v/t51.82787-15/565824898_17938858926084668_7817380969821783136_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=108&ig_cache_key=Mzc0MzE5MjM0ODE5MzgyNjg1OA%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjE0NDB4MTkyMC5zZHIuQzMifQ%3D%3D&_nc_ohc=06U6X5O7plAQ7kNvwGxUawP&_nc_oc=AdmvPoN9G14ac4luo8qm-exNwKk8W2kTojS5U39sRCsntrURDLYojoKdTvxWGHD13JU&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=dmHTrF38SiaQ_RYJzlEiMA&oh=00_AfeCfi5UdyST8XA-4io26v2iAH7lUJJWIg-rOj0hL66zbg&oe=68F59408",
        alt: "The faces of a new chapter ☀️ Every Friday at @hbtoo.official Until the Sun Rises ☀️ - Unreleased pics on Telegram, link in bio 🔗",
        url: "https://www.instagram.com/p/DPyfZ9nDR9r/",
        type: "carousel"
      },
      {
        id: "DPwsmaJjZrw",
        image: "https://scontent.cdninstagram.com/v/t51.71878-15/563916359_1076013237753036_4760748830051411946_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=108&ig_cache_key=Mzc0MjY4NzQ0MzgwOTA0OTMyOA%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjY0MHgxMTM2LnNkci5DMyJ9&_nc_ohc=9fn16eoW6xkQ7kNvwHnRBvh&_nc_oc=Admha2tG8tfCekrn9yWum5UQaWI3wyEFUHbWnvQy8v7V726S5hbHZemDsyhU2VBBVxU&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=dmHTrF38SiaQ_RYJzlEiMA&oh=00_AfeZyDrSSuv8J8SHo7raYHyJJ-GTMvl2Poj8ga2i4OHSig&oe=68F58606",
        alt: "Two worlds illuminated by the same Sun ☀️ Anima meets @dolcevita__rome for an unforgettable party 🤝🏻 Season 3 — Act II Until the Sun rises ☀️ @gianni_presutti from @dolcevita__rome @alexsilvestrimusic b2b @marenna.music @leolitterio 🏠 @hbtoo.official",
        url: "https://www.instagram.com/reel/DPwsmaJjZrw/",
        type: "reel"
      },
      {
        id: "DPmXRBPDZpQ",
        image: "https://scontent.cdninstagram.com/v/t51.82787-15/562537022_17938377252084668_1943177837770365742_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=102&ig_cache_key=MzczOTc3ODg1NzUxMjU5ODU0OA%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjE0NDB4MTgwMC5zZHIuQzMifQ%3D%3D&_nc_ohc=rDQ6EouBEuwQ7kNvwEbwrDF&_nc_oc=AdnBIG9dj1cZ48NHM8KEcsoaCO7C2HyE8Fod5PX60z4k2pZ-J2vjanM2MR8vJfx6U_s&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=dmHTrF38SiaQ_RYJzlEiMA&oh=00_AffhmijKCYE7DcZtb1jIUroL7h94baiexCf9U4ZFhNT80Q&oe=68F58C4F",
        alt: "Community guidelines ☀ Learn how could you be part of Anima Until the Sun rises ☀ See you tomorrow at 🏠 @hbtoo.official",
        url: "https://www.instagram.com/p/DPmXRBPDZpQ/",
        type: "carousel"
      },
      {
        id: "DPerq-sDTNO",
        image: "https://scontent.cdninstagram.com/v/t51.71878-15/561648270_1969327337249313_8473843443143903905_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=111&ig_cache_key=MzczNzYxNjgxMDI5MzE0NjQ0Ng%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjY0MHgxMTM2LnNkci5DMyJ9&_nc_ohc=T3c0SywTPWwQ7kNvwF3fOoG&_nc_oc=AdkuZXifi7NH0D-CKR9MXgPq0Lpxnnnh34h1mt4b2PYt6LDeBI4HQiUqCf-NAUeYw04&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=dmHTrF38SiaQ_RYJzlEiMA&oh=00_AfcpAAFnjdqIA29x37TLiY4u78fbPlE9wEdPU55i7ofrzg&oe=68F56837",
        alt: "We are back. For the third year in a row, Anima is here to shine once again ☀️ Season 3 — Act I Until the Sun rises ☀️ Anima Dj Booth | A-Z order @alexsilvestrimusic @carlogiorgetto_ @hoodiamusic @manuelguida 🏠 @hbtoo.official",
        url: "https://www.instagram.com/reel/DPerq-sDTNO/",
        type: "reel"
      },
      {
        id: "DPUUmuLDe04",
        image: "https://scontent.cdninstagram.com/v/t51.82787-15/558981942_17937663765084668_2580632001886427385_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=109&ig_cache_key=MzczNDcwMDYxMjg0NDkwNzgzMjE3OTM3NjYzNzU5MDg0NjY4.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjY2MngxMTc2LnNkci5DMyJ9&_nc_ohc=9Sv2-TK6xEMQ7kNvwGfL6O3&_nc_oc=Adlr09ligMoRWgQdWONyYgP7ix1OMinzvQ5lqzZmX-qE1xbr9L2TQa06_AsHZo27ZzA&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=dmHTrF38SiaQ_RYJzlEiMA&oh=00_Afd2ffQwnBM2_syFk1z0r3Q3LrFNrPnQAdLZXvt0nnaAoQ&oe=68F597EB",
        alt: "Season 3 begins. ANIMA — Act I The journey continues soon Until the Sun rises ☀️ 🏠 @hbtoo.official Directed & recorded by @panu.mov",
        url: "https://www.instagram.com/reel/DPUUmuLDe04/",
        type: "reel"
      }
    ]

    // Aggiungi solo 1 post placeholder per arrivare a 6 (2 righe da 3)
    const placeholderPosts: InstagramPost[] = [
      { id: "placeholder1", image: "/placeholder.jpg", alt: "ANIMA Instagram", url: "https://www.instagram.com/anima.ent", type: "post" }
    ]

    const allPosts = [...extractedPosts, ...placeholderPosts].slice(0, 6)
    setPosts(allPosts)
    setLoading(false)
  }, [])

  if (loading) {
    return (
      <div className="grid grid-cols-3 gap-1 md:gap-2 w-full">
        {Array.from({ length: 6 }).map((_, i) => (
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

