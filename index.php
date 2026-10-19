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
<?php require __DIR__ . "/partials/head.php"; ?>
<body>
    <a class="skip-link" href="#main-content">تخطي إلى المحتوى</a>
    <!-- ========== HEADER ========== -->
<?php require __DIR__ . "/partials/header.php"; ?>

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
<?php require __DIR__ . "/partials/footer.php"; ?>

    <!-- ========== FLOATING WHATSAPP ========== -->
<?php require __DIR__ . "/partials/whatsapp.php"; ?>

<?php require __DIR__ . "/partials/modals.php"; ?>

<?php require __DIR__ . "/partials/scripts.php"; ?>
</body>
</html>