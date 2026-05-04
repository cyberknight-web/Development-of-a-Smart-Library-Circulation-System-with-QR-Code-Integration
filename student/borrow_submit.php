<?php
// student/borrow_submit.php
// Create borrow request and items from cart, then redirect to QR display.

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/student_auth.php';

require_student_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/student/shelves.php');
    exit;
}

$cart = student_get_cart();
if (empty($cart) || count($cart) > 3) {
    header('Location: ' . BASE_URL . '/student/shelves.php?error=cart');
    exit;
}

$pdo = db_connect();
$student_id = (int)$_SESSION['student_id'];

// Enforce active borrowing limit: count all books that are not yet returned (pending, approved, or claimed).
try {
    $stmt = $pdo->prepare(
        "SELECT COALESCE(SUM(bri.quantity), 0) AS total_active_copies
         FROM borrow_requests br
         JOIN borrow_request_items bri ON bri.borrow_request_id = br.id
         WHERE br.student_id = :sid
           AND br.status IN ('pending', 'approved', 'claimed')"
    );
    $stmt->execute([':sid' => $student_id]);
    $active_borrowed_count = (int)($stmt->fetchColumn() ?? 0);
} catch (Throwable $e) {
    error_log('Borrow limit check failed: ' . $e->getMessage());
    $active_borrowed_count = 3; // Fail safe: block new borrows.
}

$max_books_at_a_time = 3;
$remaining_to_borrow = max(0, $max_books_at_a_time - $active_borrowed_count);
if ($remaining_to_borrow <= 0 || count($cart) > $remaining_to_borrow) {
    header('Location: ' . BASE_URL . '/student/shelves.php?error=borrow_limit');
    exit;
}

// Verify all books still available and get their ids
$placeholders = implode(',', array_fill(0, count($cart), '?'));
$stmt = $pdo->prepare(
    "SELECT id FROM books WHERE id IN ($placeholders) AND status = 'available' AND copies_available > 0"
);
$stmt->execute($cart);
$valid_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
if (empty($valid_ids)) {
    student_clear_cart();
    header('Location: ' . BASE_URL . '/student/shelves.php?error=unavailable');
    exit;
}

$qr_token = bin2hex(random_bytes(32));
$notes = trim($_POST['notes'] ?? '');
if (strlen($notes) > 500) {
    $notes = substr($notes, 0, 500);
}
$pdo->beginTransaction();
try {
    $ins = $pdo->prepare(
        'INSERT INTO borrow_requests (student_id, qr_token, status, notes) VALUES (:sid, :token, :status, :notes)'
    );
    $ins->execute([
        ':sid' => $student_id,
        ':token' => $qr_token,
        ':status' => 'pending',
        ':notes' => $notes ?: null,
    ]);
    $request_id = (int)$pdo->lastInsertId();
    $item_ins = $pdo->prepare(
        'INSERT INTO borrow_request_items (borrow_request_id, book_id, quantity) VALUES (:req_id, :book_id, 1)'
    );
    foreach ($valid_ids as $book_id) {
        $item_ins->execute([':req_id' => $request_id, ':book_id' => (int)$book_id]);
    }
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    error_log('Borrow submit error: ' . $e->getMessage());
    header('Location: ' . BASE_URL . '/student/shelves.php?error=submit');
    exit;
}

student_clear_cart();
header('Location: ' . BASE_URL . '/student/qr_display.php?token=' . urlencode($qr_token));
exit;
