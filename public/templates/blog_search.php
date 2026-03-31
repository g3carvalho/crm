<section class="blog-head">
  <h1>Busca no Blog</h1>
  <p>Resultados para: <strong><?= e($query) ?></strong></p>
</section>
<div class="cards">
<?php if (empty($posts)): ?><p>Nenhum conteúdo encontrado.</p><?php endif; ?>
<?php foreach($posts as $p): ?>
<article class="card">
<h3><a href="/blog/post/<?= e($p['slug']) ?>"><?= e($p['title']) ?></a></h3>
<p><?= e($p['excerpt']) ?></p>
</article>
<?php endforeach; ?>
</div>
