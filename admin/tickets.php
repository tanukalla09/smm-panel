<?php
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$db = getDB();
$filter = $_GET['status'] ?? '';

$sql = "
    SELECT t.*, u.username,
           (SELECT COUNT(*) FROM ticket_replies tr WHERE tr.ticket_id = t.id) AS reply_count
    FROM tickets t
    JOIN users u ON t.user_id = u.id
";
$params = [];
if ($filter !== '' && in_array($filter, ['open', 'closed'])) {
    $sql .= ' WHERE t.status = ?';
    $params[] = $filter;
}
$sql .= ' ORDER BY t.created_at DESC';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll();

$openCount = (int) $db->query("SELECT COUNT(*) FROM tickets WHERE status = 'open'")->fetchColumn();

$pageTitle = 'Support Tickets';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-end gap-3">
    <div>
        <h1>Support Tickets</h1>
        <p>Manage customer support requests</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <?php if ($openCount > 0): ?>
        <span class="badge rounded-pill px-3 py-2" style="background:rgba(245,158,11,0.15);color:var(--warning);font-weight:600">
            <?= $openCount ?> open
        </span>
        <?php endif; ?>
        <form method="GET">
            <select name="status" class="form-select" style="width:auto" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="open" <?= $filter === 'open' ? 'selected' : '' ?>>Open</option>
                <option value="closed" <?= $filter === 'closed' ? 'selected' : '' ?>>Closed</option>
            </select>
        </form>
    </div>
</div>

<?php if ($msg = flash('success')): ?>
<div class="alert alert-success"><?= sanitize($msg) ?></div>
<?php endif; ?>

<div class="card card-smm">
    <div class="card-body p-0">
        <?php if (empty($tickets)): ?>
        <div class="empty-state">
            <i class="bi bi-headset"></i>
            <p class="mb-0">No tickets found.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-smm table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Replies</th>
                        <th>Created</th>
                        <th>Updated</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $t): ?>
                    <tr>
                        <td><strong>#<?= $t['id'] ?></strong></td>
                        <td>
                            <span class="d-flex align-items-center gap-2">
                                <span class="user-avatar"><?= strtoupper(substr($t['username'], 0, 1)) ?></span>
                                <?= sanitize($t['username']) ?>
                            </span>
                        </td>
                        <td class="text-truncate" style="max-width:220px"><?= sanitize($t['subject']) ?></td>
                        <td>
                            <span class="badge badge-status badge-status-<?= $t['status'] === 'open' ? 'processing' : 'inactive' ?>">
                                <?= ucfirst($t['status']) ?>
                            </span>
                        </td>
                        <td><?= (int) $t['reply_count'] ?></td>
                        <td class="text-muted"><?= date('M j, Y H:i', strtotime($t['created_at'])) ?></td>
                        <td class="text-muted"><?= date('M j, Y H:i', strtotime($t['updated_at'])) ?></td>
                        <td>
                            <a href="<?= SITE_URL ?>/admin/ticket_view.php?id=<?= $t['id'] ?>" class="btn btn-action btn-outline-primary btn-sm">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
