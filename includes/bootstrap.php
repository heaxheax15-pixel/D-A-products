<?php
declare(strict_types=1);

if (!defined('DA_APP')) {
    define('DA_APP', true);
}

require_once dirname(__DIR__) . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
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
