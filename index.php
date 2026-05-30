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
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <!-- modern design system -->
    <link rel="stylesheet" href="<?= asset('assets/css/modern.min.css') ?>">
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
            <nav class="main-nav" id="mainNav">
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

    <!-- ========== HERO ========== -->
    <section class="hero" id="top">
        <div class="hero-bg zoom-bg" style="background-image:url(images/pro1.webp)"></div>
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <p class="hero-eyebrow">من المنحل إلى مائدتك</p>
            <h1>عسل D&amp;A – أصالة الطبيعة في كل قطرة</h1>
            <p class="hero-typewriter"><span id="typewriter"></span><span class="cursor">|</span></p>
            <p class="hero-sub">عسل طبيعي 100%، معصور على البارد من مناحلنا الجبلية المعتمدة.</p>
            <div class="hero-ctas">
                <a href="#order" class="btn btn-primary btn-lg">اطلب الآن</a>
                <a href="#story" class="btn btn-outline btn-lg">تعرف على قصتنا</a>
            </div>
        </div>
    </section>

    <!-- ========== WHY US ========== -->
    <section class="section why" id="why">
        <div class="container">
            <span class="section-tag">لماذا D&amp;A؟</span>
            <h2 class="section-title-center">تميزنا في كل قطرة</h2>
            <div class="features-grid">
                <div class="glass-card reveal">
                    <div class="feat-icon"><svg viewBox="0 0 48 48" width="40"><path fill="#FFAA00" d="M24 4l4 10h10l-8 7 3 10-9-6-9 6 3-10-8-7h10z"/></svg></div>
                    <h3>نقي طبيعياً</h3>
                    <p>عسل خام دون معالجة حرارية تُفقد فوائده.</p>
                </div>
                <div class="glass-card reveal">
                    <div class="feat-icon">🚫</div>
                    <h3>بدون سكر مضاف</h3>
                    <p>نكهة أصيلة من رحيق الأزهار فقط.</p>
                </div>
                <div class="glass-card reveal">
                    <div class="feat-icon">❄️</div>
                    <h3>معصور على البارد</h3>
                    <p>نحافظ على الإنزيمات والعناصر الحية.</p>
                </div>
                <div class="glass-card reveal">
                    <div class="feat-icon">🚚</div>
                    <h3>توصيل سريع</h3>
                    <p>تغليف آمن ووصول لباب منزلك.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== QUALITY ========== -->
    <section class="section quality" id="quality">
        <div class="container section-grid">
            <figure class="section-media reveal">
                <img src="images/pro2.webp" alt="عسل ذهبي يُسكب من ملعقة خشبية" width="800" height="600" loading="lazy">
            </figure>
            <div class="section-text reveal">
                <span class="section-tag">جودة العسل</span>
                <h2>معايير لا نتنازل عنها</h2>
                <p>في D&amp;A Product نؤمن أن الجودة تبدأ من المنحل. نفحص كل دفعة، ونرفض أي عسل لا يلبي معايير النقاء والقوام والنكهة التي اعتاد عليها عملاؤنا.</p>
                <p>عسلنا شفاف في المصدر، غني بالإنزيمات، ويُعبّأ بأيدٍ خبيرة تحترم أسرار النحل.</p>
            </div>
        </div>
    </section>

    <!-- ========== ABOUT HONEY ========== -->
    <section class="section about" id="about">
        <div class="container section-grid">
            <div class="section-text reveal">
                <span class="section-tag">🍯 معرفة</span>
                <h2>ما هو العسل؟</h2>
                <p>العسل هدية الطبيعة التي يصنعها النحل من رحيق الأزهار — مادة غذائية كاملة تحتوي على سكريات طبيعية، فيتامينات، معادن ومضادات أكسدة.</p>
                <p>منذ آلاف السنين استُخدم كمصدر طاقة ودعم للمناعة. عسلنا يُعبّأ خاماً للحفاظ على فوائده دون تسخين مفرط.</p>
            </div>
            <figure class="section-media reveal">
                <img src="images/about-honey.webp" alt="عسل طبيعي" loading="lazy">
            </figure>
        </div>
    </section>

    <!-- ========== JOURNEY ========== -->
    <section class="section journey parallax-section" id="journey" data-parallax="images/pro3.webp">
        <div class="parallax-bg"></div>
        <div class="container journey-inner">
            <span class="section-tag">رحلة العسل</span>
            <h2 class="section-title-center light-text">كيف نستخلص عسلنا؟</h2>
            <div class="timeline-interactive">
                <?php
                $steps = [
                    ['الفرز', 'نختار أجود أقراص العسل بعد فحص دقيق.'],
                    ['النضج', 'نتركه ينضج طبيعياً حتى يصل للقوام المثالي.'],
                    ['القطف', 'عصر لطيف على البارد دون إتلاف الإنزيمات.'],
                    ['التعبئة', 'تعبئة محكمة تحافظ على النقاء والنكهة.'],
                ];
                foreach ($steps as $i => [$t, $d]): ?>
                <div class="tl-step reveal" data-step="<?= $i + 1 ?>">
                    <div class="tl-icon"><svg viewBox="0 0 24 24" width="28" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg></div>
                    <h3><?= e($t) ?></h3>
                    <p><?= e($d) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ========== TEAM ========== -->
    <section class="section team" id="team">
        <div class="container section-grid">
            <figure class="section-media reveal">
                <img src="images/pro3.webp" alt="خبير نحل في منحل D&A" loading="lazy">
            </figure>
            <div class="section-text reveal">
                <span class="section-tag">خبراؤنا</span>
                <h2>أيدٍ خبيرة خلف كل برطمان</h2>
                <p>فريقنا من النحالين يعمل بشغف في مناحلنا الجبلية، بخبرة موروثة ومعايير حديثة لضمان عسل آمن ونقي في كل موسم.</p>
            </div>
        </div>
    </section>

    <!-- ========== STORY ========== -->
    <section class="section story parallax-section" id="story" data-parallax="images/pro1.webp">
        <div class="parallax-bg"></div>
        <div class="container story-content reveal">
            <span class="section-tag">مناحلنا</span>
            <h2 class="light-text">قصة D&amp;A</h2>
            <p class="light-text">ولدت D&amp;A Product من حب الطبيعة والنحل. بدأنا من مناحل عائلية صغيرة ونمونا مع ثقة عملائنا — لأننا نؤمن أن العسل ليس سلعة، بل أمانة.</p>
            <p class="light-text">كل دفعة تروي قصة زهرة وجبل وشمس، نقدمها لكم بفخر.</p>
        </div>
    </section>

    <!-- ========== FEATURED PRODUCT ========== -->
    <?php if ($featured): ?>
    <section class="section featured" id="featured" style="background-image:url(images/pro4.webp)">
        <div class="featured-overlay"></div>
        <div class="container featured-inner reveal">
            <span class="featured-badge">منتج الشهر</span>
            <h2><?= e($featured['name']) ?></h2>
            <p><?= e(mb_substr($featured['description'], 0, 120)) ?>…</p>
            <p class="featured-price"><s><?= number_format((float) $featured['price'] * 1.15, 0) ?></s> <?= number_format((float) $featured['price'], 0) ?> دج</p>
            <p class="featured-discount">خصم 15% لمدة محدودة</p>
            <div class="countdown" id="countdown" data-hours="72"></div>
            <button type="button" class="btn btn-primary btn-lg order-product-btn" data-product="<?= e($featured['name']) ?>" data-price="<?= e((string) $featured['price']) ?>">اطلب الآن</button>
        </div>
    </section>
    <?php endif; ?>

    <!-- ========== PRODUCTS ========== -->
    <section class="section products" id="products">
        <div class="container">
            <span class="section-tag">✨ تسوق</span>
            <h2 class="section-title-center">منتجاتنا</h2>
            <div class="filter-bar">
                <button type="button" class="filter-btn active" data-filter="all">الكل</button>
                <button type="button" class="filter-btn" data-filter="sidr">عسل السدر</button>
                <button type="button" class="filter-btn" data-filter="talh">عسل الطلح</button>
                <button type="button" class="filter-btn" data-filter="flowers">عسل الزهور</button>
                <button type="button" class="filter-btn" data-filter="comb">عسل الشهد</button>
            </div>
            <div class="products-grid" id="productsGrid">
                <?php foreach ($products as $p):
                    $cat = $p['category'] ?? 'flowers';
                    $img = product_image_url($p);
                    $rating = (int) ($p['rating'] ?? 5);
                ?>
                <article class="product-card" data-category="<?= e($cat) ?>"
                    data-id="<?= (int) ($p['id'] ?? 0) ?>"
                    data-name="<?= e($p['name']) ?>"
                    data-price="<?= e((string) $p['price']) ?>"
                    data-desc="<?= e($p['description']) ?>"
                    data-image="<?= e($img) ?>">
                    <?php if (!empty($p['is_bestseller'])): ?><span class="badge-bestseller">الأكثر مبيعاً</span><?php endif; ?>
                    <div class="product-img-wrap">
                        <img src="<?= e($img) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                    </div>
                    <div class="product-body">
                        <div class="stars" aria-label="تقييم <?= $rating ?>"><?= str_repeat('★', $rating) . str_repeat('☆', 5 - $rating) ?></div>
                        <h3><?= e($p['name']) ?></h3>
                        <p class="product-desc"><?= e(mb_substr($p['description'], 0, 70)) ?>…</p>
                        <p class="product-price"><?= number_format((float) $p['price'], 0) ?> <span>دج</span></p>
                        <div class="product-actions">
                            <button type="button" class="btn btn-ghost quick-view-btn">تفاصيل سريعة</button>
                            <button type="button" class="btn btn-secondary order-product-btn" data-product="<?= e($p['name']) ?>" data-price="<?= e((string) $p['price']) ?>">اطلب</button>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ========== TESTIMONIALS ========== -->
    <section class="section testimonials hex-bg" id="testimonials">
        <div class="container">
            <span class="section-tag">آراء العملاء</span>
            <h2 class="section-title-center">ماذا يقول عملاؤنا؟</h2>
            <div class="carousel" id="testimonialCarousel">
                <div class="carousel-track">
                    <?php
                    $reviews = [
                        ['نورة العتيبي', '★★★★★', 'أفضل عسل سدر جربته! النكهة غنية والتوصيل سريع.'],
                        ['خالد الشمري', '★★★★★', 'جودة ممتازة وتعامل راقٍ. أصبح عسل D&A خياري الدائم.'],
                        ['فاطمة الحربي', '★★★★☆', 'عسل زهور برية لذيذ وطبيعي. أنصح به بشدة.'],
                    ];
                    foreach ($reviews as [$name, $stars, $text]): ?>
                    <blockquote class="testimonial-card">
                        <div class="avatar"><?= e(mb_substr($name, 0, 1)) ?></div>
                        <div class="stars"><?= $stars ?></div>
                        <p><?= e($text) ?></p>
                        <cite>— <?= e($name) ?></cite>
                    </blockquote>
                    <?php endforeach; ?>
                </div>
                <div class="carousel-dots" id="carouselDots"></div>
            </div>
        </div>
    </section>

    <!-- ========== GALLERY ========== -->
    <section class="section gallery" id="gallery">
        <div class="container">
            <h2 class="section-title-center">معرض الصور</h2>
            <div class="gallery-grid">
                <?php for ($i = 1; $i <= 4; $i++): ?>
                <a href="images/gallery-<?= $i ?>.webp" class="gallery-item" data-lightbox>
                    <img src="images/gallery-<?= $i ?>.webp" alt="معرض D&A <?= $i ?>" loading="lazy">
                </a>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <!-- ========== TIPS ========== -->
    <section class="section tips" id="tips">
        <div class="container">
            <span class="section-tag">نصائح العسل</span>
            <h2 class="section-title-center">Honey Tips</h2>
            <div class="tips-grid">
                <?php foreach ($articles as $aid => $art): ?>
                <a href="article.php?id=<?= (int) $aid ?>" class="tip-card reveal">
                    <img src="<?= e($art['image']) ?>" alt="" loading="lazy">
                    <h3><?= e($art['title']) ?></h3>
                    <p><?= e($art['excerpt']) ?></p>
                    <span class="read-more">اقرأ المزيد ←</span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ========== ORDER FORM ========== -->
    <section class="section order-section" id="order">
        <div class="container">
            <h2 class="section-title-center">اطلب عسلك الآن</h2>
            <form class="order-form" id="orderForm" action="submit-order.php" method="post" enctype="multipart/form-data" novalidate>
                <?= csrf_field() ?>
                <div class="form-row"><label for="customer_name">الاسم الكامل *</label><input type="text" id="customer_name" name="customer_name" required autocomplete="name"></div>
                <div class="form-row"><label for="customer_phone">رقم الهاتف *</label><input type="tel" id="customer_phone" name="customer_phone" required pattern="<?= e(algerian_phone_pattern_html()) ?>" placeholder="05xxxxxxxx" dir="ltr" title="رقم جزائري: 10 أرقام تبدأ بـ 05 أو 06 أو 07"></div>
                <div class="form-row"><label for="customer_address">العنوان *</label><textarea id="customer_address" name="customer_address" required rows="3"></textarea></div>
                <div class="form-row form-row-2">
                    <div><label for="product_name">المنتج *</label>
                        <select id="product_name" name="product_name" required>
                            <option value="">اختر المنتج</option>
                            <?php foreach ($products as $p): ?>
                            <option value="<?= e($p['name']) ?>" data-price="<?= e((string) $p['price']) ?>"><?= e($p['name']) ?> – <?= number_format((float) $p['price'], 0) ?> دج</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div><label for="quantity">الكمية *</label><input type="number" id="quantity" name="quantity" min="1" max="99" value="1" required></div>
                </div>
                <p class="order-total" id="orderTotal">الإجمالي: <strong>0</strong> دج</p>
                <div class="form-row"><label for="notes">ملاحظات</label><textarea id="notes" name="notes" rows="2"></textarea></div>
                <fieldset class="payment-fieldset">
                    <legend>طريقة الدفع *</legend>
                    <label class="radio-card">
                        <input type="radio" name="payment_method" value="bank_transfer" required>
                        <span>الدفع عبر CCP / BaridiMob</span>
                    </label>
                    <label class="radio-card">
                        <input type="radio" name="payment_method" value="cod" required>
                        <span>الدفع عند الاستلام</span>
                    </label>
                </fieldset>
                <div class="bank-info" id="bankInfo" hidden>
                    <h3>معلومات التحويل</h3>
                    <p><strong>طريقة الدفع:</strong> CCP / BaridiMob</p>
                    <p><strong>اسم صاحب الحساب:</strong> <?= e($bankHolder) ?></p>
                    <p><strong>رقم CCP أو RIP:</strong> <code dir="ltr"><?= e($bankIban) ?></code></p>
                    <label class="receipt-label">
                        إرفاق وصل الدفع (اختياري)
                        <input type="file" name="receipt" id="receipt" accept="image/jpeg,image/png,image/webp">
                    </label>
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block" id="submitBtn">إرسال الطلب</button>
            </form>
        </div>
    </section>

    <!-- ========== FOOTER ========== -->
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
                <form id="newsletterForm" class="newsletter-form">
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