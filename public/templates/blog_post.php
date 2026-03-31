<nav class="breadcrumbs"><a href="/">Início</a> / <a href="/blog">Blog</a> / <a href="/blog/categoria/<?= e($post['category_slug'] ?? '') ?>"><?= e($post['category_name'] ?? 'Categoria') ?></a> / <span><?= e($post['title']) ?></span></nav>
<article class="post">
  <h1><?= e($post['title']) ?></h1>
  <p class="excerpt"><?= e($post['excerpt']) ?></p>
  <p class="meta">Por <?= e($post['author_name']) ?> • <?= date('d/m/Y', strtotime($post['published_at'] ?? $post['created_at'])) ?> • <?= (int)$post['reading_time'] ?> min de leitura</p>
  <?php if (!empty($post['featured_image'])): ?>
    <img src="<?= e($post['featured_image']) ?>" alt="<?= e($post['image_alt'] ?: $post['title']) ?>" loading="lazy" class="featured-image">
  <?php endif; ?>
  <?php if (!empty($toc)): ?>
  <aside class="toc"><strong>Índice</strong><ul><?php foreach($toc as $item): ?><li><?= e(strip_tags($item)) ?></li><?php endforeach; ?></ul></aside>
  <?php endif; ?>
  <div class="content"><?= $post['content'] ?></div>
  <p class="cta">Precisa de uma análise personalizada? <a class="btn" href="/contato">Fale com nossa equipe</a></p>
</article>
<section class="grid two-col">
<div>
<h3>Posts relacionados</h3>
<ul class="list"><?php foreach($related as $r): ?><li><a href="/blog/post/<?= e($r['slug']) ?>"><?= e($r['title']) ?></a></li><?php endforeach; ?></ul>
</div>
<div>
<h3>Navegação</h3>
<ul class="list">
<?php if($prev): ?><li>Anterior: <a href="/blog/post/<?= e($prev['slug']) ?>"><?= e($prev['title']) ?></a></li><?php endif; ?>
<?php if($next): ?><li>Próximo: <a href="/blog/post/<?= e($next['slug']) ?>"><?= e($next['title']) ?></a></li><?php endif; ?>
</ul>
</div>
</section>
<script type="application/ld+json">{"@context":"https://schema.org","@type":"BreadcrumbList","itemListElement":[{"@type":"ListItem","position":1,"name":"Início","item":"<?= e(site_url()) ?>"},{"@type":"ListItem","position":2,"name":"Blog","item":"<?= e(site_url('blog')) ?>"},{"@type":"ListItem","position":3,"name":"<?= e($post['title']) ?>","item":"<?= e(site_url('blog/post/'.$post['slug'])) ?>"}]}</script>
<script type="application/ld+json">{"@context":"https://schema.org","@type":"BlogPosting","headline":"<?= e($post['title']) ?>","description":"<?= e($post['excerpt']) ?>","datePublished":"<?= e($post['published_at'] ?? $post['created_at']) ?>","author":{"@type":"Person","name":"<?= e($post['author_name']) ?>"}}</script>
