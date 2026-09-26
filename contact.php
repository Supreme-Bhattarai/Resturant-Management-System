<?php
$page_title = 'Contact';
require_once 'config/config.php';
require_once 'includes/functions.php';
$settings = getRestaurantSettings($conn) ?: [];
$contact_phone = trim($settings['phone'] ?? '');
$contact_email = trim($settings['email'] ?? '');
$contact_address = trim($settings['address'] ?? '');
$opening_time = !empty($settings['opening_time']) ? formatTime($settings['opening_time']) : '';
$closing_time = !empty($settings['closing_time']) ? formatTime($settings['closing_time']) : '';
require_once 'includes/header.php';
?>

<section class="section contact-page" aria-labelledby="contact-title">
    <div class="container">
        <div class="section-title">
            <span class="eyebrow">Get in touch</span>
            <h1 id="contact-title">We’d love to hear from you.</h1>
            <p>Have a question about a visit, a booking, or the menu? Reach us directly.</p>
        </div>

        <div class="contact-page-grid">
            <div class="contact-lead-card">
                <span class="eyebrow">Let's talk</span>
                <h2>A good visit starts with a conversation.</h2>
                <p>Call or email us and our team will help with the details. Planning for a group? Tell us what you have in mind.</p>
                <div class="contact-actions">
                    <?php if ($contact_phone !== ''): ?>
                        <a class="btn-hero" href="tel:<?php echo htmlspecialchars(preg_replace('/[^+0-9]/', '', $contact_phone), ENT_QUOTES); ?>"><i class="bi bi-telephone" aria-hidden="true"></i> Call us</a>
                    <?php endif; ?>
                    <?php if (filter_var($contact_email, FILTER_VALIDATE_EMAIL)): ?>
                        <a class="btn-hero-outline" href="mailto:<?php echo htmlspecialchars($contact_email, ENT_QUOTES); ?>"><i class="bi bi-envelope" aria-hidden="true"></i> Send an email</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="contact-details-card">
                <?php if ($contact_address !== ''): ?>
                <div class="contact-detail"><i class="bi bi-geo-alt" aria-hidden="true"></i><div><h3>Visit us</h3><address><?php echo nl2br(htmlspecialchars($contact_address, ENT_QUOTES)); ?></address></div></div>
                <?php endif; ?>
                <?php if ($contact_phone !== ''): ?>
                <div class="contact-detail"><i class="bi bi-telephone" aria-hidden="true"></i><div><h3>Call</h3><a href="tel:<?php echo htmlspecialchars(preg_replace('/[^+0-9]/', '', $contact_phone), ENT_QUOTES); ?>"><?php echo htmlspecialchars($contact_phone, ENT_QUOTES); ?></a></div></div>
                <?php endif; ?>
                <?php if (filter_var($contact_email, FILTER_VALIDATE_EMAIL)): ?>
                <div class="contact-detail"><i class="bi bi-envelope" aria-hidden="true"></i><div><h3>Email</h3><a href="mailto:<?php echo htmlspecialchars($contact_email, ENT_QUOTES); ?>"><?php echo htmlspecialchars($contact_email, ENT_QUOTES); ?></a></div></div>
                <?php endif; ?>
                <?php if ($opening_time !== '' && $closing_time !== ''): ?>
                <div class="contact-detail"><i class="bi bi-clock" aria-hidden="true"></i><div><h3>Opening hours</h3><p>Every day, <?php echo htmlspecialchars($opening_time, ENT_QUOTES); ?>–<?php echo htmlspecialchars($closing_time, ENT_QUOTES); ?></p></div></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="contact-booking">
            <div><span class="eyebrow">Join us</span><h2>Know when you’re coming?</h2><p>Choose a table and send your reservation request in a few steps.</p></div>
            <a href="tables.php" class="btn-hero">Find a table <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
