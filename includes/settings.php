<?php
/**
 * Simple key-value settings store (MySQL)
 */

require_once __DIR__ . '/db.php';

function ensureSettingsTable(): void
{
    static $ready = false;
    if ($ready) {
        return;
    }

    $db = getDB();
    $db->exec("
        CREATE TABLE IF NOT EXISTS settings (
            setting_key VARCHAR(100) PRIMARY KEY,
            setting_value TEXT NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");
    $ready = true;
}

function getSetting(string $key, ?string $default = null): ?string
{
    ensureSettingsTable();
    $db = getDB();
    $stmt = $db->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
    $stmt->execute([$key]);
    $value = $stmt->fetchColumn();
    return $value !== false ? (string) $value : $default;
}

function setSetting(string $key, string $value): void
{
    ensureSettingsTable();
    $db = getDB();
    $stmt = $db->prepare('
        INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ');
    $stmt->execute([$key, $value]);
}

function formatLastSynced(?string $timestamp): string
{
    if (!$timestamp) {
        return 'Never';
    }
    $ts = strtotime($timestamp);
    if ($ts === false) {
        return 'Never';
    }
    return date('M j, Y g:i A', $ts);
}
