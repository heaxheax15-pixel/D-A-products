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
