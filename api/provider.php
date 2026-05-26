<?php
/**
 * FastXSMM API Provider Integration
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/notifications.php';

class SMMProvider
{
    private string $apiUrl;
    private string $apiKey;

    public function __construct(?array $provider = null)
    {
        if ($provider) {
            $this->apiUrl = rtrim($provider['api_url'], '/');
            $this->apiKey = $provider['api_key'];
        } else {
            $db = getDB();
            $stmt = $db->query("SELECT * FROM providers WHERE status = 'active' LIMIT 1");
            $row = $stmt->fetch();
            if (!$row) {
                throw new RuntimeException('No active provider configured.');
            }
            $this->apiUrl = rtrim($row['api_url'], '/');
            $this->apiKey = $row['api_key'];
        }
    }

    /**
     * Send request to provider API
     */
    private function request(array $params): array
    {
        $params['key'] = $this->apiKey;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $this->apiUrl,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['error' => 'Connection failed: ' . $error];
        }

        $data = json_decode($response, true);
        if ($data === null) {
            return ['error' => 'Invalid API response', 'raw' => $response, 'http_code' => $httpCode];
        }

        return $data;
    }

    /** Get account balance from provider */
    public function getBalance(): array
    {
        return $this->request(['action' => 'balance']);
    }

    /** Get all services from provider */
    public function getServices(): array
    {
        return $this->request(['action' => 'services']);
    }

    /** Place a new order */
    public function addOrder(string $serviceId, string $link, int $quantity): array
    {
        return $this->request([
            'action'   => 'add',
            'service'  => $serviceId,
            'link'     => $link,
            'quantity' => $quantity,
        ]);
    }

    /** Check order status */
    public function getOrderStatus(string $orderId): array
    {
        return $this->request([
            'action' => 'status',
            'order'  => $orderId,
        ]);
    }

    /** Cancel an order */
    public function cancelOrder(string $orderId): array
    {
        return $this->request([
            'action' => 'cancel',
            'order'  => $orderId,
        ]);
    }

    /** Refill an order */
    public function refillOrder(string $orderId): array
    {
        return $this->request([
            'action' => 'refill',
            'order'  => $orderId,
        ]);
    }
}

function refundFailedOrder(int $orderId): void
{
    $db = getDB();
    $stmt = $db->prepare("SELECT o.*, s.name AS service_name FROM orders o JOIN services s ON o.service_id = s.id WHERE o.id = ? AND o.status = 'failed'");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    if (!$order) {
        return;
    }

    $check = $db->prepare("SELECT id FROM transactions WHERE order_id = ? AND type = 'refund'");
    $check->execute([$orderId]);
    if ($check->fetch()) {
        return;
    }

    $db->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')->execute([$order['charge'], $order['user_id']]);
    $db->prepare("INSERT INTO transactions (user_id, order_id, type, amount, payment_method, status, description) VALUES (?, ?, 'refund', ?, 'balance', 'completed', ?)")
       ->execute([$order['user_id'], $orderId, $order['charge'], "Refund for failed order #{$orderId}"]);
    notify((int) $order['user_id'], 'Order failed', "Your order #{$orderId} could not be completed. Your balance has been refunded.", 'error', SITE_URL . '/user/orders.php');

    try {
        require_once __DIR__ . '/../includes/mailer.php';
        $userStmt = $db->prepare('SELECT email, username FROM users WHERE id = ?');
        $userStmt->execute([$order['user_id']]);
        $userRow = $userStmt->fetch();
        if ($userRow) {
            mailOrderFailed($userRow['email'], $userRow['username'], $order, $order['service_name'], true);
        }
    } catch (Throwable $e) {
        error_log('Order failed email failed: ' . $e->getMessage());
    }
}

/**
 * Record a provider/API error while keeping the user-facing status as pending.
 */
function recordOrderApiError(int $orderId, string $error): void
{
    $db = getDB();
    $db->prepare("UPDATE orders SET status = 'pending', error_message = ? WHERE id = ?")
       ->execute([$error, $orderId]);

    notifyAdmins(
        'Order API error',
        "Order #{$orderId} could not be forwarded to provider: {$error}",
        'warning',
        SITE_URL . '/admin/orders.php'
    );
}

function notifyOrderCompleted(int $orderId): void
{
    $db = getDB();
    $stmt = $db->prepare("SELECT o.*, s.category, s.name AS service_name, u.email, u.username FROM orders o JOIN services s ON o.service_id = s.id JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    if (!$order) return;
    notify((int)$order['user_id'], 'Order completed', "Your order #{$orderId} has been completed! Check your {$order['category']} now.", 'success', SITE_URL . '/user/orders.php');

    try {
        require_once __DIR__ . '/../includes/mailer.php';
        mailOrderCompleted($order['email'], $order['username'], $order, $order['service_name']);
    } catch (Throwable $e) {
        error_log('Order completed email failed: ' . $e->getMessage());
    }
}

/**
 * Forward a pending order to the provider and update DB
 */
function forwardOrderToProvider(int $orderId): bool
{
    $db = getDB();

    $stmt = $db->prepare("
        SELECT o.*, s.provider_service_id, s.cost_rate, p.id AS pid, p.api_url, p.api_key, p.status AS provider_status
        FROM orders o
        JOIN services s ON o.service_id = s.id
        LEFT JOIN providers p ON s.provider_id = p.id
        WHERE o.id = ? AND o.status = 'pending'
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if (!$order || !$order['provider_service_id'] || $order['provider_status'] !== 'active') {
        recordOrderApiError($orderId, 'Provider not configured or service has no provider mapping.');
        return false;
    }

    try {
        $provider = new SMMProvider([
            'api_url' => $order['api_url'],
            'api_key' => $order['api_key'],
        ]);

        $result = $provider->addOrder(
            $order['provider_service_id'],
            $order['link'],
            (int) $order['quantity']
        );

        if (isset($result['order'])) {
            $cost = $order['cost_rate']
                ? round(($order['cost_rate'] / 1000) * $order['quantity'], 2)
                : null;
            $profit = $cost !== null ? round($order['charge'] - $cost, 2) : null;

            $db->prepare("
                UPDATE orders SET
                    provider_order_id = ?,
                    status = 'processing',
                    cost = ?,
                    profit = ?,
                    error_message = NULL
                WHERE id = ?
            ")->execute([$result['order'], $cost, $profit, $orderId]);

            return true;
        }

        $error = $result['error'] ?? json_encode($result);
        recordOrderApiError($orderId, $error);
        return false;

    } catch (Exception $e) {
        recordOrderApiError($orderId, $e->getMessage());
        return false;
    }
}

/**
 * Sync order statuses from provider for active orders
 * @return array{checked: int, updated: int, errors: string[]}
 */
function syncOrderStatuses(): array
{
    $db = getDB();
    $stmt = $db->query("
        SELECT o.id, o.provider_order_id, o.status AS old_status, p.api_url, p.api_key
        FROM orders o
        JOIN services s ON o.service_id = s.id
        JOIN providers p ON s.provider_id = p.id
        WHERE o.provider_order_id IS NOT NULL
          AND o.status IN ('pending', 'processing', 'in_progress', 'partial')
        ORDER BY o.id ASC
        LIMIT 100
    ");
    $orders = $stmt->fetchAll();
    $checked = count($orders);
    $updated = 0;
    $errors = [];

    foreach ($orders as $order) {
        try {
            $provider = new SMMProvider([
                'api_url' => $order['api_url'],
                'api_key' => $order['api_key'],
            ]);
            $status = $provider->getOrderStatus($order['provider_order_id']);

            if (!isset($status['status'])) {
                continue;
            }

            $map = [
                'Pending'     => 'processing',
                'In progress' => 'in_progress',
                'Completed'   => 'completed',
                'Partial'     => 'partial',
                'Canceled'    => 'cancelled',
                'Cancelled'   => 'cancelled',
            ];

            $newStatus = $map[$status['status']] ?? 'processing';
            $startCount = (int) ($status['start_count'] ?? 0);
            $remains = (int) ($status['remains'] ?? 0);
            $oldStatus = $order['old_status'];

            $db->prepare('UPDATE orders SET status = ?, start_count = ?, remains = ? WHERE id = ?')
               ->execute([$newStatus, $startCount, $remains, $order['id']]);

            if ($newStatus !== $oldStatus) {
                $updated++;
                if ($newStatus === 'completed') {
                    notifyOrderCompleted((int) $order['id']);
                }
            }
        } catch (Exception $e) {
            $errors[] = 'Order #' . $order['id'] . ': ' . $e->getMessage();
            continue;
        }
    }

    return ['checked' => $checked, 'updated' => $updated, 'errors' => $errors];
}

/**
 * Forward pending orders and sync statuses from the provider API.
 * @return array{success: bool, orders_checked: int, orders_updated: int, orders_forwarded: int, message: string, errors: string[]}
 */
function processAllOrders(): array
{
    $db = getDB();
    $errors = [];

    $stmt = $db->query("SELECT id FROM orders WHERE status = 'pending' ORDER BY id ASC LIMIT 50");
    $pending = $stmt->fetchAll();
    $forwardChecked = count($pending);
    $forwarded = 0;

    foreach ($pending as $row) {
        try {
            if (forwardOrderToProvider((int) $row['id'])) {
                $forwarded++;
            }
        } catch (Exception $e) {
            $errors[] = 'Forward order #' . $row['id'] . ': ' . $e->getMessage();
        }
    }

    $sync = syncOrderStatuses();
    $errors = array_merge($errors, $sync['errors']);

    $ordersChecked = $forwardChecked + $sync['checked'];
    $ordersUpdated = $forwarded + $sync['updated'];

    if (!empty($errors) && $ordersUpdated === 0 && $ordersChecked === 0) {
        return [
            'success'          => false,
            'orders_checked'   => 0,
            'orders_updated'   => 0,
            'orders_forwarded' => 0,
            'message'          => 'Sync failed — provider API may be unavailable. Please try again later.',
            'errors'           => $errors,
        ];
    }

    $message = 'Orders synced successfully!';
    if ($ordersUpdated > 0) {
        $message .= " {$ordersUpdated} order(s) updated.";
    } else {
        $message .= ' All orders are up to date.';
    }

    if (!empty($errors)) {
        $message .= ' Some orders could not be synced.';
    }

    return [
        'success'          => true,
        'orders_checked'   => $ordersChecked,
        'orders_updated'   => $ordersUpdated,
        'orders_forwarded' => $forwarded,
        'message'          => $message,
        'errors'           => $errors,
    ];
}
