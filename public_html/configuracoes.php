<?php
$pageTitle = 'Configurações';
require __DIR__ . '/includes/header.php';
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (post('type') === 'password') {
        $password = (string) post('password');
        if (strlen($password) >= 6) {
            $stmt=$pdo->prepare('UPDATE users SET password_hash=:p, updated_at=NOW() WHERE id=:id');
            $stmt->execute(['p'=>password_hash($password, PASSWORD_DEFAULT),'id'=>user()['id']]);
            flash('success','Senha atualizada.');
        } else flash('error','Senha precisa ter 6+ caracteres.');
    }
    redirect('configuracoes.php');
}
?>
<?php require __DIR__ . '/includes/sidebar.php'; ?><main class="content"><?php require __DIR__ . '/includes/topbar.php'; ?><div class="container-fluid px-4 pb-4"><?php if($m=flash('success')):?><div class="alert alert-success"><?=e($m)?></div><?php endif; ?><?php if($m=flash('error')):?><div class="alert alert-danger"><?=e($m)?></div><?php endif; ?>
<div class="card p-3 mb-3"><h2 class="h6">Configuração básica</h2><p class="small text-muted">BASE_URL e banco são definidos em <code>app/config.php</code> ou variáveis de ambiente.</p></div>
<div class="card p-3"><h2 class="h6">Alterar senha</h2><form method="post" class="row g-2"><input type="hidden" name="type" value="password"><div class="col-md-4"><input type="password" name="password" class="form-control" placeholder="Nova senha" required></div><div class="col-md-2"><button class="btn btn-primary w-100">Salvar</button></div></form></div>
</div></main><?php require __DIR__ . '/includes/footer.php'; ?>
