<?php
$page_title = 'Table Management';
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once 'includes/auth.php';

$message = '';
$message_type = '';

function tableAdminEscape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function tableAdminPostText($key, $maximum, $required = false)
{
    $value = $_POST[$key] ?? '';
    if (!is_string($value)) {
        throw new InvalidArgumentException('Please enter valid table details.');
    }
    $value = trim($value);
    if (($required && $value === '') || strlen($value) > $maximum) {
        throw new InvalidArgumentException('Please check the ' . str_replace('_', ' ', $key) . ' field.');
    }
    return $value;
}

if (empty($_SESSION['table_admin_csrf'])) {
    $_SESSION['table_admin_csrf'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['table_admin_csrf'];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    try {
        $submitted_token = $_POST['csrf_token'] ?? '';
        if (!is_string($submitted_token) || !hash_equals($csrf_token, $submitted_token)) {
            throw new InvalidArgumentException('Your form expired. Refresh the page and try again.');
        }

        $action = tableAdminPostText('action', 12, true);
        if ($action === 'add' || $action === 'edit') {
            $table_number = tableAdminPostText('table_number', 10, true);
            $table_name = tableAdminPostText('table_name', 100);
            $location = tableAdminPostText('location', 100);
            $status = tableAdminPostText('status', 11, true);
            $capacity = filter_var($_POST['capacity'] ?? null, FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1, 'max_range' => 100]]);

            if ($capacity === false || $capacity === null) {
                throw new InvalidArgumentException('Enter a capacity between 1 and 100 guests.');
            }
            if (!in_array($status, ['active', 'inactive', 'maintenance'], true)) {
                throw new InvalidArgumentException('Choose a valid table status.');
            }

            $table_id = null;
            if ($action === 'edit') {
                $table_id = filter_var($_POST['table_id'] ?? null, FILTER_VALIDATE_INT,
                    ['options' => ['min_range' => 1]]);
                if ($table_id === false || $table_id === null) {
                    throw new InvalidArgumentException('Choose a valid table to update.');
                }
                $exists = $conn->prepare('SELECT id FROM restaurant_tables WHERE id = ?');
                $exists->execute([$table_id]);
                if (!$exists->fetch()) {
                    throw new InvalidArgumentException('That table no longer exists. Refresh the page.');
                }
            }

            $duplicate = $conn->prepare('SELECT id FROM restaurant_tables WHERE table_number = ? AND id <> ? LIMIT 1');
            $duplicate->execute([$table_number, $table_id ?? 0]);
            if ($duplicate->fetch()) {
                throw new InvalidArgumentException('That table number is already in use.');
            }

            if ($action === 'add') {
                $stmt = $conn->prepare('INSERT INTO restaurant_tables (table_number, table_name, capacity, location, status) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$table_number, $table_name, $capacity, $location, $status]);
                $_SESSION['table_admin_flash'] = 'Table added successfully.';
            } else {
                $stmt = $conn->prepare('UPDATE restaurant_tables SET table_number = ?, table_name = ?, capacity = ?, location = ?, status = ? WHERE id = ?');
                $stmt->execute([$table_number, $table_name, $capacity, $location, $status, $table_id]);
                $_SESSION['table_admin_flash'] = 'Table updated successfully.';
            }
        } elseif ($action === 'delete') {
            $table_id = filter_var($_POST['table_id'] ?? null, FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]);
            if ($table_id === false || $table_id === null) {
                throw new InvalidArgumentException('Choose a valid table to delete.');
            }
            $exists = $conn->prepare('SELECT id FROM restaurant_tables WHERE id = ?');
            $exists->execute([$table_id]);
            if (!$exists->fetch()) {
                throw new InvalidArgumentException('That table no longer exists. Refresh the page.');
            }
            $active_bookings = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE table_id = ? AND status IN ('confirmed', 'pending')");
            $active_bookings->execute([$table_id]);
            if ((int)$active_bookings->fetchColumn() > 0) {
                throw new InvalidArgumentException('Cannot delete a table with pending or confirmed bookings.');
            }
            $stmt = $conn->prepare('DELETE FROM restaurant_tables WHERE id = ?');
            $stmt->execute([$table_id]);
            $_SESSION['table_admin_flash'] = 'Table deleted successfully.';
        } else {
            throw new InvalidArgumentException('Choose a valid table action.');
        }

        header('Location: tables.php');
        exit();
    } catch (InvalidArgumentException $exception) {
        $message = $exception->getMessage();
        $message_type = 'danger';
    } catch (Throwable $exception) {
        error_log('Table management failed: ' . $exception->getMessage());
        $message = $exception instanceof PDOException && $exception->getCode() === '23000'
            ? 'That table number is already in use.'
            : 'The table could not be saved. Please try again.';
        $message_type = 'danger';
    }
}

if ($message === '' && isset($_SESSION['table_admin_flash'])) {
    $message = $_SESSION['table_admin_flash'];
    $message_type = 'success';
    unset($_SESSION['table_admin_flash']);
}

$stmt = $conn->prepare("SELECT * FROM restaurant_tables ORDER BY table_number");
$stmt->execute();
$tables = $stmt->fetchAll();
require_once 'includes/header.php';
?>

<div class="dashboard-content">
    <div class="page-header">
        <h2>Table Management</h2>
        <p>Configure and manage restaurant tables</p>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <?php echo tableAdminEscape($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="table-cards">
        <?php if (count($tables) > 0): ?>
            <?php foreach ($tables as $table): ?>
            <div class="table-card">
                <div class="table-card-header">
                    <div class="table-number">TABLE <?php echo tableAdminEscape(str_pad($table['table_number'], 2, '0', STR_PAD_LEFT)); ?></div>
                    <span class="status-badge <?php echo tableAdminEscape($table['status']); ?>"><?php echo tableAdminEscape(ucfirst($table['status'])); ?></span>
                </div>
                <div class="table-card-body">
                    <h4><?php echo tableAdminEscape($table['table_name']); ?></h4>
                    <div class="table-details">
                        <div class="table-detail-item">
                            <i class="bi bi-people"></i>
                            <span><?php echo (int)$table['capacity']; ?> Guests</span>
                        </div>
                        <div class="table-detail-item">
                            <i class="bi bi-geo-alt"></i>
                            <span><?php echo tableAdminEscape($table['location']); ?></span>
                        </div>
                    </div>
                </div>
                <div class="table-card-actions">
                    <button type="button" class="btn-action" data-bs-toggle="modal" data-bs-target="#editTableModal<?php echo (int)$table['id']; ?>">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    <form method="post" class="table-delete-form">
                        <input type="hidden" name="csrf_token" value="<?php echo tableAdminEscape($csrf_token); ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="table_id" value="<?php echo (int)$table['id']; ?>">
                        <button type="submit" class="btn-action danger btn-delete-confirm">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="bi bi-grid-3x3-gap" aria-hidden="true"></i></div>
                <h4>No Tables Found</h4>
                <p>Add your first restaurant table to get started</p>
            </div>
        <?php endif; ?>

        <button type="button" class="add-table-card" data-bs-toggle="modal" data-bs-target="#addTableModal">
            <div class="add-table-icon">
                <i class="bi bi-plus-lg"></i>
            </div>
            <h4>Add New Table</h4>
            <p>Click to add a new restaurant table</p>
        </button>
    </div>
</div>

<div class="modal fade" id="addTableModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Table</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="csrf_token" value="<?php echo tableAdminEscape($csrf_token); ?>">
                    <div class="form-group">
                        <label class="form-label">Table Number *</label>
                        <input type="text" class="form-control" name="table_number" maxlength="10" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Table Name</label>
                        <input type="text" class="form-control" name="table_name" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Capacity *</label>
                        <input type="number" class="form-control" name="capacity" min="1" max="100" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Table</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php foreach ($tables as $table): ?>
<div class="modal fade" id="editTableModal<?php echo $table['id']; ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Table <?php echo tableAdminEscape($table['table_number']); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="csrf_token" value="<?php echo tableAdminEscape($csrf_token); ?>">
                    <input type="hidden" name="table_id" value="<?php echo $table['id']; ?>">
                    <div class="form-group">
                        <label class="form-label">Table Number *</label>
                        <input type="text" class="form-control" name="table_number" value="<?php echo tableAdminEscape($table['table_number']); ?>" maxlength="10" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Table Name</label>
                        <input type="text" class="form-control" name="table_name" value="<?php echo tableAdminEscape($table['table_name']); ?>" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Capacity *</label>
                        <input type="number" class="form-control" name="capacity" value="<?php echo (int)$table['capacity']; ?>" min="1" max="100" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" value="<?php echo tableAdminEscape($table['location']); ?>" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="active" <?php echo $table['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $table['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            <option value="maintenance" <?php echo $table['status'] == 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Table</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<style>
.table-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 2rem;
}

.table-card {
    background: linear-gradient(135deg, var(--card-background), var(--tint-blue));
    padding: 2rem;
    border-radius: var(--radius-xl);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-md);
    transition: all 0.3s ease;
}

.table-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-lg);
}

.table-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.table-number {
    background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
    color: white;
    padding: 0.5rem 1rem;
    border-radius: var(--radius-md);
    font-weight: 700;
    font-size: 0.9rem;
}

.table-card-body h4 {
    color: var(--dark-blue);
    margin-bottom: 1.5rem;
    font-size: 1.3rem;
}

.table-details {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.table-detail-item {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    color: var(--text-gray);
    font-size: 0.95rem;
}

.table-detail-item i {
    color: var(--primary-blue);
    font-size: 1.1rem;
}

.table-card-actions {
    display: flex;
    gap: 1rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border-color);
}

.table-delete-form {
    flex: 1;
    display: flex;
    margin: 0;
}

.table-delete-form .btn-action {
    width: 100%;
}

.btn-action {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.8rem;
    border-radius: var(--radius-md);
    border: 2px solid var(--primary-blue);
    color: var(--primary-blue);
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    transition: all 0.3s ease;
    background: transparent;
}

.btn-action:hover {
    background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
    color: white;
    transform: translateY(-2px);
}

.btn-action.danger {
    border-color: var(--danger);
    color: var(--danger);
}

.btn-action.danger:hover {
    background: linear-gradient(135deg, var(--danger), #DC2626);
    color: white;
}

.add-table-card {
    background: linear-gradient(135deg, var(--light-blue), var(--light-blue-soft));
    padding: 2rem;
    border-radius: var(--radius-xl);
    border: 2px dashed var(--primary-blue);
    box-shadow: var(--shadow-md);
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 250px;
    width: 100%;
    font: inherit;
}

.add-table-card:hover {
    border-color: var(--dark-blue);
    background: linear-gradient(135deg, var(--light-blue-soft), var(--tint-blue-strong));
    transform: translateY(-8px);
    box-shadow: var(--shadow-lg);
}

.add-table-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
}

.add-table-icon i {
    color: white;
    font-size: 1.5rem;
}

.add-table-card h4 {
    color: var(--dark-blue);
    margin-bottom: 0.5rem;
}

.add-table-card p {
    color: var(--text-gray);
    font-size: 0.9rem;
    margin: 0;
}

.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem 2rem;
    color: var(--text-gray);
}

.empty-state-icon {
    font-size: 4rem;
    margin-bottom: 1.5rem;
}

.empty-state h4 {
    color: var(--dark-blue);
    margin-bottom: 1rem;
    font-size: 1.5rem;
}

@media (max-width: 768px) {
    .table-cards {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
