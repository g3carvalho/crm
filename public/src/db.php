<?php

function db_connect(string $dbPath): PDO
{
    if (!is_dir(dirname($dbPath))) {
        mkdir(dirname($dbPath), 0775, true);
    }

    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');
    return $pdo;
}

function db_migrate(PDO $pdo): void
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        created_at TEXT NOT NULL
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        description TEXT DEFAULT "",
        seo_title TEXT DEFAULT "",
        seo_description TEXT DEFAULT ""
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS tags (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        slug TEXT NOT NULL UNIQUE
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS posts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        excerpt TEXT NOT NULL,
        content TEXT NOT NULL,
        featured_image TEXT DEFAULT "",
        image_alt TEXT DEFAULT "",
        author_name TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT "draft",
        is_featured INTEGER NOT NULL DEFAULT 0,
        category_id INTEGER,
        seo_title TEXT DEFAULT "",
        seo_description TEXT DEFAULT "",
        canonical_url TEXT DEFAULT "",
        reading_time INTEGER NOT NULL DEFAULT 1,
        scheduled_at TEXT DEFAULT NULL,
        published_at TEXT DEFAULT NULL,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS post_tags (
        post_id INTEGER NOT NULL,
        tag_id INTEGER NOT NULL,
        PRIMARY KEY(post_id, tag_id),
        FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
        FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS slug_redirects (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        old_slug TEXT NOT NULL UNIQUE,
        post_id INTEGER NOT NULL,
        created_at TEXT NOT NULL,
        FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS login_attempts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT NOT NULL,
        ip_address TEXT NOT NULL,
        attempted_at INTEGER NOT NULL
    )');
}

function db_seed(PDO $pdo): void
{
    $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count === 0) {
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, created_at) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            'Administrador',
            'admin@localhost',
            password_hash('TroqueAgora123!', PASSWORD_DEFAULT),
            date('c')
        ]);
    }

    $categories = [
        ['Cartas Contempladas', 'cartas-contempladas', 'Conteúdos sobre cartas contempladas e oportunidades estratégicas.'],
        ['Contemplação Programada', 'contemplacao-programada', 'Planejamento para antecipar contemplação com segurança.'],
        ['CGI', 'cgi', 'Guias e análises sobre CGI no contexto de consórcios e investimentos.'],
        ['Imobiliária', 'imobiliaria', 'Temas imobiliários com foco em aquisição planejada e patrimônio.'],
        ['Comparativos', 'comparativos', 'Comparativos claros entre modalidades e estratégias de compra.'],
        ['Dúvidas Frequentes', 'duvidas-frequentes', 'Respostas objetivas para as dúvidas mais comuns do público.'],
    ];

    $catStmt = $pdo->prepare('INSERT OR IGNORE INTO categories (name, slug, description, seo_title, seo_description) VALUES (?, ?, ?, ?, ?)');
    foreach ($categories as $c) {
        $catStmt->execute([$c[0], $c[1], $c[2], $c[0] . ' | Blog Grupo Capital DF', mb_substr($c[2], 0, 150)]);
    }

    $postCount = (int) $pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn();
    if ($postCount > 0) {
        return;
    }

    $basePosts = [
        ['Como avaliar uma carta contemplada com segurança patrimonial', 'como-avaliar-carta-contemplada-seguranca', 'Checklist prático para analisar origem, documentação e riscos antes da compra.', 'Cartas Contempladas'],
        ['Contemplação programada: como reduzir incerteza no planejamento', 'contemplacao-programada-reduzir-incerteza', 'Entenda critérios de lance, perfil de grupo e reserva financeira para acelerar resultados.', 'Contemplação Programada'],
        ['CGI na prática: quando faz sentido para objetivos imobiliários', 'cgi-na-pratica-imobiliario', 'Veja cenários em que CGI pode aumentar eficiência de aquisição e fluxo de caixa.', 'CGI'],
        ['Consórcio imobiliário vs financiamento: custo total em 5 cenários', 'consorcio-vs-financiamento-5-cenarios', 'Comparativo direto com foco em custo efetivo, prazo e flexibilidade.', 'Comparativos'],
        ['Documentação essencial para compra de imóvel com carta contemplada', 'documentacao-imovel-carta-contemplada', 'Passo a passo dos documentos para evitar retrabalho e atrasos na operação.', 'Imobiliária'],
        ['Dúvidas frequentes sobre taxa, lance e prazo de contemplação', 'duvidas-frequentes-taxa-lance-prazo', 'Respostas rápidas para as perguntas que mais recebemos no atendimento.', 'Dúvidas Frequentes'],
    ];

    $insPost = $pdo->prepare('INSERT INTO posts (title, slug, excerpt, content, author_name, status, is_featured, category_id, seo_title, seo_description, canonical_url, reading_time, published_at, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

    foreach ($basePosts as $i => $p) {
        $catId = (int)$pdo->query("SELECT id FROM categories WHERE name = " . $pdo->quote($p[3]))->fetchColumn();
        $content = "<h2>Visão geral</h2><p>{$p[1]} é um tema relevante para quem busca decisão segura e sustentável.</p><h2>Como aplicar</h2><p>Analise perfil, custos e documentação com antecedência. Mantenha reservas para cenários de volatilidade.</p><h2>Conclusão</h2><p>Com método e acompanhamento especializado, a estratégia fica previsível e alinhada ao objetivo patrimonial.</p>";
        $now = date('c', time() - ($i * 86400));
        $insPost->execute([
            $p[0], $p[1], $p[2], $content, 'Equipe Grupo Capital DF', 'published', $i < 2 ? 1 : 0, $catId,
            $p[0] . ' | Blog Grupo Capital DF', mb_substr($p[2], 0, 155), '', 4, $now, $now, $now
        ]);
    }
}
