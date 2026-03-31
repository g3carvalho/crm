<section class="blog-head">
  <h1>Blog Grupo Capital DF</h1>
  <form method="get" action="/blog/busca" class="search-form">
    <input type="search" name="q" placeholder="Buscar por assunto" value="<?= e($query ?? '') ?>">
    <button class="btn" type="submit">Buscar</button>
  </form>
</section>
<?php if (!empty($featured)): $top = $featured[0]; ?>
<article class="featured-card">
  <p class="badge">Destaque</p>
  <h2><a href="/blog/post/<?= e($top['slug']) ?>"><?= e($top['title']) ?></a></h2>
  <p><?= e($top['excerpt']) ?></p>
</article>
<?php endif; ?>
<section class="grid two-col">
  <div>
    <h3>Últimos posts</h3>
    <div class="cards">
      <?php foreach ($posts as $p): ?>
      <article class="card">
        <h4><a href="/blog/post/<?= e($p['slug']) ?>"><?= e($p['title']) ?></a></h4>
        <p><?= e($p['excerpt']) ?></p>
        <small><?= e($p['category_name'] ?? 'Sem categoria') ?> • <?= date('d/m/Y', strtotime($p['published_at'] ?? $p['created_at'])) ?></small>
      </article>
      <?php endforeach; ?>
    </div>
    <div class="pagination">
      <?php for($i=1;$i<=$pages;$i++): ?>
        <a class="<?= $i===$page ? 'active' : '' ?>" href="/blog?page=<?= $i ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
  </div>
  <aside>
    <h3>Categorias</h3>
    <ul class="list">
      <?php foreach($categories as $c): ?>
      <li><a href="/blog/categoria/<?= e($c['slug']) ?>"><?= e($c['name']) ?></a></li>
      <?php endforeach; ?>
    </ul>
    <h3>Recentes</h3>
    <ul class="list">
      <?php foreach(array_slice($posts,0,5) as $r): ?>
      <li><a href="/blog/post/<?= e($r['slug']) ?>"><?= e($r['title']) ?></a></li>
      <?php endforeach; ?>
    </ul>
  </aside>
</section>
