<?php
declare(strict_types=1);

define('DA_APP', true);
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/admin-layout.php';

$statuses = ['pending', 'confirmed', 'delivering', 'completed', 'cancelled'];
$filter = (string) ($_GET['status'] ?? '');
$search = trim((string) ($_GET['q'] ?? ''));

if (!empty($_GET['export']) && $_GET['export'] === 'csv') {
    admin_require_login();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="orders_' . date('Y-m-d') . '.csv"');
    echo "\xEF\xBB\xBF";
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'الاسم', 'الهاتف', 'العنوان', 'المنتج', 'الكمية', 'الإجمالي', 'الدفع', 'الحالة', 'التاريخ']);
    $sql = 'SELECT * FROM orders ORDER BY created_at DESC';
    foreach (db()->query($sql) as $row) {
        fputcsv($out, [
            $row['id'], $row['customer_name'], $row['customer_phone'], $row['customer_address'],
            $row['product_name'], $row['quantity'], $row['total_price'],
            payment_label($row['payment_method']), status_label($row['status']), $row['created_at'],
        ]);
    }
    fclose($out);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    admin_require_login();
    $orderId = (int) ($_POST['order_id'] ?? 0);
    $newStatus = (string) ($_POST['status'] ?? '');
    if ($orderId > 0 && in_array($newStatus, $statuses, true)) {
        $stmt = db()->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$newStatus, $orderId]);
    }
    header('Location: orders.php?' . http_build_query(array_filter(['status' => $filter, 'q' => $search])));
    exit;
}

$sql = 'SELECT * FROM orders WHERE 1=1';
$params = [];
if ($filter !== '' && in_array($filter, $statuses, true)) {
    $sql .= ' AND status = ?';
    $params[] = $filter;
}
if ($search !== '') {
    $sql .= ' AND customer_phone LIKE ?';
    $params[] = '%' . $search . '%';
}
$sql .= ' ORDER BY created_at DESC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

admin_header('الطلبات', 'orders');
?>
<form class="admin-filters" method="get">
    <select name="status">
        <option value="">كل الحالات</option>
        <?php foreach ($statuses as $s): ?>
            <option value="<?= e($s) ?>" <?= $filter === $s ? 'selected' : '' ?>><?= e(status_label($s)) ?></option>
        <?php endforeach; ?>
    </select>
    <input type="search" name="q" value="<?= e($search) ?>" placeholder="بحث برقم الهاتف">
    <button type="submit" class="btn-admin-secondary">فلترة</button>
    <a href="orders.php?export=csv" class="btn-admin-secondary">تصدير CSV</a>
</form>
<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr><th>#</th><th>الزبون</th><th>الهاتف</th><th>المنتج</th><th>المبلغ</th><th>الدفع</th><th>الحالة</th></tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td data-label="#"><?= (int) $o['id'] ?></td>
                <td data-label="الزبون">
                    <a href="tel:<?= e($o['customer_phone']) ?>"><?= e($o['customer_name']) ?></a>
                    <small><?= e($o['customer_address']) ?></small>
                    <?php if (!empty($o['receipt_path'])): ?>
                        <br><a href="<?= e($o['receipt_path']) ?>" target="_blank" rel="noopener">📎 إيصال</a>
                    <?php endif; ?>
                </td>
                <td data-label="الهاتف"><?= e($o['customer_phone']) ?></td>
                <td data-label="المنتج"><?= e($o['product_name']) ?> ×<?= (int) $o['quantity'] ?></td>
                <td data-label="المبلغ"><?= number_format((float) $o['total_price'], 2) ?> ر.س</td>
                <td data-label="الدفع"><?= e(payment_label($o['payment_method'])) ?></td>
                <td data-label="الحالة">
                    <form method="post">
                        <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>">
                        <input type="hidden" name="update_status" value="1">
                        <select name="status" onchange="this.form.submit()">
                            <?php foreach ($statuses as $s): ?>
                                <option value="<?= e($s) ?>" <?= $o['status'] === $s ? 'selected' : '' ?>><?= e(status_label($s)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                    <time><?= e(date('Y-m-d H:i', strtotime($o['created_at']))) ?></time>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php admin_footer(); ?>
