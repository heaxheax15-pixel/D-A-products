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
