import { NextResponse } from 'next/server'
import { readFile } from 'fs/promises'
import { join } from 'path'

export async function GET() {
  let accessToken = process.env.INSTAGRAM_ACCESS_TOKEN
  
  // Try to read token from file if not in env
  if (!accessToken) {
    try {
      const tokenFilePath = join(process.cwd(), '.instagram', 'token.json')
      const tokenFileContent = await readFile(tokenFilePath, 'utf-8')
      const tokenData = JSON.parse(tokenFileContent)
      accessToken = tokenData.access_token
      
      // Check if token is expired
      const expiresAt = new Date(tokenData.expires_at)
      if (expiresAt < new Date()) {
        return NextResponse.json(
          { error: 'Token Instagram scaduto. Riconnetti l\'account.' },
          { status: 401 }
        )
      }
    } catch (err) {
      return NextResponse.json(
        { error: 'Instagram non configurato. Vai su /admin/instagram-connect per configurarlo.' },
        { status: 500 }
      )
    }
  }

  try {
    const fields = 'id,media_type,media_url,permalink,caption,timestamp'
    const url = `https://graph.instagram.com/me/media?fields=${fields}&access_token=${accessToken}`
    
    const response = await fetch(url, {
      next: { revalidate: 3600 } // Cache for 1 hour
    })

    if (!response.ok) {
      throw new Error(`Instagram API error: ${response.status}`)
    }

    const data = await response.json()
    
    return NextResponse.json(data)
  } catch (error) {
    console.error('Error fetching Instagram posts:', error)
    return NextResponse.json(
      { error: 'Failed to fetch Instagram posts' },
      { status: 500 }
    )
  }
}

