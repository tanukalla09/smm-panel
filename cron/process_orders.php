<?php
/**
 * Process pending orders and sync statuses from the provider API.
 *
 * CLI:  php cron/process_orders.php
 * Web:  AJAX from admin panel (admin session required)
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/settings.php';
require_once __DIR__ . '/../api/provider.php';

$isWeb = php_sapi_name() !== 'cli';

if ($isWeb) {
    require_once __DIR__ . '/../includes/auth.php';
    header('Content-Type: application/json; charset=utf-8');

    if (!isAdmin()) {
        http_response_code(403);
        echo json_encode([
            'success'        => false,
            'orders_checked' => 0,
            'orders_updated' => 0,
            'message'        => 'Unauthorized. Admin login required.',
        ]);
        exit;
    }
}

try {
    $result = processAllOrders();
    $now = date('Y-m-d H:i:s');
    setSetting('orders_last_synced', $now);
    $result['last_synced'] = $now;
    $result['last_synced_formatted'] = formatLastSynced($now);

    if ($isWeb) {
        echo json_encode($result);
    } else {
        $status = $result['success'] ? 'OK' : 'WARN';
        echo date('Y-m-d H:i:s') . " [{$status}] — Checked: {$result['orders_checked']}, Updated: {$result['orders_updated']}, Forwarded: {$result['orders_forwarded']}\n";
        if (!empty($result['errors'])) {
            foreach ($result['errors'] as $err) {
                echo "  - {$err}\n";
            }
        }
    }
} catch (Throwable $e) {
    error_log('Order sync failed: ' . $e->getMessage());

    if ($isWeb) {
        http_response_code(500);
        echo json_encode([
            'success'        => false,
            'orders_checked' => 0,
            'orders_updated' => 0,
            'message'        => 'Sync failed unexpectedly. Please try again.',
        ]);
    } else {
        echo date('Y-m-d H:i:s') . ' ERROR: ' . $e->getMessage() . "\n";
        exit(1);
    }
}
