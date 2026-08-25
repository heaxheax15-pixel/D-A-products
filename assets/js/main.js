(function () {
  "use strict";

  var header = document.getElementById("siteHeader");
  var navToggle = document.querySelector(".nav-toggle");
  var mainNav = document.getElementById("mainNav");
  var orderForm = document.getElementById("orderForm");
  var productSelect = document.getElementById("product_name");
  var quantityInput = document.getElementById("quantity");
  var orderTotal = document.getElementById("orderTotal");
  var bankInfo = document.getElementById("bankInfo");
  var toast = document.getElementById("toast");
  var productModal = document.getElementById("productModal");

  /* Header scroll */
  function onScroll() {
    if (header) header.classList.toggle("is-scrolled", window.scrollY > 60);
  }
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  /* Mobile nav */
  if (navToggle && mainNav) {
    navToggle.addEventListener("click", function () {
      var open = mainNav.classList.toggle("is-open");
      navToggle.classList.toggle("is-active", open);
      navToggle.setAttribute("aria-expanded", open ? "true" : "false");
    });
    mainNav.querySelectorAll("a").forEach(function (a) {
      a.addEventListener("click", function () {
        mainNav.classList.remove("is-open");
        navToggle.classList.remove("is-active");
      });
    });
  }

  /* Typewriter */
  var phrases = ["طبيعي 100%", "مباشرة من المناحل الجبلية", "جودة لا تضاهى", "معصور على البارد"];
  var twEl = document.getElementById("typewriter");
  var pi = 0, ci = 0, del = false;
  function typeTick() {
    if (!twEl) return;
    var word = phrases[pi];
    if (!del) {
      twEl.textContent = word.substring(0, ++ci);
      if (ci === word.length) {
        del = true;
        setTimeout(typeTick, 1800);
        return;
      }
    } else {
      twEl.textContent = word.substring(0, --ci);
      if (ci === 0) {
        del = false;
        pi = (pi + 1) % phrases.length;
      }
    }
    setTimeout(typeTick, del ? 60 : 100);
  }
  typeTick();

  /* Reveal on scroll */
  var reveals = document.querySelectorAll(".reveal, .tl-step");
  var io = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) en.target.classList.add("visible");
      });
    },
    { threshold: 0.15 }
  );
  reveals.forEach(function (el) {
    io.observe(el);
  });

  /* Parallax backgrounds */
  document.querySelectorAll(".parallax-section").forEach(function (sec) {
    var url = sec.getAttribute("data-parallax");
    var bg = sec.querySelector(".parallax-bg");
    if (bg && url) bg.style.backgroundImage = "url(" + url + ")";
  });

  /* Product filter */
  document.querySelectorAll(".filter-btn").forEach(function (btn) {
    btn.addEventListener("click", function () {
      document.querySelectorAll(".filter-btn").forEach(function (b) {
        b.classList.remove("active");
      });
      btn.classList.add("active");
      var f = btn.getAttribute("data-filter");
      document.querySelectorAll(".product-card").forEach(function (card) {
        var cat = card.getAttribute("data-category");
        var show = f === "all" || cat === f;
        card.classList.toggle("hidden", !show);
      });
    });
  });

  /* Order total */
  function getUnitPrice() {
    if (!productSelect || !productSelect.selectedOptions[0]) return 0;
    return parseFloat(productSelect.selectedOptions[0].dataset.price || "0", 10);
  }
  function updateSelectedSummary() {
    var selected = productSelect && productSelect.selectedOptions && productSelect.selectedOptions[0];
    var summary = document.getElementById("selectedProductSummary");
    if (!summary) return;
    if (!selected || !selected.value) {
      summary.textContent = "اختر منتجك المفضل";
      return;
    }
    var qty = parseInt(quantityInput && quantityInput.value, 10) || 1;
    summary.textContent = selected.value + " • " + qty + " وحدة";
  }
  function updateTotal() {
    if (!orderTotal) return;
    var qty = parseInt(quantityInput && quantityInput.value, 10) || 1;
    var total = (getUnitPrice() * qty).toFixed(0);
    orderTotal.innerHTML = "الإجمالي: <strong>" + total + "</strong> دج";
    updateSelectedSummary();
  }
  if (productSelect) productSelect.addEventListener("change", updateTotal);
  if (quantityInput) quantityInput.addEventListener("input", updateTotal);
  updateTotal();

  /* Payment + bank */
  document.querySelectorAll('input[name="payment_method"]').forEach(function (r) {
    r.addEventListener("change", function () {
      if (bankInfo) bankInfo.hidden = r.value !== "bank_transfer" || !r.checked;
    });
  });

  /* Order product buttons */
  function scrollToOrder(name) {
    if (productSelect && name) productSelect.value = name;
    updateTotal();
    document.getElementById("order").scrollIntoView({ behavior: "smooth" });
    document.getElementById("customer_name").focus();
  }
  document.querySelectorAll(".order-product-btn").forEach(function (btn) {
    btn.addEventListener("click", function () {
      scrollToOrder(btn.getAttribute("data-product"));
    });
  });

  /* Product modal */
  document.querySelectorAll(".quick-view-btn").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var card = btn.closest(".product-card");
      if (!card || !productModal) return;
      document.getElementById("modalImg").src = card.getAttribute("data-image");
      document.getElementById("modalTitle").textContent = card.getAttribute("data-name");
      document.getElementById("modalDesc").textContent = card.getAttribute("data-desc");
      document.getElementById("modalPrice").textContent = card.getAttribute("data-price") + "دج";
      document.getElementById("modalOrder").onclick = function () {
        productModal.hidden = true;
        scrollToOrder(card.getAttribute("data-name"));
      };
      productModal.hidden = false;
    });
  });
  productModal &&
    productModal.querySelectorAll("[data-close], .modal-close").forEach(function (el) {
      el.addEventListener("click", function () {
        productModal.hidden = true;
      });
    });

  /* Countdown */
  var cd = document.getElementById("countdown");
  if (cd) {
    var hrs = parseInt(cd.getAttribute("data-hours") || "72", 10);
    var end = Date.now() + hrs * 3600000;
    function tick() {
      var d = Math.max(0, end - Date.now());
      var h = Math.floor(d / 3600000);
      var m = Math.floor((d % 3600000) / 60000);
      var s = Math.floor((d % 60000) / 1000);
      cd.innerHTML =
        "<span>" + h + "س</span><span>" + m + "د</span><span>" + s + "ث</span>";
      if (d > 0) requestAnimationFrame(function () {
        setTimeout(tick, 1000);
      });
    }
    tick();
  }

  /* Testimonial carousel */
  var track = document.querySelector(".carousel-track");
  var dots = document.getElementById("carouselDots");
  if (track && dots) {
    var slides = track.children.length;
    var idx = 0;
    for (var i = 0; i < slides; i++) {
      var dot = document.createElement("button");
      if (i === 0) dot.classList.add("active");
      dot.setAttribute("aria-label", "شريحة " + (i + 1));
      (function (j) {
        dot.addEventListener("click", function () {
          idx = j;
          updateCarousel();
        });
      })(i);
      dots.appendChild(dot);
    }
    function updateCarousel() {
      track.style.transform = "translateX(" + idx * 100 + "%)"; /* RTL */
      dots.querySelectorAll("button").forEach(function (d, i) {
        d.classList.toggle("active", i === idx);
      });
    }
    setInterval(function () {
      idx = (idx + 1) % slides;
      updateCarousel();
    }, 5000);
  }

  /* Lightbox */
  var lightbox = document.getElementById("lightbox");
  var lightboxImg = document.getElementById("lightboxImg");
  document.querySelectorAll("[data-lightbox]").forEach(function (a) {
    a.addEventListener("click", function (e) {
      e.preventDefault();
      lightboxImg.src = a.getAttribute("href");
      lightbox.hidden = false;
    });
  });
  if (lightbox) {
    lightbox.querySelector(".lightbox-close").addEventListener("click", function () {
      lightbox.hidden = true;
    });
    lightbox.addEventListener("click", function (e) {
      if (e.target === lightbox) lightbox.hidden = true;
    });
  }

  /* Toast */
  function showToast(msg, isErr) {
    if (!toast) return;
    toast.textContent = msg;
    toast.classList.toggle("error", !!isErr);
    toast.hidden = false;
    setTimeout(function () {
      toast.hidden = true;
    }, 5000);
  }

  /* AJAX order */
  if (orderForm) {
    orderForm.addEventListener("submit", function (e) {
      e.preventDefault();
      var phone = document.getElementById("customer_phone");
      if (phone && !phone.checkValidity()) {
        showToast("رقم الهاتف غير صالح. استخدم 10 أرقام تبدأ بـ 05 أو 06 أو 07.", true);
        return;
      }
      var btn = document.getElementById("submitBtn");
      btn.disabled = true;
      btn.textContent = "جاري الإرسال...";
      var fd = new FormData(orderForm);
      fetch("submit-order.php", {
        method: "POST",
        body: fd,
        headers: { "X-Requested-With": "XMLHttpRequest" },
      })
        .then(function (r) {
          return r.json();
        })
        .then(function (data) {
          if (data.success) {
            orderForm.reset();
            updateTotal();
            if (bankInfo) bankInfo.hidden = true;
            showToast(data.message, false);
          } else {
            showToast(data.message || "حدث خطأ.", true);
          }
        })
        .catch(function () {
          showToast("تعذر الاتصال. حاول مجدداً.", true);
        })
        .finally(function () {
          btn.disabled = false;
          btn.textContent = "إرسال الطلب";
        });
    });
  }

  /* Newsletter */
  var nl = document.getElementById("newsletterForm");
  if (nl) {
    nl.addEventListener("submit", function (e) {
      e.preventDefault();
      var fd = new FormData(nl);
      fetch("newsletter.php", { method: "POST", body: fd })
        .then(function (r) {
          return r.json();
        })
        .then(function (d) {
          showToast(d.message, !d.success);
          if (d.success) nl.reset();
        });
    });
  }
})();

/* PWA Install Button */
(function () {
  var deferredPrompt = null;
  var installBtn = document.getElementById("installAppBtn");

  window.addEventListener("beforeinstallprompt", function (e) {
    e.preventDefault();
    deferredPrompt = e;
    if (installBtn) installBtn.hidden = false;
  });

  if (installBtn) {
    installBtn.addEventListener("click", function () {
      if (!deferredPrompt) return;
      deferredPrompt.prompt();
      deferredPrompt.userChoice.then(function () {
        deferredPrompt = null;
        installBtn.hidden = true;
      });
    });
  }

  window.addEventListener("appinstalled", function () {
    if (installBtn) installBtn.hidden = true;
  });
})();
