import { supabase } from "./supabase"
import type { SectionContent, SectionImage, SiteSetting } from "./supabase"

// Cache per evitare fetch multipli nella stessa sessione
let contentCache: SectionContent[] | null = null
let imagesCache: SectionImage[] | null = null
let settingsCache: SiteSetting[] | null = null

export async function fetchContent(): Promise<SectionContent[]> {
  if (contentCache) return contentCache
  if (!supabase) return []
  const { data, error } = await supabase
    .from("section_content")
    .select("*")
    .order("key")
  if (error || !data) return []
  contentCache = data
  return data
}

export async function fetchImages(section?: string): Promise<SectionImage[]> {
  if (imagesCache && !section) return imagesCache
  if (!supabase) return []
  let query = supabase
    .from("section_images")
    .select("*")
    .order("display_order")
  if (section) query = query.eq("section", section)
  const { data, error } = await query
  if (error || !data) return []
  if (!section) imagesCache = data
  return data
}

export async function fetchSettings(): Promise<SiteSetting[]> {
  if (settingsCache) return settingsCache
  if (!supabase) return []
  const { data, error } = await supabase
    .from("site_settings")
    .select("*")
    .order("key")
  if (error || !data) return []
  settingsCache = data
  return data
}

export function getSetting(settings: SiteSetting[], key: string): string {
  return settings.find((s) => s.key === key)?.value ?? ""
}

export function invalidateCache() {
  contentCache = null
  imagesCache = null
  settingsCache = null
}

/**
 * Build a translations map from CMS content for a given language.
 */
export function buildTranslations(
  content: SectionContent[],
  language: "en" | "it"
): Record<string, string> {
  const map: Record<string, string> = {}
  for (const item of content) {
    map[item.key] = language === "en" ? item.value_en : item.value_it
  }
  return map
}
