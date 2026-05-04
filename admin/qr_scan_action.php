<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/admin_auth.php';

require_admin_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/qr_scan.php');
    exit;
}

$request_id = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
$action = $_POST['action'] ?? '';
$qr_token = trim((string)($_POST['qr'] ?? ''));

if ($request_id <= 0 || !in_array($action, ['claimed', 'returned'], true)) {
    header('Location: ' . BASE_URL . '/admin/qr_scan.php');
    exit;
}

$pdo = db_connect();

$stmt = $pdo->prepare("SELECT * FROM borrow_requests WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $request_id]);
$request = $stmt->fetch();

if (!$request) {
    header('Location: ' . BASE_URL . '/admin/qr_scan.php');
    exit;
}

$redirect_url = BASE_URL . '/admin/qr_scan.php';
if ($qr_token !== '') {
    $redirect_url .= '?qr=' . urlencode($qr_token) . '#review-actions';
}

$now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
$admin_id = (int)($_SESSION['admin_id'] ?? 0);

if ($action === 'claimed') {
    if ($request['status'] === 'claimed') {
        header('Location: ' . $redirect_url);
        exit;
    }
    if ($request['status'] !== 'approved') {
        header('Location: ' . $redirect_url);
        exit;
    }
    // Only update request status; inventory was already adjusted on approval.
    $update = $pdo->prepare(
        "UPDATE borrow_requests
         SET status = 'claimed', claimed_at = :now, admin_id_claimed = :admin_id
         WHERE id = :id"
    );
    $update->execute([
        ':now' => $now,
        ':admin_id' => $admin_id,
        ':id' => $request_id,
    ]);
}

if ($action === 'returned') {
    if ($request['status'] !== 'claimed') {
        header('Location: ' . $redirect_url);
        exit;
    }
    $pdo->beginTransaction();
    try {
        $update = $pdo->prepare(
            "UPDATE borrow_requests
             SET status = 'returned', returned_at = :now, admin_id_returned = :admin_id
             WHERE id = :id"
        );
        $update->execute([
            ':now' => $now,
            ':admin_id' => $admin_id,
            ':id' => $request_id,
        ]);
        // Always restore copies_available when marking as returned
        $items = $pdo->prepare("SELECT book_id, quantity FROM borrow_request_items WHERE borrow_request_id = :id");
        $items->execute([':id' => $request_id]);
        while ($row = $items->fetch(PDO::FETCH_ASSOC)) {
            $pdo->prepare("UPDATE books SET copies_available = LEAST(copies_total, copies_available + :qty) WHERE id = :id")
                ->execute([':qty' => (int)$row['quantity'], ':id' => (int)$row['book_id']]);
        }
        $archive = $pdo->prepare(
            "INSERT INTO borrow_returns_archive (borrow_request_id) VALUES (:id)"
        );
        $archive->execute([':id' => $request_id]);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log('Return processing failed: ' . $e->getMessage());
    }
}

header('Location: ' . $redirect_url);
exit;

