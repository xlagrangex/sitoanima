import { NextRequest, NextResponse } from 'next/server'
import { writeFile, mkdir } from 'fs/promises'
import { join } from 'path'

export async function POST(request: NextRequest) {
  try {
    const { token } = await request.json()

    if (!token || typeof token !== 'string') {
      return NextResponse.json(
        { error: 'Token non valido' },
        { status: 400 }
      )
    }

    // Verifica che il token sia nel formato corretto (inizia con IG o IGQV)
    if (!token.startsWith('IG') && !token.startsWith('EAAG')) {
      return NextResponse.json(
        { error: 'Il token non sembra essere un token Instagram valido' },
        { status: 400 }
      )
    }

    // Test token validity by making a test request
    try {
      const testResponse = await fetch(
        `https://graph.instagram.com/me?fields=id,username&access_token=${token}`
      )
      
      if (!testResponse.ok) {
        return NextResponse.json(
          { error: 'Token non valido o scaduto' },
          { status: 400 }
        )
      }

      const userData = await testResponse.json()
      
      // Save token to file system
      const tokenStoragePath = join(process.cwd(), '.instagram')
      const tokenFilePath = join(tokenStoragePath, 'token.json')

      try {
        await mkdir(tokenStoragePath, { recursive: true })
      } catch (err) {
        // Directory might already exist
      }

      const tokenInfo = {
        access_token: token,
        user_id: userData.id,
        username: userData.username,
        created_at: new Date().toISOString(),
        expires_at: new Date(Date.now() + 60 * 24 * 60 * 60 * 1000).toISOString(), // 60 days
      }

      await writeFile(tokenFilePath, JSON.stringify(tokenInfo, null, 2))

      return NextResponse.json({
        success: true,
        message: 'Token salvato con successo',
        username: userData.username,
      })

    } catch (apiError) {
      return NextResponse.json(
        { error: 'Impossibile verificare il token con Instagram' },
        { status: 400 }
      )
    }

  } catch (error) {
    console.error('Error saving token:', error)
    return NextResponse.json(
      { error: 'Errore del server' },
      { status: 500 }
    )
  }
}

