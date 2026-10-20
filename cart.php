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

$cartItems = get_cart();
$subtotal  = get_cart_subtotal();
$totalQty  = get_cart_total_quantity();

$inventoryErrors = validate_cart_inventory();
$hasStockIssue   = !empty($inventoryErrors);

$siteUrl = site_origin();
$ogImg   = $siteUrl . '/' . OG_IMAGE;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>سلة التسوق – D&amp;A Product</title>
    <meta name="description" content="سلة تسوق D&A Product – راجع منتجاتك وأكمل طلبك بسهولة.">
    <meta name="robots" content="noindex, follow">
    <link rel="canonical" href="<?= e($siteUrl) ?>/cart.php">
    <meta property="og:title" content="سلة التسوق – D&amp;A Product">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ar_DZ">
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

    <main id="main-content" class="cart-page">
        <div class="container" style="padding-block: clamp(2rem, 5vw, 4rem); max-width: 880px; margin: 0 auto;">
            <nav class="breadcrumb" aria-label="مسار التنقل" style="margin-bottom: 1.5rem; font-size: .9rem;">
                <a href="index.php">الرئيسية</a> <span aria-hidden="true">/</span> <span aria-current="page">سلة التسوق</span>
            </nav>

            <h1 class="section-title-center" style="margin-bottom: 1.5rem;">سلة التسوق</h1>

<?php if ($hasStockIssue): ?>
            <div class="cart-stock-notice" role="alert" style="margin-bottom: 1.25rem; padding: 1rem 1.25rem; background: #fff4e5; border: 1px solid #e0a458; border-radius: var(--r-md); color: #7a4a10;">
                <strong>تنبيه:</strong>
                <ul style="margin: .5rem 0 0; padding-inline-start: 1.25rem;">
<?php foreach ($inventoryErrors as $err): ?>
                    <li><?= e($err) ?></li>
<?php endforeach; ?>
                </ul>
            </div>
<?php endif; ?>

<?php if (empty($cartItems)): ?>
            <div class="cart-page-empty" style="text-align: center; padding: 3rem 1rem; background: var(--cream-mid); border-radius: var(--r-lg);">
                <p style="font-size: 1.15rem; margin-bottom: 1.5rem;">سلة التسوق فارغة حالياً.</p>
                <a href="index.php#products" class="btn btn-primary btn-lg">تصفّح منتجاتنا</a>
            </div>
<?php else: ?>
            <div class="cart-page-list">
<?php foreach ($cartItems as $item):
    $pid   = (int) $item['product_id'];
    $qty   = (int) $item['quantity'];
    $price = (float) $item['price'];
    $line  = round($price * $qty, 2);
    $product = get_product_by_id($pid);
    $img = $product ? product_image_url($product) : 'images/pro4.webp';
?>
                <div class="cart-item" data-product-id="<?= $pid ?>">
                    <img src="<?= e($img) ?>" alt="<?= e($item['name']) ?>" class="cart-page-thumb" loading="lazy" width="64" height="64">
                    <div class="cart-item-info">
                        <strong><?= e($item['name']) ?></strong>
                        <span class="cart-item-price"><?= number_format($price, 0) ?> دج × <?= $qty ?></span>
                    </div>
                    <div class="cart-item-qty">
                        <button type="button" data-action="decrement" aria-label="تقليل">−</button>
                        <span aria-live="polite"><?= $qty ?></span>
                        <button type="button" data-action="increment" aria-label="زيادة">+</button>
                    </div>
                    <div class="cart-item-subtotal"><?= number_format($line, 0) ?> دج</div>
                    <button type="button" data-action="remove" aria-label="إزالة" title="إزالة">×</button>
                </div>
<?php endforeach; ?>
            </div>

            <div class="cart-page-summary" style="margin-top: 1.5rem; padding: 1.25rem; background: var(--cream-mid); border-radius: var(--r-md);">
                <div class="cart-drawer-total" style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: .75rem; font-size: 1.05rem;">
                    <span>الإجمالي (<?= $totalQty ?> منتج):</span>
                    <strong id="cartPageTotal" style="color: var(--deep); font-size: 1.2rem;"><?= number_format($subtotal, 0) ?> دج</strong>
                </div>
                <div style="display: flex; gap: .75rem; flex-wrap: wrap;">
                    <a href="index.php#order" class="btn btn-primary btn-lg" style="flex: 1; text-align: center;">متابعة الطلب</a>
                    <button type="button" class="btn btn-outline btn-lg" id="cartPageClear" style="flex: 0 0 auto;">تفريغ السلة</button>
                </div>
            </div>
<?php endif; ?>
        </div>
    </main>

<?php require __DIR__ . '/partials/footer.php'; ?>
<?php require __DIR__ . '/partials/whatsapp.php'; ?>
<?php require __DIR__ . '/partials/scripts.php'; ?>

<script>
(function () {
    'use strict';
    var clearBtn = document.getElementById('cartPageClear');
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            if (!window.confirm('هل تريد تفريغ السلة بالكامل؟')) return;
            callApi('clear', {}).then(function () { window.location.reload(); });
        });
    }

    function callApi(action, data) {
        var fd = new FormData();
        fd.append('csrf_token', window.DA_CSRF || '');
        fd.append('action', action);
        Object.keys(data || {}).forEach(function (k) { fd.append(k, data[k]); });
        return fetch('cart-action.php', {
            method: 'POST', body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin', cache: 'no-store'
        }).then(function (r) { return r.json(); });
    }

    var list = document.querySelector('.cart-page-list');
    if (!list) return;
    list.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-action]');
        if (!btn) return;
        var item = btn.closest('.cart-item');
        if (!item) return;
        var pid = parseInt(item.getAttribute('data-product-id'), 10) || 0;
        if (pid <= 0) return;
        var action = btn.getAttribute('data-action');
        var qtyEl = item.querySelector('.cart-item-qty span');
        var cur = qtyEl ? parseInt(qtyEl.textContent, 10) : 0;

        if (action === 'remove') {
            callApi('remove', { product_id: pid }).then(reloadIfOk);
        } else if (action === 'increment') {
            callApi('update', { product_id: pid, quantity: Math.min(99, cur + 1) }).then(reloadIfOk);
        } else if (action === 'decrement') {
            if (cur <= 1) callApi('remove', { product_id: pid }).then(reloadIfOk);
            else callApi('update', { product_id: pid, quantity: cur - 1 }).then(reloadIfOk);
        }
    });

    function reloadIfOk(res) {
        if (res && res.success) window.location.reload();
        else if (res && res.message) window.alert(res.message);
    }
})();
</script>
</body>
</html>
