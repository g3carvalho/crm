<header class="admin-top"><h1>Categorias</h1><a href="/admin">Voltar</a></header>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label>Nome <input type="text" name="name" required></label>
<label>Descrição <input type="text" name="description"></label>
<button class="btn" type="submit">Adicionar</button></form>
<ul class="list"><?php foreach($categories as $c): ?><li><?= e($c['name']) ?> (<?= e($c['slug']) ?>)</li><?php endforeach; ?></ul>
