"use client"

import { useState } from "react"
import { X } from "lucide-react"
import { useLanguage } from "@/contexts/LanguageContext"

interface PrivacyPolicyModalProps {
  isOpen: boolean
  onClose: () => void
}

export function PrivacyPolicyModal({ isOpen, onClose }: PrivacyPolicyModalProps) {
  const { t, language } = useLanguage()

  if (!isOpen) return null

  const content = {
    en: {
      title: "Privacy Policy",
      lastUpdated: "Last updated: December 2024",
      sections: [
        {
          title: "Information We Collect",
          content: "We collect information you provide directly to us, such as when you contact us via email or WhatsApp, including your name, email address, and phone number."
        },
        {
          title: "How We Use Your Information",
          content: "We use the information we collect to communicate with you about our events, provide customer support, and improve our services."
        },
        {
          title: "Information Sharing",
          content: "We do not sell, trade, or otherwise transfer your personal information to third parties without your consent, except as described in this policy."
        },
        {
          title: "Data Security",
          content: "We implement appropriate security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction."
        },
        {
          title: "Contact Us",
          content: "If you have any questions about this Privacy Policy, please contact us at info.animaent@gmail.com or +39 338 904 0714."
        }
      ]
    },
    it: {
      title: "Informativa sulla Privacy",
      lastUpdated: "Ultimo aggiornamento: Dicembre 2024",
      sections: [
        {
          title: "Informazioni che Raccogliamo",
          content: "Raccogliamo le informazioni che ci fornisci direttamente, ad esempio quando ci contatti via email o WhatsApp, inclusi nome, indirizzo email e numero di telefono."
        },
        {
          title: "Come Utilizziamo le Tue Informazioni",
          content: "Utilizziamo le informazioni raccolte per comunicare con te sui nostri eventi, fornire supporto clienti e migliorare i nostri servizi."
        },
        {
          title: "Condivisione delle Informazioni",
          content: "Non vendiamo, scambiamo o trasferiamo le tue informazioni personali a terze parti senza il tuo consenso, eccetto quanto descritto in questa informativa."
        },
        {
          title: "Sicurezza dei Dati",
          content: "Implementiamo misure di sicurezza appropriate per proteggere le tue informazioni personali da accessi non autorizzati, alterazioni, divulgazioni o distruzioni."
        },
        {
          title: "Contattaci",
          content: "Se hai domande su questa Informativa sulla Privacy, contattaci a info.animaent@gmail.com o +39 338 904 0714."
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
