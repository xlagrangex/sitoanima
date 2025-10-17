"use client"

import { useState, useEffect, useRef } from "react"
import { Play, Pause, SkipForward, SkipBack, Volume2 } from "lucide-react"
import { motion } from "framer-motion"

interface Track {
  id: string
  name: string
  artists: Array<{ name: string }>
  album: {
    name: string
    images: Array<{ url: string; width: number; height: number }>
  }
  preview_url: string | null
  external_urls: {
    spotify: string
  }
  duration_ms: number
}

interface SpotifyPlayerProps {
  tracks: Track[]
  currentTrackIndex: number
  onTrackChange: (index: number) => void
}

export function SpotifyPlayer({ tracks, currentTrackIndex, onTrackChange }: SpotifyPlayerProps) {
  const [isPlaying, setIsPlaying] = useState(false)
  const [currentTime, setCurrentTime] = useState(0)
  const [duration, setDuration] = useState(30) // Max 30 seconds for preview
  const [volume, setVolume] = useState(0.7)
  const audioRef = useRef<HTMLAudioElement>(null)
  const currentTrack = tracks[currentTrackIndex]

  useEffect(() => {
    const audio = audioRef.current
    if (!audio) return

    const updateTime = () => setCurrentTime(audio.currentTime)
    const updateDuration = () => setDuration(Math.min(audio.duration || 30, 30))
    const handleEnded = () => {
      setIsPlaying(false)
      setCurrentTime(0)
    }

    audio.addEventListener('timeupdate', updateTime)
    audio.addEventListener('loadedmetadata', updateDuration)
    audio.addEventListener('ended', handleEnded)

    return () => {
      audio.removeEventListener('timeupdate', updateTime)
      audio.removeEventListener('loadedmetadata', updateDuration)
      audio.removeEventListener('ended', handleEnded)
    }
  }, [currentTrackIndex])

  useEffect(() => {
    const audio = audioRef.current
    if (!audio || !currentTrack?.preview_url) return

    audio.src = currentTrack.preview_url
    audio.load()
    setCurrentTime(0)
    
    if (isPlaying) {
      audio.play().catch(console.error)
    }
  }, [currentTrackIndex, currentTrack])

  useEffect(() => {
    const audio = audioRef.current
    if (!audio) return

    audio.volume = volume
  }, [volume])

  const togglePlay = () => {
    const audio = audioRef.current
    if (!audio || !currentTrack?.preview_url) return

    if (isPlaying) {
      audio.pause()
      setIsPlaying(false)
    } else {
      audio.play().catch(console.error)
      setIsPlaying(true)
    }
  }

  const nextTrack = () => {
    const nextIndex = (currentTrackIndex + 1) % tracks.length
    onTrackChange(nextIndex)
  }

  const prevTrack = () => {
    const prevIndex = (currentTrackIndex - 1 + tracks.length) % tracks.length
    onTrackChange(prevIndex)
  }

  const handleTimeChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const audio = audioRef.current
    if (!audio) return

    const newTime = parseFloat(e.target.value)
    audio.currentTime = newTime
    setCurrentTime(newTime)
  }

  const formatTime = (time: number) => {
    const minutes = Math.floor(time / 60)
    const seconds = Math.floor(time % 60)
    return `${minutes}:${seconds.toString().padStart(2, '0')}`
  }

  if (!currentTrack) return null

  return (
    <div className="bg-gradient-to-br from-green-600 via-green-500 to-emerald-600 rounded-2xl p-8 md:p-12 text-center overflow-hidden">
      <audio ref={audioRef} preload="metadata" />
      
      {/* Album Art */}
      <div className="mb-8">
        {currentTrack.album.images[0] && (
          <motion.img
            key={currentTrack.id}
            src={currentTrack.album.images[0].url}
            alt={currentTrack.album.name}
            className="w-48 h-48 md:w-64 md:h-64 mx-auto rounded-2xl shadow-2xl object-cover"
            initial={{ opacity: 0, scale: 0.9 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.5 }}
          />
        )}
      </div>

      {/* Track Info */}
      <div className="mb-8">
        <motion.h4 
          key={`${currentTrack.id}-title`}
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.5 }}
          className="text-3xl md:text-5xl font-bold text-white mb-4"
        >
          {currentTrack.name}
        </motion.h4>
        <motion.p 
          key={`${currentTrack.id}-artist`}
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.5, delay: 0.1 }}
          className="text-green-100 text-lg md:text-2xl mb-2"
        >
          {currentTrack.artists.map(artist => artist.name).join(', ')}
        </motion.p>
        <motion.p 
          key={`${currentTrack.id}-album`}
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.5, delay: 0.2 }}
          className="text-green-200 text-sm md:text-lg"
        >
          {currentTrack.album.name}
        </motion.p>
      </div>

      {/* Progress Bar */}
      {currentTrack.preview_url && (
        <div className="mb-6">
          <div className="flex justify-between text-sm text-green-100 mb-2">
            <span>{formatTime(currentTime)}</span>
            <span>{formatTime(duration)}</span>
          </div>
          <input
            type="range"
            min="0"
            max={duration}
            value={currentTime}
            onChange={handleTimeChange}
            className="w-full h-2 bg-green-300 rounded-lg appearance-none cursor-pointer slider"
            style={{
              background: `linear-gradient(to right, white 0%, white ${(currentTime / duration) * 100}%, rgba(255,255,255,0.3) ${(currentTime / duration) * 100}%, rgba(255,255,255,0.3) 100%)`
            }}
          />
        </div>
      )}

      {/* Controls */}
      <div className="flex items-center justify-center space-x-4 mb-6">
        <motion.button
          onClick={prevTrack}
          className="bg-white/20 backdrop-blur-sm text-white rounded-full p-3 hover:bg-white/30 transition-all"
          whileHover={{ scale: 1.1 }}
          whileTap={{ scale: 0.95 }}
        >
          <SkipBack size={24} />
        </motion.button>

        <motion.button
          onClick={togglePlay}
          disabled={!currentTrack.preview_url}
          className="bg-white text-green-600 rounded-full p-4 shadow-2xl hover:scale-110 transition-transform disabled:opacity-50 disabled:cursor-not-allowed"
          whileHover={{ scale: 1.05 }}
          whileTap={{ scale: 0.95 }}
        >
          {isPlaying ? <Pause size={32} /> : <Play size={32} />}
        </motion.button>

        <motion.button
          onClick={nextTrack}
          className="bg-white/20 backdrop-blur-sm text-white rounded-full p-3 hover:bg-white/30 transition-all"
          whileHover={{ scale: 1.1 }}
          whileTap={{ scale: 0.95 }}
        >
          <SkipForward size={24} />
        </motion.button>
      </div>

      {/* Volume Control */}
      <div className="flex items-center justify-center space-x-3 mb-6">
        <Volume2 size={20} className="text-white" />
        <input
          type="range"
          min="0"
          max="1"
          step="0.1"
          value={volume}
          onChange={(e) => setVolume(parseFloat(e.target.value))}
          className="w-24 h-1 bg-green-300 rounded-lg appearance-none cursor-pointer"
        />
      </div>

      {/* Spotify Link */}
      <motion.a
        href={currentTrack.external_urls.spotify}
        target="_blank"
        rel="noopener noreferrer"
        className="inline-flex items-center space-x-2 text-white/80 hover:text-white transition-colors text-sm"
        whileHover={{ scale: 1.05 }}
      >
        <svg className="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.42 1.56-.299.421-1.02.599-1.559.3z"/>
        </svg>
        <span>Ascolta su Spotify</span>
      </motion.a>

      {!currentTrack.preview_url && (
        <div className="mt-4 p-3 bg-white/20 rounded-lg">
          <p className="text-white/80 text-sm">
            Preview non disponibile per questo brano
          </p>
        </div>
      )}
    </div>
  )
}




