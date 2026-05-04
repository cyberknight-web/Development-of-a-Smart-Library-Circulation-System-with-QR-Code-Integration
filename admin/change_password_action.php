<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/admin_auth.php';

require_admin_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/change_password.php');
    exit;
}

$current = $_POST['current_password'] ?? '';
$new = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if (strlen($new) < 8) {
    header('Location: ' . BASE_URL . '/admin/change_password.php?status=short');
    exit;
}
if ($new !== $confirm) {
    header('Location: ' . BASE_URL . '/admin/change_password.php?status=mismatch');
    exit;
}

$admin_id = (int) ($_SESSION['admin_id'] ?? 0);
if ($admin_id <= 0) {
    header('Location: ' . BASE_URL . '/admin/change_password.php?status=error');
    exit;
}

$pdo = db_connect();
$stmt = $pdo->prepare('SELECT password_hash FROM admins WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $admin_id]);
$row = $stmt->fetch();

if (!$row || !password_verify($current, $row['password_hash'])) {
    header('Location: ' . BASE_URL . '/admin/change_password.php?status=wrong_current');
    exit;
}

$hash = password_hash($new, PASSWORD_DEFAULT);
$upd = $pdo->prepare('UPDATE admins SET password_hash = :hash WHERE id = :id');
$upd->execute([':hash' => $hash, ':id' => $admin_id]);

header('Location: ' . BASE_URL . '/admin/change_password.php?status=updated');
exit;
