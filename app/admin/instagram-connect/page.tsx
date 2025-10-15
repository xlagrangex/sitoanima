"use client"

import { useState, useEffect } from "react"
import { Button } from "@/components/ui/button"
import { Instagram, Copy, Check, AlertCircle } from "lucide-react"

export default function InstagramConnectPage() {
  const [step, setStep] = useState<'initial' | 'waiting' | 'success'>('initial')
  const [tokenInput, setTokenInput] = useState('')
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState<string | null>(null)

  const handleGetToken = () => {
    // Apri il token generator in nuova finestra
    window.open(
      'https://instagram.tokenizer.app/',
      'Instagram Token Generator',
      'width=600,height=800,left=200,top=50'
    )
    
    // Mostra lo step "waiting" con il campo per incollare
    setStep('waiting')
  }

  const handleSaveToken = async () => {
    if (!tokenInput.trim()) {
      setError('Inserisci il token')
      return
    }

    setSaving(true)
    setError(null)

    try {
      const response = await fetch('/api/instagram/save-token', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ token: tokenInput.trim() }),
      })

      const data = await response.json()

      if (!response.ok) {
        setError(data.error || 'Errore nel salvataggio del token')
        setSaving(false)
        return
      }

      setStep('success')
      
      // Redirect dopo 2 secondi
      setTimeout(() => {
        window.location.href = '/'
      }, 2000)
      
    } catch (err) {
      setError('Errore di connessione')
      setSaving(false)
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

        {step === 'initial' && (
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
                  <span>Clicca "Ottieni Token" - si apre una nuova finestra</span>
                </li>
                <li className="flex gap-3">
                  <span className="flex-shrink-0 w-6 h-6 bg-red-600 text-white rounded-full flex items-center justify-center text-sm font-bold">2</span>
                  <span>Login con <strong>@anima.ent</strong> e autorizza</span>
                </li>
                <li className="flex gap-3">
                  <span className="flex-shrink-0 w-6 h-6 bg-red-600 text-white rounded-full flex items-center justify-center text-sm font-bold">3</span>
                  <span>Copia il token generato</span>
                </li>
                <li className="flex gap-3">
                  <span className="flex-shrink-0 w-6 h-6 bg-red-600 text-white rounded-full flex items-center justify-center text-sm font-bold">4</span>
                  <span>Incollalo qui sotto e clicca "Salva"</span>
                </li>
              </ol>
            </div>

            <Button
              onClick={handleGetToken}
              className="w-full bg-gradient-to-r from-purple-600 via-pink-600 to-orange-600 hover:from-purple-700 hover:via-pink-700 hover:to-orange-700 text-white py-6 text-lg font-bold"
              size="lg"
            >
              <Instagram className="w-6 h-6 mr-2" />
              Ottieni Token Instagram
            </Button>

            <p className="text-xs text-gray-500 text-center">
              Si aprirà una nuova finestra per autorizzare l'accesso
            </p>
          </div>
        )}

        {step === 'waiting' && (
          <div className="space-y-6">
            <div className="bg-green-50 border border-green-200 rounded-lg p-4">
              <h3 className="font-semibold text-green-900 mb-2">✅ Finestra aperta!</h3>
              <ol className="text-sm text-green-800 space-y-2">
                <li><strong>1.</strong> Nella nuova finestra, fai login con @anima.ent</li>
                <li><strong>2.</strong> Autorizza l'accesso</li>
                <li><strong>3.</strong> Copia il token che appare</li>
                <li><strong>4.</strong> Incollalo nel campo qui sotto</li>
              </ol>
            </div>

            <div className="space-y-3">
              <label className="block text-sm font-semibold text-gray-700">
                Incolla il Token qui:
              </label>
              <textarea
                value={tokenInput}
                onChange={(e) => setTokenInput(e.target.value)}
                placeholder="Incolla qui il token di Instagram..."
                className="w-full h-32 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent font-mono text-sm resize-none"
              />
              
              {error && (
                <div className="bg-red-50 border border-red-200 rounded-lg p-3 flex items-start gap-2">
                  <AlertCircle className="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" />
                  <p className="text-sm text-red-700">{error}</p>
                </div>
              )}

              <div className="flex gap-3">
                <Button
                  onClick={handleSaveToken}
                  disabled={saving || !tokenInput.trim()}
                  className="flex-1 bg-red-600 hover:bg-red-700 text-white py-4 text-lg font-bold"
                >
                  {saving ? 'Salvataggio...' : 'Salva Token'}
                </Button>
                <Button
                  onClick={() => setStep('initial')}
                  variant="outline"
                  className="px-6"
                  disabled={saving}
                >
                  Indietro
                </Button>
              </div>
            </div>
          </div>
        )}

        {step === 'success' && (
          <div className="space-y-6">
            <div className="bg-green-50 border border-green-200 rounded-lg p-6">
              <div className="flex items-center justify-center flex-col gap-4">
                <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                  <Check className="w-10 h-10 text-green-600" />
                </div>
                <div className="text-center">
                  <h3 className="text-2xl font-bold text-green-900 mb-2">✅ Connesso!</h3>
                  <p className="text-green-700">
                    Il feed Instagram di @anima.ent è ora attivo sul sito
                  </p>
                </div>
              </div>
            </div>

            <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <h3 className="font-semibold text-blue-900 mb-2">ℹ️ Informazioni:</h3>
              <ul className="text-sm text-blue-800 space-y-1">
                <li>• Il token è stato salvato in modo sicuro</li>
                <li>• Il feed mostrerà gli ultimi 9 post di @anima.ent</li>
                <li>• Validità: <strong>60 giorni</strong></li>
                <li>• Cache: 1 ora (per performance)</li>
              </ul>
            </div>

            <Button
              onClick={() => window.location.href = '/'}
              className="w-full bg-red-600 hover:bg-red-700 text-white py-4 text-lg font-bold"
            >
              Vai al Sito
            </Button>

            <p className="text-xs text-gray-500 text-center">
              Reindirizzamento automatico in 2 secondi...
            </p>
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

