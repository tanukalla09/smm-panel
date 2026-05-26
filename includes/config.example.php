<?php
/**
 * SMM Panel - Configuration Example
 *
 * Copy this file to config.php and fill in your values:
 *   cp includes/config.example.php includes/config.php
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'smm_panel');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'SMM Panel');
define('SITE_URL', 'http://localhost/smm-panel');

// Stripe (https://dashboard.stripe.com/apikeys)
define('STRIPE_SECRET_KEY', 'YOUR_STRIPE_SECRET_KEY');
define('STRIPE_PUBLIC_KEY', 'YOUR_STRIPE_PUBLIC_KEY');
define('STRIPE_WEBHOOK_SECRET', 'YOUR_STRIPE_WEBHOOK_SECRET');
define('STRIPE_CURRENCY', 'usd');

// Session
define('SESSION_LIFETIME', 86400); // 24 hours

// Email — Gmail SMTP (https://myaccount.google.com/apppasswords)
define('MAIL_ENABLED', true);
define('MAIL_FROM', 'your@gmail.com');
define('MAIL_PASSWORD', 'YOUR_GMAIL_APP_PASSWORD');
define('MAIL_FROM_NAME', 'SMM Panel');
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_ENCRYPTION', 'tls');

// JustAnotherPanel API key: configure in Admin → Manage Providers
// (not stored in this file — set YOUR_JAP_API_KEY in the provider settings)

// Timezone
date_default_timezone_set('UTC');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
