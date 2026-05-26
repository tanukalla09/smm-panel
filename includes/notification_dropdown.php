<?php
/**
 * Notification bell dropdown partial
 * Requires: auth.php, notifications.php, $currentUser
 */
if (!isset($currentUser) || !$currentUser) return;

require_once __DIR__ . '/notifications.php';

$notifUserId = (int) $currentUser['id'];
$unreadCount = getUnreadCount($notifUserId);
$recentNotifs = getRecentNotifications($notifUserId, 6);
$notifPage = isAdmin() ? SITE_URL . '/admin/notifications.php' : SITE_URL . '/user/notifications.php';
?>
<div class="dropdown notif-dropdown">
    <button class="btn-notif" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
        <i class="bi bi-bell"></i>
        <?php if ($unreadCount > 0): ?>
        <span class="notif-badge"><?= $unreadCount > 99 ? '99+' : $unreadCount ?></span>
        <?php endif; ?>
    </button>
    <div class="dropdown-menu dropdown-menu-end notif-menu">
        <div class="notif-menu-header">
            <strong>Notifications</strong>
            <?php if ($unreadCount > 0): ?>
            <form method="POST" action="<?= SITE_URL ?>/notification_read.php" class="d-inline">
                <input type="hidden" name="action" value="mark_all">
                <input type="hidden" name="redirect" value="<?= sanitize($_SERVER['REQUEST_URI'] ?? '') ?>">
                <button type="submit" class="btn-notif-mark">Mark all read</button>
            </form>
            <?php endif; ?>
        </div>
        <div class="notif-menu-body">
            <?php if (empty($recentNotifs)): ?>
            <div class="notif-empty">No notifications yet</div>
            <?php else: ?>
            <?php foreach ($recentNotifs as $n): ?>
            <a href="<?= SITE_URL ?>/notification_read.php?id=<?= $n['id'] ?>" class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>">
                <span class="notif-icon notif-icon-<?= $n['type'] ?>">
                    <i class="bi <?= getNotificationIcon($n['type']) ?>"></i>
                </span>
                <span class="notif-content">
                    <span class="notif-title"><?= sanitize($n['title']) ?></span>
                    <span class="notif-msg"><?= sanitize($n['message']) ?></span>
                    <span class="notif-time"><?= timeAgo($n['created_at']) ?></span>
                </span>
            </a>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <a href="<?= $notifPage ?>" class="notif-menu-footer">View all notifications</a>
    </div>
</div>
