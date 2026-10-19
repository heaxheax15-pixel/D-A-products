<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>D&A Product | عسل طبيعي فاخر – أصالة الطبيعة في كل قطرة</title>
    <?php pwa_head_tags(); ?>
    <meta name="description" content="متجر D&A Product – عسل طبيعي 100% معصور على البارد من مناحل جبلية. توصيل سريع، جودة مضمونة، وطلب مباشر بدون تعقيد.">
    <meta name="keywords" content="عسل طبيعي, عسل سدر, D&A Product, عسل جبلي, مناحل">
    <meta property="og:title" content="D&A Product | عسل طبيعي فاخر">
    <meta property="og:description" content="عسل طبيعي 100% مستخلص بعناية من أفضل المناحل الجبلية.">
    <meta property="og:image" content="<?= e($ogImg) ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ar_DZ">
    <link rel="icon" href="favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    <!-- modern design system -->
    <link rel="stylesheet" href="<?= asset('assets/css/style.min.css') ?>">
<?php if (defined('VIEW_LAYER') && VIEW_LAYER === 'v4'): ?>
<link rel="stylesheet" href="assets/css/v4.css?v=<?= @filemtime(__DIR__ . '/assets/css/v4.css') ?: time() ?>">
<?php endif; ?>
    <style>
        /* أي تجاوزات إضافية يمكن وضعها هنا، لكن التصميم الأساسي في modern.min.css */
    </style>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "LocalBusiness",
      "name": "D&A Product",
      "description": "متجر عسل طبيعي فاخر",
      "image": "<?= e($ogImg) ?>",
      "telephone": "+<?= e($waNum) ?>",
      "address": {"@type": "PostalAddress", "addressCountry": "SA"},
      "priceRange": "$$"
    }
    </script>
</head>
