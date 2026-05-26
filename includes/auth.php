<?php
/**
 * Authentication helpers
 */

require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool
{
    return isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
}

function requireAdmin(): void
{
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . '/user/dashboard.php');
        exit;
    }
}

function getCurrentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }

    $db = getDB();
    $stmt = $db->prepare('SELECT id, username, email, balance, role, status FROM users WHERE id = ? AND status = ?');
    $stmt->execute([$_SESSION['user_id'], 'active']);
    return $stmt->fetch() ?: null;
}

function loginUser(array $user): void
{
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_role'] = $user['role'];
    session_regenerate_id(true);
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

function redirectIfLoggedIn(): void
{
    if (isLoggedIn()) {
        $dest = isAdmin() ? SITE_URL . '/admin/index.php' : SITE_URL . '/user/dashboard.php';
        header('Location: ' . $dest);
        exit;
    }
}

function formatMoney(float $amount): string
{
    return '$' . number_format($amount, 2);
}

function sanitize(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}
