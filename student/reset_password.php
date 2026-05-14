<?php
// student/reset_password.php
// Set new password from reset link (token in email).

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/student_auth.php';
require_once __DIR__ . '/../includes/password_reset_tokens.php';

if (student_is_logged_in()) {
    header('Location: ' . BASE_URL . '/student/dashboard.php');
    exit;
}

$token = trim($_GET['token'] ?? '');
$errors = [];
$success = false;

if ($token === '') {
    $errors[] = 'Invalid or missing reset link.';
} else {
    try {
        $pdo = db_connect();
        ensure_password_reset_tokens_table($pdo);

        $stmt = $pdo->prepare(
            'SELECT prt.student_id, s.email, s.name FROM password_reset_tokens prt
             JOIN students s ON s.id = prt.student_id
             WHERE prt.token = :token AND prt.expires_at > NOW() LIMIT 1'
        );
        $stmt->execute([':token' => $token]);
        $row = $stmt->fetch();
        if (!$row) {
            $errors[] = 'This reset link is invalid or has expired. Request a new one from the login page.';
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirm  = $_POST['password_confirm'] ?? '';
            if (strlen($password) < 6) {
                $errors[] = 'Password must be at least 6 characters.';
            } elseif ($password !== $confirm) {
                $errors[] = 'Passwords do not match.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $upd = $pdo->prepare('UPDATE students SET password_hash = :hash WHERE id = :id');
                $upd->execute([':hash' => $hash, ':id' => $row['student_id']]);

                $del = $pdo->prepare('DELETE FROM password_reset_tokens WHERE student_id = :id');
                $del->execute([':id' => $row['student_id']]);

                $success = true;
            }
        }
    } catch (PDOException $e) {
        error_log('Reset password DB error: ' . $e->getMessage());
        $errors[] = 'We could not validate your reset link right now. Please try again later.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password | Smart Library</title>
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/images/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        :root { --sl-primary: <?php echo COLOR_PRIMARY; ?>; --sl-light: <?php echo COLOR_LIGHT; ?>; }
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f5f5f5; }
        .card { max-width: 420px; border-radius: 12px; border: none; box-shadow: 0 8px 24px rgba(0,0,0,0.1); }
        .btn-sl-primary { background-color: var(--sl-primary); color: var(--sl-light); border: none; }
        .btn-sl-primary:hover { background-color: #5c0000; color: var(--sl-light); }
    </style>
</head>
<body>
<div class="card w-100">
    <div class="card-body p-4">
        <?php if ($success): ?>
            <div class="alert alert-success">Your password has been updated. You can now <a href="<?php echo BASE_URL; ?>/student/login.php">log in</a>.</div>
        <?php elseif (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e) echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8') . '<br>'; ?>
            </div>
            <a href="<?php echo BASE_URL; ?>/student/forgot_password.php" class="btn btn-outline-secondary">Request new link</a>
        <?php elseif ($row ?? null): ?>
            <h5 class="card-title mb-3">Create New Password</h5>
            <form method="post">
                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="password" name="password" required minlength="6">
                </div>
                <div class="mb-3">
                    <label for="password_confirm" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" required minlength="6">
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-sl-primary">Update Password</button>
                    <a href="<?php echo BASE_URL; ?>/student/login.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
