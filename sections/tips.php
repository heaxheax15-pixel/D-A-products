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
