import { NextRequest, NextResponse } from "next/server";
import { verifyMetaSignature } from "@/lib/integrations/whatsapp/signature";

export async function GET(req: NextRequest) {
  const url = new URL(req.url);
  const mode = url.searchParams.get("hub.mode");
  const token = url.searchParams.get("hub.verify_token");
  const challenge = url.searchParams.get("hub.challenge");

  if (mode === "subscribe" && token === process.env.WHATSAPP_VERIFY_TOKEN) {
    return new NextResponse(challenge, { status: 200 });
  }

  return NextResponse.json({ error: "Token de verificação inválido" }, { status: 403 });
}

export async function POST(req: NextRequest) {
  const raw = await req.text();
  const signature = req.headers.get("x-hub-signature-256");
  const appSecret = process.env.WHATSAPP_APP_SECRET;

  if (!appSecret || !verifyMetaSignature(raw, signature, appSecret)) {
    return NextResponse.json({ error: "Assinatura inválida" }, { status: 401 });
  }

  return NextResponse.json({ ok: true });
}
