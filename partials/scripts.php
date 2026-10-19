<?php require __DIR__ . '/cart-drawer.php'; ?>
    <!-- ========== SCRIPTS ========== -->
    <script>window.DA_CSRF = <?= json_encode(csrf_token(), JSON_UNESCAPED_UNICODE) ?>;</script>
    <script src="<?= asset('assets/js/main.min.js') ?>" defer></script>
    <script src="<?= asset('assets/js/cart.js') ?>" defer></script>
    <script>
    (function(){
      var deferredPrompt=null;
      var btn=document.getElementById("installAppBtn");
      window.addEventListener("beforeinstallprompt",function(e){e.preventDefault();deferredPrompt=e;if(btn)btn.hidden=false;});
      if(btn){btn.addEventListener("click",function(){if(!deferredPrompt)return;deferredPrompt.prompt();deferredPrompt.userChoice.then(function(){deferredPrompt=null;btn.hidden=true;});});}
      window.addEventListener("appinstalled",function(){if(btn)btn.hidden=true;});
    })();
    </script>
    <?php pwa_register_script(); ?>
<script>
(function() {
  /* Anchor fallback: on non-index pages, #why/#products/etc do not exist.
     Intercept clicks and redirect to index.php#anchor. */
  var isIndex = /\/(index\.php)?(\?|#|$)/.test(location.pathname) || location.pathname === '/';
  if (isIndex) return;
  document.addEventListener('click', function(e) {
    var a = e.target.closest('a[href^="#"]');
    if (!a) return;
    var hash = a.getAttribute('href');
    if (hash.length < 2) return;
    if (document.getElementById(hash.slice(1))) return;
    e.preventDefault();
    window.location.href = 'index.php' + hash;
  });
})();
</script>
