<?php
$page_title = 'Book a Table';
require_once 'config/config.php';
require_once 'includes/functions.php';

$message = '';
$message_type = '';

$settings = getRestaurantSettings($conn);
$booking_window = getBookingTimeWindow($settings);

$selected_table_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : 0;
$selected_table = null;

if ($selected_table_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM restaurant_tables WHERE id = ? AND status = 'active'");
    $stmt->execute([$selected_table_id]);
    $selected_table = $stmt->fetch();
}

if (!$selected_table) {
    redirect('tables.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $booking_date = sanitize($_POST['booking_date']);
    $booking_time = sanitize($_POST['booking_time']);
    $guest_count = intval($_POST['guest_count']);
    $special_request = sanitize($_POST['special_request']);
    $table_id = intval($_POST['table_id']);
    $parsed_booking_date = DateTime::createFromFormat('!Y-m-d', $booking_date);
    $valid_booking_date = $parsed_booking_date && $parsed_booking_date->format('Y-m-d') === $booking_date;
    $valid_booking_time = (bool)preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', $booking_time);

    if (empty($booking_date) || empty($booking_time) || empty($guest_count)) {
        $message = 'Please fill in all required fields.';
        $message_type = 'danger';
    } elseif ($table_id !== $selected_table_id) {
        $message = 'Please select a table again before submitting.';
        $message_type = 'danger';
    } elseif (!$valid_booking_date || !$valid_booking_time) {
        $message = 'Please enter a valid date and time.';
        $message_type = 'danger';
    } elseif (!isBookableTime($booking_time, $booking_window)) {
        $message = 'Please choose a booking time between ' . formatTime($booking_window['opening']) . ' and ' . formatTime($booking_window['latest_start']) . '.';
        $message_type = 'danger';
    } elseif (strtotime($booking_date) < strtotime(date('Y-m-d'))) {
        $message = 'Booking date cannot be in the past.';
        $message_type = 'danger';
    } elseif ($booking_date === date('Y-m-d') && $booking_time <= date('H:i')) {
        $message = 'Please choose a future booking time for today.';
        $message_type = 'danger';
    } elseif ($guest_count < 1) {
        $message = 'Number of guests must be at least 1.';
        $message_type = 'danger';
    } elseif ($guest_count > $selected_table['capacity']) {
        $message = 'This table can accommodate a maximum of ' . $selected_table['capacity'] . ' guests.';
        $message_type = 'danger';
    } elseif (!$settings['allow_online_booking']) {
        $message = 'Online booking is currently disabled.';
        $message_type = 'danger';
    } elseif (!isLoggedIn()) {
        $_SESSION['booking_data'] = $_POST;
        $_SESSION['redirect_after_login'] = 'reservation.php?table_id=' . $table_id;
        header("Location: login.php");
        exit();
    } else {
        $booking_duration = max(1, (int)$settings['booking_duration']);
        $start_at = new DateTimeImmutable($booking_date . ' ' . $booking_time);
        $end_at = $start_at->modify('+' . $booking_duration . ' minutes');

        $stmt = $conn->prepare("
            SELECT COUNT(*) as count
            FROM bookings
            WHERE table_id = ?
            AND status IN ('pending', 'confirmed')
            AND TIMESTAMP(booking_date, booking_time) < ?
            AND TIMESTAMPADD(MINUTE, ?, TIMESTAMP(booking_date, booking_time)) > ?
        ");
        $stmt->execute([$table_id, $end_at->format('Y-m-d H:i:s'), $booking_duration, $start_at->format('Y-m-d H:i:s')]);
        $conflict = $stmt->fetch();

        if ($conflict['count'] > 0) {
            $message = 'This table is not available for the selected date and time. Please choose a different time.';
            $message_type = 'danger';
        } else {
            $booking_code = generateBookingCode();

            $stmt = $conn->prepare("
                INSERT INTO bookings (booking_code, user_id, table_id, booking_date, booking_time, guest_count, special_request, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
            ");

            if ($stmt->execute([$booking_code, $_SESSION['user_id'], $table_id, $booking_date, $booking_time, $guest_count, $special_request])) {
                unset($_SESSION['booking_data']);
                unset($_SESSION['redirect_after_login']);
                $message = 'Reservation submitted successfully! Your booking code is: ' . $booking_code;
                $message_type = 'success';
                header("refresh:3;url=my-bookings.php");
            } else {
                $message = 'Failed to submit booking. Please try again.';
                $message_type = 'danger';
            }
        }
    }
}

$prefill_data = $_SESSION['booking_data'] ?? [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $message_type === 'danger') {
    $prefill_data = array_merge($prefill_data, $_POST);
}
$requested_date = trim((string)($_GET['date'] ?? ''));
$parsed_date = DateTime::createFromFormat('!Y-m-d', $requested_date);
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $parsed_date && $parsed_date->format('Y-m-d') === $requested_date && $requested_date >= date('Y-m-d')) {
    $prefill_data['booking_date'] = $requested_date;
}
$requested_time = trim((string)($_GET['time'] ?? ''));
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', $requested_time)) {
    $prefill_data['booking_time'] = $requested_time;
}
$requested_guests = filter_input(INPUT_GET, 'guests', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => $selected_table['capacity']]]);
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $requested_guests) {
    $prefill_data['guest_count'] = $requested_guests;
}
$return_query = http_build_query(array_filter([
    'date' => $prefill_data['booking_date'] ?? '',
    'time' => $prefill_data['booking_time'] ?? '',
    'guests' => $prefill_data['guest_count'] ?? null
]));
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = 'reservation.php?table_id=' . $selected_table_id . ($return_query ? '&' . $return_query : '');
}
require_once 'includes/header.php';
?>

<section class="section" style="background-color: var(--background);">
    <div class="container">
        <div class="section-title">
            <span class="eyebrow">Almost there</span>
            <h1>Complete your reservation.</h1>
            <p>Review the details for Table <?php echo str_pad($selected_table['table_number'], 2, '0', STR_PAD_LEFT); ?>, then send your request.</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="table-summary-card">
                    <div class="table-summary-header">
                        <h3>Your Selected Table</h3>
                    </div>
                    <div class="table-summary-content">
                        <div class="table-summary-diagram">
                            <?php echo tableDiagramHtml((int)$selected_table['capacity']); ?>
                        </div>
                        <p class="table-summary-diagram-note">Seating layout illustration</p>
                        <h4>TABLE <?php echo str_pad($selected_table['table_number'], 2, '0', STR_PAD_LEFT); ?></h4>
                        <p class="table-name-small"><?php echo htmlspecialchars($selected_table['table_name']); ?></p>
                        <div class="table-details-small">
                            <p><i class="bi bi-people"></i> Capacity: <?php echo $selected_table['capacity']; ?> Guests</p>
                            <p><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($selected_table['location']); ?></p>
                        </div>
                        <div class="table-status-small">
                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                            <span class="status-text">Availability checked on submission</span>
                        </div>
                        <a href="tables.php<?php echo $return_query ? '?' . htmlspecialchars($return_query, ENT_QUOTES) : ''; ?>" class="btn-change-table">
                            <i class="bi bi-arrow-left"></i> Change Table
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="reservation-form">
                    <h3>Book Table <?php echo str_pad($selected_table['table_number'], 2, '0', STR_PAD_LEFT); ?></h3>

                    <?php if (!isLoggedIn()): ?>
                    <div class="alert alert-info">
                        <h4><i class="bi bi-lock"></i> Login Required</h4>
                        <p>Please login to complete your table reservation.</p>
                        <a href="login.php" class="btn btn-primary">Login Now</a>
                        <a href="register.php" class="btn btn-outline ms-2">Register Account</a>
                    </div>
                    <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="table_id" value="<?php echo $selected_table['id']; ?>">

                        <div class="customer-info-section">
                            <h4>Your Information</h4>
                            <div class="customer-details">
                                <p><strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></p>
                                <p><?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="booking_date">Booking date *</label>
                            <input id="booking_date" type="date" class="form-control" name="booking_date"
                                   value="<?php echo isset($prefill_data['booking_date']) ? htmlspecialchars($prefill_data['booking_date']) : ''; ?>"
                                   min="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="booking_time">Booking time *</label>
                            <input id="booking_time" type="time" class="form-control" name="booking_time"
                                   value="<?php echo isset($prefill_data['booking_time']) ? htmlspecialchars($prefill_data['booking_time']) : ''; ?>"
                                   <?php if (!$booking_window['overnight']): ?>min="<?php echo htmlspecialchars($booking_window['opening'], ENT_QUOTES, 'UTF-8'); ?>"
                                   max="<?php echo htmlspecialchars($booking_window['latest_start'], ENT_QUOTES, 'UTF-8'); ?>"<?php endif; ?> required>
                            <small class="text-secondary">Booking times: <?php echo formatTime($booking_window['opening']); ?> - <?php echo formatTime($booking_window['latest_start']); ?></small>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="guest_count">Number of guests *</label>
                            <input id="guest_count" type="number" class="form-control" name="guest_count"
                                   value="<?php echo isset($prefill_data['guest_count']) ? htmlspecialchars($prefill_data['guest_count']) : '2'; ?>"
                                   min="1" max="<?php echo $selected_table['capacity']; ?>" required>
                            <small class="text-secondary">Maximum <?php echo $selected_table['capacity']; ?> guests for this table, including children</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="special_request">Special request</label>
                            <textarea id="special_request" class="form-control" name="special_request" rows="3"
                                      placeholder="Any special requirements or requests..."><?php echo isset($prefill_data['special_request']) ? htmlspecialchars($prefill_data['special_request']) : ''; ?></textarea>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="termsCheck" required>
                                <label class="form-check-label" for="termsCheck">
                                    I agree to the restaurant's booking terms and conditions
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn-reserve">Book Table</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.table-summary-card {
    background-color: var(--card-background);
    border-radius: var(--radius-lg);
    border: 2px solid var(--border-color);
    padding: 2rem;
    box-shadow: var(--shadow-sm);
    position: sticky;
    top: 100px;
}

.table-summary-header h3 {
    color: var(--dark-teal);
    margin-bottom: 1.5rem;
    text-align: center;
}

.table-summary-content {
    text-align: center;
}

.table-summary-diagram {
    display: grid;
    place-items: center;
    height: 214px;
    overflow: hidden;
    background: radial-gradient(circle at 50% 42%, #fff 0, #e9f1ff 72%);
    border: 1px solid #d9e5f8;
    border-radius: 12px;
    margin-bottom: .4rem;
}
.table-summary-diagram-note {
    color: var(--text-light);
    font-size: .72rem;
    margin: 0 0 1.2rem;
    text-align: right;
}
.table-summary-content h4 {
    font-size: 1.3rem;
    color: var(--dark-teal);
    margin-bottom: 0.5rem;
}

.table-name-small {
    color: var(--text-gray);
    font-size: 0.95rem;
    margin-bottom: 1rem;
}

.table-details-small {
    margin-bottom: 1rem;
}

.table-details-small p {
    color: var(--text-gray);
    font-size: 0.85rem;
    margin: 0.4rem 0;
}

.table-details-small i {
    color: var(--primary-teal);
    margin-right: 0.5rem;
}

.table-status-small {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.status-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: var(--success);
}

.status-text {
    color: var(--success);
    font-weight: 500;
    font-size: 0.85rem;
}

.btn-change-table {
    display: inline-block;
    background-color: transparent;
    color: var(--primary-teal);
    padding: 0.6rem 1.5rem;
    border: 2px solid var(--primary-teal);
    border-radius: var(--radius-pill);
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-change-table:hover {
    background-color: var(--primary-teal);
    color: white;
}

.reservation-form {
    background-color: var(--card-background);
    padding: 2.5rem;
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
}

.reservation-form h3 {
    color: var(--dark-teal);
    margin-bottom: 2rem;
    text-align: center;
}

.customer-info-section {
    background-color: var(--soft-green);
    padding: 1.5rem;
    border-radius: var(--radius-md);
    margin-bottom: 2rem;
}

.customer-info-section h4 {
    color: var(--dark-teal);
    margin-bottom: 1rem;
    font-size: 1rem;
}

.customer-details p {
    color: var(--text-gray);
    margin: 0.3rem 0;
}

.customer-details p strong {
    color: var(--dark-teal);
}

.btn-reserve {
    background-color: var(--primary-teal);
    color: white;
    padding: 1rem 2rem;
    border: none;
    border-radius: var(--radius-pill);
    font-weight: 600;
    width: 100%;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    box-shadow: var(--shadow-md);
}

.btn-reserve:hover {
    background-color: var(--dark-teal);
    color: white;
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

@media (max-width: 992px) {
    .table-summary-card {
        position: static;
        margin-bottom: 2rem;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
