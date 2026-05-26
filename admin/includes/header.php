<?php
$adminAuthFile = __DIR__ . DIRECTORY_SEPARATOR . 'auth.php';
$rootAuthFile  = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'auth.php';

if (is_file($adminAuthFile)) {
    require_once $adminAuthFile;
} elseif (is_file($rootAuthFile)) {
    require_once $rootAuthFile;
} else {
    http_response_code(500);
    exit('Authentication configuration file is missing.');
}

if (!isAdmin()) {
    requireAdmin();
}

require_once __DIR__ . '/../../includes/notifications.php';

$pageTitle = $pageTitle ?? 'Admin';
$adminUser = getAdminUser();
$current = basename($_SERVER['PHP_SELF']);

$pendingOrdersBadge = 0;
try {
    $db = getDB();
    $pendingOrdersBadge = (int) $db->query("
        SELECT COUNT(*) FROM orders
        WHERE status IN ('pending', 'processing', 'in_progress')
    ")->fetchColumn();
} catch (Throwable $e) {
    $pendingOrdersBadge = 0;
}

$links = [
    'index.php'          => ['Dashboard', 'bi-speedometer2'],
    'users.php'          => ['Manage Users', 'bi-people'],
    'services.php'       => ['Manage Services', 'bi-grid'],
    'orders.php'         => ['Manage Orders', 'bi-bag'],
    'payments.php'       => ['Manage Payments', 'bi-credit-card'],
    'providers.php'      => ['Manage Providers', 'bi-plug'],
    'tickets.php'        => ['Support Tickets', 'bi-headset'],
    'notifications.php'  => ['Notifications', 'bi-bell'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($pageTitle) ?> — Admin — <?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/assets/css/style.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/assets/css/animations.css" rel="stylesheet">
</head>
<body class="anim-body">
<div class="scroll-progress" id="scrollProgress" aria-hidden="true"></div>
<div class="admin-layout">
    <aside class="admin-sidebar admin-sidebar-light">
        <a class="admin-sidebar-brand logo-gradient" href="<?= SITE_URL ?>/admin/index.php">
            <span class="logo-icon">⚡</span> Admin
        </a>
        <nav class="admin-nav py-2">
            <?php foreach ($links as $file => [$label, $icon]):
                $active = ($current === $file || ($file === 'tickets.php' && $current === 'ticket_view.php')) ? 'active' : '';
            ?>
            <a class="admin-nav-link <?= $active ?>" href="<?= SITE_URL ?>/admin/<?= $file ?>">
                <i class="bi <?= $icon ?>"></i><span><?= $label ?></span>
                <?php if ($file === 'orders.php' && $pendingOrdersBadge > 0): ?>
                <span class="admin-nav-badge" title="Orders needing attention"><?= $pendingOrdersBadge > 99 ? '99+' : $pendingOrdersBadge ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </nav>
        <div class="admin-sidebar-footer">
            <a class="admin-nav-link" href="<?= SITE_URL ?>/user/dashboard.php"><i class="bi bi-person"></i><span>User Panel</span></a>
            <a class="admin-nav-link" href="<?= SITE_URL ?>/logout.php"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
        </div>
    </aside>
    <div class="admin-main">
        <div class="admin-topbar">
            <div>
                <h2 class="admin-page-title mb-0"><?= sanitize($pageTitle) ?></h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb admin-breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/admin/index.php">Admin</a></li>
                        <li class="breadcrumb-item active"><?= sanitize($pageTitle) ?></li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex align-items-center gap-3">
                <?php $currentUser = $adminUser; require dirname(__DIR__, 2) . '/includes/notification_dropdown.php'; ?>
                <span class="profile-avatar"><?= strtoupper(substr($adminUser['username'] ?? 'A', 0, 1)) ?></span>
            </div>
        </div>
        <div class="admin-content">
