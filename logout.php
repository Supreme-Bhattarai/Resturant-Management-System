<?php
require_once 'config/config.php';

session_start();

$_SESSION = array();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

session_destroy();

header('Location: ' . SITE_URL . '/login.php');
exit();
?>
