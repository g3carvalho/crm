# CRM Web (MVP)

Aplicação CRM interna construída com React + Vite, com autenticação simulada e persistência em `localStorage`.

## Como rodar

```bash
npm i
npm run dev
```

Acesse a URL exibida no terminal (normalmente `http://localhost:5173`).

## Build de produção

```bash
npm run build
npm run preview
```

## Observações do MVP

- Rotas: `/login`, `/inicio`, `/dashboard`, `/pipeline`, `/contatos`.
- Autenticação simulada por token em `localStorage`.
- Dados de contatos e negócios com seed automática na primeira carga.
- Dashboard com métricas e gráficos (Chart.js) baseados nos dados reais salvos localmente.
- Pipeline com Kanban: movimentação por arrastar/soltar e por botões laterais.
- Contatos com busca, filtros, ordenação, paginação e CRUD em modal.
