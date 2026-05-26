<?php
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$db = getDB();

// Top-level stats
$stats = [
    'users'   => (int) $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn(),
    'orders'  => (int) $db->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'revenue' => (float) $db->query("SELECT COALESCE(SUM(charge), 0) FROM orders WHERE status NOT IN ('cancelled', 'failed')")->fetchColumn(),
    'pending' => (int) $db->query("SELECT COUNT(*) FROM orders WHERE status IN ('pending', 'processing', 'in_progress')")->fetchColumn(),
];

// Daily analytics — last 7 days (including today)
$dailyRows = $db->query("
    SELECT DATE(created_at) AS day,
           COUNT(*) AS order_count,
           COALESCE(SUM(charge), 0) AS revenue
    FROM orders
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(created_at)
")->fetchAll(PDO::FETCH_ASSOC);

$dailyMap = [];
foreach ($dailyRows as $row) {
    $dailyMap[$row['day']] = $row;
}

$chartLabels    = [];
$ordersPerDay   = [];
$revenuePerDay  = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-{$i} days"));
    $chartLabels[]   = date('M j', strtotime($date));
    $ordersPerDay[]  = (int) ($dailyMap[$date]['order_count'] ?? 0);
    $revenuePerDay[] = (float) ($dailyMap[$date]['revenue'] ?? 0);
}

$chartPayload = json_encode([
    'labels'  => $chartLabels,
    'orders'  => $ordersPerDay,
    'revenue' => $revenuePerDay,
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

// Recent orders
$recentOrders = $db->query("
    SELECT o.id, o.charge, o.status, o.created_at,
           u.username, s.name AS service_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN services s ON o.service_id = s.id
    ORDER BY o.created_at DESC
    LIMIT 10
")->fetchAll();

$pageTitle = 'Analytics Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-end gap-3">
    <div>
        <h1>Analytics Dashboard</h1>
        <p>Real-time overview of your SMM panel performance</p>
    </div>
    <span class="badge rounded-pill px-3 py-2" style="background:rgba(79,70,229,0.1);color:var(--primary);font-weight:600">
        <i class="bi bi-calendar3 me-1"></i>Last 7 days
    </span>
</div>

<!-- Stat Cards -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-dash">
            <div class="stat-icon stat-icon-indigo"><i class="bi bi-people-fill"></i></div>
            <div>
                <div class="stat-label">Total Users</div>
                <div class="stat-value"><?= number_format($stats['users']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-dash">
            <div class="stat-icon stat-icon-purple"><i class="bi bi-bag-check-fill"></i></div>
            <div>
                <div class="stat-label">Total Orders</div>
                <div class="stat-value"><?= number_format($stats['orders']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-dash">
            <div class="stat-icon stat-icon-cyan"><i class="bi bi-currency-dollar"></i></div>
            <div>
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value"><?= formatMoney($stats['revenue']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-dash">
            <div class="stat-icon stat-icon-amber"><i class="bi bi-hourglass-split"></i></div>
            <div>
                <div class="stat-label">Pending Orders</div>
                <div class="stat-value" style="color:var(--warning)"><?= number_format($stats['pending']) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="chart-card">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h6 class="fw-bold mb-1">Orders Per Day</h6>
                    <p class="text-muted small mb-0">Daily order volume — last 7 days</p>
                </div>
                <span class="chart-legend-dot" style="background:#4F46E5"></span>
            </div>
            <div class="chart-wrap">
                <canvas id="ordersChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="chart-card">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h6 class="fw-bold mb-1">Daily Revenue</h6>
                    <p class="text-muted small mb-0">Total charges collected — last 7 days</p>
                </div>
                <span class="chart-legend-dot" style="background:#06B6D4"></span>
            </div>
            <div class="chart-wrap">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="card card-smm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-2 text-primary"></i>Recent Orders</span>
        <a href="<?= SITE_URL ?>/admin/orders.php" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($recentOrders)): ?>
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <p class="mb-0">No orders yet.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-smm table-hover mb-0">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>User</th>
                        <th>Service</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $o): ?>
                    <tr>
                        <td><strong>#<?= $o['id'] ?></strong></td>
                        <td>
                            <span class="d-flex align-items-center gap-2">
                                <span class="user-avatar"><?= strtoupper(substr($o['username'], 0, 1)) ?></span>
                                <?= sanitize($o['username']) ?>
                            </span>
                        </td>
                        <td><?= sanitize($o['service_name']) ?></td>
                        <td><strong><?= formatMoney((float)$o['charge']) ?></strong></td>
                        <td><span class="badge badge-status badge-status-<?= $o['status'] ?>"><?= ucfirst(str_replace('_', ' ', $o['status'])) ?></span></td>
                        <td class="text-muted"><?= date('M j, Y H:i', strtotime($o['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script id="adminChartData" type="application/json"><?= $chartPayload ?></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
