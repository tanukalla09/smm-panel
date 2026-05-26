<?php
$pageTitle = 'Terms of Service';
require_once __DIR__ . '/includes/public_header.php';
?>

<div class="public-page-hero">
    <div class="container px-4">
        <span class="section-label">Legal</span>
        <h1 class="public-page-title">Terms of Service</h1>
        <p class="public-page-sub">Last updated: <?= date('F j, Y') ?></p>
    </div>
</div>

<div class="container px-4 pb-5">
    <div class="card card-smm legal-content p-4 p-md-5">
        <p class="text-muted">By accessing or using <?= SITE_NAME ?> ("the Service"), you agree to be bound by these Terms of Service. Please read them carefully before using our platform.</p>

        <h2>1. Usage Policy</h2>
        <p>You agree to use <?= SITE_NAME ?> only for lawful purposes and in compliance with all applicable laws and the terms of service of third-party platforms (Instagram, YouTube, TikTok, etc.). You must not use our services to:</p>
        <ul>
            <li>Violate any platform's terms of service or community guidelines</li>
            <li>Engage in spam, harassment, or fraudulent activity</li>
            <li>Attempt to gain unauthorized access to our systems or other users' accounts</li>
            <li>Resell or redistribute services in violation of our reseller policies</li>
        </ul>
        <p>We reserve the right to refuse service, suspend accounts, or cancel orders that violate this policy without refund at our discretion.</p>

        <h2>2. Payment Terms</h2>
        <p>All payments are processed securely through Stripe. Funds added to your wallet are non-transferable between accounts. Prices for services are displayed per 1,000 units and may change without notice. You are responsible for ensuring sufficient balance before placing orders.</p>
        <p>By adding funds, you confirm that you are authorized to use the payment method provided. Chargebacks or payment disputes may result in immediate account suspension.</p>

        <h2>3. Refund Policy</h2>
        <p>Refunds are issued to your wallet balance under the following conditions:</p>
        <ul>
            <li><strong>Failed orders:</strong> If an order cannot be completed, your balance is automatically refunded.</li>
            <li><strong>Partial delivery:</strong> Partial refunds may apply for partially completed orders at our discretion.</li>
            <li><strong>Completed orders:</strong> No refunds are issued for successfully delivered services.</li>
            <li><strong>Wallet deposits:</strong> Deposited funds are generally non-refundable once credited. Contact support for exceptional cases.</li>
        </ul>
        <p>To request a refund review, open a support ticket with your order ID within 7 days of the order date.</p>

        <h2>4. Service Delivery</h2>
        <p>Delivery times are estimates and vary by service type. We work with third-party providers to fulfill orders. While we strive for high success rates, we do not guarantee specific delivery speeds, retention rates, or engagement metrics.</p>
        <p>You are responsible for providing accurate links and ensuring target accounts or content are public and accessible. Incorrect links may result in failed orders without refund.</p>

        <h2>5. Account Termination</h2>
        <p>We may suspend or terminate your account at any time if you:</p>
        <ul>
            <li>Violate these Terms of Service or our Usage Policy</li>
            <li>Engage in fraudulent payment activity or chargebacks</li>
            <li>Abuse the support system or harass staff</li>
            <li>Remain inactive for an extended period (at our discretion)</li>
        </ul>
        <p>Upon termination, any remaining wallet balance may be forfeited. You may close your account at any time by contacting support, subject to resolution of any pending orders or disputes.</p>

        <h2>6. Limitation of Liability</h2>
        <p><?= SITE_NAME ?> is provided "as is" without warranties of any kind. We are not liable for any indirect, incidental, or consequential damages arising from use of our services, including account actions taken by third-party platforms.</p>

        <h2>7. Changes to Terms</h2>
        <p>We may update these Terms at any time. Continued use of the Service after changes constitutes acceptance. Material changes will be communicated via the platform or email where possible.</p>

        <p class="mb-0 mt-4 text-muted">Questions about these terms? <a href="<?= SITE_URL ?>/contact.php">Contact us</a>.</p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/public_footer.php'; ?>
