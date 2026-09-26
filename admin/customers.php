<?php
$page_title = 'Customer Management';
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
        if (menuEditorText('action', 20, true) !== 'toggle_status') {
            throw new InvalidArgumentException('Choose a valid customer action.');
        }
        $user_id = menuEditorId('user_id');
        $status = menuEditorText('status', 8, true);
        if (!in_array($status, ['active', 'inactive'], true)) {
            throw new InvalidArgumentException('Choose a valid customer status.');
        }
        $stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'customer'");
        $stmt->execute([$user_id]);
        if (!$stmt->fetchColumn()) {
            throw new InvalidArgumentException('That customer no longer exists.');
        }
        $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'customer'");
        $stmt->execute([$status, $user_id]);
        $message = 'Customer status updated successfully.';
        $message_type = 'success';
    } catch (InvalidArgumentException $exception) {
        $message = $exception->getMessage();
        $message_type = 'danger';
    } catch (Throwable $exception) {
        error_log('Customer management error: ' . $exception->getMessage());
        $message = 'The customer status could not be saved. Please try again.';
        $message_type = 'danger';
    }
}

$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "
    SELECT u.*,
           (SELECT COUNT(*) FROM bookings WHERE user_id = u.id) as total_bookings
    FROM users u
    WHERE u.role = 'customer'
";

$params = [];

if ($search) {
    $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$sql .= " ORDER BY u.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();
?>

<div class="admin-filters">
    <form method="GET" class="filter-row">
        <div class="filter-item">
            <label class="form-label">Search Customers</label>
            <input type="text" class="form-control search-input" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Name, Email, Phone">
        </div>
        <div class="filter-item">
            <button type="submit" class="btn btn-admin-primary">Search</button>
            <a href="customers.php" class="btn btn-admin-secondary">Clear</a>
        </div>
    </form>
</div>

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Customers (<?php echo count($customers); ?>)</h3>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="admin-table-container">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Total Bookings</th>
                        <th>Registered</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($customers) > 0): ?>
                        <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><?php echo $customer['id']; ?></td>
                            <td><?php echo htmlspecialchars($customer['name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                            <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                            <td><?php echo $customer['total_bookings']; ?></td>
                            <td><?php echo formatDate($customer['created_at'], 'd M Y'); ?></td>
                            <td><span class="status-badge <?php echo $customer['status']; ?>"><?php echo ucfirst($customer['status']); ?></span></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="bookings.php?search=<?php echo htmlspecialchars(rawurlencode($customer['email']), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-action btn-view">View Bookings</a>
                                    <?php if ($customer['status'] == 'active'): ?>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to disable this customer account?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="user_id" value="<?php echo $customer['id']; ?>">
                                        <input type="hidden" name="status" value="inactive">
                                        <button type="submit" class="btn btn-action btn-delete">Disable</button>
                                    </form>
                                    <?php else: ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="user_id" value="<?php echo $customer['id']; ?>">
                                        <input type="hidden" name="status" value="active">
                                        <button type="submit" class="btn btn-action btn-edit">Enable</button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="empty-table-state">
                                    <i class="bi bi-people"></i>
                                    <p>No customers found</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
