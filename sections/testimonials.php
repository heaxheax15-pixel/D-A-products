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
