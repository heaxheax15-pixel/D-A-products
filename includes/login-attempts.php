<?php
declare(strict_types=1);

/**
 * Login Rate Limiting
 * Blocks IP after 5 failed attempts within 10 minutes for 15 minutes
 */

/**
 * Get client IP address safely
 */
function get_client_ip(): string
{
    // Check for IP from shared internet
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    // Check for IP passed from proxy
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    }
    // Check for remote address
    else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    // Validate IP
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return '127.0.0.1';
    }

    return $ip;
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
