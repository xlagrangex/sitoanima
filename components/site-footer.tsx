"use client"

import { Instagram, Facebook, Twitter, Mail, MapPin, Phone } from "lucide-react"

export function SiteFooter() {
  return (
    <footer className="bg-black text-white py-16">
      <div className="container mx-auto px-4">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
          {/* Brand */}
          <div className="md:col-span-2">
            <h3 className="title-primary text-4xl md:text-5xl mb-4 text-primary">ANIMA</h3>
            <p className="text-lg text-white/80 mb-4">Until the Sun Rises</p>
            <p className="text-white/60 max-w-md">
              Il format che ridefinisce la notte. Musica elettronica, atmosfere uniche e un'esperienza che va oltre il
              semplice evento.
            </p>
          </div>

          {/* Quick Links */}
          <div>
            <h4 className="font-sequel text-sm font-black mb-4 uppercase tracking-[0.15em]" style={{ wordSpacing: '0.4em' }}>Quick Links</h4>
            <ul className="space-y-2">
              <li>
                <button
                  onClick={() => document.getElementById("format")?.scrollIntoView({ behavior: "smooth" })}
                  className="text-white/60 hover:text-white transition-colors"
                >
                  Format
                </button>
              </li>
              <li>
                <button
                  onClick={() => document.getElementById("venue")?.scrollIntoView({ behavior: "smooth" })}
                  className="text-white/60 hover:text-white transition-colors"
                >
                  Venue
                </button>
              </li>
              <li>
                <button
                  onClick={() => document.getElementById("shows")?.scrollIntoView({ behavior: "smooth" })}
                  className="text-white/60 hover:text-white transition-colors"
                >
                  Upcoming Shows
                </button>
              </li>
              <li>
                <button
                  onClick={() => document.getElementById("media")?.scrollIntoView({ behavior: "smooth" })}
                  className="text-white/60 hover:text-white transition-colors"
                >
                  Media Gallery
                </button>
              </li>
            </ul>
          </div>

          {/* Contact Info */}
          <div>
            <h4 className="font-sequel text-sm font-black mb-4 uppercase tracking-[0.15em]" style={{ wordSpacing: '0.4em' }}>Contatti</h4>
            <div className="space-y-3">
              <div className="flex items-center space-x-3">
                <Mail size={16} className="text-primary" />
                <a href="mailto:info@anima-events.com" className="text-white/60 hover:text-white transition-colors">
                  info@anima-events.com
                </a>
              </div>
              <div className="flex items-center space-x-3">
                <Phone size={16} className="text-primary" />
                <a href="tel:+393331234567" className="text-white/60 hover:text-white transition-colors">
                  +39 333 123 4567
                </a>
              </div>
              <div className="flex items-center space-x-3">
                <MapPin size={16} className="text-primary" />
                <span className="text-white/60">Milano, Italia</span>
              </div>
            </div>
          </div>
        </div>

        {/* Social Media & Copyright */}
        <div className="border-t border-white/10 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
          <div className="flex items-center space-x-6 mb-4 md:mb-0">
            <a href="#" className="text-white/60 hover:text-primary transition-colors" aria-label="Instagram">
              <Instagram size={24} />
            </a>
            <a href="#" className="text-white/60 hover:text-primary transition-colors" aria-label="Facebook">
              <Facebook size={24} />
            </a>
            <a href="#" className="text-white/60 hover:text-primary transition-colors" aria-label="Twitter">
              <Twitter size={24} />
            </a>
          </div>

          <div className="text-center md:text-right">
            <p className="text-white/60 text-sm">© 2024 ANIMA Events. Tutti i diritti riservati.</p>
            <div className="flex items-center justify-center md:justify-end space-x-4 mt-2">
              <button
                onClick={() => document.getElementById("privacy")?.scrollIntoView({ behavior: "smooth" })}
                className="text-white/40 hover:text-white/60 transition-colors text-xs"
              >
                Privacy Policy
              </button>
              <span className="text-white/20">•</span>
              <button
                onClick={() => document.getElementById("privacy")?.scrollIntoView({ behavior: "smooth" })}
                className="text-white/40 hover:text-white/60 transition-colors text-xs"
              >
                Cookie Policy
              </button>
            </div>
          </div>
        </div>
      </div>
    </footer>
  )
}
