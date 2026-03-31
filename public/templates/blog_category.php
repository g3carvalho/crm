<section class="blog-head">
  <h1><?= e($category['name']) ?></h1>
  <p><?= e($category['description']) ?></p>
</section>
<div class="cards">
<?php foreach($posts as $p): ?>
<article class="card">
<h3><a href="/blog/post/<?= e($p['slug']) ?>"><?= e($p['title']) ?></a></h3>
<p><?= e($p['excerpt']) ?></p>
</article>
<?php endforeach; ?>
</div>
<div class="pagination"><?php for($i=1;$i<=$pages;$i++): ?><a class="<?= $i===$page?'active':'' ?>" href="/blog/categoria/<?= e($category['slug']) ?>?page=<?= $i ?>"><?= $i ?></a><?php endfor; ?></div>
