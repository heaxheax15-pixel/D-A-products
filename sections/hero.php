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
                <?php $_heroHasStock = !empty(array_filter($products ?? [], function($p) { return ((int)($p["quantity_available"] ?? 0)) > 0; })); ?>
                <?php if ($_heroHasStock): ?>
                <a href="#order" class="btn btn-primary btn-lg">اطلب الآن</a>
                <?php else: ?>
                <a href="#about" class="btn btn-primary btn-lg">تعرّف على عسلنا</a>
                <?php endif; ?>
                <a href="#story" class="btn btn-outline btn-lg">تعرف على قصتنا</a>
            </div>
            <div class="trust-strip" aria-label="مزايا D&A Product">
                <div class="trust-item"><span>✓</span> عسل طبيعي 100%</div>
                <div class="trust-item"><span>✓</span> تعبئة آمنة</div>
                <div class="trust-item"><span>✓</span> توصيل سريع</div>
                <div class="trust-item"><span>✓</span> دعم واتساب مباشر</div>
            </div>
        </div>
    </section>
