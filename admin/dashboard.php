<?php
$page_title = 'Dashboard';
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once 'includes/header.php';

$today = date('Y-m-d');

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE booking_date = ?");
$stmt->execute([$today]);
$today_bookings = $stmt->fetch()['count'];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'");
$stmt->execute();
$pending_bookings = $stmt->fetch()['count'];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE status = 'confirmed'");
$stmt->execute();
$confirmed_bookings = $stmt->fetch()['count'];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
$stmt->execute();
$total_customers = $stmt->fetch()['count'];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM restaurant_tables WHERE status = 'active'");
$stmt->execute();
$total_tables = $stmt->fetch()['count'];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM menu_items WHERE is_available = 1");
$stmt->execute();
$total_menu_items = $stmt->fetch()['count'];

$stmt = $conn->prepare("
    SELECT b.*, u.name as customer_name, t.table_number
    FROM bookings b
    LEFT JOIN users u ON b.user_id = u.id
    LEFT JOIN restaurant_tables t ON b.table_id = t.id
    ORDER BY b.created_at DESC
    LIMIT 10
");
$stmt->execute();
$recent_bookings = $stmt->fetchAll();
?>

<div class="dashboard-content">
    <div class="page-header">
        <h2>Dashboard</h2>
        <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! Here's what's happening today.</p>
    </div>

    <?php if ($pending_bookings > 0): ?>
    <div class="alert-banner warning">
        <div class="alert-content">
            <i class="bi bi-exclamation-triangle"></i>
            <div class="alert-text">
                <strong><?php echo $pending_bookings; ?> bookings</strong> are waiting for confirmation
            </div>
        </div>
        <a href="bookings.php?status=pending" class="btn-alert">Review Now</a>
    </div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card-primary">
            <div class="stat-icon-wrapper royal">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $today_bookings; ?></div>
                <div class="stat-label">Today's Bookings</div>
            </div>
        </div>

        <div class="stat-card-primary">
            <div class="stat-icon-wrapper warning">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $pending_bookings; ?></div>
                <div class="stat-label">Pending Bookings</div>
            </div>
        </div>

        <div class="stat-card-primary">
            <div class="stat-icon-wrapper success">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $confirmed_bookings; ?></div>
                <div class="stat-label">Confirmed Bookings</div>
            </div>
        </div>

        <div class="stat-card-primary">
            <div class="stat-icon-wrapper sky">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $total_customers; ?></div>
                <div class="stat-label">Total Customers</div>
            </div>
        </div>
    </div>

    <div class="stats-grid-secondary">
        <div class="stat-card-secondary">
            <div class="stat-icon-wrapper indigo">
                <i class="bi bi-table"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $total_tables; ?></div>
                <div class="stat-label">Total Tables</div>
            </div>
        </div>

        <div class="stat-card-secondary">
            <div class="stat-icon-wrapper slate">
                <i class="bi bi-egg-fried"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $total_menu_items; ?></div>
                <div class="stat-label">Menu Items</div>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-main">
            <div class="admin-card">
                <div class="card-header">
                    <h3>Recent Bookings</h3>
                    <a href="bookings.php" class="btn-view-all">View All</a>
                </div>

                <div class="table-container">
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Guests</th>
                                    <th>Table</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($recent_bookings) > 0): ?>
                                    <?php foreach ($recent_bookings as $booking): ?>
                                    <tr>
                                        <td><span class="booking-id">#<?php echo $booking['id']; ?></span></td>
                                        <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                                        <td><?php echo formatDate($booking['booking_date']); ?></td>
                                        <td><?php echo formatTime($booking['booking_time']); ?></td>
                                        <td><?php echo $booking['guest_count']; ?></td>
                                        <td><?php echo $booking['table_number'] ? 'Table ' . $booking['table_number'] : 'Not assigned'; ?></td>
                                        <td><span class="status-badge <?php echo $booking['status']; ?>"><?php echo ucfirst($booking['status']); ?></span></td>
                                        <td>
                                            <a href="booking-details.php?id=<?php echo $booking['id']; ?>" class="btn-action">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No bookings found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-sidebar">
            <div class="quick-actions-card">
                <h3>Quick Actions</h3>
                <div class="quick-actions-grid">
                    <a href="bookings.php" class="quick-action-item">
                        <div class="quick-action-icon royal">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div class="quick-action-content">
                            <h4>Manage Bookings</h4>
                            <p>View and manage reservations</p>
                        </div>
                    </a>

                    <a href="tables.php" class="quick-action-item">
                        <div class="quick-action-icon indigo">
                            <i class="bi bi-table"></i>
                        </div>
                        <div class="quick-action-content">
                            <h4>Manage Tables</h4>
                            <p>Configure restaurant tables</p>
                        </div>
                    </a>

                    <a href="menu-catalog.php" class="quick-action-item">
                        <div class="quick-action-icon slate">
                            <i class="bi bi-book-half"></i>
                        </div>
                        <div class="quick-action-content">
                            <h4>Manage Catalog</h4>
                            <p>Add dishes and update prices</p>
                        </div>
                    </a>

                    <a href="customers.php" class="quick-action-item">
                        <div class="quick-action-icon sky">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="quick-action-content">
                            <h4>Manage Customers</h4>
                            <p>View customer accounts</p>
                        </div>
                    </a>
                </div>
            </div>

            <div class="system-status-card">
                <h3>System Status</h3>
                <div class="status-item">
                    <div class="status-indicator success"></div>
                    <div class="status-text">
                        <strong>Database</strong>
                        <span>Connected</span>
                    </div>
                </div>
                <div class="status-item">
                    <div class="status-indicator success"></div>
                    <div class="status-text">
                        <strong>Server</strong>
                        <span>Running</span>
                    </div>
                </div>
                <div class="status-item">
                    <div class="status-indicator success"></div>
                    <div class="status-text">
                        <strong>Storage</strong>
                        <span>Available</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-content {
    padding: 2.5rem;
}

.page-header {
    margin-bottom: 2.5rem;
}

.page-header h2 {
    font-size: 2.2rem;
    margin-bottom: 0.5rem;
    color: var(--dark-blue);
}

.page-header p {
    color: var(--text-gray);
    font-size: 1.1rem;
}

.alert-banner {
    background: linear-gradient(135deg, #FEF3C7, #FDE68A);
    border: 2px solid #FCD34D;
    border-radius: var(--radius-xl);
    padding: 1.5rem;
    margin-bottom: 2.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: var(--shadow-md);
}

.alert-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.alert-content i {
    font-size: 1.5rem;
    color: #B45309;
}

.alert-text {
    color: #B45309;
    font-size: 1.1rem;
}

.btn-alert {
    background: linear-gradient(135deg, var(--warning), #D97706);
    color: white;
    padding: 0.8rem 1.5rem;
    border-radius: var(--radius-md);
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-alert:hover {
    background: linear-gradient(135deg, #D97706, var(--warning));
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stats-grid-secondary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2.5rem;
}

.stat-card-primary {
    background: linear-gradient(135deg, var(--card-background), var(--tint-blue));
    padding: 2rem;
    border-radius: var(--radius-xl);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-md);
    display: flex;
    align-items: center;
    gap: 1.5rem;
    transition: all 0.3s ease;
}

.stat-card-primary:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.stat-card-secondary {
    background: var(--card-background);
    padding: 2rem;
    border-radius: var(--radius-xl);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-md);
    display: flex;
    align-items: center;
    gap: 1.5rem;
    transition: all 0.3s ease;
}

.stat-card-secondary:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.stat-icon-wrapper {
    width: 70px;
    height: 70px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-icon-wrapper.royal {
    background: var(--primary-blue);
}

.stat-icon-wrapper.warning {
    background: linear-gradient(135deg, var(--warning), #D97706);
}

.stat-icon-wrapper.success {
    background: linear-gradient(135deg, var(--success), #059669);
}

.stat-icon-wrapper.sky {
    background: #2e7bb5;
}

.stat-icon-wrapper.indigo {
    background: #536bc2;
}

.stat-icon-wrapper.slate {
    background: #637bbb;
}

.stat-icon-wrapper i {
    color: white;
    font-size: 2rem;
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--dark-blue);
    background: linear-gradient(135deg, var(--dark-blue), var(--primary-blue));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 0.3rem;
}

.stat-label {
    color: var(--text-gray);
    font-size: 1rem;
    font-weight: 500;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
}

.dashboard-main {
    display: flex;
    flex-direction: column;
}

.dashboard-sidebar {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.card-header h3 {
    font-size: 1.5rem;
    color: var(--dark-blue);
}

.btn-view-all {
    background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
    color: white;
    padding: 0.6rem 1.2rem;
    border-radius: var(--radius-md);
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-view-all:hover {
    background: linear-gradient(135deg, var(--dark-blue), var(--primary-blue));
    transform: translateY(-2px);
}

.booking-id {
    background: linear-gradient(135deg, var(--light-blue), var(--light-blue-soft));
    color: var(--dark-blue);
    padding: 0.3rem 0.8rem;
    border-radius: var(--radius-sm);
    font-weight: 600;
    font-size: 0.9rem;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
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
}

.quick-actions-card {
    background: var(--card-background);
    padding: 2rem;
    border-radius: var(--radius-xl);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-md);
}

.quick-actions-card h3 {
    font-size: 1.3rem;
    color: var(--dark-blue);
    margin-bottom: 1.5rem;
}

.quick-actions-grid {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.quick-action-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: linear-gradient(135deg, var(--tint-blue), var(--tint-blue-strong));
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-color);
    text-decoration: none;
    transition: all 0.3s ease;
}

.quick-action-item:hover {
    transform: translateX(5px);
    box-shadow: var(--shadow-md);
}

.quick-action-icon {
    width: 50px;
    height: 50px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.quick-action-icon.royal {
    background: var(--primary-blue);
}

.quick-action-icon.indigo {
    background: #536bc2;
}

.quick-action-icon.slate {
    background: #637bbb;
}

.quick-action-icon.sky {
    background: #2e7bb5;
}

.quick-action-icon i {
    color: white;
    font-size: 1.3rem;
}

.quick-action-content h4 {
    color: var(--dark-blue);
    font-size: 1rem;
    margin-bottom: 0.2rem;
}

.quick-action-content p {
    color: var(--text-gray);
    font-size: 0.85rem;
    margin: 0;
}

.system-status-card {
    background: var(--card-background);
    padding: 2rem;
    border-radius: var(--radius-xl);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-md);
}

.system-status-card h3 {
    font-size: 1.3rem;
    color: var(--dark-blue);
    margin-bottom: 1.5rem;
}

.status-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: linear-gradient(135deg, var(--tint-blue), var(--tint-blue-strong));
    border-radius: var(--radius-lg);
    margin-bottom: 1rem;
}

.status-item:last-child {
    margin-bottom: 0;
}

.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
}

.status-indicator.success {
    background: var(--success);
    box-shadow: 0 0 0 4px var(--success-soft);
}

.status-text {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.status-text strong {
    color: var(--dark-blue);
    font-size: 0.95rem;
}

.status-text span {
    color: var(--text-gray);
    font-size: 0.85rem;
}

@media (max-width: 1024px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .stats-grid-secondary {
        grid-template-columns: 1fr;
    }

    .stat-card-primary,
    .stat-card-secondary {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
