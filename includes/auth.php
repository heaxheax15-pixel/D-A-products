<?php
declare(strict_types=1);

/**
 * Get the hashed admin password from settings table.
 * On first login with plain ADMIN_SECRET_KEY, it will be hashed and stored.
 */
function get_admin_password_hash(): ?string
{
    try {
        $result = db()->query("SELECT setting_value FROM settings WHERE setting_key = 'admin_password_hash'")->fetch();
        return $result ? $result['setting_value'] : null;
    } catch (Throwable) {
        return null;
    }
}

/**
 * Store hashed password in settings table
 */
function set_admin_password_hash(string $hash): void
{
    $sql = db_driver() === 'sqlite'
        ? 'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
           ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value'
        : 'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
           ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)';
    $stmt = db()->prepare($sql);
    $stmt->execute(['admin_password_hash', $hash]);
}

function admin_logged_in(): bool
{
    return !empty($_SESSION['da_admin']) && isset($_SESSION['da_admin_time']);
}

function admin_require_login(): void
{
    if (!admin_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Login function - accepts plain text password and verifies against hash
 * On first ever login, hashes the ADMIN_SECRET_KEY from config and stores it
 */
function admin_login(string $plainTextPassword): bool
{
    // Never bootstrap an administrator account without a configured secret.
    if (!is_string(ADMIN_SECRET_KEY) || trim(ADMIN_SECRET_KEY) === '') {
        error_log('[D&A] ADMIN_SECRET_KEY is missing or empty. Admin login disabled.');
        return false;
    }

    // Try to get stored hash
    $storedHash = get_admin_password_hash();

    // If no hash exists yet, this is first login - hash the ADMIN_SECRET_KEY
    if ($storedHash === null) {
        // Verify against plain key first
        if (!hash_equals(ADMIN_SECRET_KEY, $plainTextPassword)) {
            return false;
        }
        // Hash it and store for future logins
        $newHash = password_hash(ADMIN_SECRET_KEY, PASSWORD_BCRYPT, ['cost' => 12]);
        set_admin_password_hash($newHash);
        $storedHash = $newHash;
    }

    // Verify password against stored hash
    if (!password_verify($plainTextPassword, $storedHash)) {
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

/**
 * Check if admin session has expired (30 minutes timeout)
 * Automatically logs out if inactive
 */
function check_admin_session_timeout(int $timeoutMinutes = 30): void
{
    if (!admin_logged_in()) {
        return;
    }

    $currentTime = time();
    $lastActivityTime = (int) $_SESSION['da_admin_time'];
    $timeoutSeconds = $timeoutMinutes * 60;

    if ($currentTime - $lastActivityTime > $timeoutSeconds) {
        admin_logout();
        $_SESSION['admin_flash_error'] = 'انتهت صلاحية جلستك بسبب عدم النشاط. يرجى تسجيل الدخول مرة أخرى.';
        header('Location: login.php');
        exit;
    }

    // Update last activity time
    $_SESSION['da_admin_time'] = $currentTime;
}
