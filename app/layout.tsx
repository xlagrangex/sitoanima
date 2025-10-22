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
    "ANIMA is more than a party — it's a ritual of sound and light. Guided by every shade of house music, the dancefloor becomes a place of pure expression, free from judgment and filled with unity.",
  generator: "Next.js",
  keywords: "ANIMA, electronic music, house music, afrohouse, DJ, nightlife, events, underground, club, HBToo, Naples",
  authors: [{ name: "ANIMA Events" }],
  creator: "ANIMA Events",
  publisher: "ANIMA Events",
  icons: {
    icon: "/anima-logo-white.webp",
    shortcut: "/anima-logo-white.webp",
    apple: "/anima-logo-white.webp",
  },
  openGraph: {
    title: "ANIMA – Until the Sun Rises",
    description: "ANIMA is more than a party — it's a ritual of sound and light. Guided by every shade of house music, the dancefloor becomes a place of pure expression, free from judgment and filled with unity.",
    type: "website",
    url: "https://anima.ent",
    siteName: "ANIMA",
    images: [
      {
        url: "/anima-complete-white.webp",
        width: 1200,
        height: 630,
        alt: "ANIMA - Until the Sun Rises",
      },
    ],
  },
  twitter: {
    card: "summary_large_image",
    title: "ANIMA – Until the Sun Rises",
    description: "ANIMA is more than a party — it's a ritual of sound and light. Guided by every shade of house music, the dancefloor becomes a place of pure expression, free from judgment and filled with unity.",
    images: ["/anima-complete-white.webp"],
  },
  robots: {
    index: true,
    follow: true,
    googleBot: {
      index: true,
      follow: true,
      "max-video-preview": -1,
      "max-image-preview": "large",
      "max-snippet": -1,
    },
  },
  verification: {
    google: "your-google-verification-code",
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
