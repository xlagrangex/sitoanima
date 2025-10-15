"use client"

import { useEffect, useState } from "react"
import { useRouter } from "next/navigation"
import { Loader2, Check, AlertCircle } from "lucide-react"
import { Button } from "@/components/ui/button"

export default function InstagramCallbackPage() {
  const [status, setStatus] = useState<'loading' | 'success' | 'error'>('loading')
  const [message, setMessage] = useState('')
  const router = useRouter()

  useEffect(() => {
    const handleCallback = async () => {
      try {
        const params = new URLSearchParams(window.location.search)
        const code = params.get('code')
        const error = params.get('error')
        const errorDescription = params.get('error_description')

        if (error) {
          setStatus('error')
          setMessage(errorDescription || error)
          return
        }

        if (!code) {
          setStatus('error')
          setMessage('Nessun codice di autorizzazione ricevuto')
          return
        }

        // Exchange code for token
        const response = await fetch('/api/instagram/exchange-token', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ code }),
        })

        const data = await response.json()

        if (!response.ok || data.error) {
          setStatus('error')
          setMessage(data.error || 'Errore durante lo scambio del token')
          return
        }

        setStatus('success')
        setMessage('Token salvato con successo!')
        
        // Redirect after 2 seconds
        setTimeout(() => {
          router.push('/')
        }, 2000)

      } catch (err) {
        setStatus('error')
        setMessage('Errore di connessione. Riprova.')
      }
    }

    handleCallback()
  }, [router])

  return (
    <div className="min-h-screen bg-black flex items-center justify-center p-4">
      <div className="max-w-md w-full bg-white rounded-lg shadow-2xl p-8">
        <div className="text-center">
          {status === 'loading' && (
            <>
              <Loader2 className="w-16 h-16 mx-auto mb-4 text-red-600 animate-spin" />
              <h1 className="text-2xl font-bold mb-2">Configurazione in corso...</h1>
              <p className="text-gray-600">Stiamo salvando il token Instagram</p>
            </>
          )}

          {status === 'success' && (
            <>
              <div className="w-16 h-16 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
                <Check className="w-10 h-10 text-green-600" />
              </div>
              <h1 className="text-2xl font-bold mb-2 text-green-900">Successo!</h1>
              <p className="text-gray-600">{message}</p>
              <p className="text-sm text-gray-500 mt-4">
                Reindirizzamento alla home page...
              </p>
            </>
          )}

          {status === 'error' && (
            <>
              <div className="w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                <AlertCircle className="w-10 h-10 text-red-600" />
              </div>
              <h1 className="text-2xl font-bold mb-2 text-red-900">Errore</h1>
              <p className="text-gray-600 mb-6">{message}</p>
              <Button
                onClick={() => router.push('/admin/instagram-connect')}
                className="bg-red-600 hover:bg-red-700 text-white"
              >
                Riprova
              </Button>
            </>
          )}
        </div>
      </div>
    </div>
  )
}

