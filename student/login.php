<?php
// student/login.php
// Student login: username, password. Link to Forgot Password. No create account (admin only).

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/student_auth.php';

if (student_is_logged_in()) {
    header('Location: ' . BASE_URL . '/student/dashboard.php');
    exit;
}

$errors = [];
$max_login_attempts = 3;
$lockout_seconds = 60;
$now = time();
$lockout_remaining_seconds = 0;

if (!isset($_SESSION['student_login_attempts'])) {
    $_SESSION['student_login_attempts'] = 0;
}

if (!empty($_SESSION['student_login_locked_until']) && $now >= (int)$_SESSION['student_login_locked_until']) {
    $_SESSION['student_login_attempts'] = 0;
    unset($_SESSION['student_login_locked_until']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    $locked_until = (int)($_SESSION['student_login_locked_until'] ?? 0);

    if ($locked_until > $now) {
        $lockout_remaining_seconds = $locked_until - $now;
        $errors[] = 'Too many failed login attempts. Please wait before trying again.';
    } elseif ($username === '' || $password === '') {
        $errors[] = 'Username and password are required.';
    } else {
        $pdo = db_connect();
        $stmt = $pdo->prepare(
            'SELECT id, name, student_id, course, section, email, username, password_hash FROM students WHERE username = :username LIMIT 1'
        );
        $stmt->execute([':username' => $username]);
        $student = $stmt->fetch();

        if ($student && password_verify($password, $student['password_hash'])) {
            session_regenerate_id(true);

            student_login(
                (int)$student['id'],
                $student['name'],
                $student['student_id'],
                $student['course'],
                $student['section'],
                $student['email'],
                $student['username']
            );

            if ($remember_me) {
                student_store_remember_token($pdo, (int)$student['id']);
            } else {
                student_delete_current_remember_token($pdo);
            }

            $_SESSION['student_login_success'] = true;
            $_SESSION['student_login_attempts'] = 0;
            unset($_SESSION['student_login_locked_until']);

            header('Location: ' . BASE_URL . '/student/dashboard.php');
            exit;
        }
        $_SESSION['student_login_attempts']++;

        if ($_SESSION['student_login_attempts'] >= $max_login_attempts) {
            $_SESSION['student_login_locked_until'] = $now + $lockout_seconds;
            $lockout_remaining_seconds = $lockout_seconds;
            $errors[] = 'Too many failed login attempts. Login is temporarily blocked for 1 minute.';
        } else {
            $attempts_left = $max_login_attempts - (int)$_SESSION['student_login_attempts'];
            $errors[] = 'Invalid username or password. Attempts remaining: ' . $attempts_left . '.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login | Smart Library</title>
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/images/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        :root { --sl-primary: <?php echo COLOR_PRIMARY; ?>; --sl-accent: <?php echo COLOR_ACCENT; ?>; --sl-light: <?php echo COLOR_LIGHT; ?>; }
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: url('<?php echo BASE_URL; ?>/assets/images/backgroundsmart.jpg') no-repeat center center fixed; background-size: cover; font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        .login-card { max-width: 420px; width: 100%; border-radius: 16px; border: none; box-shadow: 0 18px 45px rgba(0, 0, 0, 0.15); overflow: hidden; }
        .login-header { background: linear-gradient(135deg, var(--sl-primary), #4a0000); color: var(--sl-light); padding: 1.5rem 1.75rem; }
        .login-header h1 { font-size: 1.5rem; margin: 0; }
        .login-header p { margin: 0.25rem 0 0; opacity: 0.85; }
        .badge-evsu { background-color: var(--sl-accent); color: #000; }
        .btn-sl-primary { background-color: var(--sl-primary); color: var(--sl-light); border: none; }
        .btn-sl-primary:hover { background-color: #5c0000; color: var(--sl-light); }
        .password-wrapper { position: relative; }
        .password-wrapper .form-control { padding-right: 2.75rem; }
        .password-wrapper input[type="password"]::-ms-reveal,
        .password-wrapper input[type="password"]::-ms-clear { display: none; }
        .password-toggle {
            position: absolute;
            top: 50%;
            right: 0.5rem;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            padding: 0.25rem;
            line-height: 1;
            color: #6c757d;
        }
        .password-toggle:hover { color: #212529; }
    </style>
</head>
<body>
<div class="card login-card">
    <div class="login-header d-flex justify-content-between align-items-center">
        <div>
            <h1>EVSU Smart Library</h1>
            <p class="small">Student Portal</p>
        </div>
        <span class="badge badge-evsu">Student</span>
    </div>
    <div class="card-body p-4">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e): ?>
                    <div><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endforeach; ?>
                <?php if ($lockout_remaining_seconds > 0): ?>
                    <div>
                        You can try again in <span id="lockout-countdown"><?php echo (int)$lockout_remaining_seconds; ?></span> seconds.
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <form method="post" novalidate>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="password-wrapper">
                    <input type="password" class="form-control" id="password" name="password" required>
                    <button type="button" class="password-toggle" id="toggle-password" aria-label="Show password">
                        <span id="toggle-password-icon" aria-hidden="true">&#128065;</span>
                    </button>
                </div>
            </div>
            <div class="form-check mb-3">
                <input
                    class="form-check-input"
                    type="checkbox"
                    id="remember_me"
                    name="remember_me"
                    value="1"
                >
                <label class="form-check-label" for="remember_me">Remember me for 30 days</label>
            </div>
            <div class="d-grid mb-2">
                <button type="submit" class="btn btn-sl-primary" id="login-button" <?php echo $lockout_remaining_seconds > 0 ? 'disabled' : ''; ?>>Login</button>
            </div>
            <div class="text-center">
                <a href="<?php echo BASE_URL; ?>/student/forgot_password.php">Forgot Password?</a>
            </div>
        </form>
        <p class="text-muted small text-center mt-3 mb-0">No account? Only an administrator can create student accounts.</p>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function () {
        const passwordInput = document.getElementById('password');
        const toggleButton = document.getElementById('toggle-password');
        const toggleIcon = document.getElementById('toggle-password-icon');

        if (!passwordInput || !toggleButton || !toggleIcon) {
            return;
        }

        toggleButton.addEventListener('click', function () {
            const isPasswordHidden = passwordInput.type === 'password';
            passwordInput.type = isPasswordHidden ? 'text' : 'password';
            toggleButton.setAttribute('aria-label', isPasswordHidden ? 'Hide password' : 'Show password');
            toggleIcon.textContent = '\u{1F441}';
        });

        const countdown = document.getElementById('lockout-countdown');
        const loginButton = document.getElementById('login-button');

        if (countdown) {
            let secondsLeft = parseInt(countdown.textContent, 10);

            const timer = window.setInterval(function () {
                secondsLeft -= 1;

                if (secondsLeft <= 0) {
                    countdown.textContent = '0';
                    if (loginButton) {
                        loginButton.disabled = false;
                    }
                    window.clearInterval(timer);
                    return;
                }

                countdown.textContent = String(secondsLeft);
            }, 1000);
        }
    })();
</script>
</body>
</html>
