<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$user = getCurrentUser();
$db = getDB();

$stmt = $db->prepare("
    SELECT o.*, s.name AS service_name
    FROM orders o
    JOIN services s ON o.service_id = s.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();

$pageTitle = 'My Orders';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid px-4 py-4">
    <div class="page-header d-flex flex-wrap justify-content-between align-items-end gap-3">
        <div>
            <h1>My Orders</h1>
            <p>Track all your orders and their delivery status</p>
        </div>
        <a href="<?= SITE_URL ?>/user/services.php" class="btn btn-accent"><i class="bi bi-plus-lg me-1"></i>New Order</a>
    </div>

    <?php if ($msg = flash('success')): ?>
    <div class="alert alert-success"><?= sanitize($msg) ?></div>
    <?php endif; ?>

    <div class="card card-smm">
        <div class="card-body p-0">
            <?php if (empty($orders)): ?>
            <div class="empty-state">
                <i class="bi bi-bag"></i>
                <p class="mb-2">No orders found.</p>
                <a href="<?= SITE_URL ?>/user/services.php" class="btn btn-accent btn-sm">Place Your First Order</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-smm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Service</th>
                            <th>Link</th>
                            <th>Quantity</th>
                            <th>Charge</th>
                            <th>Start</th>
                            <th>Remains</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><strong>#<?= $order['id'] ?></strong></td>
                            <td><?= sanitize($order['service_name']) ?></td>
                            <td class="text-truncate" style="max-width:200px">
                                <a href="<?= sanitize($order['link']) ?>" target="_blank" class="text-decoration-none"><?= sanitize($order['link']) ?></a>
                            </td>
                            <td><?= number_format($order['quantity']) ?></td>
                            <td><strong><?= formatMoney((float)$order['charge']) ?></strong></td>
                            <td><?= number_format($order['start_count']) ?></td>
                            <td><?= number_format($order['remains']) ?></td>
                            <td><span class="badge badge-status badge-status-<?= $order['status'] ?>"><?= ucfirst(str_replace('_', ' ', $order['status'])) ?></span></td>
                            <td class="text-muted"><?= date('M j, Y H:i', strtotime($order['created_at'])) ?></td>
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
