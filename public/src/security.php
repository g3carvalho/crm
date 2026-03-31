<?php

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Token CSRF inválido.');
    }
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function slugify(string $text): string
{
    $text = mb_strtolower(trim($text));
    $text = preg_replace('/[^\p{L}\p{N}]+/u', '-', $text);
    $text = trim($text, '-');
    return $text ?: 'item-' . bin2hex(random_bytes(3));
}

function require_admin(): void
{
    if (empty($_SESSION['admin_user'])) {
        header('Location: /admin/login');
        exit;
    }
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function is_locked_out(PDO $pdo, array $config, string $email): bool
{
    $window = time() - ($config['security']['lockout_minutes'] * 60);
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM login_attempts WHERE email = ? AND ip_address = ? AND attempted_at >= ?');
    $stmt->execute([$email, client_ip(), $window]);
    return (int)$stmt->fetchColumn() >= $config['security']['max_login_attempts'];
}

function register_failed_login(PDO $pdo, string $email): void
{
    $stmt = $pdo->prepare('INSERT INTO login_attempts (email, ip_address, attempted_at) VALUES (?, ?, ?)');
    $stmt->execute([$email, client_ip(), time()]);
}

function clear_login_attempts(PDO $pdo, string $email): void
{
    $stmt = $pdo->prepare('DELETE FROM login_attempts WHERE email = ? AND ip_address = ?');
    $stmt->execute([$email, client_ip()]);
}

function validate_upload(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }
    if (($file['size'] ?? 0) > 3 * 1024 * 1024) {
        throw new RuntimeException('Imagem excede 3MB.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Formato inválido. Use JPG, PNG ou WEBP.');
    }

    $name = 'img_' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    return $name;
}
