<?php
$pageTitle = 'Help Center';
require_once __DIR__ . '/includes/public_header.php';

$faqs = [
    [
        'q' => 'How do I place an order?',
        'a' => 'After creating an account and adding funds to your wallet, go to Services, choose a service, click Order Now, enter your link (profile or post URL), set the quantity, and confirm. Your balance is deducted instantly and the order is sent for processing automatically.',
    ],
    [
        'q' => 'How do I add funds to my account?',
        'a' => 'Navigate to Add Funds in your dashboard. Enter the amount you wish to deposit and pay securely via Stripe. Once payment is confirmed, the funds are credited to your wallet immediately and you can start placing orders.',
    ],
    [
        'q' => 'How long does delivery take?',
        'a' => 'Delivery times vary by service. Most orders begin processing within minutes. Some services deliver gradually over hours or days to appear natural. Check the service description for estimated start and completion times.',
    ],
    [
        'q' => 'What if my order fails?',
        'a' => 'If an order cannot be completed, it will be marked as Failed in your order history. In most cases your balance is automatically refunded. If you believe a refund was not applied, open a support ticket and our team will investigate.',
    ],
    [
        'q' => 'Is my account safe?',
        'a' => 'Yes. We use bcrypt password hashing, secure sessions, and Stripe for payments — we never store card details. Your wallet balance and order history are protected behind login. Always use a strong, unique password.',
    ],
    [
        'q' => 'Can I cancel an order after placing it?',
        'a' => 'Orders are processed automatically once placed. Cancellation may not be possible after processing has started. Contact support as soon as possible if you need assistance — include your order ID for faster help.',
    ],
    [
        'q' => 'What links should I provide when ordering?',
        'a' => 'Provide the full, public URL to the profile, post, video, or page you want to boost. Ensure the account or content is public and the link is correct. Incorrect links may cause delays or failed orders.',
    ],
    [
        'q' => 'How do I contact support?',
        'a' => 'Log in and go to Support → New Ticket to describe your issue. Our team typically responds within 24 hours. You can also use the Contact page for general inquiries or browse your ticket history anytime.',
    ],
];
?>

<div class="public-page-hero">
    <div class="container px-4">
        <span class="section-label">Support</span>
        <h1 class="public-page-title">Help Center</h1>
        <p class="public-page-sub">Find answers to common questions about using <?= SITE_NAME ?>.</p>
    </div>
</div>

<div class="container px-4 pb-5">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card card-smm">
                <div class="card-header"><i class="bi bi-question-circle me-2 text-primary"></i>Frequently Asked Questions</div>
                <div class="card-body p-4">
                    <div class="accordion faq-accordion" id="faqAccordion">
                        <?php foreach ($faqs as $i => $faq): $id = 'faq' . $i; ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $id ?>">
                                    <?= sanitize($faq['q']) ?>
                                </button>
                            </h2>
                            <div id="<?= $id ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted"><?= sanitize($faq['a']) ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-smm p-4 text-center">
                <div class="feature-icon-wrap mx-auto mb-3"><i class="bi bi-headset"></i></div>
                <h5 class="fw-bold mb-2">Still need help?</h5>
                <p class="text-muted small mb-4">Our support team is ready to assist you with orders, payments, and account issues.</p>
                <a href="<?= SITE_URL ?>/user/tickets.php" class="btn btn-accent w-100 mb-2">
                    <i class="bi bi-ticket-perforated me-1"></i>Contact Support
                </a>
                <a href="<?= SITE_URL ?>/contact.php" class="btn btn-ghost w-100 btn-sm">Send a Message</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/public_footer.php'; ?>
