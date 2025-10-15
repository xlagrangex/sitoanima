import { NextResponse } from 'next/server'

export async function GET() {
  const accessToken = process.env.INSTAGRAM_ACCESS_TOKEN
  
  if (!accessToken) {
    return NextResponse.json(
      { error: 'Instagram access token not configured' },
      { status: 500 }
    )
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

