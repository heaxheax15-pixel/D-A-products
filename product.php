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

$slug = trim((string) ($_GET['slug'] ?? ''));
$id   = (int) ($_GET['id'] ?? 0);

$product = null;
if ($slug !== '') {
    $product = get_product_by_slug($slug);
}
if (!$product && $id > 0) {
    $product = get_product_by_id($id);
}

if (!$product) {
    http_response_code(404);
    header('Location: index.php#products');
    exit;
}

$allProducts = get_products(true);
$related = [];
foreach ($allProducts as $p) {
    if ((int) $p['id'] === (int) $product['id']) continue;
    if (($p['category'] ?? '') === ($product['category'] ?? '')) {
        $related[] = $p;
        if (count($related) >= 3) break;
    }
}
if (count($related) < 3) {
    foreach ($allProducts as $p) {
        if ((int) $p['id'] === (int) $product['id']) continue;
        if (in_array($p['id'], array_column($related, 'id'), true)) continue;
        $related[] = $p;
        if (count($related) >= 3) break;
    }
}

$siteUrl = site_origin();

$productImage = product_image_url($product);
$productImageAbs = $productImage;
if (!preg_match('#^https?://#i', $productImageAbs)) {
    $productImageAbs = $siteUrl . '/' . ltrim($productImageAbs, '/');
}
$productUrlAbs = $siteUrl . '/product.php?slug=' . urlencode((string) $product['slug']);
$isAvailable = ((int) ($product['quantity_available'] ?? 0)) > 0;
$ratingValue = 4.9;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($product['name']) ?> – D&amp;A Product</title>
    <meta name="description" content="<?= e(mb_substr((string) $product['description'], 0, 155)) ?>">
    <link rel="canonical" href="<?= e($productUrlAbs) ?>">

    <meta property="og:type" content="product">
    <meta property="og:site_name" content="D&amp;A Product">
    <meta property="og:title" content="<?= e($product['name']) ?> – D&amp;A Product">
    <meta property="og:description" content="<?= e(mb_substr((string) $product['description'], 0, 200)) ?>">
    <meta property="og:url" content="<?= e($productUrlAbs) ?>">
    <meta property="og:image" content="<?= e($productImageAbs) ?>">
    <meta property="og:locale" content="ar_DZ">
    <meta property="product:price:amount" content="<?= e((string) $product['price']) ?>">
    <meta property="product:price:currency" content="DZD">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($product['name']) ?> – D&amp;A Product">
    <meta name="twitter:description" content="<?= e(mb_substr((string) $product['description'], 0, 200)) ?>">
    <meta name="twitter:image" content="<?= e($productImageAbs) ?>">

    <link rel="icon" href="favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/style.min.css') ?>">
<?php if (defined('VIEW_LAYER') && VIEW_LAYER === 'v4'): ?>
<link rel="stylesheet" href="assets/css/v4.css?v=<?= @filemtime(__DIR__ . '/assets/css/v4.css') ?: time() ?>">
<?php endif; ?>

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Product",
      "name": <?= json_encode($product['name'], JSON_UNESCAPED_UNICODE) ?>,
      "image": [<?= json_encode($productImageAbs) ?>],
      "description": <?= json_encode(mb_substr((string) $product['description'], 0, 500), JSON_UNESCAPED_UNICODE) ?>,
      "sku": <?= json_encode('DA-' . $product['id']) ?>,
      "brand": { "@type": "Brand", "name": "D&A Product" },
      "offers": {
        "@type": "Offer",
        "url": <?= json_encode($productUrlAbs) ?>,
        "priceCurrency": "DZD",
        "price": <?= json_encode((string) $product['price']) ?>,
        "availability": <?= json_encode($isAvailable ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock') ?>,
        "itemCondition": "https://schema.org/NewCondition",
        "seller": { "@type": "Organization", "name": "D&A Product" }
      },
      "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": <?= json_encode((string) $ratingValue) ?>,
        "bestRating": "5",
        "reviewCount": "127"
      }
    }
    </script>

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "BreadcrumbList",
      "itemListElement": [
        { "@type": "ListItem", "position": 1, "name": "الرئيسية", "item": <?= json_encode($siteUrl . '/') ?> },
        { "@type": "ListItem", "position": 2, "name": "المنتجات", "item": <?= json_encode($siteUrl . '/index.php#products') ?> },
        { "@type": "ListItem", "position": 3, "name": <?= json_encode($product['name'], JSON_UNESCAPED_UNICODE) ?>, "item": <?= json_encode($productUrlAbs) ?> }
      ]
    }
    </script>
</head>
<body>
    <a class="skip-link" href="#main-content">تخطي إلى المحتوى</a>
    <?php require __DIR__ . '/partials/header.php'; ?>

    <main id="main-content" class="product-page">
        <div class="container" style="padding-block: clamp(2rem, 5vw, 4rem);">
            <nav class="breadcrumb" aria-label="مسار التنقل" style="margin-bottom: 1.5rem; font-size: .9rem;">
                <a href="index.php">الرئيسية</a>
                <span aria-hidden="true"> / </span>
                <a href="index.php#products">المنتجات</a>
                <span aria-hidden="true"> / </span>
                <span aria-current="page"><?= e($product['name']) ?></span>
            </nav>

            <div class="product-detail" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2.5rem; align-items: start;">
                <div class="product-detail-image">
                    <img src="<?= e($productImage) ?>" alt="<?= e($product['name']) ?>" loading="eager" style="width: 100%; border-radius: var(--r-lg, 28px); box-shadow: var(--sh-md);">
                </div>

                <div class="product-detail-info">
                    <h1 style="font-family: var(--font-d); font-size: clamp(1.8rem, 4vw, 2.8rem); line-height: 1.2; margin-bottom: 1rem;"><?= e($product['name']) ?></h1>
                    <p style="color: var(--deep); font-size: 1.05rem; line-height: 1.7; margin-bottom: 1.5rem;"><?= e($product['description']) ?></p>

                    <div class="product-detail-price" style="font-size: 1.8rem; font-weight: 800; color: var(--deep); margin-bottom: 1.5rem;">
                        <?= number_format((float) $product['price'], 0) ?> <span style="font-size: 1rem; font-weight: 600;">دج</span>
                    </div>

                    <div class="product-detail-availability" style="margin-bottom: 1.5rem; font-size: .95rem;">
                        <?php if ($isAvailable): ?>
                            <span style="color: #1e7e34;">✓ متوفر — جاهز للتوصيل</span>
                        <?php else: ?>
                            <span style="color: #b02a37;">✗ غير متوفر حالياً</span>
                        <?php endif; ?>
                    </div>

                    <div class="product-detail-cta" style="display: flex; gap: .75rem; flex-wrap: wrap;">
                        <?php if ($isAvailable): ?>
                        <button type="button"
                                class="btn btn-primary btn-lg add-to-cart-btn"
                                data-product-id="<?= (int) $product['id'] ?>"
                                data-quantity="1">
                            🛒 أضف للسلة
                        </button>
                        <?php endif; ?>
                        <a href="index.php#order" class="btn btn-outline btn-lg">اطلب مباشرة</a>
                        <a href="index.php#products" class="btn btn-outline btn-lg">← كل المنتجات</a>
                    </div>
                </div>
            </div>

            <?php if (!empty($related)): ?>
            <section class="related-products" style="margin-top: 4rem;">
                <h2 style="font-family: var(--font-d); font-size: 1.6rem; margin-bottom: 1.5rem;">منتجات قد تعجبك</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">
                    <?php foreach ($related as $r): ?>
                    <article class="product-card">
                        <a href="product.php?slug=<?= e(urlencode((string) $r['slug'])) ?>" style="display: block;">
                            <img src="<?= e(product_image_url($r)) ?>" alt="<?= e($r['name']) ?>" loading="lazy" style="width: 100%; aspect-ratio: 4/5; object-fit: cover; border-radius: var(--r-md);">
                        </a>
                        <h3 style="margin-top: .75rem; font-size: 1rem;"><a href="product.php?slug=<?= e(urlencode((string) $r['slug'])) ?>" style="color: inherit;"><?= e($r['name']) ?></a></h3>
                        <p style="font-weight: 700; color: var(--deep);"><?= number_format((float) $r['price'], 0) ?> دج</p>
                    </article>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
        </div>
    </main>

    <?php require __DIR__ . '/partials/footer.php'; ?>
    <?php require __DIR__ . '/partials/whatsapp.php'; ?>
    <?php require __DIR__ . '/partials/scripts.php'; ?>
</body>
</html>
