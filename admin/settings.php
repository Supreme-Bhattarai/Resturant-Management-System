<?php
$page_title = 'Restaurant Settings';
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once 'includes/menu-editor-security.php';
require_once 'includes/header.php';

$message = '';
$message_type = '';
$csrf_token = menuEditorCsrfToken();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    try {
        menuEditorVerifyCsrf();
        $restaurant_name = menuEditorText('restaurant_name', 100, true);
        $phone = menuEditorText('phone', 20);
        $email = menuEditorText('email', 100);
        $address = menuEditorText('address', 4000);
        $opening_time = menuEditorText('opening_time', 8, true);
        $closing_time = menuEditorText('closing_time', 8, true);
        $booking_duration = filter_var($_POST['booking_duration'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 30, 'max_range' => 240]]);
        $max_booking_guests = filter_var($_POST['max_booking_guests'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 50]]);
        if (($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) ||
            !preg_match('/^\d{2}:\d{2}(?::\d{2})?$/', $opening_time) ||
            !preg_match('/^\d{2}:\d{2}(?::\d{2})?$/', $closing_time) ||
            strtotime($opening_time) === false || strtotime($closing_time) === false ||
            strtotime($opening_time) >= strtotime($closing_time) ||
            $booking_duration === false || $max_booking_guests === false) {
            throw new InvalidArgumentException('Check the email, opening hours, booking duration, and guest limit.');
        }
        if (strtotime($opening_time) + $booking_duration * 60 > strtotime($closing_time)) {
            throw new InvalidArgumentException('Booking duration must fit between opening and closing time.');
        }
        $allow_online_booking = isset($_POST['allow_online_booking']) ? 1 : 0;
        $stmt = $conn->prepare('INSERT INTO restaurant_settings (id, restaurant_name, phone, email, address, opening_time, closing_time, booking_duration, max_booking_guests, allow_online_booking)
            VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE restaurant_name = VALUES(restaurant_name), phone = VALUES(phone), email = VALUES(email), address = VALUES(address),
            opening_time = VALUES(opening_time), closing_time = VALUES(closing_time), booking_duration = VALUES(booking_duration),
            max_booking_guests = VALUES(max_booking_guests), allow_online_booking = VALUES(allow_online_booking)');
        $stmt->execute([$restaurant_name, $phone, $email, $address, $opening_time, $closing_time, $booking_duration, $max_booking_guests, $allow_online_booking]);
        $message = 'Settings updated successfully.';
        $message_type = 'success';
    } catch (InvalidArgumentException $exception) {
        $message = $exception->getMessage();
        $message_type = 'danger';
    } catch (Throwable $exception) {
        error_log('Restaurant settings error: ' . $exception->getMessage());
        $message = 'The settings could not be saved. Please try again.';
        $message_type = 'danger';
    }
}

$settings = getRestaurantSettings($conn);
?>

<div class="admin-card">
    <h3>Restaurant Settings</h3>

    <?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Restaurant Name *</label>
                <input type="text" class="form-control" name="restaurant_name" value="<?php echo htmlspecialchars($settings['restaurant_name']); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Phone</label>
                <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($settings['phone']); ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($settings['email']); ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Booking Duration (minutes)</label>
                <input type="number" class="form-control" name="booking_duration" value="<?php echo $settings['booking_duration']; ?>" min="30" max="240" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea class="form-control" name="address" rows="3"><?php echo htmlspecialchars($settings['address']); ?></textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Opening Time</label>
                <input type="time" class="form-control" name="opening_time" value="<?php echo $settings['opening_time']; ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Closing Time</label>
                <input type="time" class="form-control" name="closing_time" value="<?php echo $settings['closing_time']; ?>" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Maximum Guests Per Booking</label>
                <input type="number" class="form-control" name="max_booking_guests" value="<?php echo $settings['max_booking_guests']; ?>" min="1" max="50" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">&nbsp;</label>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="allow_online_booking" id="allowOnlineBooking" <?php echo $settings['allow_online_booking'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="allowOnlineBooking">Allow Online Booking</label>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-admin-primary">Update Settings</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
