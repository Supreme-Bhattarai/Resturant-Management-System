<?php
$page_title = 'Choose Your Table';
require_once 'config/config.php';
require_once 'includes/functions.php';

$settings = getRestaurantSettings($conn) ?: [];
$booking_window = getBookingTimeWindow($settings);
$max_guests = max(1, (int)($settings['max_booking_guests'] ?? 20));
$default_guests = min(2, $max_guests);

$raw_date = $_GET['date'] ?? '';
$requested_date = is_string($raw_date) ? trim($raw_date) : '';
$parsed_date = $requested_date !== '' ? DateTime::createFromFormat('!Y-m-d', $requested_date) : false;
if (!$parsed_date || $parsed_date->format('Y-m-d') !== $requested_date || $requested_date < date('Y-m-d')) {
    $requested_date = '';
}

$raw_time = $_GET['time'] ?? '';
$requested_time = is_string($raw_time) ? trim($raw_time) : '';
$time_notice = '';
if (!preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', $requested_time)) {
    $requested_time = '';
} elseif (!isBookableTime($requested_time, $booking_window)) {
    $requested_time = '';
    $time_notice = 'That time is outside booking hours. Please choose a time from ' . formatTime($booking_window['opening']) . ' to ' . formatTime($booking_window['latest_start']) . '.';
} elseif ($requested_date === date('Y-m-d') && $requested_time <= date('H:i')) {
    $requested_time = '';
    $time_notice = 'Please choose a future time for today, or select another date.';
}

$requested_guests = filter_var(
    $_GET['guests'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1, 'max_range' => $max_guests]]
);
if ($requested_guests === false || $requested_guests === null) {
    $requested_guests = $default_guests;
}

$stmt = $conn->prepare("SELECT * FROM restaurant_tables WHERE status = 'active' ORDER BY capacity ASC, table_number ASC");
$stmt->execute();
$all_tables = $stmt->fetchAll();
$capacity_tables = array_values(array_filter($all_tables, function ($table) use ($requested_guests) {
    return (int)$table['capacity'] >= $requested_guests;
}));
$availability_checked = $requested_date !== '' && $requested_time !== '';
$tables = $availability_checked
    ? getAvailableTables($conn, $requested_date, $requested_time, $requested_guests)
    : $capacity_tables;

$reservation_params = ['guests' => $requested_guests];
if ($requested_date !== '') {
    $reservation_params['date'] = $requested_date;
}
if ($requested_time !== '') {
    $reservation_params['time'] = $requested_time;
}
$reservation_query = http_build_query($reservation_params, '', '&', PHP_QUERY_RFC3986);
$page_stylesheet = 'assets/css/tables.css';
require_once 'includes/header.php';
?>

<section class="table-picker" aria-labelledby="table-picker-title">
    <div class="table-picker__hero">
        <div class="container">
            <span class="table-picker__eyebrow"><i class="bi bi-stars" aria-hidden="true"></i> Your visit starts here</span>
            <h1 id="table-picker-title">Find your place<br><em>at the table.</em></h1>
            <p>Choose the table that feels right for your group. Pick a date and time now, or add them when you make your request.</p>
            <ol class="table-picker__steps" aria-label="Reservation steps">
                <li class="is-current" aria-current="step"><span>01</span> Plan your visit</li>
                <li><span>02</span> Choose a table</li>
                <li><span>03</span> Send your request</li>
            </ol>
        </div>
    </div>

    <div class="container table-picker__content">
        <form id="table-search" class="table-search" method="get" action="tables.php" aria-labelledby="table-search-title">
            <div class="table-search__intro">
                <div>
                    <span class="table-search__kicker">A little planning goes a long way</span>
                    <h2 id="table-search-title">Plan your visit</h2>
                </div>
                <p>Choose your party size to see tables that fit. Date and time can be added here or on the next step.</p>
            </div>
            <div class="table-search__fields">
                <div class="table-search__field">
                    <label for="table-date">Date <span>Optional</span></label>
                    <input id="table-date" type="date" name="date" min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($requested_date, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="table-search__field">
                    <label for="table-time">Time <span>Optional</span></label>
                    <input id="table-time" type="time" name="time" value="<?php echo htmlspecialchars($requested_time, ENT_QUOTES, 'UTF-8'); ?>" <?php if (!$booking_window['overnight']): ?>min="<?php echo htmlspecialchars($booking_window['opening'], ENT_QUOTES, 'UTF-8'); ?>" max="<?php echo htmlspecialchars($booking_window['latest_start'], ENT_QUOTES, 'UTF-8'); ?>"<?php endif; ?>>
                </div>
                <div class="table-search__field">
                    <label for="table-guests">Party size <span>Include children</span></label>
                    <input id="table-guests" type="number" name="guests" min="1" max="<?php echo $max_guests; ?>" value="<?php echo $requested_guests; ?>" required>
                </div>
                <button type="submit" class="table-search__submit">Show matching tables <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
            </div>
            <p class="table-search__hours"><i class="bi bi-clock" aria-hidden="true"></i> Reservations start from <?php echo formatTime($booking_window['opening']); ?> to <?php echo formatTime($booking_window['latest_start']); ?>.</p>
            <?php if ($time_notice !== ''): ?>
                <p class="table-search__notice" role="alert"><?php echo htmlspecialchars($time_notice, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
        </form>

        <div class="table-results" aria-live="polite">
            <div class="table-results__heading">
                <div>
                    <span class="table-results__kicker">Choose your spot</span>
                    <h2>
                        <?php if ($availability_checked): ?>
                            <?php echo count($tables); ?> <?php echo count($tables) === 1 ? 'table' : 'tables'; ?> available for your party
                        <?php else: ?>
                            <?php echo count($tables); ?> <?php echo count($tables) === 1 ? 'table fits' : 'tables fit'; ?> your party
                        <?php endif; ?>
                    </h2>
                </div>
                <span class="table-results__party"><i class="bi bi-people" aria-hidden="true"></i> <?php echo $requested_guests; ?> <?php echo $requested_guests === 1 ? 'guest' : 'guests'; ?></span>
            </div>
            <p class="table-results__note">
                <?php if ($availability_checked): ?>
                    Showing tables available for your selected date and time. Availability is checked again when you send your request.
                <?php elseif ($requested_date !== '' || $requested_time !== ''): ?>
                    Your selected <?php echo $requested_date !== '' && $requested_time !== '' ? 'date and time are' : ($requested_date !== '' ? 'date is' : 'time is'); ?> saved for the next step.
                    Final availability is checked when you send your reservation request.
                <?php else: ?>
                    You can choose a date and time on the next step. Final availability is checked when you send your reservation request.
                <?php endif; ?>
            </p>
        </div>

        <?php if ($tables): ?>
        <div class="table-choices">
            <?php foreach ($tables as $table):
                $table_number = trim((string)$table['table_number']);
                $table_name = trim((string)($table['table_name'] ?? ''));
                $table_name = $table_name !== '' ? $table_name : 'Table ' . $table_number;
                $table_location = trim((string)($table['location'] ?? ''));
                $capacity = (int)$table['capacity'];
            ?>
            <a class="table-choice" href="reservation.php?table_id=<?php echo (int)$table['id']; ?>&amp;<?php echo htmlspecialchars($reservation_query, ENT_QUOTES, 'UTF-8'); ?>">
                <div class="table-choice__visual">
                    <?php echo tableDiagramHtml($capacity); ?>
                    <span class="table-choice__number">TABLE <?php echo htmlspecialchars($table_number, ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="table-choice__fit"><i class="bi bi-check2-circle" aria-hidden="true"></i> Fits your party</span>
                </div>
                <div class="table-choice__body">
                    <h3><?php echo htmlspecialchars($table_name, ENT_QUOTES, 'UTF-8'); ?></h3>
                    <div class="table-choice__details">
                        <span><i class="bi bi-people" aria-hidden="true"></i> Seats up to <?php echo $capacity; ?></span>
                        <?php if ($table_location !== ''): ?>
                            <span><i class="bi bi-geo-alt" aria-hidden="true"></i> <?php echo htmlspecialchars($table_location, ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="table-choice__action">Select table <i class="bi bi-arrow-up-right" aria-hidden="true"></i></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="table-empty">
            <div class="table-empty__icon"><i class="bi bi-people" aria-hidden="true"></i></div>
            <?php if ($availability_checked && $capacity_tables): ?>
                <h3>No tables are available at that time.</h3>
                <p>Try another date or time, or contact us to plan your visit.</p>
                <div class="table-empty__actions">
                    <a href="#table-search">Change date or time</a>
                    <a href="contact.php">Contact us <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                </div>
            <?php elseif ($all_tables): ?>
                <h3>No table seats <?php echo $requested_guests; ?> <?php echo $requested_guests === 1 ? 'guest' : 'guests'; ?>.</h3>
                <p>Try a smaller party size, or contact us about a group visit.</p>
                <div class="table-empty__actions">
                    <a href="#table-search">Change party size</a>
                    <a href="contact.php">Contact us <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                </div>
            <?php else: ?>
                <h3>Tables are being prepared.</h3>
                <p>Please check back soon or contact us to plan your visit.</p>
                <div class="table-empty__actions"><a href="contact.php">Contact us <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <p class="table-picker__help"><i class="bi bi-info-circle" aria-hidden="true"></i> Your booking is a request until the restaurant confirms it.</p>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
