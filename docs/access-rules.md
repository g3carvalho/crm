# Regras de acesso multiempresa (RLS)

## Princípios
1. O isolamento é feito no banco (não somente na UI).
2. Todo acesso passa por `has_tenant_access(tenant_id)`.
3. Operações administrativas globais exigem `is_superadmin()`.

## Funções auxiliares
- `current_user_id()` → usuário autenticado.
- `current_tenant_id()` → tenant ativo no JWT.
- `current_role()` → papel atual (`superadmin`, `owner`, `manager`, `agent`, `viewer`).
- `has_min_role(tenant_id, role)` → checa hierarquia de permissão.

## Políticas aplicadas
- **Leitura tenantizada**: `using (has_tenant_access(tenant_id))`.
- **Escrita tenantizada**: `with check (has_tenant_access(tenant_id))`.
- **Memberships**: alteração permitida para `owner`+ ou `superadmin`.
- **Tenants**: alteração somente `superadmin`.
- **Audit logs**: leitura por tenant e superadmin; inserção auditável por contexto autorizado.

## Hierarquia de papéis
`superadmin > owner > manager > agent > viewer`

- `viewer`: leitura de dados.
- `agent`: operação de atendimento/leads/agenda.
- `manager`: supervisão, relatórios e configurações operacionais.
- `owner`: administração completa do tenant.
- `superadmin`: administração global (área master).
