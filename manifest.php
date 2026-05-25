<?php
declare(strict_types=1);

define('DA_APP', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    $sessionDir = __DIR__ . '/storage/sessions';
    if (is_dir($sessionDir) && is_writable($sessionDir)) {
        session_save_path($sessionDir);
    }
    session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);
}

header('Content-Type: application/manifest+json; charset=utf-8');
header('Cache-Control: no-cache');

$base = app_base();
$prefix = $base !== '' ? $base . '/' : '/';
$start = admin_logged_in() ? $prefix . 'dashboard.php' : $prefix . 'login.php';

echo json_encode([
    'name' => 'D&A Dashboard',
    'short_name' => 'D&A',
    'description' => 'لوحة تحكم متجر D&A Product',
    'start_url' => $start,
    'scope' => $prefix,
    'display' => 'standalone',
    'orientation' => 'portrait-primary',
    'background_color' => '#FFFDF7',
    'theme_color' => '#F5A623',
    'dir' => 'rtl',
    'lang' => 'ar',
    'icons' => [
        [
            'src' => $prefix . 'icons/icon-192.png',
            'sizes' => '192x192',
            'type' => 'image/png',
            'purpose' => 'any maskable',
        ],
        [
            'src' => $prefix . 'icons/icon-512.png',
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'any maskable',
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
