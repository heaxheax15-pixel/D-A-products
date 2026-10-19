    <!-- ========== SCRIPTS ========== -->
    <script>window.DA_CSRF = <?= json_encode(csrf_token(), JSON_UNESCAPED_UNICODE) ?>;</script>
    <script src="<?= asset('assets/js/main.min.js') ?>" defer></script>
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
