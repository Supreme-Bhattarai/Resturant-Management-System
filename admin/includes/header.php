<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#142b59">
    <title><?php echo isset($page_title) ? $page_title . ' - Admin' : 'Admin Dashboard'; ?> - 4 TO 9</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin.css?v=<?php echo filemtime(__DIR__ . '/../../assets/css/admin.css'); ?>">
    <?php if (!empty($admin_stylesheet) && is_file(__DIR__ . '/../../' . $admin_stylesheet)): ?>
    <link rel="stylesheet" href="<?php echo SITE_URL . '/' . htmlspecialchars($admin_stylesheet, ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo filemtime(__DIR__ . '/../../' . $admin_stylesheet); ?>">
    <?php endif; ?>
</head>
<body>
    <a class="skip-link" href="#admin-content">Skip to main content</a>
    <div class="admin-layout">
        <?php include __DIR__ . '/sidebar.php'; ?>
        <div class="admin-main">
            <?php include __DIR__ . '/navbar.php'; ?>
