<?php
declare(strict_types=1);

define('DA_APP', true);
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/admin-layout.php';

$stats = ['total' => 0, 'today' => 0, 'pending' => 0, 'revenue' => 0.0];
try {
    $stats['total'] = (int) db()->query('SELECT COUNT(*) FROM orders')->fetchColumn();
    $stats['today'] = (int) db()->query('SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()')->fetchColumn();
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

admin_header('لوحة القيادة', 'dashboard');
?>
<div class="stats-grid">
    <div class="stat-card"><span>إجمالي الطلبات</span><strong><?= $stats['total'] ?></strong></div>
    <div class="stat-card"><span>طلبات اليوم</span><strong><?= $stats['today'] ?></strong></div>
    <div class="stat-card"><span>قيد الانتظار</span><strong><?= $stats['pending'] ?></strong></div>
    <div class="stat-card"><span>إجمالي المبيعات</span><strong><?= number_format($stats['revenue'], 0) ?> ر.س</strong></div>
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
                <td><?= e($o['product_name']) ?> ×<?= (int) $o['quantity'] ?></td>
                <td><?= number_format((float) $o['total_price'], 0) ?> ر.س</td>
                <td><?= e(status_label($o['status'])) ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
<p><a href="orders.php" class="btn-admin-primary">عرض كل الطلبات →</a></p>
<?php admin_footer(); ?>
