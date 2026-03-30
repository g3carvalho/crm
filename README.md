# CRM Executivo (PHP + MySQL)

Sistema CRM pronto para hospedagem compartilhada (Hostinger/cPanel), sem build.

## Requisitos
- PHP 8+
- MySQL/MariaDB
- Extensões PDO e pdo_mysql

## Estrutura
- `app/`: configuração, autenticação e funções de negócio (privado)
- `public_html/`: arquivos públicos do sistema
- `sql/crm.sql`: banco completo
- `storage/uploads`: anexos
- `storage/logs`: logs

## Instalação (Hostinger/cPanel)
1. Envie a pasta `crm` para o servidor.
2. Importe `sql/crm.sql` no phpMyAdmin.
3. Ajuste credenciais no arquivo `app/config.php` ou variáveis de ambiente:
   - `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`, `CRM_BASE_URL`.
4. Se publicar em subpasta (`https://dominio.com/crm/public_html`), use `CRM_BASE_URL=/crm/public_html`.
5. Se publicar no domínio raiz, ajuste `base_url` para vazio (`''`) ou a subpasta correta.
6. Garanta permissão de escrita para `storage/uploads` e `storage/logs`.

## Acesso inicial
- URL: `SEU_DOMINIO/login.php` dentro da base configurada
- Usuário: `admin@crm.local`
- Senha: `123456`

## Segurança aplicada
- Sessão e controle de login
- Rotas protegidas
- PDO + prepared statements
- Escape de saída HTML
- `.htaccess` para bloquear acesso a áreas privadas

## Módulos
- Login / Logout
- Dashboard com KPIs + gráficos
- Pipeline com atualização de etapa
- Leads + criação de oportunidade
- Detalhe de oportunidade (timeline, tarefas, propostas)
- Tarefas
- Propostas
- Relatórios
- Configurações
