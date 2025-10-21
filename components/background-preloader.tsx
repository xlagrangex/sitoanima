"use client"

import { useBackgroundPreload } from "@/hooks/useBackgroundPreload"

export function BackgroundPreloader() {
  useBackgroundPreload()
  return null // This component doesn't render anything
}
