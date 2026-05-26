<?php
/**
 * Stripe Webhook Handler
 * Configure webhook URL: https://yoursite.com/payment/stripe_webhook.php
 * Events: checkout.session.completed
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';

$payload = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

if (STRIPE_WEBHOOK_SECRET && strpos(STRIPE_WEBHOOK_SECRET, 'YOUR_') === false && $sigHeader) {
    $parts = explode(',', $sigHeader);
    $timestamp = null;
    $signature = null;
    foreach ($parts as $part) {
        [$k, $v] = array_pad(explode('=', trim($part), 2), 2, null);
        if ($k === 't') $timestamp = $v;
        if ($k === 'v1') $signature = $v;
    }
    $signedPayload = $timestamp . '.' . $payload;
    $expected = hash_hmac('sha256', $signedPayload, STRIPE_WEBHOOK_SECRET);
    if (!hash_equals($expected, $signature ?? '')) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid signature']);
        exit;
    }
}

$event = json_decode($payload, true);
if (!$event || !isset($event['type'])) {
    http_response_code(400);
    exit;
}

if ($event['type'] === 'checkout.session.completed') {
    $session = $event['data']['object'];
    $userId = (int) ($session['metadata']['user_id'] ?? $session['client_reference_id'] ?? 0);
    $amount = (float) ($session['metadata']['amount'] ?? 0);
    $sessionId = $session['id'] ?? '';

    if ($userId && $amount > 0 && ($session['payment_status'] ?? '') === 'paid') {
        $db = getDB();

        $stmt = $db->prepare("SELECT * FROM transactions WHERE stripe_session_id = ? AND status = 'pending'");
        $stmt->execute([$sessionId]);
        $txn = $stmt->fetch();

        if ($txn) {
            $db->beginTransaction();
            $db->prepare("UPDATE transactions SET status = 'completed', stripe_payment_id = ? WHERE id = ?")
               ->execute([$session['payment_intent'] ?? $sessionId, $txn['id']]);
            $db->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')
               ->execute([$txn['amount'], $userId]);
            $db->commit();

            require_once __DIR__ . '/../includes/notifications.php';
            $uname = $db->prepare('SELECT username, email, balance FROM users WHERE id = ?');
            $uname->execute([$userId]);
            $userRow = $uname->fetch();
            $username = $userRow['username'] ?? 'User';
            $newBalance = (float) ($userRow['balance'] ?? 0);
            notify($userId, 'Funds added', 'Your wallet has been topped up with $' . number_format((float)$txn['amount'], 2) . ' successfully.', 'success', SITE_URL . '/user/dashboard.php');
            notifyAdmins('Payment received', 'Payment of $' . number_format((float)$txn['amount'], 2) . " received from {$username}.", 'success', SITE_URL . '/admin/payments.php');

            try {
                require_once __DIR__ . '/../includes/mailer.php';
                if ($userRow) {
                    mailFundsAdded($userRow['email'], $username, (float) $txn['amount'], $newBalance);
                    mailAdminPaymentReceived($username, $userRow['email'], (float) $txn['amount']);
                }
            } catch (Throwable $e) {
                error_log('Webhook payment email failed: ' . $e->getMessage());
            }
        }
    }
}

http_response_code(200);
echo json_encode(['received' => true]);
