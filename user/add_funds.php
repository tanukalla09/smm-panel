<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$user = getCurrentUser();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float) ($_POST['amount'] ?? 0);

    if ($amount < 1 || $amount > 10000) {
        $error = 'Amount must be between $1.00 and $10,000.00';
    } elseif (strpos(STRIPE_SECRET_KEY, 'YOUR_') !== false) {
        $error = 'Stripe is not configured. Please set your API keys in includes/config.php';
    } else {
        require_once __DIR__ . '/../payment/stripe.php';
        require_once __DIR__ . '/../includes/db.php';

        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO transactions (user_id, type, amount, payment_method, status, description)
            VALUES (?, 'deposit', ?, 'stripe', 'pending', ?)
        ");
        $stmt->execute([$user['id'], $amount, "Stripe deposit — \${$amount}"]);
        $txnId = (int) $db->lastInsertId();

        $successUrl = SITE_URL . '/payment/stripe_success.php?session_id={CHECKOUT_SESSION_ID}&txn=' . $txnId;
        $cancelUrl = SITE_URL . '/user/add_funds.php?cancelled=1';

        $session = createCheckoutSession($user['id'], $amount, $successUrl, $cancelUrl);

        if (isset($session['url'])) {
            $db->prepare('UPDATE transactions SET stripe_session_id = ? WHERE id = ?')
               ->execute([$session['id'], $txnId]);
            header('Location: ' . $session['url']);
            exit;
        }

        $error = $session['error']['message'] ?? 'Failed to create payment session.';
        $db->prepare("UPDATE transactions SET status = 'failed' WHERE id = ?")->execute([$txnId]);
    }
}

// Recent deposits
$db = getDB();
$stmt = $db->prepare("
    SELECT * FROM transactions
    WHERE user_id = ? AND type = 'deposit'
    ORDER BY created_at DESC LIMIT 10
");
$stmt->execute([$user['id']]);
$deposits = $stmt->fetchAll();

$pageTitle = 'Add Funds';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid px-4 py-4">
    <div class="page-header">
        <h1>Add Funds</h1>
        <p>Top up your balance securely via Stripe</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger"><?= sanitize($error) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['cancelled'])): ?>
    <div class="alert alert-warning">Payment was cancelled.</div>
    <?php endif; ?>
    <?php if ($msg = flash('success')): ?>
    <div class="alert alert-success"><?= sanitize($msg) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="stat-card text-center mb-4">
                <div class="stat-icon stat-icon-indigo mx-auto"><i class="bi bi-wallet2"></i></div>
                <div class="stat-label">Current Balance</div>
                <div class="stat-value display-6"><?= formatMoney((float)$user['balance']) ?></div>
            </div>

            <div class="payment-form-card">
                <div class="card-header"><i class="bi bi-credit-card me-2"></i>Secure Payment via Stripe</div>
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label">Amount (USD)</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">$</span>
                                <input type="number" name="amount" class="form-control" min="1" max="10000" step="0.01" value="10.00" required>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mb-4">
                            <?php foreach ([5, 10, 25, 50, 100] as $preset): ?>
                            <button type="button" class="preset-btn flex-fill preset-amount" data-amount="<?= $preset ?>">$<?= $preset ?></button>
                            <?php endforeach; ?>
                        </div>
                        <button type="submit" class="btn btn-accent w-100 py-3">
                            <i class="bi bi-lock-fill me-2"></i>Pay with Stripe
                        </button>
                    </form>
                    <div class="d-flex align-items-center gap-2 mt-3 text-muted small">
                        <i class="bi bi-shield-check text-success"></i>
                        <span>256-bit SSL encryption. Funds added instantly after confirmation.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card card-smm">
                <div class="card-header"><i class="bi bi-clock-history me-2 text-primary"></i>Recent Deposits</div>
                <div class="card-body p-0">
                    <?php if (empty($deposits)): ?>
                    <div class="empty-state">
                        <i class="bi bi-credit-card"></i>
                        <p class="mb-0">No deposits yet.</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-smm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($deposits as $dep): ?>
                                <tr>
                                    <td><strong>#<?= $dep['id'] ?></strong></td>
                                    <td><strong><?= formatMoney((float)$dep['amount']) ?></strong></td>
                                    <td><?= sanitize(ucfirst($dep['payment_method'] ?? 'N/A')) ?></td>
                                    <td><span class="badge badge-status badge-status-<?= $dep['status'] === 'completed' ? 'completed' : ($dep['status'] === 'pending' ? 'pending' : 'failed') ?>"><?= ucfirst($dep['status']) ?></span></td>
                                    <td class="text-muted"><?= date('M j, Y H:i', strtotime($dep['created_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
