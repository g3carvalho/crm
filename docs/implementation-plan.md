# Plano de implementação por etapas

1. **Fundação técnica (Semana 1)**
   - Setup Next.js + TypeScript + Tailwind.
   - Migração inicial de banco com RLS.
   - Auth + memberships + contexto de tenant.
2. **Área master e onboarding de cliente (Semana 2)**
   - CRUD de tenants.
   - Ativação/suspensão.
   - Templates de funil/automações por nicho.
3. **CRM + Kanban (Semanas 3-4)**
   - Leads, pipeline, stages, eventos.
   - Drag and drop + histórico de mudança.
4. **Agenda (Semana 5)**
   - Profissionais, serviços, unidades.
   - Agendamentos + status + bloqueios.
5. **Chat WhatsApp (Semanas 6-7)**
   - Inbox, threads, mensagens, leitura/entrega.
   - Webhooks verificados + idempotência.
6. **Google Ads + automações (Semanas 8-9)**
   - Mapeamento de estágio para conversão.
   - Jobs assíncronos + retry + DLQ lógica.
7. **PWA + Offline (Semana 10)**
   - Cache de agenda e dados críticos.
   - Fila local de mutações e reconciliação.
8. **Qualidade e go-live (Semanas 11-12)**
   - Testes básicos + hardening de segurança.
   - Runbooks de produção e observabilidade.
