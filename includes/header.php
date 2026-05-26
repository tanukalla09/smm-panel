<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/notifications.php';
$currentUser = getCurrentUser();
$pageTitle = $pageTitle ?? SITE_NAME;
$navLinks = [
    ['Dashboard', SITE_URL . '/user/dashboard.php', 'bi-speedometer2'],
    ['Services', SITE_URL . '/user/services.php', 'bi-grid'],
    ['Orders', SITE_URL . '/user/orders.php', 'bi-bag'],
    ['Add Funds', SITE_URL . '/user/add_funds.php', 'bi-wallet2'],
    ['Support', SITE_URL . '/user/tickets.php', 'bi-headset'],
];
$currentPath = $_SERVER['PHP_SELF'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($pageTitle) ?> — <?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/assets/css/style.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/assets/css/animations.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100 anim-body">
<div class="scroll-progress" id="scrollProgress" aria-hidden="true"></div>
<nav class="navbar navbar-expand-lg navbar-smm sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand logo-gradient" href="<?= SITE_URL ?>/">
            <span class="logo-icon">⚡</span> <?= SITE_NAME ?>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto ms-lg-3 gap-lg-1">
                <?php if (isLoggedIn() && !isAdmin()): ?>
                <?php foreach ($navLinks as [$label, $url, $icon]):
                    $active = str_contains($currentPath, basename($url)) ? 'active' : '';
                ?>
                <li class="nav-item">
                    <a class="nav-link nav-link-premium <?= $active ?>" href="<?= $url ?>">
                        <i class="bi <?= $icon ?> me-1"></i><?= $label ?>
                    </a>
                </li>
                <?php endforeach; ?>
                <?php elseif (isAdmin()): ?>
                <li class="nav-item"><a class="nav-link nav-link-premium" href="<?= SITE_URL ?>/admin/index.php"><i class="bi bi-shield-lock me-1"></i>Admin</a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav align-items-lg-center gap-2">
                <?php if ($currentUser): ?>
                <li class="nav-item">
                    <a href="<?= SITE_URL ?>/user/add_funds.php" class="wallet-pill">
                        <i class="bi bi-currency-dollar"></i><?= number_format((float)$currentUser['balance'], 2) ?>
                    </a>
                </li>
                <li class="nav-item"><?php require __DIR__ . '/notification_dropdown.php'; ?></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle profile-trigger" href="#" data-bs-toggle="dropdown">
                        <span class="profile-avatar"><?= strtoupper(substr($currentUser['username'], 0, 1)) ?></span>
                        <span class="d-none d-md-inline fw-semibold"><?= sanitize($currentUser['username']) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/user/notifications.php"><i class="bi bi-bell me-2"></i>Notifications</a></li>
                        <?php if (isAdmin()): ?>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/admin/index.php"><i class="bi bi-shield-lock me-2"></i>Admin Panel</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= SITE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item"><a class="nav-link nav-link-premium" href="<?= SITE_URL ?>/login.php">Login</a></li>
                <li class="nav-item"><a class="btn btn-accent btn-sm px-3" href="<?= SITE_URL ?>/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="flex-grow-1">
