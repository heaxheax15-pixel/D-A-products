<?php
declare(strict_types=1);

/** نمط HTML5: 10 أرقام محلية تبدأ بـ 05 أو 06 أو 07 */
define('ALGERIAN_PHONE_PATTERN', '0[567][0-9]{8}');

/**
 * يتحقق من رقم جزائري: 0[567]XXXXXXXX (10 أرقام) أو 213[567]XXXXXXXX (دولي).
 */
function is_valid_algerian_phone(string $phone): bool
{
    $phone = normalize_algerian_phone($phone);
    if ($phone === '') {
        return false;
    }
    return (bool) preg_match('/^(0[567][0-9]{8}|213[567][0-9]{8})$/', $phone);
}

/**
 * يزيل المسافات والرموز ويوحّد البادئة الدولية 213 عند الإدخال بصيغة +213.
 */
function normalize_algerian_phone(string $phone): string
{
    $phone = preg_replace('/[\s\-\.\(\)]+/', '', trim($phone)) ?? '';
    if ($phone === '') {
        return '';
    }
    if (str_starts_with($phone, '+')) {
        $phone = substr($phone, 1);
    }
    return $phone;
}

function algerian_phone_pattern_html(): string
{
    return '^(0[567][0-9]{8}|213[567][0-9]{8})$';
}
