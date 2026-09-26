<?php

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function redirect($url) {
    if (strpos($url, 'http') === 0) {
        header("Location: $url");
    } else {
        header("Location: " . SITE_URL . "/$url");
    }
    exit();
}

function getRestaurantSettings($conn) {
    $stmt = $conn->prepare("SELECT * FROM restaurant_settings WHERE id = 1");
    $stmt->execute();
    return $stmt->fetch();
}

function generateBookingCode() {
    return 'BK' . strtoupper(uniqid());
}

function formatDate($date, $format = 'd M Y') {
    return date($format, strtotime($date));
}

function formatTime($time) {
    return date('h:i A', strtotime($time));
}

function getStatusBadge($status) {
    $badges = [
        'pending' => 'bg-warning',
        'confirmed' => 'bg-success',
        'rejected' => 'bg-danger',
        'cancelled' => 'bg-secondary',
        'completed' => 'bg-info'
    ];
    return $badges[$status] ?? 'bg-secondary';
}

function isTableAvailable($conn, $table_id, $date, $time, $booking_duration = 90) {
    $booking_duration = max(1, (int)$booking_duration);
    $start_at = new DateTimeImmutable($date . ' ' . $time);
    $end_at = $start_at->modify('+' . $booking_duration . ' minutes');
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count
        FROM bookings
        WHERE table_id = ?
        AND status IN ('confirmed', 'pending')
        AND TIMESTAMP(booking_date, booking_time) < ?
        AND TIMESTAMPADD(MINUTE, ?, TIMESTAMP(booking_date, booking_time)) > ?
    ");
    $stmt->execute([$table_id, $end_at->format('Y-m-d H:i:s'), $booking_duration, $start_at->format('Y-m-d H:i:s')]);
    $result = $stmt->fetch();
    return $result['count'] == 0;
}

function getAvailableTables($conn, $date, $time, $guests) {
    $settings = getRestaurantSettings($conn);
    $booking_duration = max(1, (int)($settings['booking_duration'] ?? 90));
    $start_at = new DateTimeImmutable($date . ' ' . $time);
    $end_at = $start_at->modify('+' . $booking_duration . ' minutes');

    $stmt = $conn->prepare("
        SELECT t.*
        FROM restaurant_tables t
        WHERE t.status = 'active'
        AND t.capacity >= ?
        AND t.id NOT IN (
            SELECT table_id
            FROM bookings
            WHERE status IN ('confirmed', 'pending')
            AND TIMESTAMP(booking_date, booking_time) < ?
            AND TIMESTAMPADD(MINUTE, ?, TIMESTAMP(booking_date, booking_time)) > ?
        )
        ORDER BY t.capacity ASC, t.table_number ASC
    ");
    $stmt->execute([$guests, $end_at->format('Y-m-d H:i:s'), $booking_duration, $start_at->format('Y-m-d H:i:s')]);
    return $stmt->fetchAll();
}

function getBookingTimeWindow($settings) {
    $opening = substr((string)($settings['opening_time'] ?? '10:00'), 0, 5);
    $closing = substr((string)($settings['closing_time'] ?? '23:00'), 0, 5);
    $duration = max(1, (int)($settings['booking_duration'] ?? 90));
    $open_parts = array_map('intval', explode(':', $opening));
    $close_parts = array_map('intval', explode(':', $closing));
    $open_minutes = $open_parts[0] * 60 + $open_parts[1];
    $close_minutes = $close_parts[0] * 60 + $close_parts[1];
    $overnight = $close_minutes <= $open_minutes;
    if ($overnight) {
        $close_minutes += 1440;
    }
    $latest_minutes = $close_minutes - $duration;
    $latest_clock = (($latest_minutes % 1440) + 1440) % 1440;
    return [
        'opening' => $opening,
        'closing' => $closing,
        'latest_start' => sprintf('%02d:%02d', intdiv($latest_clock, 60), $latest_clock % 60),
        'open_minutes' => $open_minutes,
        'latest_minutes' => $latest_minutes,
        'overnight' => $overnight,
    ];
}

function isBookableTime($time, $window) {
    if (!preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', (string)$time)) {
        return false;
    }
    $parts = array_map('intval', explode(':', $time));
    $minutes = $parts[0] * 60 + $parts[1];
    if ($window['overnight'] && $minutes < $window['open_minutes']) {
        $minutes += 1440;
    }
    return $minutes >= $window['open_minutes'] && $minutes <= $window['latest_minutes'];
}

function menuItemImageUrl($item) {
    $uploaded_image = !empty($item['image']) ? basename($item['image']) : '';
    if ($uploaded_image && is_file(__DIR__ . '/../uploads/menu/' . $uploaded_image)) {
        return SITE_URL . '/uploads/menu/' . rawurlencode($uploaded_image);
    }

    $photo_by_name = [
        'spring rolls' => 'menu-spring-rolls.jpg',
        'garlic bread' => 'menu-garlic-bread.jpg',
        'grilled chicken' => 'menu-grilled-chicken.jpg',
        'fish curry' => 'menu-fish-curry.jpg',
        'margherita pizza' => 'menu-margherita-pizza.jpg',
        'pepperoni pizza' => 'menu-pepperoni-pizza.jpg',
        'chicken burger' => 'menu-chicken-burger.jpg',
        'veggie burger' => 'menu-veggie-burger.jpg',
        'spaghetti carbonara' => 'menu-spaghetti-carbonara.jpg',
        'penne arrabiata' => 'menu-penne-arrabbiata.jpg',
        'fresh lime soda' => 'menu-fresh-lime-soda.jpg',
        'mango smoothie' => 'menu-mango-smoothie.jpg',
        'chocolate brownie' => 'menu-chocolate-brownie.jpg',
        'ice cream sundae' => 'menu-ice-cream-sundae.jpg',
        'little margherita pizza' => 'kids-mini-pizza.jpg',
        'mini cheeseburger' => 'kids-mini-burger.jpg',
        'creamy mac & cheese' => 'kids-mac-cheese.jpg',
        'mini sundae' => 'kids-sundae.jpg',
        'veg steamed momo' => 'menu-momo-steamed.jpg',
        'crispy fried momo' => 'menu-momo-fried.jpg',
        'momo platter' => 'menu-momo-platter.jpg',
        'loaded fries' => 'menu-loaded-fries.jpg',
        'chicken wrap' => 'menu-chicken-wrap.jpg',
    ];
    $name = strtolower(trim((string)($item['name'] ?? '')));
    $photo = $photo_by_name[$name] ?? '';
    if ($photo && is_file(__DIR__ . '/../assets/images/photos/' . $photo)) {
        return SITE_URL . '/assets/images/photos/' . $photo;
    }

    return SITE_URL . '/assets/images/food-placeholder.svg';
}

function tableDiagramHtml($capacity) {
    $seats = max(1, min(12, (int)$capacity));
    $shape = $seats <= 4 ? 'round' : 'long';
    $label = 'Illustration of a dining table with ' . $seats . ' ' . ($seats === 1 ? 'chair' : 'chairs');
    $html = '<div class="seating-diagram seating-diagram--' . $shape . '" role="img" aria-label="' . $label . '">';
    $html .= '<span class="seating-diagram__table" aria-hidden="true"><span class="seating-diagram__plate"></span></span>';
    for ($seat = 0; $seat < $seats; $seat++) {
        $angle = (int)round($seat * 360 / $seats);
        $html .= '<span class="seating-diagram__chair" style="--seat-angle:' . $angle . 'deg" aria-hidden="true"></span>';
    }
    return $html . '</div>';
}
?>
