<?php
declare(strict_types=1);

// ============================================================================
// FORCE ERROR REPORTING GLOBALLY - MUST BE FIRST
// ============================================================================
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if (!defined('DA_APP')) {
    define('DA_APP', true);
}

require_once dirname(__DIR__) . '/config.php';

// ============================================================================
// DEVELOPMENT/LOCAL DETECTION (checks for local IPs, localhost, and debug flag)
// ============================================================================
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$hostParts = explode(':', $host)[0]; // Remove port if present

function is_local_ip(string $ip): bool {
    return str_starts_with($ip, '10.') ||
           str_starts_with($ip, '192.') ||
           str_starts_with($ip, '172.') ||
           in_array($ip, ['localhost', '127.0.0.1', '::1'], true);
}

define('IS_LOCAL_DEV', is_local_ip($hostParts));

if (!headers_sent()) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
}

// ============================================================================
// SAFE HTTPS ENFORCEMENT (disabled on local/dev networks)
// ============================================================================
function enforce_https(): void
{
    // Immediately return if local/development environment
    $hostParts = explode(':', $_SERVER['HTTP_HOST'] ?? 'localhost')[0];
    if (is_local_ip($hostParts)) {
        return;
    }

    // Only enforce on production domains
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    if (!$isSecure) {
        $url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('Location: ' . $url, true, 301);
        exit;
    }
}

if (session_status() === PHP_SESSION_NONE) {
    $sessionDir = dirname(__DIR__) . '/storage/sessions';
    if (!is_dir($sessionDir)) {
        @mkdir($sessionDir, 0700, true);
    }
    if (is_writable($sessionDir)) {
        session_save_path($sessionDir);
    }
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' => false,
    ]);
}

// ============================================================================
// CALL HTTPS ENFORCEMENT ON LOCAL/SAFE NETWORKS
// ============================================================================
if (!headers_sent()) {
    enforce_https();
}

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/products.php';
require_once __DIR__ . '/articles.php';
require_once __DIR__ . '/auth.php';

load_runtime_settings();

// ============================================================================
// ADMIN SESSION TIMEOUT CHECK (30 minutes)
// ============================================================================
// Check for admin session timeout on relevant pages
$currentScript = basename($_SERVER['SCRIPT_FILENAME'] ?? '');
$adminPages = ['dashboard.php', 'orders.php', 'products-management.php', 'settings.php'];

if (in_array($currentScript, $adminPages, true)) {
    check_admin_session_timeout(30); // 30-minute timeout
}
