<?php
$pageTitle = 'Pipeline';
require __DIR__ . '/includes/header.php';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) post('id');
    $stage = (string) post('stage_slug');
    $lostReason = post('lost_reason_id');
    $archiveReason = post('archive_reason_id');
    $closedValue = (float) post('closed_value', 0);
    $saleDate = post('sale_date') ?: null;

    if (in_array($stage, ACTIVE_STAGES, true)) {
        if (!post('next_action') || !post('next_followup')) {
            flash('error', 'Oportunidade ativa precisa de próxima ação e follow-up.');
            redirect('pipeline.php');
        }
    }

    if ($stage === 'perdido' && !$lostReason) {
        flash('error', 'Selecione motivo de perda.');
        redirect('pipeline.php');
    }
    if ($stage === 'arquivado' && !$archiveReason) {
        flash('error', 'Selecione motivo de arquivamento.');
        redirect('pipeline.php');
    }

    $stmt = $pdo->prepare('UPDATE opportunities SET stage_slug=:stage,next_action=:next_action,next_followup=:next_followup,lost_reason_id=:lost,archive_reason_id=:arch,closed_value=:closed,sale_date=:sale,updated_at=NOW() WHERE id=:id');
    $stmt->execute([
        'stage' => $stage,
        'next_action' => post('next_action') ?: null,
        'next_followup' => post('next_followup') ?: null,
        'lost' => $lostReason ?: null,
        'arch' => $archiveReason ?: null,
        'closed' => $stage === 'venda' ? $closedValue : null,
        'sale' => $stage === 'venda' ? $saleDate : null,
        'id' => $id,
    ]);
    flash('success', 'Pipeline atualizado.');
    redirect('pipeline.php');
}

$stages = listStages();
$items = $pdo->query('SELECT o.*, l.company FROM opportunities o LEFT JOIN leads l ON l.id=o.lead_id ORDER BY o.priority DESC, o.updated_at DESC')->fetchAll();
$lostReasons = $pdo->query('SELECT * FROM lost_reasons')->fetchAll();
$archiveReasons = $pdo->query('SELECT * FROM archive_reasons')->fetchAll();
$grouped = [];
foreach ($stages as $s) $grouped[$s['slug']] = [];
foreach ($items as $item) $grouped[$item['stage_slug']][] = $item;
?>
<?php require __DIR__ . '/includes/sidebar.php'; ?>
<main class="content"><?php require __DIR__ . '/includes/topbar.php'; ?><div class="container-fluid px-4 pb-4">
<?php if($m=flash('error')):?><div class="alert alert-danger"><?=e($m)?></div><?php endif; ?>
<?php if($m=flash('success')):?><div class="alert alert-success"><?=e($m)?></div><?php endif; ?>
<div class="pipeline-grid">
<?php foreach($stages as $stage): ?><section class="pipeline-col"><h2 class="h6"><?=e($stage['name'])?></h2>
<?php foreach($grouped[$stage['slug']] as $o): ?><div class="card p-2 mb-2"><strong><?=e($o['lead_name'])?></strong><div class="small"><?=e($o['company']??'')?></div><div class="small">Serviço: <?=e($o['service_interest'])?></div><div class="small">Origem: <?=e($o['lead_source'])?></div><div class="small">Estimado: R$ <?=number_format((float)$o['estimated_value'],2,',','.')?></div><div class="small">Prioridade: <?=e($o['priority'])?></div><div class="small">Última interação: <?=e((string)$o['last_contact_at'])?></div><div class="small">Próxima ação: <?=e((string)$o['next_action'])?></div><div class="small">Follow-up: <?=e((string)$o['next_followup'])?></div>
<form method="post" class="mt-2">
<input type="hidden" name="id" value="<?=$o['id']?>">
<select name="stage_slug" class="form-select form-select-sm mb-1"><?php foreach($stages as $s): ?><option value="<?=e($s['slug'])?>" <?=$o['stage_slug']===$s['slug']?'selected':''?>><?=e($s['name'])?></option><?php endforeach; ?></select>
<input class="form-control form-control-sm mb-1" name="next_action" placeholder="Próxima ação" value="<?=e((string)$o['next_action'])?>">
<input class="form-control form-control-sm mb-1" type="date" name="next_followup" value="<?=e((string)$o['next_followup'])?>">
<select class="form-select form-select-sm mb-1" name="lost_reason_id"><option value="">Motivo perda</option><?php foreach($lostReasons as $r): ?><option value="<?=$r['id']?>"><?=e($r['name'])?></option><?php endforeach; ?></select>
<select class="form-select form-select-sm mb-1" name="archive_reason_id"><option value="">Motivo arquiv.</option><?php foreach($archiveReasons as $r): ?><option value="<?=$r['id']?>"><?=e($r['name'])?></option><?php endforeach; ?></select>
<input class="form-control form-control-sm mb-1" type="number" step="0.01" name="closed_value" placeholder="Valor fechado">
<input class="form-control form-control-sm mb-1" type="date" name="sale_date">
<button class="btn btn-primary btn-sm w-100">Atualizar</button></form>
</div><?php endforeach; ?></section><?php endforeach; ?>
</div></div></main><?php require __DIR__ . '/includes/footer.php'; ?>
