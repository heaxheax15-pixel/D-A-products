<?php
declare(strict_types=1);

require_once __DIR__ . '/pwa.php';

function admin_header(string $title, string $active = ''): void
{
    admin_require_login();
    $nav = [
        'dashboard' => ['dashboard.php', 'لوحة القيادة'],
        'orders' => ['orders.php', 'الطلبات'],
        'products' => ['products-management.php', 'المنتجات'],
        'settings' => ['settings.php', 'الإعدادات'],
    ];
    ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= e($title) ?> – D&A Admin</title>
    <link rel="icon" href="<?= e(app_url('favicon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= asset('assets/css/admin.min.css') ?>">
    <?php pwa_head_tags(); ?>
</head>
<body class="admin-app">
    <button type="button" class="admin-menu-btn" id="adminMenuBtn" aria-label="القائمة">☰</button>
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-brand">🐝 D&amp;A <small>Admin</small></div>
        <button type="button" class="btn-install-pwa" id="installPwaBtn" hidden>📲 تثبيت التطبيق</button>
        <nav>
            <?php foreach ($nav as $key => [$url, $label]): ?>
                <a href="<?= e($url) ?>" class="<?= $active === $key ? 'active' : '' ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </nav>
        <a href="logout.php" class="admin-logout">تسجيل الخروج</a>
    </aside>
    <main class="admin-content">
        <header class="admin-topbar"><h1><?= e($title) ?></h1></header>
    <?php
}

function admin_footer(): void
{
    ?>
    </main>
    <script src="<?= asset('assets/js/admin.js') ?>" defer></script>
    <?php pwa_register_script(); ?>
</body>
</html>
    <?php
}
