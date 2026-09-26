<?php
$page_title = 'Bookings Management';
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once 'includes/header.php';

$allowed_statuses = ['all', 'pending', 'confirmed', 'rejected', 'cancelled', 'completed'];
$status_filter = is_string($_GET['status'] ?? null) && in_array($_GET['status'], $allowed_statuses, true)
    ? $_GET['status'] : 'all';
$date_filter = is_string($_GET['date'] ?? null) ? $_GET['date'] : '';
$valid_date = $date_filter !== '' ? DateTimeImmutable::createFromFormat('!Y-m-d', $date_filter) : false;
if ($valid_date === false || $valid_date->format('Y-m-d') !== $date_filter) {
    $date_filter = '';
}
$search = is_string($_GET['search'] ?? null) ? substr(trim($_GET['search']), 0, 100) : '';

$sql = "
    SELECT b.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone, t.table_number, t.table_name, t.location
    FROM bookings b
    LEFT JOIN users u ON b.user_id = u.id
    LEFT JOIN restaurant_tables t ON b.table_id = t.id
    WHERE 1=1
";

$params = [];

if ($status_filter != 'all') {
    $sql .= " AND b.status = ?";
    $params[] = $status_filter;
}

if ($date_filter) {
    $sql .= " AND b.booking_date = ?";
    $params[] = $date_filter;
}

if ($search !== '') {
    $sql .= " AND (b.id = ? OR b.booking_code LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $search_id = ltrim($search, '#');
    $params[] = ctype_digit($search_id) ? (int)$search_id : 0;
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$sql .= " ORDER BY b.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM bookings GROUP BY status");
$stmt->execute();
$status_counts = [];
while ($row = $stmt->fetch()) {
    $status_counts[$row['status']] = $row['count'];
}
?>

<div class="dashboard-content">
    <div class="page-header">
        <h2>Bookings Management</h2>
        <p>View and manage all restaurant reservations</p>
    </div>

    <div class="filter-section">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending (<?php echo $status_counts['pending'] ?? 0; ?>)</option>
                    <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed (<?php echo $status_counts['confirmed'] ?? 0; ?>)</option>
                    <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected (<?php echo $status_counts['rejected'] ?? 0; ?>)</option>
                    <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled (<?php echo $status_counts['cancelled'] ?? 0; ?>)</option>
                    <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed (<?php echo $status_counts['completed'] ?? 0; ?>)</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="form-label">Date</label>
                <input type="date" class="form-control" name="date" value="<?php echo htmlspecialchars($date_filter); ?>">
            </div>
            <div class="filter-group">
                <label class="form-label">Search</label>
                <div class="search-wrapper">
                    <i class="bi bi-search"></i>
                    <input type="text" class="form-control" name="search" maxlength="100" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Booking ID, code, customer, email or phone">
                </div>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="bookings.php" class="btn btn-outline">Clear</a>
            </div>
        </form>
    </div>

    <div class="table-container">
        <div class="table-header">
            <h3>Bookings (<?php echo count($bookings); ?>)</h3>
            <div class="table-actions">
                <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Guests</th>
                        <th>Table</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($bookings) > 0): ?>
                        <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><span class="booking-id">#<?php echo $booking['id']; ?></span></td>
                            <td><span class="booking-code"><?php echo htmlspecialchars($booking['booking_code']); ?></span></td>
                            <td>
                                <div class="customer-info">
                                    <strong><?php echo htmlspecialchars($booking['customer_name']); ?></strong>
                                    <small><?php echo htmlspecialchars($booking['customer_email']); ?></small>
                                </div>
                            </td>
                            <td><?php echo formatDate($booking['booking_date']); ?></td>
                            <td><?php echo formatTime($booking['booking_time']); ?></td>
                            <td><?php echo $booking['guest_count']; ?></td>
                            <td>
                                <?php if ($booking['table_number']): ?>
                                    <div class="table-info-inline">
                                        <span class="table-badge">Table <?php echo $booking['table_number']; ?></span>
                                        <?php if ($booking['location']): ?>
                                        <span class="table-location"><?php echo htmlspecialchars($booking['location']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">Not assigned</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="status-badge <?php echo $booking['status']; ?>"><?php echo ucfirst($booking['status']); ?></span></td>
                            <td><?php echo formatDate($booking['created_at'], 'd M Y'); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="booking-details.php?id=<?php echo $booking['id']; ?>" class="btn-action">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <?php if ($booking['status'] == 'pending'): ?>
                                    <a href="booking-details.php?id=<?php echo (int)$booking['id']; ?>#manage-booking" class="btn-action success">
                                        <i class="bi bi-check-circle"></i> Review &amp; confirm
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center">
                                <div class="empty-table-state">
                                    <i class="bi bi-calendar-x"></i>
                                    <p>No bookings found</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.filter-section {
    background: var(--card-background);
    padding: 2rem;
    border-radius: var(--radius-xl);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-md);
    margin-bottom: 2rem;
}

.filter-form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.search-wrapper {
    position: relative;
}

.search-wrapper i {
    position: absolute;
    left: 1.2rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--primary-blue);
    font-size: 1.1rem;
}

.search-wrapper .form-control {
    padding-left: 3rem;
}

.filter-actions {
    display: flex;
    gap: 1rem;
}

.booking-id {
    background: linear-gradient(135deg, var(--light-blue), var(--light-blue-soft));
    color: var(--dark-blue);
    padding: 0.4rem 0.8rem;
    border-radius: var(--radius-sm);
    font-weight: 600;
    font-size: 0.9rem;
}

.booking-code {
    background: linear-gradient(135deg, var(--tint-blue), var(--tint-blue-strong));
    color: var(--dark-blue);
    padding: 0.4rem 0.8rem;
    border-radius: var(--radius-sm);
    font-weight: 600;
    font-size: 0.85rem;
}

.customer-info {
    display: flex;
    flex-direction: column;
}

.customer-info strong {
    color: var(--dark-blue);
    font-size: 0.95rem;
}

.customer-info small {
    color: var(--text-gray);
    font-size: 0.8rem;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 1rem;
    border-radius: var(--radius-sm);
    border: 1px solid var(--primary-blue);
    color: var(--primary-blue);
    font-weight: 600;
    font-size: 0.85rem;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-action:hover {
    background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
    color: white;
    transform: translateY(-2px);
}

.btn-action.success {
    border-color: var(--success);
    color: var(--success);
}

.btn-action.success:hover {
    background: linear-gradient(135deg, var(--success), #059669);
    color: white;
}

.empty-table-state {
    text-align: center;
    padding: 3rem;
    color: var(--text-gray);
}

.empty-table-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: var(--text-light);
}

.empty-table-state p {
    margin: 0;
    font-size: 1rem;
}

@media (max-width: 768px) {
    .filter-form {
        grid-template-columns: 1fr;
    }

    .filter-actions {
        flex-direction: column;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
