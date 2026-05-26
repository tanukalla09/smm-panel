<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$user = getCurrentUser();
$db = getDB();

$stmt = $db->prepare("
    SELECT o.*, s.name AS service_name
    FROM orders o JOIN services s ON o.service_id = s.id
    WHERE o.user_id = ? ORDER BY o.created_at DESC LIMIT 10
");
$stmt->execute([$user['id']]);
$recentOrders = $stmt->fetchAll();

$stmt = $db->prepare('SELECT COUNT(*) FROM orders WHERE user_id = ?');
$stmt->execute([$user['id']]);
$totalOrders = (int) $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status IN ('pending','processing','in_progress')");
$stmt->execute([$user['id']]);
$pendingOrders = (int) $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status = 'completed'");
$stmt->execute([$user['id']]);
$completedOrders = (int) $stmt->fetchColumn();

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid px-4 py-4">
    <div class="welcome-banner mb-4 anim-slide-down">
        <div class="welcome-banner-content">
            <h1>Welcome back, <?= sanitize($user['username']) ?> 👋</h1>
            <p>Here's what's happening with your account today.</p>
        </div>
    </div>

    <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= sanitize($msg) ?></div><?php endif; ?>
    <?php if ($msg = flash('error')): ?><div class="alert alert-danger"><?= sanitize($msg) ?></div><?php endif; ?>

    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card stat-card-accent stat-accent-green anim-stat" style="--stat-delay: 0">
                <div class="stat-icon stat-icon-green"><i class="bi bi-wallet2"></i></div>
                <div><div class="stat-label">Wallet Balance</div><div class="stat-value" data-count="<?= (float)$user['balance'] ?>" data-prefix="$" data-decimals="2" data-duration="1400">$0.00</div></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card stat-card-accent stat-accent-blue anim-stat" style="--stat-delay: 1">
                <div class="stat-icon stat-icon-indigo"><i class="bi bi-bag-check"></i></div>
                <div><div class="stat-label">Total Orders</div><div class="stat-value" data-count="<?= $totalOrders ?>" data-decimals="0" data-duration="1400">0</div></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card stat-card-accent stat-accent-yellow anim-stat" style="--stat-delay: 2">
                <div class="stat-icon stat-icon-amber"><i class="bi bi-hourglass-split"></i></div>
                <div><div class="stat-label">Pending Orders</div><div class="stat-value" data-count="<?= $pendingOrders ?>" data-decimals="0" data-duration="1400">0</div></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card stat-card-accent stat-accent-purple anim-stat" style="--stat-delay: 3">
                <div class="stat-icon stat-icon-purple"><i class="bi bi-check-circle"></i></div>
                <div><div class="stat-label">Completed Orders</div><div class="stat-value" data-count="<?= $completedOrders ?>" data-decimals="0" data-duration="1400">0</div></div>
            </div>
        </div>
    </div>

    <div class="card card-smm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-clock-history me-2 text-primary"></i>Recent Orders</span>
            <a href="<?= SITE_URL ?>/user/orders.php" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($recentOrders)): ?>
            <div class="empty-state"><i class="bi bi-inbox"></i><p class="mb-2">No orders yet.</p><a href="<?= SITE_URL ?>/user/services.php" class="btn btn-accent btn-sm">Browse Services</a></div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-smm table-hover mb-0">
                    <thead><tr><th>ID</th><th>Service</th><th>Charge</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentOrders as $i => $order): ?>
                    <tr class="anim-row" style="--row-delay: <?= $i ?>">
                        <td><strong>#<?= $order['id'] ?></strong></td>
                        <td><?= sanitize($order['service_name']) ?></td>
                        <td><strong><?= formatMoney((float)$order['charge']) ?></strong></td>
                        <td><span class="badge badge-status badge-status-<?= $order['status'] ?>"><?= ucfirst(str_replace('_', ' ', $order['status'])) ?></span></td>
                        <td class="text-muted"><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
