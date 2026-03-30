<?php
$pageTitle = 'Propostas';
require __DIR__ . '/includes/header.php';
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $stmt=$pdo->prepare('INSERT INTO proposals (name,opportunity_id,service,value,sent_date,status,close_chance,expected_close_date,notes,created_at,updated_at) VALUES (:name,:op,:service,:value,:sent,:status,:chance,:expected,:notes,NOW(),NOW())');
  $stmt->execute(['name'=>post('name'),'op'=>post('opportunity_id'),'service'=>post('service'),'value'=>(float)post('value'),'sent'=>post('sent_date'),'status'=>post('status'),'chance'=>(int)post('close_chance'),'expected'=>post('expected_close_date'),'notes'=>post('notes')]);
  flash('success','Proposta salva.'); redirect('propostas.php');
}
$proposals=$pdo->query('SELECT p.*, o.lead_name FROM proposals p LEFT JOIN opportunities o ON o.id=p.opportunity_id ORDER BY p.sent_date DESC')->fetchAll();
$opps=$pdo->query('SELECT id,lead_name,service_interest FROM opportunities ORDER BY lead_name')->fetchAll();
?>
<?php require __DIR__ . '/includes/sidebar.php'; ?><main class="content"><?php require __DIR__ . '/includes/topbar.php'; ?><div class="container-fluid px-4 pb-4"><?php if($m=flash('success')):?><div class="alert alert-success"><?=e($m)?></div><?php endif; ?>
<div class="card p-3 mb-3"><h2 class="h6">Nova proposta</h2><form method="post" class="row g-2"><div class="col-md-3"><input name="name" required class="form-control" placeholder="Nome"></div><div class="col-md-3"><select name="opportunity_id" class="form-select" required><?php foreach($opps as $o): ?><option value="<?=$o['id']?>"><?=e($o['lead_name'])?></option><?php endforeach; ?></select></div><div class="col-md-2"><input name="service" class="form-control" placeholder="Serviço"></div><div class="col-md-2"><input type="number" step="0.01" name="value" class="form-control" placeholder="Valor"></div><div class="col-md-2"><input type="date" name="sent_date" class="form-control" required></div><div class="col-md-2"><select name="status" class="form-select"><option>rascunho</option><option>enviada</option><option>em_analise</option><option>aprovada</option><option>recusada</option></select></div><div class="col-md-2"><input type="number" name="close_chance" class="form-control" placeholder="Chance %"></div><div class="col-md-2"><input type="date" name="expected_close_date" class="form-control"></div><div class="col-md-4"><input name="notes" class="form-control" placeholder="Observações"></div><div class="col-md-2"><button class="btn btn-primary w-100">Salvar</button></div></form></div>
<div class="card p-0"><table class="table mb-0"><thead><tr><th>Proposta</th><th>Lead</th><th>Serviço</th><th>Valor</th><th>Status</th><th>Envio</th></tr></thead><tbody><?php foreach($proposals as $p): ?><tr><td><?=e($p['name'])?></td><td><?=e($p['lead_name'])?></td><td><?=e($p['service'])?></td><td>R$ <?=number_format((float)$p['value'],2,',','.')?></td><td><?=e($p['status'])?></td><td><?=e($p['sent_date'])?></td></tr><?php endforeach; ?></tbody></table></div>
</div></main><?php require __DIR__ . '/includes/footer.php'; ?>
