<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$user = getCurrentUser();
$db = getDB();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($subject === '' || strlen($subject) < 3) {
        $error = 'Subject must be at least 3 characters.';
    } elseif ($message === '' || strlen($message) < 10) {
        $error = 'Message must be at least 10 characters.';
    } else {
        $stmt = $db->prepare('INSERT INTO tickets (user_id, subject, message, status) VALUES (?, ?, ?, ?)');
        $stmt->execute([$user['id'], $subject, $message, 'open']);
        $ticketId = (int) $db->lastInsertId();

        require_once __DIR__ . '/../includes/notifications.php';
        notifyAdmins('New support ticket', "New ticket opened by {$user['username']}: {$subject}", 'warning', SITE_URL . '/admin/ticket_view.php?id=' . $ticketId);

        try {
            require_once __DIR__ . '/../includes/mailer.php';
            mailAdminNewTicket($ticketId, $subject, $message, $user['username'], $user['email']);
        } catch (Throwable $e) {
            error_log('New ticket email failed: ' . $e->getMessage());
        }

        flash('success', 'Support ticket #' . $ticketId . ' created successfully.');
        header('Location: ' . SITE_URL . '/user/ticket_view.php?id=' . $ticketId);
        exit;
    }
}

$stmt = $db->prepare("
    SELECT t.*,
           (SELECT COUNT(*) FROM ticket_replies tr WHERE tr.ticket_id = t.id) AS reply_count
    FROM tickets t
    WHERE t.user_id = ?
    ORDER BY t.created_at DESC
");
$stmt->execute([$user['id']]);
$tickets = $stmt->fetchAll();

$pageTitle = 'Support';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid px-4 py-4">
    <div class="page-header d-flex flex-wrap justify-content-between align-items-end gap-3">
        <div>
            <h1>Support Tickets</h1>
            <p>Get help from our support team</p>
        </div>
        <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#createTicketModal">
            <i class="bi bi-plus-lg me-1"></i>New Ticket
        </button>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger"><?= sanitize($error) ?></div>
    <?php endif; ?>
    <?php if ($msg = flash('success')): ?>
    <div class="alert alert-success"><?= sanitize($msg) ?></div>
    <?php endif; ?>
    <?php if ($msg = flash('error')): ?>
    <div class="alert alert-danger"><?= sanitize($msg) ?></div>
    <?php endif; ?>

    <div class="card card-smm">
        <div class="card-body p-0">
            <?php if (empty($tickets)): ?>
            <div class="empty-state">
                <i class="bi bi-headset"></i>
                <p class="mb-3">You haven't opened any support tickets yet.</p>
                <button class="btn btn-accent btn-sm" data-bs-toggle="modal" data-bs-target="#createTicketModal">
                    Create Your First Ticket
                </button>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-smm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Replies</th>
                            <th>Created</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $t): ?>
                        <tr>
                            <td><strong>#<?= $t['id'] ?></strong></td>
                            <td><?= sanitize($t['subject']) ?></td>
                            <td>
                                <span class="badge badge-status badge-status-<?= $t['status'] === 'open' ? 'processing' : 'inactive' ?>">
                                    <?= ucfirst($t['status']) ?>
                                </span>
                            </td>
                            <td><?= (int) $t['reply_count'] ?></td>
                            <td class="text-muted"><?= date('M j, Y H:i', strtotime($t['created_at'])) ?></td>
                            <td>
                                <a href="<?= SITE_URL ?>/user/ticket_view.php?id=<?= $t['id'] ?>" class="btn btn-action btn-outline-primary btn-sm">View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Create Ticket Modal -->
<div class="modal fade" id="createTicketModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-headset me-2 text-primary"></i>New Support Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control" placeholder="Brief description of your issue" required minlength="3" value="<?= sanitize($_POST['subject'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="6" placeholder="Describe your issue in detail..." required minlength="10"><?= sanitize($_POST['message'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent">Submit Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($error && ($_POST['action'] ?? '') === 'create'): ?>
<script>document.addEventListener('DOMContentLoaded', () => new bootstrap.Modal(document.getElementById('createTicketModal')).show());</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
