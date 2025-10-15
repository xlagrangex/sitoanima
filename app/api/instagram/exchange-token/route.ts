import { NextRequest, NextResponse } from 'next/server'
import { writeFile, mkdir } from 'fs/promises'
import { join } from 'path'

export async function POST(request: NextRequest) {
  try {
    const { code } = await request.json()

    if (!code) {
      return NextResponse.json(
        { error: 'Codice di autorizzazione mancante' },
        { status: 400 }
      )
    }

    const clientId = process.env.INSTAGRAM_CLIENT_ID
    const clientSecret = process.env.INSTAGRAM_CLIENT_SECRET
    const redirectUri = process.env.INSTAGRAM_REDIRECT_URI || `${request.nextUrl.origin}/admin/instagram-connect/callback`

    if (!clientId || !clientSecret) {
      return NextResponse.json(
        { error: 'App Instagram non configurata. Configura INSTAGRAM_CLIENT_ID e INSTAGRAM_CLIENT_SECRET in .env.local' },
        { status: 500 }
      )
    }

    // Step 1: Exchange code for short-lived token
    const tokenUrl = 'https://api.instagram.com/oauth/access_token'
    const formData = new URLSearchParams({
      client_id: clientId,
      client_secret: clientSecret,
      grant_type: 'authorization_code',
      redirect_uri: redirectUri,
      code: code,
    })

    const tokenResponse = await fetch(tokenUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: formData.toString(),
    })

    if (!tokenResponse.ok) {
      const errorData = await tokenResponse.json()
      return NextResponse.json(
        { error: errorData.error_message || 'Errore durante lo scambio del token' },
        { status: 400 }
      )
    }

    const tokenData = await tokenResponse.json()
    const shortLivedToken = tokenData.access_token

    // Step 2: Exchange short-lived token for long-lived token (60 days)
    const longLivedUrl = `https://graph.instagram.com/access_token?grant_type=ig_exchange_token&client_secret=${clientSecret}&access_token=${shortLivedToken}`
    
    const longLivedResponse = await fetch(longLivedUrl)
    
    if (!longLivedResponse.ok) {
      // If long-lived exchange fails, use short-lived token
      console.warn('Failed to get long-lived token, using short-lived')
    }

    const longLivedData = await longLivedResponse.json()
    const accessToken = longLivedData.access_token || shortLivedToken
    const expiresIn = longLivedData.expires_in || 3600

    // Step 3: Save token to file system
    const tokenStoragePath = join(process.cwd(), '.instagram')
    const tokenFilePath = join(tokenStoragePath, 'token.json')

    try {
      await mkdir(tokenStoragePath, { recursive: true })
    } catch (err) {
      // Directory might already exist
    }

    const tokenInfo = {
      access_token: accessToken,
      user_id: tokenData.user_id,
      expires_at: new Date(Date.now() + expiresIn * 1000).toISOString(),
      created_at: new Date().toISOString(),
    }

    await writeFile(tokenFilePath, JSON.stringify(tokenInfo, null, 2))

    return NextResponse.json({
      success: true,
      message: 'Token salvato con successo',
      expires_in_days: Math.floor(expiresIn / 86400),
    })

  } catch (error) {
    console.error('Error exchanging token:', error)
    return NextResponse.json(
      { error: 'Errore del server durante il salvataggio del token' },
      { status: 500 }
    )
  }
}

