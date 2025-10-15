"use client"

import { useState, useEffect } from "react"
import { Button } from "@/components/ui/button"
import { Instagram, Copy, Check, AlertCircle } from "lucide-react"

export default function InstagramConnectPage() {
  const [token, setToken] = useState<string | null>(null)
  const [copied, setCopied] = useState(false)
  const [error, setError] = useState<string | null>(null)

  // Instagram OAuth config - questi valori vengono dal .env.local
  const [appId, setAppId] = useState<string>("")
  
  useEffect(() => {
    // Fetch app config from env
    fetch('/api/instagram/config')
      .then(res => res.json())
      .then(data => {
        if (data.clientId) {
          setAppId(data.clientId)
        }
      })
      .catch(err => console.error('Failed to load Instagram config:', err))
  }, [])

  const REDIRECT_URI = typeof window !== 'undefined' 
    ? `${window.location.origin}/admin/instagram-connect/callback` 
    : ""
  
  useEffect(() => {
    // Check if there's a token in URL params (from callback)
    const params = new URLSearchParams(window.location.search)
    const tokenParam = params.get('token')
    const errorParam = params.get('error')
    
    if (tokenParam) {
      setToken(tokenParam)
      // Clean URL
      window.history.replaceState({}, '', '/admin/instagram-connect')
    }
    
    if (errorParam) {
      setError(errorParam)
    }
  }, [])

  const handleConnectInstagram = () => {
    if (!appId) {
      setError("App Instagram non configurata. Configura INSTAGRAM_CLIENT_ID in .env.local")
      return
    }
    
    const scope = 'user_profile,user_media'
    const authUrl = `https://api.instagram.com/oauth/authorize?client_id=${appId}&redirect_uri=${encodeURIComponent(REDIRECT_URI)}&scope=${scope}&response_type=code`
    
    // Open OAuth in same window
    window.location.href = authUrl
  }

  const copyToClipboard = async () => {
    if (token) {
      await navigator.clipboard.writeText(token)
      setCopied(true)
      setTimeout(() => setCopied(false), 2000)
    }
  }

  return (
    <div className="min-h-screen bg-black flex items-center justify-center p-4">
      <div className="max-w-2xl w-full bg-white rounded-lg shadow-2xl p-8 md:p-12">
        <div className="text-center mb-8">
          <div className="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-purple-600 via-pink-600 to-orange-600 rounded-2xl mb-4">
            <Instagram className="w-10 h-10 text-white" />
          </div>
          <h1 className="text-3xl md:text-4xl font-bold mb-2">
            Connetti Instagram
          </h1>
          <p className="text-gray-600">
            Account: <span className="font-semibold">@anima.ent</span>
          </p>
        </div>

        {!token && !error && (
          <div className="space-y-6">
            <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <h3 className="font-semibold text-blue-900 mb-2">📋 Prima di iniziare:</h3>
              <ul className="text-sm text-blue-800 space-y-1">
                <li>✅ Assicurati che @anima.ent sia un <strong>Account Business</strong></li>
                <li>✅ Avrai bisogno delle credenziali di @anima.ent</li>
                <li>✅ Il processo richiede 30 secondi</li>
              </ul>
            </div>

            <div className="space-y-4">
              <h3 className="font-semibold text-lg">Come funziona:</h3>
              <ol className="space-y-3 text-gray-700">
                <li className="flex gap-3">
                  <span className="flex-shrink-0 w-6 h-6 bg-red-600 text-white rounded-full flex items-center justify-center text-sm font-bold">1</span>
                  <span>Clicca il pulsante "Connetti Instagram" qui sotto</span>
                </li>
                <li className="flex gap-3">
                  <span className="flex-shrink-0 w-6 h-6 bg-red-600 text-white rounded-full flex items-center justify-center text-sm font-bold">2</span>
                  <span>Accedi con le credenziali di <strong>@anima.ent</strong></span>
                </li>
                <li className="flex gap-3">
                  <span className="flex-shrink-0 w-6 h-6 bg-red-600 text-white rounded-full flex items-center justify-center text-sm font-bold">3</span>
                  <span>Autorizza l'accesso quando richiesto</span>
                </li>
                <li className="flex gap-3">
                  <span className="flex-shrink-0 w-6 h-6 bg-red-600 text-white rounded-full flex items-center justify-center text-sm font-bold">4</span>
                  <span>Il token verrà generato automaticamente</span>
                </li>
              </ol>
            </div>

            <Button
              onClick={handleConnectInstagram}
              className="w-full bg-gradient-to-r from-purple-600 via-pink-600 to-orange-600 hover:from-purple-700 hover:via-pink-700 hover:to-orange-700 text-white py-6 text-lg font-bold"
              size="lg"
            >
              <Instagram className="w-6 h-6 mr-2" />
              Connetti Instagram
            </Button>

            <p className="text-xs text-gray-500 text-center">
              Verrai reindirizzato a Instagram per autorizzare l'accesso
            </p>
          </div>
        )}

        {error && (
          <div className="bg-red-50 border border-red-200 rounded-lg p-6">
            <div className="flex items-start gap-3">
              <AlertCircle className="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" />
              <div>
                <h3 className="font-semibold text-red-900 mb-1">Errore</h3>
                <p className="text-red-700 text-sm mb-4">{error}</p>
                <Button
                  onClick={() => {
                    setError(null)
                    window.location.href = '/admin/instagram-connect'
                  }}
                  variant="outline"
                  className="border-red-300 text-red-700 hover:bg-red-50"
                >
                  Riprova
                </Button>
              </div>
            </div>
          </div>
        )}

        {token && (
          <div className="space-y-6">
            <div className="bg-green-50 border border-green-200 rounded-lg p-6">
              <div className="flex items-start gap-3 mb-4">
                <Check className="w-6 h-6 text-green-600 flex-shrink-0" />
                <div>
                  <h3 className="font-semibold text-green-900 mb-1">✅ Token Generato!</h3>
                  <p className="text-green-700 text-sm">
                    Il feed Instagram di @anima.ent è ora connesso
                  </p>
                </div>
              </div>

              <div className="bg-white rounded-lg p-4 border border-green-200">
                <label className="text-xs font-semibold text-gray-700 mb-2 block">
                  ACCESS TOKEN:
                </label>
                <div className="flex gap-2">
                  <code className="flex-1 bg-gray-50 px-3 py-2 rounded text-xs break-all border border-gray-200 font-mono">
                    {token}
                  </code>
                  <Button
                    onClick={copyToClipboard}
                    variant="outline"
                    size="sm"
                    className="flex-shrink-0"
                  >
                    {copied ? (
                      <Check className="w-4 h-4 text-green-600" />
                    ) : (
                      <Copy className="w-4 h-4" />
                    )}
                  </Button>
                </div>
              </div>
            </div>

            <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
              <h3 className="font-semibold text-yellow-900 mb-2">⚠️ Importante:</h3>
              <ul className="text-sm text-yellow-800 space-y-1">
                <li>• Il token è già configurato nel sistema</li>
                <li>• Dura <strong>60 giorni</strong></li>
                <li>• Riceverai un promemoria quando scade</li>
                <li>• Non condividerlo con nessuno</li>
              </ul>
            </div>

            <div className="flex gap-3">
              <Button
                onClick={() => window.location.href = '/'}
                className="flex-1 bg-red-600 hover:bg-red-700 text-white"
              >
                Vai al Sito
              </Button>
              <Button
                onClick={() => {
                  setToken(null)
                  window.location.href = '/admin/instagram-connect'
                }}
                variant="outline"
                className="flex-1"
              >
                Genera Nuovo Token
              </Button>
            </div>
          </div>
        )}

        <div className="mt-8 pt-6 border-t border-gray-200">
          <p className="text-xs text-gray-500 text-center">
            🔒 Questa pagina è sicura. Il token viene salvato automaticamente in modo sicuro.
          </p>
        </div>
      </div>
    </div>
  )
}

