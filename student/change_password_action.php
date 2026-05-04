<?php
// student/change_password_action.php
// Backend: verify current password and update student password.

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/student_auth.php';

require_student_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/student/change_password.php');
    exit;
}

$current = $_POST['current_password'] ?? '';
$new = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if (strlen($new) < 8) {
    header('Location: ' . BASE_URL . '/student/change_password.php?status=short');
    exit;
}
if ($new !== $confirm) {
    header('Location: ' . BASE_URL . '/student/change_password.php?status=mismatch');
    exit;
}

$student_id = (int) ($_SESSION['student_id'] ?? 0);
if ($student_id <= 0) {
    header('Location: ' . BASE_URL . '/student/change_password.php?status=error');
    exit;
}

$pdo = db_connect();
$stmt = $pdo->prepare('SELECT password_hash FROM students WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $student_id]);
$row = $stmt->fetch();

if (!$row || !password_verify($current, $row['password_hash'])) {
    header('Location: ' . BASE_URL . '/student/change_password.php?status=wrong_current');
    exit;
}

$hash = password_hash($new, PASSWORD_DEFAULT);
$upd = $pdo->prepare('UPDATE students SET password_hash = :hash WHERE id = :id');
$upd->execute([':hash' => $hash, ':id' => $student_id]);

header('Location: ' . BASE_URL . '/student/change_password.php?status=updated');
exit;
