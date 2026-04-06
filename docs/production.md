# Produção e operação

## Ambiente
- Vercel para frontend/API.
- Supabase para DB/Auth/Realtime.
- Worker assíncrono para jobs de integração.

## Segurança
- RLS habilitado em todas as tabelas multiempresa.
- Segredos em cofre (Vercel env + Supabase secrets).
- Rotação periódica de tokens externos.

## Monitoramento
- Logs de integração por tenant.
- Métricas de fila (pendente/sucesso/falha/retry).
- Alertas para webhook inválido, erro 5xx recorrente e backlog.

## Continuidade
- Backup diário do Postgres.
- Processo de restore testado mensalmente.
- Migrações versionadas e revisadas em PR.
