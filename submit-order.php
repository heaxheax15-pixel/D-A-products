<?php
declare(strict_types=1);

define('DA_APP', true);
require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

/**
 * Send a JSON response and terminate execution.
 *
 * @param int   $code    HTTP status code.
 * @param array $payload Associative array to be JSON‑encoded.
 */
function json_response(int $code, array $payload): void {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Enforce a minimum interval between orders.
 *
 * @param int $interval Seconds to wait between orders.
 */
function enforce_rate_limit(int $interval = 60): void {
    $ip = get_client_ip();
    $wait = order_rate_limit_seconds_remaining($ip, $interval);
    if ($wait > 0) {
        json_response(429, [
            'success' => false,
            'message' => "يرجى الانتظار $wait ثواني قبل تقديم طلب جديد."
        ]);
    }
    if (!empty($_SESSION['last_order_time'])) {
        $elapsed = time() - (int)$_SESSION['last_order_time'];
        if ($elapsed < $interval) {
            $wait = $interval - $elapsed;
            json_response(429, [
                'success' => false,
                'message' => "يرجى الانتظار $wait ثواني قبل تقديم طلب جديد."
            ]);
        }
    }
}

/**
 * Validate order input and return a DTO.
 *
 * @throws RuntimeException on validation failure.
 */
function validate_order_input(array $data): object {
    $name    = trim((string)($data['customer_name'] ?? ''));
    $phone   = normalize_algerian_phone((string)($data['customer_phone'] ?? ''));
    $address = trim((string)($data['customer_address'] ?? ''));
    $notes   = trim((string)($data['notes'] ?? ''));
    $payment = (string)($data['payment_method'] ?? '');

    if ($name === '' || $phone === '' || $address === '') {
        throw new RuntimeException('يرجى تعبئة جميع الحقول المطلوبة.');
    }
    if (!is_valid_algerian_phone($phone)) {
        throw new RuntimeException('رقم الهاتف غير صالح. استخدم 10 أرقام تبدأ بـ 05 أو 06 أو 07 (مثال: 0555123456).');
    }
    if (!in_array($payment, ['bank_transfer', 'cod'], true)) {
        throw new RuntimeException('اختر طريقة دفع صالحة.');
    }

    return (object)[
        'name'    => $name,
        'phone'   => $phone,
        'address' => $address,
        'notes'   => $notes,
        'payment' => $payment,
    ];
}

/**
 * Handle receipt upload for bank‑transfer payments.
 *
 * @return string|null Relative path to the uploaded receipt or null.
 */
function handle_receipt_upload(): ?string {
    if (empty($_FILES['receipt']['name'])) {
        return null;
    }
    $file = $_FILES['receipt'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $finfo   = finfo_open(FILEINFO_MIME_TYPE);
    $mime    = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $allowed, true) || $file['size'] > MAX_RECEIPT_BYTES) {
        return null;
    }
    $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $ext    = $extMap[$mime] ?? 'jpg';
    $fname   = 'receipt_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest   = UPLOAD_RECEIPTS . '/' . $fname;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return 'uploads/receipts/' . $fname;
    }
    return null;
}

// ---------------------------------------------------------------------------
// Request method check
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(405, ['success' => false, 'message' => 'طريقة غير مسموحة']);
}

// ---------------------------------------------------------------------------
// CSRF verification
// ---------------------------------------------------------------------------
if (!csrf_verify()) {
    json_response(403, ['success' => false, 'message' => 'انتهت صلاحية الجلسة. أعد تحميل الصفحة.']);
}

// ---------------------------------------------------------------------------
// Rate limiting (60 seconds between orders)
// ---------------------------------------------------------------------------
enforce_rate_limit(60);

// ---------------------------------------------------------------------------
// Cart validation
// ---------------------------------------------------------------------------
// إذا الزبون اختار منتجاً مباشرة من الفورم، أضفه للسلة تلقائياً
if (empty(get_cart()) && !empty($_POST['product_id'])) {
    $pid = (int) $_POST['product_id'];
    $qty = max(1, min(99, (int) ($_POST['quantity'] ?? 1)));
    add_to_cart($pid, $qty);
}

$cart = get_cart();
if (empty(get_cart())) {
    $pid = 0;

    if (!empty($_POST['product_id'])) {
        $pid = (int) $_POST['product_id'];

    } elseif (!empty($_POST['product_name'])) {
        $stmt = db()->prepare(
            'SELECT id FROM products WHERE name = ? AND is_active = 1 LIMIT 1'
        );
        $stmt->execute([trim($_POST['product_name'])]);
        $row = $stmt->fetch();
        $pid = $row ? (int) $row['id'] : 0;
    }

    if ($pid > 0) {
        $qty = max(1, min(99, (int) ($_POST['quantity'] ?? 1)));
        add_to_cart($pid, $qty);
    }
}
if (empty(get_cart())) {
    json_response(400, ['success' => false, 'message' => 'سلة التسوق فارغة. يرجى إضافة منتجات قبل تقديم الطلب.']);
} 


// ---------------------------------------------------------------------------
// Input validation
// ---------------------------------------------------------------------------
try {
    $input = validate_order_input($_POST);
} catch (RuntimeException $e) {
    json_response(400, ['success' => false, 'message' => $e->getMessage()]);
}

// ---------------------------------------------------------------------------
// Inventory validation
// ---------------------------------------------------------------------------
$inventoryErrors = validate_cart_inventory();
if (!empty($inventoryErrors)) {
    json_response(400, [
        'success' => false,
        'message' => implode(' | ', $inventoryErrors)
    ]);
}

// ---------------------------------------------------------------------------
// Receipt upload (if bank transfer)
// ---------------------------------------------------------------------------
$receiptPath = null;
if ($input->payment === 'bank_transfer') {
    $receiptPath = handle_receipt_upload();
}

// ---------------------------------------------------------------------------
// Database transaction – create order, order items, decrement stock
// ---------------------------------------------------------------------------
$orderId    = null;
$totalPrice = 0.0;
$dbItems    = [];

try {
    $pdo    = db();
    $driver = db_driver();
    $pdo->beginTransaction();

    // 1️⃣ Verify stock & fetch DB prices
    foreach ($cart as $item) {
        $productId = (int)$item['product_id'];
        $quantity  = (int)$item['quantity'];

        $lock = $driver === 'mysql' ? ' FOR UPDATE' : '';
        $stmt = $pdo->prepare(
            'SELECT id, name, price, quantity_available FROM products WHERE id = ?' . $lock
        );
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product) {
            throw new RuntimeException('أحد المنتجات لم يعد متاحاً');
        }
        if ((int)$product['quantity_available'] < $quantity) {
            throw new RuntimeException(sprintf(
                'الكمية المطلوبة من "%s" غير متوفرة. المتوفر: %d فقط',
                $product['name'],
                $product['quantity_available']
            ));
        }

        $unitPrice   = (float)$product['price'];
        $totalPrice += $unitPrice * $quantity;

        $dbItems[] = [
            'product_id' => $productId,
            'quantity'   => $quantity,
            'unit_price' => $unitPrice,
            'name'       => $product['name'],
        ];
    }

    // 2️⃣ Insert order
    $ts = $driver === 'mysql' ? 'NOW()' : 'CURRENT_TIMESTAMP';
    $orderStmt = $pdo->prepare(
        "INSERT INTO orders
            (customer_name, customer_phone, customer_address, total_price,
             payment_method, receipt_path, status, notes, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, {$ts}, {$ts})"
    );
    $orderStmt->execute([
        $input->name,
        $input->phone,
        $input->address,
        $totalPrice,
        $input->payment,
        $receiptPath,
        $input->notes !== '' ? $input->notes : null,
    ]);
    $orderId = (int)$pdo->lastInsertId();

    // 3️⃣ Insert order items, decrement stock, optional audit log
    $itemStmt = $pdo->prepare(
        'INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)'
    );
    $decrStmt = $pdo->prepare(
        'UPDATE products SET quantity_available = quantity_available - ? WHERE id = ?'
    );

    foreach ($dbItems as $itm) {
        $itemStmt->execute([$orderId, $itm['product_id'], $itm['quantity'], $itm['unit_price']]);
        $decrStmt->execute([$itm['quantity'], $itm['product_id']]);

        try {
            $pdo->prepare(
                'INSERT INTO inventory_transactions
                    (product_id, order_id, quantity_change, transaction_type, notes)
                 VALUES (?, ?, ?, ?, ?)'
            )->execute([
                $itm['product_id'],
                $orderId,
                -$itm['quantity'],
                'order',
                sprintf('Order #%d – %d piece(s)', $orderId, $itm['quantity']),
            ]);
        } catch (Throwable) {
            // audit table optional
        }
    }

    $pdo->commit();
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e);

    // RuntimeException = رسالة مقصودة للمستخدم (مخزون، منتج غير متاح...)
    // أي شيء آخر = خطأ نظام، لا نكشفه
    $userMsg = $e instanceof RuntimeException
        ? $e->getMessage()
        : 'حدث خطأ غير متوقع.';

    json_response(500, ['success' => false, 'message' => $userMsg]);
}

// ---------------------------------------------------------------------------
// WhatsApp notification
// ---------------------------------------------------------------------------
$itemList = '';
foreach ($dbItems as $itm) {
    $itemList .= sprintf(
        "\n• %s × %d = %.2f دج",
        $itm['name'],
        $itm['quantity'],
        $itm['unit_price'] * $itm['quantity']
    );
}


$wa = sprintf(
    "🐝 طلب جديد #%d\n\n📝 البيانات:\nالاسم: %s\nالهاتف: %s\nالعنوان: %s\n\n📦 المنتجات:%s\n\n💰 الإجمالي: %.2f دج\n💳 الدفع: %s",
    $orderId,
    $input->name,
    $input->phone,
    $input->address,
    $itemList,
    $totalPrice,
    payment_label($input->payment)
);

if ($input->notes !== '') {
    $wa .= "\n\n📌 ملاحظات:\n" . $input->notes;
}
if ($receiptPath) {
    $wa .= "\n\n✅ تم رفع إيصال التحويل";
}
send_whatsapp_notification($wa);

// ---------------------------------------------------------------------------
// Clear cart & set rate limit
// ---------------------------------------------------------------------------
clear_cart();
$_SESSION['last_order_time'] = time();
record_order_submission(get_client_ip());

json_response(200, [
    'success'   => true,
    'order_id'  => $orderId,
    'message'   => 'شكراً لك! تم استلام طلبك بنجاح وسنتواصل معك قريباً.',
]);