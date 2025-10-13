"use client"

import type React from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Textarea } from "@/components/ui/textarea"
import { useState } from "react"
import { useLanguage } from "@/contexts/LanguageContext"

export function AccordionSections() {
  const { t } = useLanguage()
  const [formData, setFormData] = useState({
    name: "",
    email: "",
    message: "",
  })

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    console.log("Form submitted:", formData)
  }

  const sections = [
    {
      id: "format",
      titleKey: "section1.title",
      contentKey: "section1.content",
      cta: null,
    },
    {
      id: "venue",
      titleKey: "section2.title",
      contentKey: "section2.content",
      cta: {
        textKey: "section2.cta",
        action: () => document.getElementById("map")?.scrollIntoView({ behavior: "smooth" }),
      },
    },
    {
      id: "shows",
      titleKey: "section3.title",
      contentKey: "section3.content",
      cta: null,
    },
    {
      id: "guests",
      titleKey: "section4.title",
      contentKey: "section4.content",
      cta: null,
    },
    {
      id: "stage",
      titleKey: "section5.title",
      contentKey: "section5.content",
      cta: null,
    },
    {
      id: "media",
      titleKey: "section6.title",
      contentKey: "section6.content",
      cta: {
        textKey: "section6.cta",
        href: "https://t.me/UNTILTHESUNRISES",
      },
    },
    {
      id: "playlist",
      titleKey: "section7.title",
      contentKey: "section7.content",
      cta: {
        textKey: "section7.cta",
        href: "https://open.spotify.com/playlist/5WpQ7y1j9EhRMQlMKPJqSx?si=aa8b855a6bdc432e",
      },
    },
    {
      id: "collaborations",
      titleKey: "section8.title",
      contentKey: "section8.content",
      cta: {
        textKey: "section8.cta",
        href: "mailto:info.animaent@gmail.com",
      },
    },
    {
      id: "liste",
      titleKey: "section9.title",
      contentKey: "section9.content",
      cta: {
        textKey: "section9.cta",
        href: "https://wa.me/393389040714",
      },
    },
    {
      id: "map",
      titleKey: "section10.title",
      contentKey: "section10.content",
      cta: {
        textKey: "section10.cta",
        href: "https://maps.google.com",
      },
    },
  ]

  const backgrounds = [
    'bg-white',
    'bg-orange-50/30',
    'bg-gray-50',
    'bg-red-50/20',
    'bg-amber-50/40',
    'bg-stone-50',
  ]

  return (
    <div className="w-full overflow-x-hidden">
      {sections.map((section, index) => (
        <div 
          key={section.id} 
          className={`w-full py-12 md:py-16 px-4 text-center ${backgrounds[index % backgrounds.length]}`}
        >
          <div className="max-w-[90%] md:max-w-[60%] mx-auto">
            <h2 className="title-primary mb-8 md:mb-12 text-black leading-[0.9]" style={{ fontSize: 'clamp(1rem, 3.75vw, 4.5rem)' }}>
              {t(section.titleKey)}
          </h2>

            <div className="max-w-4xl mx-auto">
            <p className="text-base md:text-lg lg:text-xl leading-relaxed text-gray-700 mb-6 md:mb-8 text-left md:text-center">
                {t(section.contentKey)}
            </p>
            </div>
          </div>
              </div>
      ))}
    </div>
  )
}
