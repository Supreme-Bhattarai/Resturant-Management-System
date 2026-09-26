<?php
$page_title = 'Admin Profile';
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
        $action = menuEditorText('action', 20, true);
        if ($action === 'update_profile') {
            $name = menuEditorText('name', 100, true);
            $email = menuEditorText('email', 100, true);
            $phone = menuEditorText('phone', 20, true);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[0-9]{10}$/', $phone)) {
                throw new InvalidArgumentException('Enter a valid email address and 10 digit phone number.');
            }
            $stmt = $conn->prepare('UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ? AND role = ?');
            $stmt->execute([$name, $email, $phone, $_SESSION['user_id'], 'admin']);
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $message = 'Profile updated successfully.';
        } elseif ($action === 'change_password') {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            if (!is_string($current_password) || !is_string($new_password) || !is_string($confirm_password) ||
                $current_password === '' || strlen($new_password) < 6 || $new_password !== $confirm_password) {
                throw new InvalidArgumentException('Enter your current password and matching new passwords of at least 6 characters.');
            }
            $stmt = $conn->prepare('SELECT password FROM users WHERE id = ? AND role = ?');
            $stmt->execute([$_SESSION['user_id'], 'admin']);
            $user = $stmt->fetch();
            if (!$user || !password_verify($current_password, $user['password'])) {
                throw new InvalidArgumentException('Current password is incorrect.');
            }
            $stmt = $conn->prepare('UPDATE users SET password = ? WHERE id = ? AND role = ?');
            $stmt->execute([password_hash($new_password, PASSWORD_DEFAULT), $_SESSION['user_id'], 'admin']);
            $message = 'Password updated successfully.';
        } else {
            throw new InvalidArgumentException('Choose a valid profile action.');
        }
        $message_type = 'success';
    } catch (InvalidArgumentException $exception) {
        $message = $exception->getMessage();
        $message_type = 'danger';
    } catch (Throwable $exception) {
        error_log('Admin profile error: ' . $exception->getMessage());
        $message = 'The change could not be saved. Please try again.';
        $message_type = 'danger';
    }
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch();
?>

<div class="row">
    <div class="col-md-4">
        <div class="admin-card">
            <div class="text-center">
                <div class="profile-avatar mx-auto mb-3" style="width: 120px; height: 120px;">
                    <i class="bi bi-person-badge" aria-hidden="true"></i>
                </div>
                <h3><?php echo htmlspecialchars($admin['name']); ?></h3>
                <p class="text-secondary"><?php echo htmlspecialchars($admin['email']); ?></p>
                <span class="badge bg-success">Administrator</span>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p><strong>Member Since:</strong> <?php echo formatDate($admin['created_at']); ?></p>
                <p><strong>Status:</strong> <span class="status-badge <?php echo $admin['status']; ?>"><?php echo ucfirst($admin['status']); ?></span></p>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="admin-card">
            <h3>Edit Profile</h3>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="action" value="update_profile">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($admin['phone']); ?>" pattern="[0-9]{10}" required>
                </div>
                <button type="submit" class="btn btn-admin-primary">Update Profile</button>
            </form>

            <hr class="my-4">

            <h3>Change Password</h3>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="action" value="change_password">
                <div class="mb-3">
                    <label class="form-label">Current Password</label>
                    <input type="password" class="form-control" name="current_password" required autocomplete="current-password">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control" name="new_password" minlength="6" required autocomplete="new-password">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" name="confirm_password" minlength="6" required autocomplete="new-password">
                    </div>
                </div>
                <button type="submit" class="btn btn-admin-primary">Change Password</button>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
