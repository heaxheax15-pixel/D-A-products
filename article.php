<?php
declare(strict_types=1);

if (extension_loaded('zlib')) {
    ob_start('ob_gzhandler');
} else {
    ob_start();
}

define('DA_APP', true);
require_once __DIR__ . '/includes/bootstrap.php';

$id = (int) ($_GET['id'] ?? 0);
$article = get_article($id);
if (!$article) {
    http_response_code(404);
    header('Location: index.php#tips');
    exit;
}

$wa = runtime('CONTACT_WHATSAPP', CONTACT_WHATSAPP);
$ig = runtime('CONTACT_INSTAGRAM', CONTACT_INSTAGRAM);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($article['title']) ?> – D&A Product</title>
    <meta name="description" content="<?= e($article['excerpt']) ?>">
    <link rel="icon" href="favicon.svg" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/style.min.css') ?>">
</head>
<body>
    <header class="site-header is-scrolled">
        <div class="container header-inner">
            <a href="index.php" class="logo"><span class="logo-text">D&amp;A Product</span></a>
            <a href="index.php#tips" class="btn btn-secondary">← العودة</a>
        </div>
    </header>
    <article class="article-page container">
        <img src="<?= e($article['image']) ?>" alt="" class="article-hero-img" loading="lazy">
        <time datetime="<?= e($article['date']) ?>"><?= e(date('d/m/Y', strtotime($article['date']))) ?></time>
        <h1><?= e($article['title']) ?></h1>
        <div class="article-body"><?= $article['body'] ?></div>
        <a href="index.php#order" class="btn btn-primary">اطلب عسلك الآن</a>
    </article>
    <footer class="site-footer site-footer-compact">
        <div class="container"><p>&copy; <?= date('Y') ?> D&amp;A Product</p></div>
    </footer>
</body>
</html>
