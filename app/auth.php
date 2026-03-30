<?php

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function isLoggedIn(): bool
{
    return user() !== null;
}

function requireAuth(): void
{
    if (!isLoggedIn()) {
        flash('error', 'Faça login para continuar.');
        redirect('login.php');
    }
}

function attemptLogin(string $email, string $password): bool
{
    $user = findUserByEmail($email);
    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    unset($user['password_hash']);
    $_SESSION['user'] = $user;
    return true;
}

function logoutUser(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
