<?php
/**
 * Quick health check — delete or restrict access in production.
 */
header('Content-Type: application/json');

$checks = ['php' => true, 'config' => false, 'database' => false, 'tables' => false];

try {
    require_once __DIR__ . '/includes/config.php';
    $checks['config'] = true;

    require_once __DIR__ . '/includes/db.php';
    $db = getDB();
    $checks['database'] = true;

    $count = (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $checks['tables'] = $count >= 0;
    $checks['admin_users'] = $count;
    $checks['services'] = (int) $db->query("SELECT COUNT(*) FROM services WHERE status = 'active'")->fetchColumn();
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'checks' => $checks, 'error' => $e->getMessage()]);
    exit;
}

echo json_encode(['ok' => true, 'checks' => $checks, 'site' => SITE_NAME, 'url' => SITE_URL]);
