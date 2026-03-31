<header class="admin-top"><h1>Painel do Blog</h1><a class="btn" href="/admin/logout">Sair</a></header>
<p><a class="btn" href="/admin/post/novo">Novo post</a> <a class="btn" href="/admin/categorias">Categorias</a></p>
<table><thead><tr><th>Título</th><th>Status</th><th>Ações</th></tr></thead><tbody>
<?php foreach($posts as $p): ?>
<tr><td><?= e($p['title']) ?></td><td><?= e($p['status']) ?></td><td><a href="/admin/post/editar/<?= $p['id'] ?>">Editar</a> | <a href="/blog/post/<?= e($p['slug']) ?>" target="_blank">Prévia</a></td></tr>
<?php endforeach; ?>
</tbody></table>
