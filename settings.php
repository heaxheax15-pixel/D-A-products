<?php
declare(strict_types=1);

define('DA_APP', true);
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/admin-layout.php';

admin_require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $textKeys = [
        'bank_name'            => 'bank_name',
        'bank_holder'          => 'bank_holder',
        'bank_iban'            => 'bank_iban',
        'contact_whatsapp'     => 'contact_whatsapp',
        'whatsapp_greeting'    => 'whatsapp_greeting',
        'contact_instagram'    => 'contact_instagram',
        'contact_tiktok'       => 'contact_tiktok',
        'delivery_company'     => 'delivery_company',
        'delivery_fee'         => 'delivery_fee',
        'free_delivery_threshold' => 'free_delivery_threshold',
    ];

    foreach ($textKeys as $postKey => $settingKey) {
        if (!isset($_POST[$postKey])) {
            continue;
        }
        $value = trim((string) $_POST[$postKey]);

        if ($postKey === 'contact_whatsapp' && $value !== '' && !is_valid_algerian_phone($value)) {
            $_SESSION['admin_flash_error'] = 'رقم واتساب غير صالح. استخدم 10 أرقام تبدأ بـ 05 أو 06 أو 07.';
            header('Location: settings.php');
            exit;
        }
        if (($postKey === 'delivery_fee' || $postKey === 'free_delivery_threshold')) {
            $value = (string) max(0, (float) $value);
        }
        save_setting($settingKey, $value);
    }

    header('Location: settings.php?msg=' . urlencode('تم حفظ الإعدادات.'));
    exit;
}

$msg = !empty($_GET['msg']) ? (string) $_GET['msg'] : '';

admin_header('الإعدادات', 'settings');
?>
<?php if ($flash = admin_flash_error()): ?><div class="admin-flash admin-flash-error"><?= e($flash) ?></div><?php endif; ?>
<?php if ($msg): ?><div class="admin-flash"><?= e($msg) ?></div><?php endif; ?>

<form method="post" class="admin-form">
    <?= csrf_field() ?>

    <div class="admin-grid-2">
        <div class="admin-panel">
            <h3 class="admin-subtitle">📱 معلومات التواصل</h3>

            <label>رقم الواتساب (بدون + أو مسافات)</label>
            <input type="text" name="contact_whatsapp" dir="ltr" placeholder="0555123456 أو 213555123456"
                   pattern="<?= e(algerian_phone_pattern_html()) ?>"
                   value="<?= e(runtime('CONTACT_WHATSAPP', CONTACT_WHATSAPP)) ?>">
            <p class="admin-hint">يُستخدم في زر التواصل العائم وفي إشعارات الطلبات.</p>

            <label>رسالة واتساب الجاهزة</label>
            <textarea name="whatsapp_greeting" rows="2" placeholder="مرحباً، أريد الاستفسار عن..."><?= e(runtime('WHATSAPP_GREETING', WHATSAPP_GREETING)) ?></textarea>
            <p class="admin-hint">النص الذي يظهر تلقائياً للزبون عند فتح واتساب.</p>

            <label>إنستغرام</label>
            <input type="url" name="contact_instagram" dir="ltr" placeholder="https://instagram.com/..."
                   value="<?= e(runtime('CONTACT_INSTAGRAM', CONTACT_INSTAGRAM)) ?>">

            <label>تيك توك</label>
            <input type="url" name="contact_tiktok" dir="ltr" placeholder="https://tiktok.com/@..."
                   value="<?= e(runtime('CONTACT_TIKTOK', CONTACT_TIKTOK)) ?>">
        </div>

        <div class="admin-panel">
            <h3 class="admin-subtitle">🏦 المعلومات البنكية (CCP)</h3>

            <label>اسم البنك</label>
            <input type="text" name="bank_name" value="<?= e(runtime('BANK_NAME', BANK_NAME)) ?>">

            <label>صاحب الحساب</label>
            <input type="text" name="bank_holder" placeholder="الاسم واللقب"
                   value="<?= e(runtime('BANK_ACCOUNT_HOLDER', BANK_ACCOUNT_HOLDER)) ?>">

            <label>رقم الحساب (CCP / RIP)</label>
            <input type="text" name="bank_iban" dir="ltr" placeholder="007999990123456789"
                   value="<?= e(runtime('BANK_IBAN', BANK_IBAN)) ?>">
        </div>
    </div>

    <div class="admin-panel" style="margin-top:1.5rem;">
        <h3 class="admin-subtitle">🚚 إعدادات التوصيل</h3>
        <div class="admin-grid-2">
            <div>
                <label>شركة التوصيل (اختياري)</label>
                <input type="text" name="delivery_company" placeholder="مثال: Yalidine, ZR Express"
                       value="<?= e(runtime('DELIVERY_COMPANY', '')) ?>">
                <p class="admin-hint">اسم شركة التوصيل التي ستتعامل معها (للاستخدام الداخلي).</p>
            </div>
            <div>
                <label>سعر التوصيل (دج)</label>
                <input type="number" name="delivery_fee" min="0" step="50"
                       value="<?= e(runtime('DELIVERY_FEE', '0')) ?>">
                <p class="admin-hint">يُضاف هذا المبلغ تلقائياً إلى إجمالي الطلب عند الإتمام.</p>
            </div>
            <div>
                <label>حدّ التوصيل المجاني (دج)</label>
                <input type="number" name="free_delivery_threshold" min="0" step="500"
                       value="<?= e(runtime('FREE_DELIVERY_THRESHOLD', '0')) ?>">
                <p class="admin-hint">إذا بلغ المجموع الفرعي هذا الحد يصبح التوصيل مجانياً (0 = تعطيل).</p>
            </div>
        </div>
    </div>

    <p class="admin-hint" style="margin-top:1.5rem;">مفتاح الأمان يُعدّل من ملف config.php: ADMIN_SECRET_KEY</p>
    <div style="margin-top:0.5rem;">
        <button type="submit" class="btn-admin-primary">حفظ جميع الإعدادات</button>
    </div>
</form>

<?php admin_footer(); ?>
