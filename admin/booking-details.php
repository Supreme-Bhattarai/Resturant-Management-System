<?php
$page_title = 'Booking Details';
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once 'includes/auth.php';

$booking_id = filter_var(
    $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['booking_id'] ?? null) : ($_GET['id'] ?? null),
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);
$message = null;
$message_type = 'danger';

if (empty($_SESSION['admin_booking_csrf'])) {
    $_SESSION['admin_booking_csrf'] = bin2hex(random_bytes(32));
}

if ($booking_id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!is_string($_POST['csrf_token'] ?? null) ||
            !hash_equals($_SESSION['admin_booking_csrf'], $_POST['csrf_token'])) {
            throw new InvalidArgumentException('This form expired. Refresh the page and try again.');
        }

        $quick_status = $_POST['quick_status'] ?? null;
        $status = $quick_status !== null ? $quick_status : ($_POST['status'] ?? null);
        $valid_statuses = ['pending', 'confirmed', 'rejected', 'cancelled', 'completed'];
        if (!is_string($status) || !in_array($status, $valid_statuses, true)) {
            throw new InvalidArgumentException('Choose a valid booking status.');
        }

        $raw_table_id = $_POST['table_id'] ?? '';
        if (!is_string($raw_table_id)) {
            throw new InvalidArgumentException('Choose a valid table.');
        }
        $table_id = null;
        if ($raw_table_id !== '') {
            $table_id = filter_var($raw_table_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($table_id === false) {
                throw new InvalidArgumentException('Choose a valid table.');
            }
        }

        $admin_note = $_POST['admin_note'] ?? '';
        if (!is_string($admin_note) || strlen($admin_note) > 5000) {
            throw new InvalidArgumentException('The admin note must be shorter than 5,000 characters.');
        }
        $admin_note = trim($admin_note);

        $conn->beginTransaction();
        $stmt = $conn->prepare('SELECT * FROM bookings WHERE id = ? FOR UPDATE');
        $stmt->execute([$booking_id]);
        $current_booking = $stmt->fetch();
        if (!$current_booking) {
            throw new InvalidArgumentException('Booking not found.');
        }

        $settings = getRestaurantSettings($conn);
        $booking_duration = max(1, (int)($settings['booking_duration'] ?? 90));
        $start_at = new DateTimeImmutable($current_booking['booking_date'] . ' ' . $current_booking['booking_time']);
        $end_at = $start_at->modify('+' . $booking_duration . ' minutes');

        // Quick confirmation assigns the smallest available table when none was chosen.
        if ($quick_status === 'confirmed' && $table_id === null) {
            $stmt = $conn->prepare("SELECT t.id FROM restaurant_tables t
                WHERE t.status = 'active' AND t.capacity >= ?
                AND NOT EXISTS (
                    SELECT 1 FROM bookings b WHERE b.table_id = t.id AND b.id <> ?
                    AND b.status IN ('pending', 'confirmed')
                    AND TIMESTAMP(b.booking_date, b.booking_time) < ?
                    AND TIMESTAMPADD(MINUTE, ?, TIMESTAMP(b.booking_date, b.booking_time)) > ?
                ) ORDER BY t.capacity, t.table_number LIMIT 1");
            $stmt->execute([$current_booking['guest_count'], $booking_id,
                $end_at->format('Y-m-d H:i:s'), $booking_duration, $start_at->format('Y-m-d H:i:s')]);
            $table_id = $stmt->fetchColumn() ?: null;
        }

        if ($status === 'confirmed' && $table_id === null) {
            throw new InvalidArgumentException($quick_status === 'confirmed'
                ? 'No table is available for this booking. Choose another time or add a suitable table.'
                : 'Choose an available table before confirming this booking.');
        }

        if ($table_id !== null) {
            $stmt = $conn->prepare('SELECT id, capacity, status FROM restaurant_tables WHERE id = ? FOR UPDATE');
            $stmt->execute([$table_id]);
            $table = $stmt->fetch();
            if (!$table) {
                throw new InvalidArgumentException('The selected table no longer exists.');
            }
            if (in_array($status, ['pending', 'confirmed'], true)) {
                if ($table['status'] !== 'active' || $table['capacity'] < $current_booking['guest_count']) {
                    throw new InvalidArgumentException('Choose an active table with enough seats for this party.');
                }

                // A locking read sees bookings committed while we waited for the table lock.
                $stmt = $conn->prepare("SELECT b.id FROM bookings b
                    WHERE b.table_id = ? AND b.id <> ? AND b.status IN ('pending', 'confirmed')
                    AND TIMESTAMP(b.booking_date, b.booking_time) < ?
                    AND TIMESTAMPADD(MINUTE, ?, TIMESTAMP(b.booking_date, b.booking_time)) > ?
                    LIMIT 1 FOR UPDATE");
                $stmt->execute([$table_id, $booking_id, $end_at->format('Y-m-d H:i:s'),
                    $booking_duration, $start_at->format('Y-m-d H:i:s')]);
                if ($stmt->fetchColumn() !== false) {
                    throw new InvalidArgumentException('This table is already reserved at that time. Choose another table.');
                }
            }
        }

        $stmt = $conn->prepare('UPDATE bookings SET status = ?, table_id = ?, admin_note = ? WHERE id = ?');
        $stmt->execute([$status, $table_id, $admin_note, $booking_id]);
        $conn->commit();
        $_SESSION['admin_booking_flash'] = 'Booking updated successfully.';
        header('Location: booking-details.php?id=' . $booking_id);
        exit();
    } catch (InvalidArgumentException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $message = $e->getMessage();
    } catch (Throwable $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log('Admin booking update failed: ' . $e->getMessage());
        $message = 'The booking could not be updated. Please try again.';
    }
}

if (!$message && isset($_SESSION['admin_booking_flash'])) {
    $message = $_SESSION['admin_booking_flash'];
    $message_type = 'success';
    unset($_SESSION['admin_booking_flash']);
}

require_once 'includes/header.php';

if (!$booking_id) {
    echo '<div class="alert alert-danger">Choose a valid booking.</div>';
    require_once 'includes/footer.php';
    exit();
}

$stmt = $conn->prepare("
    SELECT b.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone, t.table_number, t.table_name, t.location, t.capacity
    FROM bookings b
    LEFT JOIN users u ON b.user_id = u.id
    LEFT JOIN restaurant_tables t ON b.table_id = t.id
    WHERE b.id = ?
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

if (!$booking) {
    echo '<div class="alert alert-danger">Booking not found.</div>';
    require_once 'includes/footer.php';
    exit();
}

$settings = getRestaurantSettings($conn);
$booking_duration = max(1, (int)($settings['booking_duration'] ?? 90));
$start_at = new DateTimeImmutable($booking['booking_date'] . ' ' . $booking['booking_time']);
$end_at = $start_at->modify('+' . $booking_duration . ' minutes');
$stmt = $conn->prepare("SELECT t.*, EXISTS (
        SELECT 1 FROM bookings b WHERE b.table_id = t.id AND b.id <> ?
        AND b.status IN ('pending', 'confirmed')
        AND TIMESTAMP(b.booking_date, b.booking_time) < ?
        AND TIMESTAMPADD(MINUTE, ?, TIMESTAMP(b.booking_date, b.booking_time)) > ?
    ) AS is_reserved
    FROM restaurant_tables t WHERE t.status = 'active' AND t.capacity >= ?
    ORDER BY t.capacity, t.table_number");
$stmt->execute([$booking_id, $end_at->format('Y-m-d H:i:s'), $booking_duration,
    $start_at->format('Y-m-d H:i:s'), $booking['guest_count']]);
$all_tables = $stmt->fetchAll();
$available_tables = array_values(array_filter($all_tables, function ($table) {
    return !(int)$table['is_reserved'];
}));
$form_table_id = $message_type === 'danger' && isset($table_id) ? $table_id : $booking['table_id'];
$form_status = $message_type === 'danger' && isset($status) && in_array($status, $valid_statuses ?? [], true)
    ? $status : $booking['status'];
$form_admin_note = $message_type === 'danger' && isset($admin_note) && is_string($admin_note)
    ? $admin_note : ($booking['admin_note'] ?? '');
?>

<?php if ($message): ?>
<div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="admin-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Booking #<?php echo $booking['id']; ?></h3>
                <span class="status-badge <?php echo $booking['status']; ?> fs-6">
                    <?php echo ucfirst($booking['status']); ?>
                </span>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>Booking Information</h5>
                    <table class="table table-dark">
                        <tr>
                            <td><strong>Booking Code:</strong></td>
                            <td><?php echo htmlspecialchars($booking['booking_code']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Date:</strong></td>
                            <td><?php echo formatDate($booking['booking_date']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Time:</strong></td>
                            <td><?php echo formatTime($booking['booking_time']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Number of Guests:</strong></td>
                            <td><?php echo $booking['guest_count']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Current Table:</strong></td>
                            <td>
                                <?php if ($booking['table_number']): ?>
                                    <div class="table-info-inline">
                                        <span class="table-badge">Table <?php echo $booking['table_number']; ?></span>
                                        <span class="table-name"><?php echo htmlspecialchars($booking['table_name']); ?></span>
                                        <?php if (isset($booking['location'])): ?>
                                        <span class="table-location"><?php echo htmlspecialchars($booking['location']); ?></span>
                                        <?php endif; ?>
                                        <?php if (isset($booking['capacity'])): ?>
                                        <span class="table-capacity"><i class="bi bi-people"></i> <?php echo $booking['capacity']; ?> guests</span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">Not assigned</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td><?php echo formatDate($booking['created_at']); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>Customer Information</h5>
                    <table class="table table-dark">
                        <tr>
                            <td><strong>Name:</strong></td>
                            <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td><?php echo htmlspecialchars($booking['customer_email']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Phone:</strong></td>
                            <td><?php echo htmlspecialchars($booking['customer_phone']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <?php if ($booking['special_request']): ?>
            <div class="mb-4">
                <h5>Special Request</h5>
                <div class="p-3 bg-secondary rounded">
                    <?php echo htmlspecialchars($booking['special_request']); ?>
                </div>
            </div>
            <?php endif; ?>

            <form method="POST" id="manage-booking" action="booking-details.php?id=<?php echo $booking['id']; ?>">
                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['admin_booking_csrf'], ENT_QUOTES, 'UTF-8'); ?>">

                <div class="mb-3">
                    <label class="form-label">Assign Table</label>
                    <select class="form-select" name="table_id" id="booking_table_id">
                        <option value="">Select a table</option>
                        <?php foreach ($all_tables as $table): ?>
                        <option value="<?php echo $table['id']; ?>"
                                <?php echo (string)$form_table_id === (string)$table['id'] ? 'selected' : ''; ?>
                                <?php echo (int)$table['is_reserved'] ? 'disabled' : ''; ?>>
                            Table <?php echo htmlspecialchars($table['table_number'], ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars($table['table_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?> (Capacity: <?php echo $table['capacity']; ?>)
                            <?php echo (int)$table['is_reserved'] ? ' - Reserved' : ''; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Quick confirmation will assign the next available table if you leave this blank.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Admin Note</label>
                    <textarea class="form-control" name="admin_note" rows="3" maxlength="5000"><?php echo htmlspecialchars($form_admin_note, ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Update Status</label>
                    <select class="form-select status-select" name="status" data-previous="<?php echo $booking['status']; ?>">
                        <option value="pending" <?php echo $form_status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $form_status == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="rejected" <?php echo $form_status == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        <option value="cancelled" <?php echo $form_status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        <option value="completed" <?php echo $form_status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>

                <div class="action-buttons">
                    <button type="submit" class="btn btn-admin-primary">Update Booking</button>
                    <a href="bookings.php" class="btn btn-admin-secondary">Back to Bookings</a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="admin-card">
            <h4>Quick Actions</h4>
            <div class="d-flex flex-column gap-2 mt-3">
                <?php if ($booking['status'] == 'pending'): ?>
                <button type="submit" form="manage-booking" name="quick_status" value="confirmed" class="btn btn-admin-success booking-quick-submit">
                    <i class="bi bi-check-circle"></i> Confirm Booking
                </button>
                <button type="submit" form="manage-booking" name="quick_status" value="rejected" class="btn btn-admin-danger booking-quick-submit">
                    <i class="bi bi-x-circle"></i> Reject Booking
                </button>
                <?php endif; ?>

                <?php if ($booking['status'] == 'confirmed'): ?>
                <button type="submit" form="manage-booking" name="quick_status" value="completed" class="btn btn-admin-warning booking-quick-submit">
                    <i class="bi bi-check-circle"></i> Mark as Completed
                </button>
                <button type="submit" form="manage-booking" name="quick_status" value="cancelled" class="btn btn-admin-secondary booking-quick-submit">
                    <i class="bi bi-x-circle"></i> Cancel Booking
                </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="admin-card mt-4">
            <h4>Table Availability</h4>
            <div class="mt-3">
                <?php if (count($available_tables) > 0): ?>
                    <div class="alert-custom success">
                        <i class="bi bi-check-circle"></i> <?php echo count($available_tables); ?> table(s) available
                    </div>
                    <ul class="list-unstyled">
                        <?php foreach ($available_tables as $table): ?>
                        <li class="mb-2">
                            <strong>Table <?php echo $table['table_number']; ?></strong> -
                            <?php echo htmlspecialchars($table['table_name']); ?>
                            (Capacity: <?php echo $table['capacity']; ?>)
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="alert-custom danger">
                        <i class="bi bi-x-circle"></i> No tables available for this time
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
