"use client"

import { useState, useEffect } from 'react'

export function useDeviceDetection() {
  const [isAndroid, setIsAndroid] = useState(false)
  const [isMobile, setIsMobile] = useState(false)
  const [isIOS, setIsIOS] = useState(false)

  useEffect(() => {
    if (typeof window === 'undefined') return

    const userAgent = navigator.userAgent.toLowerCase()
    
    setIsAndroid(/android/.test(userAgent))
    setIsIOS(/iphone|ipad|ipod/.test(userAgent))
    setIsMobile(/android|iphone|ipad|ipod|blackberry|iemobile|opera mini/.test(userAgent))
  }, [])

  return { isAndroid, isMobile, isIOS }
}

