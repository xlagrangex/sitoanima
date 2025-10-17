import type React from "react"
import type { Metadata } from "next"
import { Analytics } from "@vercel/analytics/next"
import "./globals.css"
import { Suspense } from "react"
import { LanguageProvider } from "@/contexts/LanguageContext"
import { LanguageSwitcher } from "@/components/language-switcher"
import { Preloader } from "@/components/preloader"

export const metadata: Metadata = {
  title: "ANIMA – Until the Sun Rises | Electronic Music Events",
  description:
    "Join us for an unforgettable night of electronic music. ANIMA presents cutting-edge DJs and immersive experiences until the sun rises.",
  generator: "v0.app",
  keywords: "electronic music, DJ, nightlife, events, ANIMA, underground",
  icons: {
    icon: "/anima-logo-white.png",
    shortcut: "/anima-logo-white.png",
    apple: "/anima-logo-white.png",
  },
  openGraph: {
    title: "ANIMA – Until the Sun Rises",
    description: "Join us for an unforgettable night of electronic music",
    type: "website",
    images: ["/electronic-music-event-poster.png"],
  },
  twitter: {
    card: "summary_large_image",
    title: "ANIMA – Until the Sun Rises",
    description: "Join us for an unforgettable night of electronic music",
  },
}

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode
}>) {
  return (
    <html lang="en">
      <body className="font-avenir">
        <Preloader />
        <LanguageProvider>
          <Suspense fallback={null}>{children}</Suspense>
          <LanguageSwitcher />
          <Analytics />
        </LanguageProvider>
      </body>
    </html>
  )
}
