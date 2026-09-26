<?php
$page_title = 'Gallery Management';
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once 'includes/menu-editor-security.php';
require_once 'includes/header.php';

$message = '';
$message_type = '';
$csrf_token = menuEditorCsrfToken();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $uploaded = null;
    try {
        menuEditorVerifyCsrf();
        $action = menuEditorText('action', 20, true);
        if ($action === 'add') {
            $title = menuEditorText('title', 100);
            $status = menuEditorText('status', 8, true);
            if (!in_array($status, ['active', 'inactive'], true)) {
                throw new InvalidArgumentException('Choose a valid gallery status.');
            }
            $uploaded = menuEditorUploadImage('gallery');
            if ($uploaded === null) {
                throw new InvalidArgumentException('Choose a JPG, PNG, or WebP image to upload.');
            }
            $stmt = $conn->prepare('INSERT INTO gallery (title, image, status) VALUES (?, ?, ?)');
            $stmt->execute([$title, $uploaded, $status]);
            $uploaded = null;
            $message = 'Image added to gallery successfully.';
        } elseif ($action === 'delete') {
            $image_id = menuEditorId('image_id');
            $stmt = $conn->prepare('SELECT image FROM gallery WHERE id = ?');
            $stmt->execute([$image_id]);
            $image = $stmt->fetchColumn();
            if ($image === false) {
                throw new InvalidArgumentException('That gallery image no longer exists.');
            }
            $stmt = $conn->prepare('DELETE FROM gallery WHERE id = ?');
            $stmt->execute([$image_id]);
            $image_path = __DIR__ . '/../uploads/gallery/' . basename($image);
            if (is_file($image_path)) {
                @unlink($image_path);
            }
            $message = 'Image deleted successfully.';
        } else {
            throw new InvalidArgumentException('Choose a valid gallery action.');
        }
        $message_type = 'success';
    } catch (InvalidArgumentException $exception) {
        $message = $exception->getMessage();
        $message_type = 'danger';
    } catch (Throwable $exception) {
        error_log('Gallery management error: ' . $exception->getMessage());
        $message = 'The gallery change could not be saved. Please try again.';
        $message_type = 'danger';
    } finally {
        if ($uploaded !== null) {
            @unlink(__DIR__ . '/../uploads/gallery/' . basename($uploaded));
        }
    }
}

$stmt = $conn->prepare("SELECT * FROM gallery ORDER BY created_at DESC");
$stmt->execute();
$gallery_images = $stmt->fetchAll();
?>

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Restaurant Gallery</h3>
        <button class="btn btn-admin-primary" data-bs-toggle="modal" data-bs-target="#addImageModal">
            <i class="bi bi-plus-circle"></i> Add Image
        </button>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <?php if (count($gallery_images) > 0): ?>
            <?php foreach ($gallery_images as $image): ?>
            <div class="col-md-4 mb-4">
                <div class="admin-card">
                    <div class="position-relative">
                        <img src="<?php echo SITE_URL; ?>/uploads/gallery/<?php echo htmlspecialchars(rawurlencode(basename($image['image'])), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($image['title'], ENT_QUOTES, 'UTF-8'); ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 5px;">
                        <span class="position-absolute top-0 end-0 m-2 status-badge <?php echo htmlspecialchars($image['status'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo ucfirst($image['status']); ?>
                        </span>
                    </div>
                    <div class="mt-3">
                        <h5><?php echo htmlspecialchars($image['title']); ?></h5>
                        <small class="text-secondary">Added: <?php echo formatDate($image['created_at'], 'd M Y'); ?></small>
                        <div class="mt-2">
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="image_id" value="<?php echo (int)$image['id']; ?>">
                                <button type="submit" class="btn btn-action btn-delete btn-delete-confirm">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center">
                <p class="text-secondary">No gallery images found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="addImageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Gallery Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" name="title">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image *</label>
                        <input type="file" class="form-control" name="image" accept="image/*" required>
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
                    <button type="submit" class="btn btn-admin-primary">Add Image</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
