<?php
declare(strict_types=1);

define('DA_APP', true);
require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'طريقة غير مسموحة'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!csrf_verify()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'انتهت صلاحية الجلسة. أعد تحميل الصفحة.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$name = trim((string) ($_POST['customer_name'] ?? ''));
$phone = preg_replace('/\s+/', '', trim((string) ($_POST['customer_phone'] ?? '')));
$address = trim((string) ($_POST['customer_address'] ?? ''));
$notes = trim((string) ($_POST['notes'] ?? ''));
$product = trim((string) ($_POST['product_name'] ?? ''));
$quantity = (int) ($_POST['quantity'] ?? 1);
$payment = (string) ($_POST['payment_method'] ?? '');

if ($name === '' || $phone === '' || $address === '' || $product === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'يرجى تعبئة جميع الحقول المطلوبة.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!preg_match('/^(05|5|9665)[0-9]{8,9}$/', $phone)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'رقم الهاتف غير صالح. استخدم صيغة سعودية مثل 05xxxxxxxx.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($quantity < 1 || $quantity > 99) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'الكمية غير صالحة.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!in_array($payment, ['bank_transfer', 'cod'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'اختر طريقة دفع صالحة.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$productRow = get_product_by_name($product);
if (!$productRow) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'المنتج غير متوفر.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$total = round((float) $productRow['price'] * $quantity, 2);
$receiptPath = null;

if ($payment === 'bank_transfer' && !empty($_FILES['receipt']['name'])) {
    $file = $_FILES['receipt'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (in_array($mime, $allowed, true) && $file['size'] <= MAX_RECEIPT_BYTES) {
            $ext = match ($mime) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => 'jpg',
            };
            $fname = 'receipt_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $dest = UPLOAD_RECEIPTS . '/' . $fname;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $receiptPath = 'uploads/receipts/' . $fname;
            }
        }
    }
}

try {
    $stmt = db()->prepare(
        'INSERT INTO orders (customer_name, customer_phone, customer_address, notes, product_name, quantity, total_price, payment_method, receipt_path, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $name, $phone, $address,
        $notes !== '' ? $notes : null,
        $product, $quantity, $total, $payment, $receiptPath, 'pending',
    ]);
} catch (PDOException $ex) {
    error_log('[D&A] order: ' . $ex->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'تعذر حفظ الطلب. حاول لاحقاً.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$wa = sprintf(
    "طلب جديد من %s، رقم الهاتف: %s، العنوان: %s، المنتج: %s، الكمية: %d، الإجمالي: %.2f ر.س. طريقة الدفع: %s.",
    $name, $phone, $address, $product, $quantity, $total, payment_label($payment)
);
if ($notes !== '') {
    $wa .= "\nملاحظات: " . $notes;
}
if ($receiptPath) {
    $wa .= "\nتم رفع إيصال التحويل.";
}
send_whatsapp_notification($wa);

echo json_encode([
    'success' => true,
    'message' => 'شكراً لك! تم استلام طلبك بنجاح وسنتواصل معك قريباً.',
], JSON_UNESCAPED_UNICODE);
