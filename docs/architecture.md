# Arquitetura do CRM Multiempresa

## 1) Visão geral
O produto é um **SaaS multiempresa único** (single codebase + single database cluster) com isolamento por `tenant_id` e **Row Level Security (RLS)** no Postgres (Supabase).

### Camadas
1. **Frontend (Next.js App Router + Tailwind)**
   - Interface em português do Brasil.
   - Áreas: login, painel, CRM, agenda, conversas, automações, integrações, configurações e área master.
   - PWA instalável com suporte offline básico (fila local para mutações).
2. **Backend BFF (Route Handlers do Next.js)**
   - Endpoints de negócio, webhooks (WhatsApp/Google Ads), cron jobs.
   - Validação com Zod e enforcement de contexto multiempresa.
3. **Dados e segurança (Supabase/Postgres)**
   - Tabelas multiempresa com `tenant_id`.
   - Políticas RLS por tenant, papel e estado da assinatura (tenant ativo/suspenso).
4. **Integrações e automações**
   - WhatsApp Business Cloud API por cliente (número e conta próprios).
   - Google Ads Data Manager API para conversões offline e ECL.
   - Jobs assíncronos, retries, idempotência e logs.

## 2) Estratégia multiempresa
- `tenant_id` em todas as tabelas de negócio.
- Cada usuário pode pertencer a 1..N tenants via `memberships`.
- Claims JWT carregam `app_role` e `active_tenant_id` para simplificar autorização.
- Master area acessível somente com papel `superadmin`.

## 3) Padrões de confiabilidade
- Idempotência de webhooks por `external_event_id` + índice único.
- Retentativas exponenciais para integração externa.
- Auditoria de ações críticas em `audit_logs`.
- Observabilidade por `integration_logs`, `automation_runs`, `notification_jobs`.

## 4) Suposições adotadas
1. O banco principal será Supabase Postgres com extensão `pgcrypto` habilitada.
2. Processamento assíncrono poderá usar Supabase Edge Functions + scheduler externo (ou worker Node dedicado).
3. Upload de conversões Google Ads será realizado por jobs assíncronos, não no request síncrono do usuário.
4. OAuth para Google Ads/Meta ficará no módulo de integrações, com tokens criptografados.
