<?php
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$db = getDB();
$message = '';

$providers = $db->query('SELECT id, name FROM providers ORDER BY name')->fetchAll();
require_once __DIR__ . '/includes/service_form_fields.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $stmt = $db->prepare("
            INSERT INTO services (provider_id, provider_service_id, name, category, description, rate, cost_rate, min_quantity, max_quantity, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['provider_id'] ?: null,
            trim($_POST['provider_service_id']),
            trim($_POST['name']),
            trim($_POST['category']),
            trim($_POST['description']),
            (float) $_POST['rate'],
            (float) $_POST['cost_rate'] ?: null,
            (int) $_POST['min_quantity'],
            (int) $_POST['max_quantity'],
            $_POST['status'] ?? 'active',
        ]);
        $message = 'Service created.';
    }

    if ($action === 'update') {
        $stmt = $db->prepare("
            UPDATE services SET provider_id=?, provider_service_id=?, name=?, category=?, description=?,
            rate=?, cost_rate=?, min_quantity=?, max_quantity=?, status=? WHERE id=?
        ");
        $stmt->execute([
            $_POST['provider_id'] ?: null,
            trim($_POST['provider_service_id']),
            trim($_POST['name']),
            trim($_POST['category']),
            trim($_POST['description']),
            (float) $_POST['rate'],
            (float) $_POST['cost_rate'] ?: null,
            (int) $_POST['min_quantity'],
            (int) $_POST['max_quantity'],
            $_POST['status'],
            (int) $_POST['service_id'],
        ]);
        $message = 'Service updated.';
    }

    if ($action === 'delete') {
        $db->prepare('DELETE FROM services WHERE id = ?')->execute([(int) $_POST['service_id']]);
        $message = 'Service deleted.';
    }
}

$services = $db->query("
    SELECT s.*, p.name AS provider_name
    FROM services s
    LEFT JOIN providers p ON s.provider_id = p.id
    ORDER BY s.category, s.name
")->fetchAll();

$pageTitle = 'Manage Services';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-end">
    <div>
        <h1>Manage Services</h1>
        <p>Create and edit SMM services</p>
    </div>
    <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#createService"><i class="bi bi-plus-lg me-1"></i>Add Service</button>
</div>

<?php if ($message): ?><div class="alert alert-success"><?= sanitize($message) ?></div><?php endif; ?>

<div class="card card-smm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-smm table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Rate/1K</th>
                        <th>Cost/1K</th>
                        <th>Provider</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $s): ?>
                    <tr>
                        <td>#<?= $s['id'] ?></td>
                        <td><?= sanitize($s['name']) ?></td>
                        <td><?= sanitize($s['category']) ?></td>
                        <td>$<?= number_format((float)$s['rate'], 2) ?></td>
                        <td><?= $s['cost_rate'] ? '$' . number_format((float)$s['cost_rate'], 2) : '—' ?></td>
                        <td><?= sanitize($s['provider_name'] ?? '—') ?></td>
                        <td><span class="badge badge-status badge-status-<?= $s['status'] ?>"><?= ucfirst($s['status']) ?></span></td>
                        <td>
                            <button class="btn btn-action btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editService<?= $s['id'] ?>">Edit</button>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this service?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                                <button type="submit" class="btn btn-action btn-outline-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="editService<?= $s['id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Service</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                                        <?php renderServiceFields($s, $providers); ?>
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

<div class="modal fade" id="createService" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <?php renderServiceFields([], $providers); ?>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-accent">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
