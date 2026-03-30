<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';

const ACTIVE_STAGES = ['contato', 'qualificado', 'estudo', 'reuniao', 'contrato'];
const CLOSED_STAGES = ['venda', 'perdido', 'arquivado'];

function findUserByEmail(string $email): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    return $stmt->fetch() ?: null;
}

function dashboardMetrics(): array
{
    $pdo = db();

    $metrics = [];
    $metrics['total_leads'] = (int) $pdo->query('SELECT COUNT(*) FROM leads')->fetchColumn();
    $metrics['new_month'] = (int) $pdo->query('SELECT COUNT(*) FROM leads WHERE MONTH(created_at)=MONTH(CURRENT_DATE()) AND YEAR(created_at)=YEAR(CURRENT_DATE())')->fetchColumn();
    $metrics['open_opps'] = (int) $pdo->query("SELECT COUNT(*) FROM opportunities WHERE stage_slug NOT IN ('venda','perdido','arquivado')")->fetchColumn();
    $metrics['sales_month'] = (int) $pdo->query("SELECT COUNT(*) FROM opportunities WHERE stage_slug='venda' AND MONTH(sale_date)=MONTH(CURRENT_DATE()) AND YEAR(sale_date)=YEAR(CURRENT_DATE())")->fetchColumn();
    $metrics['lost_month'] = (int) $pdo->query("SELECT COUNT(*) FROM opportunities WHERE stage_slug='perdido' AND MONTH(updated_at)=MONTH(CURRENT_DATE()) AND YEAR(updated_at)=YEAR(CURRENT_DATE())")->fetchColumn();
    $metrics['archived_month'] = (int) $pdo->query("SELECT COUNT(*) FROM opportunities WHERE stage_slug='arquivado' AND MONTH(updated_at)=MONTH(CURRENT_DATE()) AND YEAR(updated_at)=YEAR(CURRENT_DATE())")->fetchColumn();
    $metrics['total_negotiation'] = (float) $pdo->query("SELECT COALESCE(SUM(estimated_value),0) FROM opportunities WHERE stage_slug NOT IN ('venda','perdido','arquivado')")->fetchColumn();
    $metrics['total_closed'] = (float) $pdo->query("SELECT COALESCE(SUM(closed_value),0) FROM opportunities WHERE stage_slug='venda'")->fetchColumn();

    $validOpps = (int) $pdo->query("SELECT COUNT(*) FROM opportunities WHERE stage_slug <> 'arquivado'")->fetchColumn();
    $sales = (int) $pdo->query("SELECT COUNT(*) FROM opportunities WHERE stage_slug='venda'")->fetchColumn();
    $metrics['conversion_rate'] = $validOpps > 0 ? ($sales / $validOpps) * 100 : 0;
    $metrics['avg_ticket'] = $sales > 0 ? $metrics['total_closed'] / $sales : 0;

    $metrics['today_followups'] = (int) $pdo->query("SELECT COUNT(*) FROM opportunities WHERE next_followup = CURRENT_DATE() AND stage_slug NOT IN ('venda','perdido','arquivado')")->fetchColumn();
    $metrics['pending_tasks'] = (int) $pdo->query("SELECT COUNT(*) FROM tasks WHERE status IN ('pendente','em_andamento')")->fetchColumn();
    $metrics['late_tasks'] = (int) $pdo->query("SELECT COUNT(*) FROM tasks WHERE due_date < CURRENT_DATE() AND status <> 'concluida'")->fetchColumn();
    $metrics['stalled'] = (int) $pdo->query("SELECT COUNT(*) FROM opportunities WHERE updated_at < DATE_SUB(NOW(), INTERVAL 7 DAY) AND stage_slug NOT IN ('venda','perdido','arquivado')")->fetchColumn();
    $metrics['overdue_followups'] = (int) $pdo->query("SELECT COUNT(*) FROM opportunities WHERE next_followup < CURRENT_DATE() AND stage_slug NOT IN ('venda','perdido','arquivado')")->fetchColumn();

    return $metrics;
}

function listStages(): array
{
    return db()->query('SELECT * FROM pipeline_stages ORDER BY sort_order')->fetchAll();
}

function logActivity(int $userId, string $entityType, int $entityId, string $action, ?string $details = null): void
{
    $stmt = db()->prepare('INSERT INTO activity_logs (user_id, entity_type, entity_id, action, details, created_at, updated_at) VALUES (:user_id,:entity_type,:entity_id,:action,:details,NOW(),NOW())');
    $stmt->execute([
        'user_id' => $userId,
        'entity_type' => $entityType,
        'entity_id' => $entityId,
        'action' => $action,
        'details' => $details,
    ]);
}
