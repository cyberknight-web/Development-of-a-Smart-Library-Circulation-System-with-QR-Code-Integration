<?php
// student/forgot_password.php
// Forgot password: enter registered email, system sends reset link using PHPMailer.

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/student_auth.php';
require_once __DIR__ . '/../includes/mail_helper.php';
require_once __DIR__ . '/../includes/password_reset_tokens.php';

if (student_is_logged_in()) {
    header('Location: ' . BASE_URL . '/student/dashboard.php');
    exit;
}

$message = null;
$is_error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    if ($email === '') {
        $message = 'Please enter your registered email.';
        $is_error = true;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $is_error = true;
    } else {
        try {
            $pdo = db_connect();
            ensure_password_reset_tokens_table($pdo);

            $stmt = $pdo->prepare('SELECT id, name FROM students WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            $student = $stmt->fetch();
            if (!$student) {
                $message = 'No account found with that email.';
                $is_error = true;
            } else {
                $token = bin2hex(random_bytes(32));
                $expires = (new DateTimeImmutable('now', new DateTimeZone(APP_TIMEZONE)))->modify('+1 hour')->format('Y-m-d H:i:s');

                $del = $pdo->prepare('DELETE FROM password_reset_tokens WHERE student_id = :id');
                $del->execute([':id' => $student['id']]);

                $ins = $pdo->prepare('INSERT INTO password_reset_tokens (student_id, token, expires_at) VALUES (:id, :token, :expires)');
                $ins->execute([':id' => $student['id'], ':token' => $token, ':expires' => $expires]);

                $reset_link = BASE_URL . '/student/reset_password.php?token=' . urlencode($token);
                $body = '<p>Hello ' . htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8') . ',</p>';
                $body .= '<p>You requested a password reset. Click the link below to set a new password (valid for 1 hour):</p>';
                $body .= '<p><a href="' . htmlspecialchars($reset_link, ENT_QUOTES, 'UTF-8') . '">Reset Password</a></p>';
                $body .= '<p>If you did not request this, ignore this email.</p>';

                $sent = send_mail($email, $student['name'], 'Smart Library - Reset Password', $body);
                if ($sent) {
                    $message = 'If that email is registered, we sent a reset link. Check your inbox.';
                } else {
                    $message = 'We could not send the email. Please try again or contact the administrator.';
                    $is_error = true;
                }
            }
        } catch (PDOException $e) {
            error_log('Forgot password DB error: ' . $e->getMessage());
            $message = 'We could not process your request right now. Please try again later.';
            $is_error = true;
        } catch (Throwable $e) {
            error_log('Forgot password error: ' . $e->getMessage());
            $message = 'We could not process your request right now. Please try again later.';
            $is_error = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Smart Library</title>
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/images/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/responsive.css">
    <style>
        :root { --sl-primary: <?php echo COLOR_PRIMARY; ?>; --sl-accent: <?php echo COLOR_ACCENT; ?>; --sl-light: <?php echo COLOR_LIGHT; ?>; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                linear-gradient(rgba(255, 255, 255, 0.82), rgba(255, 255, 255, 0.9)),
                url('<?php echo BASE_URL; ?>/assets/images/backgroundsmart.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            padding: 1rem;
        }
        .forgot-card {
            max-width: 440px;
            border-radius: 12px;
            border: 1px solid rgba(128, 0, 0, 0.08);
            box-shadow: 0 18px 45px rgba(0,0,0,0.14);
            overflow: hidden;
        }
        .forgot-header {
            background: linear-gradient(135deg, var(--sl-primary), #4a0000);
            color: var(--sl-light);
            padding: 1.35rem 1.5rem;
        }
        .forgot-header h1 {
            font-size: 1.35rem;
            margin: 0;
        }
        .forgot-header p {
            color: rgba(255, 255, 255, 0.86);
            margin: 0.25rem 0 0;
        }
        .btn-sl-primary { background-color: var(--sl-primary); color: var(--sl-light); border: none; }
        .btn-sl-primary:hover { background-color: #5c0000; color: var(--sl-light); }
        .sending-message { display: none; }
        .sending-message.is-visible { display: block; }
    </style>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/modern-minimal.css">
</head>
<body>
<div class="card forgot-card w-100">
    <div class="forgot-header">
        <h1>Reset Password</h1>
        <p class="small">EVSU Smart Library student account</p>
    </div>
    <div class="card-body p-4">
        <p class="text-muted small">Enter your registered email. We will send you a link to create a new password.</p>
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $is_error ? 'danger' : 'success'; ?>">
                <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>
        <form method="post" id="forgotPasswordForm">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required
                       autocomplete="email"
                       value="<?php echo htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="alert alert-info py-2 sending-message" id="sendingResetMessage" role="status" aria-live="polite">
                Sending reset link...
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-sl-primary" id="sendResetButton">Send Reset Link</button>
                <a href="<?php echo BASE_URL; ?>/student/login.php" class="btn btn-outline-secondary">Back to Login</a>
            </div>
        </form>
    </div>
</div>
<script>
    (function () {
        const form = document.getElementById('forgotPasswordForm');
        const sendButton = document.getElementById('sendResetButton');
        const sendingMessage = document.getElementById('sendingResetMessage');

        if (!form || !sendButton || !sendingMessage) {
            return;
        }

        form.addEventListener('submit', function () {
            sendButton.disabled = true;
            sendingMessage.classList.add('is-visible');
        });
    })();
</script>
</body>
</html>
