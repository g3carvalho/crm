<?php
$config = require __DIR__ . '/../config.php';

date_default_timezone_set('America/Sao_Paulo');

session_name($config['session_name']);
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
]);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/blog.php';
require_once __DIR__ . '/view.php';

$pdo = db_connect($config['db_path']);
db_migrate($pdo);
db_seed($pdo);
