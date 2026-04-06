export interface TenantContext {
  tenantId: string;
  userId: string;
  role: "superadmin" | "owner" | "manager" | "agent" | "viewer";
}

export function assertTenantContext(ctx: Partial<TenantContext>): asserts ctx is TenantContext {
  if (!ctx.tenantId || !ctx.userId || !ctx.role) {
    throw new Error("Contexto multiempresa inválido.");
  }
}
