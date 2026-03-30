<?php
$pageTitle = 'Relatórios';
require __DIR__ . '/includes/header.php';
$pdo = db();
$reports = [
  'Leads por origem' => $pdo->query('SELECT lead_source label, COUNT(*) total FROM leads GROUP BY lead_source')->fetchAll(),
  'Leads por serviço' => $pdo->query('SELECT service_interest label, COUNT(*) total FROM leads GROUP BY service_interest')->fetchAll(),
  'Conversão por origem' => $pdo->query("SELECT lead_source label, ROUND(SUM(stage_slug='venda')/COUNT(*)*100,1) total FROM opportunities GROUP BY lead_source")->fetchAll(),
  'Conversão por serviço' => $pdo->query("SELECT service_interest label, ROUND(SUM(stage_slug='venda')/COUNT(*)*100,1) total FROM opportunities GROUP BY service_interest")->fetchAll(),
  'Oportunidades por etapa' => $pdo->query('SELECT stage_slug label, COUNT(*) total FROM opportunities GROUP BY stage_slug')->fetchAll(),
  'Perdas por motivo' => $pdo->query('SELECT lr.name label, COUNT(*) total FROM opportunities o LEFT JOIN lost_reasons lr ON lr.id=o.lost_reason_id WHERE o.stage_slug="perdido" GROUP BY lr.name')->fetchAll(),
  'Arquivamentos por motivo' => $pdo->query('SELECT ar.name label, COUNT(*) total FROM opportunities o LEFT JOIN archive_reasons ar ON ar.id=o.archive_reason_id WHERE o.stage_slug="arquivado" GROUP BY ar.name')->fetchAll(),
  'Vendas por período' => $pdo->query("SELECT DATE_FORMAT(sale_date,'%Y-%m') label, COUNT(*) total FROM opportunities WHERE stage_slug='venda' GROUP BY DATE_FORMAT(sale_date,'%Y-%m')")->fetchAll(),
  'Valor fechado por período' => $pdo->query("SELECT DATE_FORMAT(sale_date,'%Y-%m') label, SUM(closed_value) total FROM opportunities WHERE stage_slug='venda' GROUP BY DATE_FORMAT(sale_date,'%Y-%m')")->fetchAll(),
  'Tempo médio até venda (dias)' => $pdo->query("SELECT service_interest label, ROUND(AVG(DATEDIFF(sale_date,created_at)),1) total FROM opportunities WHERE stage_slug='venda' GROUP BY service_interest")->fetchAll(),
  'Sem atualização > 7 dias' => $pdo->query("SELECT lead_name label, DATEDIFF(NOW(),updated_at) total FROM opportunities WHERE updated_at < DATE_SUB(NOW(), INTERVAL 7 DAY) AND stage_slug NOT IN ('venda','perdido','arquivado')")->fetchAll(),
];
?>
<?php require __DIR__ . '/includes/sidebar.php'; ?><main class="content"><?php require __DIR__ . '/includes/topbar.php'; ?><div class="container-fluid px-4 pb-4"><div class="row g-3"><?php foreach($reports as $title => $rows): ?><div class="col-lg-6"><div class="card p-3"><h2 class="h6"><?=e($title)?></h2><table class="table table-sm"><tbody><?php foreach($rows as $r): ?><tr><td><?=e((string)$r['label'])?></td><td class="text-end"><?=e((string)$r['total'])?></td></tr><?php endforeach; ?></tbody></table></div></div><?php endforeach; ?></div></div></main><?php require __DIR__ . '/includes/footer.php'; ?>
