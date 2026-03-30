<?php
$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';
$metrics = dashboardMetrics();
$pdo = db();
$stageData = $pdo->query('SELECT stage_slug, COUNT(*) total FROM opportunities GROUP BY stage_slug')->fetchAll();
$originData = $pdo->query('SELECT lead_source, COUNT(*) total FROM leads GROUP BY lead_source')->fetchAll();
$salesByService = $pdo->query("SELECT service_interest, COALESCE(SUM(closed_value),0) total FROM opportunities WHERE stage_slug='venda' GROUP BY service_interest")->fetchAll();
$leadsPeriod = $pdo->query('SELECT DATE(created_at) d, COUNT(*) total FROM leads WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY d')->fetchAll();
$followups = $pdo->query("SELECT id, lead_name, next_action, next_followup FROM opportunities WHERE next_followup=CURRENT_DATE() AND stage_slug NOT IN ('venda','perdido','arquivado') ORDER BY priority DESC LIMIT 10")->fetchAll();
$lateTasks = $pdo->query("SELECT title, due_date, status FROM tasks WHERE due_date < CURRENT_DATE() AND status <> 'concluida' ORDER BY due_date LIMIT 10")->fetchAll();
$stalled = $pdo->query("SELECT id, lead_name, updated_at FROM opportunities WHERE updated_at < DATE_SUB(NOW(), INTERVAL 7 DAY) AND stage_slug NOT IN ('venda','perdido','arquivado') ORDER BY updated_at LIMIT 10")->fetchAll();
?>
<?php require __DIR__ . '/includes/sidebar.php'; ?>
<main class="content">
  <?php require __DIR__ . '/includes/topbar.php'; ?>
  <div class="container-fluid px-4 pb-4">
    <div class="row g-3 mb-3">
      <?php $cards=[['Leads',$metrics['total_leads']],['Novos no mês',$metrics['new_month']],['Oportunidades abertas',$metrics['open_opps']],['Vendas no mês',$metrics['sales_month']],['Conversão',number_format($metrics['conversion_rate'],1).'%'],['Ticket médio','R$ '.number_format($metrics['avg_ticket'],2,',','.')],['Follow-ups hoje',$metrics['today_followups']],['Tarefas atrasadas',$metrics['late_tasks']]]; ?>
      <?php foreach($cards as [$t,$v]): ?><div class="col-md-3"><div class="card kpi"><div class="small text-muted"><?=e($t)?></div><div class="h4 mb-0"><?=e((string)$v)?></div></div></div><?php endforeach; ?>
    </div>
    <div class="row g-3">
      <div class="col-lg-8"><div class="card p-3"><h2 class="h6">Leads por período</h2><canvas id="chartLeads" data-series='<?= e(json_encode($leadsPeriod)) ?>'></canvas></div></div>
      <div class="col-lg-4"><div class="card p-3"><h2 class="h6">Funil por etapa</h2><canvas id="chartFunnel" data-series='<?= e(json_encode($stageData)) ?>'></canvas></div></div>
      <div class="col-lg-6"><div class="card p-3"><h2 class="h6">Vendas por serviço</h2><canvas id="chartSalesService" data-series='<?= e(json_encode($salesByService)) ?>'></canvas></div></div>
      <div class="col-lg-6"><div class="card p-3"><h2 class="h6">Origem dos leads</h2><canvas id="chartOrigin" data-series='<?= e(json_encode($originData)) ?>'></canvas></div></div>
    </div>
    <div class="row g-3 mt-1">
      <div class="col-md-4"><div class="card p-3"><h2 class="h6">Follow-ups de hoje</h2><?php foreach($followups as $f): ?><div class="border-bottom py-2"><strong><?=e($f['lead_name'])?></strong><div class="small"><?=e($f['next_action'])?></div></div><?php endforeach; ?></div></div>
      <div class="col-md-4"><div class="card p-3"><h2 class="h6">Tarefas atrasadas</h2><?php foreach($lateTasks as $t): ?><div class="border-bottom py-2"><strong><?=e($t['title'])?></strong><div class="small"><?=e($t['due_date'])?></div></div><?php endforeach; ?></div></div>
      <div class="col-md-4"><div class="card p-3"><h2 class="h6">Oportunidades paradas</h2><?php foreach($stalled as $s): ?><div class="border-bottom py-2"><a href="<?=e(base_url('oportunidade.php?id='.$s['id']))?>"><?=e($s['lead_name'])?></a><div class="small"><?=e($s['updated_at'])?></div></div><?php endforeach; ?></div></div>
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
