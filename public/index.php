<?php
require __DIR__ . '/src/bootstrap.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

function admin_render(string $view, array $data = []): void {
    extract($data, EXTR_SKIP);
    include __DIR__ . '/templates/admin/layout.php';
    exit;
}

if ($path === '/robots.txt') {
    header('Content-Type: text/plain; charset=utf-8');
    echo "User-agent: *\nAllow: /\nDisallow: /admin\nSitemap: " . site_url('sitemap.xml');
    exit;
}

if ($path === '/sitemap.xml') {
    header('Content-Type: application/xml; charset=utf-8');
    $posts = fetch_posts($pdo, 1000, 0);
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";
    echo '<url><loc>' . e(site_url()) . '</loc></url><url><loc>' . e(site_url('blog')) . '</loc></url>';
    foreach ($posts as $p) {
        echo '<url><loc>' . e(site_url('blog/post/' . $p['slug'])) . '</loc><lastmod>' . e(date('c', strtotime($p['updated_at']))) . '</lastmod></url>';
    }
    echo '</urlset>';
    exit;
}

if ($path === '/blog/rss.xml') {
    header('Content-Type: application/rss+xml; charset=utf-8');
    $posts = fetch_posts($pdo, 20, 0);
    echo '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel><title>Blog Grupo Capital DF</title><link>' . e(site_url('blog')) . '</link><description>Atualizações do blog</description>';
    foreach ($posts as $p) {
        echo '<item><title>' . e($p['title']) . '</title><link>' . e(site_url('blog/post/'.$p['slug'])) . '</link><description>' . e($p['excerpt']) . '</description><pubDate>' . date(DATE_RSS, strtotime($p['published_at'] ?? $p['created_at'])) . '</pubDate></item>';
    }
    echo '</channel></rss>';
    exit;
}

if ($path === '/admin/login' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    admin_render('login', ['view' => 'login', 'title' => 'Login Admin']);
}
if ($path === '/admin/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (is_locked_out($pdo, $config, $email)) {
        admin_render('login', ['view' => 'login', 'error' => 'Muitas tentativas. Tente mais tarde.']);
    }
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user || !password_verify($password, $user['password_hash'])) {
        register_failed_login($pdo, $email);
        admin_render('login', ['view' => 'login', 'error' => 'Credenciais inválidas.']);
    }
    clear_login_attempts($pdo, $email);
    session_regenerate_id(true);
    $_SESSION['admin_user'] = ['id' => $user['id'], 'name' => $user['name']];
    header('Location: /admin');
    exit;
}
if ($path === '/admin/logout') {
    session_destroy();
    header('Location: /admin/login'); exit;
}

if (str_starts_with($path, '/admin')) {
    require_admin();
    if ($path === '/admin') {
        $posts = $pdo->query('SELECT * FROM posts ORDER BY updated_at DESC')->fetchAll(PDO::FETCH_ASSOC);
        admin_render('dashboard', ['view' => 'dashboard', 'posts' => $posts, 'title' => 'Dashboard']);
    }
    if ($path === '/admin/categorias') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $name = trim($_POST['name'] ?? '');
            if ($name !== '') {
                $stmt = $pdo->prepare('INSERT OR IGNORE INTO categories (name, slug, description, seo_title, seo_description) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$name, slugify($name), trim($_POST['description'] ?? ''), $name . ' | Blog Grupo Capital DF', '']);
            }
        }
        $categories = fetch_categories($pdo);
        admin_render('categories', ['view' => 'categories', 'categories' => $categories, 'title' => 'Categorias']);
    }

    $isNew = $path === '/admin/post/novo';
    $isEdit = preg_match('#^/admin/post/editar/(\d+)$#', $path, $m);
    if ($isNew || $isEdit) {
        $post = null;
        if ($isEdit) {
            $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
            $stmt->execute([(int)$m[1]]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            if (!empty($_POST['delete']) && $post) {
                $pdo->prepare('DELETE FROM posts WHERE id = ?')->execute([$post['id']]);
                header('Location: /admin'); exit;
            }

            $title = trim($_POST['title'] ?? '');
            $excerpt = trim($_POST['excerpt'] ?? '');
            $content = trim($_POST['content'] ?? '');
            if ($title === '' || $excerpt === '' || $content === '') {
                $categories = fetch_categories($pdo);
                admin_render('post_form', ['view' => 'post_form', 'post' => $post, 'categories' => $categories, 'error' => 'Preencha título, resumo e conteúdo.']);
            }

            $slug = slugify(trim($_POST['slug'] ?? '') ?: $title);
            $existing = find_post_by_slug($pdo, $slug);
            if ($existing && (!$post || (int)$existing['id'] !== (int)$post['id'])) {
                $slug .= '-' . random_int(10, 99);
            }

            $featured = $post['featured_image'] ?? '';
            if (!empty($_FILES['featured_image']['name'])) {
                $fileName = validate_upload($_FILES['featured_image']);
                if ($fileName) {
                    move_uploaded_file($_FILES['featured_image']['tmp_name'], $config['upload_dir'] . '/' . $fileName);
                    $featured = $config['upload_url'] . '/' . $fileName;
                }
            }

            $status = $_POST['status'] ?? 'draft';
            $scheduledAt = !empty($_POST['scheduled_at']) ? date('c', strtotime($_POST['scheduled_at'])) : null;
            $publishedAt = ($status === 'published') ? date('c') : ($post['published_at'] ?? null);
            $reading = estimate_reading_time($content);

            if ($post) {
                if ($post['slug'] !== $slug) {
                    $pdo->prepare('INSERT OR IGNORE INTO slug_redirects (old_slug, post_id, created_at) VALUES (?, ?, ?)')->execute([$post['slug'], $post['id'], date('c')]);
                }
                $stmt = $pdo->prepare('UPDATE posts SET title=?,slug=?,excerpt=?,content=?,featured_image=?,image_alt=?,author_name=?,status=?,is_featured=?,category_id=?,seo_title=?,seo_description=?,canonical_url=?,reading_time=?,scheduled_at=?,published_at=?,updated_at=? WHERE id=?');
                $stmt->execute([$title,$slug,$excerpt,$content,$featured,trim($_POST['image_alt']??''),trim($_POST['author_name']??'Equipe Grupo Capital DF'),$status,!empty($_POST['is_featured'])?1:0,($_POST['category_id']?:null),trim($_POST['seo_title']??''),trim($_POST['seo_description']??''),trim($_POST['canonical_url']??''),$reading,$scheduledAt,$publishedAt,date('c'),$post['id']]);
                $postId = (int)$post['id'];
            } else {
                $stmt = $pdo->prepare('INSERT INTO posts (title,slug,excerpt,content,featured_image,image_alt,author_name,status,is_featured,category_id,seo_title,seo_description,canonical_url,reading_time,scheduled_at,published_at,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
                $stmt->execute([$title,$slug,$excerpt,$content,$featured,trim($_POST['image_alt']??''),trim($_POST['author_name']??'Equipe Grupo Capital DF'),$status,!empty($_POST['is_featured'])?1:0,($_POST['category_id']?:null),trim($_POST['seo_title']??''),trim($_POST['seo_description']??''),trim($_POST['canonical_url']??''),$reading,$scheduledAt,$publishedAt,date('c'),date('c')]);
                $postId = (int)$pdo->lastInsertId();
            }

            $pdo->prepare('DELETE FROM post_tags WHERE post_id = ?')->execute([$postId]);
            foreach (array_filter(array_map('trim', explode(',', $_POST['tags'] ?? ''))) as $tagName) {
                $tagSlug = slugify($tagName);
                $pdo->prepare('INSERT OR IGNORE INTO tags (name, slug) VALUES (?, ?)')->execute([$tagName, $tagSlug]);
                $tagId = (int)$pdo->query('SELECT id FROM tags WHERE slug = ' . $pdo->quote($tagSlug))->fetchColumn();
                $pdo->prepare('INSERT OR IGNORE INTO post_tags (post_id, tag_id) VALUES (?, ?)')->execute([$postId, $tagId]);
            }

            if (!empty($_POST['duplicate']) && $post) {
                $copyTitle = $title . ' (Cópia)';
                $copySlug = slugify($copyTitle) . '-' . random_int(10,99);
                $pdo->prepare('INSERT INTO posts (title,slug,excerpt,content,featured_image,image_alt,author_name,status,is_featured,category_id,seo_title,seo_description,canonical_url,reading_time,created_at,updated_at) SELECT ?,?,?,content,featured_image,image_alt,author_name,"draft",0,category_id,seo_title,seo_description,canonical_url,reading_time,?,? FROM posts WHERE id=?')->execute([$copyTitle,$copySlug,$excerpt,date('c'),date('c'),$postId]);
            }

            header('Location: /admin'); exit;
        }

        $categories = fetch_categories($pdo);
        $tagsCsv = '';
        if ($post) {
            $tags = fetch_tags_for_post($pdo, (int)$post['id']);
            $tagsCsv = implode(', ', array_column($tags, 'name'));
        }
        admin_render('post_form', ['view' => 'post_form', 'post' => $post, 'categories' => $categories, 'tagsCsv' => $tagsCsv, 'title' => 'Editor de Post']);
    }
}

if ($path === '/') {
    render_layout('home', ['metaTitle' => 'Grupo Capital DF', 'canonical' => site_url()]); exit;
}
if ($path === '/blog') {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 6;
    $posts = fetch_posts($pdo, $perPage, ($page - 1) * $perPage);
    $featured = $pdo->query('SELECT * FROM posts WHERE status = "published" AND is_featured = 1 ORDER BY published_at DESC LIMIT 3')->fetchAll(PDO::FETCH_ASSOC);
    $categories = fetch_categories($pdo);
    $total = count_posts($pdo);
    render_layout('blog_index', ['posts' => $posts, 'featured' => $featured, 'categories' => $categories, 'page' => $page, 'pages' => max(1, (int)ceil($total / $perPage)), 'metaTitle' => 'Blog | Grupo Capital DF', 'canonical' => site_url('blog')]);
    exit;
}
if (preg_match('#^/blog/categoria/([a-z0-9-]+)$#', $path, $m)) {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE slug = ? LIMIT 1');
    $stmt->execute([$m[1]]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$category) { http_response_code(404); render_layout('404'); exit; }
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 8;
    $posts = fetch_posts($pdo, $perPage, ($page-1)*$perPage, (int)$category['id']);
    $total = count_posts($pdo, (int)$category['id']);
    render_layout('blog_category', ['category'=>$category,'posts'=>$posts,'page'=>$page,'pages'=>max(1,(int)ceil($total/$perPage)), 'metaTitle' => ($category['seo_title'] ?: $category['name'] . ' | Blog Grupo Capital DF'), 'metaDescription' => ($category['seo_description'] ?: $category['description']), 'canonical' => site_url('blog/categoria/'.$category['slug'])]);
    exit;
}
if ($path === '/blog/busca') {
    $q = trim($_GET['q'] ?? '');
    $posts = $q === '' ? [] : fetch_posts($pdo, 30, 0, null, $q);
    render_layout('blog_search', ['query' => $q, 'posts' => $posts, 'metaTitle' => 'Busca: '.$q.' | Blog Grupo Capital DF', 'canonical' => site_url('blog/busca?q='.urlencode($q))]);
    exit;
}
if (preg_match('#^/blog/post/([a-z0-9-]+)$#', $path, $m)) {
    $slug = $m[1];
    $post = find_post_by_slug($pdo, $slug);
    if (!$post) {
        $newSlug = resolve_redirect_slug($pdo, $slug);
        if ($newSlug) {
            header('Location: /blog/post/' . $newSlug, true, 301); exit;
        }
        http_response_code(404); render_layout('404'); exit;
    }
    if ($post['status'] !== 'published') {
        http_response_code(404); render_layout('404'); exit;
    }
    $toc = post_toc($post['content']);
    $rel = $pdo->prepare('SELECT id,title,slug FROM posts WHERE status="published" AND category_id = ? AND id != ? ORDER BY published_at DESC LIMIT 3');
    $rel->execute([$post['category_id'], $post['id']]);
    $related = $rel->fetchAll(PDO::FETCH_ASSOC);
    $prev = $pdo->query('SELECT title,slug FROM posts WHERE status="published" AND id < ' . (int)$post['id'] . ' ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC) ?: null;
    $next = $pdo->query('SELECT title,slug FROM posts WHERE status="published" AND id > ' . (int)$post['id'] . ' ORDER BY id ASC LIMIT 1')->fetch(PDO::FETCH_ASSOC) ?: null;
    render_layout('blog_post', ['post'=>$post,'toc'=>$toc,'related'=>$related,'prev'=>$prev,'next'=>$next,'metaTitle'=>($post['seo_title'] ?: $post['title'].' | Blog Grupo Capital DF'),'metaDescription'=>($post['seo_description'] ?: $post['excerpt']),'canonical'=>($post['canonical_url'] ?: site_url('blog/post/'.$post['slug']))]);
    exit;
}

http_response_code(404);
render_layout('404');
