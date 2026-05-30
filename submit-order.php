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

// ============================================================================
// RATE LIMITING CHECK (60 seconds between orders)
// ============================================================================
if (!empty($_SESSION['last_order_time'])) {
    $timeSinceLastOrder = time() - (int) $_SESSION['last_order_time'];
    if ($timeSinceLastOrder < 60) {
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'message' => 'يرجى الانتظار ' . (60 - $timeSinceLastOrder) . ' ثواني قبل تقديم طلب جديد.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ============================================================================
// INPUT VALIDATION
// ============================================================================
$name = trim((string) ($_POST['customer_name'] ?? ''));
$phone = normalize_algerian_phone((string) ($_POST['customer_phone'] ?? ''));
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

if (!is_valid_algerian_phone($phone)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'رقم الهاتف غير صالح. استخدم 10 أرقام تبدأ بـ 05 أو 06 أو 07 (مثال: 0555123456).'], JSON_UNESCAPED_UNICODE);
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

// ============================================================================
// PRODUCT LOOKUP
// ============================================================================
$productRow = get_product_by_name($product);
if (!$productRow) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'المنتج غير متوفر.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$total = round((float) $productRow['price'] * $quantity, 2);
$receiptPath = null;

// ============================================================================
// RECEIPT UPLOAD (if bank transfer)
// ============================================================================
if ($payment === 'bank_transfer' && !empty($_FILES['receipt']['name'])) {
    $file = $_FILES['receipt'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (in_array($mime, $allowed, true) && $file['size'] <= MAX_RECEIPT_BYTES) {
            $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
            $ext = $extMap[$mime] ?? 'jpg';
            $fname = 'receipt_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $dest = UPLOAD_RECEIPTS . '/' . $fname;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $receiptPath = 'uploads/receipts/' . $fname;
            }
        }
    }
}

// ============================================================================
// DATABASE TRANSACTION - INSERT ORDER & DECREMENT STOCK
// ============================================================================
$orderId = null;
try {
    $pdo = db();

    // Begin transaction
    $pdo->beginTransaction();

    // Check current inventory with row lock
    $checkStmt = $pdo->prepare('SELECT id, quantity_available FROM products WHERE name = ? FOR UPDATE');
    $checkStmt->execute([$product]);
    $productData = $checkStmt->fetch();

    if (!$productData) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'المنتج غير متوفر.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $availableQuantity = (int) $productData['quantity_available'];
    $productId = (int) $productData['id'];

    // Check if enough stock
    if ($availableQuantity < $quantity) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => sprintf(
                'الكمية المطلوبة غير متوفرة. المتوفر حالياً: %d',
                $availableQuantity
            )
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Insert order
    $insertStmt = $pdo->prepare(
        'INSERT INTO orders (customer_name, customer_phone, customer_address, notes, product_name, quantity, total_price, payment_method, receipt_path, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $insertStmt->execute([
        $name, $phone, $address,
        $notes !== '' ? $notes : null,
        $product, $quantity, $total, $payment, $receiptPath, 'pending',
    ]);

    $orderId = (int) $pdo->lastInsertId();

    // Decrement product inventory
    $decrementStmt = $pdo->prepare(
        'UPDATE products SET quantity_available = quantity_available - ? WHERE id = ?'
    );
    $decrementStmt->execute([$quantity, $productId]);

    // Log inventory transaction (optional audit trail)
    try {
        $auditStmt = $pdo->prepare(
            'INSERT INTO inventory_transactions (product_id, order_id, quantity_change, transaction_type, notes)
             VALUES (?, ?, ?, ?, ?)'
        );
        $auditStmt->execute([
            $productId, $orderId, -$quantity, 'order',
            sprintf('Order #%d - Quantity: %d', $orderId, $quantity)
        ]);
    } catch (Throwable) {
        // Audit table may not exist on SQLite, ignore
    }

    // Commit transaction
    $pdo->commit();

} catch (Throwable $ex) {
    // Rollback on any error
    try {
        $pdo->rollBack();
    } catch (Throwable) {
        // Already rolled back or not in transaction
    }

    error_log('[D&A] order transaction: ' . $ex->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'تعذر حفظ الطلب. حاول لاحقاً.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================================================
// SEND NOTIFICATION & SET RATE LIMIT
// ============================================================================
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

// Set rate limit for next order
$_SESSION['last_order_time'] = time();

echo json_encode([
    'success' => true,
    'order_id' => $orderId,
    'message' => 'شكراً لك! تم استلام طلبك بنجاح وسنتواصل معك قريباً.',
], JSON_UNESCAPED_UNICODE);

