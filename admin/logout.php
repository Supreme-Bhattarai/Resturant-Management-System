<?php
require_once '../config/config.php';

$_SESSION = array();

if (isset($_COOKIE[session_name()])) {
    $cookie = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
}

session_destroy();

header('Location: ' . SITE_URL . '/login.php');
exit();
?>
