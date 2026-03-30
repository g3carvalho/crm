<?php
$pageTitle = 'Oportunidades';
require __DIR__ . '/includes/header.php';
$pdo = db();
$id = (int)get('id',0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id > 0) {
    if (post('form_type') === 'interaction') {
        $stmt = $pdo->prepare('INSERT INTO interactions (opportunity_id,type,description,interaction_date,created_at,updated_at) VALUES (:op,:type,:desc,:date,NOW(),NOW())');
        $stmt->execute(['op'=>$id,'type'=>post('type'),'desc'=>post('description'),'date'=>post('interaction_date') ?: date('Y-m-d')]);
        $pdo->prepare('UPDATE opportunities SET updated_at=NOW(), last_contact_at=:d WHERE id=:id')->execute(['d'=>post('interaction_date') ?: date('Y-m-d'),'id'=>$id]);
    }
    if (post('form_type') === 'update') {
        $stmt = $pdo->prepare('UPDATE opportunities SET status=:status,priority=:priority,temperature=:temperature,estimated_value=:estimated,closed_value=:closed,next_action=:next_action,contact_type=:contact_type,next_followup=:next_followup,notes=:notes,tags=:tags,updated_at=NOW() WHERE id=:id');
        $stmt->execute([
            'status'=>post('status'),'priority'=>post('priority'),'temperature'=>post('temperature'),'estimated'=>(float)post('estimated_value',0),'closed'=>(float)post('closed_value',0) ?: null,'next_action'=>post('next_action'),'contact_type'=>post('contact_type'),'next_followup'=>post('next_followup') ?: null,'notes'=>post('notes'),'tags'=>post('tags'),'id'=>$id
        ]);
    }
    flash('success','Oportunidade atualizada.');
    redirect('oportunidade.php?id='.$id);
}

if ($id > 0) {
    $oppStmt = $pdo->prepare('SELECT o.*, l.company,l.phone,l.whatsapp,l.email,l.city,l.state FROM opportunities o LEFT JOIN leads l ON l.id=o.lead_id WHERE o.id=:id');
    $oppStmt->execute(['id'=>$id]);
    $opp = $oppStmt->fetch();
    if (!$opp) { redirect('oportunidade.php'); }
    $interactions = $pdo->prepare('SELECT * FROM interactions WHERE opportunity_id=:id ORDER BY interaction_date DESC, id DESC');
    $interactions->execute(['id'=>$id]); $interactions = $interactions->fetchAll();
    $tasks = $pdo->prepare('SELECT * FROM tasks WHERE opportunity_id=:id ORDER BY due_date'); $tasks->execute(['id'=>$id]); $tasks=$tasks->fetchAll();
    $proposals = $pdo->prepare('SELECT * FROM proposals WHERE opportunity_id=:id ORDER BY sent_date DESC'); $proposals->execute(['id'=>$id]); $proposals=$proposals->fetchAll();
}

$list = $pdo->query('SELECT id,lead_name,service_interest,stage_slug,status,priority,next_followup,updated_at FROM opportunities ORDER BY updated_at DESC')->fetchAll();
?>
<?php require __DIR__ . '/includes/sidebar.php'; ?><main class="content"><?php require __DIR__ . '/includes/topbar.php'; ?><div class="container-fluid px-4 pb-4">
<?php if($m=flash('success')):?><div class="alert alert-success"><?=e($m)?></div><?php endif; ?>
<div class="row g-3"><div class="col-lg-4"><div class="card p-3"><h2 class="h6">Lista</h2><?php foreach($list as $o): ?><a class="d-block border-bottom py-2" href="<?=e(base_url('oportunidade.php?id='.$o['id']))?>"><strong><?=e($o['lead_name'])?></strong><div class="small"><?=e($o['stage_slug'])?> · <?=e($o['service_interest'])?></div></a><?php endforeach; ?></div></div>
<div class="col-lg-8"><?php if(!empty($opp)): ?><div class="card p-3 mb-3"><h2 class="h6">Detalhe</h2><p><strong><?=e($opp['lead_name'])?></strong> · <?=e($opp['company'])?></p><p class="small">Contato: <?=e($opp['phone'])?> / <?=e($opp['whatsapp'])?> / <?=e($opp['email'])?></p>
<form method="post" class="row g-2"><input type="hidden" name="form_type" value="update"><div class="col-md-4"><input name="status" class="form-control" value="<?=e($opp['status'])?>"></div><div class="col-md-4"><input name="priority" class="form-control" value="<?=e($opp['priority'])?>"></div><div class="col-md-4"><input name="temperature" class="form-control" value="<?=e($opp['temperature'])?>"></div><div class="col-md-4"><input type="number" step="0.01" name="estimated_value" class="form-control" value="<?=e((string)$opp['estimated_value'])?>"></div><div class="col-md-4"><input type="number" step="0.01" name="closed_value" class="form-control" value="<?=e((string)$opp['closed_value'])?>"></div><div class="col-md-4"><input name="next_action" class="form-control" value="<?=e((string)$opp['next_action'])?>"></div><div class="col-md-4"><input name="contact_type" class="form-control" value="<?=e((string)$opp['contact_type'])?>"></div><div class="col-md-4"><input type="date" name="next_followup" class="form-control" value="<?=e((string)$opp['next_followup'])?>"></div><div class="col-md-4"><input name="tags" class="form-control" value="<?=e((string)$opp['tags'])?>"></div><div class="col-12"><textarea name="notes" class="form-control" rows="2"><?=e((string)$opp['notes'])?></textarea></div><div class="col-12"><button class="btn btn-primary">Salvar</button></div></form></div>
<div class="card p-3 mb-3"><h2 class="h6">Registrar interação</h2><form method="post" class="row g-2"><input type="hidden" name="form_type" value="interaction"><div class="col-md-4"><select name="type" class="form-select"><option>ligacao</option><option>mensagem</option><option>reuniao</option><option>proposta_enviada</option><option>retorno_recebido</option><option>observacao</option><option>mudanca_etapa</option><option>perda</option><option>arquivamento</option></select></div><div class="col-md-4"><input type="date" name="interaction_date" class="form-control"></div><div class="col-md-4"><button class="btn btn-outline-primary w-100">Adicionar</button></div><div class="col-12"><textarea name="description" class="form-control" required></textarea></div></form></div>
<div class="card p-3"><h2 class="h6">Timeline</h2><?php foreach($interactions as $i): ?><div class="border-bottom py-2"><strong><?=e($i['type'])?></strong> <span class="small text-muted"><?=e($i['interaction_date'])?></span><div><?=e($i['description'])?></div></div><?php endforeach; ?><h3 class="h6 mt-3">Tarefas vinculadas</h3><?php foreach($tasks as $t): ?><div class="small"><?=e($t['title'])?> · <?=e($t['status'])?></div><?php endforeach; ?><h3 class="h6 mt-3">Propostas vinculadas</h3><?php foreach($proposals as $p): ?><div class="small"><?=e($p['name'])?> · <?=e($p['status'])?> · R$ <?=number_format((float)$p['value'],2,',','.')?></div><?php endforeach; ?></div>
<?php else: ?><div class="card p-3">Selecione uma oportunidade.</div><?php endif; ?></div></div>
</div></main><?php require __DIR__ . '/includes/footer.php'; ?>
