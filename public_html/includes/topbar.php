<header class="topbar d-flex justify-content-between align-items-center px-4 py-3">
  <h1 class="h5 m-0"><?= e($pageTitle ?? APP_NAME) ?></h1>
  <div>
    <span class="me-3 small text-muted">Olá, <?= e(user()['name'] ?? '') ?></span>
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(base_url('logout.php')) ?>">Sair</a>
  </div>
</header>
