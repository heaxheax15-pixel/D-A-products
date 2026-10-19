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
