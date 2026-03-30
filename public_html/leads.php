<?php
$pageTitle = 'Leads';
require __DIR__ . '/includes/header.php';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('INSERT INTO leads (name,company,phone,whatsapp,email,city,state,service_interest,lead_source,created_at,updated_at) VALUES (:name,:company,:phone,:whatsapp,:email,:city,:state,:service,:source,NOW(),NOW())');
    $stmt->execute([
        'name' => post('name'), 'company' => post('company'), 'phone' => post('phone'), 'whatsapp' => post('whatsapp'), 'email' => post('email'), 'city' => post('city'), 'state' => post('state'), 'service' => post('service_interest'), 'source' => post('lead_source')
    ]);
    $leadId = (int)$pdo->lastInsertId();
    $o = $pdo->prepare('INSERT INTO opportunities (lead_id,lead_name,service_interest,lead_source,stage_slug,status,estimated_value,priority,temperature,next_action,contact_type,next_followup,last_contact_at,notes,created_at,updated_at) VALUES (:lead_id,:lead_name,:service,:source,:stage,:status,:estimated,:priority,:temp,:next_action,:contact_type,:next_followup,:last_contact,:notes,NOW(),NOW())');
    $o->execute([
        'lead_id'=>$leadId,'lead_name'=>post('name'),'service'=>post('service_interest'),'source'=>post('lead_source'),'stage'=>'contato','status'=>'ativo','estimated'=>(float)post('estimated_value',0),'priority'=>post('priority','media'),'temp'=>post('temperature','morno'),'next_action'=>post('next_action'),'contact_type'=>post('contact_type'),'next_followup'=>post('next_followup'),'last_contact'=>post('last_contact_at') ?: date('Y-m-d'),'notes'=>post('notes')
    ]);
    flash('success', 'Lead criado com oportunidade inicial.');
    redirect('leads.php');
}

$q = trim((string)get('q',''));
$where = '1=1'; $params=[];
if ($q !== '') { $where .= ' AND (name LIKE :q OR company LIKE :q OR phone LIKE :q OR whatsapp LIKE :q OR email LIKE :q)'; $params['q']="%$q%"; }
if ($s=get('service')) { $where .= ' AND service_interest=:service'; $params['service']=$s; }
if ($o=get('origin')) { $where .= ' AND lead_source=:origin'; $params['origin']=$o; }
$sql = "SELECT * FROM leads WHERE $where ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql); $stmt->execute($params); $leads=$stmt->fetchAll();
?>
<?php require __DIR__ . '/includes/sidebar.php'; ?><main class="content"><?php require __DIR__ . '/includes/topbar.php'; ?><div class="container-fluid px-4 pb-4">
<?php if($m=flash('success')):?><div class="alert alert-success"><?=e($m)?></div><?php endif; ?>
<div class="card p-3 mb-3"><form method="get" class="row g-2"><div class="col-md-4"><input class="form-control" name="q" value="<?=e($q)?>" placeholder="Buscar"></div><div class="col-md-3"><input class="form-control" name="service" value="<?=e((string)get('service',''))?>" placeholder="Serviço"></div><div class="col-md-3"><input class="form-control" name="origin" value="<?=e((string)get('origin',''))?>" placeholder="Origem"></div><div class="col-md-2"><button class="btn btn-outline-primary w-100">Filtrar</button></div></form></div>
<div class="card p-3 mb-3"><h2 class="h6">Novo lead</h2><form method="post" class="row g-2">
<div class="col-md-3"><input required name="name" class="form-control" placeholder="Nome"></div><div class="col-md-3"><input name="company" class="form-control" placeholder="Empresa"></div><div class="col-md-2"><input name="phone" class="form-control" placeholder="Telefone"></div><div class="col-md-2"><input name="whatsapp" class="form-control" placeholder="WhatsApp"></div><div class="col-md-2"><input type="email" name="email" class="form-control" placeholder="E-mail"></div>
<div class="col-md-2"><input name="city" class="form-control" placeholder="Cidade"></div><div class="col-md-1"><input name="state" class="form-control" placeholder="UF"></div><div class="col-md-3"><input name="service_interest" class="form-control" placeholder="Serviço"></div><div class="col-md-2"><input name="lead_source" class="form-control" placeholder="Origem"></div>
<div class="col-md-2"><select name="priority" class="form-select"><option>baixa</option><option selected>media</option><option>alta</option></select></div><div class="col-md-2"><select name="temperature" class="form-select"><option>frio</option><option selected>morno</option><option>quente</option></select></div><div class="col-md-2"><input type="number" step="0.01" name="estimated_value" class="form-control" placeholder="Valor estimado"></div>
<div class="col-md-3"><input name="next_action" required class="form-control" placeholder="Próxima ação"></div><div class="col-md-2"><input name="contact_type" class="form-control" placeholder="Tipo contato"></div><div class="col-md-2"><input type="date" required name="next_followup" class="form-control"></div><div class="col-md-2"><input type="date" name="last_contact_at" class="form-control"></div><div class="col-md-3"><input name="notes" class="form-control" placeholder="Observações"></div>
<div class="col-md-2"><button class="btn btn-primary w-100">Salvar</button></div></form></div>
<div class="card p-0"><table class="table mb-0"><thead><tr><th>Nome</th><th>Empresa</th><th>Contato</th><th>Serviço</th><th>Origem</th><th>Criado em</th></tr></thead><tbody><?php foreach($leads as $l): ?><tr><td><?=e($l['name'])?></td><td><?=e($l['company'])?></td><td><?=e($l['phone'])?> <?=e($l['email'])?></td><td><?=e($l['service_interest'])?></td><td><?=e($l['lead_source'])?></td><td><?=e($l['created_at'])?></td></tr><?php endforeach; ?></tbody></table></div>
</div></main><?php require __DIR__ . '/includes/footer.php'; ?>
