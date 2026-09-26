<?php require_once __DIR__ . '/../config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#142b59">
    <meta name="description" content="Gather around good food at 4 TO 9. Explore our menu and reserve a table for your next visit.">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/style.css'); ?>">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/experience.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/experience.css'); ?>">
    <?php
    $page_css_files = !empty($page_stylesheet) ? [(string)$page_stylesheet] : [];
    if (!empty($page_stylesheets) && is_array($page_stylesheets)) {
        $page_css_files = array_merge($page_css_files, $page_stylesheets);
    }
    foreach (array_unique($page_css_files) as $page_css_file):
        $page_css_path = ltrim((string)$page_css_file, '/');
        $page_css_local = __DIR__ . '/../' . $page_css_path;
    ?>
    <link rel="stylesheet" href="<?php echo SITE_URL . '/' . htmlspecialchars($page_css_path, ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo is_file($page_css_local) ? filemtime($page_css_local) : 1; ?>">
    <?php endforeach; ?>
    <link rel="icon" type="image/svg+xml" href="<?php echo SITE_URL; ?>/assets/images/favicon.svg">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
    <a class="skip-link" href="#main-content">Skip to content</a>
    <?php include __DIR__ . '/navbar.php'; ?>
    <main id="main-content">
