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
            <div class="product-spotlight">
                <div>
                    <span>توصيل سريع</span>
                    <strong>أكثر من 2,400 طلب تم توصيله</strong>
                </div>
                <div>
                    <span>جودة موثوقة</span>
                    <strong>عسل طبيعي 100% وبدون إضافات</strong>
                </div>
                <div>
                    <span>دعم مباشر</span>
                    <strong>رد خلال دقائق على واتساب</strong>
                </div>
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
