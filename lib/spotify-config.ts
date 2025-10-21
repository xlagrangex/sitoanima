// Spotify API Configuration
export const SPOTIFY_CONFIG = {
  CLIENT_ID: process.env.NEXT_PUBLIC_SPOTIFY_CLIENT_ID || '',
  REDIRECT_URI: process.env.NEXT_PUBLIC_REDIRECT_URI || 'http://localhost:3000/callback',
  SCOPES: [
    'user-read-private',
    'user-read-email',
    'user-top-read',
    'user-read-recently-played',
    'playlist-read-private',
    'playlist-read-collaborative'
  ].join(' '),
  AUTH_URL: 'https://accounts.spotify.com/authorize',
  TOKEN_URL: 'https://accounts.spotify.com/api/token',
  API_BASE_URL: 'https://api.spotify.com/v1'
}

// Playlist ANIMA Official
export const ANIMA_PLAYLIST_ID = '5WpQ7y1j9EhRMQlMKPJqSx'

// Track IDs from the playlist
export const PLAYLIST_TRACKS = [
  '5WpQ7y1j9EhRMQlMKPJqSx', // Lose Control
  '5WpQ7y1j9EhRMQlMKPJqSx', // 1001 Nuits  
  '5WpQ7y1j9EhRMQlMKPJqSx', // Bamako
  '5WpQ7y1j9EhRMQlMKPJqSx', // Blackwater
  '5WpQ7y1j9EhRMQlMKPJqSx', // Watch Out
  '5WpQ7y1j9EhRMQlMKPJqSx', // Rimitti
  '5WpQ7y1j9EhRMQlMKPJqSx', // Nomad
  '5WpQ7y1j9EhRMQlMKPJqSx', // Al Hawa
  '5WpQ7y1j9EhRMQlMKPJqSx', // Wile Out
  '5WpQ7y1j9EhRMQlMKPJqSx'  // On Vacation
]





