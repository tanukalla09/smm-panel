<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/notifications.php';

if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? SITE_URL . '/admin/index.php' : SITE_URL . '/user/dashboard.php'));
    exit;
}

$db = getDB();
$popularServices = $db->query("SELECT name, category, rate, min_quantity FROM services WHERE status = 'active' ORDER BY id LIMIT 8")->fetchAll();

$features = [
    ['bi-lightning-charge-fill', 'Instant Delivery', 'Automated processing from order to delivery.', 'left'],
    ['bi-shield-check', 'Secure Payments', 'Stripe-powered wallet with instant top-ups.', 'right'],
    ['bi-graph-up-arrow', 'Wholesale Pricing', 'Best rates for resellers and agencies.', 'left'],
    ['bi-headset', '24/7 Support', 'Real humans ready to help via tickets.', 'right'],
    ['bi-code-slash', 'API Ready', 'Integrate into your own platform.', 'left'],
    ['bi-arrow-repeat', 'Auto Refill', 'Drop protection on eligible services.', 'right'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> — Premium Social Media Marketing</title>
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
            <div class="d-flex gap-2">
                <a href="<?= SITE_URL ?>/login.php" class="btn btn-ghost btn-sm px-3">Login</a>
                <a href="<?= SITE_URL ?>/register.php" class="btn btn-accent btn-sm px-4">Get Started Free</a>
            </div>
        </div>
    </div>
</nav>

<section class="hero-premium hero-animated hero-vibrant" id="hero">
    <div class="hero-gradient-bg hero-lavender-bg" aria-hidden="true"></div>
    <div class="hero-glow-orb" aria-hidden="true"></div>
    <canvas class="hero-particles" id="heroParticles" aria-hidden="true"></canvas>
    <div class="hero-mesh" aria-hidden="true"></div>
    <div class="container px-4 position-relative">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-6 text-center text-lg-start py-5">
                <span class="hero-badge anim-fade-up">Trusted by 10,000+ creators</span>
                <h1 class="hero-premium-title hero-title-xl">
                    <span id="typingHeadline" data-text="Grow Your Social Media..."></span><span class="typing-cursor">|</span><span class="hero-title-rest"> with <span class="text-gradient">Real Results</span></span>
                </h1>
                <p class="hero-premium-sub anim-fade-up-delay">The premium SMM panel built for agencies, creators, and resellers. Instant delivery, wholesale pricing, enterprise security.</p>
                <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start mt-4 hero-cta anim-slide-up-bounce">
                    <a href="<?= SITE_URL ?>/register.php" class="btn btn-accent btn-lg px-4">Get Started Free</a>
                    <a href="#services" class="btn btn-ghost btn-lg px-4">View Services</a>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block position-relative">
                <div class="hero-isometric anim-reveal" data-reveal="right">
                    <div class="iso-scene">
                        <div class="iso-float-icon i1" aria-hidden="true">📱</div>
                        <div class="iso-float-icon i2" aria-hidden="true">📊</div>
                        <div class="iso-float-icon i3" aria-hidden="true">💻</div>
                        <div class="iso-float-icon i4" aria-hidden="true">▶️</div>
                        <div class="iso-desk-leg left"></div>
                        <div class="iso-desk-leg right"></div>
                        <div class="iso-desk"></div>
                        <div class="iso-laptop"></div>
                        <div class="iso-laptop-base"></div>
                        <div class="iso-person">
                            <div class="iso-head"></div>
                            <div class="iso-body"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <a href="#features" class="hero-scroll-indicator" aria-label="Scroll down">
        <span class="hero-scroll-text">Scroll</span>
        <span class="hero-scroll-arrow"><i class="bi bi-chevron-down"></i></span>
    </a>
</section>

<div class="container px-4">
    <div class="social-proof-bar anim-reveal-scale">
        <div class="proof-item">
            <strong class="count-up" data-count="10000" data-suffix="+" data-decimals="0">0</strong>
            <span>Orders Delivered</span>
        </div>
        <div class="proof-divider"></div>
        <div class="proof-item">
            <strong class="count-up" data-count="500" data-suffix="+" data-decimals="0">0</strong>
            <span>Services</span>
        </div>
        <div class="proof-divider"></div>
        <div class="proof-item">
            <strong class="count-up" data-count="99.9" data-suffix="%" data-decimals="1">0</strong>
            <span>Uptime</span>
        </div>
    </div>
</div>

<section class="section-padding" id="features">
    <div class="container px-4">
        <div class="text-center mb-5 section-header-reveal">
            <span class="section-label">Features</span>
            <h2 class="section-title">Built for scale</h2>
        </div>
        <div class="row g-4">
            <?php foreach ($features as [$icon, $title, $desc, $dir]): ?>
            <div class="col-md-4">
                <div class="feature-card anim-reveal" data-reveal="<?= $dir ?>">
                    <div class="feature-icon-wrap"><i class="bi <?= $icon ?>"></i></div>
                    <h5 class="fw-bold"><?= $title ?></h5>
                    <p class="text-muted mb-0"><?= $desc ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-padding bg-white-section how-it-works-section" id="how-it-works">
    <div class="container px-4 text-center">
        <div class="section-header-reveal mb-5">
            <span class="section-label">How it works</span>
            <h2 class="section-title">Three simple steps</h2>
        </div>
        <div class="row g-4 steps-row position-relative">
            <div class="steps-connector" aria-hidden="true"><div class="steps-connector-line"></div></div>
            <?php foreach ([['1','Create Account','Sign up free in seconds.'],['2','Add Funds','Top up via Stripe instantly.'],['3','Place Orders','Browse services and grow.']] as [$n, $t, $d]): ?>
            <div class="col-md-4">
                <div class="step-card anim-step">
                    <div class="step-circle"><?= $n ?></div>
                    <h5 class="fw-bold"><?= $t ?></h5>
                    <p class="text-muted mb-0"><?= $d ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-padding" id="services">
    <div class="container px-4">
        <div class="text-center mb-5 section-header-reveal">
            <span class="section-label">Pricing</span>
            <h2 class="section-title">Popular services</h2>
        </div>
        <div class="row g-4">
            <?php foreach ($popularServices as $i => $svc): ?>
            <div class="col-md-6 col-lg-3">
                <div class="service-preview-card anim-stagger" style="--stagger: <?= $i + 1 ?>">
                    <div class="platform-emoji mb-2"><?= platformEmoji($svc['category']) ?></div>
                    <span class="category-badge"><?= sanitize($svc['category']) ?></span>
                    <h6 class="fw-bold mt-2 mb-1"><?= sanitize($svc['name']) ?></h6>
                    <div class="service-rate" style="font-size:1.25rem">$<?= number_format((float)$svc['rate'], 2) ?><small class="text-muted fs-6">/1K</small></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5 anim-reveal" data-reveal="up">
            <a href="<?= SITE_URL ?>/register.php" class="btn btn-accent btn-lg px-5">Get Started Free</a>
        </div>
    </div>
</section>

<footer class="landing-footer">
    <div class="container px-4">
        <div class="row g-4 mb-4">
            <div class="col-md-4"><h6 class="logo-gradient"><span class="logo-icon">⚡</span> <?= SITE_NAME ?></h6><p class="small">Premium SMM panel for professionals.</p></div>
            <div class="col-md-2"><h6>Platform</h6><a href="<?= SITE_URL ?>/login.php">Login</a><a href="<?= SITE_URL ?>/register.php">Register</a></div>
            <div class="col-md-2"><h6>Support</h6><a href="<?= SITE_URL ?>/help.php">Help Center</a><a href="<?= SITE_URL ?>/contact.php">Contact</a></div>
            <div class="col-md-2"><h6>Legal</h6><a href="<?= SITE_URL ?>/terms.php">Terms of Service</a><a href="<?= SITE_URL ?>/privacy.php">Privacy Policy</a></div>
        </div>
        <hr><div class="text-center small">&copy; <?= date('Y') ?> <?= SITE_NAME ?></div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= SITE_URL ?>/assets/js/animations.js"></script>
</body>
</html>
