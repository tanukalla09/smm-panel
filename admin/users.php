<?php
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$db = getDB();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update') {
        $id = (int) $_POST['user_id'];
        $balance = (float) $_POST['balance'];
        $status = in_array($_POST['status'], ['active', 'suspended']) ? $_POST['status'] : 'active';
        $role = in_array($_POST['role'], ['user', 'admin']) ? $_POST['role'] : 'user';

        $db->prepare('UPDATE users SET balance = ?, status = ?, role = ? WHERE id = ?')
           ->execute([$balance, $status, $role, $id]);
        $message = 'User updated successfully.';
    }

    if ($action === 'adjust_balance') {
        $id = (int) $_POST['user_id'];
        $adjust = (float) $_POST['adjust_amount'];
        $db->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')->execute([$adjust, $id]);
        $type = $adjust >= 0 ? 'adjustment' : 'adjustment';
        $db->prepare("INSERT INTO transactions (user_id, type, amount, payment_method, status, description) VALUES (?, ?, ?, 'admin', 'completed', ?)")
           ->execute([$id, $type, abs($adjust), "Admin balance adjustment: " . ($adjust >= 0 ? '+' : '') . $adjust]);
        $message = 'Balance adjusted.';
    }
}

$users = $db->query("SELECT id, username, email, balance, role, status, created_at FROM users ORDER BY id DESC")->fetchAll();

$pageTitle = 'Manage Users';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Manage Users</h1>
    <p>View and manage registered users</p>
</div>

<?php if ($message): ?><div class="alert alert-success"><?= sanitize($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= sanitize($error) ?></div><?php endif; ?>

<div class="card card-smm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-smm table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Balance</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td>#<?= $u['id'] ?></td>
                        <td><?= sanitize($u['username']) ?></td>
                        <td><?= sanitize($u['email']) ?></td>
                        <td><?= formatMoney((float)$u['balance']) ?></td>
                        <td><span class="badge bg-<?= $u['role'] === 'admin' ? 'primary' : 'secondary' ?>"><?= ucfirst($u['role']) ?></span></td>
                        <td><span class="badge badge-status badge-status-<?= $u['status'] ?>"><?= ucfirst($u['status']) ?></span></td>
                        <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <button class="btn btn-action btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editUser<?= $u['id'] ?>">Edit</button>
                        </td>
                    </tr>

                    <div class="modal fade" id="editUser<?= $u['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit User: <?= sanitize($u['username']) ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Balance</label>
                                            <input type="number" name="balance" class="form-control" step="0.01" value="<?= $u['balance'] ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Role</label>
                                            <select name="role" class="form-select">
                                                <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select">
                                                <option value="active" <?= $u['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                                <option value="suspended" <?= $u['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                                            </select>
                                        </div>
                                        <hr>
                                        <div class="mb-3">
                                            <label class="form-label">Quick Balance Adjustment (+/-)</label>
                                        </div>
                                    </div>
                                    <div class="modal-footer flex-wrap">
                                        <button type="submit" class="btn btn-accent">Save Changes</button>
                                    </div>
                                </form>
                                <form method="POST" class="px-3 pb-3">
                                    <input type="hidden" name="action" value="adjust_balance">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <div class="input-group">
                                        <input type="number" name="adjust_amount" class="form-control" step="0.01" placeholder="e.g. 10 or -5">
                                        <button type="submit" class="btn btn-outline-warning">Adjust</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
