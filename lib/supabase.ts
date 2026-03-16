import { createClient } from '@supabase/supabase-js'

const supabaseUrl = process.env.NEXT_PUBLIC_SUPABASE_URL!
const supabaseAnonKey = process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY!

export const supabase = createClient(supabaseUrl, supabaseAnonKey)

// Types for CMS
export interface SectionContent {
  id: string
  key: string
  value_en: string
  value_it: string
  updated_at: string
}

export interface SectionImage {
  id: string
  section: string
  image_url: string
  alt_text: string
  display_order: number
  metadata: Record<string, string>
  created_at: string
  updated_at: string
}

export interface SiteSetting {
  id: string
  key: string
  value: string
  updated_at: string
}
