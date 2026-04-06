export type ConversionTrigger = "lead_created" | "lead_qualified" | "appointment_completed" | "deal_won";

export interface LeadAttribution {
  gclid?: string;
  gbraid?: string;
  wbraid?: string;
  utmSource?: string;
  utmMedium?: string;
  utmCampaign?: string;
}

export function canSendOfflineConversion(trigger: ConversionTrigger, allowedTriggers: ConversionTrigger[]) {
  return allowedTriggers.includes(trigger);
}
