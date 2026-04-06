import { NextRequest, NextResponse } from "next/server";
import { z } from "zod";
import { getServiceSupabase } from "@/lib/supabase/server";

const schema = z.object({
  tenantId: z.string().uuid(),
  name: z.string().min(2),
  phone: z.string().min(8),
  serviceInterest: z.string().optional(),
  attribution: z
    .object({
      gclid: z.string().optional(),
      gbraid: z.string().optional(),
      wbraid: z.string().optional(),
      utm_source: z.string().optional(),
      utm_medium: z.string().optional(),
      utm_campaign: z.string().optional(),
      utm_term: z.string().optional(),
      utm_content: z.string().optional(),
      landing_page: z.string().optional(),
      page_url: z.string().optional(),
      referrer: z.string().optional(),
      consent: z.boolean().optional()
    })
    .optional()
});

export async function POST(req: NextRequest) {
  const payload = schema.parse(await req.json());
  const supabase = getServiceSupabase();

  const { data: firstStage } = await supabase
    .from("pipeline_stages")
    .select("id")
    .eq("tenant_id", payload.tenantId)
    .eq("order_index", 1)
    .limit(1)
    .single();

  const { data, error } = await supabase
    .from("leads")
    .insert({
      tenant_id: payload.tenantId,
      name: payload.name,
      phone: payload.phone,
      service_interest: payload.serviceInterest,
      stage_id: firstStage?.id,
      attribution: payload.attribution ?? {}
    })
    .select("id")
    .single();

  if (error) {
    return NextResponse.json({ error: error.message }, { status: 400 });
  }

  return NextResponse.json({ id: data.id }, { status: 201 });
}
