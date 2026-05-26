<?php
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$admin = getAdminUser();
$db = getDB();
$ticketId = (int) ($_GET['id'] ?? 0);

$stmt = $db->prepare("
    SELECT t.*, u.username, u.email
    FROM tickets t
    JOIN users u ON t.user_id = u.id
    WHERE t.id = ?
");
$stmt->execute([$ticketId]);
$ticket = $stmt->fetch();

if (!$ticket) {
    flash('error', 'Ticket not found.');
    header('Location: ' . SITE_URL . '/admin/tickets.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'reply') {
        $message = trim($_POST['message'] ?? '');
        if ($message === '' || strlen($message) < 2) {
            flash('error', 'Please enter a reply message.');
        } else {
            $db->prepare('INSERT INTO ticket_replies (ticket_id, user_id, is_admin, message) VALUES (?, ?, 1, ?)')
               ->execute([$ticketId, $admin['id'], $message]);
            $db->prepare("UPDATE tickets SET status = 'open', updated_at = NOW() WHERE id = ?")->execute([$ticketId]);

            require_once __DIR__ . '/../includes/notifications.php';
            notify((int)$ticket['user_id'], 'Admin replied to your ticket', "Admin replied to your ticket: {$ticket['subject']}", 'info', SITE_URL . '/user/ticket_view.php?id=' . $ticketId);

            try {
                require_once __DIR__ . '/../includes/mailer.php';
                mailTicketReply($ticket['email'], $ticket['username'], $ticket['subject'], $message, $ticketId);
            } catch (Throwable $e) {
                error_log('Ticket reply email failed: ' . $e->getMessage());
            }

            flash('success', 'Reply sent successfully.');
        }
        header('Location: ' . SITE_URL . '/admin/ticket_view.php?id=' . $ticketId);
        exit;
    }

    if ($action === 'close') {
        $db->prepare("UPDATE tickets SET status = 'closed', updated_at = NOW() WHERE id = ?")->execute([$ticketId]);
        flash('success', 'Ticket #' . $ticketId . ' has been closed.');
        header('Location: ' . SITE_URL . '/admin/ticket_view.php?id=' . $ticketId);
        exit;
    }

    if ($action === 'reopen') {
        $db->prepare("UPDATE tickets SET status = 'open', updated_at = NOW() WHERE id = ?")->execute([$ticketId]);
        flash('success', 'Ticket #' . $ticketId . ' has been reopened.');
        header('Location: ' . SITE_URL . '/admin/ticket_view.php?id=' . $ticketId);
        exit;
    }
}

$stmt = $db->prepare("
    SELECT tr.*, u.username
    FROM ticket_replies tr
    JOIN users u ON tr.user_id = u.id
    WHERE tr.ticket_id = ?
    ORDER BY tr.created_at ASC
");
$stmt->execute([$ticketId]);
$replies = $stmt->fetchAll();

$pageTitle = 'Ticket #' . $ticketId;
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-2">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/admin/tickets.php">Support Tickets</a></li>
            <li class="breadcrumb-item active">Ticket #<?= $ticketId ?></li>
        </ol>
    </nav>
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1><?= sanitize($ticket['subject']) ?></h1>
            <p class="mb-0">
                <span class="user-avatar d-inline-flex me-1" style="width:24px;height:24px;font-size:0.65rem"><?= strtoupper(substr($ticket['username'], 0, 1)) ?></span>
                <?= sanitize($ticket['username']) ?> &middot; <?= sanitize($ticket['email']) ?>
                &middot; Opened <?= date('M j, Y H:i', strtotime($ticket['created_at'])) ?>
            </p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <span class="badge badge-status badge-status-<?= $ticket['status'] === 'open' ? 'processing' : 'inactive' ?> fs-6">
                <?= ucfirst($ticket['status']) ?>
            </span>
            <?php if ($ticket['status'] === 'open'): ?>
            <form method="POST" class="d-inline" onsubmit="return confirm('Close this ticket?')">
                <input type="hidden" name="action" value="close">
                <button type="submit" class="btn btn-action btn-outline-danger btn-sm">
                    <i class="bi bi-x-circle me-1"></i>Close Ticket
                </button>
            </form>
            <?php else: ?>
            <form method="POST" class="d-inline">
                <input type="hidden" name="action" value="reopen">
                <button type="submit" class="btn btn-action btn-outline-success btn-sm">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reopen
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($msg = flash('success')): ?>
<div class="alert alert-success"><?= sanitize($msg) ?></div>
<?php endif; ?>
<?php if ($msg = flash('error')): ?>
<div class="alert alert-danger"><?= sanitize($msg) ?></div>
<?php endif; ?>

<div class="card card-smm mb-4"><div class="card-body p-4">
    <div class="chat-bubble chat-bubble-user">
        <div class="chat-meta"><strong><?= sanitize($ticket['username']) ?></strong><span class="chat-time"><?= date('M j, Y H:i', strtotime($ticket['created_at'])) ?></span></div>
        <div class="chat-text"><?= nl2br(sanitize($ticket['message'])) ?></div>
    </div>
    <?php foreach ($replies as $reply): ?>
    <div class="chat-bubble <?= $reply['is_admin'] ? 'chat-bubble-admin' : 'chat-bubble-user' ?>">
        <div class="chat-meta">
            <strong><?= $reply['is_admin'] ? 'Support Team' : sanitize($reply['username']) ?></strong>
            <?php if ($reply['is_admin']): ?><span class="badge badge-status badge-status-completed ms-1">Support Team</span><?php endif; ?>
            <span class="chat-time"><?= date('M j, Y H:i', strtotime($reply['created_at'])) ?></span>
        </div>
        <div class="chat-text"><?= nl2br(sanitize($reply['message'])) ?></div>
    </div>
    <?php endforeach; ?>
</div></div>

<?php if ($ticket['status'] === 'open'): ?>
<div class="card card-smm"><div class="card-body p-4">
    <form method="POST">
        <input type="hidden" name="action" value="reply">
        <label class="form-label fw-semibold">Reply as Admin</label>
        <textarea name="message" class="form-control reply-box mb-3" rows="4" placeholder="Type your reply..." required></textarea>
        <button type="submit" class="btn btn-accent">Send Reply <i class="bi bi-arrow-right ms-1"></i></button>
    </form>
</div></div>
<?php else: ?>
<div class="alert alert-warning mb-0">
    <i class="bi bi-lock me-1"></i>This ticket is closed. Reopen it to send a reply.
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
