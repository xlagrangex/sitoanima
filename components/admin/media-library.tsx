"use client"

import { useState, useEffect, useCallback, useRef } from "react"
import { supabase } from "@/lib/supabase"
import { optimizeImage, formatFileSize } from "@/lib/image-utils"

export interface MediaItem {
  id: string
  file_name: string
  original_name: string
  file_url: string
  file_size: number
  mime_type: string
  width: number | null
  height: number | null
  alt_text: string
  tags: string[]
  folder: string
  uploaded_at: string
}

interface MediaLibraryProps {
  isOpen: boolean
  onClose: () => void
  onSelect: (item: MediaItem) => void
  folder?: string
}

export function MediaLibrary({ isOpen, onClose, onSelect, folder }: MediaLibraryProps) {
  const [items, setItems] = useState<MediaItem[]>([])
  const [loading, setLoading] = useState(false)
  const [uploading, setUploading] = useState(false)
  const [uploadProgress, setUploadProgress] = useState("")
  const [search, setSearch] = useState("")
  const [activeFolder, setActiveFolder] = useState(folder || "all")
  const [selectedItem, setSelectedItem] = useState<MediaItem | null>(null)
  const [dragActive, setDragActive] = useState(false)
  const fileInputRef = useRef<HTMLInputElement>(null)

  const loadMedia = useCallback(async () => {
    setLoading(true)
    let query = supabase
      .from("media_library")
      .select("*")
      .order("uploaded_at", { ascending: false })

    if (activeFolder !== "all") {
      query = query.eq("folder", activeFolder)
    }

    if (search) {
      query = query.or(
        `original_name.ilike.%${search}%,alt_text.ilike.%${search}%,file_name.ilike.%${search}%`
      )
    }

    const { data } = await query
    setItems(data || [])
    setLoading(false)
  }, [activeFolder, search])

  useEffect(() => {
    if (isOpen) loadMedia()
  }, [isOpen, loadMedia])

  const handleUpload = async (files: FileList | File[]) => {
    setUploading(true)
    const fileArray = Array.from(files)

    for (let i = 0; i < fileArray.length; i++) {
      const file = fileArray[i]
      setUploadProgress(`Ottimizzando ${i + 1}/${fileArray.length}: ${file.name}...`)

      try {
        // Optimize image (convert to WebP, compress, resize)
        const optimized = await optimizeImage(file)

        setUploadProgress(`Caricando ${i + 1}/${fileArray.length}: ${optimized.fileName}...`)

        // Upload to Supabase Storage
        const storagePath = `${activeFolder === "all" ? "general" : activeFolder}/${Date.now()}-${optimized.fileName}`

        const { error: uploadError } = await supabase.storage
          .from("site-images")
          .upload(storagePath, optimized.blob, {
            cacheControl: "3600",
            upsert: false,
            contentType: optimized.blob.type,
          })

        if (uploadError) {
          console.error("Upload error:", uploadError)
          continue
        }

        // Get public URL
        const { data: urlData } = supabase.storage
          .from("site-images")
          .getPublicUrl(storagePath)

        // Save to media_library table
        await supabase.from("media_library").insert({
          file_name: optimized.fileName,
          original_name: file.name,
          file_url: urlData.publicUrl,
          file_size: optimized.optimizedSize,
          mime_type: optimized.blob.type,
          width: optimized.width || null,
          height: optimized.height || null,
          alt_text: file.name.replace(/\.[^.]+$/, "").replace(/[-_]/g, " "),
          folder: activeFolder === "all" ? "general" : activeFolder,
        })
      } catch (err) {
        console.error("Error processing:", file.name, err)
      }
    }

    setUploading(false)
    setUploadProgress("")
    loadMedia()
  }

  const handleDelete = async (item: MediaItem) => {
    if (!confirm(`Eliminare "${item.original_name}"?`)) return

    // Delete from storage
    const path = item.file_url.split("/site-images/")[1]
    if (path) {
      await supabase.storage.from("site-images").remove([decodeURIComponent(path)])
    }

    // Delete from database
    await supabase.from("media_library").delete().eq("id", item.id)
    setItems((prev) => prev.filter((i) => i.id !== item.id))
    if (selectedItem?.id === item.id) setSelectedItem(null)
  }

  const updateAltText = async (id: string, alt_text: string) => {
    await supabase.from("media_library").update({ alt_text }).eq("id", id)
    setItems((prev) =>
      prev.map((i) => (i.id === id ? { ...i, alt_text } : i))
    )
    if (selectedItem?.id === id) {
      setSelectedItem((prev) => (prev ? { ...prev, alt_text } : null))
    }
  }

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault()
    setDragActive(false)
    if (e.dataTransfer.files.length > 0) {
      handleUpload(e.dataTransfer.files)
    }
  }

  const folders = ["all", "general", "media", "guests", "hero", "shows", "events"]

  if (!isOpen) return null

  return (
    <div className="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 backdrop-blur-sm">
      <div className="bg-gray-900 rounded-2xl w-[95vw] max-w-6xl h-[85vh] flex flex-col border border-gray-700 shadow-2xl overflow-hidden">
        {/* Header */}
        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-800">
          <div className="flex items-center gap-3">
            <h2 className="text-lg font-bold text-white">Media Library</h2>
            <span className="text-xs text-gray-500">{items.length} file</span>
          </div>
          <div className="flex items-center gap-3">
            {/* Search */}
            <div className="relative">
              <input
                type="text"
                placeholder="Cerca media..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                className="bg-gray-800 border border-gray-700 rounded-lg px-3 py-1.5 pl-8 text-white text-sm focus:outline-none focus:border-red-500 w-48"
              />
              <svg
                className="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-500"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                />
              </svg>
            </div>
            <button
              onClick={onClose}
              className="text-gray-400 hover:text-white transition-colors p-1"
            >
              <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <div className="flex flex-1 min-h-0">
          {/* Sidebar - Folders */}
          <div className="w-40 border-r border-gray-800 py-3 flex-shrink-0">
            {folders.map((f) => (
              <button
                key={f}
                onClick={() => setActiveFolder(f)}
                className={`w-full text-left px-4 py-2 text-sm transition-colors ${
                  activeFolder === f
                    ? "bg-red-600/20 text-red-400 border-r-2 border-red-500"
                    : "text-gray-400 hover:text-white hover:bg-gray-800/50"
                }`}
              >
                {f === "all" ? "Tutti" : f.charAt(0).toUpperCase() + f.slice(1)}
              </button>
            ))}
          </div>

          {/* Main content */}
          <div className="flex-1 flex flex-col min-h-0">
            {/* Upload area */}
            <div
              className={`mx-4 mt-4 border-2 border-dashed rounded-xl transition-colors ${
                dragActive
                  ? "border-red-500 bg-red-500/10"
                  : "border-gray-700 hover:border-gray-500"
              }`}
              onDragOver={(e) => {
                e.preventDefault()
                setDragActive(true)
              }}
              onDragLeave={() => setDragActive(false)}
              onDrop={handleDrop}
            >
              <div className="flex items-center justify-center gap-3 py-3">
                {uploading ? (
                  <div className="flex items-center gap-2">
                    <div className="animate-spin h-4 w-4 border-2 border-red-500 border-t-transparent rounded-full" />
                    <span className="text-sm text-gray-300">{uploadProgress}</span>
                  </div>
                ) : (
                  <>
                    <span className="text-sm text-gray-400">
                      Trascina file qui oppure
                    </span>
                    <button
                      onClick={() => fileInputRef.current?.click()}
                      className="text-sm bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg transition-colors"
                    >
                      Sfoglia
                    </button>
                    <span className="text-xs text-gray-500">
                      Auto-conversione WebP + compressione
                    </span>
                    <input
                      ref={fileInputRef}
                      type="file"
                      accept="image/*,video/mp4"
                      multiple
                      className="hidden"
                      onChange={(e) => {
                        if (e.target.files) handleUpload(e.target.files)
                        e.target.value = ""
                      }}
                    />
                  </>
                )}
              </div>
            </div>

            {/* Grid */}
            <div className="flex-1 overflow-y-auto p-4">
              {loading ? (
                <div className="flex items-center justify-center py-20">
                  <div className="animate-spin h-8 w-8 border-2 border-red-500 border-t-transparent rounded-full" />
                </div>
              ) : items.length === 0 ? (
                <div className="flex flex-col items-center justify-center py-20 text-gray-500">
                  <svg className="w-12 h-12 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <p className="text-sm">Nessun media trovato</p>
                </div>
              ) : (
                <div className="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
                  {items.map((item) => (
                    <div
                      key={item.id}
                      onClick={() => setSelectedItem(item)}
                      className={`group relative aspect-square rounded-lg overflow-hidden cursor-pointer border-2 transition-all ${
                        selectedItem?.id === item.id
                          ? "border-red-500 ring-2 ring-red-500/30"
                          : "border-transparent hover:border-gray-600"
                      }`}
                    >
                      {item.mime_type.startsWith("video/") ? (
                        <div className="w-full h-full bg-gray-800 flex items-center justify-center">
                          <svg className="w-8 h-8 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                          </svg>
                        </div>
                      ) : (
                        <img
                          src={item.file_url}
                          alt={item.alt_text}
                          className="w-full h-full object-cover"
                          loading="lazy"
                        />
                      )}
                      {/* Overlay */}
                      <div className="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all flex items-end">
                        <div className="w-full px-2 py-1.5 bg-gradient-to-t from-black/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                          <p className="text-[10px] text-white truncate">{item.original_name}</p>
                          <p className="text-[9px] text-gray-400">{formatFileSize(item.file_size)}</p>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>

          {/* Detail sidebar */}
          {selectedItem && (
            <div className="w-72 border-l border-gray-800 p-4 flex-shrink-0 overflow-y-auto">
              <div className="space-y-4">
                {/* Preview */}
                <div className="aspect-square rounded-lg overflow-hidden bg-gray-800">
                  {selectedItem.mime_type.startsWith("video/") ? (
                    <video src={selectedItem.file_url} className="w-full h-full object-cover" controls />
                  ) : (
                    <img
                      src={selectedItem.file_url}
                      alt={selectedItem.alt_text}
                      className="w-full h-full object-cover"
                    />
                  )}
                </div>

                {/* Info */}
                <div className="space-y-2 text-sm">
                  <div>
                    <label className="text-xs text-gray-500 block">Nome file</label>
                    <p className="text-white text-xs break-all">{selectedItem.original_name}</p>
                  </div>
                  <div className="flex gap-4">
                    <div>
                      <label className="text-xs text-gray-500 block">Dimensione</label>
                      <p className="text-white text-xs">{formatFileSize(selectedItem.file_size)}</p>
                    </div>
                    {selectedItem.width && selectedItem.height && (
                      <div>
                        <label className="text-xs text-gray-500 block">Risoluzione</label>
                        <p className="text-white text-xs">
                          {selectedItem.width}x{selectedItem.height}
                        </p>
                      </div>
                    )}
                  </div>
                  <div>
                    <label className="text-xs text-gray-500 block">Tipo</label>
                    <p className="text-white text-xs">{selectedItem.mime_type}</p>
                  </div>
                  <div>
                    <label className="text-xs text-gray-500 block">Cartella</label>
                    <p className="text-white text-xs">{selectedItem.folder}</p>
                  </div>
                  <div>
                    <label className="text-xs text-gray-500 block">Caricato il</label>
                    <p className="text-white text-xs">
                      {new Date(selectedItem.uploaded_at).toLocaleDateString("it-IT", {
                        day: "numeric",
                        month: "long",
                        year: "numeric",
                        hour: "2-digit",
                        minute: "2-digit",
                      })}
                    </p>
                  </div>

                  {/* Alt text edit */}
                  <div>
                    <label className="text-xs text-gray-500 block mb-1">Alt text</label>
                    <input
                      type="text"
                      value={selectedItem.alt_text}
                      onChange={(e) => {
                        setSelectedItem((prev) =>
                          prev ? { ...prev, alt_text: e.target.value } : null
                        )
                      }}
                      onBlur={() => {
                        if (selectedItem)
                          updateAltText(selectedItem.id, selectedItem.alt_text)
                      }}
                      className="w-full bg-gray-800 border border-gray-700 rounded px-2 py-1 text-white text-xs focus:outline-none focus:border-red-500"
                    />
                  </div>

                  {/* URL copy */}
                  <div>
                    <label className="text-xs text-gray-500 block mb-1">URL</label>
                    <div className="flex gap-1">
                      <input
                        type="text"
                        readOnly
                        value={selectedItem.file_url}
                        className="w-full bg-gray-800 border border-gray-700 rounded px-2 py-1 text-gray-400 text-[10px] focus:outline-none"
                      />
                      <button
                        onClick={() => navigator.clipboard.writeText(selectedItem.file_url)}
                        className="text-xs bg-gray-700 hover:bg-gray-600 text-white px-2 py-1 rounded transition-colors flex-shrink-0"
                        title="Copia URL"
                      >
                        Copia
                      </button>
                    </div>
                  </div>
                </div>

                {/* Actions */}
                <div className="flex gap-2 pt-2 border-t border-gray-700">
                  <button
                    onClick={() => {
                      onSelect(selectedItem)
                      onClose()
                    }}
                    className="flex-1 bg-red-600 hover:bg-red-700 text-white text-xs py-2 rounded-lg transition-colors"
                  >
                    Seleziona
                  </button>
                  <button
                    onClick={() => handleDelete(selectedItem)}
                    className="bg-gray-700 hover:bg-red-800 text-gray-300 hover:text-white text-xs px-3 py-2 rounded-lg transition-colors"
                  >
                    Elimina
                  </button>
                </div>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  )
}
