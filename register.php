<?php
$page_title = 'Register';
require_once 'config/config.php';
require_once 'includes/functions.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $message = 'Please fill in all fields.';
        $message_type = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $message_type = 'danger';
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $message = 'Please enter a valid 10-digit phone number.';
        $message_type = 'danger';
    } elseif (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters long.';
        $message_type = 'danger';
    } elseif ($password !== $confirm_password) {
        $message = 'Passwords do not match.';
        $message_type = 'danger';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $message = 'Email already registered. Please use a different email or login.';
            $message_type = 'danger';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role, status) VALUES (?, ?, ?, ?, 'customer', 'active')");

            if ($stmt->execute([$name, $email, $phone, $hashed_password])) {
                $message = 'Registration successful! You can now login.';
                $message_type = 'success';
                if (isset($_SESSION['booking_data'])) {
                    $_SESSION['redirect_after_register'] = true;
                }
                header("refresh:2;url=login.php");
            } else {
                $message = 'Registration failed. Please try again.';
                $message_type = 'danger';
            }
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
    <title>Register - 4 TO 9</title>
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
                    <h2>Make yourself at home.</h2>
                    <p>Create an account to keep your visits together.</p>
                    <div class="auth-features">
                        <div class="feature-item">
                            <i class="bi bi-table"></i>
                            <span>Easy Table Selection</span>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-calendar-check"></i>
                            <span>Manage your reservations</span>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-star"></i>
                            <span>Come back to a familiar place</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="auth-form-side">
                <div class="auth-card">
                    <a class="auth-brand" href="index.php" aria-label="4 TO 9 Restaurant, home"><img src="<?php echo SITE_URL; ?>/assets/images/logo-4to9.png" alt="" width="156" height="104"></a>
                    <div class="auth-header">
                        <h1>Join us.</h1>
                        <p>Just a few details, then you're ready to book.</p>
                    </div>

                    <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label" for="register-name">Full name</label>
                            <div class="input-wrapper">
                                <i class="bi bi-person"></i>
                                <input id="register-name" type="text" class="form-control" name="name" autocomplete="name" placeholder="Enter your full name" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="register-email">Email address</label>
                            <div class="input-wrapper">
                                <i class="bi bi-envelope"></i>
                                <input id="register-email" type="email" class="form-control" name="email" autocomplete="email" placeholder="Enter your email" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="register-phone">Phone number</label>
                            <div class="input-wrapper">
                                <i class="bi bi-telephone"></i>
                                <input id="register-phone" type="tel" class="form-control" name="phone" autocomplete="tel" placeholder="10-digit number" pattern="[0-9]{10}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="register-password">Password</label>
                            <div class="input-wrapper">
                                <i class="bi bi-lock"></i>
                                <input id="register-password" type="password" class="form-control" name="password" autocomplete="new-password" placeholder="Create a password" minlength="6" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="register-confirm">Confirm password</label>
                            <div class="input-wrapper">
                                <i class="bi bi-lock-fill"></i>
                                <input id="register-confirm" type="password" class="form-control" name="confirm_password" autocomplete="new-password" placeholder="Confirm your password" minlength="6" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-hero">Create Account</button>
                    </form>

                    <div class="auth-divider">
                        <span>or</span>
                    </div>

                    <div class="auth-links">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
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
