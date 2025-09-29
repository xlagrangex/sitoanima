"use client"

import { useState, useEffect } from "react"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { X } from "lucide-react"

export function CookieBanner() {
  const [showBanner, setShowBanner] = useState(false)

  useEffect(() => {
    const consent = localStorage.getItem("cookie-consent")
    if (!consent) {
      setShowBanner(true)
    }
  }, [])

  const acceptCookies = () => {
    localStorage.setItem("cookie-consent", "accepted")
    setShowBanner(false)
  }

  const rejectCookies = () => {
    localStorage.setItem("cookie-consent", "rejected")
    setShowBanner(false)
  }

  if (!showBanner) return null

  return (
    <div className="fixed bottom-4 left-4 right-4 z-50 max-w-md mx-auto">
      <Card>
        <CardContent className="p-4">
          <div className="flex justify-between items-start mb-3">
            <h3 className="font-semibold text-sm">Cookie Policy</h3>
            <Button variant="ghost" size="icon" className="h-6 w-6" onClick={rejectCookies}>
              <X className="h-4 w-4" />
            </Button>
          </div>
          <p className="text-xs text-muted-foreground mb-4 leading-relaxed">
            Utilizziamo cookies per migliorare la tua esperienza e per scopi analitici. Continuando la navigazione
            accetti il loro utilizzo.
          </p>
          <div className="flex gap-2">
            <Button size="sm" onClick={acceptCookies} className="flex-1">
              Accetta
            </Button>
            <Button size="sm" variant="outline" onClick={rejectCookies} className="flex-1 bg-transparent">
              Rifiuta
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}
