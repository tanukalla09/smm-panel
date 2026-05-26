<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/notifications.php';

if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'mark_all') {
    markAllNotificationsRead($userId);
    $redirect = $_POST['redirect'] ?? (SITE_URL . (isAdmin() ? '/admin/notifications.php' : '/user/notifications.php'));
    header('Location: ' . $redirect);
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM notifications WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    $notification = $stmt->fetch();

    if ($notification) {
        markNotificationRead($id, $userId);
        $dest = $notification['link'] ?: (isAdmin() ? SITE_URL . '/admin/notifications.php' : SITE_URL . '/user/notifications.php');
        header('Location: ' . $dest);
        exit;
    }
}

header('Location: ' . SITE_URL . '/user/dashboard.php');
exit;
