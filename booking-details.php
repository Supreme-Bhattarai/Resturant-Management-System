<?php
$page_title = 'Booking Details';
$page_stylesheet = 'assets/css/account.css';
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$booking_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;

$stmt = $conn->prepare("
    SELECT b.*, t.table_number, t.table_name, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone
    FROM bookings b
    LEFT JOIN restaurant_tables t ON b.table_id = t.id
    LEFT JOIN users u ON b.user_id = u.id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    redirect('my-bookings.php');
}

$status_content = [
    'pending' => ['icon' => 'bi-hourglass-split', 'title' => 'Awaiting confirmation', 'description' => 'We have your request. The restaurant will confirm your table shortly.'],
    'confirmed' => ['icon' => 'bi-check-circle', 'title' => 'Your table is confirmed', 'description' => 'Your reservation is set. We look forward to welcoming you.'],
    'rejected' => ['icon' => 'bi-exclamation-circle', 'title' => 'Reservation not available', 'description' => 'We could not accept this request. Please choose another table or time, or contact us.'],
    'cancelled' => ['icon' => 'bi-x-circle', 'title' => 'Reservation cancelled', 'description' => 'This booking has been cancelled. You can make a new reservation at any time.'],
    'completed' => ['icon' => 'bi-heart', 'title' => 'Visit completed', 'description' => 'Thank you for dining with us. We hope to see you again soon.'],
];
$status = array_key_exists($booking['status'], $status_content) ? $booking['status'] : 'unknown';
$status_display = $status_content[$status] ?? [
    'icon' => 'bi-info-circle',
    'title' => 'Reservation update',
    'description' => 'Contact us if you need help with this booking.',
];
$settings = getRestaurantSettings($conn) ?: [];
$support_phone = trim($settings['phone'] ?? '');
$support_email = trim($settings['email'] ?? '');

require_once 'includes/header.php';
?>

<section class="section account-page booking-detail-page">
    <div class="container">
        <a class="account-back-link" href="my-bookings.php"><i class="bi bi-arrow-left" aria-hidden="true"></i> All bookings</a>
        <div class="section-title account-page-heading">
            <span class="eyebrow">Your visit</span>
            <h1>Booking details</h1>
            <p>Everything you need for your reservation, in one place.</p>
        </div>

        <div class="booking-detail-layout">
            <div class="booking-detail-main">
                <article class="booking-detail-card booking-detail-overview">
                    <header class="booking-detail-overview__header">
                        <div>
                            <span class="detail-overline">Reservation #<?php echo (int)$booking['id']; ?></span>
                            <h2><?php echo htmlspecialchars(formatDate($booking['booking_date']), ENT_QUOTES, 'UTF-8'); ?></h2>
                            <p>Booking code <strong><?php echo htmlspecialchars((string)$booking['booking_code'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
                        </div>
                        <span class="status-badge <?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(ucfirst((string)$booking['status']), ENT_QUOTES, 'UTF-8'); ?></span>
                    </header>

                    <div class="booking-detail-facts">
                        <div class="booking-detail-fact"><i class="bi bi-clock" aria-hidden="true"></i><div><span>Arrival time</span><strong><?php echo htmlspecialchars(formatTime($booking['booking_time']), ENT_QUOTES, 'UTF-8'); ?></strong></div></div>
                        <div class="booking-detail-fact"><i class="bi bi-people" aria-hidden="true"></i><div><span>Party size</span><strong><?php echo (int)$booking['guest_count']; ?> <?php echo (int)$booking['guest_count'] === 1 ? 'guest' : 'guests'; ?></strong></div></div>
                        <div class="booking-detail-fact"><i class="bi bi-grid-3x3" aria-hidden="true"></i><div><span>Table</span><strong><?php echo $booking['table_number'] ? 'Table ' . htmlspecialchars((string)$booking['table_number'], ENT_QUOTES, 'UTF-8') : 'To be assigned'; ?></strong></div></div>
                    </div>

                    <div class="booking-detail-section">
                        <h3>Guest details</h3>
                        <dl class="booking-detail-list">
                            <div><dt>Name</dt><dd><?php echo htmlspecialchars((string)$booking['customer_name'], ENT_QUOTES, 'UTF-8'); ?></dd></div>
                            <div><dt>Email</dt><dd><?php echo htmlspecialchars((string)$booking['customer_email'], ENT_QUOTES, 'UTF-8'); ?></dd></div>
                            <div><dt>Phone</dt><dd><?php echo htmlspecialchars((string)$booking['customer_phone'], ENT_QUOTES, 'UTF-8'); ?></dd></div>
                            <?php if (!empty($booking['table_name'])): ?>
                            <div><dt>Seating area</dt><dd><?php echo htmlspecialchars((string)$booking['table_name'], ENT_QUOTES, 'UTF-8'); ?></dd></div>
                            <?php endif; ?>
                            <div><dt>Booked on</dt><dd><?php echo htmlspecialchars(formatDate($booking['created_at']), ENT_QUOTES, 'UTF-8'); ?></dd></div>
                        </dl>
                    </div>

                    <?php if ($booking['special_request']): ?>
                    <div class="booking-detail-section">
                        <h3>Special request</h3>
                        <div class="booking-detail-note"><i class="bi bi-chat-quote" aria-hidden="true"></i><p><?php echo nl2br(htmlspecialchars((string)$booking['special_request'], ENT_QUOTES, 'UTF-8')); ?></p></div>
                    </div>
                    <?php endif; ?>

                    <?php if ($booking['admin_note']): ?>
                    <div class="booking-detail-section">
                        <h3>Note from the restaurant</h3>
                        <div class="booking-detail-note"><i class="bi bi-info-circle" aria-hidden="true"></i><p><?php echo nl2br(htmlspecialchars((string)$booking['admin_note'], ENT_QUOTES, 'UTF-8')); ?></p></div>
                    </div>
                    <?php endif; ?>
                </article>

                <div class="booking-detail-actions">
                    <a href="my-bookings.php" class="btn-hero-outline"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to bookings</a>
                    <?php if ($booking['status'] === 'pending' || $booking['status'] === 'confirmed'): ?>
                    <form method="POST" action="my-bookings.php" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                        <input type="hidden" name="booking_id" value="<?php echo (int)$booking['id']; ?>">
                        <button type="submit" name="cancel_booking" class="booking-cancel-button"><i class="bi bi-x-circle" aria-hidden="true"></i> Cancel booking</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <aside class="booking-detail-aside">
                <div class="booking-detail-card booking-detail-status booking-detail-status--<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>" role="status">
                    <span class="booking-detail-status__icon"><i class="bi <?php echo htmlspecialchars($status_display['icon'], ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i></span>
                    <h2><?php echo htmlspecialchars($status_display['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p><?php echo htmlspecialchars($status_display['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                </div>

                <div class="booking-detail-card booking-detail-help">
                    <span class="detail-overline">Here to help</span>
                    <h2>Questions about your visit?</h2>
                    <p>Get in touch and we will help with the details.</p>
                    <?php if ($support_phone !== ''): ?>
                    <a class="booking-detail-contact" href="tel:<?php echo htmlspecialchars(preg_replace('/[^+0-9]/', '', $support_phone), ENT_QUOTES, 'UTF-8'); ?>"><i class="bi bi-telephone" aria-hidden="true"></i><?php echo htmlspecialchars($support_phone, ENT_QUOTES, 'UTF-8'); ?></a>
                    <?php endif; ?>
                    <?php if (filter_var($support_email, FILTER_VALIDATE_EMAIL)): ?>
                    <a class="booking-detail-contact" href="mailto:<?php echo htmlspecialchars($support_email, ENT_QUOTES, 'UTF-8'); ?>"><i class="bi bi-envelope" aria-hidden="true"></i><?php echo htmlspecialchars($support_email, ENT_QUOTES, 'UTF-8'); ?></a>
                    <?php endif; ?>
                    <a href="contact.php" class="btn-hero">Contact us <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                </div>
            </aside>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
