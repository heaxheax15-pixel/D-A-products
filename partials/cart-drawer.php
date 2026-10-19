<?php /* Cart drawer — hidden by default, opened via JS */ ?>
<aside id="cartDrawer" class="cart-drawer" aria-hidden="true" aria-label="سلة التسوق" role="dialog">
    <div class="cart-drawer-backdrop" aria-hidden="true"></div>
    <div class="cart-drawer-panel" role="document">
        <header class="cart-drawer-header">
            <h2>سلة التسوق (<span id="cartDrawerCount">0</span>)</h2>
            <button type="button" class="cart-drawer-close" aria-label="إغلاق السلة">×</button>
        </header>
        <div class="cart-drawer-body" id="cartDrawerBody">
            <p class="cart-empty">سلة التسوق فارغة</p>
        </div>
        <footer class="cart-drawer-footer" id="cartDrawerFooter" hidden>
            <div class="cart-drawer-total">
                <span>الإجمالي:</span>
                <strong id="cartDrawerTotal">0 دج</strong>
            </div>
            <a href="index.php#order" class="btn btn-primary btn-block" id="cartDrawerCheckout">متابعة الطلب</a>
        </footer>
    </div>
</aside>
