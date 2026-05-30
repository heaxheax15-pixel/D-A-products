<?php
declare(strict_types=1);

function pwa_head_tags(): void
{
    $base = app_base();
    $root = $base !== '' ? $base . '/' : '/';
    $manifest = app_url('manifest.json');
    ?>
    <link rel="manifest" href="<?= e($manifest) ?>">
    <meta name="theme-color" content="#ffffff">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="D&A">
    <link rel="apple-touch-icon" href="<?= e($root . 'icons/icon-192.png') ?>">
    <?php
}

function pwa_register_script(): void
{
    $swUrl = json_encode(app_url('service-worker.js'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $scope = json_encode(app_base() !== '' ? app_base() . '/' : '/', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $base = json_encode(app_base(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    ?>
    <script>
    (function () {
      window.DA_BASE = <?= $base ?>;
      if (!('serviceWorker' in navigator)) return;

      var swUrl = <?= $swUrl ?>;
      var scope = <?= $scope ?>;

      function registerServiceWorker() {
        navigator.serviceWorker.register(swUrl, { scope: scope })
          .then(function (registration) {
            if (registration.installing) {
              registration.installing.addEventListener('statechange', function (event) {
                if (event.target && event.target.state === 'installed' && navigator.serviceWorker.controller) {
                  event.target.postMessage({ type: 'SKIP_WAITING' });
                }
              });
            }
          })
          .catch(function (err) {
            console.warn('[D&A PWA] Service worker registration failed:', err);
          });
      }

      if (document.readyState === 'loading') {
        window.addEventListener('load', registerServiceWorker);
      } else {
        registerServiceWorker();
      }
    })();
    </script>
    <?php
}
