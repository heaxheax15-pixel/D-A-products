<?php
declare(strict_types=1);

define('DA_APP', true);
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/admin-layout.php';

$stats = ['total' => 0, 'today' => 0, 'pending' => 0, 'revenue' => 0.0];
try {
    $stats['total'] = (int) db()->query('SELECT COUNT(*) FROM orders')->fetchColumn();
    $todaySql = db_driver() === 'sqlite'
        ? "SELECT COUNT(*) FROM orders WHERE date(created_at) = date('now')"
        : 'SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()';
    $stats['today'] = (int) db()->query($todaySql)->fetchColumn();
    $stats['pending'] = (int) db()->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
    $stats['revenue'] = (float) db()->query("SELECT COALESCE(SUM(total_price),0) FROM orders WHERE status NOT IN ('cancelled')")->fetchColumn();
} catch (Throwable $e) {
    error_log('[D&A] dashboard: ' . $e->getMessage());
}

$recent = [];
try {
    $recent = db()->query('SELECT * FROM orders ORDER BY created_at DESC LIMIT 8')->fetchAll();
} catch (Throwable $e) {
}

// Get low stock products
$lowStockProducts = get_low_stock_products();

admin_header('لوحة القيادة', 'dashboard');
?>

<?php if (!empty($lowStockProducts)): ?>
<div style="background:#fff3cd;border:1px solid #ffc107;border-radius:8px;padding:16px;margin-bottom:20px;color:#856404;">
    <strong style="display:block;margin-bottom:8px;">⚠️ تنبيه: منتجات بمخزون منخفض</strong>
    <ul style="margin:8px 0;padding-left:20px;">
    <?php foreach ($lowStockProducts as $p): ?>
        <li><strong><?= e($p['name']) ?></strong> — المتوفر: <?= (int) $p['quantity_available'] ?> فقط</li>
    <?php endforeach; ?>
    </ul>
    <a href="products-management.php" style="color:#856404;text-decoration:underline;font-size:0.9rem;">إدارة المنتجات →</a>
</div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card"><span>إجمالي الطلبات</span><strong><?= $stats['total'] ?></strong></div>
    <div class="stat-card"><span>طلبات اليوم</span><strong><?= $stats['today'] ?></strong></div>
    <div class="stat-card"><span>قيد الانتظار</span><strong><?= $stats['pending'] ?></strong></div>
    <div class="stat-card"><span>إجمالي المبيعات</span><strong><?= number_format($stats['revenue'], 0) ?> دج</strong></div>
</div>
<h2 class="admin-subtitle">أحدث الطلبات</h2>
<div class="admin-table-wrap">
    <table class="admin-table">
        <thead><tr><th>#</th><th>الزبون</th><th>المنتج</th><th>المبلغ</th><th>الحالة</th></tr></thead>
        <tbody>
        <?php if (!$recent): ?>
            <tr><td colspan="5">لا توجد طلبات بعد.</td></tr>
        <?php else: foreach ($recent as $o): ?>
            <tr>
                <td><?= (int) $o['id'] ?></td>
                <td><a href="tel:<?= e($o['customer_phone']) ?>"><?= e($o['customer_name']) ?></a></td>
                <td>
                    <?php
                    // Get product names from order_items if available
                    try {
                        $items = db()->prepare('
                            SELECT oi.quantity, p.name
                            FROM order_items oi
                            JOIN products p ON oi.product_id = p.id
                            WHERE oi.order_id = ?
                        ')->execute([(int) $o['id']])->fetchAll();
                        if (!empty($items)) {
                            echo implode(', ', array_map(function($item) {
                                return e($item['name']) . ' ×' . (int) $item['quantity'];
                            }, $items));
                        }
                    } catch (Throwable) {
                        // Fallback if order_items doesn't exist yet (old data)
                        echo 'مشمولات الطلب';
                    }
                    ?>
                </td>
                <td><?= number_format((float) $o['total_price'], 0) ?> دج</td>
                <td><?= e(status_label($o['status'])) ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
<p><a href="orders.php" class="btn-admin-primary">عرض كل الطلبات →</a></p>
<?php admin_footer(); ?>
