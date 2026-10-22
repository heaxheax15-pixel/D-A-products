    <!-- ========== ORDER FORM ========== -->
    <section class="section order-section" id="order">
        <div class="container">
            <h2 class="section-title-center">اطلب عسلك الآن</h2>
            <?php $_inStock = array_values(array_filter($products, function($p) { return ((int)($p["quantity_available"] ?? 0)) > 0; })); ?>
            <?php if (empty($_inStock)): ?>
            <div class="order-unavailable-notice" style="max-width: 720px; margin: 0 auto; padding: 2rem 1.5rem; background: var(--cream-mid); border-radius: var(--r-lg); text-align: center;">
                <h3 style="font-family: var(--font-d); font-size: 1.6rem; margin-bottom: 1rem;">الطلب سيفتح قريباً</h3>
                <p style="color: var(--deep); margin-bottom: 1.5rem;">نجهّز الدفعة الأولى من عسلنا الطبيعي. للاستفسار أو الطلب المسبق:</p>
                <a href="https://wa.me/<?= e(runtime("CONTACT_WHATSAPP", CONTACT_WHATSAPP)) ?>?text=<?= urlencode(runtime("WHATSAPP_GREETING", WHATSAPP_GREETING)) ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener">تواصل عبر واتساب</a>
            </div>
            <?php else: ?>
            <form class="order-form" id="orderForm" action="submit-order.php" method="post" enctype="multipart/form-data" novalidate aria-label="نموذج الطلب">
                <?= csrf_field() ?>
                <div class="order-form-header">
                    <span>طلبك سهل ومباشر</span>
                    <strong id="selectedProductSummary">اختر منتجك المفضل</strong>
                </div>
                <div class="form-row"><label for="customer_name">الاسم الكامل *</label><input type="text" id="customer_name" name="customer_name" required autocomplete="name"></div>
                <div class="form-row"><label for="customer_phone">رقم الهاتف *</label><input type="tel" id="customer_phone" name="customer_phone" required pattern="<?= e(algerian_phone_pattern_html()) ?>" placeholder="05xxxxxxxx" dir="ltr" title="رقم جزائري: 10 أرقام تبدأ بـ 05 أو 06 أو 07"></div>
                <div class="form-row checkout-location" id="locationBlock">
                    <div>
                        <label for="wilaya">الولاية *</label>
                        <select id="wilaya" required>
                            <option value="">اختر الولاية</option>
                        </select>
                    </div>
                </div>
                <div class="form-row checkout-location" id="communeRow" hidden>
                    <div>
                        <label for="commune">البلدية *</label>
                        <select id="commune" required>
                            <option value="">اختر البلدية</option>
                        </select>
                    </div>
                </div>
                <div class="form-row" id="addressDetailsRow" hidden>
                    <div>
                        <label for="address_details">تفاصيل العنوان (الشارع، رقم المنزل...) — اختياري</label>
                        <input type="text" id="address_details" placeholder="مثال: حي 5 جويلية، عمارة 4...">
                    </div>
                </div>
                <div class="form-row" id="addressFallbackRow">
                    <div>
                        <label for="customer_address_fallback">العنوان الكامل *</label>
                        <textarea id="customer_address_fallback" name="customer_address" required rows="3" placeholder="الولاية، البلدية، الشارع..."></textarea>
                    </div>
                </div>
                <input type="hidden" id="customer_address" name="customer_address" disabled>
                <div class="form-row form-row-2">
                    <div><label for="product_name">المنتج *</label>
                        <select id="product_name" name="product_name" required>
                            <option value="">اختر المنتج</option>
                            <?php foreach ($_inStock as $p): ?>
                            <option value="<?= e($p['name']) ?>" data-price="<?= e((string) $p['price']) ?>"><?= e($p['name']) ?> – <?= number_format((float) $p['price'], 0) ?> دج</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div><label for="quantity">الكمية *</label><input type="number" id="quantity" name="quantity" min="1" max="99" value="1" required></div>
                </div>
                <div class="order-total-wrap">
                    <p class="order-total" id="orderTotal">الإجمالي: <strong>0</strong> دج</p>
                    <p class="order-note">سيتم تأكيد الطلب مباشرة عبر واتساب بعد إرسال النموذج.</p>
                </div>
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
            <?php endif; ?>
        </div>
    </section>
