<?php
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$db = getDB();

$filter = $_GET['type'] ?? '';
$sql = "
    SELECT t.*, u.username
    FROM transactions t
    JOIN users u ON t.user_id = u.id
";
$params = [];
if ($filter !== '') {
    $sql .= ' WHERE t.type = ?';
    $params[] = $filter;
}
$sql .= ' ORDER BY t.created_at DESC LIMIT 200';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

$totals = [
    'deposits' => (float) $db->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='deposit' AND status='completed'")->fetchColumn(),
    'orders'   => (float) $db->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='order' AND status='completed'")->fetchColumn(),
];

$pageTitle = 'Manage Payments';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-end gap-3">
    <div>
        <h1>Manage Payments</h1>
        <p>View all transactions and deposits</p>
    </div>
    <form method="GET">
        <select name="type" class="form-select" style="width:auto" onchange="this.form.submit()">
            <option value="">All Types</option>
            <?php foreach (['deposit','order','refund','adjustment'] as $t): ?>
            <option value="<?= $t ?>" <?= $filter === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="stat-card">
            <div class="stat-label">Total Deposits</div>
            <div class="stat-value text-success"><?= formatMoney($totals['deposits']) ?></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card">
            <div class="stat-label">Total Order Charges</div>
            <div class="stat-value"><?= formatMoney($totals['orders']) ?></div>
        </div>
    </div>
</div>

<div class="card card-smm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-smm table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Description</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $t): ?>
                    <tr>
                        <td>#<?= $t['id'] ?></td>
                        <td><?= sanitize($t['username']) ?></td>
                        <td><span class="badge bg-secondary"><?= ucfirst($t['type']) ?></span></td>
                        <td><?= formatMoney((float)$t['amount']) ?></td>
                        <td><?= sanitize($t['payment_method'] ?? '—') ?></td>
                        <td><span class="badge badge-status badge-status-<?= $t['status'] === 'completed' ? 'completed' : ($t['status'] === 'pending' ? 'pending' : 'failed') ?>"><?= ucfirst($t['status']) ?></span></td>
                        <td class="text-truncate" style="max-width:200px"><?= sanitize($t['description'] ?? '') ?></td>
                        <td><?= date('M j, Y H:i', strtotime($t['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
