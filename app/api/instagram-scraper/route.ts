import { NextResponse } from 'next/server'

export const dynamic = 'force-dynamic'

export async function GET() {
  try {
    const username = 'anima.ent'
    
    // Metodo 1: Tentativo di accesso ai dati pubblici di Instagram
    // NOTA: Questo metodo può smettere di funzionare in qualsiasi momento
    // Instagram cambia frequentemente la struttura dei dati
    
    const url = `https://www.instagram.com/${username}/`
    
    const response = await fetch(url, {
      headers: {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
        'Accept-Language': 'en-US,en;q=0.5',
        'Sec-Fetch-Dest': 'document',
        'Sec-Fetch-Mode': 'navigate',
        'Sec-Fetch-Site': 'none',
      },
      next: { revalidate: 3600 } // Cache for 1 hour
    })

    if (!response.ok) {
      throw new Error(`Instagram returned ${response.status}`)
    }

    const html = await response.text()
    
    // Cerca i dati JSON embedded nella pagina
    const scriptRegex = /<script type="application\/ld\+json">(.+?)<\/script>/g
    const dataRegex = /window\._sharedData = (.+?);<\/script>/
    
    let posts: any[] = []
    
    // Prova a estrarre i dati dal tag script
    const match = html.match(dataRegex)
    
    if (match && match[1]) {
      try {
        const data = JSON.parse(match[1])
        const edges = data?.entry_data?.ProfilePage?.[0]?.graphql?.user?.edge_owner_to_timeline_media?.edges || []
        
        posts = edges.slice(0, 9).map((edge: any) => ({
          id: edge.node.id,
          image: edge.node.display_url || edge.node.thumbnail_src,
          likes: formatNumber(edge.node.edge_liked_by?.count || 0),
          comments: formatNumber(edge.node.edge_media_to_comment?.count || 0),
          caption: edge.node.edge_media_to_caption?.edges?.[0]?.node?.text || '',
        }))
      } catch (parseError) {
        console.error('Failed to parse Instagram data:', parseError)
      }
    }
    
    // Se non troviamo post, proviamo un approccio alternativo
    if (posts.length === 0) {
      // Cerca pattern alternativi nella pagina
      const altRegex = /"edge_owner_to_timeline_media":\{"count":\d+,"page_info":\{[^}]+\},"edges":\[(.+?)\]\}/
      const altMatch = html.match(altRegex)
      
      if (altMatch && altMatch[1]) {
        try {
          const edgesData = JSON.parse(`[${altMatch[1]}]`)
          posts = edgesData.slice(0, 9).map((edge: any) => ({
            id: edge.node?.id || Math.random().toString(),
            image: edge.node?.display_url || edge.node?.thumbnail_src || '',
            likes: formatNumber(edge.node?.edge_liked_by?.count || 0),
            comments: formatNumber(edge.node?.edge_media_to_comment?.count || 0),
            caption: edge.node?.edge_media_to_caption?.edges?.[0]?.node?.text || '',
          }))
        } catch (error) {
          console.error('Alternative parsing failed:', error)
        }
      }
    }

    if (posts.length === 0) {
      return NextResponse.json(
        { 
          posts: [],
          error: 'Unable to scrape Instagram. Using placeholder images.',
          message: 'Instagram structure may have changed or rate limit reached.'
        },
        { status: 200 }
      )
    }

    return NextResponse.json({ 
      posts,
      scraped: true,
      timestamp: new Date().toISOString()
    })
    
  } catch (error) {
    console.error('Instagram scraping error:', error)
    
    return NextResponse.json(
      { 
        posts: [],
        error: 'Scraping failed. Using placeholder images.',
        message: error instanceof Error ? error.message : 'Unknown error'
      },
      { status: 200 } // Return 200 to use fallback images
    )
  }
}

function formatNumber(num: number): string {
  if (num >= 1000000) {
    return (num / 1000000).toFixed(1) + 'M'
  }
  if (num >= 1000) {
    return (num / 1000).toFixed(1) + 'K'
  }
  return num.toString()
}

