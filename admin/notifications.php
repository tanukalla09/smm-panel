<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../includes/notifications.php';
requireAdmin();

$admin = getAdminUser();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'mark_all') {
    markAllNotificationsRead($admin['id']);
    flash('success', 'All notifications marked as read.');
    header('Location: ' . SITE_URL . '/admin/notifications.php');
    exit;
}

$stmt = $db->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50');
$stmt->execute([$admin['id']]);
$notifications = $stmt->fetchAll();

$pageTitle = 'Notifications';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-end gap-3">
    <div>
        <h1>Notifications</h1>
        <p>Admin alerts and system updates</p>
    </div>
    <?php if (getUnreadCount($admin['id']) > 0): ?>
    <form method="POST">
        <input type="hidden" name="action" value="mark_all">
        <button type="submit" class="btn btn-ghost btn-sm">Mark all as read</button>
    </form>
    <?php endif; ?>
</div>

<div class="card card-smm">
    <div class="card-body p-0">
        <?php if (empty($notifications)): ?>
        <div class="empty-state"><i class="bi bi-bell"></i><p class="mb-0">No notifications yet.</p></div>
        <?php else: ?>
        <div class="notif-list-full">
            <?php foreach ($notifications as $n): ?>
            <a href="<?= SITE_URL ?>/notification_read.php?id=<?= $n['id'] ?>" class="notif-item-full <?= $n['is_read'] ? '' : 'unread' ?>">
                <span class="notif-icon notif-icon-<?= $n['type'] ?>"><i class="bi <?= getNotificationIcon($n['type']) ?>"></i></span>
                <span class="flex-grow-1">
                    <span class="notif-title d-block"><?= sanitize($n['title']) ?></span>
                    <span class="notif-msg d-block"><?= sanitize($n['message']) ?></span>
                </span>
                <span class="notif-time text-nowrap"><?= timeAgo($n['created_at']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
