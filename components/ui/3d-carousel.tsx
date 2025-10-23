"use client"

import { memo, useEffect, useLayoutEffect, useMemo, useState } from "react"
import {
  AnimatePresence,
  motion,
  useAnimation,
  useMotionValue,
  useTransform,
} from "framer-motion"

export const useIsomorphicLayoutEffect =
  typeof window !== "undefined" ? useLayoutEffect : useEffect

type UseMediaQueryOptions = {
  defaultValue?: boolean
  initializeWithValue?: boolean
}

const IS_SERVER = typeof window === "undefined"

export function useMediaQuery(
  query: string,
  {
    defaultValue = false,
    initializeWithValue = true,
  }: UseMediaQueryOptions = {}
): boolean {
  const getMatches = (query: string): boolean => {
    if (IS_SERVER) {
      return defaultValue
    }
    return window.matchMedia(query).matches
  }

  const [matches, setMatches] = useState<boolean>(() => {
    if (initializeWithValue) {
      return getMatches(query)
    }
    return defaultValue
  })

  const handleChange = () => {
    setMatches(getMatches(query))
  }

  useIsomorphicLayoutEffect(() => {
    const matchMedia = window.matchMedia(query)
    handleChange()

    matchMedia.addEventListener("change", handleChange)

    return () => {
      matchMedia.removeEventListener("change", handleChange)
    }
  }, [query])

  return matches
}

const animaImages = [
  "/charlotte-de-witte-dj-poster-dark-techno.webp",
  "/amelie-lens-dj-poster-techno-event.webp",
  "/ben-klock-dj-poster-underground-techno.webp",
  "/dj-performing-at-electronic-music-event-with-red-l.webp",
  "/electronic-music-event-crowd-with-purple-lights.webp",
  "/dj-mixing-on-cdj-turntables-with-neon-lights.webp",
  "/crowd-dancing-at-underground-techno-party.webp",
  "/electronic-music-crowd-dancing-purple-lights.webp",
  "/professional-dj-booth-with-cdj-and-mixer-purple-li.webp",
  "/dj-performing-electronic-music-purple-lighting.webp",
  "/techno-party-crowd-with-hands-up-dancing.webp",
  "/electronic-music-stage-with-led-visuals.webp",
  "/industrial-venue-interior-with-stage-and-purple-li.webp",
  "/concert-stage-with-professional-lighting-and-sound.webp",
]

const duration = 0.1
const transition = { duration, ease: [0.25, 0.46, 0.45, 0.94], filter: "blur(2px)" }
const transitionOverlay = { duration: 0.3, ease: [0.25, 0.46, 0.45, 0.94] }

const Carousel = memo(
  ({
    handleClick,
    controls,
    cards,
    isCarouselActive,
    rotation,
  }: {
    handleClick: (imgUrl: string, index: number) => void
    controls: any
    cards: string[]
    isCarouselActive: boolean
    rotation: any
  }) => {
    const isScreenSizeSm = useMediaQuery("(max-width: 640px)")
    const cylinderWidth = isScreenSizeSm ? 1600 : 3000
    const faceCount = cards.length
    const faceWidth = cylinderWidth / faceCount
    const radius = cylinderWidth / (2 * Math.PI)
    const rotationValue = useMotionValue(0)
    const transform = useTransform(
      rotationValue,
      (value) => `rotate3d(0, 1, 0, ${value}deg)`
    )

    return (
      <div
        className="flex h-full items-center justify-center bg-mauve-dark-2"
        style={{
          perspective: "1000px",
          transformStyle: "preserve-3d",
          willChange: "transform",
        }}
      >
        <motion.div
          drag={false}
          className="relative flex h-full origin-center justify-center"
        style={{
          transform,
          rotateY: rotationValue,
          width: cylinderWidth,
          transformStyle: "preserve-3d",
          willChange: "transform",
          backfaceVisibility: "hidden",
        }}
          animate={controls}
        >
          {cards.map((imgUrl, i) => (
            <motion.div
              key={`key-${imgUrl}-${i}`}
              className="absolute flex h-full origin-center items-center justify-center rounded-xl bg-mauve-dark-2 p-2"
              style={{
                width: `${faceWidth}px`,
                transform: `rotateY(${
                  i * (360 / faceCount)
                }deg) translateZ(${radius}px)`,
              }}
              onClick={() => handleClick(imgUrl, i)}
            >
              <motion.img
                src={imgUrl}
                alt={`keyword_${i} ${imgUrl}`}
                layoutId={`img-${imgUrl}`}
                className="pointer-events-none  w-full rounded-xl object-cover aspect-square"
                initial={{ filter: "blur(2px)" }}
                layout="position"
                animate={{ filter: "blur(0px)" }}
                transition={transition}
                whileHover={{ scale: 1.02 }}
                whileTap={{ scale: 0.98 }}
              />
            </motion.div>
          ))}
        </motion.div>
      </div>
    )
  }
)

const hiddenMask = `repeating-linear-gradient(to right, rgba(0,0,0,0) 0px, rgba(0,0,0,0) 30px, rgba(0,0,0,1) 30px, rgba(0,0,0,1) 30px)`
const visibleMask = `repeating-linear-gradient(to right, rgba(0,0,0,0) 0px, rgba(0,0,0,0) 0px, rgba(0,0,0,1) 0px, rgba(0,0,0,1) 30px)`
function ThreeDPhotoCarousel() {
  const [activeImg, setActiveImg] = useState<string | null>(null)
  const [isCarouselActive, setIsCarouselActive] = useState(true)
  const controls = useAnimation()
  const rotation = useMotionValue(0)
  const isMobile = useMediaQuery("(max-width: 768px)")
  
  // Auto-rotation for mobile
  useEffect(() => {
    if (!isMobile || !isCarouselActive) return
    
    const autoRotate = () => {
      if (isCarouselActive) {
        rotation.set(rotation.get() + 2) // Increased speed from 0.5 to 2
      }
    }
    
    const interval = setInterval(autoRotate, 30) // Increased frequency from 50ms to 30ms
    return () => clearInterval(interval)
  }, [isMobile, isCarouselActive, rotation])
  
  const cards = useMemo(
    () => animaImages,
    []
  )

  useEffect(() => {
    console.log("Cards loaded:", cards)
  }, [cards])

  const handleClick = (imgUrl: string) => {
    setActiveImg(imgUrl)
    setIsCarouselActive(false)
    controls.stop()
  }

  // Touch handlers for mobile - disable drag, open lightbox on tap
  const handleTouchStart = (e: React.TouchEvent) => {
    // Track touch start for potential lightbox opening
    e.stopPropagation()
  }

  const handleTouchMove = (e: React.TouchEvent) => {
    // Allow natural scrolling - no drag functionality
    e.stopPropagation()
  }

  const handleTouchEnd = (e: React.TouchEvent) => {
    // On mobile tap, open lightbox for the front item
    e.stopPropagation()
    if (isCarouselActive) {
      // Find the front item (closest to 0 degrees)
      const frontItemIndex = Math.round((360 - (rotation.get() % 360)) / (360 / cards.length)) % cards.length
      const frontImgUrl = cards[frontItemIndex]
      handleClick(frontImgUrl)
    }
  }

  const handleClose = () => {
    setActiveImg(null)
    setIsCarouselActive(true)
  }
  

  return (
    <motion.div layout className="relative">
      <AnimatePresence mode="sync">
        {activeImg && (
          <motion.div
            initial={{ opacity: 0, scale: 0 }}
            animate={{ opacity: 1, scale: 1 }}
            exit={{ opacity: 0, scale: 0 }}
            layoutId={`img-container-${activeImg}`}
            layout="position"
            onClick={handleClose}
            className="fixed inset-0 bg-black bg-opacity-10 flex items-center justify-center z-50 m-5 md:m-36 lg:mx-[19rem] rounded-3xl"
            style={{ willChange: "opacity" }}
            transition={transitionOverlay}
          >
            <motion.img
              layoutId={`img-${activeImg}`}
              src={activeImg}
              className="max-w-full max-h-full rounded-lg shadow-lg"
              initial={{ scale: 0.5 }} // Start with a smaller scale
              animate={{ scale: 1 }} // Animate to full scale
              transition={{
                delay: 0.5,
                duration: 0.5,
                ease: [0.25, 0.1, 0.25, 1],
              }} // Clean ease-out curve
              style={{
                willChange: "transform",
              }}
            />
          </motion.div>
        )}
      </AnimatePresence>
      <div 
        className="relative h-[700px] md:h-[900px] w-full overflow-hidden"
        onTouchStart={isMobile ? handleTouchStart : undefined}
        onTouchMove={isMobile ? handleTouchMove : undefined}
        onTouchEnd={isMobile ? handleTouchEnd : undefined}
      >
        <Carousel
          handleClick={handleClick}
          controls={controls}
          cards={cards}
          isCarouselActive={isCarouselActive}
          rotation={rotation}
        />
        
      </div>
    </motion.div>
  )
}

export { ThreeDPhotoCarousel };
