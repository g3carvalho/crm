import { NextResponse } from "next/server";

export async function POST() {
  return NextResponse.json(
    {
      ok: true,
      message:
        "Endpoint reservado para callbacks operacionais de integração Google Ads. Implementação efetiva ocorre por jobs assíncronos."
    },
    { status: 202 }
  );
}
