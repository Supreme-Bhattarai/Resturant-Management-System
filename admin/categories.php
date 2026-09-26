<?php
$page_title = 'Category Management';
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/menu-editor-security.php';

$message = '';
$message_type = '';
$csrf_token = menuEditorCsrfToken();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $uploaded = null;
    try {
        menuEditorVerifyCsrf();
        $action = menuEditorText('action', 20, true);

        if ($action === 'delete') {
            $category_id = menuEditorId('category_id');
            $stmt = $conn->prepare('SELECT image FROM menu_categories WHERE id = ?');
            $stmt->execute([$category_id]);
            $existing = $stmt->fetch();
            if (!$existing) {
                throw new InvalidArgumentException('That category no longer exists.');
            }
            $stmt = $conn->prepare('SELECT COUNT(*) FROM menu_items WHERE category_id = ?');
            $stmt->execute([$category_id]);
            if ((int)$stmt->fetchColumn() > 0) {
                throw new InvalidArgumentException('Remove this category\'s dishes before deleting it.');
            }
            $stmt = $conn->prepare('DELETE FROM menu_categories WHERE id = ?');
            $stmt->execute([$category_id]);
            if ($stmt->rowCount() === 0) {
                throw new InvalidArgumentException('That category no longer exists.');
            }
            menuEditorDeleteUpload('categories', $existing['image']);
            $message = 'Category deleted successfully.';
        } elseif ($action === 'add' || $action === 'edit') {
            $name = menuEditorText('name', 100, true);
            $description = menuEditorText('description', 4000);
            $status = menuEditorText('status', 8, true);
            if (!in_array($status, ['active', 'inactive'], true)) {
                throw new InvalidArgumentException('Choose a valid category status.');
            }

            if ($action === 'edit') {
                $category_id = menuEditorId('category_id');
                $stmt = $conn->prepare('SELECT image FROM menu_categories WHERE id = ?');
                $stmt->execute([$category_id]);
                $existing = $stmt->fetch();
                if (!$existing) {
                    throw new InvalidArgumentException('That category no longer exists.');
                }
            }
            $stmt = $conn->prepare('SELECT id FROM menu_categories WHERE LOWER(name) = LOWER(?)' . ($action === 'edit' ? ' AND id <> ?' : '') . ' LIMIT 1');
            $stmt->execute($action === 'edit' ? [$name, $category_id] : [$name]);
            if ($stmt->fetch()) {
                throw new InvalidArgumentException('That category name is already in use.');
            }

            $uploaded = menuEditorUploadImage('categories');
            if ($action === 'edit') {
                $image = $uploaded ?? $existing['image'];
                $stmt = $conn->prepare('UPDATE menu_categories SET name = ?, description = ?, image = ?, status = ? WHERE id = ?');
                $stmt->execute([$name, $description, $image, $status, $category_id]);
                if ($uploaded !== null) {
                    menuEditorDeleteUpload('categories', $existing['image']);
                }
                $message = 'Category updated successfully.';
            } else {
                $stmt = $conn->prepare('INSERT INTO menu_categories (name, description, image, status) VALUES (?, ?, ?, ?)');
                $stmt->execute([$name, $description, $uploaded ?? '', $status]);
                $message = 'Category added successfully.';
            }
            $uploaded = null;
        } else {
            throw new InvalidArgumentException('Choose a valid category action.');
        }
        $_SESSION['category_editor_flash'] = $message;
        header('Location: categories.php');
        exit();
    } catch (InvalidArgumentException $exception) {
        menuEditorDeleteUpload('categories', $uploaded);
        $message = $exception->getMessage();
        $message_type = 'danger';
    } catch (Throwable $exception) {
        menuEditorDeleteUpload('categories', $uploaded);
        error_log('Category editor error: ' . $exception->getMessage());
        $message = 'The category could not be saved. Please try again.';
        $message_type = 'danger';
    }
}

if ($message === '' && isset($_SESSION['category_editor_flash'])) {
    $message = $_SESSION['category_editor_flash'];
    $message_type = 'success';
    unset($_SESSION['category_editor_flash']);
}

$stmt = $conn->prepare("SELECT * FROM menu_categories ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll();
require_once 'includes/header.php';
?>

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Menu Categories</h3>
        <button class="btn btn-admin-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="bi bi-plus-circle"></i> Add Category
        </button>
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
                        <th>Image</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($categories) > 0): ?>
                        <?php foreach ($categories as $category): ?>
                        <tr>
                            <td>
                                <?php if ($category['image']): ?>
                                <img src="<?php echo SITE_URL; ?>/uploads/categories/<?php echo htmlspecialchars(rawurlencode(basename($category['image'])), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                <?php else: ?>
                                <span class="text-secondary">No image</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                            <td><?php echo htmlspecialchars(substr($category['description'], 0, 50)) . '...'; ?></td>
                            <td><span class="status-badge <?php echo $category['status']; ?>"><?php echo ucfirst($category['status']); ?></span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#editCategoryModal<?php echo $category['id']; ?>">Edit</button>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="category_id" value="<?php echo (int)$category['id']; ?>">
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
                                    <i class="bi bi-tags"></i>
                                    <p>No categories found</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Category Name *</label>
                        <input type="text" class="form-control" name="name" maxlength="100" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" maxlength="4000"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-admin-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-admin-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php foreach ($categories as $category): ?>
<div class="modal fade" id="editCategoryModal<?php echo $category['id']; ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                    <div class="mb-3">
                        <label class="form-label">Category Name *</label>
                        <input type="text" class="form-control" name="name" maxlength="100" value="<?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" maxlength="4000"><?php echo htmlspecialchars($category['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Image</label>
                        <?php if ($category['image']): ?>
                        <img src="<?php echo SITE_URL; ?>/uploads/categories/<?php echo htmlspecialchars(rawurlencode(basename($category['image'])), ENT_QUOTES, 'UTF-8'); ?>" alt="Current image" style="width: 100px; height: 100px; object-fit: cover; border-radius: 5px;">
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Image</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="active" <?php echo $category['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $category['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-admin-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-admin-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php require_once 'includes/footer.php'; ?>
