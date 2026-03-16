"use client"

import React from "react"
import Image from "next/image"
import { Button } from "@/components/ui/button"
import { useState, useEffect } from "react"
import { useLanguage } from "@/contexts/LanguageContext"
import { motion } from "framer-motion"
import { GlitchText } from "@/components/glitch-animations"
import { Gallery4, type Gallery4Item } from "@/components/ui/gallery4"
import type { GalleryItem } from "@/components/ui/circular-gallery"
import { InstagramGrid } from "@/components/instagram-grid"
import { LazySpotifyPlaylist } from "@/components/lazy-spotify-playlist"
import { LazyGoogleMap } from "@/components/lazy-google-map"
import { Mail, MessageCircle, MapPin, Send, Music, Instagram } from "lucide-react"
import { fetchImages, fetchSettings, getSetting } from "@/lib/cms"
import type { SectionImage, SiteSetting } from "@/lib/supabase"

// Static fallback data
const STATIC_GALLERY: GalleryItem[] = Array.from({ length: 15 }, (_, i) => ({
  common: "",
  binomial: "",
  photo: {
    url: `/immagini-optimized/media-${i + 1}.webp?v=2`,
    text: `ANIMA Event - Moment ${String(i + 1).padStart(2, "0")}`,
    by: "ANIMA Events",
  },
}))

const STATIC_GUESTS = [
  { quote: "Electronic music duo bringing innovative sounds and dynamic performances to the scene.", name: "Picca & Mars", designation: "Electronic Duo", src: "/immagini-guest-cropped/piccaemars.webp" },
  { quote: "Electronic music artist bringing fresh sounds and innovative beats to the underground scene.", name: "Twolate", designation: "Electronic Artist", src: "/immagini-guest-cropped/twolate.webp" },
  { quote: "Techno producer and DJ known for his powerful sets and underground sound.", name: "Peppe Citarella", designation: "Techno DJ & Producer", src: "/immagini-guest-cropped/peppe-citarella.webp" },
  { quote: "Electronic music artist with a unique style blending techno and house elements.", name: "Marco Lys", designation: "Electronic Artist", src: "/immagini-guest-cropped/Marco Lys at Il Muretto 3.webp" },
  { quote: "House music producer and DJ bringing energy and groove to every performance.", name: "Piero Pirupa", designation: "House DJ & Producer", src: "/immagini-guest-cropped/Pieropirupa.webp" },
  { quote: "Electronic music artist known for innovative sounds and captivating performances.", name: "Grossomoddo", designation: "Electronic Artist", src: "/immagini-guest-cropped/grossomoddo-new.webp" },
  { quote: "Techno DJ and producer delivering deep, hypnotic beats to the underground scene.", name: "Peaty", designation: "Techno DJ & Producer", src: "/immagini-guest-cropped/peaty.webp" },
]

const STATIC_SETTINGS: Record<string, string> = {
  eventbrite_url: "https://www.eventbrite.it/e/biglietti-anima-meets-disco-magazine-vol03-act-18-1982663047547?aff=oddtdtcreator",
  telegram_url: "https://t.me/UNTILTHESUNRISES",
  spotify_playlist_url: "https://open.spotify.com/playlist/5WpQ7y1j9EhRMQlMKPJqSx?si=aa8b855a6bdc432e",
  spotify_embed_url: "https://open.spotify.com/embed/playlist/5WpQ7y1j9EhRMQlMKPJqSx?utm_source=generator",
  whatsapp_url: "https://wa.me/393389040714",
  email: "info.animaent@gmail.com",
  instagram_url: "https://www.instagram.com/anima.ent",
  google_maps_url: "https://www.google.com/maps/place/HBTOO/@40.8011031,14.1726738,17z/data=!3m1!4b1!4m6!3m5!1s0x133b0c1e75bca2d7:0xe08e8f4f3a917d24",
  google_maps_embed: "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3020.2225497639356!2d14.172673776545675!3d40.80110307138039!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133b0c1e75bca2d7%3A0xe08e8f4f3a917d24!2sHBTOO!5e0!3m2!1sit!2sit!4v1760536364942!5m2!1sit!2sit",
}

function buildGalleryFromCMS(images: SectionImage[]): GalleryItem[] {
  return images.map((img) => ({
    common: "",
    binomial: "",
    photo: {
      url: img.image_url + "?v=2",
      text: img.alt_text || "ANIMA Event",
      by: "ANIMA Events",
    },
  }))
}

function buildGuestGalleryFromCMS(
  guestImages: SectionImage[],
  featureImages: SectionImage[]
): GalleryItem[] {
  const featureItems: GalleryItem[] = featureImages.map((img) => ({
    common: img.metadata?.name || "",
    binomial: "",
    photo: {
      url: img.image_url,
      text: img.alt_text || "ANIMA Event",
      by: "ANIMA Events",
      pos: "center",
    },
  }))

  const guestItems: GalleryItem[] = guestImages.map((guest) => ({
    common: guest.metadata?.name || "",
    binomial: guest.metadata?.designation || "",
    photo: {
      url: guest.image_url,
      text: guest.metadata?.quote || "",
      by: "ANIMA Events",
      pos:
        guest.metadata?.name === "Grossomoddo"
          ? "center top"
          : guest.metadata?.name === "Peppe Citarella"
          ? "center center"
          : "center",
    },
  }))

  return [...featureItems, ...guestItems]
}

export function AccordionSections() {
  const { t } = useLanguage()
  const [isMobile, setIsMobile] = useState(false)
  const [cmsImages, setCmsImages] = useState<SectionImage[]>([])
  const [cmsSettings, setCmsSettings] = useState<SiteSetting[]>([])
  const [dataLoaded, setDataLoaded] = useState(false)

  useEffect(() => {
    const checkMobile = () => setIsMobile(window.innerWidth < 768)
    checkMobile()
    window.addEventListener("resize", checkMobile)
    return () => window.removeEventListener("resize", checkMobile)
  }, [])

  // Fetch CMS data
  useEffect(() => {
    Promise.all([fetchImages(), fetchSettings()])
      .then(([imgs, sets]) => {
        if (imgs.length > 0) setCmsImages(imgs)
        if (sets.length > 0) setCmsSettings(sets)
        setDataLoaded(true)
      })
      .catch(() => setDataLoaded(true))
  }, [])

  // Helper to get setting value with fallback
  const setting = (key: string): string => {
    if (cmsSettings.length > 0) {
      return getSetting(cmsSettings, key)
    }
    return STATIC_SETTINGS[key] || ""
  }

  // Build gallery items from CMS or static fallback
  const mediaImages = cmsImages.filter((img) => img.section === "media")
  const guestImages = cmsImages.filter((img) => img.section === "guests")
  const featureImages = cmsImages.filter((img) => img.section === "guests_feature")
  const showsImages = cmsImages.filter((img) => img.section === "shows")

  const galleryItems =
    mediaImages.length > 0 ? buildGalleryFromCMS(mediaImages) : STATIC_GALLERY

  const guestGalleryItems =
    guestImages.length > 0
      ? buildGuestGalleryFromCMS(guestImages, featureImages)
      : [
          {
            common: "KILIMANJARO",
            binomial: "",
            photo: {
              url: "/KILLI_PART_II_16_10_2025154301.webp",
              text: "ANIMA Event - KILLI PART II",
              by: "ANIMA Events",
              pos: "center",
            },
          },
          ...STATIC_GUESTS.map((guest) => ({
            common: guest.name,
            binomial: guest.designation,
            photo: {
              url: guest.src,
              text: guest.quote,
              by: "ANIMA Events",
              pos:
                guest.name === "Grossomoddo"
                  ? "center top"
                  : guest.name === "Peppe Citarella"
                  ? "center center"
                  : "center",
            },
          })),
        ]

  const showsImageUrl =
    showsImages.length > 0
      ? showsImages[0].image_url
      : "/immagini-optimized/FEED-27-02.webp"

  const getButtonIcon = (href?: string) => {
    if (!href) return null
    if (href.includes("mailto:")) return <Mail className="w-4 h-4 mr-2" />
    if (href.includes("wa.me") || href.includes("whatsapp"))
      return <MessageCircle className="w-4 h-4 mr-2" />
    if (href.includes("maps.google") || href.includes("google.com/maps"))
      return <MapPin className="w-4 h-4 mr-2" />
    if (href.includes("t.me") || href.includes("telegram"))
      return <Send className="w-4 h-4 mr-2" />
    if (href.includes("spotify")) return <Music className="w-4 h-4 mr-2" />
    return null
  }

  const convertToGallery4Items = (
    items: GalleryItem[],
    showOnlyNames: boolean = false
  ): Gallery4Item[] => {
    return items.map((item, index) => ({
      id:
        item.photo.url.split("/").pop()?.replace(".webp", "") ||
        `item-${index}`,
      title: showOnlyNames ? item.common || "" : "",
      description: item.photo.by || item.binomial || "",
      href: item.photo.url || "#",
      image: item.photo.url,
    }))
  }

  const gallery4Items = convertToGallery4Items(galleryItems, false)
  const guestGallery4Items = convertToGallery4Items(guestGalleryItems, true)

  const sections = [
    {
      id: "format",
      subtitleKey: "section1.subtitle",
      titleKey: "section1.title",
      contentKey: "section1.content",
      cta: null,
    },
    {
      id: "venue",
      subtitleKey: "section2.subtitle",
      titleKey: "section2.title",
      contentKey: "section2.content",
      cta: {
        textKey: "section2.cta",
        action: () => {
          const element = document.getElementById("map")
          if (!element) return
          const isMob = window.innerWidth < 768
          if (isMob) {
            const headerHeight = 80
            const elementPosition =
              element.getBoundingClientRect().top + window.pageYOffset
            window.scrollTo({
              top: elementPosition - headerHeight,
              behavior: "auto",
            })
          } else {
            element.scrollIntoView({ behavior: "smooth" })
          }
        },
      },
    },
    {
      id: "shows",
      subtitleKey: "section3.subtitle",
      titleKey: "section3.title",
      contentKey: "section3.content",
      cta: { textKey: "section3.cta", href: setting("eventbrite_url") },
    },
    {
      id: "guests",
      subtitleKey: "section4.subtitle",
      titleKey: "section4.title",
      contentKey: "section4.content",
      cta: null,
    },
    {
      id: "media",
      subtitleKey: "section6.subtitle",
      titleKey: "section6.title",
      contentKey: "section6.content",
      cta: { textKey: "section6.cta", href: setting("telegram_url") },
    },
    {
      id: "playlist",
      subtitleKey: "section7.subtitle",
      titleKey: "section7.title",
      contentKey: "section7.content",
      cta: { textKey: "section7.cta", href: setting("spotify_playlist_url") },
    },
    {
      id: "collaborations",
      subtitleKey: "section8.subtitle",
      titleKey: "section8.title",
      contentKey: "section8.content",
      cta: { textKey: "section8.cta", href: `mailto:${setting("email")}` },
    },
    {
      id: "liste",
      subtitleKey: "section9.subtitle",
      titleKey: "section9.title",
      contentKey: "section9.content",
      cta: { textKey: "section9.cta", href: setting("whatsapp_url") },
    },
    {
      id: "instagram",
      subtitleKey: "section11.subtitle",
      titleKey: "section11.title",
      contentKey: "section11.content",
      hasInstagram: true,
      cta: { textKey: "section11.cta", href: setting("instagram_url") },
    },
    {
      id: "map",
      subtitleKey: "section10.subtitle",
      titleKey: "section10.title",
      contentKey: "section10.content",
      cta: { textKey: "section10.cta", href: setting("google_maps_url") },
    },
  ]

  const colorSchemes = [
    { bg: "bg-black", text: "text-white", accent: "text-red-600" },
    { bg: "bg-red-700", text: "text-white", accent: "text-black" },
    { bg: "bg-white", text: "text-black", accent: "text-red-600" },
    { bg: "bg-[#E78E00]", text: "text-white", accent: "text-white" },
  ]

  return (
    <div className="w-full overflow-x-hidden">
      {sections.map((section, index) => {
        const scheme = colorSchemes[index % colorSchemes.length]

        return (
          <div
            key={section.id}
            id={section.id}
            className={`w-full flex items-center justify-center py-12 md:py-16 px-4 text-center ${scheme.bg} ${
              section.id === "format"
                ? "h-[70vh] md:min-h-screen"
                : section.id === "collaborations"
                ? "h-[60vh] md:min-h-screen"
                : section.id === "liste"
                ? "h-[60vh] md:min-h-screen"
                : "min-h-screen"
            }`}
          >
            <div className="max-w-[90%] md:max-w-[60%] mx-auto w-full">
              {section.subtitleKey && (
                <p
                  className={`text-xs md:text-sm uppercase tracking-wider mb-3 md:mb-4 ${scheme.text} opacity-70 font-avenir`}
                >
                  {t(section.subtitleKey)}
                </p>
              )}
              <GlitchText
                className={`title-primary mb-8 md:mb-12 ${scheme.text} leading-[0.9] block`}
                style={{ fontSize: "clamp(2rem, 4.5vw, 4.5rem)" }}
                delay={index * 0.1}
              >
                {t(section.titleKey)}
              </GlitchText>

              {section.id === "media" ? (
                <div className="w-full max-w-6xl mx-auto">
                  <Gallery4 items={gallery4Items} aspectRatio="2/3" />
                  {section.cta && section.cta.href && (
                    <div className="mt-8 flex justify-center">
                      <Button
                        asChild
                        className={`btn-primary ${
                          scheme.accent === "text-red-600"
                            ? "bg-red-600 hover:bg-red-700"
                            : scheme.accent === "text-black"
                            ? "bg-black hover:bg-gray-900"
                            : "bg-white hover:bg-gray-100"
                        } ${
                          scheme.accent === "text-white"
                            ? "text-black"
                            : "text-white"
                        } px-3 md:px-8 py-3 md:py-4 text-sm md:text-base shadow-lg max-w-full`}
                      >
                        <a
                          href={section.cta.href}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="flex items-center justify-center"
                        >
                          {getButtonIcon(section.cta.href)}
                          {t(section.cta.textKey)}
                        </a>
                      </Button>
                    </div>
                  )}
                </div>
              ) : section.id === "shows" ? (
                <div className="max-w-4xl mx-auto">
                  <p
                    className={`text-base md:text-lg lg:text-xl leading-relaxed ${scheme.text} mb-6 md:mb-8 text-center`}
                  >
                    {t(section.contentKey)}
                  </p>
                  <div className="w-full max-w-2xl mx-auto">
                    <Image
                      src={showsImageUrl}
                      alt="ANIMA Event - Next Act"
                      width={1081}
                      height={1081}
                      className="w-full h-auto rounded-lg shadow-2xl"
                      priority
                    />
                  </div>
                  {section.cta && section.cta.href && (
                    <div className="mt-8 flex justify-center">
                      <Button
                        asChild
                        className={`btn-primary ${
                          scheme.accent === "text-red-600"
                            ? "bg-red-600 hover:bg-red-700"
                            : scheme.accent === "text-black"
                            ? "bg-black hover:bg-gray-900"
                            : "bg-white hover:bg-gray-100"
                        } ${
                          scheme.accent === "text-white"
                            ? "text-black"
                            : "text-white"
                        } px-3 md:px-8 py-3 md:py-4 text-sm md:text-base shadow-lg max-w-full`}
                      >
                        <a
                          href={section.cta.href}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="flex items-center justify-center"
                        >
                          {t(section.cta.textKey)}
                        </a>
                      </Button>
                    </div>
                  )}
                </div>
              ) : section.id === "guests" ? (
                <div className="w-full max-w-6xl mx-auto">
                  <Gallery4 items={guestGallery4Items} aspectRatio="2/3" />
                </div>
              ) : section.id === "playlist" ? (
                <div className="max-w-4xl mx-auto">
                  <p
                    className={`text-base md:text-lg lg:text-xl leading-relaxed ${scheme.text} mb-6 md:mb-8 text-center`}
                  >
                    {t(section.contentKey)}
                  </p>
                  <LazySpotifyPlaylist
                    src={setting("spotify_embed_url") || "https://open.spotify.com/embed/playlist/5WpQ7y1j9EhRMQlMKPJqSx?utm_source=generator"}
                    width="100%"
                    height="352"
                    fallbackUrl={setting("spotify_playlist_url") || "https://open.spotify.com/playlist/5WpQ7y1j9EhRMQlMKPJqSx?si=UO87sHUrQSOU5BDQYn6CQQ&pi=sQFh_xM3SnavA"}
                  />
                </div>
              ) : section.id === "instagram" ? (
                <div className="w-full max-w-6xl mx-auto">
                  <p
                    className={`text-base md:text-lg lg:text-xl leading-relaxed ${scheme.text} mb-8 md:mb-12 text-center`}
                  >
                    {t(section.contentKey)}
                  </p>
                  <InstagramGrid />
                  {section.cta && section.cta.href && (
                    <div className="mt-8 flex justify-center">
                      <Button
                        asChild
                        className={`btn-primary ${
                          scheme.accent === "text-red-600"
                            ? "bg-red-600 hover:bg-red-700"
                            : scheme.accent === "text-black"
                            ? "bg-black hover:bg-gray-900"
                            : "bg-white hover:bg-gray-100"
                        } ${
                          scheme.accent === "text-white"
                            ? "text-black"
                            : "text-white"
                        } px-3 md:px-8 py-3 md:py-4 text-sm md:text-base shadow-lg max-w-full`}
                      >
                        <a
                          href={section.cta.href}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="flex items-center justify-center"
                        >
                          <Instagram className="w-4 h-4 mr-2" />
                          {t(section.cta.textKey)}
                        </a>
                      </Button>
                    </div>
                  )}
                </div>
              ) : section.id === "map" ? (
                <div className="w-full max-w-5xl mx-auto">
                  <p
                    className={`text-base md:text-lg lg:text-xl leading-relaxed ${scheme.text} mb-6 md:mb-8 text-center`}
                  >
                    {t(section.contentKey)}
                  </p>
                  <div className="w-full aspect-[4/3] md:aspect-[16/9] rounded-lg overflow-hidden shadow-2xl relative">
                    <LazyGoogleMap
                      src={setting("google_maps_embed") || "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3020.2225497639356!2d14.172673776545675!3d40.80110307138039!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133b0c1e75bca2d7%3A0xe08e8f4f3a917d24!2sHBTOO!5e0!3m2!1sit!2sit!4v1760536364942!5m2!1sit!2sit"}
                      className="absolute inset-0 w-full h-full"
                    />
                  </div>
                  {section.cta && section.cta.href && (
                    <div className="mt-6 flex justify-center">
                      <Button
                        asChild
                        className={`btn-primary ${
                          scheme.accent === "text-red-600"
                            ? "bg-red-600 hover:bg-red-700"
                            : scheme.accent === "text-black"
                            ? "bg-black hover:bg-gray-900"
                            : "bg-white hover:bg-gray-100"
                        } ${
                          scheme.accent === "text-white"
                            ? "text-black"
                            : "text-white"
                        } px-3 md:px-8 py-3 md:py-4 text-sm md:text-base shadow-lg max-w-full`}
                      >
                        <a
                          href={section.cta.href}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="flex items-center justify-center"
                        >
                          {getButtonIcon(section.cta.href)}
                          {t(section.cta.textKey)}
                        </a>
                      </Button>
                    </div>
                  )}
                </div>
              ) : (
                <>
                  <div className="max-w-4xl mx-auto space-y-4">
                    {t(section.contentKey)
                      .split("\n\n")
                      .map((paragraph: string, i: number) => (
                        <p
                          key={i}
                          className={`text-base md:text-lg lg:text-xl leading-relaxed ${scheme.text} text-center`}
                        >
                          {paragraph}
                        </p>
                      ))}
                  </div>

                  {section.cta && (
                    <div className="mt-6 flex justify-center">
                      {"href" in section.cta && section.cta.href ? (
                        <Button
                          asChild
                          className={`btn-primary ${
                            scheme.accent === "text-red-600"
                              ? "bg-red-600 hover:bg-red-700"
                              : scheme.accent === "text-black"
                              ? "bg-black hover:bg-gray-900"
                              : "bg-white hover:bg-gray-100"
                          } ${
                            scheme.accent === "text-white"
                              ? "text-black"
                              : "text-white"
                          } px-3 md:px-8 py-3 md:py-4 text-sm md:text-base shadow-lg max-w-full`}
                        >
                          <a
                            href={section.cta.href}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="flex items-center justify-center"
                          >
                            {getButtonIcon(section.cta.href)}
                            {t(section.cta.textKey)}
                          </a>
                        </Button>
                      ) : (
                        <Button
                          onClick={"action" in section.cta ? section.cta.action : undefined}
                          className={`btn-primary ${
                            scheme.accent === "text-red-600"
                              ? "bg-red-600 hover:bg-red-700"
                              : scheme.accent === "text-black"
                              ? "bg-black hover:bg-gray-900"
                              : "bg-white hover:bg-gray-100"
                          } ${
                            scheme.accent === "text-white"
                              ? "text-black"
                              : "text-white"
                          } px-3 md:px-8 py-3 md:py-4 text-sm md:text-base shadow-lg flex items-center justify-center max-w-full`}
                        >
                          <MapPin className="w-4 h-4 mr-2" />
                          {t(section.cta.textKey)}
                        </Button>
                      )}
                    </div>
                  )}
                </>
              )}
            </div>
          </div>
        )
      })}
    </div>
  )
}
