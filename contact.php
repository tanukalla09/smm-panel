<?php
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Contact Us';
$success = '';
$error = '';
$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || strlen($name) < 2) {
        $error = 'Please enter your name.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($subject === '' || strlen($subject) < 3) {
        $error = 'Please enter a subject (at least 3 characters).';
    } elseif ($message === '' || strlen($message) < 10) {
        $error = 'Please enter a message (at least 10 characters).';
    } else {
        $db = getDB();
        $userId = $user ? (int) $user['id'] : null;
        $stmt = $db->prepare('INSERT INTO contacts (user_id, name, email, subject, message) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $name, $email, $subject, $message]);

        if ($user) {
            require_once __DIR__ . '/includes/notifications.php';
            notifyAdmins('New contact message', "Contact from {$name}: {$subject}", 'info', SITE_URL . '/admin/index.php');
        }

        $success = 'Thank you! Your message has been sent. We will get back to you soon.';
        $_POST = [];
    }
}

require_once __DIR__ . '/includes/public_header.php';
?>

<div class="public-page-hero">
    <div class="container px-4">
        <span class="section-label">Get in Touch</span>
        <h1 class="public-page-title">Contact Us</h1>
        <p class="public-page-sub">Have a question or need assistance? We'd love to hear from you.</p>
    </div>
</div>

<div class="container px-4 pb-5">
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card card-smm">
                <div class="card-header"><i class="bi bi-envelope me-2 text-primary"></i>Send a Message</div>
                <div class="card-body p-4">
                    <?php if ($success): ?>
                    <div class="alert alert-success"><?= sanitize($success) ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                    <div class="alert alert-danger"><?= sanitize($error) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" required minlength="2"
                                    value="<?= sanitize($_POST['name'] ?? ($user['username'] ?? '')) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required
                                    value="<?= sanitize($_POST['email'] ?? ($user['email'] ?? '')) ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Subject</label>
                                <input type="text" name="subject" class="form-control" required minlength="3"
                                    value="<?= sanitize($_POST['subject'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Message</label>
                                <textarea name="message" class="form-control" rows="6" required minlength="10" placeholder="How can we help you?"><?= sanitize($_POST['message'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-accent px-4">
                                    <i class="bi bi-send me-1"></i>Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card card-smm p-4 mb-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-building me-2 text-primary"></i>Company Info</h5>
                <ul class="list-unstyled text-muted mb-0 company-info-list">
                    <li class="mb-3"><i class="bi bi-envelope me-2"></i>support@smmpanel.local</li>
                    <li class="mb-3"><i class="bi bi-clock me-2"></i>Support: Mon–Sun, 24/7</li>
                    <li class="mb-3"><i class="bi bi-globe me-2"></i><?= SITE_URL ?></li>
                    <li><i class="bi bi-shield-check me-2"></i>Secure &amp; encrypted communications</li>
                </ul>
            </div>
            <div class="card card-smm p-4">
                <h5 class="fw-bold mb-2">Existing customer?</h5>
                <p class="text-muted small mb-3">For order-specific issues, use our ticket system for faster resolution with your account history attached.</p>
                <a href="<?= SITE_URL ?>/user/tickets.php" class="btn btn-outline-primary w-100 mb-2">
                    <i class="bi bi-ticket-perforated me-1"></i>Open Support Ticket
                </a>
                <a href="<?= SITE_URL ?>/help.php" class="btn btn-ghost w-100 btn-sm">Browse Help Center</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/public_footer.php'; ?>
