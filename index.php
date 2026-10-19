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

$products = get_products();
$featured = get_featured_product();
$articles = get_articles();
$waNum = runtime('CONTACT_WHATSAPP', CONTACT_WHATSAPP);
$waMsg = urlencode(runtime('WHATSAPP_GREETING', WHATSAPP_GREETING));
$bankName = runtime('BANK_NAME', BANK_NAME);
$bankHolder = runtime('BANK_ACCOUNT_HOLDER', BANK_ACCOUNT_HOLDER);
$bankIban = runtime('BANK_IBAN', BANK_IBAN);
$ig = runtime('CONTACT_INSTAGRAM', CONTACT_INSTAGRAM);
$tiktok = runtime('CONTACT_TIKTOK', CONTACT_TIKTOK);
$siteUrl = SITE_URL ?: ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
$ogImg = $siteUrl . '/' . OG_IMAGE;
?>
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
<body>
    <a class="skip-link" href="#main-content">تخطي إلى المحتوى</a>
    <!-- ========== HEADER ========== -->
    <header class="site-header" id="siteHeader">
        <div class="container header-inner">
            <a href="#top" class="logo">
  <!-- الشعار الفني -->
  <img src="<?= asset('images/logo.svg') ?>" alt="D&A Product" width="60" height="60">
  <!-- الاسم الكتابي يبقى ظاهراً -->
  <span class="logo-text">D&amp;A Product</span>
</a>
                
            <button class="nav-toggle" type="button" aria-label="القائمة" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
            <nav class="main-nav" id="mainNav" aria-label="القائمة الرئيسية">
                <a href="#why">لماذا نحن</a>
                <a href="#quality">الجودة</a>
                <a href="#about">عن العسل</a>
                <a href="#journey">رحلة العسل</a>
                <a href="#products">منتجاتنا</a>
                <a href="#featured">عرض الشهر</a>
                <a href="#testimonials">آراء العملاء</a>
                <a href="#gallery">المعرض</a>
                <a href="#tips">نصائح</a>
                <a href="#order" class="nav-cta">اطلب الآن</a>
                <button id="installAppBtn" hidden style="background:var(--primary);color:#fff;border:none;border-radius:60px;padding:10px 20px;cursor:pointer;font-family:inherit;font-weight:600;">📲 تثبيت التطبيق</button>
            </nav>
        </div>
    </header>

    <main id="main-content">
<?php require __DIR__ . "/sections/hero.php"; ?>

<?php require __DIR__ . "/sections/why.php"; ?>

<?php require __DIR__ . "/sections/quality.php"; ?>

<?php require __DIR__ . "/sections/about.php"; ?>

<?php require __DIR__ . "/sections/journey.php"; ?>

<?php require __DIR__ . "/sections/team.php"; ?>

<?php require __DIR__ . "/sections/story.php"; ?>

    <!-- ========== FEATURED PRODUCT ========== -->
    <?php if ($featured): ?>
<?php require __DIR__ . "/sections/featured.php"; ?>
    <?php endif; ?>

<?php require __DIR__ . "/sections/products.php"; ?>

<?php require __DIR__ . "/sections/testimonials.php"; ?>

<?php require __DIR__ . "/sections/gallery.php"; ?>

<?php require __DIR__ . "/sections/tips.php"; ?>

<?php require __DIR__ . "/sections/order.php"; ?>

    <!-- ========== FOOTER ========== -->
    </main>
    <footer class="site-footer" id="footer">
        <div class="container footer-grid">
            <div class="footer-col">
                <span class="logo-text">D&amp;A Product</span>
                <p><?= e(SITE_TAGLINE) ?></p>
            </div>
            <div class="footer-col">
                <h4>روابط سريعة</h4>
                <a href="#products">منتجاتنا</a>
                <a href="#story">قصتنا</a>
                <a href="#order">اطلب الآن</a>
                <a href="#tips">نصائح العسل</a>
                <a href="privacy-policy.php">سياسة الخصوصية والشروط</a>
            </div>
            <div class="footer-col">
                <h4>تواصل معنا</h4>
                <a href="https://wa.me/<?= e($waNum) ?>?text=<?= $waMsg ?>" target="_blank" rel="noopener">واتساب</a>
                <a href="<?= e($ig) ?>" target="_blank" rel="noopener">إنستغرام</a>
                <a href="<?= e($tiktok) ?>" target="_blank" rel="noopener">تيك توك</a>
            </div>
            <div class="footer-col">
                <h4>النشرة البريدية</h4>
                <form id="newsletterForm" class="newsletter-form" aria-label="النشرة البريدية">
                    <input type="email" name="email" placeholder="بريدك الإلكتروني" required>
                    <button type="submit">اشترك</button>
                </form>
            </div>
        </div>
        <p class="footer-copy">&copy; <?= date('Y') ?> D&amp;A Product</p>
    </footer>

    <!-- ========== FLOATING WHATSAPP ========== -->
    <a href="https://wa.me/<?= e($waNum) ?>?text=<?= $waMsg ?>" class="whatsapp-float" target="_blank" rel="noopener" aria-label="واتساب">
        <svg viewBox="0 0 24 24" width="28" fill="#fff"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
    </a>

    <!-- ========== MODALS ========== -->
    <div class="modal" id="productModal" hidden>
        <div class="modal-backdrop" data-close></div>
        <div class="modal-box">
            <button type="button" class="modal-close" data-close>&times;</button>
            <img id="modalImg" src="" alt="">
            <h3 id="modalTitle"></h3>
            <p id="modalDesc"></p>
            <p class="modal-price" id="modalPrice"></p>
            <button type="button" class="btn btn-primary" id="modalOrder">اطلب هذا المنتج</button>
        </div>
    </div>
    <div class="lightbox" id="lightbox" hidden>
        <button type="button" class="lightbox-close">&times;</button>
        <img src="" alt="" id="lightboxImg">
    </div>
    <div class="toast" id="toast" hidden role="alert"></div>

    <!-- ========== SCRIPTS ========== -->
    <script>window.DA_CSRF = <?= json_encode(csrf_token(), JSON_UNESCAPED_UNICODE) ?>;</script>
    <script src="<?= asset('assets/js/main.min.js') ?>" defer></script>
    <script>
    (function(){
      var deferredPrompt=null;
      var btn=document.getElementById("installAppBtn");
      window.addEventListener("beforeinstallprompt",function(e){e.preventDefault();deferredPrompt=e;if(btn)btn.hidden=false;});
      if(btn){btn.addEventListener("click",function(){if(!deferredPrompt)return;deferredPrompt.prompt();deferredPrompt.userChoice.then(function(){deferredPrompt=null;btn.hidden=true;});});}
      window.addEventListener("appinstalled",function(){if(btn)btn.hidden=true;});
    })();
    </script>
    <?php pwa_register_script(); ?>
</body>
</html>