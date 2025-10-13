"use client"

import { useState } from "react"
import { X } from "lucide-react"
import { useLanguage } from "@/contexts/LanguageContext"

interface CookiePolicyModalProps {
  isOpen: boolean
  onClose: () => void
}

export function CookiePolicyModal({ isOpen, onClose }: CookiePolicyModalProps) {
  const { t, language } = useLanguage()

  if (!isOpen) return null

  const content = {
    en: {
      title: "Cookie Policy",
      lastUpdated: "Last updated: December 2024",
      sections: [
        {
          title: "What Are Cookies",
          content: "Cookies are small text files that are placed on your computer or mobile device when you visit our website. They help us provide you with a better experience by remembering your preferences and analyzing how you use our site."
        },
        {
          title: "Types of Cookies We Use",
          content: "We use essential cookies that are necessary for the website to function properly, and analytics cookies to understand how visitors interact with our website."
        },
        {
          title: "Managing Cookies",
          content: "You can control and manage cookies through your browser settings. However, disabling certain cookies may affect the functionality of our website."
        },
        {
          title: "Third-Party Cookies",
          content: "We may use third-party services like Google Analytics that set their own cookies. These help us understand website traffic and user behavior."
        },
        {
          title: "Contact Us",
          content: "If you have any questions about our use of cookies, please contact us at info.animaent@gmail.com or +39 338 904 0714."
        }
      ]
    },
    it: {
      title: "Informativa sui Cookie",
      lastUpdated: "Ultimo aggiornamento: Dicembre 2024",
      sections: [
        {
          title: "Cosa Sono i Cookie",
          content: "I cookie sono piccoli file di testo che vengono memorizzati sul tuo computer o dispositivo mobile quando visiti il nostro sito web. Ci aiutano a fornirti un'esperienza migliore ricordando le tue preferenze e analizzando come utilizzi il nostro sito."
        },
        {
          title: "Tipi di Cookie che Utilizziamo",
          content: "Utilizziamo cookie essenziali necessari per il corretto funzionamento del sito web e cookie di analisi per capire come i visitatori interagiscono con il nostro sito."
        },
        {
          title: "Gestione dei Cookie",
          content: "Puoi controllare e gestire i cookie attraverso le impostazioni del tuo browser. Tuttavia, disabilitare alcuni cookie potrebbe influire sulla funzionalità del nostro sito web."
        },
        {
          title: "Cookie di Terze Parti",
          content: "Potremmo utilizzare servizi di terze parti come Google Analytics che impostano i propri cookie. Questi ci aiutano a comprendere il traffico del sito web e il comportamento degli utenti."
        },
        {
          title: "Contattaci",
          content: "Se hai domande sull'uso dei cookie, contattaci a info.animaent@gmail.com o +39 338 904 0714."
        }
      ]
    }
  }

  const currentContent = content[language]

  return (
    <div className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div className="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div className="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
          <h2 className="text-2xl font-bold text-black">{currentContent.title}</h2>
          <button
            onClick={onClose}
            className="text-gray-500 hover:text-gray-700 transition-colors"
          >
            <X size={24} />
          </button>
        </div>
        
        <div className="p-6 space-y-6">
          <p className="text-sm text-gray-600">{currentContent.lastUpdated}</p>
          
          {currentContent.sections.map((section, index) => (
            <div key={index}>
              <h3 className="text-lg font-semibold text-black mb-2">{section.title}</h3>
              <p className="text-gray-700 leading-relaxed">{section.content}</p>
            </div>
          ))}
        </div>
      </div>
    </div>
  )
}
