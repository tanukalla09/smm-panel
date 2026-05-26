<?php
/**
 * Admin panel authentication bootstrap.
 * Loads shared auth helpers from the project root.
 */

require_once __DIR__ . '/../../includes/auth.php';

/**
 * Enforce admin access. Redirects non-admins to the user dashboard
 * and guests to the login page.
 */
function enforceAdminAccess(): void
{
    requireAdmin();
}

/**
 * Get the currently logged-in admin user record, or null if unavailable.
 */
function getAdminUser(): ?array
{
    $user = getCurrentUser();
    if (!$user || ($user['role'] ?? '') !== 'admin') {
        return null;
    }
    return $user;
}
