"use client"

import { useState } from "react"
import { ChevronLeft, ChevronRight } from "lucide-react"
import { motion } from "framer-motion"
import { SpotifyPlayer } from "./spotify-player"
import { useSpotifyData } from "@/hooks/useSpotifyData"

export function SpotifyCarousel() {
  const [currentIndex, setCurrentIndex] = useState(0)
  const { tracks, loading, error } = useSpotifyData()

  const nextTrack = () => {
    setCurrentIndex((prev) => (prev + 1) % tracks.length)
  }

  const prevTrack = () => {
    setCurrentIndex((prev) => (prev - 1 + tracks.length) % tracks.length)
  }

  if (loading) {
    return (
      <div className="w-full max-w-4xl mx-auto">
        <div className="bg-gradient-to-br from-green-600 via-green-500 to-emerald-600 rounded-2xl p-8 md:p-12 text-center">
          <div className="animate-spin rounded-full h-16 w-16 border-b-2 border-white mx-auto mb-4"></div>
          <p className="text-white text-lg">Caricamento playlist...</p>
        </div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="w-full max-w-4xl mx-auto">
        <div className="bg-gradient-to-br from-red-600 via-red-500 to-red-400 rounded-2xl p-8 md:p-12 text-center">
          <p className="text-white text-lg">{error}</p>
        </div>
      </div>
    )
  }

  if (tracks.length === 0) {
    return (
      <div className="w-full max-w-4xl mx-auto">
        <div className="bg-gradient-to-br from-gray-600 via-gray-500 to-gray-400 rounded-2xl p-8 md:p-12 text-center">
          <p className="text-white text-lg">Nessun brano disponibile</p>
        </div>
      </div>
    )
  }

  return (
    <div className="w-full max-w-4xl mx-auto">
      {/* Main Player */}
      <div className="relative">
        <SpotifyPlayer 
          tracks={tracks}
          currentTrackIndex={currentIndex}
          onTrackChange={setCurrentIndex}
        />

        {/* Navigation Arrows */}
        <button
          onClick={prevTrack}
          className="absolute left-4 top-1/2 -translate-y-1/2 bg-white/20 backdrop-blur-sm text-white rounded-full p-3 hover:bg-white/30 transition-all z-10"
        >
          <ChevronLeft size={24} />
        </button>
        
        <button
          onClick={nextTrack}
          className="absolute right-4 top-1/2 -translate-y-1/2 bg-white/20 backdrop-blur-sm text-white rounded-full p-3 hover:bg-white/30 transition-all z-10"
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
              index === currentIndex ? "bg-green-500 w-8" : "bg-gray-300"
            }`}
          />
        ))}
      </div>

      {/* Track List */}
      <div className="mt-8 grid grid-cols-1 md:grid-cols-2 gap-3">
        {tracks.map((track, index) => (
          <motion.div
            key={track.id}
            className={`p-4 rounded-lg cursor-pointer transition-all ${
              index === currentIndex 
                ? "bg-green-500/20 border border-green-500/50" 
                : "bg-gray-100 hover:bg-gray-200"
            }`}
            onClick={() => setCurrentIndex(index)}
            whileHover={{ scale: 1.02 }}
            whileTap={{ scale: 0.98 }}
          >
            <div className="flex items-center space-x-3">
              <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold ${
                index === currentIndex ? "bg-green-500 text-white" : "bg-gray-300 text-gray-700"
              }`}>
                {index + 1}
              </div>
              <div className="flex-1 min-w-0">
                <p className={`font-medium truncate ${
                  index === currentIndex ? "text-green-700" : "text-gray-700"
                }`}>{track.name}</p>
                <p className="text-gray-500 text-sm truncate">
                  {track.artists.map(artist => artist.name).join(', ')}
                </p>
              </div>
            </div>
          </motion.div>
        ))}
      </div>
    </div>
  )
}
