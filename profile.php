<?php
$page_title = 'My Profile';
$page_stylesheet = 'assets/css/account.css';
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_action = $_POST['form_action'] ?? '';

    if ($form_action === 'profile') {
        $name = sanitize($_POST['name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');

        if ($name === '' || $phone === '') {
            $message = 'Please enter your name and phone number.';
            $message_type = 'danger';
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
            if ($stmt->execute([$name, $phone, $_SESSION['user_id']])) {
                $_SESSION['user_name'] = $name;
                $message = 'Profile updated successfully!';
                $message_type = 'success';
            } else {
                $message = 'Failed to update profile.';
                $message_type = 'danger';
            }
        }
    } elseif ($form_action === 'password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if ($current_password === '' || $new_password === '' || $confirm_password === '') {
            $message = 'Complete all password fields to continue.';
            $message_type = 'danger';
        } elseif ($new_password !== $confirm_password || strlen($new_password) < 6) {
            $message = 'New passwords do not match or are too short.';
            $message_type = 'danger';
        } else {
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($current_password, $user['password'])) {
                $message = 'Current password is incorrect.';
                $message_type = 'danger';
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($stmt->execute([$hashed_password, $_SESSION['user_id']])) {
                    $message = 'Password updated successfully!';
                    $message_type = 'success';
                } else {
                    $message = 'Failed to update password.';
                    $message_type = 'danger';
                }
            }
        }
    }
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM bookings WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$booking_count = $stmt->fetch()['total'];

require_once 'includes/header.php';
?>

<section class="section account-page profile-page">
    <div class="container">
        <div class="section-title account-page-heading">
            <span class="eyebrow">Your account</span>
            <h1>My profile</h1>
            <p>Keep your details up to date for a smoother visit.</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <div class="profile-layout">
            <div class="profile-sidebar">
                <div class="profile-card-sidebar">
                    <div class="profile-avatar-large">
                        <?php echo htmlspecialchars(strtoupper(substr($user['name'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                    <div class="profile-status">
                        <span class="status-dot active" aria-hidden="true"></span>
                        <span>Member account</span>
                    </div>

                    <div class="profile-stats-grid">
                        <div class="stat-box">
                            <div class="stat-number"><?php echo (int)$booking_count; ?></div>
                            <div class="stat-label">Total Bookings</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-number"><?php echo formatDate($user['created_at'], 'M Y'); ?></div>
                            <div class="stat-label">Member Since</div>
                        </div>
                    </div>

                    <div class="profile-actions">
                        <a href="my-bookings.php" class="btn-action">
                            <i class="bi bi-calendar-check" aria-hidden="true"></i> My bookings
                        </a>
                        <a href="logout.php" class="btn-action danger">
                            <i class="bi bi-box-arrow-right" aria-hidden="true"></i> Sign out
                        </a>
                    </div>
                </div>
            </div>

            <div class="profile-main">
                <div class="profile-card-main">
                    <h2>Edit profile</h2>
                    <p class="card-intro">These details help us identify your reservation and reach you if plans change.</p>
                    <form method="POST">
                        <input type="hidden" name="form_action" value="profile">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="profile-name">Full name</label>
                                <input id="profile-name" type="text" class="form-control" name="name" autocomplete="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="profile-email">Email address</label>
                                <input id="profile-email" type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" aria-describedby="profile-email-hint" readonly>
                                <small id="profile-email-hint">Your email address cannot be changed here.</small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="profile-phone">Phone number</label>
                            <input id="profile-phone" type="tel" class="form-control" name="phone" autocomplete="tel" inputmode="numeric" value="<?php echo htmlspecialchars($user['phone']); ?>" pattern="[0-9]{10}" aria-describedby="profile-phone-hint" required>
                            <small id="profile-phone-hint">Enter a 10-digit phone number.</small>
                        </div>
                        <button type="submit" class="btn btn-hero"><i class="bi bi-check2" aria-hidden="true"></i> Save changes</button>
                    </form>
                </div>

                <div class="profile-card-main">
                    <h2>Change password</h2>
                    <p class="card-intro">Use at least six characters for your new password.</p>
                    <form method="POST">
                        <input type="hidden" name="form_action" value="password">
                        <div class="form-group">
                            <label class="form-label" for="current-password">Current password</label>
                            <input id="current-password" type="password" class="form-control" name="current_password" autocomplete="current-password" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="new-password">New password</label>
                                <input id="new-password" type="password" class="form-control" name="new_password" autocomplete="new-password" minlength="6" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="confirm-password">Confirm new password</label>
                                <input id="confirm-password" type="password" class="form-control" name="confirm_password" autocomplete="new-password" minlength="6" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-hero"><i class="bi bi-shield-lock" aria-hidden="true"></i> Update password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.profile-layout {
    display: grid;
    grid-template-columns: minmax(270px, 320px) minmax(0, 1fr);
    gap: 1.5rem;
    align-items: start;
}

.profile-sidebar {
    position: sticky;
    top: 100px;
    height: fit-content;
}

.profile-card-sidebar {
    background: var(--card-background);
    padding: 2rem;
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-color);
    border-top: 4px solid var(--primary-blue);
    box-shadow: var(--shadow-sm);
    text-align: center;
}

.profile-avatar-large {
    width: 88px;
    height: 88px;
    margin: 0 auto 1.2rem;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.4rem;
    color: white;
    font-weight: 700;
    box-shadow: var(--shadow-sm);
}

.profile-card-sidebar h3 {
    color: var(--dark-blue);
    margin-bottom: 0.5rem;
    font-size: 1.5rem;
}

.profile-card-sidebar p {
    color: var(--text-gray);
    margin-bottom: 1rem;
    font-size: 1rem;
}

.profile-status {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    color: var(--text-gray);
    font-size: .85rem;
}

.status-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.status-dot.active {
    background: var(--success);
    box-shadow: 0 0 0 4px #e7f4ec;
}

.profile-stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: .65rem;
    margin-bottom: 1.5rem;
}

.stat-box {
    background: var(--soft-purple);
    padding: 1rem .7rem;
    border-radius: var(--radius-md);
    border: 1px solid var(--border-color);
}

.stat-box .stat-number {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--dark-blue);
}

.stat-box .stat-label {
    color: var(--text-gray);
    font-size: 0.76rem;
    margin-top: 0.35rem;
}

.profile-actions {
    display: flex;
    flex-direction: column;
    gap: .65rem;
}

.btn-action {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: .8rem 1rem;
    border-radius: var(--radius-md);
    border: 1px solid var(--primary-blue);
    color: var(--primary-blue);
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-action:hover {
    background: var(--primary-blue);
    color: white;
    transform: translateY(-2px);
}

.btn-action.danger {
    border-color: var(--danger);
    color: var(--danger);
}

.btn-action.danger:hover {
    background: var(--danger);
    color: white;
}

.profile-main {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.profile-card-main {
    background: var(--card-background);
    padding: clamp(1.4rem, 3vw, 2.4rem);
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
}

.profile-card-main h2 {
    color: var(--dark-blue);
    margin-bottom: .4rem;
    font-size: 1.6rem;
}

.profile-card-main .card-intro {
    color: var(--text-gray);
    margin-bottom: 1.5rem;
    font-size: .94rem;
}

.profile-page .form-control[readonly] {
    background: var(--soft-purple);
    color: var(--text-gray);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group small {
    color: var(--text-light);
    font-size: 0.85rem;
    margin-top: 0.5rem;
    display: block;
}

@media (max-width: 992px) {
    .profile-layout {
        grid-template-columns: 1fr;
    }

    .profile-sidebar {
        position: static;
    }
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }

    .profile-page .profile-card-main .btn-hero { width: 100%; }
}

@media (max-width: 380px) {
    .profile-stats-grid { grid-template-columns: 1fr; }
}
</style>

<?php require_once 'includes/footer.php'; ?>
