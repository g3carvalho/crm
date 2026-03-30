<?php
$pageTitle = 'Tarefas';
require __DIR__ . '/includes/header.php';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('INSERT INTO tasks (title,description,lead_id,opportunity_id,priority,due_date,status,type,notes,created_at,updated_at) VALUES (:title,:description,:lead_id,:opportunity_id,:priority,:due_date,:status,:type,:notes,NOW(),NOW())');
    $stmt->execute([
      'title'=>post('title'),'description'=>post('description'),'lead_id'=>post('lead_id') ?: null,'opportunity_id'=>post('opportunity_id') ?: null,'priority'=>post('priority'),'due_date'=>post('due_date'),'status'=>post('status'),'type'=>post('type'),'notes'=>post('notes')
    ]);
    flash('success','Tarefa criada.'); redirect('tarefas.php');
}
$tasks=$pdo->query('SELECT t.*, o.lead_name FROM tasks t LEFT JOIN opportunities o ON o.id=t.opportunity_id ORDER BY due_date ASC')->fetchAll();
$opps=$pdo->query('SELECT id,lead_name FROM opportunities ORDER BY lead_name')->fetchAll();
?>
<?php require __DIR__ . '/includes/sidebar.php'; ?><main class="content"><?php require __DIR__ . '/includes/topbar.php'; ?><div class="container-fluid px-4 pb-4">
<?php if($m=flash('success')):?><div class="alert alert-success"><?=e($m)?></div><?php endif; ?>
<div class="card p-3 mb-3"><h2 class="h6">Nova tarefa</h2><form method="post" class="row g-2"><div class="col-md-3"><input name="title" class="form-control" required placeholder="Título"></div><div class="col-md-3"><input name="description" class="form-control" placeholder="Descrição"></div><div class="col-md-2"><select name="opportunity_id" class="form-select"><option value="">Oportunidade</option><?php foreach($opps as $o): ?><option value="<?=$o['id']?>"><?=e($o['lead_name'])?></option><?php endforeach; ?></select></div><div class="col-md-1"><select name="priority" class="form-select"><option>baixa</option><option selected>media</option><option>alta</option></select></div><div class="col-md-2"><input type="date" name="due_date" class="form-control" required></div><div class="col-md-1"><select name="status" class="form-select"><option>pendente</option><option>em_andamento</option><option>concluida</option><option>atrasada</option></select></div><div class="col-md-2"><select name="type" class="form-select"><option>ligacao</option><option>mensagem</option><option>reuniao</option><option>enviar_proposta</option><option>cobrar_retorno</option><option>analise</option><option>outro</option></select></div><div class="col-md-8"><input name="notes" class="form-control" placeholder="Observações"></div><div class="col-md-2"><button class="btn btn-primary w-100">Salvar</button></div></form></div>
<div class="card p-0"><table class="table mb-0"><thead><tr><th>Título</th><th>Oportunidade</th><th>Prioridade</th><th>Prazo</th><th>Status</th><th>Tipo</th></tr></thead><tbody><?php foreach($tasks as $t): ?><tr><td><?=e($t['title'])?></td><td><?=e($t['lead_name'])?></td><td><?=e($t['priority'])?></td><td><?=e($t['due_date'])?></td><td><?=e($t['status'])?></td><td><?=e($t['type'])?></td></tr><?php endforeach; ?></tbody></table></div>
</div></main><?php require __DIR__ . '/includes/footer.php'; ?>
