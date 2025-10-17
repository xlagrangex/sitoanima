"use client"

import { useState, useEffect } from "react"
import { Menu, X } from "lucide-react"
import { motion, AnimatePresence } from "framer-motion"
import Image from "next/image"

export function StickyHeader() {
  const [isMenuOpen, setIsMenuOpen] = useState(false)
  const [isScrolled, setIsScrolled] = useState(false)

  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 50)
    }
    window.addEventListener("scroll", handleScroll)
    return () => window.removeEventListener("scroll", handleScroll)
  }, [])

  const scrollToSection = (id: string) => {
    document.getElementById(id)?.scrollIntoView({ behavior: "smooth" })
    setIsMenuOpen(false)
  }

  return (
    <motion.header
      className={`fixed top-4 left-1/2 -translate-x-1/2 z-[9999] transition-all duration-300 ${
        isScrolled
          ? "backdrop-blur-md bg-black/40 border border-white/20"
          : "backdrop-blur-sm bg-black/30 border border-white/15"
      } rounded-full shadow-2xl`}
      style={{ width: "min(95vw, 1100px)", padding: "12px 32px" }}
      initial={{ opacity: 0, y: -20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.6, delay: 0.5, ease: "easeOut" }}
    >
      <nav className="flex items-center justify-between h-full">
        <motion.button
          onClick={() => scrollToSection("hero")}
          className="flex items-center hover:scale-105 transition-transform"
          whileHover={{ scale: 1.05 }}
          whileTap={{ scale: 0.95 }}
        >
          <Image src="/anima-logo-white.png" alt="ANIMA" width={120} height={40} className="h-10 w-auto object-contain" />
        </motion.button>

        {/* Desktop Navigation - Centered */}
        <div className="hidden md:flex items-center space-x-4 absolute left-1/2 transform -translate-x-1/2">
          {["format", "venue", "shows", "guests", "media", "instagram", "map"].map((section, index) => (
            <motion.button
              key={section}
              onClick={() => scrollToSection(section)}
              className="font-sequel text-white/90 hover:text-white transition-colors text-sm font-black tracking-[0.08em] uppercase"
              style={{ wordSpacing: '0.4em' }}
              initial={{ opacity: 0, y: -10 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.4, delay: 0.7 + index * 0.1 }}
              whileHover={{ scale: 1.05 }}
            >
              {section === "format" && "Format"}
              {section === "venue" && "Venue"}
              {section === "shows" && "Shows"}
              {section === "guests" && "Guests"}
              {section === "media" && "Media"}
              {section === "instagram" && "Instagram"}
              {section === "map" && "Contatti"}
            </motion.button>
          ))}
        </div>

        {/* Mobile Menu Button */}
        <motion.button
          onClick={() => setIsMenuOpen(!isMenuOpen)}
          className="md:hidden text-white p-2"
          whileHover={{ scale: 1.1 }}
          whileTap={{ scale: 0.95 }}
        >
          {isMenuOpen ? <X size={20} /> : <Menu size={20} />}
        </motion.button>
      </nav>

      {/* Mobile Menu */}
      <AnimatePresence>
        {isMenuOpen && (
          <motion.div
            className="md:hidden absolute top-full left-0 right-0 mt-2 backdrop-blur-md bg-black/50 border border-white/20 rounded-2xl p-4"
            initial={{ opacity: 0, y: -10, scale: 0.95 }}
            animate={{ opacity: 1, y: 0, scale: 1 }}
            exit={{ opacity: 0, y: -10, scale: 0.95 }}
            transition={{ duration: 0.2 }}
          >
            <div className="flex flex-col space-y-3">
              {["format", "venue", "shows", "guests", "media", "instagram", "map"].map((section, index) => (
                <motion.button
                  key={section}
                  onClick={() => scrollToSection(section)}
                  className="font-sequel text-white/90 hover:text-white transition-colors text-left py-2 font-black uppercase text-sm tracking-[0.15em]"
                  style={{ wordSpacing: '0.4em' }}
                  initial={{ opacity: 0, x: -10 }}
                  animate={{ opacity: 1, x: 0 }}
                  transition={{ duration: 0.2, delay: index * 0.05 }}
                >
                  {section === "format" && "Format"}
                  {section === "venue" && "Venue"}
                  {section === "shows" && "Shows"}
                  {section === "guests" && "Guests"}
                  {section === "media" && "Media"}
                  {section === "instagram" && "Instagram"}
                  {section === "map" && "Contatti"}
                </motion.button>
              ))}
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </motion.header>
  )
}
