<?php
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$db = getDB();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int) $_POST['order_id'];
    $status = $_POST['status'] ?? '';
    $valid = ['pending', 'processing', 'in_progress', 'completed', 'partial', 'cancelled', 'failed'];

    if (in_array($status, $valid)) {
        $stmt = $db->prepare("
            SELECT o.*, u.email, u.username, s.name AS service_name
            FROM orders o
            JOIN users u ON o.user_id = u.id
            JOIN services s ON o.service_id = s.id
            WHERE o.id = ?
        ");
        $stmt->execute([$orderId]);
        $orderRow = $stmt->fetch();
        $oldStatus = $orderRow['status'] ?? '';

        $db->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$status, $orderId]);
        $message = 'Order status updated.';

        if ($orderRow && $oldStatus !== $status) {
            if ($status === 'failed') {
                require_once __DIR__ . '/../api/provider.php';
                refundFailedOrder($orderId);
            } elseif ($status !== 'failed' && in_array($status, ['processing', 'completed'], true)) {
                $db->prepare('UPDATE orders SET error_message = NULL WHERE id = ?')->execute([$orderId]);
            }

            try {
                require_once __DIR__ . '/../includes/mailer.php';
                $orderRow['status'] = $status;
                if ($status === 'completed') {
                    mailOrderCompleted($orderRow['email'], $orderRow['username'], $orderRow, $orderRow['service_name']);
                } elseif ($status === 'failed') {
                    mailOrderFailed($orderRow['email'], $orderRow['username'], $orderRow, $orderRow['service_name'], orderWasRefunded($orderId));
                }
            } catch (Throwable $e) {
                error_log('Order status email failed: ' . $e->getMessage());
            }
        }
    }
}

$filter = $_GET['status'] ?? '';
$range = $_GET['range'] ?? '';
$search = trim($_GET['q'] ?? '');

$validStatuses = ['pending', 'processing', 'completed', 'failed', 'cancelled'];
if ($filter !== '' && !in_array($filter, $validStatuses, true)) {
    $filter = '';
}

$sql = "
    SELECT o.*, u.username, s.name AS service_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN services s ON o.service_id = s.id
";
$params = [];
$where = [];

if ($filter !== '') {
    $where[] = 'o.status = ?';
    $params[] = $filter;
}

if ($range === 'today') {
    $where[] = 'DATE(o.created_at) = CURDATE()';
} elseif ($range === 'week') {
    $where[] = 'o.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
} elseif ($range === 'month') {
    $where[] = 'o.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
}

if ($search !== '') {
    $where[] = '(u.username LIKE ? OR s.name LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
}

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY o.created_at DESC LIMIT 200';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

require_once __DIR__ . '/../includes/settings.php';
$lastSynced = getSetting('orders_last_synced');
$lastSyncedFormatted = formatLastSynced($lastSynced);

$pageTitle = 'Manage Orders';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header mb-3">
    <div>
        <h1>Manage Orders</h1>
        <p>Monitor and update order statuses</p>
    </div>
</div>

<div class="orders-toolbar-card mb-3">
    <div class="orders-action-bar">
        <button type="button" id="syncOrdersBtn" class="btn btn-sync-gradient">
            <span class="sync-btn-text"><span class="sync-icon">🔄</span> Sync Order Status</span>
            <span class="sync-btn-spinner d-none">
                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                Syncing…
            </span>
        </button>

        <form method="GET" class="orders-filters-form" id="ordersFilterForm">
            <div class="orders-filter-select-wrap">
                <select name="status" class="orders-filter-select" aria-label="Filter by status">
                    <option value="" <?= $filter === '' ? 'selected' : '' ?>>All Statuses</option>
                    <option value="pending" <?= $filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="processing" <?= $filter === 'processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="completed" <?= $filter === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="failed" <?= $filter === 'failed' ? 'selected' : '' ?>>Failed</option>
                    <option value="cancelled" <?= $filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
                <i class="bi bi-chevron-down orders-filter-chevron" aria-hidden="true"></i>
            </div>

            <div class="orders-filter-select-wrap">
                <select name="range" class="orders-filter-select" aria-label="Filter by date">
                    <option value="" <?= $range === '' ? 'selected' : '' ?>>All Time</option>
                    <option value="today" <?= $range === 'today' ? 'selected' : '' ?>>Today</option>
                    <option value="week" <?= $range === 'week' ? 'selected' : '' ?>>This Week</option>
                    <option value="month" <?= $range === 'month' ? 'selected' : '' ?>>This Month</option>
                </select>
                <i class="bi bi-chevron-down orders-filter-chevron" aria-hidden="true"></i>
            </div>

            <div class="orders-search-wrap">
                <i class="bi bi-search orders-search-icon" aria-hidden="true"></i>
                <input type="search" name="q" class="orders-search-input" value="<?= sanitize($search) ?>"
                    placeholder="Search user or service…" aria-label="Search by username or service">
            </div>

            <button type="submit" class="btn orders-filter-apply">Apply</button>
        </form>
    </div>

    <div class="orders-toolbar-meta">
        <p class="sync-last-text mb-0">
            Last synced: <span id="lastSyncedTime"><?= sanitize($lastSyncedFormatted) ?></span>
        </p>
        <div id="syncFeedback" class="sync-feedback d-none" role="alert"></div>
    </div>
</div>

<?php if ($message): ?><div class="alert alert-success"><?= sanitize($message) ?></div><?php endif; ?>

<div class="card card-smm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-smm table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Service</th>
                        <th>Link</th>
                        <th>Qty</th>
                        <th>Charge</th>
                        <th>Profit</th>
                        <th>Provider ID</th>
                        <th class="orders-col-status">Status</th>
                        <th class="orders-col-internal">
                            Internal
                            <span class="orders-internal-tip" tabindex="0" title="Only visible to admins" data-bs-toggle="tooltip" data-bs-placement="top">ℹ️</span>
                        </th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                    <tr>
                        <td>#<?= $o['id'] ?></td>
                        <td><?= sanitize($o['username']) ?></td>
                        <td><?= sanitize($o['service_name']) ?></td>
                        <td class="text-truncate" style="max-width:120px"><?= sanitize($o['link']) ?></td>
                        <td><?= number_format($o['quantity']) ?></td>
                        <td><?= formatMoney((float)$o['charge']) ?></td>
                        <td class="text-success"><?= $o['profit'] !== null ? formatMoney((float)$o['profit']) : '—' ?></td>
                        <td><?= sanitize($o['provider_order_id'] ?? '—') ?></td>
                        <td class="orders-col-status">
                            <span class="badge badge-status badge-status-<?= $o['status'] ?>"><?= ucfirst(str_replace('_', ' ', $o['status'])) ?></span>
                        </td>
                        <td class="orders-col-internal">
                            <?php if (!empty($o['error_message'])): ?>
                            <div class="internal-error-cell">
                                <span class="badge badge-api-error">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>API Error
                                </span>
                                <div class="admin-error-note"><?= sanitize($o['error_message']) ?></div>
                            </div>
                            <?php else: ?>
                            <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('M j, H:i', strtotime($o['created_at'])) ?></td>
                        <td>
                            <form method="POST" class="d-flex gap-1">
                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                <select name="status" class="form-select form-select-sm" style="width:auto">
                                    <?php foreach (['pending','processing','in_progress','completed','partial','cancelled','failed'] as $st): ?>
                                    <option value="<?= $st ?>" <?= $o['status'] === $st ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $st)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-action btn-outline-primary btn-sm">Save</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
(function () {
    const btn = document.getElementById('syncOrdersBtn');
    if (!btn) return;

    const textEl = btn.querySelector('.sync-btn-text');
    const spinnerEl = btn.querySelector('.sync-btn-spinner');
    const feedbackEl = document.getElementById('syncFeedback');
    const lastSyncedEl = document.getElementById('lastSyncedTime');
    const syncUrl = <?= json_encode(SITE_URL . '/cron/process_orders.php') ?>;

    function showFeedback(type, html) {
        feedbackEl.className = 'sync-feedback alert alert-' + type;
        feedbackEl.innerHTML = html;
        feedbackEl.classList.remove('d-none');
    }

    btn.addEventListener('click', async function () {
        btn.disabled = true;
        textEl.classList.add('d-none');
        spinnerEl.classList.remove('d-none');
        feedbackEl.classList.add('d-none');

        try {
            const res = await fetch(syncUrl, {
                method: 'GET',
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });

            let data;
            try {
                data = await res.json();
            } catch (e) {
                throw new Error('Invalid response from server.');
            }

            if (data.success) {
                const detail = data.orders_checked + ' checked · ' + data.orders_updated + ' updated';
                showFeedback('success',
                    '<strong>' + (data.message || 'Orders synced successfully!') + '</strong>' +
                    '<div class="small mt-1 opacity-75">' + detail + '</div>'
                );
                if (data.last_synced_formatted && lastSyncedEl) {
                    lastSyncedEl.textContent = data.last_synced_formatted;
                }
                if (data.orders_updated > 0) {
                    setTimeout(function () { window.location.reload(); }, 1800);
                }
            } else {
                showFeedback('danger', data.message || 'Sync failed. Please try again.');
            }
        } catch (err) {
            showFeedback('danger', 'Could not reach the sync endpoint. Check your connection and try again.');
        } finally {
            btn.disabled = false;
            textEl.classList.remove('d-none');
            spinnerEl.classList.add('d-none');
        }
    });
})();

document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
    new bootstrap.Tooltip(el);
});

(function () {
    var form = document.getElementById('ordersFilterForm');
    if (!form) return;
    form.querySelectorAll('select').forEach(function (sel) {
        sel.addEventListener('change', function () { form.submit(); });
    });
})();
</script>
