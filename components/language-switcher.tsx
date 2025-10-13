"use client"

import { useLanguage } from "@/contexts/LanguageContext"
import { Globe } from "lucide-react"

export function LanguageSwitcher() {
  const { language, setLanguage } = useLanguage()

  return (
    <div className="fixed bottom-6 right-6 z-50">
      <div className="bg-black/80 backdrop-blur-sm rounded-full p-2 shadow-lg border border-white/20">
        <div className="flex items-center gap-2">
          <Globe className="text-white w-5 h-5 ml-2" />
          <button
            onClick={() => setLanguage('en')}
            className={`px-3 py-1.5 rounded-full text-sm font-bold transition-all ${
              language === 'en'
                ? 'bg-primary text-white'
                : 'text-white/60 hover:text-white'
            }`}
          >
            EN
          </button>
          <button
            onClick={() => setLanguage('it')}
            className={`px-3 py-1.5 rounded-full text-sm font-bold transition-all ${
              language === 'it'
                ? 'bg-primary text-white'
                : 'text-white/60 hover:text-white'
            }`}
          >
            IT
          </button>
        </div>
      </div>
    </div>
  )
}


