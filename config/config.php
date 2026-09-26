<?php
define('SITE_NAME', '4 TO 9');
define('SITE_URL', 'http://localhost/Restaurantphp');
define('ADMIN_URL', SITE_URL . '/admin');

date_default_timezone_set('Asia/Kolkata');

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/database.php';
?>
