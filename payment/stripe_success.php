<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/stripe.php';
requireLogin();

$sessionId = $_GET['session_id'] ?? '';
$txnId = (int) ($_GET['txn'] ?? 0);

if (!$sessionId || !$txnId) {
    flash('error', 'Invalid payment session.');
    header('Location: ' . SITE_URL . '/user/add_funds.php');
    exit;
}

$db = getDB();
$user = getCurrentUser();

$stmt = $db->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ? AND type = 'deposit'");
$stmt->execute([$txnId, $user['id']]);
$txn = $stmt->fetch();

if (!$txn) {
    flash('error', 'Transaction not found.');
    header('Location: ' . SITE_URL . '/user/add_funds.php');
    exit;
}

if ($txn['status'] === 'completed') {
    flash('success', 'Payment already processed. Balance: ' . formatMoney((float)$user['balance']));
    header('Location: ' . SITE_URL . '/user/dashboard.php');
    exit;
}

$session = stripeGet('checkout/sessions/' . urlencode($sessionId));

if (($session['payment_status'] ?? '') === 'paid') {
    try {
        $db->beginTransaction();

        $db->prepare("UPDATE transactions SET status = 'completed', stripe_payment_id = ? WHERE id = ? AND status = 'pending'")
           ->execute([$session['payment_intent'] ?? $sessionId, $txnId]);

        $db->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')
           ->execute([$txn['amount'], $user['id']]);

        $db->commit();

        require_once __DIR__ . '/../includes/notifications.php';
        $uname = $db->prepare('SELECT username, email, balance FROM users WHERE id = ?');
        $uname->execute([$user['id']]);
        $userRow = $uname->fetch();
        $username = $userRow['username'] ?? 'User';
        $newBalance = (float) ($userRow['balance'] ?? 0);
        notify($user['id'], 'Funds added', 'Your wallet has been topped up with ' . formatMoney((float)$txn['amount']) . ' successfully.', 'success', SITE_URL . '/user/dashboard.php');
        notifyAdmins('Payment received', 'Payment of ' . formatMoney((float)$txn['amount']) . " received from {$username}.", 'success', SITE_URL . '/admin/payments.php');

        try {
            require_once __DIR__ . '/../includes/mailer.php';
            mailFundsAdded($userRow['email'], $username, (float) $txn['amount'], $newBalance);
            mailAdminPaymentReceived($username, $userRow['email'], (float) $txn['amount']);
        } catch (Throwable $e) {
            error_log('Payment email failed: ' . $e->getMessage());
        }

        flash('success', 'Payment successful! ' . formatMoney((float)$txn['amount']) . ' added to your balance.');
    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        flash('error', 'Payment verification failed.');
    }
} else {
    flash('error', 'Payment was not completed.');
}

header('Location: ' . SITE_URL . '/user/dashboard.php');
exit;
