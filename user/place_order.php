<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../api/provider.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . SITE_URL . '/user/services.php');
    exit;
}

$user = getCurrentUser();
$serviceId = (int) ($_POST['service_id'] ?? 0);
$link = trim($_POST['link'] ?? '');
$quantity = (int) ($_POST['quantity'] ?? 0);

$db = getDB();
$stmt = $db->prepare("SELECT * FROM services WHERE id = ? AND status = 'active'");
$stmt->execute([$serviceId]);
$service = $stmt->fetch();

if (!$service) {
    flash('error', 'Service not found.');
    header('Location: ' . SITE_URL . '/user/services.php');
    exit;
}

if ($quantity < $service['min_quantity'] || $quantity > $service['max_quantity']) {
    flash('error', "Quantity must be between {$service['min_quantity']} and {$service['max_quantity']}.");
    header('Location: ' . SITE_URL . '/user/services.php');
    exit;
}

if (!filter_var($link, FILTER_VALIDATE_URL)) {
    flash('error', 'Please enter a valid URL.');
    header('Location: ' . SITE_URL . '/user/services.php');
    exit;
}

$charge = round(($service['rate'] / 1000) * $quantity, 2);

if ((float)$user['balance'] < $charge) {
    flash('error', 'Insufficient balance. Please add funds.');
    header('Location: ' . SITE_URL . '/user/add_funds.php');
    exit;
}

try {
    $db->beginTransaction();

    // Deduct balance
    $stmt = $db->prepare('UPDATE users SET balance = balance - ? WHERE id = ? AND balance >= ?');
    $stmt->execute([$charge, $user['id'], $charge]);
    if ($stmt->rowCount() === 0) {
        throw new Exception('Insufficient balance.');
    }

    // Create order
    $cost = $service['cost_rate'] ? round(($service['cost_rate'] / 1000) * $quantity, 2) : null;
    $profit = $cost !== null ? round($charge - $cost, 2) : null;

    $stmt = $db->prepare("
        INSERT INTO orders (user_id, service_id, link, quantity, charge, cost, profit, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->execute([$user['id'], $serviceId, $link, $quantity, $charge, $cost, $profit]);
    $orderId = (int) $db->lastInsertId();

    // Log transaction
    $stmt = $db->prepare("
        INSERT INTO transactions (user_id, order_id, type, amount, payment_method, status, description)
        VALUES (?, ?, 'order', ?, 'balance', 'completed', ?)
    ");
    $stmt->execute([$user['id'], $orderId, $charge, "Order #{$orderId} — {$service['name']}"]);

    $db->commit();

    try {
        forwardOrderToProvider($orderId);
    } catch (Throwable $e) {
        error_log('Order #' . $orderId . ' forward failed: ' . $e->getMessage());
        recordOrderApiError($orderId, $e->getMessage());
    }

    require_once __DIR__ . '/../includes/notifications.php';
    notify($user['id'], 'Order placed', "Your order #{$orderId} for {$service['name']} has been placed successfully.", 'success', SITE_URL . '/user/orders.php');
    notifyAdmins('New order placed', "New order #{$orderId} placed by {$user['username']} for {$service['name']}.", 'info', SITE_URL . '/admin/orders.php');

    try {
        require_once __DIR__ . '/../includes/mailer.php';
        $orderRow = ['id' => $orderId, 'link' => $link, 'quantity' => $quantity, 'charge' => $charge, 'status' => 'pending'];
        mailOrderPlaced($user['email'], $user['username'], $orderRow, $service['name']);
    } catch (Throwable $e) {
        error_log('Order placed email failed: ' . $e->getMessage());
    }

    flash('success', "Order #{$orderId} placed successfully! Charge: " . formatMoney($charge));

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    flash('error', 'Order failed: ' . $e->getMessage());
}

header('Location: ' . SITE_URL . '/user/orders.php');
exit;
