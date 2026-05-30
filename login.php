<?php
declare(strict_types=1);

define('DA_APP', true);
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/pwa.php';

admin_try_legacy_key();
if (admin_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $key = trim((string) ($_POST['secret_key'] ?? ''));
    if (admin_login($key)) {
        header('Location: dashboard.php');
        exit;
    }
    $error = 'مفتاح الدخول غير صحيح.';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>دخول لوحة التحكم – D&A</title>
    <link rel="icon" href="<?= e(app_url('favicon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= asset('assets/css/admin.min.css') ?>">
    <?php pwa_head_tags(); ?>
</head>
<body class="login-page">
    <form class="login-card" method="post">
        <div class="login-logo">🐝 D&amp;A</div>
        <h1>لوحة التحكم</h1>
        <p>أدخل مفتاح الأمان للمتابعة</p>
        <?php if ($error): ?><div class="login-error"><?= e($error) ?></div><?php endif; ?>
        <input type="password" name="secret_key" placeholder="مفتاح الأمان" required autofocus autocomplete="current-password">
        <button type="submit" class="btn-admin-primary">دخول</button>
        <button type="button" class="btn-install-pwa login-install" id="installPwaBtn" hidden>📲 تثبيت التطبيق على الجوال</button>
        <a href="index.php" class="login-back">← العودة للمتجر</a>
    </form>
    <script src="<?= asset('assets/js/admin.js') ?>" defer></script>
    <?php pwa_register_script(); ?>
</body>
</html>
