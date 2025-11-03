"use client"

import { Button } from "@/components/ui/button"
import Image from "next/image"
import { useLanguage } from "@/contexts/LanguageContext"
import { useEffect, useRef, useState } from "react"

export function HeroSection() {
  const { t } = useLanguage()
  const videoRef = useRef<HTMLVideoElement>(null)
  const heroSectionRef = useRef<HTMLElement>(null)
  const interactionOverlayRef = useRef<HTMLDivElement>(null)
  const [showFallback, setShowFallback] = useState(true)
  const [minTimeElapsed, setMinTimeElapsed] = useState(false)
  
  // Timer minimo di 1.5 secondi per il precaricamento
  useEffect(() => {
    const timer = setTimeout(() => {
      setMinTimeElapsed(true)
      // Se il video sta già riproducendo, nascondi il fallback
      if (videoRef.current && !videoRef.current.paused) {
        setShowFallback(false)
      }
    }, 1500)
    
    return () => clearTimeout(timer)
  }, [])
  
  useEffect(() => {
    const video = videoRef.current
    if (!video) return

    let retryInterval: NodeJS.Timeout | null = null
    let retryCount = 0
    const MAX_RETRIES = 100 // Aumentato per modalità risparmio energetico
    let isPlayingSuccessfully = false

    // Reset del video per risolvere problemi di cache su dispositivi mobili
    video.load()
    video.currentTime = 0
    video.muted = true
    video.volume = 0
    video.autoplay = true

    // Rileva Chrome mobile per disabilitare strategie che interferiscono con lo scroll
    const isChromeMobile = typeof navigator !== 'undefined' && 
      /Chrome/.test(navigator.userAgent) && 
      /Mobile|Android|iPhone|iPad/.test(navigator.userAgent)

    // Funzione per simulare interazione sulla hero section (per sbloccare autoplay iOS)
    const simulateInteraction = () => {
      if (!heroSectionRef.current || !video) return

      // Su Chrome mobile, SKIP completamente lo scroll minimo perché causa bug di scroll
      // quando si cambia direzione. Usa solo click/touch che non interferiscono.
      if (!isChromeMobile) {
        // Strategia 1: Scroll minimo impercettibile (solo su browser non-Chrome mobile)
        try {
          const currentScroll = window.pageYOffset
          // Usa requestAnimationFrame per evitare conflitti con scroll nativo
          requestAnimationFrame(() => {
            window.scrollTo({ top: currentScroll + 0.1, behavior: 'auto' })
            setTimeout(() => {
              window.scrollTo({ top: currentScroll, behavior: 'auto' })
            }, 10)
          })
        } catch (e) {}
      }

      // Strategia 2: Prova click diretto sull'overlay (più probabile che funzioni)
      try {
        if (interactionOverlayRef.current) {
          // Usa requestAnimationFrame per rendere il click più "naturale"
          requestAnimationFrame(() => {
            interactionOverlayRef.current?.click()
          })
        }
      } catch (e) {}

      // Strategia 3: Click diretto sulla hero section
      try {
        requestAnimationFrame(() => {
          heroSectionRef.current?.click()
        })
      } catch (e) {}

      // Strategia 4: TouchEvent simulato (più "naturale" per iOS)
      try {
        if ('TouchEvent' in window && heroSectionRef.current) {
          requestAnimationFrame(() => {
            const touchObj = new Touch({
              identifier: Date.now(),
              target: heroSectionRef.current!,
              clientX: window.innerWidth / 2,
              clientY: window.innerHeight / 2,
              radiusX: 2.5,
              radiusY: 2.5,
              rotationAngle: 0,
              force: 0.5
            })
            const touchEvent = new TouchEvent('touchstart', {
              bubbles: true,
              cancelable: true,
              view: window,
              touches: [touchObj]
            })
            heroSectionRef.current.dispatchEvent(touchEvent)
            
            setTimeout(() => {
              const touchEndEvent = new TouchEvent('touchend', {
                bubbles: true,
                cancelable: true,
                view: window,
                changedTouches: [touchObj]
              })
              heroSectionRef.current?.dispatchEvent(touchEndEvent)
              
              // Dopo il touch, prova immediatamente il play
              setTimeout(() => {
                if (video && video.paused) {
                  video.play().catch(() => {})
                }
              }, 10)
            }, 50)
          })
        }
      } catch (e) {}

      // Strategia 5: Focus sulla hero section
      try {
        if (heroSectionRef.current instanceof HTMLElement) {
          heroSectionRef.current.focus()
        }
      } catch (e) {}
    }

    // Funzione AGRESSIVA per forzare la riproduzione (anche in modalità risparmio energetico)
    const attemptPlay = () => {
      if (!video || (isPlayingSuccessfully && !video.paused)) return

      // Preparazione aggressiva per sbloccare autoplay
      video.muted = true
      video.volume = 0
      video.autoplay = true
      
      // Su iOS potrebbe servire ricaricare
      if (video.readyState < 2) {
        video.load()
      }

      const playPromise = video.play()
      if (playPromise !== undefined) {
        playPromise
          .then(() => {
            isPlayingSuccessfully = true
            retryCount = 0
            if (retryInterval) {
              clearInterval(retryInterval)
              retryInterval = null
            }
            if (minTimeElapsed) {
              setShowFallback(false)
            }
          })
          .catch((error) => {
            retryCount++
            // Se dopo 5 tentativi falliti, prova a simulare interazione
            if (retryCount === 5 || retryCount === 10 || retryCount === 20) {
              simulateInteraction()
            }
            // Retry continuo anche in modalità risparmio energetico
            if (retryCount < MAX_RETRIES) {
              // Retry più frequenti per forzare anche con risparmio energetico
              setTimeout(() => {
                if (video && video.paused) {
                  attemptPlay()
                }
              }, 100)
            }
          })
      } else {
        // Browser che non supportano Promise, verifica manualmente
        setTimeout(() => {
          if (video && video.paused) {
            attemptPlay()
          }
        }, 200)
      }
    }

    // Retry continuo per forzare anche in modalità risparmio energetico
    const startContinuousRetry = () => {
      if (retryInterval) return
      
      retryInterval = setInterval(() => {
        if (video && video.paused && !isPlayingSuccessfully) {
          attemptPlay()
        }
      }, 500) // Check ogni 500ms
    }

    // Gestione quando il video è pronto
    const handleVideoReady = () => {
      attemptPlay()
      startContinuousRetry()
    }

    // Event listener per quando il video inizia a riprodurre
    const handlePlaying = () => {
      isPlayingSuccessfully = true
      if (retryInterval) {
        clearInterval(retryInterval)
        retryInterval = null
      }
      if (minTimeElapsed) {
        setShowFallback(false)
      }
    }

    // Event listener per quando il video si mette in pausa (anche automaticamente)
    const handlePause = () => {
      // Se non è stato terminato e non è stato messo in pausa manualmente, riprova
      if (!video.ended && !document.hidden) {
        setTimeout(() => {
          attemptPlay()
        }, 100)
      }
    }

    video.addEventListener('play', handlePlaying)
    video.addEventListener('playing', handlePlaying)
    video.addEventListener('pause', handlePause)

    // Prova immediatamente se il video è già caricato
    if (video.readyState >= 2) {
      setTimeout(handleVideoReady, 50)
    } else {
      video.addEventListener('loadeddata', handleVideoReady, { once: true })
      video.addEventListener('canplay', handleVideoReady, { once: true })
      video.addEventListener('canplaythrough', handleVideoReady, { once: true })
      video.addEventListener('loadedmetadata', handleVideoReady, { once: true })
    }

    // Gestione della visibilità della pagina
    const handleVisibilityChange = () => {
      if (!document.hidden && video.paused) {
        isPlayingSuccessfully = false
        attemptPlay()
        startContinuousRetry()
      }
    }

    document.addEventListener('visibilitychange', handleVisibilityChange)

    // Gestione quando la pagina diventa visibile
    const handleFocus = () => {
      if (video.paused) {
        isPlayingSuccessfully = false
        attemptPlay()
        startContinuousRetry()
      }
    }

    window.addEventListener('focus', handleFocus)

    // LISTENER AGGIUNTIVI PER SBLOCCO AUTOPLAY (anche in modalità risparmio energetico)
    // Qualsiasi interazione utente può sbloccare l'autoplay bloccato
    // NOTA: Rimosso 'scroll' e 'wheel' per evitare interferenze con lo scroll nativo su Chrome mobile
    const interactionEvents = [
      'touchstart',
      'touchend',
      'mousedown',
      'click',
      'keydown',
      'resize',
      'orientationchange'
    ]

    const handleUserInteraction = () => {
      if (video && video.paused) {
        isPlayingSuccessfully = false
        attemptPlay()
        startContinuousRetry()
      }
    }

    interactionEvents.forEach(event => {
      window.addEventListener(event, handleUserInteraction, { passive: true })
      document.addEventListener(event, handleUserInteraction, { passive: true })
    })

    // Avvia retry continuo dopo un breve delay
    setTimeout(() => {
      startContinuousRetry()
    }, 100)

    return () => {
      if (retryInterval) {
        clearInterval(retryInterval)
      }
      video.removeEventListener('play', handlePlaying)
      video.removeEventListener('playing', handlePlaying)
      video.removeEventListener('pause', handlePause)
      video.removeEventListener('loadeddata', handleVideoReady)
      video.removeEventListener('canplay', handleVideoReady)
      video.removeEventListener('canplaythrough', handleVideoReady)
      video.removeEventListener('loadedmetadata', handleVideoReady)
      document.removeEventListener('visibilitychange', handleVisibilityChange)
      window.removeEventListener('focus', handleFocus)
      interactionEvents.forEach(event => {
        window.removeEventListener(event, handleUserInteraction)
        document.removeEventListener(event, handleUserInteraction)
      })
    }
  }, [minTimeElapsed])
  
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
    <section 
      id="hero" 
      ref={heroSectionRef}
      className="relative flex items-center justify-center overflow-hidden w-screen bg-black" 
      style={{ height: '80vh' }}
    >
      {/* Overlay invisibile per sbloccare autoplay quando necessario */}
      <div
        ref={interactionOverlayRef}
        className="absolute inset-0 z-[3]"
        style={{ 
          opacity: 0,
          pointerEvents: 'auto',
          cursor: 'pointer',
          touchAction: 'auto',
          // Usa opacity minima invece di 0 per permettere interazioni
          // Alcuni browser bloccano interazioni su elementi con opacity: 0
          // opacity: 0.0001 potrebbe funzionare meglio
        }}
        onClick={(e) => {
          // Quando viene cliccato, forza il play
          e.preventDefault()
          e.stopPropagation()
          const video = videoRef.current
          if (video && video.paused) {
            video.muted = true
            video.play().catch(() => {})
          }
        }}
        onTouchStart={(e) => {
          // Intercetta touch per sbloccare autoplay
          e.preventDefault()
          e.stopPropagation()
          const video = videoRef.current
          if (video && video.paused) {
            video.muted = true
            video.play().catch(() => {})
          }
        }}
        onMouseDown={(e) => {
          // Intercetta anche mousedown
          const video = videoRef.current
          if (video && video.paused) {
            video.muted = true
            video.play().catch(() => {})
          }
        }}
      />
      {/* Fallback Image - Mostrata per almeno 1.5 secondi durante il precaricamento */}
      {showFallback && (
        <Image
          src="/Screenshot 2025-10-29 at 14.32.56.png"
          alt="ANIMA Background"
          fill
          className="absolute inset-0 w-full h-full object-cover transition-opacity duration-500"
          style={{ zIndex: 0 }}
          priority
          quality={90}
        />
      )}
      
      {/* Video Background */}
      <video
        ref={videoRef}
        autoPlay
        muted
        loop
        playsInline
        controls={false}
        preload="auto"
        className="absolute inset-0 w-full h-full object-cover pointer-events-none transition-opacity duration-500"
        style={{ zIndex: showFallback ? 1 : 0, opacity: showFallback ? 0 : 1 }}
        onLoadedData={(e) => {
          // Forza la riproduzione quando i dati sono caricati
          const video = e.currentTarget
          video.muted = true
          video.play()
            .then(() => {
              // Nascondi fallback solo se è passato il tempo minimo
              if (minTimeElapsed) {
                setShowFallback(false)
              }
            })
            .catch(() => {
              // Retry dopo breve delay per dispositivi mobili
              setTimeout(() => {
                video.play()
                  .then(() => {
                    if (minTimeElapsed) {
                      setShowFallback(false)
                    }
                  })
                  .catch(() => {})
              }, 100)
            })
        }}
        onCanPlay={(e) => {
          // Forza la riproduzione quando può essere riprodotto
          const video = e.currentTarget
          video.muted = true
          if (video.paused) {
            video.play()
              .then(() => {
                if (minTimeElapsed) {
                  setShowFallback(false)
                }
              })
              .catch(() => {
                setTimeout(() => {
                  video.play()
                    .then(() => {
                      if (minTimeElapsed) {
                        setShowFallback(false)
                      }
                    })
                    .catch(() => {})
                }, 100)
              })
          }
        }}
        onPlay={() => {
          // Assicurati che rimanga muto quando parte e nascondi fallback se tempo minimo passato
          const video = videoRef.current
          if (video && !video.muted) {
            video.muted = true
          }
          if (minTimeElapsed) {
            setShowFallback(false)
          }
        }}
        onPlaying={() => {
          // Conferma che il video sta riproducendo e nascondi fallback se tempo minimo passato
          if (minTimeElapsed) {
            setShowFallback(false)
          }
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
