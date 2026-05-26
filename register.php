<?php
require_once __DIR__ . '/includes/auth.php';
redirectIfLoggedIn();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        $db = getDB();

        $stmt = $db->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = 'Username or email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
            $stmt->execute([$username, $email, $hash]);
            $newUserId = (int) $db->lastInsertId();

            require_once __DIR__ . '/includes/notifications.php';
            notify($newUserId, 'Welcome to SMM Panel! 🎉', 'Start by adding funds and placing your first order.', 'success', SITE_URL . '/user/add_funds.php');
            notifyAdmins('New user registered', "New user {$username} just registered.", 'info', SITE_URL . '/admin/users.php');

            try {
                require_once __DIR__ . '/includes/mailer.php';
                mailWelcomeUser($email, $username);
                mailAdminNewUser($username, $email);
            } catch (Throwable $e) {
                error_log('Registration email failed: ' . $e->getMessage());
            }

            flash('success', 'Registration successful! Please log in.');
            header('Location: ' . SITE_URL . '/login.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — <?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/assets/css/style.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/assets/css/animations.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/assets/css/auth.css" rel="stylesheet">
</head>
<body class="auth-page-v2 anim-body">

<div class="auth-page-bg" aria-hidden="true">
    <div class="auth-gradient-layer"></div>
    <div class="auth-streak auth-streak-1"></div>
    <div class="auth-streak auth-streak-2"></div>
    <div class="auth-geo auth-geo-triangle"></div>
    <div class="auth-geo auth-geo-circle"></div>
    <div class="auth-geo auth-geo-square sq1"></div>
    <div class="auth-geo auth-geo-square sq2"></div>
    <div class="auth-geo auth-geo-square sq3"></div>
    <div class="auth-geo auth-geo-square sq4"></div>
</div>

<div class="auth-split">
    <div class="auth-split-left d-none d-lg-flex">
        <?php require __DIR__ . '/includes/auth_illustration.php'; ?>
    </div>
    <div class="auth-split-right">
        <div class="auth-form-card anim-float-in">
            <a href="<?= SITE_URL ?>/" class="auth-logo logo-gradient d-block text-center text-decoration-none">
                <span class="logo-icon">⚡</span> <?= SITE_NAME ?>
            </a>
            <p class="auth-subtitle">Create your free account</p>

            <?php foreach ($errors as $err): ?>
            <div class="alert alert-danger py-2 mb-3"><?= sanitize($err) ?></div>
            <?php endforeach; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" value="<?= sanitize($_POST['username'] ?? '') ?>" required minlength="3" placeholder="Choose a username">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= sanitize($_POST['email'] ?? '') ?>" required placeholder="you@example.com">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required minlength="6" placeholder="At least 6 characters">
                </div>
                <div class="mb-4">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required placeholder="Repeat your password">
                </div>
                <button type="submit" class="btn btn-auth-submit">Create Account</button>
            </form>
            <p class="auth-switch">
                Already have an account? <a href="<?= SITE_URL ?>/login.php">Sign in</a>
            </p>
        </div>
    </div>
</div>

<script src="<?= SITE_URL ?>/assets/js/animations.js"></script>
</body>
</html>
