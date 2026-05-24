<?php
/**
 * D&A Product – إعدادات التطبيق
 */

if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'config.php') {
    http_response_code(403);
    exit('Forbidden');
}

define('APP_VERSION', '2.0.0');
define('APP_ROOT', __DIR__);
define('UPLOAD_RECEIPTS', APP_ROOT . '/uploads/receipts');
define('UPLOAD_PRODUCTS', APP_ROOT . '/uploads/products');
define('MAX_RECEIPT_BYTES', 3 * 1024 * 1024);

define('DB_HOST', 'localhost');
define('DB_NAME', 'da_honey_shop');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('ADMIN_SECRET_KEY', 'CHANGE_ME_STRONG_KEY_2026');

define('BANK_NAME', 'البنك الأهلي السعودي');
define('BANK_ACCOUNT_HOLDER', 'D&A Product');
define('BANK_IBAN', 'SA00 0000 0000 0000 0000 0000');

define('WHATSAPP_ENABLED', true);
define('WHATSAPP_PHONE_NUMBER_ID', 'YOUR_PHONE_NUMBER_ID');
define('WHATSAPP_ACCESS_TOKEN', 'YOUR_PERMANENT_ACCESS_TOKEN');
define('WHATSAPP_NOTIFY_TO', '966500000000');
define('WHATSAPP_GREETING', 'مرحباً، أريد الاستفسار عن العسل');

define('CONTACT_WHATSAPP', '966500000000');
define('CONTACT_INSTAGRAM', 'https://instagram.com/da_product');
define('CONTACT_TIKTOK', 'https://tiktok.com/@da_product');

define('SITE_NAME', 'D&A Product');
define('SITE_TAGLINE', 'عسل طبيعي 100% – أصالة في كل قطرة');
define('SITE_URL', ''); // مثال: https://example.com
define('OG_IMAGE', 'images/pro4.webp');

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}

function send_whatsapp_notification(string $message): bool
{
    if (!WHATSAPP_ENABLED || WHATSAPP_ACCESS_TOKEN === 'YOUR_PERMANENT_ACCESS_TOKEN') {
        error_log('[D&A] WhatsApp غير مُعدّ.');
        return false;
    }
    $url = 'https://graph.facebook.com/v21.0/' . WHATSAPP_PHONE_NUMBER_ID . '/messages';
    $payload = [
        'messaging_product' => 'whatsapp',
        'to' => preg_replace('/\D/', '', WHATSAPP_NOTIFY_TO),
        'type' => 'text',
        'text' => ['preview_url' => false, 'body' => $message],
    ];
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . WHATSAPP_ACCESS_TOKEN,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 15,
    ]);
    $response = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err || $code < 200 || $code >= 300) {
        error_log('[D&A] WhatsApp: ' . ($err ?: $response));
        return false;
    }
    return true;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function asset(string $path): string
{
    return $path . '?v=' . APP_VERSION;
}

function payment_label(string $method): string
{
    return $method === 'bank_transfer' ? 'تحويل بنكي' : 'الدفع عند الاستلام';
}

function status_label(string $status): string
{
    $labels = [
        'pending' => 'قيد الانتظار',
        'confirmed' => 'مؤكد',
        'delivering' => 'قيد التوصيل',
        'completed' => 'مكتمل',
        'cancelled' => 'ملغي',
    ];
    return $labels[$status] ?? $status;
}

function category_label(string $cat): string
{
    $labels = ['sidr' => 'عسل السدر', 'flowers' => 'عسل الزهور', 'talh' => 'عسل الطلح', 'comb' => 'عسل الشهد'];
    return $labels[$cat] ?? $cat;
}
