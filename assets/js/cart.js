/* D&A Cart — drawer + API integration */
(function () {
    'use strict';

    function getCsrf() {
        // Defensive read at call time — in case inline CSRF script was
        // reordered (bundle, cache, service worker, etc.).
        return (typeof window.DA_CSRF !== 'undefined' && window.DA_CSRF) ? window.DA_CSRF : '';
    }
    var endpoint = 'cart-action.php';
    var refs = {};

    function ready() {
        refs.drawer    = document.getElementById('cartDrawer');
        refs.button    = document.getElementById('cartButton');
        refs.badge     = document.getElementById('cartBadge');
        refs.body      = document.getElementById('cartDrawerBody');
        refs.footer    = document.getElementById('cartDrawerFooter');
        refs.totalEl   = document.getElementById('cartDrawerTotal');
        refs.countEl   = document.getElementById('cartDrawerCount');
        refs.close     = document.querySelector('#cartDrawer .cart-drawer-close');
        refs.backdrop  = document.querySelector('#cartDrawer .cart-drawer-backdrop');

        if (!refs.drawer || !refs.button) return;

        refs.button.addEventListener('click', open);
        if (refs.close) refs.close.addEventListener('click', close);
        if (refs.backdrop) refs.backdrop.addEventListener('click', close);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && refs.drawer.classList.contains('is-open')) close();
        });

        if (refs.body) refs.body.addEventListener('click', onBodyClick);

        document.querySelectorAll('.add-to-cart-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var pid = parseInt(btn.getAttribute('data-product-id'), 10) || 0;
                var qty = parseInt(btn.getAttribute('data-quantity') || '1', 10) || 1;
                if (pid > 0) window.DACart.add(pid, qty);
            });
        });

        callApi('get', {});
    }

    function callApi(action, data) {
        var fd = new FormData();
        fd.append('csrf_token', getCsrf());
        fd.append('action', action);
        Object.keys(data || {}).forEach(function (k) { fd.append(k, data[k]); });

        return fetch(endpoint, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
            cache: 'no-store'
        }).then(function (r) { return r.json(); })
          .then(function (res) {
              if (res && res.success && res.cart) render(res.cart);
              return res;
          })
          .catch(function (err) {
              console.warn('[D&A cart] API error:', err);
              /* Network failure: hide the badge to avoid a stale count
                 (better to show no badge than a wrong one). */
              if (refs.badge) refs.badge.hidden = true;
              return { success: false, error: 'network' };
          });
    }

    function render(cart) {
        if (refs.badge) {
            if (cart.total_quantity > 0) {
                refs.badge.hidden = false;
                refs.badge.textContent = cart.total_quantity;
            } else {
                refs.badge.hidden = true;
            }
        }
        if (refs.countEl) refs.countEl.textContent = cart.count;

        if (!refs.body) return;
        if (!cart.items || cart.items.length === 0) {
            refs.body.innerHTML = '<p class="cart-empty">سلة التسوق فارغة</p>';
            if (refs.footer) refs.footer.hidden = true;
            return;
        }
        if (refs.footer) refs.footer.hidden = false;
        if (refs.totalEl) refs.totalEl.textContent = formatMoney(cart.total);

        var html = '';
        cart.items.forEach(function (it) {
            html += '<div class="cart-item" data-product-id="' + it.product_id + '">';
            html += '<div class="cart-item-info"><strong>' + esc(it.name) + '</strong>';
            html += '<span class="cart-item-price">' + formatMoney(it.price) + ' × ' + it.quantity + '</span></div>';
            html += '<div class="cart-item-qty">';
            html += '<button type="button" data-action="decrement" aria-label="تقليل">−</button>';
            html += '<span aria-live="polite">' + it.quantity + '</span>';
            html += '<button type="button" data-action="increment" aria-label="زيادة">+</button>';
            html += '</div>';
            html += '<div class="cart-item-subtotal">' + formatMoney(it.subtotal) + '</div>';
            html += '<button type="button" data-action="remove" aria-label="إزالة" title="إزالة">×</button>';
            html += '</div>';
        });
        refs.body.innerHTML = html;
    }

    function onBodyClick(e) {
        var btn = e.target.closest('[data-action]');
        if (!btn) return;
        var item = btn.closest('.cart-item');
        if (!item) return;
        var pid = parseInt(item.getAttribute('data-product-id'), 10) || 0;
        if (pid <= 0) return;
        var action = btn.getAttribute('data-action');
        var qtyEl = item.querySelector('.cart-item-qty span');
        var cur = qtyEl ? parseInt(qtyEl.textContent, 10) : 0;

        if (action === 'remove') {
            callApi('remove', { product_id: pid });
        } else if (action === 'increment') {
            callApi('update', { product_id: pid, quantity: Math.min(99, cur + 1) });
        } else if (action === 'decrement') {
            if (cur <= 1) callApi('remove', { product_id: pid });
            else callApi('update', { product_id: pid, quantity: cur - 1 });
        }
    }

    function open() {
        if (!refs.drawer) return;
        refs.drawer.classList.add('is-open');
        refs.drawer.setAttribute('aria-hidden', 'false');
        if (refs.button) refs.button.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
        /* Defensive: refresh cart state every time the drawer opens.
           Prevents badge/drawer desync when page HTML is stale. */
        callApi('get', {});
    }

    function close() {
        if (!refs.drawer) return;
        refs.drawer.classList.remove('is-open');
        refs.drawer.setAttribute('aria-hidden', 'true');
        if (refs.button) refs.button.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    function formatMoney(n) {
        var v = Math.round(Number(n) || 0);
        return v.toLocaleString('en-US') + ' دج';
    }

    function esc(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    window.DACart = {
        add: function (pid, qty) {
            return callApi('add', { product_id: pid, quantity: qty || 1 }).then(function (res) {
                if (res && res.success) open();
                return res;
            });
        },
        open: open,
        close: close,
        refresh: function () { return callApi('get', {}); }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ready);
    } else {
        ready();
    }
})();
