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
    // Ultimi 9 post estratti dall'HTML Instagram di anima.ent
    const extractedPosts: InstagramPost[] = [
      {
        id: "DQHFiV6DZlG",
        image: "https://scontent.cdninstagram.com/v/t51.82787-15/569956739_17939671992084668_4996891839792711689_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=102&ig_cache_key=Mzc0ODk4OTU0ODcyNzQ4NDY0MQ%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjE0NDB4MTkyMC5zZHIuQzMifQ%3D%3D&_nc_ohc=Mqliv6dExVEQ7kNvwHLezl2&_nc_oc=AdkkDnrWYYpKpBwygC2UhYI43w3IECqWPkT0j8kwD4Antj2YooQWoJkx6LVj55DTWDU&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=cFoy-ZzHmrTvyonaRMzYTA&oh=00_Afcz6NN-QJV791yPAhgealqAvcoZrnfeLIIoLl7WUJuMUQ&oe=690000D1",
        alt: "Moments from Friday ☀️ thanks to our friends from @dolcevita__rome for joining us for a fantastic party Every Friday at @hbtoo.official Until the Sun Rises ☀️ - Unreleased pics on Telegram, link in bio 🔗",
        url: "https://www.instagram.com/anima.ent/p/DQHFiV6DZlG/",
        type: "carousel"
      },
      {
        id: "DQErm0FDFUW",
        image: "https://scontent.cdninstagram.com/v/t51.82787-15/568781270_18075461702137838_6321427613533355128_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=104&ig_cache_key=Mzc0ODMxMjU3MzEzODQ1Nzg3ODE4MDc1NDYxNjk2MTM3ODM4.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjcyMHgxMjgwLnNkci5DMyJ9&_nc_ohc=zkgP9gnbmwoQ7kNvwF3kQMb&_nc_oc=AdkmhL4OzM4zRRmABDl96MhrgS4HZvWWTtFF1YxwpKhdrLY25beYZ6i3V7rJPc6dGmY&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=cFoy-ZzHmrTvyonaRMzYTA&oh=00_AfdCzsfXfZo88w3kaikP_5YEzawXNnhqgcw4RpzgEqQc5w&oe=68FFF775",
        alt: "25.10 // Under the same sun, again☀️ Dolcevita meets @anima.ent at @theflatbymacan #Dolcevita #porscheitalia",
        url: "https://www.instagram.com/dolcevita__rome/reel/DQErm0FDFUW/",
        type: "reel"
      },
      {
        id: "DQCq_v6Ddxs",
        image: "https://scontent.cdninstagram.com/v/t51.71878-15/567353087_1219370173568255_3348238122376727574_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=108&ig_cache_key=Mzc0Nzc0NjkzODY0NjAyNzM3Mg%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjY0MHgxMTM2LnNkci5DMyJ9&_nc_ohc=DeoQeavbxVoQ7kNvwFW5pnM&_nc_oc=AdnQZqpfJ6okST053myke14jnZRh8te0Q1_HwPK-0UkuOhB6xbWyLVf96-ie-Q4RgAo&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=cFoy-ZzHmrTvyonaRMzYTA&oh=00_AfeZJRqdSPPMN1ZpBAGoTanPscRykN0AzfN09cZQPXA6cA&oe=690017E3",
        alt: "Welcome to the Sunrise Society ☀️ Season 3 — Act III Until the Sun rises ☀️ Anima Dj Booth | A-Z order @alexsilvestrimusic @1xloco @mattia_scodellaro @monamii.music 🏠 @hbtoo.official",
        url: "https://www.instagram.com/anima.ent/reel/DQCq_v6Ddxs/",
        type: "reel"
      },
      {
        id: "DP4YVf3jcxT",
        image: "https://scontent.cdninstagram.com/v/t51.71878-15/566628690_1324336816037974_4379666572277382920_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=109&ig_cache_key=Mzc0NDg1MDEyMDYwMTg4MTY4Mw%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjY0MHgxMTM2LnNkci5DMyJ9&_nc_ohc=sLDZvenno_AQ7kNvwFDOMKH&_nc_oc=AdnR_QK937SIXCxvOgv73nW8PiSXdqpCpkh41eGZxa2ayTrcxIAMsCyovG9XHk2PwJs&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=cFoy-ZzHmrTvyonaRMzYTA&oh=00_AfetklRU30A9sOkcLULV5wqJjTwyw_0DScbeqR-22Rd-rw&oe=69000A7A",
        alt: "Flashes from an outstanding first night ☀️ Third Season has officially begun See you tomorrow at home 🏠 @hbtoo.official 📹 @panu.mov",
        url: "https://www.instagram.com/anima.ent/reel/DP4YVf3jcxT/",
        type: "reel"
      },
      {
        id: "DPyfZ9nDR9r",
        image: "https://scontent.cdninstagram.com/v/t51.82787-15/565824898_17938858926084668_7817380969821783136_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=108&ig_cache_key=Mzc0MzE5MjM0ODE5MzgyNjg1OA%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjE0NDB4MTkyMC5zZHIuQzMifQ%3D%3D&_nc_ohc=PkshpBfHkGIQ7kNvwFzX7t7&_nc_oc=AdlJrcchCz0jK_pUSQngFNdkskfhIUYSMzDf0SMck6yg-1sNAbE0xMHkKpjNIpC6BkE&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=cFoy-ZzHmrTvyonaRMzYTA&oh=00_AfeKRcLWvYfFgtQnrmVGEwsTpYCB3c0W6YFl-49sFSmlsw&oe=68FFE7C8",
        alt: "The faces of a new chapter ☀️ Every Friday at @hbtoo.official Until the Sun Rises ☀️ - Unreleased pics on Telegram, link in bio 🔗",
        url: "https://www.instagram.com/anima.ent/p/DPyfZ9nDR9r/",
        type: "carousel"
      },
      {
        id: "DPwsmaJjZrw",
        image: "https://scontent.cdninstagram.com/v/t51.71878-15/563916359_1076013237753036_4760748830051411946_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=108&ig_cache_key=Mzc0MjY4NzQ0MzgwOTA0OTMyOA%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjY0MHgxMTM2LnNkci5DMyJ9&_nc_ohc=HWz8P006alYQ7kNvwGeWQ6f&_nc_oc=AdkSFthOP9ctO80KcdmBTOajuJQftjg5UKQd5Xrn2bsB72B6hQT0f8wpW1anUi4Y9Mo&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=cFoy-ZzHmrTvyonaRMzYTA&oh=00_AffrsU0517ywaCjfBTmZF3sV7UYKrTs6Wpqocjn5CQdQxA&oe=69001206",
        alt: "Two worlds illuminated by the same Sun ☀️ Anima meets @dolcevita__rome for an unforgettable party 🤝🏻 Season 3 — Act II Until the Sun rises ☀️ @gianni_presutti from @dolcevita__rome @alexsilvestrimusic b2b @marenna.music @leolitterio 🏠 @hbtoo.official",
        url: "https://www.instagram.com/anima.ent/reel/DPwsmaJjZrw/",
        type: "reel"
      },
      {
        id: "DPmXRBPDZpQ",
        image: "https://scontent.cdninstagram.com/v/t51.82787-15/562537022_17938377252084668_1943177837770365742_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=102&ig_cache_key=MzczOTc3ODg1NzUxMjU5ODU0OA%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjE0NDB4MTgwMC5zZHIuQzMifQ%3D%3D&_nc_ohc=ZvH4jC2D-aIQ7kNvwHQquPo&_nc_oc=AdkqWwd0m0HBWbZnL2oXpX2MTCxTGgo9mSZtThiIBBomHyCDQ2GsJtY533Rk_jyO_8g&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=cFoy-ZzHmrTvyonaRMzYTA&oh=00_AfdU0ohKuA7_M-w8cud5rSsxtcdiGkjvc3Q-M6HsSd4j2w&oe=6900184F",
        alt: "Community guidelines ☀ Learn how could you be part of Anima Until the Sun rises ☀ See you tomorrow at 🏠 @hbtoo.official",
        url: "https://www.instagram.com/anima.ent/p/DPmXRBPDZpQ/",
        type: "carousel"
      },
      {
        id: "DPerq-sDTNO",
        image: "https://scontent.cdninstagram.com/v/t51.71878-15/561648270_1969327337249313_8473843443143903905_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=111&ig_cache_key=MzczNzYxNjgxMDI5MzE0NjQ0Ng%3D%3D.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjY0MHgxMTM2LnNkci5DMyJ9&_nc_ohc=2fPJWW5aJ6sQ7kNvwEAm-_X&_nc_oc=Adm4jnIQgIMQ291Y7IKiqQ9mmY6MNNc7F_VzDLLqhuziXMp9h2hwbrEA5X122fAv-iA&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=cFoy-ZzHmrTvyonaRMzYTA&oh=00_AfeMDrBSbIhyDp7CdNyj3NdDcSaORSPSao76yWteMU7FZw&oe=68FFF437",
        alt: "We are back. For the third year in a row, Anima is here to shine once again ☀️ Season 3 — Act I Until the Sun rises ☀️ Anima Dj Booth | A-Z order @alexsilvestrimusic @carlogiorgetto_ @hoodiamusic @manuelguida 🏠 @hbtoo.official",
        url: "https://www.instagram.com/anima.ent/reel/DPerq-sDTNO/",
        type: "reel"
      },
      {
        id: "DPUUmuLDe04",
        image: "https://scontent.cdninstagram.com/v/t51.82787-15/558981942_17937663765084668_2580632001886427385_n.jpg?stp=dst-jpg_e15_tt6&_nc_cat=109&ig_cache_key=MzczNDcwMDYxMjg0NDkwNzgzMjE3OTM3NjYzNzU5MDg0NjY4.3-ccb1-7&ccb=1-7&_nc_sid=58cdad&efg=eyJ2ZW5jb2RlX3RhZyI6InhwaWRzLjY2MngxMTc2LnNkci5DMyJ9&_nc_ohc=7wGDOhYe8ssQ7kNvwHmcj_z&_nc_oc=AdnU9p9PGrooUE7SO6Dd9qVHERSgMTRYsLH67RADPkyL3vaMZFuI9IQco4be3i9RLP0&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=cFoy-ZzHmrTvyonaRMzYTA&oh=00_AfeSHMYhwzsHzUZ1PpCUigDrT0F6prvdx8hlxr01ZeL4zw&oe=68FFEBAB",
        alt: "Season 3 begins. ANIMA — Act I The journey continues soon Until the Sun rises ☀️ 🏠 @hbtoo.official Directed & recorded by @panu.mov",
        url: "https://www.instagram.com/anima.ent/reel/DPUUmuLDe04/",
        type: "reel"
      }
    ]

    // Mostra solo i primi 9 post (3 righe da 3)
    const allPosts = extractedPosts.slice(0, 9)
    setPosts(allPosts)
    setLoading(false)
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

