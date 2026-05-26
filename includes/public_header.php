<?php
require_once __DIR__ . '/auth.php';
$pageTitle = $pageTitle ?? SITE_NAME;
$currentUser = getCurrentUser();
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
<body class="landing-body anim-body">
<div class="scroll-progress" id="scrollProgress" aria-hidden="true"></div>

<nav class="landing-nav landing-nav-animate" id="landingNav">
    <div class="container px-4">
        <div class="d-flex justify-content-between align-items-center">
            <a class="logo-gradient navbar-brand m-0" href="<?= SITE_URL ?>/"><span class="logo-icon">⚡</span> <?= SITE_NAME ?></a>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= SITE_URL ?>/help.php" class="btn btn-ghost btn-sm px-3 d-none d-md-inline-flex">Help</a>
                <?php if ($currentUser): ?>
                <a href="<?= SITE_URL ?>/user/dashboard.php" class="btn btn-accent btn-sm px-4">Dashboard</a>
                <?php else: ?>
                <a href="<?= SITE_URL ?>/login.php" class="btn btn-ghost btn-sm px-3">Login</a>
                <a href="<?= SITE_URL ?>/register.php" class="btn btn-accent btn-sm px-4">Get Started</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<main class="public-page">
