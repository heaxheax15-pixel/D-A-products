<?php
declare(strict_types=1);

define('DA_APP', true);
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/pwa.php';

// LOCAL_AUTOFILL: Auto-fill password only on localhost for the client
$__is_local = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost:8000', '127.0.0.1:8000'], true);
$__auto_pass = $__is_local && defined('ADMIN_SECRET_KEY') ? ADMIN_SECRET_KEY : '';


if (admin_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$clientIp = get_client_ip();

// Check if IP is blocked
if (is_login_blocked($clientIp)) {
    $remaining = get_block_remaining_seconds($clientIp);
    $error = sprintf('محاولات دخول كثيرة جداً. حاول بعد %d دقائق', ceil($remaining / 60));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error === '') {
    if (!csrf_verify()) {
        $error = 'انتهت صلاحية الجلسة. أعد تحميل الصفحة وحاول مجدداً.';
    }

    $key = trim((string) ($_POST['secret_key'] ?? ''));

    if ($error === '' && admin_login($key)) {
        clear_login_attempts($clientIp);
        header('Location: dashboard.php');
        exit;
    }
    
    record_failed_login($clientIp);
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
        <?= csrf_field() ?>
        <input type="password" name="secret_key" placeholder="مفتاح الأمان" required autofocus autocomplete="current-password" value="<?= e($__auto_pass) ?>">
        <button type="submit" class="btn-admin-primary">دخول</button>
        <button type="button" class="btn-install-pwa login-install" id="installPwaBtn" hidden>📲 تثبيت التطبيق على الجوال</button>
        <a href="index.php" class="login-back">← العودة للمتجر</a>
    </form>
    <script src="<?= asset('assets/js/admin.js') ?>" defer></script>
    <?php pwa_unregister_script(); ?>
</body>
</html>
