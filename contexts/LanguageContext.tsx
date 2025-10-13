"use client"

import React, { createContext, useContext, useState, ReactNode } from 'react'

type Language = 'en' | 'it'

interface LanguageContextType {
  language: Language
  setLanguage: (lang: Language) => void
  t: (key: string) => string
}

const translations = {
  en: {
    // Hero
    'hero.tagline': 'Where house and afrohouse converge into a radiant journey.',
    'hero.tagline2': 'A club experience built around joy, light and freedom — designed to last until the sunrise.',
    'hero.cta': 'Discover the Format',
    
    // Sections
    'section1.title': 'THE SOUL OF THE NIGHT.',
    'section1.content': 'ANIMA is more than a party — it\'s a ritual of sound and light. Guided by house and afrohouse beats, the dancefloor becomes a place of pure expression, free from judgment and filled with unity. The rising sun above the DJ booth symbolizes the endless energy that carries us from midnight to dawn.',
    
    'section2.title': 'THE FUTURE OF CLUBBING, TODAY.',
    'section2.content': 'Our venue hosts up to 1,200 people, transformed by immersive light and sound. At its core shines a glowing glass Sun, surrounded by spiraling LED cables — a symbol of power and beauty that defines the ANIMA experience. Red and white beams cut through the crowd, setting the rhythm for a night of elegance and intensity. Our home for the Winter is the Iconic neapolitan club HBToo, who hosts Anima since the beginning.',
    'section2.cta': 'How to get here',
    
    'section3.title': 'NEXT ACT',
    'section3.content': 'Every season ANIMA curates nights where international electronic music rise and spreads vibes through the night. Be part of the story — explore the upcoming shows and secure your spot beneath the Sun.',
    
    'section4.title': 'THOSE WHO SHARED THE LIGHT.',
    'section4.content': 'From acclaimed DJs to rising voices, every guest has left their mark on our stage. Explore the legacy of artists who shaped our journey — and discover who\'s next.',
    
    'section5.title': 'ENGINEERED FOR EUPHORIA.',
    'section5.content': 'A stage crafted for energy and emotion. High-end sound, immersive lights, LED architecture and the iconic rising sun create an atmosphere where every drop resonates, every transition glows, every moment becomes unforgettable.',
    
    'section6.title': 'RELIVE THE SUNRISE',
    'section6.content': 'Every moment is captured: aftermovies, photo galleries and highlights that embody the spirit of ANIMA. Step back into the crowd, the beats, the sunrise and carry the memory forward.',
    'section6.cta': 'Check our Telegram group',
    
    'section7.title': 'THE SOUNDTRACK OF YOUR NIGHT',
    'section7.content': 'Take ANIMA with you. Listen to our official playlist, curated to bring the sunrise energy to any moment.',
    'section7.cta': 'Listen on Spotify',
    
    'section8.title': 'LET\'S CREATE TOGETHER.',
    'section8.content': 'ANIMA is open to collaborations with brands, promoters and artists. Let\'s build the next chapter of future clubbing, together.',
    'section8.cta': 'Send Request',
    
    'section9.title': 'BE THE FIRST TO DANCE.',
    'section9.content': 'Get in touch with ANIMA! Reserve your access and book your private zone! Stay connected — from midnight until sunrise.',
    'section9.cta': 'Contact Us on WhatsApp',
    
    'section10.title': 'FIND YOUR WAY TO THE SUN.',
    'section10.content': 'Plan your night with ease and step into the world of ANIMA. The dancefloor awaits.',
    'section10.cta': 'Open on Google Maps',
    
    // Footer
    'footer.tagline': 'Until the Sunrise',
    'footer.description': 'Where house and afrohouse converge into a radiant journey. A club experience built around joy, light and freedom.',
    'footer.quicklinks': 'Quick Links',
    'footer.contact': 'Contact',
    'footer.social': 'Let\'s get in touch! Follow us on our Social Media',
    'footer.rights': '© 2024 ANIMA Events. All rights reserved.',
  },
  it: {
    // Hero
    'hero.tagline': 'Dove house e afrohouse convergono in un viaggio radioso.',
    'hero.tagline2': 'Un\'esperienza club costruita su gioia, luce e libertà — progettata per durare fino all\'alba.',
    'hero.cta': 'Scopri il Format',
    
    // Sections
    'section1.title': 'L\'ANIMA DELLA NOTTE.',
    'section1.content': 'ANIMA è più di una festa — è un rituale di suono e luce. Guidata dai ritmi house e afrohouse, la pista da ballo diventa un luogo di pura espressione, libero da giudizi e pieno di unità. Il sole che sorge sopra la console DJ simboleggia l\'energia infinita che ci accompagna dalla mezzanotte all\'alba.',
    
    'section2.title': 'IL FUTURO DEL CLUBBING, OGGI.',
    'section2.content': 'Il nostro locale ospita fino a 1.200 persone, trasformate da luci e suoni immersivi. Al centro brilla un Sole di vetro luminoso, circondato da cavi LED a spirale — un simbolo di potere e bellezza che definisce l\'esperienza ANIMA. Fasci rossi e bianchi attraversano la folla, dettando il ritmo di una notte di eleganza e intensità. La nostra casa per l\'inverno è l\'iconico club napoletano HBToo, che ospita Anima fin dall\'inizio.',
    'section2.cta': 'Come arrivare',
    
    'section3.title': 'PROSSIMO EVENTO',
    'section3.content': 'Ogni stagione ANIMA cura serate in cui la musica elettronica internazionale si eleva e diffonde vibrazioni per tutta la notte. Fai parte della storia — esplora i prossimi eventi e assicurati il tuo posto sotto il Sole.',
    
    'section4.title': 'CHI HA CONDIVISO LA LUCE.',
    'section4.content': 'Da DJ acclamati a voci emergenti, ogni ospite ha lasciato il proprio segno sul nostro palco. Esplora l\'eredità degli artisti che hanno plasmato il nostro viaggio — e scopri chi sarà il prossimo.',
    
    'section5.title': 'PROGETTATO PER L\'EUFORIA.',
    'section5.content': 'Un palco creato per energia ed emozione. Suono di alta qualità, luci immersive, architettura LED e l\'iconico sole nascente creano un\'atmosfera in cui ogni drop risuona, ogni transizione brilla, ogni momento diventa indimenticabile.',
    
    'section6.title': 'RIVIVI L\'ALBA',
    'section6.content': 'Ogni momento è catturato: aftermovie, gallerie fotografiche e highlights che incarnano lo spirito di ANIMA. Torna tra la folla, i beat, l\'alba e porta avanti il ricordo.',
    'section6.cta': 'Unisciti al nostro gruppo Telegram',
    
    'section7.title': 'LA COLONNA SONORA DELLA TUA NOTTE',
    'section7.content': 'Porta ANIMA con te. Ascolta la nostra playlist ufficiale, curata per portare l\'energia dell\'alba in ogni momento.',
    'section7.cta': 'Ascolta su Spotify',
    
    'section8.title': 'CREIAMO INSIEME.',
    'section8.content': 'ANIMA è aperta a collaborazioni con brand, promoter e artisti. Costruiamo insieme il prossimo capitolo del clubbing futuro.',
    'section8.cta': 'Invia Richiesta',
    
    'section9.title': 'SII IL PRIMO A BALLARE.',
    'section9.content': 'Mettiti in contatto con ANIMA! Prenota il tuo accesso e riserva la tua zona privata! Rimani connesso — dalla mezzanotte fino all\'alba.',
    'section9.cta': 'Contattaci su WhatsApp',
    
    'section10.title': 'TROVA LA VIA VERSO IL SOLE.',
    'section10.content': 'Pianifica la tua serata con facilità e entra nel mondo di ANIMA. La pista da ballo ti aspetta.',
    'section10.cta': 'Apri su Google Maps',
    
    // Footer
    'footer.tagline': 'Fino all\'Alba',
    'footer.description': 'Dove house e afrohouse convergono in un viaggio radioso. Un\'esperienza club costruita su gioia, luce e libertà.',
    'footer.quicklinks': 'Link Rapidi',
    'footer.contact': 'Contatti',
    'footer.social': 'Mettiamoci in contatto! Seguici sui nostri Social Media',
    'footer.rights': '© 2024 ANIMA Events. Tutti i diritti riservati.',
  },
}

const LanguageContext = createContext<LanguageContextType | undefined>(undefined)

export function LanguageProvider({ children }: { children: ReactNode }) {
  const [language, setLanguage] = useState<Language>('en')

  const t = (key: string): string => {
    return translations[language][key as keyof typeof translations['en']] || key
  }

  return (
    <LanguageContext.Provider value={{ language, setLanguage, t }}>
      {children}
    </LanguageContext.Provider>
  )
}

export function useLanguage() {
  const context = useContext(LanguageContext)
  if (context === undefined) {
    throw new Error('useLanguage must be used within a LanguageProvider')
  }
  return context
}
