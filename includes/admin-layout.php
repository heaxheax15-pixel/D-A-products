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
    <link rel="stylesheet" href="<?= asset('assets/css/admin.modern.css') ?>">
    <meta name="theme-color" content="#0a0a0a">
    <?php pwa_head_tags(); ?>
</head>
<body class="admin-app">
    <button type="button" class="admin-menu-btn" id="adminMenuBtn" aria-label="القائمة">☰</button>
    <aside class="admin-sidebar" id="adminSidebar">
       <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="40" height="40">
    <!-- ... نفس كود الـ SVG الخاص بك ... -->
     <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">

  <defs>
    <linearGradient id="gold" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#FFE082"/>
      <stop offset="50%" stop-color="#D4A017"/>
      <stop offset="100%" stop-color="#B8860B"/>
    </linearGradient>
  </defs>

  <!-- Honey Drop -->
  <path
    d="M256 40
       C340 130 390 210 390 320
       C390 420 330 472 256 472
       C182 472 122 420 122 320
       C122 210 172 130 256 40 Z"
    fill="none"
    stroke="url(#gold)"
    stroke-width="12"/>

  <!-- Honeycomb -->
  <polygon
    points="256,90 284,106 284,138 256,154 228,138 228,106"
    fill="none"
    stroke="url(#gold)"
    stroke-width="8"/>

  <polygon
    points="256,106 270,114 270,128 256,136 242,128 242,114"
    fill="url(#gold)"/>

  <!-- Jar Neck -->
  <line x1="190" y1="175" x2="322" y2="175"
        stroke="url(#gold)"
        stroke-width="10"
        stroke-linecap="round"/>

  <line x1="200" y1="195" x2="312" y2="195"
        stroke="url(#gold)"
        stroke-width="8"
        stroke-linecap="round"/>

  <!-- Letter D -->
  <path
    d="M175 200
       L175 380
       Q355 380 355 290
       Q355 200 175 200 Z"
    fill="none"
    stroke="url(#gold)"
    stroke-width="14"
    stroke-linejoin="round"/>

  <!-- Letter A inside D -->
  <path
    d="M240 360
       L285 225
       L330 360

       M255 305
       L315 305"
    fill="none"
    stroke="url(#gold)"
    stroke-width="12"
    stroke-linecap="round"
    stroke-linejoin="round"/>

  <!-- Honey Flow -->
  <path
    d="M185 395
       Q256 430 327 395
       Q295 450 256 455
       Q217 450 185 395"
    fill="url(#gold)"/>

  <!-- Drop -->
  <path
    d="M256 455
       C274 480 274 500 256 512
       C238 500 238 480 256 455 Z"
    fill="url(#gold)"/>

</svg>
  </svg>
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
