<?php
// includes/admin_auth.php
// Helpers for admin authentication and session handling.

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function admin_is_logged_in(): bool
{
    return isset($_SESSION['admin_id']);
}

function require_admin_login(): void
{
    if (!admin_is_logged_in()) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}

function admin_login(int $admin_id, string $admin_username): void
{
    $_SESSION['admin_id'] = $admin_id;
    $_SESSION['admin_username'] = $admin_username;
}

function admin_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

