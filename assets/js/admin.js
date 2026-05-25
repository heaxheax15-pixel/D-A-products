(function () {
  "use strict";

  var btn = document.getElementById("adminMenuBtn");
  var sidebar = document.getElementById("adminSidebar");
  if (btn && sidebar) {
    btn.addEventListener("click", function () {
      sidebar.classList.toggle("open");
    });
  }

  var installBtn = document.getElementById("installPwaBtn");
  var deferredPrompt = null;

  window.addEventListener("beforeinstallprompt", function (e) {
    e.preventDefault();
    deferredPrompt = e;
    if (installBtn) {
      installBtn.hidden = false;
    }
  });

  if (installBtn) {
    installBtn.addEventListener("click", function () {
      if (!deferredPrompt) {
        alert(
          "لتثبيت التطبيق:\n\n" +
            "• أندرويد (Chrome): القائمة ⋮ ← «إضافة إلى الشاشة الرئيسية»\n" +
            "• iPhone (Safari): زر المشاركة ← «إضافة إلى الشاشة الرئيسية»\n\n" +
            "يفضّل فتح الرابط عبر HTTPS."
        );
        return;
      }
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
