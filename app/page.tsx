import { StickyHeader } from "@/components/sticky-header"
import { HeroSection } from "@/components/hero-section"
import { AccordionSections } from "@/components/accordion-sections"
import { SiteFooter } from "@/components/site-footer"
import { CookieBanner } from "@/components/cookie-banner"

export default function HomePage() {
  return (
    <>
      <StickyHeader />
      <main className="min-h-screen">
        <HeroSection />
        <AccordionSections />
      </main>
      <SiteFooter />
      <CookieBanner />
    </>
  )
}
