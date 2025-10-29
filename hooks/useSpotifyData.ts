import { useState, useEffect } from 'react'
import { ANIMA_PLAYLIST_ID, PLAYLIST_TRACKS } from '@/lib/spotify-config'

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

// Mock data per ora - in futuro si può integrare con la vera Spotify API
const MOCK_TRACKS: Track[] = [
  {
    id: "1",
    name: "Lose Control",
    artists: [{ name: "Alex Silvestri, Fabio Stingo, Cristina Manzo" }],
    album: {
      name: "ANIMA Official Playlist",
      images: [{ url: "https://via.placeholder.com/640x640/1DB954/FFFFFF?text=Lose+Control", width: 640, height: 640 }]
    },
    preview_url: null, // Per ora senza preview
    external_urls: { spotify: "https://open.spotify.com/track/5WpQ7y1j9EhRMQlMKPJqSx" },
    duration_ms: 180000
  },
  {
    id: "2",
    name: "1001 Nuits",
    artists: [{ name: "GROSSOMODDO" }],
    album: {
      name: "ANIMA Official Playlist",
      images: [{ url: "https://via.placeholder.com/640x640/1DB954/FFFFFF?text=1001+Nuits", width: 640, height: 640 }]
    },
    preview_url: null,
    external_urls: { spotify: "https://open.spotify.com/track/5WpQ7y1j9EhRMQlMKPJqSx" },
    duration_ms: 200000
  },
  {
    id: "3",
    name: "Bamako",
    artists: [{ name: "PEATY" }],
    album: {
      name: "ANIMA Official Playlist",
      images: [{ url: "https://via.placeholder.com/640x640/1DB954/FFFFFF?text=Bamako", width: 640, height: 640 }]
    },
    preview_url: null,
    external_urls: { spotify: "https://open.spotify.com/track/5WpQ7y1j9EhRMQlMKPJqSx" },
    duration_ms: 190000
  },
  {
    id: "4",
    name: "Blackwater",
    artists: [{ name: "Octave One" }],
    album: {
      name: "ANIMA Official Playlist",
      images: [{ url: "https://via.placeholder.com/640x640/1DB954/FFFFFF?text=Blackwater", width: 640, height: 640 }]
    },
    preview_url: null,
    external_urls: { spotify: "https://open.spotify.com/track/5WpQ7y1j9EhRMQlMKPJqSx" },
    duration_ms: 210000
  },
  {
    id: "5",
    name: "Watch Out",
    artists: [{ name: "Tony Romera, Crusy, Low Steppa" }],
    album: {
      name: "ANIMA Official Playlist",
      images: [{ url: "https://via.placeholder.com/640x640/1DB954/FFFFFF?text=Watch+Out", width: 640, height: 640 }]
    },
    preview_url: null,
    external_urls: { spotify: "https://open.spotify.com/track/5WpQ7y1j9EhRMQlMKPJqSx" },
    duration_ms: 195000
  },
  {
    id: "6",
    name: "Rimitti",
    artists: [{ name: "Maesic, Samson" }],
    album: {
      name: "ANIMA Official Playlist",
      images: [{ url: "https://via.placeholder.com/640x640/1DB954/FFFFFF?text=Rimitti", width: 640, height: 640 }]
    },
    preview_url: null,
    external_urls: { spotify: "https://open.spotify.com/track/5WpQ7y1j9EhRMQlMKPJqSx" },
    duration_ms: 185000
  },
  {
    id: "7",
    name: "Nomad",
    artists: [{ name: "GROSSOMODDO" }],
    album: {
      name: "ANIMA Official Playlist",
      images: [{ url: "https://via.placeholder.com/640x640/1DB954/FFFFFF?text=Nomad", width: 640, height: 640 }]
    },
    preview_url: null,
    external_urls: { spotify: "https://open.spotify.com/track/5WpQ7y1j9EhRMQlMKPJqSx" },
    duration_ms: 205000
  },
  {
    id: "8",
    name: "Al Hawa",
    artists: [{ name: "Eran Hersh, Mili, DiMO (BG)" }],
    album: {
      name: "ANIMA Official Playlist",
      images: [{ url: "https://via.placeholder.com/640x640/1DB954/FFFFFF?text=Al+Hawa", width: 640, height: 640 }]
    },
    preview_url: null,
    external_urls: { spotify: "https://open.spotify.com/track/5WpQ7y1j9EhRMQlMKPJqSx" },
    duration_ms: 220000
  },
  {
    id: "9",
    name: "Wile Out",
    artists: [{ name: "Bontan, Surya Sen" }],
    album: {
      name: "ANIMA Official Playlist",
      images: [{ url: "https://via.placeholder.com/640x640/1DB954/FFFFFF?text=Wile+Out", width: 640, height: 640 }]
    },
    preview_url: null,
    external_urls: { spotify: "https://open.spotify.com/track/5WpQ7y1j9EhRMQlMKPJqSx" },
    duration_ms: 175000
  },
  {
    id: "10",
    name: "On Vacation",
    artists: [{ name: "Re.You, Samm (BE)" }],
    album: {
      name: "ANIMA Official Playlist",
      images: [{ url: "https://via.placeholder.com/640x640/1DB954/FFFFFF?text=On+Vacation", width: 640, height: 640 }]
    },
    preview_url: null,
    external_urls: { spotify: "https://open.spotify.com/track/5WpQ7y1j9EhRMQlMKPJqSx" },
    duration_ms: 200000
  }
]

export function useSpotifyData() {
  const [tracks, setTracks] = useState<Track[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    // Simula caricamento dati
    const loadTracks = async () => {
      try {
        setLoading(true)
        // Simula delay di caricamento
        await new Promise(resolve => setTimeout(resolve, 1000))
        setTracks(MOCK_TRACKS)
        setError(null)
      } catch (err) {
        setError('Errore nel caricamento dei brani')
        console.error('Error loading tracks:', err)
      } finally {
        setLoading(false)
      }
    }

    loadTracks()
  }, [])

  return { tracks, loading, error }
}










