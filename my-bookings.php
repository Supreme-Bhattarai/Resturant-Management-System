<?php
$page_title = 'My Bookings';
$page_stylesheet = 'assets/css/account.css';
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

if (isset($_POST['cancel_booking'])) {
    $booking_id = $_POST['booking_id'];

    $stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ? AND status IN ('pending', 'confirmed')");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch();

    if ($booking) {
        $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
        if ($stmt->execute([$booking_id])) {
            $message = 'Booking cancelled successfully.';
            $message_type = 'success';
        } else {
            $message = 'Failed to cancel booking.';
            $message_type = 'danger';
        }
    } else {
        $message = 'Booking cannot be cancelled.';
        $message_type = 'danger';
    }
}

$stmt = $conn->prepare("
    SELECT b.*, t.table_number, t.table_name, t.location
    FROM bookings b
    LEFT JOIN restaurant_tables t ON b.table_id = t.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<section class="section account-page bookings-page">
    <div class="container">
        <div class="section-title account-page-heading">
            <span class="eyebrow">Your visits</span>
            <h1>My bookings</h1>
            <p>Find reservation details and manage upcoming visits.</p>
        </div>

        <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <?php if (count($bookings) > 0): ?>
        <div class="bookings-grid">
            <?php foreach ($bookings as $booking): ?>
            <article class="booking-card">
                <div class="booking-header">
                    <div class="booking-number">
                        <span class="booking-label">Booking #</span>
                        <span class="booking-id"><?php echo (int)$booking['id']; ?></span>
                    </div>
                    <span class="status-badge <?php echo htmlspecialchars($booking['status'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars(ucfirst($booking['status']), ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>

                <div class="booking-body">
                    <div class="booking-info">
                        <div class="info-item">
                            <i class="bi bi-calendar-event" aria-hidden="true"></i>
                            <div>
                                <span class="info-label">Date</span>
                                <span class="info-value"><?php echo formatDate($booking['booking_date']); ?></span>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="bi bi-clock" aria-hidden="true"></i>
                            <div>
                                <span class="info-label">Time</span>
                                <span class="info-value"><?php echo formatTime($booking['booking_time']); ?></span>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="bi bi-people" aria-hidden="true"></i>
                            <div>
                                <span class="info-label">Guests</span>
                                <span class="info-value"><?php echo (int)$booking['guest_count']; ?></span>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="bi bi-grid-3x3" aria-hidden="true"></i>
                            <div>
                                <span class="info-label">Table</span>
                                <span class="info-value">
                                    <?php if ($booking['table_number']): ?>
                                        Table <?php echo htmlspecialchars($booking['table_number'], ENT_QUOTES, 'UTF-8'); ?>
                                        <?php if ($booking['location']): ?>
                                        <small class="location-text"><?php echo htmlspecialchars($booking['location']); ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        Not assigned
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <?php if ($booking['special_request']): ?>
                    <div class="special-request">
                        <i class="bi bi-chat-quote" aria-hidden="true"></i>
                        <div>
                            <span class="request-label">Special Request</span>
                            <span class="request-text"><?php echo htmlspecialchars($booking['special_request']); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($booking['admin_note']): ?>
                    <div class="admin-note">
                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                        <div>
                            <span class="note-label">Admin Note</span>
                            <span class="note-text"><?php echo htmlspecialchars($booking['admin_note']); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="booking-footer">
                    <div class="booking-meta">
                        <span class="booking-code">Code: <?php echo htmlspecialchars((string)$booking['booking_code'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="booking-date">Booked: <?php echo formatDate($booking['created_at']); ?></span>
                    </div>
                    <div class="booking-actions">
                        <a href="booking-details.php?id=<?php echo (int)$booking['id']; ?>" class="btn-action" aria-label="View details for booking <?php echo (int)$booking['id']; ?>">
                            <i class="bi bi-eye" aria-hidden="true"></i> View details
                        </a>

                        <?php if ($booking['status'] == 'pending' || $booking['status'] == 'confirmed'): ?>
                        <form method="POST" class="cancel-form" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                            <input type="hidden" name="booking_id" value="<?php echo (int)$booking['id']; ?>">
                            <button type="submit" name="cancel_booking" class="btn-action danger" aria-label="Cancel booking <?php echo (int)$booking['id']; ?>">
                                <i class="bi bi-x-circle" aria-hidden="true"></i> Cancel booking
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-calendar-plus empty-state-icon" aria-hidden="true"></i>
                <h2>No bookings yet</h2>
                <p>Choose a table and time for your first visit with us.</p>
                <a href="tables.php" class="btn-hero">Find a table</a>
            </div>
        <?php endif; ?>

        <?php if (count($bookings) > 0): ?>
        <div class="page-actions">
            <a href="tables.php" class="btn-hero"><i class="bi bi-calendar-plus" aria-hidden="true"></i> Make a new reservation</a>
            <a href="profile.php" class="btn-hero-outline"><i class="bi bi-person" aria-hidden="true"></i> My profile</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
.bookings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 390px), 1fr));
    gap: 1.25rem;
    margin-bottom: 2rem;
}

.booking-card {
    background: var(--card-background);
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    padding: 0;
    margin: 0;
    transition: all 0.3s ease;
}

.booking-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
}

.booking-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.3rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
    background: var(--soft-purple);
    margin: 0;
}

.booking-number {
    display: flex;
    flex-direction: column;
}

.booking-label {
    font-size: 0.85rem;
    color: var(--text-gray);
    font-weight: 500;
}

.booking-id {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--dark-blue);
}

.booking-body {
    padding: 1.5rem;
}

.booking-info {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.2rem;
    margin-bottom: 0;
}

.info-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.info-item i {
    color: var(--primary-blue);
    font-size: 1.3rem;
    margin-top: 0.2rem;
}

.info-item > div {
    display: flex;
    flex-direction: column;
}

.info-label {
    font-size: 0.85rem;
    color: var(--text-gray);
    font-weight: 500;
    margin-bottom: 0.3rem;
}

.info-value {
    font-size: 1rem;
    color: var(--dark-blue);
    font-weight: 600;
}

.location-text {
    display: block;
    font-size: 0.85rem;
    color: var(--text-gray);
    font-weight: 400;
    margin-top: 0.2rem;
}

.special-request {
    background: var(--soft-purple);
    border: 1px solid var(--border-color);
    padding: 1rem 1.1rem;
    border-radius: var(--radius-md);
    margin-top: 1.25rem;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.special-request i {
    color: var(--primary-blue);
    font-size: 1.2rem;
}

.request-label {
    font-size: 0.85rem;
    color: var(--dark-blue);
    font-weight: 600;
    display: block;
    margin-bottom: 0.3rem;
}

.request-text {
    font-size: 0.95rem;
    color: var(--text-dark);
    line-height: 1.5;
}

.admin-note {
    background: #e8eeff;
    border: 1px solid #c9d8fa;
    padding: 1rem 1.1rem;
    border-radius: var(--radius-md);
    margin-top: 1rem;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.admin-note i {
    color: var(--primary-blue);
    font-size: 1.2rem;
}

.note-label {
    font-size: 0.85rem;
    color: var(--dark-blue);
    font-weight: 600;
    display: block;
    margin-bottom: 0.3rem;
}

.note-text {
    font-size: 0.95rem;
    color: var(--text-dark);
    line-height: 1.5;
}

.booking-footer {
    padding: 1.2rem 1.5rem;
    border-top: 1px solid var(--border-color);
    background: var(--card-background);
}

.booking-meta {
    display: flex;
    flex-wrap: wrap;
    gap: .3rem 1rem;
    justify-content: space-between;
    margin-bottom: 1.1rem;
    font-size: 0.85rem;
    color: var(--text-gray);
}

.booking-actions {
    display: flex;
    flex-wrap: wrap;
    gap: .65rem;
}

.btn-action {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    justify-content: center;
    padding: .72rem 1.1rem;
    border-radius: var(--radius-md);
    border: 1px solid var(--primary-blue);
    background: transparent;
    color: var(--primary-blue);
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-action:hover {
    background: var(--primary-blue);
    color: white;
    transform: translateY(-2px);
}

.btn-action.danger {
    border-color: var(--danger);
    color: var(--danger);
}

.btn-action.danger:hover {
    background: var(--danger);
    color: white;
}

.cancel-form {
    display: inline;
}

.page-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

@media (max-width: 768px) {
    .bookings-grid {
        grid-template-columns: 1fr;
    }

    .booking-info {
        grid-template-columns: 1fr;
    }

    .booking-actions {
        flex-direction: column;
    }

    .booking-actions .btn-action,
    .booking-actions .cancel-form { width: 100%; }

    .page-actions {
        flex-direction: column;
    }

    .page-actions > a { width: 100%; }
}
</style>

<?php require_once 'includes/footer.php'; ?>
