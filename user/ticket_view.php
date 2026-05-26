<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$user = getCurrentUser();
$db = getDB();
$ticketId = (int) ($_GET['id'] ?? 0);

$stmt = $db->prepare('SELECT * FROM tickets WHERE id = ? AND user_id = ?');
$stmt->execute([$ticketId, $user['id']]);
$ticket = $stmt->fetch();

if (!$ticket) {
    flash('error', 'Ticket not found.');
    header('Location: ' . SITE_URL . '/user/tickets.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reply') {
    if ($ticket['status'] === 'closed') {
        flash('error', 'This ticket is closed.');
    } else {
        $message = trim($_POST['message'] ?? '');
        if (strlen($message) >= 2) {
            $db->prepare('INSERT INTO ticket_replies (ticket_id, user_id, is_admin, message) VALUES (?, ?, 0, ?)')
               ->execute([$ticketId, $user['id'], $message]);
            $db->prepare('UPDATE tickets SET updated_at = NOW() WHERE id = ?')->execute([$ticketId]);
            flash('success', 'Reply sent.');
        }
    }
    header('Location: ' . SITE_URL . '/user/ticket_view.php?id=' . $ticketId);
    exit;
}

$stmt = $db->prepare("SELECT tr.*, u.username FROM ticket_replies tr JOIN users u ON tr.user_id = u.id WHERE tr.ticket_id = ? ORDER BY tr.created_at ASC");
$stmt->execute([$ticketId]);
$replies = $stmt->fetchAll();

$pageTitle = 'Ticket #' . $ticketId;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid px-4 py-4">
    <div class="page-header">
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-2"><li class="breadcrumb-item"><a href="<?= SITE_URL ?>/user/tickets.php">Support</a></li><li class="breadcrumb-item active">#<?= $ticketId ?></li></ol></nav>
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div><h1><?= sanitize($ticket['subject']) ?></h1><p class="mb-0 text-muted">Opened <?= date('M j, Y H:i', strtotime($ticket['created_at'])) ?></p></div>
            <span class="badge badge-status badge-status-<?= $ticket['status'] === 'open' ? 'processing' : 'inactive' ?>"><?= ucfirst($ticket['status']) ?></span>
        </div>
    </div>

    <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= sanitize($msg) ?></div><?php endif; ?>
    <?php if ($msg = flash('error')): ?><div class="alert alert-danger"><?= sanitize($msg) ?></div><?php endif; ?>

    <div class="ticket-chat card card-smm mb-4">
        <div class="card-body p-4">
            <div class="chat-bubble chat-bubble-user">
                <div class="chat-meta"><strong><?= sanitize($user['username']) ?></strong><span class="chat-time"><?= date('M j, Y H:i', strtotime($ticket['created_at'])) ?></span></div>
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
        </div>
    </div>

    <?php if ($ticket['status'] === 'open'): ?>
    <div class="card card-smm">
        <div class="card-body p-4">
            <form method="POST">
                <input type="hidden" name="action" value="reply">
                <label class="form-label fw-semibold">Your reply</label>
                <textarea name="message" class="form-control reply-box mb-3" rows="4" placeholder="Type your message..." required></textarea>
                <button type="submit" class="btn btn-accent">Send Reply <i class="bi bi-arrow-right ms-1"></i></button>
            </form>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-warning mb-0"><i class="bi bi-lock me-1"></i>This ticket is closed.</div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
