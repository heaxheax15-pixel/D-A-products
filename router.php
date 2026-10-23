<?php
/**
 * Router for PHP's built-in web server (php -S).
 *
 * The built-in server does not read .htaccess, so this front controller
 * enforces the same protections on the offline Windows package:
 *   - never serve .env files or any dotfile
 *   - never serve storage/ (SQLite DB, sessions), includes/, vendor/
 *   - never serve .sql / .sqlite / .log / .md source files
 *   - strip any attempt to reach them via path traversal
 *
 * Everything else is served normally; missing files fall through to the
 * built-in server's own 404 handling.
 *
 * Usage:  php -S localhost:8000 router.php
 */

declare(strict_types=1);

$root = __DIR__;
$uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri  = is_string($uri) ? $uri : '/';
$uri  = rawurldecode($uri);
$uri  = str_replace("\0", '', $uri);

// Block path traversal outright.
if (strpos($uri, '..') !== false) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo '403 Forbidden';
    return true;
}

$path = $root . str_replace('/', DIRECTORY_SEPARATOR, $uri);
$real = realpath($path);

// Anything that escapes the document root is denied.
if ($real !== false) {
    $rootReal = realpath($root);
    if ($rootReal !== false && strpos($real, $rootReal . DIRECTORY_SEPARATOR) !== 0 && $real !== $rootReal) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        echo '403 Forbidden';
        return true;
    }
}

// Deny sensitive directories (first path segment).
$segments = array_values(array_filter(explode('/', $uri), static fn($s) => $s !== ''));
$blockedDirs = ['storage', 'includes', 'vendor', 'node_modules', 'bot'];
if (isset($segments[0]) && in_array(strtolower($segments[0]), $blockedDirs, true)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo '403 Forbidden';
    return true;
}

// Deny sensitive file names / extensions anywhere in the path.
$basename = $segments ? end($segments) : '';
$blockedNames = ['.env', '.env.example', '.env.production.example', '.htaccess', '.gitignore', 'database.sql'];
$blockedExt   = ['sql', 'sqlite', 'sqlite3', 'db', 'log', 'md', 'lock', 'dist', 'ini', 'bak'];

if ($basename !== '') {
    $lowerName = strtolower($basename);
    $ext = strtolower(pathinfo($lowerName, PATHINFO_EXTENSION));

    if (
        strpos($lowerName, '.env') === 0
        || in_array($lowerName, $blockedNames, true)
        || (strlen($basename) > 0 && $basename[0] === '.' && $basename !== '.well-known')
        || in_array($ext, $blockedExt, true)
    ) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        echo '403 Forbidden';
        return true;
    }
}

// Serve existing real files (assets, PHP endpoints, images, ...) as-is.
if ($real !== false && is_file($real)) {
    return false;
}

// Directory request: let PHP handle directory index.
if ($real !== false && is_dir($real)) {
    $index = rtrim($real, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'index.php';
    if (is_file($index)) {
        require $index;
        return true;
    }
    return false;
}

// Not found: fall back to the built-in server's 404.
return false;
