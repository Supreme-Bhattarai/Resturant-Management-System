<?php
$page_title = 'Database Backup';
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/menu-editor-security.php';

$message = '';
$message_type = '';
$csrf_token = menuEditorCsrfToken();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'create') {
    try {
        menuEditorVerifyCsrf();
        $backup_file = '4to9_backup_' . date('Y_m_d_His') . '_' . bin2hex(random_bytes(3)) . '.sql';
        $backup_path = __DIR__ . '/../backups/' . $backup_file;

    $tables = [];
    $result = $conn->query("SHOW TABLES");
    while (($table = $result->fetchColumn()) !== false) {
        $tables[] = $table;
    }

    $sql = "-- Database Backup: restaurant_4to9\n";
    $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $sql .= "-- ----------------------------------------\n\n";
    $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

    foreach ($tables as $table) {
        $sql .= "-- Table structure for table `$table`\n";
        $sql .= "DROP TABLE IF EXISTS `$table`;\n";

        $create_table = $conn->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
        $sql .= $create_table[1] . ";\n\n";

        $sql .= "-- Data for table `$table`\n";

        $rows = $conn->query("SELECT * FROM `$table`");
        while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
            $sql .= "INSERT INTO `$table` VALUES (";
            $values = [];
            foreach ($row as $value) {
                $values[] = $value === null ? 'NULL' : $conn->quote((string)$value);
            }
            $sql .= implode(', ', $values) . ");\n";
        }
        $sql .= "\n";
    }

    $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

    if (file_put_contents($backup_path, $sql, LOCK_EX) !== false) {
        $file_size = filesize($backup_path);

        $stmt = $conn->prepare("INSERT INTO backups (filename, file_size, created_by) VALUES (?, ?, ?)");
        $stmt->execute([$backup_file, $file_size, $_SESSION['user_id']]);
        $message = 'Backup created successfully.';
        $message_type = 'success';
    } else {
        $message = 'Failed to create backup file. Please check directory permissions.';
        $message_type = 'danger';
    }
    } catch (InvalidArgumentException $exception) {
        $message = $exception->getMessage();
        $message_type = 'danger';
    } catch (Throwable $exception) {
        error_log('Backup creation error: ' . $exception->getMessage());
        $message = 'The backup could not be created. Please try again.';
        $message_type = 'danger';
    }
}

if (isset($_GET['download']) && !empty($_GET['download'])) {
    $backup_id = intval($_GET['download']);

    $stmt = $conn->prepare("SELECT * FROM backups WHERE id = ?");
    $stmt->execute([$backup_id]);
    $backup = $stmt->fetch();

    if ($backup) {
        $backup_path = __DIR__ . '/../backups/' . basename($backup['filename']);
        if (is_file($backup_path)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $backup['filename'] . '"');
            header('Content-Length: ' . filesize($backup_path));
            readfile($backup_path);
            exit();
        } else {
            $message = 'Backup file not found.';
            $message_type = 'danger';
        }
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    try {
    menuEditorVerifyCsrf();
    $backup_id = menuEditorId('backup_id');

    $stmt = $conn->prepare("SELECT * FROM backups WHERE id = ?");
    $stmt->execute([$backup_id]);
    $backup = $stmt->fetch();

    if ($backup) {
        $backup_path = __DIR__ . '/../backups/' . basename($backup['filename']);

        $stmt = $conn->prepare("DELETE FROM backups WHERE id = ?");
        if ($stmt->execute([$backup_id])) {
            if (is_file($backup_path)) {
                @unlink($backup_path);
            }
            $message = 'Backup deleted successfully.';
            $message_type = 'success';
        } else {
            $message = 'Failed to delete backup record.';
            $message_type = 'danger';
        }
    } else {
        throw new InvalidArgumentException('That backup no longer exists.');
    }
    } catch (InvalidArgumentException $exception) {
        $message = $exception->getMessage();
        $message_type = 'danger';
    } catch (Throwable $exception) {
        error_log('Backup deletion error: ' . $exception->getMessage());
        $message = 'The backup could not be deleted. Please try again.';
        $message_type = 'danger';
    }
}

$stmt = $conn->prepare("
    SELECT b.*, u.name as created_by_name
    FROM backups b
    LEFT JOIN users u ON b.created_by = u.id
    ORDER BY b.created_at DESC
");
$stmt->execute();
$backups = $stmt->fetchAll();
require_once 'includes/header.php';
?>

<div class="admin-card">
    <h3>Database Backup Management</h3>

    <?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-6">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="action" value="create">
                <button type="submit" class="btn btn-admin-primary">
                    <i class="bi bi-download"></i> Create New Backup
                </button>
            </form>
        </div>
        <div class="col-md-6 text-end">
            <small class="text-secondary">
                <i class="bi bi-info-circle"></i>
                Backups are stored in the /backups/ directory
            </small>
        </div>
    </div>

    <div class="admin-table-container">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Filename</th>
                        <th>Size</th>
                        <th>Created By</th>
                        <th>Created Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($backups) > 0): ?>
                        <?php foreach ($backups as $backup): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($backup['filename']); ?></td>
                            <td><?php echo number_format($backup['file_size'] / 1024, 2); ?> KB</td>
                            <td><?php echo htmlspecialchars($backup['created_by_name'] ?? 'System'); ?></td>
                            <td><?php echo formatDate($backup['created_at']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="backup.php?download=<?php echo $backup['id']; ?>" class="btn btn-action btn-view">Download</a>
                                    <button type="button" class="btn btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#restoreModal<?php echo (int)$backup['id']; ?>">Restore instructions</button>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="backup_id" value="<?php echo (int)$backup['id']; ?>">
                                        <button type="submit" class="btn btn-action btn-delete btn-delete-confirm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">
                                <div class="empty-table-state">
                                    <i class="bi bi-archive"></i>
                                    <p>No backups found</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php foreach ($backups as $backup): ?>
<div class="modal fade" id="restoreModal<?php echo $backup['id']; ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Restore instructions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Download this SQL file, then import it with phpMyAdmin to restore the database:</p>
                <p><strong><?php echo htmlspecialchars($backup['filename']); ?></strong></p>
                <p><small>Created: <?php echo formatDate($backup['created_at']); ?></small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-admin-secondary" data-bs-dismiss="modal">Close</button>
                <a href="backup.php?download=<?php echo (int)$backup['id']; ?>" class="btn btn-admin-primary">Download SQL</a>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php require_once 'includes/footer.php'; ?>
