"use client"

import { useState, useEffect, useCallback } from "react"
import { supabase } from "@/lib/supabase"
import type { SectionContent, SectionImage, SiteSetting } from "@/lib/supabase"
import { MediaLibrary, type MediaItem } from "@/components/admin/media-library"
import { optimizeImage, formatFileSize } from "@/lib/image-utils"

type Tab = "content" | "images" | "settings" | "media"

// Section grouping for content editor
const SECTION_GROUPS = [
  { label: "Hero", keys: ["hero.tagline", "hero.tagline2", "hero.cta"] },
  { label: "Format", keys: ["section1.subtitle", "section1.title", "section1.content"] },
  { label: "Venue", keys: ["section2.subtitle", "section2.title", "section2.content", "section2.cta"] },
  { label: "Shows", keys: ["section3.subtitle", "section3.title", "section3.content", "section3.cta"] },
  { label: "Guests", keys: ["section4.subtitle", "section4.title", "section4.content"] },
  { label: "Media", keys: ["section6.subtitle", "section6.title", "section6.content", "section6.cta"] },
  { label: "Playlist", keys: ["section7.subtitle", "section7.title", "section7.content", "section7.cta"] },
  { label: "Collaborazioni", keys: ["section8.subtitle", "section8.title", "section8.content", "section8.cta"] },
  { label: "Guest Lists", keys: ["section9.subtitle", "section9.title", "section9.content", "section9.cta"] },
  { label: "Location", keys: ["section10.subtitle", "section10.title", "section10.content", "section10.cta"] },
  { label: "Instagram", keys: ["section11.subtitle", "section11.title", "section11.content", "section11.cta"] },
  { label: "Navigation", keys: ["nav.format", "nav.venue", "nav.shows", "nav.guests", "nav.media", "nav.instagram", "nav.contact"] },
  { label: "Footer", keys: ["footer.tagline", "footer.description", "footer.quicklinks", "footer.contact", "footer.social", "footer.rights"] },
  { label: "Cookie Banner", keys: ["cookie.title", "cookie.message", "cookie.accept", "cookie.reject"] },
]

const IMAGE_SECTIONS = [
  { key: "hero", label: "Hero (Background & Logo)" },
  { key: "shows", label: "Shows (Locandina Evento)" },
  { key: "media", label: "Media Gallery" },
  { key: "guests", label: "Guest DJs" },
  { key: "guests_feature", label: "Guest Feature Image" },
]

const SETTING_GROUPS = [
  { label: "Links & CTA", keys: ["eventbrite_url", "telegram_url", "spotify_playlist_url", "spotify_embed_url", "whatsapp_url", "email", "instagram_url", "google_maps_url", "google_maps_embed"] },
]

export default function AdminPage() {
  const [isAuthenticated, setIsAuthenticated] = useState(false)
  const [password, setPassword] = useState("")
  const [loginError, setLoginError] = useState("")
  const [activeTab, setActiveTab] = useState<Tab>("content")
  const [content, setContent] = useState<SectionContent[]>([])
  const [images, setImages] = useState<SectionImage[]>([])
  const [settings, setSettings] = useState<SiteSetting[]>([])
  const [loading, setLoading] = useState(false)
  const [saving, setSaving] = useState<string | null>(null)
  const [toast, setToast] = useState<{ message: string; type: "success" | "error" } | null>(null)
  const [expandedGroup, setExpandedGroup] = useState<string | null>(null)
  const [expandedImageSection, setExpandedImageSection] = useState<string | null>(null)
  const [uploadingFor, setUploadingFor] = useState<string | null>(null)
  const [mediaLibraryOpen, setMediaLibraryOpen] = useState(false)
  const [mediaLibraryTarget, setMediaLibraryTarget] = useState<{
    imageId: string
    section: string
  } | null>(null)

  const showToast = (message: string, type: "success" | "error" = "success") => {
    setToast({ message, type })
    setTimeout(() => setToast(null), 3000)
  }

  const handleLogin = async () => {
    const { data } = await supabase
      .from("site_settings")
      .select("value")
      .eq("key", "admin_password")
      .single()

    if (data && data.value === password) {
      setIsAuthenticated(true)
      setLoginError("")
      sessionStorage.setItem("admin_auth", "true")
    } else {
      setLoginError("Password errata")
    }
  }

  const loadData = useCallback(async () => {
    setLoading(true)
    const [contentRes, imagesRes, settingsRes] = await Promise.all([
      supabase.from("section_content").select("*").order("key"),
      supabase.from("section_images").select("*").order("section").order("display_order"),
      supabase.from("site_settings").select("*").order("key"),
    ])
    if (contentRes.data) setContent(contentRes.data)
    if (imagesRes.data) setImages(imagesRes.data)
    if (settingsRes.data) setSettings(settingsRes.data)
    setLoading(false)
  }, [])

  useEffect(() => {
    if (sessionStorage.getItem("admin_auth") === "true") {
      setIsAuthenticated(true)
    }
  }, [])

  useEffect(() => {
    if (isAuthenticated) loadData()
  }, [isAuthenticated, loadData])

  // --- Content handlers ---
  const updateContent = async (id: string, field: "value_en" | "value_it", value: string) => {
    setContent((prev) =>
      prev.map((c) => (c.id === id ? { ...c, [field]: value } : c))
    )
  }

  const saveContent = async (item: SectionContent) => {
    setSaving(item.id)
    const { error } = await supabase
      .from("section_content")
      .update({ value_en: item.value_en, value_it: item.value_it, updated_at: new Date().toISOString() })
      .eq("id", item.id)
    setSaving(null)
    if (error) {
      showToast("Errore nel salvataggio: " + error.message, "error")
    } else {
      showToast(`"${item.key}" salvato!`)
    }
  }

  // --- Settings handlers ---
  const updateSetting = (id: string, value: string) => {
    setSettings((prev) =>
      prev.map((s) => (s.id === id ? { ...s, value } : s))
    )
  }

  const saveSetting = async (item: SiteSetting) => {
    setSaving(item.id)
    const { error } = await supabase
      .from("site_settings")
      .update({ value: item.value, updated_at: new Date().toISOString() })
      .eq("id", item.id)
    setSaving(null)
    if (error) {
      showToast("Errore: " + error.message, "error")
    } else {
      showToast(`"${item.key}" salvato!`)
    }
  }

  // --- Image handlers ---
  const updateImageField = (id: string, field: string, value: string) => {
    setImages((prev) =>
      prev.map((img) => {
        if (img.id !== id) return img
        if (field.startsWith("metadata.")) {
          const metaKey = field.replace("metadata.", "")
          return { ...img, metadata: { ...img.metadata, [metaKey]: value } }
        }
        return { ...img, [field]: value }
      })
    )
  }

  const saveImage = async (item: SectionImage) => {
    setSaving(item.id)
    const { error } = await supabase
      .from("section_images")
      .update({
        image_url: item.image_url,
        alt_text: item.alt_text,
        display_order: item.display_order,
        metadata: item.metadata,
        updated_at: new Date().toISOString(),
      })
      .eq("id", item.id)
    setSaving(null)
    if (error) {
      showToast("Errore: " + error.message, "error")
    } else {
      showToast(`Immagine "${item.alt_text || item.section}" salvata!`)
    }
  }

  const deleteImage = async (id: string) => {
    if (!confirm("Sei sicuro di voler eliminare questa immagine?")) return
    const { error } = await supabase.from("section_images").delete().eq("id", id)
    if (error) {
      showToast("Errore: " + error.message, "error")
    } else {
      setImages((prev) => prev.filter((img) => img.id !== id))
      showToast("Immagine eliminata!")
    }
  }

  const addImage = async (section: string) => {
    const maxOrder = images
      .filter((img) => img.section === section)
      .reduce((max, img) => Math.max(max, img.display_order), 0)

    const { data, error } = await supabase
      .from("section_images")
      .insert({
        section,
        image_url: "",
        alt_text: "",
        display_order: maxOrder + 1,
        metadata: section === "guests" ? { name: "", designation: "", quote: "" } : {},
      })
      .select()
      .single()

    if (error) {
      showToast("Errore: " + error.message, "error")
    } else if (data) {
      setImages((prev) => [...prev, data])
      showToast("Nuova immagine aggiunta!")
    }
  }

  const handleImageUpload = async (imageId: string, section: string, file: File) => {
    setUploadingFor(imageId)

    try {
      // Optimize image: convert to WebP + compress + resize
      const optimized = await optimizeImage(file)

      // Upload to Supabase Storage
      const storagePath = `${section}/${Date.now()}-${optimized.fileName}`

      const { error } = await supabase.storage
        .from("site-images")
        .upload(storagePath, optimized.blob, {
          cacheControl: "3600",
          upsert: false,
          contentType: optimized.blob.type,
        })

      if (error) {
        showToast("Errore upload: " + error.message, "error")
        setUploadingFor(null)
        return
      }

      const { data: urlData } = supabase.storage.from("site-images").getPublicUrl(storagePath)

      // Save to media library too
      await supabase.from("media_library").insert({
        file_name: optimized.fileName,
        original_name: file.name,
        file_url: urlData.publicUrl,
        file_size: optimized.optimizedSize,
        mime_type: optimized.blob.type,
        width: optimized.width || null,
        height: optimized.height || null,
        alt_text: file.name.replace(/\.[^.]+$/, "").replace(/[-_]/g, " "),
        folder: section,
      })

      // Update the image URL in state and database
      updateImageField(imageId, "image_url", urlData.publicUrl)
      await supabase
        .from("section_images")
        .update({ image_url: urlData.publicUrl, updated_at: new Date().toISOString() })
        .eq("id", imageId)

      const saved = formatFileSize(file.size - optimized.optimizedSize)
      showToast(`Immagine ottimizzata e caricata! (WebP, risparmiati ${saved})`)
    } catch (err) {
      showToast("Errore nell'ottimizzazione: " + String(err), "error")
    }

    setUploadingFor(null)
  }

  // Open media library to pick an image for a specific field
  const openMediaLibrary = (imageId: string, section: string) => {
    setMediaLibraryTarget({ imageId, section })
    setMediaLibraryOpen(true)
  }

  // Handle media library selection
  const handleMediaSelect = async (item: MediaItem) => {
    if (!mediaLibraryTarget) return

    updateImageField(mediaLibraryTarget.imageId, "image_url", item.file_url)
    await supabase
      .from("section_images")
      .update({ image_url: item.file_url, updated_at: new Date().toISOString() })
      .eq("id", mediaLibraryTarget.imageId)

    showToast("Immagine selezionata dalla libreria!")
    setMediaLibraryTarget(null)
  }

  // --- Login screen ---
  if (!isAuthenticated) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-950 px-4">
        <div className="w-full max-w-sm">
          <div className="bg-gray-900 rounded-2xl p-8 shadow-2xl border border-gray-800">
            <div className="text-center mb-8">
              <h1 className="text-2xl font-bold text-white mb-1">ANIMA</h1>
              <p className="text-gray-400 text-sm">Admin Panel</p>
            </div>
            <div className="space-y-4">
              <input
                type="password"
                placeholder="Password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                onKeyDown={(e) => e.key === "Enter" && handleLogin()}
                className="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500"
              />
              {loginError && (
                <p className="text-red-400 text-sm">{loginError}</p>
              )}
              <button
                onClick={handleLogin}
                className="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-3 rounded-lg transition-colors"
              >
                Accedi
              </button>
            </div>
          </div>
        </div>
      </div>
    )
  }

  // --- Main admin panel ---
  return (
    <div className="min-h-screen bg-gray-950">
      {/* Toast */}
      {toast && (
        <div
          className={`fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium transition-all ${
            toast.type === "success"
              ? "bg-green-600 text-white"
              : "bg-red-600 text-white"
          }`}
        >
          {toast.message}
        </div>
      )}

      {/* Header */}
      <header className="sticky top-0 z-40 bg-gray-900/95 backdrop-blur border-b border-gray-800">
        <div className="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <h1 className="text-lg font-bold text-white">ANIMA</h1>
            <span className="text-xs bg-red-600 text-white px-2 py-0.5 rounded">Admin</span>
          </div>
          <div className="flex items-center gap-2">
            <a
              href="/"
              target="_blank"
              className="text-xs text-gray-400 hover:text-white transition-colors px-3 py-1.5 rounded border border-gray-700 hover:border-gray-500"
            >
              Vedi Sito
            </a>
            <button
              onClick={() => {
                sessionStorage.removeItem("admin_auth")
                setIsAuthenticated(false)
              }}
              className="text-xs text-gray-400 hover:text-red-400 transition-colors px-3 py-1.5 rounded border border-gray-700 hover:border-red-700"
            >
              Esci
            </button>
          </div>
        </div>

        {/* Tabs */}
        <div className="max-w-7xl mx-auto px-4">
          <div className="flex gap-1">
            {(
              [
                { key: "content", label: "Contenuti" },
                { key: "images", label: "Immagini" },
                { key: "media", label: "Media Library" },
                { key: "settings", label: "Impostazioni" },
              ] as const
            ).map((tab) => (
              <button
                key={tab.key}
                onClick={() => setActiveTab(tab.key)}
                className={`px-4 py-2 text-sm font-medium rounded-t-lg transition-colors ${
                  activeTab === tab.key
                    ? "bg-gray-800 text-white"
                    : "text-gray-400 hover:text-white hover:bg-gray-800/50"
                }`}
              >
                {tab.label}
              </button>
            ))}
          </div>
        </div>
      </header>

      {/* Content */}
      <main className="max-w-7xl mx-auto px-4 py-6">
        {loading ? (
          <div className="flex items-center justify-center py-20">
            <div className="animate-spin h-8 w-8 border-2 border-red-500 border-t-transparent rounded-full" />
          </div>
        ) : activeTab === "content" ? (
          <ContentTab
            content={content}
            updateContent={updateContent}
            saveContent={saveContent}
            saving={saving}
            expandedGroup={expandedGroup}
            setExpandedGroup={setExpandedGroup}
          />
        ) : activeTab === "images" ? (
          <ImagesTab
            images={images}
            updateImageField={updateImageField}
            saveImage={saveImage}
            deleteImage={deleteImage}
            addImage={addImage}
            handleImageUpload={handleImageUpload}
            openMediaLibrary={openMediaLibrary}
            uploadingFor={uploadingFor}
            saving={saving}
            expandedSection={expandedImageSection}
            setExpandedSection={setExpandedImageSection}
          />
        ) : activeTab === "media" ? (
          <MediaTabStandalone />
        ) : (
          <SettingsTab
            settings={settings}
            updateSetting={updateSetting}
            saveSetting={saveSetting}
            saving={saving}
          />
        )}
      </main>

      {/* Media Library Popup */}
      <MediaLibrary
        isOpen={mediaLibraryOpen}
        onClose={() => {
          setMediaLibraryOpen(false)
          setMediaLibraryTarget(null)
        }}
        onSelect={handleMediaSelect}
        folder={mediaLibraryTarget?.section}
      />
    </div>
  )
}

// ============================================
// Media Tab (standalone browse view)
// ============================================
function MediaTabStandalone() {
  const [isOpen, setIsOpen] = useState(false)

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <p className="text-gray-400 text-sm">
          Gestisci tutti i media caricati. Le immagini vengono automaticamente convertite in WebP e compresse.
        </p>
        <button
          onClick={() => setIsOpen(true)}
          className="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-lg transition-colors"
        >
          Apri Media Library
        </button>
      </div>

      <MediaLibrary
        isOpen={isOpen}
        onClose={() => setIsOpen(false)}
        onSelect={(item) => {
          navigator.clipboard.writeText(item.file_url)
          setIsOpen(false)
        }}
      />
    </div>
  )
}

// ============================================
// Content Tab
// ============================================
function ContentTab({
  content,
  updateContent,
  saveContent,
  saving,
  expandedGroup,
  setExpandedGroup,
}: {
  content: SectionContent[]
  updateContent: (id: string, field: "value_en" | "value_it", value: string) => void
  saveContent: (item: SectionContent) => void
  saving: string | null
  expandedGroup: string | null
  setExpandedGroup: (g: string | null) => void
}) {
  return (
    <div className="space-y-2">
      <p className="text-gray-400 text-sm mb-4">
        Modifica i testi del sito in inglese e italiano. Clicca su una sezione per espandere.
      </p>
      {SECTION_GROUPS.map((group) => {
        const isExpanded = expandedGroup === group.label
        const groupItems = content.filter((c) => group.keys.includes(c.key))

        return (
          <div key={group.label} className="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <button
              onClick={() => setExpandedGroup(isExpanded ? null : group.label)}
              className="w-full flex items-center justify-between px-5 py-4 hover:bg-gray-800/50 transition-colors"
            >
              <div className="flex items-center gap-3">
                <span className="text-white font-medium">{group.label}</span>
                <span className="text-xs text-gray-500">{groupItems.length} campi</span>
              </div>
              <svg
                className={`w-5 h-5 text-gray-400 transition-transform ${isExpanded ? "rotate-180" : ""}`}
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            {isExpanded && (
              <div className="border-t border-gray-800 px-5 py-4 space-y-6">
                {groupItems.map((item) => (
                  <div key={item.id} className="space-y-3">
                    <div className="flex items-center justify-between">
                      <label className="text-sm font-mono text-red-400">{item.key}</label>
                      <button
                        onClick={() => saveContent(item)}
                        disabled={saving === item.id}
                        className="text-xs bg-red-600 hover:bg-red-700 disabled:bg-gray-700 disabled:text-gray-400 text-white px-3 py-1 rounded transition-colors"
                      >
                        {saving === item.id ? "Salvando..." : "Salva"}
                      </button>
                    </div>
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-3">
                      <div>
                        <label className="text-xs text-gray-500 mb-1 block">English</label>
                        {item.value_en.includes("\n") || item.value_en.length > 100 ? (
                          <textarea
                            value={item.value_en}
                            onChange={(e) => updateContent(item.id, "value_en", e.target.value)}
                            rows={4}
                            className="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-red-500 resize-y"
                          />
                        ) : (
                          <input
                            type="text"
                            value={item.value_en}
                            onChange={(e) => updateContent(item.id, "value_en", e.target.value)}
                            className="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-red-500"
                          />
                        )}
                      </div>
                      <div>
                        <label className="text-xs text-gray-500 mb-1 block">Italiano</label>
                        {item.value_it.includes("\n") || item.value_it.length > 100 ? (
                          <textarea
                            value={item.value_it}
                            onChange={(e) => updateContent(item.id, "value_it", e.target.value)}
                            rows={4}
                            className="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-red-500 resize-y"
                          />
                        ) : (
                          <input
                            type="text"
                            value={item.value_it}
                            onChange={(e) => updateContent(item.id, "value_it", e.target.value)}
                            className="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-red-500"
                          />
                        )}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        )
      })}
    </div>
  )
}

// ============================================
// Images Tab
// ============================================
function ImagesTab({
  images,
  updateImageField,
  saveImage,
  deleteImage,
  addImage,
  handleImageUpload,
  openMediaLibrary,
  uploadingFor,
  saving,
  expandedSection,
  setExpandedSection,
}: {
  images: SectionImage[]
  updateImageField: (id: string, field: string, value: string) => void
  saveImage: (item: SectionImage) => void
  deleteImage: (id: string) => void
  addImage: (section: string) => void
  handleImageUpload: (imageId: string, section: string, file: File) => void
  openMediaLibrary: (imageId: string, section: string) => void
  uploadingFor: string | null
  saving: string | null
  expandedSection: string | null
  setExpandedSection: (s: string | null) => void
}) {
  const [draggedId, setDraggedId] = useState<string | null>(null)
  const [dragOverId, setDragOverId] = useState<string | null>(null)
  const [reorderSaving, setReorderSaving] = useState(false)

  const handleDragStart = (e: React.DragEvent, id: string) => {
    setDraggedId(id)
    e.dataTransfer.effectAllowed = "move"
    // Make the drag image semi-transparent
    if (e.currentTarget instanceof HTMLElement) {
      e.currentTarget.style.opacity = "0.5"
    }
  }

  const handleDragEnd = (e: React.DragEvent) => {
    setDraggedId(null)
    setDragOverId(null)
    if (e.currentTarget instanceof HTMLElement) {
      e.currentTarget.style.opacity = "1"
    }
  }

  const handleDragOver = (e: React.DragEvent, id: string) => {
    e.preventDefault()
    e.dataTransfer.dropEffect = "move"
    if (id !== draggedId) {
      setDragOverId(id)
    }
  }

  const handleDrop = async (e: React.DragEvent, targetId: string, sectionKey: string) => {
    e.preventDefault()
    setDragOverId(null)

    if (!draggedId || draggedId === targetId) return

    const sectionImages = images
      .filter((img) => img.section === sectionKey)
      .sort((a, b) => a.display_order - b.display_order)

    const draggedIndex = sectionImages.findIndex((img) => img.id === draggedId)
    const targetIndex = sectionImages.findIndex((img) => img.id === targetId)

    if (draggedIndex === -1 || targetIndex === -1) return

    // Reorder: remove dragged and insert at target position
    const reordered = [...sectionImages]
    const [removed] = reordered.splice(draggedIndex, 1)
    reordered.splice(targetIndex, 0, removed)

    // Update display_order for all items
    setReorderSaving(true)
    const updates = reordered.map((img, idx) => ({
      id: img.id,
      display_order: idx + 1,
    }))

    // Update local state immediately
    for (const u of updates) {
      updateImageField(u.id, "display_order", String(u.display_order))
    }

    // Persist to database
    for (const u of updates) {
      await supabase
        .from("section_images")
        .update({ display_order: u.display_order, updated_at: new Date().toISOString() })
        .eq("id", u.id)
    }

    setReorderSaving(false)
    setDraggedId(null)
  }

  return (
    <div className="space-y-2">
      <p className="text-gray-400 text-sm mb-4">
        Gestisci le immagini delle varie sezioni. Trascina per riordinare, carica nuove immagini o scegli dalla libreria.
      </p>
      {IMAGE_SECTIONS.map(({ key, label }) => {
        const isExpanded = expandedSection === key
        const sectionImages = images
          .filter((img) => img.section === key)
          .sort((a, b) => a.display_order - b.display_order)

        return (
          <div key={key} className="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <button
              onClick={() => setExpandedSection(isExpanded ? null : key)}
              className="w-full flex items-center justify-between px-5 py-4 hover:bg-gray-800/50 transition-colors"
            >
              <div className="flex items-center gap-3">
                <span className="text-white font-medium">{label}</span>
                <span className="text-xs text-gray-500">{sectionImages.length} immagini</span>
                {reorderSaving && isExpanded && (
                  <span className="text-xs text-yellow-400 animate-pulse">Salvando ordine...</span>
                )}
              </div>
              <svg
                className={`w-5 h-5 text-gray-400 transition-transform ${isExpanded ? "rotate-180" : ""}`}
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            {isExpanded && (
              <div className="border-t border-gray-800 px-5 py-4 space-y-4">
                {sectionImages.map((img) => (
                  <div
                    key={img.id}
                    draggable
                    onDragStart={(e) => handleDragStart(e, img.id)}
                    onDragEnd={handleDragEnd}
                    onDragOver={(e) => handleDragOver(e, img.id)}
                    onDrop={(e) => handleDrop(e, img.id, key)}
                    className={`bg-gray-800/50 rounded-lg p-4 space-y-3 transition-all ${
                      dragOverId === img.id
                        ? "border-2 border-red-500 border-dashed bg-red-500/5"
                        : "border-2 border-transparent"
                    } ${draggedId === img.id ? "opacity-50" : ""}`}
                  >
                    <div className="flex items-start gap-4">
                      {/* Drag handle */}
                      <div className="flex-shrink-0 cursor-grab active:cursor-grabbing pt-8 text-gray-500 hover:text-white transition-colors" title="Trascina per riordinare">
                        <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 8h16M4 16h16" />
                        </svg>
                      </div>

                      {/* Image preview */}
                      {img.image_url && !img.image_url.endsWith(".mp4") && (
                        <div className="w-24 h-24 rounded-lg overflow-hidden flex-shrink-0 bg-gray-700">
                          <img
                            src={img.image_url}
                            alt={img.alt_text}
                            className="w-full h-full object-cover"
                          />
                        </div>
                      )}
                      {img.image_url && img.image_url.endsWith(".mp4") && (
                        <div className="w-24 h-24 rounded-lg overflow-hidden flex-shrink-0 bg-gray-700 flex items-center justify-center">
                          <span className="text-xs text-gray-400">VIDEO</span>
                        </div>
                      )}

                      <div className="flex-1 space-y-2">
                        {/* URL field */}
                        <div>
                          <label className="text-xs text-gray-500 mb-1 block">URL immagine</label>
                          <input
                            type="text"
                            value={img.image_url}
                            onChange={(e) => updateImageField(img.id, "image_url", e.target.value)}
                            className="w-full bg-gray-800 border border-gray-700 rounded px-3 py-1.5 text-white text-sm focus:outline-none focus:border-red-500"
                          />
                        </div>

                        {/* Alt text */}
                        <div>
                          <label className="text-xs text-gray-500 mb-1 block">Alt text</label>
                          <input
                            type="text"
                            value={img.alt_text}
                            onChange={(e) => updateImageField(img.id, "alt_text", e.target.value)}
                            className="w-full bg-gray-800 border border-gray-700 rounded px-3 py-1.5 text-white text-sm focus:outline-none focus:border-red-500"
                          />
                        </div>

                        {/* Order */}
                        <div className="flex items-center gap-2">
                          <label className="text-xs text-gray-500">Ordine:</label>
                          <input
                            type="number"
                            value={img.display_order}
                            onChange={(e) => updateImageField(img.id, "display_order", e.target.value)}
                            className="w-16 bg-gray-800 border border-gray-700 rounded px-2 py-1 text-white text-sm focus:outline-none focus:border-red-500"
                          />
                        </div>

                        {/* Guest metadata */}
                        {key === "guests" && img.metadata && (
                          <div className="grid grid-cols-1 md:grid-cols-3 gap-2 pt-2 border-t border-gray-700">
                            <div>
                              <label className="text-xs text-gray-500 mb-1 block">Nome</label>
                              <input
                                type="text"
                                value={img.metadata.name || ""}
                                onChange={(e) => updateImageField(img.id, "metadata.name", e.target.value)}
                                className="w-full bg-gray-800 border border-gray-700 rounded px-2 py-1 text-white text-sm focus:outline-none focus:border-red-500"
                              />
                            </div>
                            <div>
                              <label className="text-xs text-gray-500 mb-1 block">Ruolo</label>
                              <input
                                type="text"
                                value={img.metadata.designation || ""}
                                onChange={(e) => updateImageField(img.id, "metadata.designation", e.target.value)}
                                className="w-full bg-gray-800 border border-gray-700 rounded px-2 py-1 text-white text-sm focus:outline-none focus:border-red-500"
                              />
                            </div>
                            <div>
                              <label className="text-xs text-gray-500 mb-1 block">Quote</label>
                              <input
                                type="text"
                                value={img.metadata.quote || ""}
                                onChange={(e) => updateImageField(img.id, "metadata.quote", e.target.value)}
                                className="w-full bg-gray-800 border border-gray-700 rounded px-2 py-1 text-white text-sm focus:outline-none focus:border-red-500"
                              />
                            </div>
                          </div>
                        )}

                        {key === "guests_feature" && img.metadata && (
                          <div>
                            <label className="text-xs text-gray-500 mb-1 block">Nome</label>
                            <input
                              type="text"
                              value={img.metadata.name || ""}
                              onChange={(e) => updateImageField(img.id, "metadata.name", e.target.value)}
                              className="w-full bg-gray-800 border border-gray-700 rounded px-2 py-1 text-white text-sm focus:outline-none focus:border-red-500"
                            />
                          </div>
                        )}
                      </div>
                    </div>

                    {/* Actions */}
                    <div className="flex items-center justify-between pt-2 border-t border-gray-700">
                      <div className="flex items-center gap-2">
                        <label className="text-xs bg-gray-700 hover:bg-gray-600 text-white px-3 py-1.5 rounded cursor-pointer transition-colors">
                          {uploadingFor === img.id ? "Ottimizzando..." : "Upload"}
                          <input
                            type="file"
                            accept="image/*,video/mp4"
                            className="hidden"
                            onChange={(e) => {
                              const file = e.target.files?.[0]
                              if (file) handleImageUpload(img.id, key, file)
                            }}
                          />
                        </label>
                        <button
                          onClick={() => openMediaLibrary(img.id, key)}
                          className="text-xs bg-gray-700 hover:bg-gray-600 text-white px-3 py-1.5 rounded transition-colors"
                        >
                          Libreria
                        </button>
                      </div>
                      <div className="flex items-center gap-2">
                        <button
                          onClick={() => saveImage(img)}
                          disabled={saving === img.id}
                          className="text-xs bg-red-600 hover:bg-red-700 disabled:bg-gray-700 text-white px-3 py-1.5 rounded transition-colors"
                        >
                          {saving === img.id ? "Salvando..." : "Salva"}
                        </button>
                        <button
                          onClick={() => deleteImage(img.id)}
                          className="text-xs bg-gray-700 hover:bg-red-800 text-gray-300 hover:text-white px-3 py-1.5 rounded transition-colors"
                        >
                          Elimina
                        </button>
                      </div>
                    </div>
                  </div>
                ))}

                {/* Add image button */}
                <button
                  onClick={() => addImage(key)}
                  className="w-full border-2 border-dashed border-gray-700 hover:border-red-600 rounded-lg py-3 text-sm text-gray-400 hover:text-white transition-colors"
                >
                  + Aggiungi immagine
                </button>
              </div>
            )}
          </div>
        )
      })}
    </div>
  )
}

// ============================================
// Settings Tab
// ============================================
function SettingsTab({
  settings,
  updateSetting,
  saveSetting,
  saving,
}: {
  settings: SiteSetting[]
  updateSetting: (id: string, value: string) => void
  saveSetting: (item: SiteSetting) => void
  saving: string | null
}) {
  const settingLabels: Record<string, string> = {
    eventbrite_url: "Eventbrite (Biglietti)",
    telegram_url: "Telegram",
    spotify_playlist_url: "Spotify Playlist URL",
    spotify_embed_url: "Spotify Embed URL",
    whatsapp_url: "WhatsApp",
    email: "Email",
    instagram_url: "Instagram",
    google_maps_url: "Google Maps URL",
    google_maps_embed: "Google Maps Embed",
    admin_password: "Password Admin",
  }

  return (
    <div className="space-y-4">
      <p className="text-gray-400 text-sm mb-4">
        Modifica i link dei pulsanti CTA e le impostazioni generali del sito.
      </p>

      {/* Links */}
      <div className="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
        <div className="px-5 py-4 border-b border-gray-800">
          <h3 className="text-white font-medium">Links & CTA</h3>
        </div>
        <div className="px-5 py-4 space-y-4">
          {settings
            .filter((s) => s.key !== "admin_password")
            .map((item) => (
              <div key={item.id} className="space-y-1">
                <div className="flex items-center justify-between">
                  <label className="text-sm text-gray-300">
                    {settingLabels[item.key] || item.key}
                  </label>
                  <button
                    onClick={() => saveSetting(item)}
                    disabled={saving === item.id}
                    className="text-xs bg-red-600 hover:bg-red-700 disabled:bg-gray-700 text-white px-3 py-1 rounded transition-colors"
                  >
                    {saving === item.id ? "Salvando..." : "Salva"}
                  </button>
                </div>
                <input
                  type="text"
                  value={item.value}
                  onChange={(e) => updateSetting(item.id, e.target.value)}
                  className="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-red-500"
                />
              </div>
            ))}
        </div>
      </div>

      {/* Admin Password */}
      <div className="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
        <div className="px-5 py-4 border-b border-gray-800">
          <h3 className="text-white font-medium">Sicurezza</h3>
        </div>
        <div className="px-5 py-4">
          {settings
            .filter((s) => s.key === "admin_password")
            .map((item) => (
              <div key={item.id} className="space-y-1">
                <div className="flex items-center justify-between">
                  <label className="text-sm text-gray-300">Password Admin</label>
                  <button
                    onClick={() => saveSetting(item)}
                    disabled={saving === item.id}
                    className="text-xs bg-red-600 hover:bg-red-700 disabled:bg-gray-700 text-white px-3 py-1 rounded transition-colors"
                  >
                    {saving === item.id ? "Salvando..." : "Salva"}
                  </button>
                </div>
                <input
                  type="password"
                  value={item.value}
                  onChange={(e) => updateSetting(item.id, e.target.value)}
                  className="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-red-500"
                />
                <p className="text-xs text-gray-500">
                  Cambia la password per accedere al pannello admin.
                </p>
              </div>
            ))}
        </div>
      </div>
    </div>
  )
}
