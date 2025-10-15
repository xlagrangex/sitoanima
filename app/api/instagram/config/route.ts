import { NextResponse } from 'next/server'

export async function GET() {
  const clientId = process.env.INSTAGRAM_CLIENT_ID
  
  return NextResponse.json({
    clientId: clientId || null,
    configured: !!clientId,
  })
}

