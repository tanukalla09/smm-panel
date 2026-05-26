<?php
/**
 * Notification system helpers
 */

require_once __DIR__ . '/db.php';

function notify(int $userId, string $title, string $message, string $type = 'info', ?string $link = null): void
{
    $allowed = ['info', 'success', 'warning', 'error'];
    if (!in_array($type, $allowed, true)) {
        $type = 'info';
    }

    $db = getDB();
    $stmt = $db->prepare('INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$userId, $title, $message, $type, $link]);
}

function notifyAdmins(string $title, string $message, string $type = 'info', ?string $link = null): void
{
    $db = getDB();
    $admins = $db->query("SELECT id FROM users WHERE role = 'admin' AND status = 'active'")->fetchAll();
    foreach ($admins as $admin) {
        notify((int) $admin['id'], $title, $message, $type, $link);
    }
}

function getUnreadCount(?int $userId = null): int
{
    if ($userId === null && !isLoggedIn()) {
        return 0;
    }
    $userId = $userId ?? (int) $_SESSION['user_id'];
    $db = getDB();
    $stmt = $db->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
    $stmt->execute([$userId]);
    return (int) $stmt->fetchColumn();
}

function getRecentNotifications(int $userId, int $limit = 8): array
{
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?');
    $stmt->bindValue(1, $userId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function markNotificationRead(int $notificationId, int $userId): bool
{
    $db = getDB();
    $stmt = $db->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
    $stmt->execute([$notificationId, $userId]);
    return $stmt->rowCount() > 0;
}

function markAllNotificationsRead(int $userId): void
{
    $db = getDB();
    $db->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0')->execute([$userId]);
}

function getNotificationIcon(string $type): string
{
    return match ($type) {
        'success' => 'bi-check-circle-fill',
        'warning' => 'bi-exclamation-triangle-fill',
        'error'   => 'bi-x-circle-fill',
        default   => 'bi-info-circle-fill',
    };
}

function timeAgo(string $datetime): string
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', strtotime($datetime));
}

function platformEmoji(string $category): string
{
    $map = [
        'Instagram' => '📸', 'YouTube' => '▶️', 'TikTok' => '🎵',
        'Twitter'   => '🐦', 'Facebook' => '👥', 'Telegram' => '✈️',
        'Spotify'   => '🎧', 'LinkedIn' => '💼', 'General' => '🌐',
    ];
    if (isset($map[$category])) {
        return $map[$category];
    }
    foreach ($map as $name => $emoji) {
        if (strcasecmp($name, $category) === 0) {
            return $emoji;
        }
    }
    return '🌐';
}
