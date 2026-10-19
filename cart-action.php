<?php
declare(strict_types=1);

define('DA_APP', true);
require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

function cart_state_payload(): array
{
    $items = [];
    foreach (get_cart() as $it) {
        $items[] = [
            'product_id' => (int) $it['product_id'],
            'name'       => (string) $it['name'],
            'price'      => (float) $it['price'],
            'quantity'   => (int) $it['quantity'],
            'subtotal'   => round((float) $it['price'] * (int) $it['quantity'], 2),
        ];
    }
    return [
        'items'          => $items,
        'count'          => get_cart_count(),
        'total_quantity' => get_cart_total_quantity(),
        'subtotal'       => get_cart_subtotal(),
        'total'          => get_cart_total(),
    ];
}

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

$action = (string) ($_POST['action'] ?? '');

try {
    switch ($action) {
        case 'get':
            echo json_encode([
                'success' => true,
                'cart'    => cart_state_payload(),
            ], JSON_UNESCAPED_UNICODE);
            exit;

        case 'add':
            $pid = (int) ($_POST['product_id'] ?? 0);
            $qty = max(1, min(99, (int) ($_POST['quantity'] ?? 1)));
            if ($pid <= 0 || !add_to_cart($pid, $qty)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'تعذّر إضافة المنتج'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $msg = 'تمت الإضافة إلى السلة';
            break;

        case 'update':
            $pid = (int) ($_POST['product_id'] ?? 0);
            $qty = max(0, min(99, (int) ($_POST['quantity'] ?? 0)));
            if ($pid <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'معرّف المنتج غير صالح'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            update_cart_quantity($pid, $qty);
            $msg = 'تم تحديث الكمية';
            break;

        case 'remove':
            $pid = (int) ($_POST['product_id'] ?? 0);
            if ($pid <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'معرّف المنتج غير صالح'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            remove_from_cart($pid);
            $msg = 'تمت الإزالة';
            break;

        case 'clear':
            clear_cart();
            $msg = 'تم تفريغ السلة';
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'إجراء غير معروف'], JSON_UNESCAPED_UNICODE);
            exit;
    }

    echo json_encode([
        'success' => true,
        'message' => $msg,
        'cart'    => cart_state_payload(),
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('[D&A cart-action] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'حدث خطأ غير متوقع'], JSON_UNESCAPED_UNICODE);
}
