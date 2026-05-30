<?php
declare(strict_types=1);

function load_runtime_settings(): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $loaded = true;
    try {
        $rows = db()->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
        foreach ($rows as $row) {
            $GLOBALS['da_settings'][$row['setting_key']] = $row['setting_value'];
        }
    } catch (Throwable $e) {
        $GLOBALS['da_settings'] = [];
    }
}

function setting(string $key, ?string $default = null): ?string
{
    return $GLOBALS['da_settings'][$key] ?? $default;
}

function runtime(string $constName, string $fallback): string
{
    $map = [
        'BANK_NAME' => 'bank_name',
        'BANK_ACCOUNT_HOLDER' => 'bank_holder',
        'BANK_IBAN' => 'bank_iban',
        'CONTACT_WHATSAPP' => 'contact_whatsapp',
        'WHATSAPP_GREETING' => 'whatsapp_greeting',
        'CONTACT_INSTAGRAM' => 'contact_instagram',
        'CONTACT_TIKTOK' => 'contact_tiktok',
    ];
    if (isset($map[$constName])) {
        $v = setting($map[$constName]);
        if ($v !== null && $v !== '') {
            return $v;
        }
    }
    return defined($constName) ? (string) constant($constName) : $fallback;
}

function save_setting(string $key, string $value): void
{
    $sql = db_driver() === 'sqlite'
        ? 'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
           ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value'
        : 'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
           ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)';
    $stmt = db()->prepare($sql);
    $stmt->execute([$key, $value]);
    $GLOBALS['da_settings'][$key] = $value;
}
