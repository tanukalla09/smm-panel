<?php
/**
 * URL helpers — root-relative asset paths avoid HTTPS mixed-content blocking.
 */

function basePath(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    $path = parse_url(SITE_URL, PHP_URL_PATH);
    $base = ($path && $path !== '/') ? rtrim($path, '/') : '';
    return $base;
}

function assetUrl(string $path): string
{
    $path = ltrim(str_replace('\\', '/', $path), '/');
    $base = basePath();
    return ($base ? $base . '/' : '/') . $path;
}

function siteUrl(): string
{
    if (!defined('SITE_URL')) {
        return '';
    }
    $url = SITE_URL;
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
    if ($isHttps && strpos($url, 'http://') === 0) {
        $url = 'https://' . substr($url, 7);
    }
    return rtrim($url, '/');
}
