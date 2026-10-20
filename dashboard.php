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
<?php
// Fetch real order counts for the last 7 days
$chartLabels = [];
$chartData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chartLabels[] = date('D', strtotime("-$i days"));
    try {
        $stmt = db()->prepare("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = ?");
        $stmt->execute([$date]);
        $chartData[] = (int)$stmt->fetchColumn();
    } catch (Throwable $e) {
        $chartData[] = 0;
    }
}
?>

    <div class="admin-panel" style="margin-top: 2rem;">
        <h2 style="font-size: 1.1rem; margin-bottom: 1rem; color: var(--gold);">الطلبات في آخر 7 أيام</h2>
        <canvas id="salesChart" style="max-height: 300px; width: 100%;"></canvas>
    </div>
    <script src="<?= asset('assets/js/vendor/chart.umd.min.js') ?>"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('salesChart');
        if (!ctx) return;
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    label: 'الطلبات',
                    data: <?= json_encode($chartData) ?>,
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: 'rgba(59, 130, 246, 1)',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    });
    </script>
<?php admin_footer(); ?>
