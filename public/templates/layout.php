<?php
$metaTitle = $metaTitle ?? 'Grupo Capital DF';
$metaDescription = $metaDescription ?? 'Grupo Capital DF: soluções premium com planejamento patrimonial e consórcios.';
$canonical = $canonical ?? current_full_url();
$noindex = $noindex ?? false;
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($metaTitle) ?></title>
<meta name="description" content="<?= e($metaDescription) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<?php if ($noindex): ?><meta name="robots" content="noindex,nofollow"><?php endif; ?>
<meta property="og:type" content="website">
<meta property="og:title" content="<?= e($metaTitle) ?>">
<meta property="og:description" content="<?= e($metaDescription) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:site_name" content="Grupo Capital DF">
<link rel="stylesheet" href="/assets/css/site.css">
<script type="application/ld+json">{"@context":"https://schema.org","@type":"Organization","name":"Grupo Capital DF","url":"<?= e(site_url()) ?>"}</script>
<script type="application/ld+json">{"@context":"https://schema.org","@type":"WebSite","name":"Grupo Capital DF","url":"<?= e(site_url()) ?>","potentialAction":{"@type":"SearchAction","target":"<?= e(site_url('blog/busca?q={search_term_string}')) ?>","query-input":"required name=search_term_string"}}</script>
</head>
<body>
<?php include __DIR__ . '/partials/header.php'; ?>
<main class="container">
<?php include __DIR__ . '/' . $view . '.php'; ?>
</main>
<?php include __DIR__ . '/partials/footer.php'; ?>
<script src="/assets/js/site.js" defer></script>
</body>
</html>
