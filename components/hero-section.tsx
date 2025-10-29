"use client"

import { Button } from "@/components/ui/button"
import Image from "next/image"
import { useLanguage } from "@/contexts/LanguageContext"
import { useEffect, useRef } from "react"

export function HeroSection() {
  const { t } = useLanguage()
  const videoRef = useRef<HTMLVideoElement>(null)
  
  useEffect(() => {
    const video = videoRef.current
    if (!video) return

    // DISABILITA COMPLETAMENTE TUTTI I CONTROLLI
    video.controls = false
    video.removeAttribute('controls')
    video.setAttribute('controls', 'false')
    video.setAttribute('controlslist', 'nodownload nofullscreen noremoteplayback')
    video.setAttribute('disablePictureInPicture', 'true')
    video.setAttribute('disableRemotePlayback', 'true')
    
    // Nascondi tutti i controlli via JavaScript (doppia sicurezza)
    const hideControls = () => {
      video.controls = false
      video.removeAttribute('controls')
      
      // Rimuovi eventuali controlli aggiunti dal browser
      const controls = video.querySelectorAll('*')
      controls.forEach((el: any) => {
        if (el.classList && (
          el.classList.contains('controls') ||
          el.classList.contains('media-controls') ||
          el.classList.contains('play-button') ||
          el.classList.contains('pause-button')
        )) {
          el.style.display = 'none'
          el.remove()
        }
      })
    }

    // Reset del video per risolvere problemi di cache su dispositivi mobili
    // Questo è cruciale per dispositivi che hanno già visitato il sito
    video.load()
    video.currentTime = 0
    hideControls()

    // Funzione per forzare la riproduzione SENZA MAI mostrare controlli
    const attemptPlay = () => {
      // Assicurati che il video sia muto (richiesto per autoplay)
      video.muted = true
      video.controls = false
      hideControls()
      
      const playPromise = video.play()
      if (playPromise !== undefined) {
        playPromise
          .then(() => {
            console.log("Video playing successfully")
            // Nascondi controlli anche dopo che parte
            hideControls()
            video.controls = false
          })
          .catch((error) => {
            console.log("Autoplay prevented:", error)
            hideControls()
            // Su mobile, prova più volte con delay crescenti
            setTimeout(() => {
              video.muted = true
              video.controls = false
              video.play().catch(() => {
                hideControls()
                setTimeout(() => {
                  video.muted = true
                  video.controls = false
                  video.play().catch(() => {
                    hideControls()
                    console.log("Multiple autoplay attempts failed")
                  })
                }, 300)
              })
            }, 200)
          })
      }
    }

    // Gestione quando il video è pronto
    const handleVideoReady = () => {
      hideControls()
      video.controls = false
      if (video.paused) {
        attemptPlay()
      }
    }

    // Observer per nascondere eventuali controlli aggiunti dinamicamente
    const observer = new MutationObserver(() => {
      hideControls()
      video.controls = false
    })

    observer.observe(video, {
      childList: true,
      subtree: true,
      attributes: true,
      attributeFilter: ['controls', 'class']
    })

    // Prova immediatamente se il video è già caricato
    if (video.readyState >= 2) {
      setTimeout(handleVideoReady, 50)
    } else {
      // Aspetta che il video sia pronto
      video.addEventListener('loadeddata', handleVideoReady, { once: true })
      video.addEventListener('canplay', handleVideoReady, { once: true })
      video.addEventListener('canplaythrough', handleVideoReady, { once: true })
    }

    // Gestione della visibilità della pagina (importante per mobile quando si torna al tab)
    const handleVisibilityChange = () => {
      if (!document.hidden && video.paused) {
        attemptPlay()
      }
    }

    document.addEventListener('visibilitychange', handleVisibilityChange)

    // Gestione quando la pagina diventa visibile (per mobile)
    const handleFocus = () => {
      if (video.paused) {
        attemptPlay()
      }
    }

    window.addEventListener('focus', handleFocus)

    // Controlla periodicamente che i controlli non appaiano
    const controlCheckInterval = setInterval(() => {
      hideControls()
      video.controls = false
    }, 100)

    return () => {
      video.removeEventListener('loadeddata', handleVideoReady)
      video.removeEventListener('canplay', handleVideoReady)
      video.removeEventListener('canplaythrough', handleVideoReady)
      document.removeEventListener('visibilitychange', handleVisibilityChange)
      window.removeEventListener('focus', handleFocus)
      observer.disconnect()
      clearInterval(controlCheckInterval)
    }
  }, [])
  
  const scrollToFormat = () => {
    const element = document.getElementById("format")
    if (!element) return
    
    const isMobile = window.innerWidth < 768
    
    if (isMobile) {
      // On mobile, calculate offset to account for browser address bar
      const headerHeight = 80 // Approximate height of browser bar + offset
      const elementPosition = element.getBoundingClientRect().top + window.pageYOffset
      const offsetPosition = elementPosition - headerHeight
      
      window.scrollTo({
        top: offsetPosition,
        behavior: "auto"
      })
    } else {
      element.scrollIntoView({ behavior: "smooth" })
    }
  }

  return (
    <section id="hero" className="relative flex items-center justify-center overflow-hidden w-screen bg-black" style={{ height: '80vh' }}>
      {/* Video Background */}
      <video
        ref={videoRef}
        autoPlay
        muted
        loop
        playsInline
        controls={false}
        disablePictureInPicture
        disableRemotePlayback
        controlsList="nodownload nofullscreen noremoteplayback"
        preload="auto"
        className="absolute inset-0 w-full h-full object-cover pointer-events-none"
        style={{ zIndex: 0 }}
        onLoadedData={(e) => {
          // Forza la riproduzione quando i dati sono caricati
          const video = e.currentTarget
          video.muted = true
          video.controls = false
          video.removeAttribute('controls')
          video.play().catch(() => {
            // Retry dopo breve delay per dispositivi mobili
            setTimeout(() => {
              video.muted = true
              video.controls = false
              video.play().catch(() => {})
            }, 100)
          })
        }}
        onCanPlay={(e) => {
          // Forza la riproduzione quando può essere riprodotto
          const video = e.currentTarget
          video.muted = true
          video.controls = false
          video.removeAttribute('controls')
          if (video.paused) {
            video.play().catch(() => {
              setTimeout(() => {
                video.muted = true
                video.controls = false
                video.play().catch(() => {})
              }, 100)
            })
          }
        }}
        onPlay={() => {
          // Assicurati che rimanga muto e senza controlli quando parte
          const video = videoRef.current
          if (video) {
            video.muted = true
            video.controls = false
            video.removeAttribute('controls')
          }
        }}
        onContextMenu={(e) => {
          // Previeni menu contestuale su video
          e.preventDefault()
          return false
        }}
      >
        <source src="/video2-optimized.mp4" type="video/mp4" />
      </video>

      {/* Overlay semitrasparente */}
      <div className="absolute inset-0 bg-black/50 z-[1]"></div>

      {/* Content */}
      <div className="relative z-[2] text-center text-white px-4 max-w-2xl">
        <div className="flex justify-center mb-6">
          <Image
            src="/anima-complete-white-optimized.webp"
            alt="ANIMA - Until the Sunrise"
            width={400}
            height={150}
            className="w-full max-w-md h-auto drop-shadow-2xl"
            priority
          />
        </div>

        <p className="text-base md:text-lg mb-6 leading-relaxed max-w-xl mx-auto">
          {t('hero.tagline')}<br />
          {t('hero.tagline2')}
        </p>
      </div>

      <div className="absolute left-4 top-1/2 -translate-y-1/2 w-1 h-32 bg-white/30 hidden lg:block z-[2]" />
      <div className="absolute right-4 top-1/2 -translate-y-1/2 w-1 h-32 bg-white/30 hidden lg:block z-[2]" />
    </section>
  )
}
