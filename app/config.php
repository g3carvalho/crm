<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const APP_NAME = 'CRM Executivo';
const APP_TIMEZONE = 'America/Sao_Paulo';

date_default_timezone_set(APP_TIMEZONE);

return [
    'base_url' => getenv('CRM_BASE_URL') ?: '/crm/public_html',
    'db' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_NAME') ?: 'crm',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
    'upload_dir' => __DIR__ . '/../storage/uploads',
    'max_upload_size' => 5 * 1024 * 1024,
];
