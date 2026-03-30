<aside class="sidebar">
  <div class="brand px-3 py-3"><img src="<?= e(base_url('assets/img/logo.svg')) ?>" height="32" alt="CRM"></div>
  <nav class="nav flex-column px-2">
    <?php $menu = ['dashboard.php'=>'Dashboard','pipeline.php'=>'Pipeline','leads.php'=>'Leads','oportunidade.php'=>'Oportunidades','tarefas.php'=>'Tarefas','propostas.php'=>'Propostas','relatorios.php'=>'Relatórios','configuracoes.php'=>'Configurações']; ?>
    <?php foreach ($menu as $file => $label): ?>
      <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === $file ? 'active' : '' ?>" href="<?= e(base_url($file)) ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
  </nav>
</aside>
