<?php


declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/admin_auth.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $errors[] = 'Username and password are required.';
    } else {
        $pdo = db_connect();
        $stmt = $pdo->prepare('SELECT id, username, password_hash FROM admins WHERE username = :username LIMIT 1');
        $stmt->execute([':username' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            admin_login((int)$admin['id'], $admin['username']);
            header('Location: ' . BASE_URL . '/admin/dashboard.php');
            exit;
        } else {
            $errors[] = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Library Admin Login</title>
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/images/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/responsive.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/modern-minimal.css">
    <style>
        :root {
            --sl-primary: <?php echo COLOR_PRIMARY; ?>;
            --sl-accent: <?php echo COLOR_ACCENT; ?>;
            --sl-light: <?php echo COLOR_LIGHT; ?>;
        }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                linear-gradient(rgba(255, 255, 255, 0.46), rgba(255, 255, 255, 0.46)),
                url('<?php echo BASE_URL; ?>/assets/images/student-login-smart-library.png') no-repeat center center fixed !important;
            background-size: cover !important;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            padding: 2rem clamp(1rem, 6vw, 5rem);
        }
        .login-card {
            max-width: 430px;
            width: 100%;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.9) !important;
            box-shadow: 0 24px 70px rgba(17, 17, 17, 0.36) !important;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.97) !important;
            backdrop-filter: blur(10px);
        }
        .login-header {
            background: linear-gradient(135deg, #720000, #4a0000) !important;
            color: var(--sl-light);
            padding: 1.5rem 1.75rem;
        }
        .login-header h1 {
            font-size: 1.5rem;
            margin: 0;
        }
        .login-header p {
            margin: 0.25rem 0 0;
            opacity: 0.85;
        }
        .login-logo {
            width: 56px;
            height: 56px;
            object-fit: contain;
            background: #fff;
            border-radius: 50%;
            padding: 3px;
        }
        .badge-evsu {
            background-color: var(--sl-accent);
            color: #000;
        }
        .btn-sl-primary {
            background-color: var(--sl-primary);
            color: var(--sl-light);
            border: none;
        }
        .btn-sl-primary:hover {
            background-color: #5c0000;
            color: var(--sl-light);
        }
        .password-wrapper {
            position: relative;
        }
        .password-wrapper .form-control {
            padding-right: 2.75rem;
        }
        .password-wrapper input[type="password"]::-ms-reveal,
        .password-wrapper input[type="password"]::-ms-clear {
            display: none;
        }
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
        .password-toggle:hover {
            color: #212529;
        }
        @media (max-width: 991.98px) {
            body {
                background:
                    linear-gradient(rgba(255, 255, 255, 0.54), rgba(255, 255, 255, 0.54)),
                    url('<?php echo BASE_URL; ?>/assets/images/student-login-smart-library.png') no-repeat center center fixed !important;
                background-size: cover !important;
            }
        }
        @media (max-width: 575.98px) {
            body {
                align-items: flex-start;
                padding: 1rem;
            }
            .login-card {
                margin-top: 1.25rem;
            }
            .login-header {
                padding: 1.2rem;
            }
            .login-header h1 {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>
<div class="card login-card">
    <div class="login-header d-flex justify-content-between align-items-center">
        <div>
            <h1>EVSU Smart Library</h1>
            <p class="small">Administrator Portal</p>
        </div>
        <img src="<?php echo BASE_URL; ?>/assets/images/evsu-logo.png" alt="EVSU logo" class="login-logo">
    </div>
    <div class="card-body p-4">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="post" novalidate>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input
                    type="text"
                    class="form-control"
                    id="username"
                    name="username"
                    required
                    autofocus
                >
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="password-wrapper">
                    <input
                        type="password"
                        class="form-control"
                        id="password"
                        name="password"
                        required
                    >
                    <button type="button" class="password-toggle" id="toggle-password" aria-label="Show password">
                        <span id="toggle-password-icon" aria-hidden="true">&#128065;</span>
                    </button>
                </div>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-sl-primary">
                    Login
                </button>
            </div>
        </form>
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
    })();
</script>
</body>
</html>

