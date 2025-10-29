"use client"

import { Instagram, Mail, MapPin, Phone } from "lucide-react"
import { useLanguage } from "@/contexts/LanguageContext"
import Image from "next/image"
import { useState } from "react"
import { PrivacyPolicyModal } from "./privacy-policy-modal"
import { CookiePolicyModal } from "./cookie-policy-modal"
import { CreditsFooter } from "./credits-footer"

export function SiteFooter() {
  const { t } = useLanguage()
  const [isPrivacyOpen, setIsPrivacyOpen] = useState(false)
  const [isCookieOpen, setIsCookieOpen] = useState(false)
  
  const scrollToElement = (id: string) => {
    const element = document.getElementById(id)
    if (!element) return
    
    // Use instant scroll on mobile to prevent scroll jumping/bugging
    const isMobile = window.innerWidth < 768
    element.scrollIntoView({ behavior: isMobile ? "auto" : "smooth" })
  }
  
  return (
    <>
      <footer className="bg-black text-white py-16">
      <div className="container mx-auto px-4">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
          {/* Brand */}
          <div className="md:col-span-2">
            <div className="mb-6">
              <Image 
                src="/anima-extended-white.webp" 
                alt="ANIMA" 
                width={200} 
                height={60} 
                className="h-12 w-auto object-contain"
              />
            </div>
            <p className="text-lg text-white/80 mb-4">{t('footer.tagline')}</p>
            <p className="text-white/60 max-w-md">
              {t('footer.description')}
            </p>
          </div>

          {/* Quick Links */}
          <div>
            <h4 className="font-sequel text-sm font-black mb-4 uppercase tracking-[0.15em]" style={{ wordSpacing: '0.4em' }}>{t('footer.quicklinks')}</h4>
            <ul className="space-y-2">
              <li>
                <button
                  onClick={() => scrollToElement("format")}
                  className="text-white/60 hover:text-white transition-colors"
                >
                  Format
                </button>
              </li>
              <li>
                <button
                  onClick={() => scrollToElement("venue")}
                  className="text-white/60 hover:text-white transition-colors"
                >
                  Venue
                </button>
              </li>
              <li>
                <button
                  onClick={() => scrollToElement("shows")}
                  className="text-white/60 hover:text-white transition-colors"
                >
                  Upcoming Shows
                </button>
              </li>
              <li>
                <button
                  onClick={() => scrollToElement("media")}
                  className="text-white/60 hover:text-white transition-colors"
                >
                  Media Gallery
                </button>
              </li>
            </ul>
          </div>

          {/* Contact Info */}
          <div>
            <h4 className="font-sequel text-sm font-black mb-4 uppercase tracking-[0.15em]" style={{ wordSpacing: '0.4em' }}>{t('footer.contact')}</h4>
            <div className="space-y-3">
              <div className="flex items-center space-x-3">
                <Mail size={16} className="text-primary" />
                <a href="mailto:info.animaent@gmail.com" className="text-white/60 hover:text-white transition-colors text-sm">
                  info.animaent@gmail.com
                </a>
              </div>
              <div className="flex items-center space-x-3">
                <Phone size={16} className="text-primary" />
                <a href="https://wa.me/393389040714" className="text-white/60 hover:text-white transition-colors">
                  +39 338 904 0714
                </a>
              </div>
              <div className="flex items-center space-x-3">
                <MapPin size={16} className="text-primary" />
                <span className="text-white/60">Napoli, Italia</span>
              </div>
            </div>
          </div>
        </div>

        {/* Social Media & Copyright */}
        <div className="border-t border-white/10 mt-12 pt-8">
          <div className="text-center mb-6">
            <p className="text-white/80 mb-4">{t('footer.social')}</p>
            <div className="flex items-center justify-center space-x-6">
              <a href="https://instagram.com/anima.ent" target="_blank" rel="noopener noreferrer" className="text-white/60 hover:text-primary transition-colors" aria-label="Instagram">
                <Instagram size={28} />
              </a>
              <a href="https://t.me/UNTILTHESUNRISES" target="_blank" rel="noopener noreferrer" className="text-white/60 hover:text-primary transition-colors" aria-label="Telegram">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.161c-.18 1.897-.962 6.502-1.359 8.627-.168.9-.5 1.201-.82 1.23-.697.064-1.226-.461-1.901-.903-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.139-5.062 3.345-.479.329-.913.489-1.302.481-.428-.008-1.252-.241-1.865-.44-.752-.244-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.831-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635.099-.002.321.023.465.141.121.1.155.234.171.339.016.105.036.344.02.531z"/>
                </svg>
              </a>
            </div>
          </div>
          
          <div className="flex flex-col md:flex-row justify-between items-center border-t border-white/10 pt-6">
            <div className="text-center md:text-left mb-4 md:mb-0">
              <p className="text-white/60 text-sm">{t('footer.rights')}</p>
            </div>
            <div className="flex items-center space-x-4">
              <button
                onClick={() => setIsPrivacyOpen(true)}
                className="text-white/40 hover:text-white/60 transition-colors text-xs"
              >
                Privacy Policy
              </button>
              <span className="text-white/20">•</span>
              <button
                onClick={() => setIsCookieOpen(true)}
                className="text-white/40 hover:text-white/60 transition-colors text-xs"
              >
                Cookie Policy
              </button>
            </div>
          </div>
        </div>
      </div>
    </footer>

    {/* Credits Footer */}
    <CreditsFooter />

    {/* Modals */}
    <PrivacyPolicyModal 
      isOpen={isPrivacyOpen} 
      onClose={() => setIsPrivacyOpen(false)} 
    />
    <CookiePolicyModal 
      isOpen={isCookieOpen} 
      onClose={() => setIsCookieOpen(false)} 
    />
    </>
  )
}
