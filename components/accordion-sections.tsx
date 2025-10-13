"use client"

import type React from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Textarea } from "@/components/ui/textarea"
import { useState } from "react"

export function AccordionSections() {
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
      title: "THE SOUL OF THE NIGHT.",
      content:
        "ANIMA is more than a party — it's a ritual of sound and light. Guided by house and afrohouse beats, the dancefloor becomes a place of pure expression, free from judgment and filled with unity. The rising sun above the DJ booth symbolizes the endless energy that carries us from midnight to dawn.",
      cta: null,
    },
    {
      id: "venue",
      title: "THE FUTURE OF CLUBBING, TODAY.",
      content:
        "Our venue hosts up to 1,200 people, transformed by immersive light and sound. At its core shines a glowing glass Sun, surrounded by spiraling LED cables — a symbol of power and beauty that defines the ANIMA experience. Red and white beams cut through the crowd, setting the rhythm for a night of elegance and intensity. Our home for the Winter is the Iconic neapolitan club HBToo, who hosts Anima since the beginning.",
      cta: {
        text: "How to get here",
        action: () => document.getElementById("map")?.scrollIntoView({ behavior: "smooth" }),
      },
    },
    {
      id: "shows",
      title: "NEXT ACT",
      content:
        "Every season ANIMA curates nights where international electronic music rise and spreads vibes through the night. Be part of the story — explore the upcoming shows and secure your spot beneath the Sun.",
      cta: null,
    },
    {
      id: "guests",
      title: "THOSE WHO SHARED THE LIGHT.",
      content:
        "From acclaimed DJs to rising voices, every guest has left their mark on our stage. Explore the legacy of artists who shaped our journey — and discover who's next.",
      cta: null,
    },
    {
      id: "stage",
      title: "ENGINEERED FOR EUPHORIA.",
      content:
        "A stage crafted for energy and emotion. High-end sound, immersive lights, LED architecture and the iconic rising sun create an atmosphere where every drop resonates, every transition glows, every moment becomes unforgettable.",
      cta: null,
    },
    {
      id: "media",
      title: "RELIVE THE SUNRISE",
      content:
        "Every moment is captured: aftermovies, photo galleries and highlights that embody the spirit of ANIMA. Step back into the crowd, the beats, the sunrise and carry the memory forward.",
      cta: {
        text: "Check our Telegram group",
        href: "https://t.me/UNTILTHESUNRISES",
      },
    },
    {
      id: "playlist",
      title: "THE SOUNDTRACK OF YOUR NIGHT",
      content:
        "Take ANIMA with you. Listen to our official playlist, curated to bring the sunrise energy to any moment.",
      cta: {
        text: "Listen on Spotify",
        href: "https://open.spotify.com/playlist/5WpQ7y1j9EhRMQlMKPJqSx?si=aa8b855a6bdc432e",
      },
    },
    {
      id: "collaborations",
      title: "LET'S CREATE TOGETHER.",
      content:
        "ANIMA is open to collaborations with brands, promoters and artists. Let's build the next chapter of future clubbing, together.",
      cta: {
        text: "Send Request",
        href: "mailto:info.animaent@gmail.com",
      },
    },
    {
      id: "liste",
      title: "BE THE FIRST TO DANCE.",
      content:
        "Get in touch with ANIMA! Reserve your access and book your private zone! Stay connected — from midnight until sunrise.",
      cta: {
        text: "Contact Us on WhatsApp",
        href: "https://wa.me/393389040714",
      },
    },
    {
      id: "map",
      title: "FIND YOUR WAY TO THE SUN.",
      content:
        "Plan your night with ease and step into the world of ANIMA. The dancefloor awaits.",
      cta: {
        text: "Open on Google Maps",
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
          className={`w-full py-16 px-4 text-center ${backgrounds[index % backgrounds.length]}`}
        >
          <div className="max-w-[60%] mx-auto">
            <h2 className="title-primary mb-12 text-black leading-[0.9]" style={{ fontSize: 'clamp(1rem, 3.75vw, 4.5rem)' }}>
              {section.title}
          </h2>

            <div className="max-w-4xl mx-auto">
            <p className="text-lg md:text-xl leading-relaxed text-gray-700 mb-8">
                {section.content}
            </p>
          </div>

            {section.cta && (
              <div className="mt-6">
                {section.cta.href ? (
                  <Button
                    asChild
                    className="btn-primary bg-primary hover:bg-primary/90 text-white px-8 py-4 text-base shadow-lg"
                  >
                    <a href={section.cta.href} target="_blank" rel="noopener noreferrer">
                      {section.cta.text}
                    </a>
                  </Button>
                ) : (
                  <Button
                    onClick={section.cta.action}
                    className="btn-primary bg-primary hover:bg-primary/90 text-white px-8 py-4 text-base shadow-lg"
                  >
                    {section.cta.text}
                  </Button>
                )}
              </div>
          )}
          </div>
        </div>
      ))}
    </div>
  )
}
