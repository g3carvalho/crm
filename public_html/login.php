<?php
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/auth.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var((string) post('email'), FILTER_VALIDATE_EMAIL) ?: '';
    $password = (string) post('password');

    if ($email && $password && attemptLogin($email, $password)) {
        redirect('dashboard.php');
    }

    flash('error', 'Credenciais inválidas.');
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - <?= e(APP_NAME) ?></title>
  <link rel="icon" href="<?= e(base_url('assets/img/favicon.svg')) ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(base_url('assets/css/style.css')) ?>">
</head>
<body class="login-bg">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow-sm border-0">
        <div class="card-body p-4">
          <div class="text-center mb-4">
            <img src="<?= e(base_url('assets/img/logo.svg')) ?>" alt="Logo" height="42">
            <h1 class="h4 mt-3">Acessar CRM</h1>
          </div>
          <?php if ($error = flash('error')): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
          <?php endif; ?>
          <form method="post">
            <div class="mb-3">
              <label class="form-label">E-mail</label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Senha</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <button class="btn btn-primary w-100">Entrar</button>
          </form>
          <p class="small text-muted mt-3 mb-0">Admin padrão: admin@crm.local / 123456</p>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
