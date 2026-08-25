<?php
declare(strict_types=1);

/**
 * Login Rate Limiting
 * Blocks IP after 5 failed attempts within 10 minutes for 15 minutes
 */

/**
 * IPs الخاصة بأي reverse proxy موثوق (Nginx/Apache أمام PHP-FPM).
 * اتركها فارغة إن كان الخادم يستقبل الطلبات مباشرة بلا وسيط.
 */
const TRUSTED_PROXY_IPS = [];

function get_client_ip(): string
{
    $remote = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    if (in_array($remote, TRUSTED_PROXY_IPS, true) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $forwarded = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        if (filter_var($forwarded, FILTER_VALIDATE_IP)) {
            return $forwarded;
        }
    }

    return filter_var($remote, FILTER_VALIDATE_IP) ? $remote : '127.0.0.1';
}

/**
 * Ensure login_attempts table exists
 */
function ensure_login_attempts_table(): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    $pdo = db();
    $driver = db_driver();

    try {
        // Try to query the table
        $pdo->query('SELECT COUNT(*) FROM login_attempts');
    } catch (Throwable) {
        // Table doesn't exist, create it
        if ($driver === 'mysql') {
            $pdo->exec('
                CREATE TABLE IF NOT EXISTS login_attempts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    ip_address VARCHAR(45) NOT NULL,
                    attempts INT NOT NULL DEFAULT 1,
                    last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    blocked_until TIMESTAMP NULL,
                    INDEX idx_ip (ip_address),
                    UNIQUE KEY uk_ip (ip_address)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ');
        } elseif ($driver === 'sqlite') {
            $pdo->exec('
                CREATE TABLE login_attempts (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    ip_address TEXT NOT NULL UNIQUE,
                    attempts INTEGER NOT NULL DEFAULT 1,
                    last_attempt TEXT DEFAULT CURRENT_TIMESTAMP,
                    blocked_until TEXT NULL
                )
            ');
            $pdo->exec('CREATE INDEX idx_login_attempts_ip ON login_attempts(ip_address)');
        }
    }
}

/**
 * Check if IP is currently blocked
 */
function is_login_blocked(string $ip): bool
{
    ensure_login_attempts_table();
    
    try {
        $stmt = db()->prepare('SELECT blocked_until FROM login_attempts WHERE ip_address = ?');
        $stmt->execute([$ip]);
        $row = $stmt->fetch();

        if (!$row) {
            return false;
        }

        $blockedUntil = $row['blocked_until'];
        if ($blockedUntil === null) {
            return false;
        }

        $now = new DateTime();
        $blocked = new DateTime($blockedUntil);

        return $now < $blocked;
    } catch (Throwable) {
        return false;
    }
}

/**
 * Get remaining block time in seconds
 */
function get_block_remaining_seconds(string $ip): int
{
    ensure_login_attempts_table();
    
    try {
        $stmt = db()->prepare('SELECT blocked_until FROM login_attempts WHERE ip_address = ?');
        $stmt->execute([$ip]);
        $row = $stmt->fetch();

        if (!$row || $row['blocked_until'] === null) {
            return 0;
        }

        $now = new DateTime();
        $blocked = new DateTime($row['blocked_until']);

        if ($now >= $blocked) {
            return 0;
        }

        return (int) $blocked->format('U') - (int) $now->format('U');
    } catch (Throwable) {
        return 0;
    }
}

/**
 * Record a failed login attempt
 */
function record_failed_login(string $ip): void
{
    ensure_login_attempts_table();
    
    try {
        $pdo = db();
        $driver = db_driver();

        // Check current attempts
        $stmt = $pdo->prepare('SELECT attempts, last_attempt FROM login_attempts WHERE ip_address = ?');
        $stmt->execute([$ip]);
        $row = $stmt->fetch();

        if (!$row) {
            // First attempt for this IP
            $insertStmt = $pdo->prepare('
                INSERT INTO login_attempts (ip_address, attempts)
                VALUES (?, 1)
            ');
            $insertStmt->execute([$ip]);
        } else {
            $attempts = (int) $row['attempts'] + 1;
            $lastAttempt = new DateTime($row['last_attempt']);
            $now = new DateTime();
            $timeDiff = $now->getTimestamp() - $lastAttempt->getTimestamp();

            // If more than 10 minutes passed, reset attempts
            if ($timeDiff > 600) {
                $attempts = 1;
            }

            // Block after 5 attempts
            $blockedUntil = null;
            if ($attempts >= 5) {
                $blockTime = new DateTime();
                $blockTime->modify('+15 minutes');
                $blockedUntil = $blockTime->format('Y-m-d H:i:s');
            }

            // Update using driver-specific syntax
            if ($driver === 'mysql') {
                $updateStmt = $pdo->prepare('
                    UPDATE login_attempts
                    SET attempts = ?, last_attempt = NOW(), blocked_until = ?
                    WHERE ip_address = ?
                ');
            } else {
                // SQLite
                $updateStmt = $pdo->prepare('
                    UPDATE login_attempts
                    SET attempts = ?, last_attempt = CURRENT_TIMESTAMP, blocked_until = ?
                    WHERE ip_address = ?
                ');
            }
            $updateStmt->execute([$attempts, $blockedUntil, $ip]);
        }
    } catch (Throwable $e) {
        error_log('[D&A] Failed to record login attempt: ' . $e->getMessage());
    }
}

/**
 * Clear failed login attempts for an IP
 */
function clear_login_attempts(string $ip): void
{
    ensure_login_attempts_table();
    
    try {
        $stmt = db()->prepare('DELETE FROM login_attempts WHERE ip_address = ?');
        $stmt->execute([$ip]);
    } catch (Throwable) {
        // Ignore errors
    }
}

/**
 * Clear all old login attempts (older than 24 hours)
 */
function cleanup_old_login_attempts(): void
{
    ensure_login_attempts_table();
    
    try {
        $driver = db_driver();
        if ($driver === 'mysql') {
            db()->exec("DELETE FROM login_attempts WHERE last_attempt < DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        } else {
            db()->exec("DELETE FROM login_attempts WHERE last_attempt < datetime('now', '-24 hours')");
        }
    } catch (Throwable) {
        // Ignore errors
    }
}

function ensure_order_rate_limit_table(): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    $pdo = db();
    $driver = db_driver();

    try {
        $pdo->query('SELECT COUNT(*) FROM order_rate_limits');
    } catch (Throwable) {
        if ($driver === 'mysql') {
            $pdo->exec('
                CREATE TABLE IF NOT EXISTS order_rate_limits (
                    ip_address VARCHAR(45) NOT NULL PRIMARY KEY,
                    last_order_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ');
        } elseif ($driver === 'sqlite') {
            $pdo->exec('
                CREATE TABLE order_rate_limits (
                    ip_address TEXT NOT NULL PRIMARY KEY,
                    last_order_at TEXT DEFAULT CURRENT_TIMESTAMP
                )
            ');
        }
    }
}

function order_rate_limit_seconds_remaining(string $ip, int $interval = 60): int
{
    ensure_order_rate_limit_table();
    try {
        $stmt = db()->prepare('SELECT last_order_at FROM order_rate_limits WHERE ip_address = ?');
        $stmt->execute([$ip]);
        $row = $stmt->fetch();
        if (!$row) {
            return 0;
        }
        $elapsed = time() - (new DateTime($row['last_order_at']))->getTimestamp();
        return max(0, $interval - $elapsed);
    } catch (Throwable) {
        return 0;
    }
}

function record_order_submission(string $ip): void
{
    ensure_order_rate_limit_table();
    $driver = db_driver();
    try {
        $sql = $driver === 'mysql'
            ? 'INSERT INTO order_rate_limits (ip_address, last_order_at) VALUES (?, NOW())
               ON DUPLICATE KEY UPDATE last_order_at = NOW()'
            : 'INSERT INTO order_rate_limits (ip_address, last_order_at) VALUES (?, CURRENT_TIMESTAMP)
               ON CONFLICT(ip_address) DO UPDATE SET last_order_at = CURRENT_TIMESTAMP';
        db()->prepare($sql)->execute([$ip]);
    } catch (Throwable) {
        // silent — لا يجب أن يمنع هذا إتمام الطلب
    }
}
