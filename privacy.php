<?php
$pageTitle = 'Privacy Policy';
require_once __DIR__ . '/includes/public_header.php';
?>

<div class="public-page-hero">
    <div class="container px-4">
        <span class="section-label">Legal</span>
        <h1 class="public-page-title">Privacy Policy</h1>
        <p class="public-page-sub">Last updated: <?= date('F j, Y') ?></p>
    </div>
</div>

<div class="container px-4 pb-5">
    <div class="card card-smm legal-content p-4 p-md-5">
        <p class="text-muted"><?= SITE_NAME ?> ("we," "us," or "our") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, and safeguard your information when you use our SMM panel platform.</p>

        <h2>1. Data Collection</h2>
        <p>We collect information you provide directly and data generated through your use of the Service:</p>
        <ul>
            <li><strong>Account data:</strong> Username, email address, and hashed password when you register</li>
            <li><strong>Transaction data:</strong> Order history, wallet balance, payment records, and Stripe transaction IDs</li>
            <li><strong>Support data:</strong> Ticket messages, contact form submissions, and correspondence</li>
            <li><strong>Technical data:</strong> IP address, browser type, session identifiers, and access logs</li>
            <li><strong>Order data:</strong> Links and quantities submitted when placing orders</li>
        </ul>

        <h2>2. How We Use Data</h2>
        <p>We use your information to:</p>
        <ul>
            <li>Create and manage your account</li>
            <li>Process orders, payments, and wallet transactions</li>
            <li>Send in-app notifications about orders, payments, and support replies</li>
            <li>Respond to support tickets and contact form inquiries</li>
            <li>Prevent fraud, abuse, and unauthorized access</li>
            <li>Improve our services and platform performance</li>
            <li>Comply with legal obligations</li>
        </ul>
        <p>We do not sell your personal data to third parties.</p>

        <h2>3. Cookies</h2>
        <p>We use essential session cookies to keep you logged in and maintain your session security. These cookies are required for the platform to function and cannot be disabled while using authenticated features.</p>
        <p>We do not use third-party advertising or tracking cookies. Session cookies expire when you log out or after a period of inactivity as configured in our security settings.</p>

        <h2>4. Third Party Services</h2>
        <p>We integrate with trusted third-party services to operate the platform:</p>
        <ul>
            <li><strong>Stripe:</strong> Payment processing. Stripe handles card data according to their <a href="https://stripe.com/privacy" target="_blank" rel="noopener">Privacy Policy</a>. We do not store full card numbers.</li>
            <li><strong>SMM Providers:</strong> Order fulfillment partners receive order links and quantities necessary to deliver services.</li>
            <li><strong>Hosting infrastructure:</strong> Our servers and database providers may process data in the course of hosting the Service.</li>
        </ul>
        <p>Each third-party service operates under its own privacy policy. We recommend reviewing their policies for full details.</p>

        <h2>5. Data Retention &amp; Security</h2>
        <p>We retain account and transaction data for as long as your account is active and as required for legal, accounting, or dispute resolution purposes. Passwords are stored using bcrypt hashing. We implement reasonable technical and organizational measures to protect your data.</p>

        <h2>6. Your Rights</h2>
        <p>Depending on your jurisdiction, you may have the right to access, correct, or delete your personal data. To exercise these rights, contact us using the details below. Account deletion requests are subject to resolution of pending orders and legal retention requirements.</p>

        <h2>7. Contact</h2>
        <p>For privacy-related questions or requests, contact us at:</p>
        <ul>
            <li>Email: <a href="mailto:privacy@smmpanel.local">privacy@smmpanel.local</a></li>
            <li>Contact form: <a href="<?= SITE_URL ?>/contact.php"><?= SITE_URL ?>/contact.php</a></li>
        </ul>

        <p class="mb-0 mt-4 text-muted">We may update this Privacy Policy periodically. Continued use of the Service after changes constitutes acceptance of the updated policy.</p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/public_footer.php'; ?>
