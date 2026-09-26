<?php
require_once __DIR__ . '/functions.php';

$footerSettings = getRestaurantSettings($conn) ?: [];
$footerPhone = trim($footerSettings['phone'] ?? '');
$footerEmail = trim($footerSettings['email'] ?? '');
$footerAddress = trim($footerSettings['address'] ?? '');
$footerOpening = !empty($footerSettings['opening_time']) ? formatTime($footerSettings['opening_time']) : '';
$footerClosing = !empty($footerSettings['closing_time']) ? formatTime($footerSettings['closing_time']) : '';
?>
</main>
<footer class="footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <p class="footer-kicker">Gather around good food</p>
                <h2 class="visually-hidden">4 TO 9 Restaurant</h2>
                <img class="footer-logo" src="<?php echo SITE_URL; ?>/assets/images/logo-4to9.png" alt="" width="245" height="163" loading="lazy" decoding="async">
                <p class="footer-text">Nepali flavors, thoughtful cooking, and a warm place to share a meal.</p>
                <a class="btn-book footer-booking-link" href="tables.php">Reserve a table <i class="bi bi-arrow-up-right ms-1" aria-hidden="true"></i></a>
            </div>
            <div class="col-lg-2 col-md-6">
                <h2 class="footer-heading">Explore</h2>
                <ul class="footer-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="menu.php">Menu</a></li>
                    <li><a href="menu.php#kids">Kids menu</a></li>
                    <li><a href="about.php">Our story</a></li>
                    <li><a href="gallery.php">Gallery</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-md-6">
                <h2 class="footer-heading">Your visit</h2>
                <ul class="footer-links">
                    <li><a href="tables.php">Book a table</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="my-bookings.php">My bookings</a></li>
                        <li><a href="profile.php">My profile</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Sign in</a></li>
                        <li><a href="register.php">Create an account</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-lg-4 col-md-6">
                <h2 class="footer-heading">Find us</h2>
                <address class="footer-text">
                    <?php if ($footerAddress !== ''): ?>
                        <span><i class="bi bi-geo-alt me-2" aria-hidden="true"></i><?php echo nl2br(htmlspecialchars($footerAddress, ENT_QUOTES, 'UTF-8')); ?></span><br>
                    <?php endif; ?>
                    <?php if ($footerPhone !== ''): ?>
                        <a class="footer-contact-link" href="tel:<?php echo htmlspecialchars(preg_replace('/[^+0-9]/', '', $footerPhone), ENT_QUOTES, 'UTF-8'); ?>"><i class="bi bi-telephone me-2" aria-hidden="true"></i><?php echo htmlspecialchars($footerPhone, ENT_QUOTES, 'UTF-8'); ?></a><br>
                    <?php endif; ?>
                    <?php if (filter_var($footerEmail, FILTER_VALIDATE_EMAIL)): ?>
                        <a class="footer-contact-link" href="mailto:<?php echo htmlspecialchars($footerEmail, ENT_QUOTES, 'UTF-8'); ?>"><i class="bi bi-envelope me-2" aria-hidden="true"></i><?php echo htmlspecialchars($footerEmail, ENT_QUOTES, 'UTF-8'); ?></a>
                    <?php endif; ?>
                </address>
                <?php if ($footerOpening !== '' && $footerClosing !== ''): ?>
                    <h3 class="footer-heading footer-hours-heading">Opening hours</h3>
                    <p class="footer-text mb-0">Daily, <?php echo htmlspecialchars($footerOpening, ENT_QUOTES, 'UTF-8'); ?> &ndash; <?php echo htmlspecialchars($footerClosing, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> 4 TO 9 Restaurant. All rights reserved.</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo SITE_URL; ?>/assets/js/script.js"></script>
</body>
</html>
