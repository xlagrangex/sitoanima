"use client"

import { useState, useEffect, useRef } from "react"
import { ChevronLeft, ChevronRight, Play, Pause } from "lucide-react"
import { motion, AnimatePresence } from "framer-motion"

interface Track {
  id: string
  title: string
  artist: string
  spotifyId: string
  image: string
}

export function SpotifyCarousel() {
  const [currentIndex, setCurrentIndex] = useState(0)
  const [isPlaying, setIsPlaying] = useState(false)
  const [currentTrack, setCurrentTrack] = useState<Track | null>(null)
  const carouselRef = useRef<HTMLDivElement>(null)

  const tracks: Track[] = [
    {
      id: "1",
      title: "Lose Control",
      artist: "Alex Silvestri, Fabio Stingo, Cristina Manzo",
      spotifyId: "5WpQ7y1j9EhRMQlMKPJqSx",
      image: "/spotify-placeholder.jpg"
    },
    {
      id: "2", 
      title: "1001 Nuits",
      artist: "GROSSOMODDO",
      spotifyId: "5WpQ7y1j9EhRMQlMKPJqSx",
      image: "/spotify-placeholder.jpg"
    },
    {
      id: "3",
      title: "Bamako", 
      artist: "PEATY",
      spotifyId: "5WpQ7y1j9EhRMQlMKPJqSx",
      image: "/spotify-placeholder.jpg"
    },
    {
      id: "4",
      title: "Blackwater",
      artist: "Octave One",
      spotifyId: "5WpQ7y1j9EhRMQlMKPJqSx", 
      image: "/spotify-placeholder.jpg"
    },
    {
      id: "5",
      title: "Watch Out",
      artist: "Tony Romera, Crusy, Low Steppa",
      spotifyId: "5WpQ7y1j9EhRMQlMKPJqSx",
      image: "/spotify-placeholder.jpg"
    },
    {
      id: "6",
      title: "Rimitti",
      artist: "Maesic, Samson", 
      spotifyId: "5WpQ7y1j9EhRMQlMKPJqSx",
      image: "/spotify-placeholder.jpg"
    },
    {
      id: "7",
      title: "Nomad",
      artist: "GROSSOMODDO",
      spotifyId: "5WpQ7y1j9EhRMQlMKPJqSx",
      image: "/spotify-placeholder.jpg"
    },
    {
      id: "8",
      title: "Al Hawa", 
      artist: "Eran Hersh, Mili, DiMO (BG)",
      spotifyId: "5WpQ7y1j9EhRMQlMKPJqSx",
      image: "/spotify-placeholder.jpg"
    },
    {
      id: "9",
      title: "Wile Out",
      artist: "Bontan, Surya Sen",
      spotifyId: "5WpQ7y1j9EhRMQlMKPJqSx",
      image: "/spotify-placeholder.jpg"
    },
    {
      id: "10",
      title: "On Vacation",
      artist: "Re.You, Samm (BE)",
      spotifyId: "5WpQ7y1j9EhRMQlMKPJqSx",
      image: "/spotify-placeholder.jpg"
    }
  ]

  const nextTrack = () => {
    setCurrentIndex((prev) => (prev + 1) % tracks.length)
  }

  const prevTrack = () => {
    setCurrentIndex((prev) => (prev - 1 + tracks.length) % tracks.length)
  }

  const playTrack = (track: Track) => {
    setCurrentTrack(track)
    setIsPlaying(true)
  }

  const pauseTrack = () => {
    setIsPlaying(false)
  }

  useEffect(() => {
    const interval = setInterval(() => {
      if (isPlaying) {
        nextTrack()
      }
    }, 5000)

    return () => clearInterval(interval)
  }, [isPlaying])

  return (
    <div className="w-full max-w-6xl mx-auto">
      {/* Header */}
      <div className="text-center mb-8">
        <h3 className="text-2xl md:text-3xl font-bold text-white mb-2">
          ANIMA Official Playlist
        </h3>
        <p className="text-white/70 text-sm">
          {tracks.length} tracks • {tracks[currentIndex].artist}
        </p>
      </div>

      {/* Main Carousel */}
      <div className="relative">
        <div className="overflow-hidden rounded-2xl">
          <motion.div
            ref={carouselRef}
            className="flex"
            animate={{ x: -currentIndex * 100 + "%" }}
            transition={{ type: "spring", stiffness: 300, damping: 30 }}
          >
            {tracks.map((track, index) => (
              <div
                key={track.id}
                className="w-full flex-shrink-0 relative"
                style={{ minHeight: "400px" }}
              >
                <div className="bg-gradient-to-br from-green-600 via-green-500 to-emerald-600 p-8 h-full flex flex-col justify-center items-center text-center">
                  {/* Track Info */}
                  <div className="mb-6">
                    <h4 className="text-2xl md:text-4xl font-bold text-white mb-2">
                      {track.title}
                    </h4>
                    <p className="text-green-100 text-lg md:text-xl">
                      {track.artist}
                    </p>
                  </div>

                  {/* Spotify Widget */}
                  <div className="w-full max-w-md mb-6">
                    <iframe
                      src={`https://open.spotify.com/embed/track/${track.spotifyId}?utm_source=generator&theme=0`}
                      width="100%"
                      height="152"
                      frameBorder="0"
                      allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
                      loading="lazy"
                      className="rounded-lg shadow-2xl"
                    ></iframe>
                  </div>

                  {/* Play/Pause Button */}
                  <motion.button
                    onClick={() => isPlaying ? pauseTrack() : playTrack(track)}
                    className="bg-white text-green-600 rounded-full p-4 shadow-2xl hover:scale-110 transition-transform"
                    whileHover={{ scale: 1.1 }}
                    whileTap={{ scale: 0.95 }}
                  >
                    {isPlaying && currentTrack?.id === track.id ? (
                      <Pause size={32} />
                    ) : (
                      <Play size={32} />
                    )}
                  </motion.button>
                </div>
              </div>
            ))}
          </motion.div>
        </div>

        {/* Navigation Arrows */}
        <button
          onClick={prevTrack}
          className="absolute left-4 top-1/2 -translate-y-1/2 bg-white/20 backdrop-blur-sm text-white rounded-full p-3 hover:bg-white/30 transition-all"
        >
          <ChevronLeft size={24} />
        </button>
        
        <button
          onClick={nextTrack}
          className="absolute right-4 top-1/2 -translate-y-1/2 bg-white/20 backdrop-blur-sm text-white rounded-full p-3 hover:bg-white/30 transition-all"
        >
          <ChevronRight size={24} />
        </button>
      </div>

      {/* Track Indicators */}
      <div className="flex justify-center space-x-2 mt-6">
        {tracks.map((_, index) => (
          <button
            key={index}
            onClick={() => setCurrentIndex(index)}
            className={`w-2 h-2 rounded-full transition-all ${
              index === currentIndex ? "bg-green-500 w-8" : "bg-white/30"
            }`}
          />
        ))}
      </div>

      {/* Track List */}
      <div className="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-60 overflow-y-auto">
        {tracks.map((track, index) => (
          <motion.div
            key={track.id}
            className={`p-3 rounded-lg cursor-pointer transition-all ${
              index === currentIndex 
                ? "bg-green-500/20 border border-green-500/50" 
                : "bg-white/10 hover:bg-white/20"
            }`}
            onClick={() => setCurrentIndex(index)}
            whileHover={{ scale: 1.02 }}
            whileTap={{ scale: 0.98 }}
          >
            <div className="flex items-center space-x-3">
              <div className="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                {index + 1}
              </div>
              <div className="flex-1 min-w-0">
                <p className="text-white font-medium truncate">{track.title}</p>
                <p className="text-white/60 text-sm truncate">{track.artist}</p>
              </div>
            </div>
          </motion.div>
        ))}
      </div>
    </div>
  )
}
