<?php
$page_title = 'Login';
require_once 'config/config.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    if (isset($_SESSION['redirect_after_login'])) {
        $redirect_url = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']);
        redirect($redirect_url);
    } else {
        if ($_SESSION['user_role'] === 'admin') {
            redirect('admin/dashboard.php');
        } else {
            redirect('index.php');
        }
    }
}

$message = '';
$message_type = '';

if (isset($_SESSION['redirect_after_login'])) {
    $message = 'Please login to complete your table reservation.';
    $message_type = 'warning';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $message = 'Please fill in all fields.';
        $message_type = 'danger';
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            session_regenerate_id(true);

            if ($user['role'] === 'admin') {
                if (isset($_SESSION['redirect_after_login'])) {
                    $redirect_url = $_SESSION['redirect_after_login'];
                    unset($_SESSION['redirect_after_login']);
                    redirect($redirect_url);
                } else {
                    redirect('admin/dashboard.php');
                }
            } else {
                if (isset($_SESSION['redirect_after_login'])) {
                    $redirect_url = $_SESSION['redirect_after_login'];
                    unset($_SESSION['redirect_after_login']);
                    redirect($redirect_url);
                } else {
                    redirect('index.php');
                }
            }
        } else {
            $message = 'Invalid email or password. Please try again.';
            $message_type = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#142b59">
    <title>Login - 4 TO 9 Restaurant</title>
    <link rel="icon" type="image/svg+xml" href="<?php echo SITE_URL; ?>/assets/images/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/assets/css/style.css'); ?>">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/experience.css?v=<?php echo filemtime(__DIR__ . '/assets/css/experience.css'); ?>">
</head>
<body>
    <div class="auth-container">
        <div class="auth-layout">
            <div class="auth-image-side">
                <div class="auth-image-content">
                    <h2>Welcome back.</h2>
                    <p>Your table and your next good meal are waiting.</p>
                    <div class="auth-features">
                        <div class="feature-item">
                            <i class="bi bi-shield-check"></i>
                            <span>Keep your bookings in one place</span>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-person-badge"></i>
                            <span>Find your favorite table</span>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-lock"></i>
                            <span>Feel right at home</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="auth-form-side">
                <div class="auth-card">
                    <a class="auth-brand" href="index.php" aria-label="4 TO 9 Restaurant, home"><img src="<?php echo SITE_URL; ?>/assets/images/logo-4to9.png" alt="" width="156" height="104"></a>
                    <div class="auth-header">
                        <h1>Sign in.</h1>
                        <p>Pick up where you left off.</p>
                    </div>

                    <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label" for="login-email">Email address</label>
                            <div class="input-wrapper">
                                <i class="bi bi-envelope"></i>
                                <input id="login-email" type="email" class="form-control" name="email" autocomplete="email" placeholder="Enter your email" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="login-password">Password</label>
                            <div class="input-wrapper">
                                <i class="bi bi-lock"></i>
                                <input id="login-password" type="password" class="form-control" name="password" autocomplete="current-password" placeholder="Enter your password" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-hero">Login to Account</button>
                    </form>

                    <div class="auth-divider">
                        <span>or</span>
                    </div>

                    <div class="auth-links">
                        <p>Don't have an account? <a href="register.php">Create Account</a></p>
                        <p><a href="index.php">Back to Home</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<style>
.auth-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--soft-purple), var(--light-purple), var(--light-blue));
    padding: 2rem;
}

.auth-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    max-width: 1100px;
    width: 100%;
    border-radius: var(--radius-2xl);
    overflow: hidden;
    box-shadow: var(--shadow-xl);
}

.auth-image-side {
    background: linear-gradient(135deg, var(--dark-blue), var(--primary-blue));
    padding: 4rem;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.auth-image-side::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
    animation: float 25s infinite ease-in-out;
}

@keyframes float {
    0%, 100% { transform: translate(0, 0) rotate(0deg); }
    50% { transform: translate(40px, 40px) rotate(5deg); }
}

.auth-image-content {
    position: relative;
    z-index: 1;
    color: white;
}

.auth-image-content h2 {
    color: white;
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.auth-image-content p {
    font-size: 1.1rem;
    margin-bottom: 3rem;
    opacity: 0.9;
}

.auth-features {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 1.1rem;
}

.feature-item i {
    font-size: 1.5rem;
    opacity: 0.8;
}

.auth-form-side {
    background: var(--card-background);
    padding: 4rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.auth-card {
    width: 100%;
    max-width: 450px;
}

.auth-header {
    text-align: center;
    margin-bottom: 2.5rem;
}

.auth-header h1 {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    color: var(--dark-teal);
}

.auth-header p {
    color: var(--text-gray);
    font-size: 1.1rem;
}

.input-wrapper {
    position: relative;
}

.input-wrapper i {
    position: absolute;
    left: 1.2rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--primary-blue);
    font-size: 1.2rem;
}

.input-wrapper .form-control {
    padding-left: 3rem;
}

.auth-divider {
    display: flex;
    align-items: center;
    margin: 2rem 0;
    color: var(--text-gray);
}

.auth-divider::before,
.auth-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border-color);
}

.auth-divider span {
    padding: 0 1rem;
    font-size: 0.9rem;
}

.auth-links {
    text-align: center;
}

.auth-links p {
    color: var(--text-gray);
    margin-bottom: 1rem;
}

.auth-links a {
    color: var(--primary-teal);
    font-weight: 600;
    transition: all 0.3s ease;
}

.auth-links a:hover {
    color: var(--dark-teal);
}

@media (max-width: 768px) {
    .auth-layout {
        grid-template-columns: 1fr;
    }

    .auth-image-side {
        display: none;
    }

    .auth-form-side {
        padding: 2rem;
    }
}
</style>
