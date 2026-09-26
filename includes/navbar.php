<?php
require_once __DIR__ . '/functions.php';

$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
$bookingPages = ['tables.php', 'select-table.php', 'reservation.php'];
$accountPages = ['my-bookings.php', 'booking-details.php', 'profile.php'];
$isBookingPage = in_array($currentPage, $bookingPages, true);
$isAccountPage = in_array($currentPage, $accountPages, true);
?>
<nav class="navbar navbar-expand-lg" id="mainNavbar" aria-label="Main navigation">
    <div class="container">
        <a class="navbar-brand" href="index.php" aria-label="4 TO 9 Restaurant, home">
            <img class="brand-logo" src="<?php echo SITE_URL; ?>/assets/images/logo-4to9.png" alt="" width="108" height="72">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon" aria-hidden="true"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link<?php echo $currentPage === 'index.php' ? ' active' : ''; ?>" href="index.php"<?php echo $currentPage === 'index.php' ? ' aria-current="page"' : ''; ?>>Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?php echo $currentPage === 'menu.php' ? ' active' : ''; ?>" href="menu.php"<?php echo $currentPage === 'menu.php' ? ' aria-current="page"' : ''; ?>>Menu</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="menu.php#kids">Kids</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?php echo $currentPage === 'about.php' ? ' active' : ''; ?>" href="about.php"<?php echo $currentPage === 'about.php' ? ' aria-current="page"' : ''; ?>>About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?php echo $currentPage === 'gallery.php' ? ' active' : ''; ?>" href="gallery.php"<?php echo $currentPage === 'gallery.php' ? ' aria-current="page"' : ''; ?>>Gallery</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?php echo $currentPage === 'contact.php' ? ' active' : ''; ?>" href="contact.php"<?php echo $currentPage === 'contact.php' ? ' aria-current="page"' : ''; ?>>Contact</a>
                </li>
                <li class="nav-item nav-booking">
                    <a class="nav-link btn-book<?php echo $isBookingPage ? ' active' : ''; ?>" href="tables.php"<?php echo $currentPage === 'tables.php' ? ' aria-current="page"' : ''; ?>>
                        <i class="bi bi-calendar-check me-2" aria-hidden="true"></i>Book a Table
                    </a>
                </li>
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item dropdown">
                        <button class="nav-link dropdown-toggle<?php echo $isAccountPage ? ' active' : ''; ?>" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                            Account <i class="bi bi-chevron-down ms-1" aria-hidden="true"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (isAdmin()): ?>
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/dashboard.php"><i class="bi bi-grid-1x2 me-2" aria-hidden="true"></i>Admin dashboard</a></li>
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/menu-catalog.php"><i class="bi bi-book-half me-2" aria-hidden="true"></i>Manage menu catalog</a></li>
                            <?php else: ?>
                            <li><a class="dropdown-item<?php echo in_array($currentPage, ['my-bookings.php', 'booking-details.php'], true) ? ' active' : ''; ?>" href="my-bookings.php"<?php echo $currentPage === 'my-bookings.php' ? ' aria-current="page"' : ''; ?>>My Bookings</a></li>
                            <li><a class="dropdown-item<?php echo $currentPage === 'profile.php' ? ' active' : ''; ?>" href="profile.php"<?php echo $currentPage === 'profile.php' ? ' aria-current="page"' : ''; ?>>Profile</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Log out</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link<?php echo $currentPage === 'login.php' ? ' active' : ''; ?>" href="login.php"<?php echo $currentPage === 'login.php' ? ' aria-current="page"' : ''; ?>>Sign in</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
