# Modelagem de banco de dados

## Entidades principais
- **Identidade e tenancy**: `tenants`, `users`, `memberships`.
- **CRM**: `pipelines`, `pipeline_stages`, `leads`, `lead_events`.
- **Agenda**: `locations`, `professionals`, `services`, `appointments`.
- **Chat**: `conversations`, `messages`.
- **Automações**: `automations`, `automation_runs`, `notification_jobs`, `review_requests`.
- **Integrações**: `integrations`, `integration_logs`.
- **Governança**: `audit_logs`.

## Relacionamentos-chave
1. `tenants 1:N memberships` e `users 1:N memberships`.
2. `tenants 1:N pipelines 1:N pipeline_stages`.
3. `leads` pertencem a `tenant`, opcionalmente ligados ao `pipeline/stage`.
4. `appointments` podem apontar para `lead`, `service`, `professional` e `location`.
5. `conversations` vinculam `lead` e agregam `messages`.
6. `integrations` possuem histórico em `integration_logs`.

## Campos de rastreio de mídia
O lead mantém `attribution` em JSONB com chaves:
`gclid`, `gbraid`, `wbraid`, `utm_source`, `utm_medium`, `utm_campaign`, `utm_term`, `utm_content`, `landing_page`, `page_url`, `referrer`, `consent`.

## Observações de design
- `tenant_id` obrigatório em todas as tabelas de negócio.
- `unique(tenant_id, external_message_id)` para idempotência de mensagens.
- `unique(tenant_id, event_type, external_event_id)` para idempotência de webhook/log.
- `automation_runs.idempotency_key` evita duplicidade em gatilhos.
