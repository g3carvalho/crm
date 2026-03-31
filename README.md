# Grupo Capital DF + Blog integrado

## Deploy rápido
1. Aponte o DocumentRoot para a pasta `public/`.
2. Garanta PHP 8.1+ com extensões `pdo_sqlite`, `fileinfo` e `mbstring`.
3. Ajuste `BASE_URL` no ambiente (ex.: `https://seudominio.com`).
4. Permita escrita em `public/storage/` e `public/uploads/`.

## Painel administrativo
- URL: `/admin/login`
- Usuário inicial: `admin@localhost`
- Senha inicial: `TroqueAgora123!`

> Troque a senha imediatamente: edite o hash no banco SQLite após primeiro acesso.

## Estrutura
- `public/index.php`: roteador principal (site, blog e admin).
- `public/src/`: bootstrap, segurança, banco e funções de domínio.
- `public/templates/`: layout e views públicas/admin.
- `public/storage/blog.sqlite`: persistência SQLite.
- `public/uploads/`: imagens destacadas.

## SEO implementado
- URLs limpas via `.htaccess`.
- `robots.txt` e `sitemap.xml` dinâmicos.
- RSS em `/blog/rss.xml`.
- Canonical, Open Graph, schema Organization/WebSite/BreadcrumbList/BlogPosting.
- `noindex` no admin.
