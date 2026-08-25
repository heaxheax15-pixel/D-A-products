<?php
declare(strict_types=1);

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): bool
{
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return is_string($token) && $token !== ''
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/** يوقف طلبات POST الإدارية عند فشل التحقق من CSRF. */
function csrf_require(): void
{
    if (csrf_verify()) {
        return;
    }
    $_SESSION['admin_flash_error'] = 'انتهت صلاحية الجلسة. أعد تحميل الصفحة وحاول مجدداً.';
    $target = 'dashboard.php';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $refererPath = parse_url($referer, PHP_URL_PATH);
    if (is_string($refererPath) && str_starts_with($refererPath, '/') && !str_starts_with($refererPath, '//')) {
        $target = $refererPath;
    }
    header('Location: ' . $target);
    exit;
}

function admin_flash_error(): ?string
{
    if (empty($_SESSION['admin_flash_error'])) {
        return null;
    }
    $msg = (string) $_SESSION['admin_flash_error'];
    unset($_SESSION['admin_flash_error']);
    return $msg;
}
