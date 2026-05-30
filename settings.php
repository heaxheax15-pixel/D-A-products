<?php
declare(strict_types=1);

define('DA_APP', true);
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/admin-layout.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_require_login();
    csrf_require();
    $keys = ['bank_name', 'bank_holder', 'bank_iban', 'contact_whatsapp', 'whatsapp_greeting', 'contact_instagram', 'contact_tiktok'];
    foreach ($keys as $k) {
        if (!isset($_POST[$k])) {
            continue;
        }
        $value = trim((string) $_POST[$k]);
        if ($k === 'contact_whatsapp' && $value !== '' && !is_valid_algerian_phone($value)) {
            $_SESSION['admin_flash_error'] = 'رقم واتساب غير صالح. استخدم 10 أرقام تبدأ بـ 05 أو 06 أو 07.';
            header('Location: settings.php');
            exit;
        }
        save_setting($k, $value);
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
<?php if ($csrfErr = admin_flash_error()): ?><div class="admin-flash admin-flash-error"><?= e($csrfErr) ?></div><?php endif; ?>
<?php if ($message): ?><div class="admin-flash"><?= e($message) ?></div><?php endif; ?>
<form method="post" class="admin-form admin-panel">
    <?= csrf_field() ?>
    <h2>التحويل البنكي</h2>
    <label>اسم البنك <input type="text" name="bank_name" value="<?= e(runtime('BANK_NAME', BANK_NAME)) ?>"></label>
    <label>صاحب الحساب <input type="text" name="bank_holder" value="<?= e(runtime('BANK_ACCOUNT_HOLDER', BANK_ACCOUNT_HOLDER)) ?>"></label>
    <label>IBAN <input type="text" name="bank_iban" dir="ltr" value="<?= e(runtime('BANK_IBAN', BANK_IBAN)) ?>"></label>
    <h2>التواصل</h2>
    <label>واتساب (بدون +) <input type="text" name="contact_whatsapp" dir="ltr" pattern="<?= e(algerian_phone_pattern_html()) ?>" placeholder="0555123456 أو 213555123456" value="<?= e(runtime('CONTACT_WHATSAPP', CONTACT_WHATSAPP)) ?>"></label>
    <label>رسالة واتساب الجاهزة <input type="text" name="whatsapp_greeting" value="<?= e(runtime('WHATSAPP_GREETING', WHATSAPP_GREETING)) ?>"></label>
    <label>إنستغرام <input type="url" name="contact_instagram" value="<?= e(runtime('CONTACT_INSTAGRAM', CONTACT_INSTAGRAM)) ?>"></label>
    <label>تيك توك <input type="url" name="contact_tiktok" value="<?= e(runtime('CONTACT_TIKTOK', CONTACT_TIKTOK)) ?>"></label>
    <p class="admin-hint">مفتاح الأمان يُعدّل من ملف config.php: ADMIN_SECRET_KEY</p>
    <button type="submit" class="btn-admin-primary">حفظ الإعدادات</button>
</form>
<?php admin_footer(); ?>
