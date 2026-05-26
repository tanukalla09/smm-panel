<?php
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$db = getDB();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $db->prepare('INSERT INTO providers (name, api_url, api_key, status) VALUES (?, ?, ?, ?)')
           ->execute([trim($_POST['name']), trim($_POST['api_url']), trim($_POST['api_key']), $_POST['status'] ?? 'inactive']);
        $message = 'Provider created.';
    }

    if ($action === 'update') {
        $db->prepare('UPDATE providers SET name=?, api_url=?, api_key=?, status=? WHERE id=?')
           ->execute([trim($_POST['name']), trim($_POST['api_url']), trim($_POST['api_key']), $_POST['status'], (int)$_POST['provider_id']]);
        $message = 'Provider updated.';
    }

    if ($action === 'test') {
        require_once __DIR__ . '/../api/provider.php';
        $stmt = $db->prepare('SELECT * FROM providers WHERE id = ?');
        $stmt->execute([(int)$_POST['provider_id']]);
        $prov = $stmt->fetch();
        if ($prov) {
            try {
                $api = new SMMProvider($prov);
                $result = $api->getBalance();
                if (isset($result['balance'])) {
                    $message = 'Connection OK! Provider balance: $' . $result['balance'];
                } else {
                    $error = 'API error: ' . ($result['error'] ?? json_encode($result));
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

$providers = $db->query('SELECT * FROM providers ORDER BY id')->fetchAll();

$pageTitle = 'Manage Providers';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-end">
    <div>
        <h1>Manage Providers</h1>
        <p>Configure API providers (e.g. fastxsmm.com)</p>
    </div>
    <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#createProvider"><i class="bi bi-plus-lg me-1"></i>Add Provider</button>
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
                        <th>Name</th>
                        <th>API URL</th>
                        <th>API Key</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($providers as $p): ?>
                    <tr>
                        <td>#<?= $p['id'] ?></td>
                        <td><?= sanitize($p['name']) ?></td>
                        <td class="text-truncate" style="max-width:200px"><?= sanitize($p['api_url']) ?></td>
                        <td><code><?= sanitize(substr($p['api_key'], 0, 8)) ?>...</code></td>
                        <td><span class="badge badge-status badge-status-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                        <td class="d-flex gap-1">
                            <button class="btn btn-action btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editProv<?= $p['id'] ?>">Edit</button>
                            <form method="POST">
                                <input type="hidden" name="action" value="test">
                                <input type="hidden" name="provider_id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn btn-action btn-outline-success btn-sm">Test</button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="editProv<?= $p['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Provider</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="provider_id" value="<?= $p['id'] ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Name</label>
                                            <input type="text" name="name" class="form-control" value="<?= sanitize($p['name']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">API URL</label>
                                            <input type="url" name="api_url" class="form-control" value="<?= sanitize($p['api_url']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">API Key</label>
                                            <input type="text" name="api_key" class="form-control" value="<?= sanitize($p['api_key']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select">
                                                <option value="active" <?= $p['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                                <option value="inactive" <?= $p['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-accent">Save</button>
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

<div class="modal fade" id="createProvider" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add Provider</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="FastXSMM" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">API URL</label>
                        <input type="url" name="api_url" class="form-control" value="https://fastxsmm.com/api/v2" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">API Key</label>
                        <input type="text" name="api_key" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="inactive">Inactive</option>
                            <option value="active">Active</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-accent">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
