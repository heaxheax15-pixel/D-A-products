<?php
/* Self-contained: safe to include from any page without pre-defining variables */
$_waNum = $waNum ?? runtime('CONTACT_WHATSAPP', CONTACT_WHATSAPP);
$_waMsg = $waMsg ?? urlencode(runtime('WHATSAPP_GREETING', WHATSAPP_GREETING));
?>
<a href="https://wa.me/<?= e($_waNum) ?>?text=<?= $_waMsg ?>" class="whatsapp-float" target="_blank" rel="noopener" aria-label="تواصل عبر واتساب">
    <svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor" aria-hidden="true"><path d="M20.52 3.48A11.9 11.9 0 0 0 12 0C5.4 0 0 5.4 0 12a11.9 11.9 0 0 0 1.7 6.05L0 24l5.95-1.7A11.9 11.9 0 0 0 12 24c6.6 0 12-5.4 12-12 0-3.2-1.3-6.2-3.48-8.52zM12 22c-1.7 0-3.4-.45-4.9-1.3l-.35-.2-3.6 1 1-3.5-.25-.35A9.9 9.9 0 0 1 2 12c0-5.5 4.5-10 10-10s10 4.5 10 10-4.5 10-10 10zm5.5-7.5c-.3-.15-1.8-.9-2.05-1-.3-.1-.5-.15-.7.15s-.8 1-.95 1.2-.35.15-.65 0a8.2 8.2 0 0 1-2.4-1.5 9 9 0 0 1-1.7-2.1c-.15-.3 0-.45.1-.6.1-.1.35-.4.5-.6.15-.2.2-.35.3-.55.1-.2 0-.4 0-.55s-.7-1.7-1-2.3c-.25-.6-.5-.5-.7-.5h-.6c-.2 0-.55.1-.85.4-.3.3-1.1 1.1-1.1 2.7s1.15 3.1 1.3 3.3c.15.2 2.25 3.4 5.45 4.8.75.3 1.35.5 1.8.65.75.25 1.45.2 2 .15.6-.1 1.8-.75 2.05-1.45.25-.7.25-1.35.15-1.45-.1-.15-.3-.25-.6-.4z"/></svg>
</a>
<?php unset($_waNum, $_waMsg); ?>
