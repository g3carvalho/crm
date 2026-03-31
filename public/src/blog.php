<?php

function site_url(string $path = ''): string
{
    $base = rtrim((require __DIR__ . '/../config.php')['base_url'], '/');
    return $base . '/' . ltrim($path, '/');
}

function current_full_url(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/');
}

function fetch_categories(PDO $pdo): array
{
    return $pdo->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
}

function fetch_tags_for_post(PDO $pdo, int $postId): array
{
    $stmt = $pdo->prepare('SELECT t.* FROM tags t JOIN post_tags pt ON pt.tag_id = t.id WHERE pt.post_id = ? ORDER BY t.name');
    $stmt->execute([$postId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetch_posts(PDO $pdo, int $limit = 9, int $offset = 0, ?int $categoryId = null, ?string $search = null): array
{
    $sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM posts p LEFT JOIN categories c ON c.id = p.category_id
            WHERE p.status = "published" AND (p.scheduled_at IS NULL OR p.scheduled_at <= :now)';
    $params = [':now' => date('c')];

    if ($categoryId) {
        $sql .= ' AND p.category_id = :cat';
        $params[':cat'] = $categoryId;
    }
    if ($search) {
        $sql .= ' AND (p.title LIKE :search OR p.excerpt LIKE :search OR p.content LIKE :search)';
        $params[':search'] = '%' . $search . '%';
    }

    $sql .= ' ORDER BY COALESCE(p.published_at, p.created_at) DESC LIMIT :limit OFFSET :offset';
    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function count_posts(PDO $pdo, ?int $categoryId = null, ?string $search = null): int
{
    $sql = 'SELECT COUNT(*) FROM posts p WHERE p.status = "published" AND (p.scheduled_at IS NULL OR p.scheduled_at <= :now)';
    $params = [':now' => date('c')];
    if ($categoryId) {
        $sql .= ' AND p.category_id = :cat';
        $params[':cat'] = $categoryId;
    }
    if ($search) {
        $sql .= ' AND (p.title LIKE :search OR p.excerpt LIKE :search OR p.content LIKE :search)';
        $params[':search'] = '%' . $search . '%';
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}

function find_post_by_slug(PDO $pdo, string $slug): ?array
{
    $stmt = $pdo->prepare('SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM posts p LEFT JOIN categories c ON c.id = p.category_id WHERE p.slug = ? LIMIT 1');
    $stmt->execute([$slug]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    return $post ?: null;
}

function resolve_redirect_slug(PDO $pdo, string $slug): ?string
{
    $stmt = $pdo->prepare('SELECT p.slug FROM slug_redirects s JOIN posts p ON p.id = s.post_id WHERE s.old_slug = ? LIMIT 1');
    $stmt->execute([$slug]);
    $new = $stmt->fetchColumn();
    return $new ?: null;
}

function estimate_reading_time(string $html): int
{
    $words = str_word_count(strip_tags($html));
    return max(1, (int)ceil($words / 220));
}

function post_toc(string $html): array
{
    preg_match_all('/<h2>(.*?)<\/h2>/i', $html, $matches);
    return $matches[1] ?? [];
}
