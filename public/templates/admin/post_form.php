<header class="admin-top"><h1><?= $post ? 'Editar post' : 'Novo post' ?></h1><a href="/admin">Voltar</a></header>
<?php if(!empty($error)): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label>Título* <input type="text" name="title" required value="<?= e($post['title'] ?? '') ?>"></label>
<label>Slug <input type="text" name="slug" value="<?= e($post['slug'] ?? '') ?>"></label>
<label>Resumo* <textarea name="excerpt" required><?= e($post['excerpt'] ?? '') ?></textarea></label>
<label>Conteúdo HTML* <textarea name="content" rows="12" required><?= e($post['content'] ?? '') ?></textarea></label>
<label>Autor* <input type="text" name="author_name" required value="<?= e($post['author_name'] ?? 'Equipe Grupo Capital DF') ?>"></label>
<label>Categoria
<select name="category_id"><option value="">Sem categoria</option><?php foreach($categories as $c): ?><option value="<?= $c['id'] ?>" <?= (isset($post['category_id']) && (int)$post['category_id']===(int)$c['id'])?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select>
</label>
<label>Tags (separadas por vírgula) <input type="text" name="tags" value="<?= e($tagsCsv ?? '') ?>"></label>
<label>Título SEO <input type="text" name="seo_title" value="<?= e($post['seo_title'] ?? '') ?>"></label>
<label>Meta description <textarea name="seo_description"><?= e($post['seo_description'] ?? '') ?></textarea></label>
<label>Canonical <input type="url" name="canonical_url" value="<?= e($post['canonical_url'] ?? '') ?>"></label>
<label>Imagem destacada <input type="file" name="featured_image" accept="image/jpeg,image/png,image/webp"></label>
<label>Alt da imagem <input type="text" name="image_alt" value="<?= e($post['image_alt'] ?? '') ?>"></label>
<label>Status
<select name="status">
<?php foreach(['draft'=>'Rascunho','published'=>'Publicado','scheduled'=>'Agendado'] as $k=>$v): ?><option value="<?= $k ?>" <?= (($post['status'] ?? 'draft')===$k)?'selected':'' ?>><?= $v ?></option><?php endforeach; ?>
</select></label>
<label>Agendar para <input type="datetime-local" name="scheduled_at" value="<?= !empty($post['scheduled_at']) ? date('Y-m-d\TH:i', strtotime($post['scheduled_at'])) : '' ?>"></label>
<label><input type="checkbox" name="is_featured" value="1" <?= !empty($post['is_featured'])?'checked':'' ?>> Marcar como destaque</label>
<p class="checklist">Checklist: título, resumo, conteúdo, categoria, SEO e imagem revisados.</p>
<button class="btn" type="submit">Salvar</button>
<?php if($post): ?><button class="btn" name="duplicate" value="1">Duplicar post</button><button class="btn btn-danger" name="delete" value="1" onclick="return confirm('Excluir post?')">Excluir</button><?php endif; ?>
</form>
