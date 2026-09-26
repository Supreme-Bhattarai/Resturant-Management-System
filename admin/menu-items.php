<?php
$page_title = 'Menu Item Management';
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
            $item_id = menuEditorId('item_id');
            $stmt = $conn->prepare('SELECT image FROM menu_items WHERE id = ?');
            $stmt->execute([$item_id]);
            $existing = $stmt->fetch();
            if (!$existing) {
                throw new InvalidArgumentException('That dish no longer exists.');
            }
            $stmt = $conn->prepare('DELETE FROM menu_items WHERE id = ?');
            $stmt->execute([$item_id]);
            if ($stmt->rowCount() === 0) {
                throw new InvalidArgumentException('That dish no longer exists.');
            }
            menuEditorDeleteUpload('menu', $existing['image']);
            $message = 'Menu item deleted successfully.';
        } elseif ($action === 'add' || $action === 'edit') {
            $category_id = menuEditorId('category_id');
            $name = menuEditorText('name', 100, true);
            $description = menuEditorText('description', 4000);
            $price = menuEditorPrice();
            $is_available = isset($_POST['is_available']) ? 1 : 0;
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;

            $stmt = $conn->prepare('SELECT id FROM menu_categories WHERE id = ?');
            $stmt->execute([$category_id]);
            if (!$stmt->fetch()) {
                throw new InvalidArgumentException('Choose an existing category.');
            }

            if ($action === 'edit') {
                $item_id = menuEditorId('item_id');
                $stmt = $conn->prepare('SELECT image FROM menu_items WHERE id = ?');
                $stmt->execute([$item_id]);
                $existing = $stmt->fetch();
                if (!$existing) {
                    throw new InvalidArgumentException('That dish no longer exists.');
                }
                $uploaded = menuEditorUploadImage('menu');
                $image = $uploaded ?? $existing['image'];
                $stmt = $conn->prepare('UPDATE menu_items SET category_id = ?, name = ?, description = ?, price = ?, image = ?, is_available = ?, is_featured = ? WHERE id = ?');
                $stmt->execute([$category_id, $name, $description, $price, $image, $is_available, $is_featured, $item_id]);
                if ($uploaded !== null) {
                    menuEditorDeleteUpload('menu', $existing['image']);
                }
                $message = 'Menu item updated successfully.';
            } else {
                $uploaded = menuEditorUploadImage('menu');
                $image = $uploaded ?? '';
                $stmt = $conn->prepare('INSERT INTO menu_items (category_id, name, description, price, image, is_available, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$category_id, $name, $description, $price, $image, $is_available, $is_featured]);
                $message = 'Menu item added successfully.';
            }
            $uploaded = null;
        } else {
            throw new InvalidArgumentException('Choose a valid menu action.');
        }
        $_SESSION['menu_item_editor_flash'] = $message;
        header('Location: menu-items.php');
        exit();
    } catch (InvalidArgumentException $exception) {
        menuEditorDeleteUpload('menu', $uploaded);
        $message = $exception->getMessage();
        $message_type = 'danger';
    } catch (Throwable $exception) {
        menuEditorDeleteUpload('menu', $uploaded);
        error_log('Menu item editor error: ' . $exception->getMessage());
        $message = 'The menu item could not be saved. Please try again.';
        $message_type = 'danger';
    }
}

if ($message === '' && isset($_SESSION['menu_item_editor_flash'])) {
    $message = $_SESSION['menu_item_editor_flash'];
    $message_type = 'success';
    unset($_SESSION['menu_item_editor_flash']);
}

$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';
$availability_filter = isset($_GET['availability']) ? $_GET['availability'] : 'all';

$sql = "SELECT mi.*, mc.name as category_name FROM menu_items mi JOIN menu_categories mc ON mi.category_id = mc.id WHERE 1=1";
$params = [];

if ($category_filter != 'all') {
    $sql .= " AND mi.category_id = ?";
    $params[] = $category_filter;
}

if ($availability_filter != 'all') {
    $sql .= " AND mi.is_available = ?";
    $params[] = $availability_filter;
}

$sql .= " ORDER BY mc.name, mi.name";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$menu_items = $stmt->fetchAll();

$stmt = $conn->prepare('SELECT * FROM menu_categories ORDER BY name');
$stmt->execute();
$categories = $stmt->fetchAll();
require_once 'includes/header.php';
?>

<div class="admin-filters">
    <form method="GET" class="filter-row">
        <div class="filter-item">
            <label class="form-label">Category</label>
            <select class="form-select filter-select" name="category">
                <option value="all" <?php echo $category_filter == 'all' ? 'selected' : ''; ?>>All Categories</option>
                <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['id']; ?>" <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($category['name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-item">
            <label class="form-label">Availability</label>
            <select class="form-select filter-select" name="availability">
                <option value="all" <?php echo $availability_filter == 'all' ? 'selected' : ''; ?>>All</option>
                <option value="1" <?php echo $availability_filter == '1' ? 'selected' : ''; ?>>Available</option>
                <option value="0" <?php echo $availability_filter == '0' ? 'selected' : ''; ?>>Unavailable</option>
            </select>
        </div>
        <div class="filter-item">
            <button type="submit" class="btn btn-admin-primary">Filter</button>
            <a href="menu-items.php" class="btn btn-admin-secondary">Clear</a>
        </div>
    </form>
</div>

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Menu Items (<?php echo count($menu_items); ?>)</h3>
        <button class="btn btn-admin-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
            <i class="bi bi-plus-circle"></i> Add Menu Item
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
                        <th>Category</th>
                        <th>Price</th>
                        <th>Available</th>
                        <th>Featured</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($menu_items) > 0): ?>
                        <?php foreach ($menu_items as $item): ?>
                        <tr>
                            <td>
                                <?php if ($item['image']): ?>
                                <img src="<?php echo SITE_URL; ?>/uploads/menu/<?php echo htmlspecialchars(rawurlencode(basename($item['image'])), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                <?php else: ?>
                                <span class="text-secondary">No image</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                            <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <span class="status-badge <?php echo $item['is_available'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $item['is_available'] ? 'Available' : 'Unavailable'; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($item['is_featured']): ?>
                                <span class="badge bg-info">Featured</span>
                                <?php else: ?>
                                <span class="text-secondary">No</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#editItemModal<?php echo $item['id']; ?>">Edit</button>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="item_id" value="<?php echo (int)$item['id']; ?>">
                                        <button type="submit" class="btn btn-action btn-delete btn-delete-confirm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="empty-table-state">
                                    <i class="bi bi-egg-fried"></i>
                                    <p>No menu items found</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Menu Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category *</label>
                            <select class="form-select" name="category_id" required>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?><?php echo $category['status'] === 'inactive' ? ' (hidden)' : ''; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price *</label>
                            <input type="number" class="form-control" name="price" step="0.01" min="0.01" max="99999999.99" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Item Name *</label>
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
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_available" id="addAvailable" checked>
                                <label class="form-check-label" for="addAvailable">Available</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_featured" id="addFeatured">
                                <label class="form-check-label" for="addFeatured">Featured Item</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-admin-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-admin-primary">Add Menu Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php foreach ($menu_items as $item): ?>
<div class="modal fade" id="editItemModal<?php echo $item['id']; ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Menu Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category *</label>
                            <select class="form-select" name="category_id" required>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo $item['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?><?php echo $category['status'] === 'inactive' ? ' (hidden)' : ''; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price *</label>
                            <input type="number" class="form-control" name="price" step="0.01" min="0.01" max="99999999.99" value="<?php echo $item['price']; ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Item Name *</label>
                        <input type="text" class="form-control" name="name" maxlength="100" value="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" maxlength="4000"><?php echo htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Image</label>
                        <?php if ($item['image']): ?>
                        <img src="<?php echo SITE_URL; ?>/uploads/menu/<?php echo htmlspecialchars(rawurlencode(basename($item['image'])), ENT_QUOTES, 'UTF-8'); ?>" alt="Current image" style="width: 100px; height: 100px; object-fit: cover; border-radius: 5px;">
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Image</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_available" id="editAvailable<?php echo $item['id']; ?>" <?php echo $item['is_available'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="editAvailable<?php echo $item['id']; ?>">Available</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_featured" id="editFeatured<?php echo $item['id']; ?>" <?php echo $item['is_featured'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="editFeatured<?php echo $item['id']; ?>">Featured Item</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-admin-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-admin-primary">Update Menu Item</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php require_once 'includes/footer.php'; ?>
