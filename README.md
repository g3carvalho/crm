# CRM Multiempresa para Prestadores de Serviço

Projeto base de um CRM multiempresa com agenda, chat, automações e integrações (WhatsApp + Google Ads), orientado para produção.

## Entregas iniciais desta etapa
- Arquitetura técnica documentada.
- Estrutura de pastas por domínio.
- Modelagem de banco com migração SQL, índices, RLS e políticas multiempresa.
- Código-base Next.js App Router com telas principais.
- Endpoints iniciais para captura de leads, webhooks e cron.
- Configuração inicial de PWA/offline por fila local de mutações.

## Setup rápido
1. Copie variáveis:
   ```bash
   cp .env.example .env.local
   ```
2. Instale dependências:
   ```bash
   npm install
   ```
3. Rode em desenvolvimento:
   ```bash
   npm run dev
   ```
4. Execute migrações no Supabase (via CLI ou painel SQL):
   - `supabase/migrations/202604060001_init.sql`
5. (Opcional) Popule dados de seed:
   - `supabase/seed.sql`

## Documentação
- Arquitetura: `docs/architecture.md`
- Estrutura: `docs/folder-structure.md`
- Modelagem do banco: `docs/database-model.md`
- Regras de acesso: `docs/access-rules.md`
- Plano de implementação: `docs/implementation-plan.md`
- Produção: `docs/production.md`
