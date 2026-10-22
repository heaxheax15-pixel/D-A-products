<?php
/* Shared checkout fields used by both sections/order.php (quick buy) and checkout.php (cart flow) */
?>
<div class="form-row">
    <div>
        <label for="customer_name">الاسم الكامل *</label>
        <input type="text" id="customer_name" name="customer_name" required placeholder="الاسم واللقب" value="<?= e($_POST['customer_name'] ?? '') ?>">
    </div>
</div>
<div class="form-row">
    <div>
        <label for="customer_phone">رقم الهاتف *</label>
        <input type="tel" id="customer_phone" name="customer_phone" required pattern="<?= e(algerian_phone_pattern_html()) ?>" placeholder="05xxxxxxxx" dir="ltr" value="<?= e($_POST['customer_phone'] ?? '') ?>">
    </div>
</div>
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
        <textarea id="customer_address_fallback" name="customer_address" required rows="3" placeholder="الولاية، البلدية، الشارع..."><?= e($_POST['customer_address'] ?? '') ?></textarea>
    </div>
</div>
<input type="hidden" id="customer_address" disabled>
<div class="form-row">
    <div>
        <label>طريقة الدفع *</label>
        <div class="payment-options">
            <label class="radio-card">
                <input type="radio" name="payment_method" value="bank_transfer" required <?= (($_POST['payment_method'] ?? '') === 'bank_transfer') ? 'checked' : '' ?>>
                <span>تحويل بنكي / CCP</span>
            </label>
            <label class="radio-card">
                <input type="radio" name="payment_method" value="cod" <?= (($_POST['payment_method'] ?? 'cod') === 'cod') ? 'checked' : '' ?>>
                <span>الدفع عند الاستلام</span>
            </label>
        </div>
    </div>
</div>
<div id="bankInfo" class="bank-info" hidden>
    <p><strong>اسم صاحب الحساب:</strong> <?= e(runtime('BANK_ACCOUNT_HOLDER', BANK_ACCOUNT_HOLDER)) ?></p>
    <p><strong>رقم CCP أو RIP:</strong> <code dir="ltr"><?= e(runtime('BANK_IBAN', BANK_IBAN)) ?></code></p>
</div>
<div class="form-row" id="receiptUploadRow" hidden>
    <div>
        <label for="receipt">صورة الوصل *</label>
        <input type="file" id="receipt" name="receipt" accept="image/jpeg,image/png,image/webp">
    </div>
</div>
<div class="form-row">
    <div>
        <label for="notes">ملاحظات (اختياري)</label>
        <textarea id="notes" name="notes" rows="2" placeholder="أي تفاصيل إضافية..."><?= e($_POST['notes'] ?? '') ?></textarea>
    </div>
</div>
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
