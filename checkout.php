<?php
declare(strict_types=1);

if (extension_loaded('zlib')) {
    ob_start('ob_gzhandler');
} else {
    ob_start();
}

define('DA_APP', true);
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/pwa.php';

header('Vary: Accept-Encoding');
header('X-Robots-Tag: noindex, nofollow');

// Checkout requires a non-empty, in-stock cart.
if (empty(get_cart()) || !empty(validate_cart_inventory())) {
    header('Location: cart.php');
    exit;
}

$cart     = get_cart();
$subtotal = get_cart_subtotal();
$totalQty = get_cart_total_quantity();

$siteUrl = site_origin();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>إتمام الطلب – D&amp;A Product</title>
    <meta name="description" content="أكمل طلبك من D&A Product في خطوة واحدة.">
    <meta name="robots" content="noindex, nofollow">
    <link rel="canonical" href="<?= e($siteUrl) ?>/checkout.php">
    <link rel="icon" type="image/png" href="favicon-32.png">
    <link rel="apple-touch-icon" href="icons/icon-192.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/style.min.css') ?>">
<?php if (defined('VIEW_LAYER') && VIEW_LAYER === 'v4'): ?>
<link rel="stylesheet" href="assets/css/v4.css?v=<?= @filemtime(__DIR__ . '/assets/css/v4.css') ?: time() ?>">
<?php endif; ?>
</head>
<body>
    <a class="skip-link" href="#main-content">تخطي إلى المحتوى</a>
<?php require __DIR__ . '/partials/header.php'; ?>

    <main id="main-content" class="checkout-page">
        <div class="container" style="padding-block: clamp(2rem, 5vw, 4rem); max-width: 960px; margin: 0 auto;">
            <nav class="breadcrumb" aria-label="مسار التنقل" style="margin-bottom: 1.5rem; font-size: .9rem;">
                <a href="index.php">الرئيسية</a> <span aria-hidden="true">/</span>
                <a href="cart.php">سلة التسوق</a> <span aria-hidden="true">/</span>
                <span aria-current="page">إتمام الطلب</span>
            </nav>

            <h1 class="section-title-center" style="margin-bottom: 1.5rem;">إتمام الطلب</h1>

            <div class="checkout-grid">
                <div class="checkout-form-wrapper">
                    <h2 class="checkout-card-title">بيانات التوصيل</h2>
                    <form id="checkoutForm" class="order-form" action="submit-order.php" method="post" enctype="multipart/form-data" novalidate aria-label="إتمام الطلب">
<?php require __DIR__ . '/partials/checkout-fields.php'; ?>
                        <button type="submit" class="btn btn-primary btn-lg btn-block" id="checkoutBtn" style="margin-top: 1.25rem;">تأكيد الطلب</button>
                    </form>
                </div>

                <aside class="checkout-summary">
                    <h2 class="checkout-card-title">ملخّص السلة</h2>
                    <div class="checkout-summary-items">
<?php foreach ($cart as $item):
    $qty  = (int) $item['quantity'];
    $line = round((float) $item['price'] * $qty, 2);
?>
                        <div class="checkout-summary-row">
                            <span><?= e($item['name']) ?> <strong>× <?= $qty ?></strong></span>
                            <span><?= number_format($line, 0) ?> دج</span>
                        </div>
<?php endforeach; ?>
                    </div>
                    <div class="checkout-summary-total">
                        <span>الإجمالي (<?= $totalQty ?> منتج)</span>
                        <strong><?= number_format($subtotal, 0) ?> دج</strong>
                    </div>
                    <a href="cart.php" class="checkout-edit-link">← تعديل السلة</a>
                </aside>
            </div>
        </div>
    </main>

<?php require __DIR__ . '/partials/footer.php'; ?>
<?php require __DIR__ . '/partials/whatsapp.php'; ?>
<?php require __DIR__ . '/partials/scripts.php'; ?>

<script>
(function () {
    'use strict';

    var form = document.getElementById('checkoutForm');
    if (!form) return;
    var btn = document.getElementById('checkoutBtn');
    var bankInfo = document.getElementById('bankInfo');
    var receiptRow = document.getElementById('receiptUploadRow');

    function syncPayment() {
        var checked = form.querySelector('input[name="payment_method"]:checked');
        var isBank = !!(checked && checked.value === 'bank_transfer');
        if (bankInfo) bankInfo.hidden = !isBank;
        if (receiptRow) receiptRow.hidden = !isBank;
    }
    form.querySelectorAll('input[name="payment_method"]').forEach(function (r) {
        r.addEventListener('change', syncPayment);
    });
    syncPayment();

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var phone = document.getElementById('customer_phone');
        if (phone && !phone.checkValidity()) {
            alert('رقم الهاتف غير صالح. استخدم 10 أرقام تبدأ بـ 05 أو 06 أو 07.');
            phone.focus();
            return;
        }
        var original = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'جاري تأكيد الطلب...';

        fetch('submit-order.php', {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data && data.success) {
                var main = document.getElementById('main-content');
                main.innerHTML =
                    '<div class="container" style="max-width:640px;margin:0 auto;padding:clamp(3rem,8vw,6rem) 1rem;text-align:center;">' +
                    '<h1 style="color:var(--amber);font-size:clamp(1.6rem,5vw,2.4rem);margin-bottom:1rem;">تم استلام طلبك بنجاح!</h1>' +
                    '<p style="font-size:1.05rem;margin-bottom:.5rem;">' + (data.order_id ? ('رقم الطلب: <strong>#' + data.order_id + '</strong>') : '') + '</p>' +
                    '<p style="color:var(--deep);margin-bottom:2rem;">' + (data.message || 'سنتواصل معك قريباً لتأكيد الطلب.') + '</p>' +
                    '<a href="index.php" class="btn btn-primary btn-lg">العودة للرئيسية</a>' +
                    '</div>';
                window.scrollTo(0, 0);
            } else {
                alert((data && data.message) || 'حدث خطأ، يرجى المحاولة مرة أخرى.');
                btn.disabled = false;
                btn.textContent = original;
            }
        })
        .catch(function () {
            alert('خطأ في الاتصال. يرجى المحاولة مرة أخرى.');
            btn.disabled = false;
            btn.textContent = original;
        });
    });
})();
</script>
</body>
</html>
