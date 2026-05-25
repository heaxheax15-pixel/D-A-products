<?php
declare(strict_types=1);

function pwa_head_tags(): void
{
    $base = app_base();
    $manifest = ($base !== '' ? $base . '/' : '/') . 'manifest.php';
    ?>
    <link rel="manifest" href="<?= e($manifest) ?>">
    <meta name="theme-color" content="#F5A623">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="D&A">
    <link rel="apple-touch-icon" href="<?= e(($base !== '' ? $base . '/' : '/') . 'icons/icon-192.png') ?>">
    <?php
}

function pwa_register_script(): void
{
    $sw = e(app_url('service-worker.js'));
    $base = e(app_base());
    ?>
    <script>
    window.DA_BASE = <?= json_encode(app_base(), JSON_UNESCAPED_UNICODE) ?>;
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', function () {
        navigator.serviceWorker.register('<?= $sw ?>', { scope: '<?= $base !== '' ? $base . '/' : '/' ?>' }).catch(function (err) {
          console.warn('SW register failed', err);
        });
      });
    }
    </script>
    <?php
}
