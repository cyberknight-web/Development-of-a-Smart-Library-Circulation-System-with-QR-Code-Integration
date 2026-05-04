<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/admin_auth.php';

require_admin_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/borrow_requests.php');
    exit;
}

$request_id = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
$action = $_POST['action'] ?? '';

if ($request_id <= 0 || !in_array($action, ['approve', 'reject'], true)) {
    header('Location: ' . BASE_URL . '/admin/borrow_requests.php');
    exit;
}

$pdo = db_connect();

$stmt = $pdo->prepare(
    "SELECT br.*, s.email, s.name AS student_name
     FROM borrow_requests br
     JOIN students s ON s.id = br.student_id
     WHERE br.id = :id LIMIT 1"
);
$stmt->execute([':id' => $request_id]);
$request = $stmt->fetch();

if (!$request || $request['status'] !== 'pending') {
    header('Location: ' . BASE_URL . '/admin/borrow_requests.php');
    exit;
}

$now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
$admin_id = (int)($_SESSION['admin_id'] ?? 0);

if ($action === 'approve') {
    // Enforce 3-book maximum borrowing limit
    $student_id = (int)$request['student_id'];
    
    // Count all non-returned books EXCEPT the current request being approved
    $active_check = $pdo->prepare(
        "SELECT COALESCE(SUM(bri.quantity), 0) AS total_active_copies
         FROM borrow_requests br
         JOIN borrow_request_items bri ON bri.borrow_request_id = br.id
         WHERE br.student_id = :sid
           AND br.status IN ('pending', 'approved', 'claimed')
           AND br.id != :req_id"
    );
    $active_check->execute([':sid' => $student_id, ':req_id' => $request_id]);
    $active_count = (int)($active_check->fetchColumn() ?? 0);
    
    // Count books in this request
    $request_check = $pdo->prepare(
        "SELECT COALESCE(SUM(quantity), 0) AS total_quantity
         FROM borrow_request_items
         WHERE borrow_request_id = :req_id"
    );
    $request_check->execute([':req_id' => $request_id]);
    $request_quantity = (int)($request_check->fetchColumn() ?? 0);
    
    // Enforce maximum of 3 books at a time
    if ($active_count + $request_quantity > 3) {
        header('Location: ' . BASE_URL . '/admin/borrow_requests.php?error=borrow_limit&request_id=' . $request_id);
        exit;
    }
    
    $pdo->beginTransaction();
    try {
        
        $update = $pdo->prepare(
            "UPDATE borrow_requests
             SET status = 'approved', approved_at = :now, admin_id_approved = :admin_id
             WHERE id = :id"
        );
        $update->execute([
            ':now' => $now,
            ':admin_id' => $admin_id,
            ':id' => $request_id,
        ]);

        $items = $pdo->prepare("SELECT book_id, quantity FROM borrow_request_items WHERE borrow_request_id = :id");
        $items->execute([':id' => $request_id]);
        while ($row = $items->fetch(PDO::FETCH_ASSOC)) {
            $pdo->prepare(
                "UPDATE books
                 SET copies_available = GREATEST(0, copies_available - :qty)
                 WHERE id = :id"
            )->execute([
                ':qty' => (int)$row['quantity'],
                ':id' => (int)$row['book_id'],
            ]);
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log('Borrow approval failed: ' . $e->getMessage());
    }


    header('Location: ' . BASE_URL . '/admin/borrow_requests.php?status=approved');
    exit;
}

if ($action === 'reject') {
    $update = $pdo->prepare(
        "UPDATE borrow_requests
         SET status = 'rejected', rejected_at = :now, admin_id_rejected = :admin_id
         WHERE id = :id"
    );
    $update->execute([
        ':now' => $now,
        ':admin_id' => $admin_id,
        ':id' => $request_id,
    ]);

    header('Location: ' . BASE_URL . '/admin/borrow_requests.php?status=rejected');
    exit;
}

