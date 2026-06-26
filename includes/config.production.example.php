<?php
/**
 * SMM Panel - Production configuration example (InfinityFree / shared hosting)
 *
 * Copy values into includes/config.php on your hosting account.
 * Do NOT commit real credentials to GitHub.
 */

define('DB_HOST', 'sqlXXX.infinityfree.com');      // From hosting panel → MySQL Databases
define('DB_NAME', 'if0_XXXXXXX_smm_panel');
define('DB_USER', 'if0_XXXXXXX');
define('DB_PASS', 'YOUR_DB_PASSWORD');
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'SMM Panel');
define('SITE_URL', 'https://yourdomain.infinityfreeapp.com');  // Your live site URL (no trailing slash)

define('STRIPE_SECRET_KEY', 'YOUR_STRIPE_SECRET_KEY');
define('STRIPE_PUBLIC_KEY', 'YOUR_STRIPE_PUBLIC_KEY');
define('STRIPE_WEBHOOK_SECRET', 'YOUR_STRIPE_WEBHOOK_SECRET');
define('STRIPE_CURRENCY', 'usd');

define('SESSION_LIFETIME', 86400);

define('MAIL_ENABLED', true);
define('MAIL_FROM', 'your@gmail.com');
define('MAIL_PASSWORD', 'YOUR_GMAIL_APP_PASSWORD');
define('MAIL_FROM_NAME', 'SMM Panel');
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_ENCRYPTION', 'tls');

date_default_timezone_set('UTC');
error_reporting(E_ALL);
ini_set('display_errors', 0);
