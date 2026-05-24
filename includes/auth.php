<?php
declare(strict_types=1);

function admin_logged_in(): bool
{
    return !empty($_SESSION['da_admin']);
}

function admin_require_login(): void
{
    if (!admin_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function admin_login(string $key): bool
{
    if (!hash_equals(ADMIN_SECRET_KEY, $key)) {
        return false;
    }
    $_SESSION['da_admin'] = true;
    $_SESSION['da_admin_time'] = time();
    return true;
}

function admin_logout(): void
{
    unset($_SESSION['da_admin'], $_SESSION['da_admin_time']);
}

/** دعم الرابط القديم ?key= */
function admin_try_legacy_key(): void
{
    $key = (string) ($_GET['key'] ?? '');
    if ($key !== '' && hash_equals(ADMIN_SECRET_KEY, $key)) {
        admin_login($key);
    }
}
