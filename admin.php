<?php
declare(strict_types=1);

define('DA_APP', true);
require_once __DIR__ . '/includes/bootstrap.php';

admin_try_legacy_key();
header('Location: ' . (admin_logged_in() ? 'dashboard.php' : 'login.php'));
exit;
