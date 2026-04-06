export interface ScheduledJob {
  tenantId: string;
  type: "daily_schedule" | "review_request" | "appointment_reminder";
  runAt: string;
  payload: Record<string, unknown>;
}

export function buildReviewRequestJob(tenantId: string, appointmentId: string, runAt: string): ScheduledJob {
  return {
    tenantId,
    type: "review_request",
    runAt,
    payload: {
      appointmentId
    }
  };
}
