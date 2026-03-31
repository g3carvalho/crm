<h1>Admin do Blog</h1>
<?php if(!empty($error)): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
<form method="post" action="/admin/login">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label>Email <input type="email" name="email" required></label>
<label>Senha <input type="password" name="password" required></label>
<button class="btn" type="submit">Entrar</button>
</form>
