<?php
require_once __DIR__ . '/includes/auth.php';
redirectIfLoggedIn();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter username and password.';
    } else {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE (username = ? OR email = ?) AND status = ?');
        $stmt->execute([$username, $username, 'active']);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            loginUser($user);
            $dest = $user['role'] === 'admin'
                ? SITE_URL . '/admin/index.php'
                : SITE_URL . '/user/dashboard.php';
            header('Location: ' . $dest);
            exit;
        }
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?= SITE_NAME ?></title>
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
            <p class="auth-subtitle">Welcome back — sign in to continue</p>

            <?php if ($error): ?>
            <div class="alert alert-danger py-2 mb-3"><?= sanitize($error) ?></div>
            <?php endif; ?>

            <?php if ($msg = flash('success')): ?>
            <div class="alert alert-success py-2 mb-3"><?= sanitize($msg) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Username or Email</label>
                    <input type="text" name="username" class="form-control" value="<?= sanitize($_POST['username'] ?? '') ?>" required autofocus placeholder="Enter your username">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Enter your password">
                </div>
                <div class="auth-form-row">
                    <label class="auth-remember">
                        <input type="checkbox" name="remember" value="1">
                        Remember me
                    </label>
                    <a href="<?= SITE_URL ?>/help.php" class="auth-forgot">Forgot password?</a>
                </div>
                <button type="submit" class="btn btn-auth-submit">Sign In</button>
            </form>
            <p class="auth-switch">
                Don't have an account? <a href="<?= SITE_URL ?>/register.php">Create one free</a>
            </p>
        </div>
    </div>
</div>

<script src="<?= SITE_URL ?>/assets/js/animations.js"></script>
</body>
</html>
