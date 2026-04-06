export type OfflineEntity = "lead" | "appointment";

export interface OfflineMutation {
  id: string;
  entity: OfflineEntity;
  action: "create" | "update";
  payload: Record<string, unknown>;
  createdAt: string;
}

const KEY = "crm.offline.queue";

export function enqueueMutation(mutation: OfflineMutation) {
  const existing = getQueue();
  const updated = [...existing, mutation];
  localStorage.setItem(KEY, JSON.stringify(updated));
}

export function getQueue(): OfflineMutation[] {
  const raw = localStorage.getItem(KEY);
  if (!raw) return [];
  try {
    return JSON.parse(raw) as OfflineMutation[];
  } catch {
    return [];
  }
}
