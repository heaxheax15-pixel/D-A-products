<?php
declare(strict_types=1);

define('DA_APP', true);
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/admin-layout.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_require_login();
    $keys = ['bank_name', 'bank_holder', 'bank_iban', 'contact_whatsapp', 'whatsapp_greeting', 'contact_instagram', 'contact_tiktok'];
    foreach ($keys as $k) {
        if (isset($_POST[$k])) {
            save_setting($k, trim((string) $_POST[$k]));
        }
    }
    $message = 'تم حفظ الإعدادات.';
    header('Location: settings.php?msg=' . urlencode($message));
    exit;
}
if (!empty($_GET['msg'])) {
    $message = (string) $_GET['msg'];
}

admin_header('الإعدادات', 'settings');
?>
<?php if ($message): ?><div class="admin-flash"><?= e($message) ?></div><?php endif; ?>
<form method="post" class="admin-form admin-panel">
    <h2>التحويل البنكي</h2>
    <label>اسم البنك <input type="text" name="bank_name" value="<?= e(runtime('BANK_NAME', BANK_NAME)) ?>"></label>
    <label>صاحب الحساب <input type="text" name="bank_holder" value="<?= e(runtime('BANK_ACCOUNT_HOLDER', BANK_ACCOUNT_HOLDER)) ?>"></label>
    <label>IBAN <input type="text" name="bank_iban" dir="ltr" value="<?= e(runtime('BANK_IBAN', BANK_IBAN)) ?>"></label>
    <h2>التواصل</h2>
    <label>واتساب (بدون +) <input type="text" name="contact_whatsapp" dir="ltr" value="<?= e(runtime('CONTACT_WHATSAPP', CONTACT_WHATSAPP)) ?>"></label>
    <label>رسالة واتساب الجاهزة <input type="text" name="whatsapp_greeting" value="<?= e(runtime('WHATSAPP_GREETING', WHATSAPP_GREETING)) ?>"></label>
    <label>إنستغرام <input type="url" name="contact_instagram" value="<?= e(runtime('CONTACT_INSTAGRAM', CONTACT_INSTAGRAM)) ?>"></label>
    <label>تيك توك <input type="url" name="contact_tiktok" value="<?= e(runtime('CONTACT_TIKTOK', CONTACT_TIKTOK)) ?>"></label>
    <p class="admin-hint">مفتاح الأمان يُعدّل من ملف config.php: ADMIN_SECRET_KEY</p>
    <button type="submit" class="btn-admin-primary">حفظ الإعدادات</button>
</form>
<?php admin_footer(); ?>
