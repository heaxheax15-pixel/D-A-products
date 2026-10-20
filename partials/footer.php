<?php
/* Self-contained: safe to include from any page without pre-defining variables */
$_waNum  = $waNum  ?? runtime('CONTACT_WHATSAPP', CONTACT_WHATSAPP);
$_waMsg  = $waMsg  ?? urlencode(runtime('WHATSAPP_GREETING', WHATSAPP_GREETING));
$_ig     = $ig     ?? runtime('CONTACT_INSTAGRAM', CONTACT_INSTAGRAM);
$_tiktok = $tiktok ?? runtime('CONTACT_TIKTOK', CONTACT_TIKTOK);
?>
    <footer class="site-footer" id="footer">
        <div class="container footer-grid">
            <div class="footer-col">
                <span class="logo-text"><img src="<?= asset('images/logo-full.png') ?>" alt="D&amp;A Product" width="120" height="120" style="display: block; margin-bottom: 1rem; object-fit: contain;"><br>
D&amp;A Product</span>
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
                <a href="https://wa.me/<?= e($_waNum) ?>?text=<?= $_waMsg ?>" target="_blank" rel="noopener">واتساب</a>
                <a href="<?= e($_ig) ?>" target="_blank" rel="noopener">إنستغرام</a>
                <a href="<?= e($_tiktok) ?>" target="_blank" rel="noopener">تيك توك</a>
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
<?php unset($_waNum, $_waMsg, $_ig, $_tiktok); ?>
