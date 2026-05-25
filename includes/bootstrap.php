<?php
declare(strict_types=1);

if (!defined('DA_APP')) {
    define('DA_APP', true);
}

require_once dirname(__DIR__) . '/config.php';

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
    ]);
}

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/products.php';
require_once __DIR__ . '/articles.php';

load_runtime_settings();
