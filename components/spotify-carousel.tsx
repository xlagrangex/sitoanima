"use client"

import { useState, useEffect } from "react"
import { ChevronLeft, ChevronRight, ExternalLink } from "lucide-react"
import { motion } from "framer-motion"

interface Track {
  id: string
  title: string
  artist: string
  spotifyUrl: string
}

export function SpotifyCarousel() {
  const [currentIndex, setCurrentIndex] = useState(0)

  const tracks: Track[] = [
    {
      id: "1",
      title: "Lose Control",
      artist: "Alex Silvestri, Fabio Stingo, Cristina Manzo",
      spotifyUrl: "https://open.spotify.com/track/5WpQ7y1j9EhRMQlMKPJqSx"
    },
    {
      id: "2", 
      title: "1001 Nuits",
      artist: "GROSSOMODDO",
      spotifyUrl: "https://open.spotify.com/track/5WpQ7y1j9EhRMQlMKPJqSx"
    },
    {
      id: "3",
      title: "Bamako", 
      artist: "PEATY",
      spotifyUrl: "https://open.spotify.com/track/5WpQ7y1j9EhRMQlMKPJqSx"
    },
    {
      id: "4",
      title: "Blackwater",
      artist: "Octave One",
      spotifyUrl: "https://open.spotify.com/track/5WpQ7y1j9EhRMQlMKPJqSx"
    },
    {
      id: "5",
      title: "Watch Out",
      artist: "Tony Romera, Crusy, Low Steppa",
      spotifyUrl: "https://open.spotify.com/track/5WpQ7y1j9EhRMQlMKPJqSx"
    },
    {
      id: "6",
      title: "Rimitti",
      artist: "Maesic, Samson",
      spotifyUrl: "https://open.spotify.com/track/5WpQ7y1j9EhRMQlMKPJqSx"
    },
    {
      id: "7",
      title: "Nomad",
      artist: "GROSSOMODDO",
      spotifyUrl: "https://open.spotify.com/track/5WpQ7y1j9EhRMQlMKPJqSx"
    },
    {
      id: "8",
      title: "Al Hawa", 
      artist: "Eran Hersh, Mili, DiMO (BG)",
      spotifyUrl: "https://open.spotify.com/track/5WpQ7y1j9EhRMQlMKPJqSx"
    },
    {
      id: "9",
      title: "Wile Out",
      artist: "Bontan, Surya Sen",
      spotifyUrl: "https://open.spotify.com/track/5WpQ7y1j9EhRMQlMKPJqSx"
    },
    {
      id: "10",
      title: "On Vacation",
      artist: "Re.You, Samm (BE)",
      spotifyUrl: "https://open.spotify.com/track/5WpQ7y1j9EhRMQlMKPJqSx"
    }
  ]

  const nextTrack = () => {
    setCurrentIndex((prev) => (prev + 1) % tracks.length)
  }

  const prevTrack = () => {
    setCurrentIndex((prev) => (prev - 1 + tracks.length) % tracks.length)
  }

  const currentTrack = tracks[currentIndex]

  return (
    <div className="w-full max-w-4xl mx-auto">
      {/* Main Carousel */}
      <div className="relative">
        <div className="bg-gradient-to-br from-green-600 via-green-500 to-emerald-600 rounded-2xl p-8 md:p-12 text-center overflow-hidden">
          {/* Track Info */}
          <div className="mb-8">
            <motion.h4 
              key={currentTrack.id}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.5 }}
              className="text-3xl md:text-5xl font-bold text-white mb-4"
            >
              {currentTrack.title}
            </motion.h4>
            <motion.p 
              key={`${currentTrack.id}-artist`}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.5, delay: 0.1 }}
              className="text-green-100 text-lg md:text-2xl"
            >
              {currentTrack.artist}
            </motion.p>
          </div>

          {/* Spotify Link Button */}
          <motion.a
            href="https://open.spotify.com/playlist/5WpQ7y1j9EhRMQlMKPJqSx?si=aa8b855a6bdc432e"
            target="_blank"
            rel="noopener noreferrer"
            className="inline-flex items-center space-x-3 bg-white text-green-600 px-8 py-4 rounded-full font-semibold text-lg shadow-2xl hover:scale-105 transition-transform"
            whileHover={{ scale: 1.05 }}
            whileTap={{ scale: 0.95 }}
          >
            <svg className="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.42 1.56-.299.421-1.02.599-1.559.3z"/>
            </svg>
            <span>Ascolta su Spotify</span>
            <ExternalLink size={20} />
          </motion.a>
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
                }`}>{track.title}</p>
                <p className="text-gray-500 text-sm truncate">{track.artist}</p>
              </div>
            </div>
          </motion.div>
        ))}
      </div>
    </div>
  )
}
