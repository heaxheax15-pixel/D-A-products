    <!-- ========== MODALS ========== -->
    <div class="modal" id="productModal" hidden>
        <div class="modal-backdrop" data-close></div>
        <div class="modal-box">
            <button type="button" class="modal-close" data-close>&times;</button>
            <img id="modalImg" src="" alt="">
            <h3 id="modalTitle"></h3>
            <p id="modalDesc"></p>
            <p class="modal-price" id="modalPrice"></p>
            <button type="button" class="btn btn-primary" id="modalOrder">اطلب هذا المنتج</button>
        </div>
    </div>
    <div class="lightbox" id="lightbox" hidden>
        <button type="button" class="lightbox-close">&times;</button>
        <img src="" alt="" id="lightboxImg">
    </div>
    <div class="toast" id="toast" hidden role="alert"></div>
