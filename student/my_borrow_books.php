<?php
// student/my_borrow_books.php
// Display the student's currently borrowed books (borrow requests not yet returned).

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/student_auth.php';
require_once __DIR__ . '/../includes/student_layout.php';

require_student_login();

$pdo = db_connect();
$student_id = (int)$_SESSION['student_id'];

// Count currently claimed copies.
try {
    $stmt = $pdo->prepare(
        "SELECT COALESCE(SUM(bri.quantity), 0) AS total_active_copies
         FROM borrow_requests br
         JOIN borrow_request_items bri ON bri.borrow_request_id = br.id
         WHERE br.student_id = :sid
           AND br.status = 'claimed'"
    );
    $stmt->execute([':sid' => $student_id]);
    $active_borrowed_count = (int)($stmt->fetchColumn() ?? 0);
} catch (Throwable $e) {
    error_log('My borrow books active count failed: ' . $e->getMessage());
    $active_borrowed_count = 0;
}

$max_books_at_a_time = 3;
$remaining_to_borrow = max(0, $max_books_at_a_time - $active_borrowed_count);

// Load currently claimed borrow requests with book titles.
$stmt = $pdo->prepare(
    "SELECT br.id,
            br.qr_token,
            br.status,
            br.requested_at,
            br.approved_at,
            br.claimed_at,
            (SELECT GROUP_CONCAT(b.title ORDER BY b.title SEPARATOR ', ')
             FROM borrow_request_items bri
             JOIN books b ON b.id = bri.book_id
             WHERE bri.borrow_request_id = br.id) AS book_titles,
            (SELECT COALESCE(SUM(bri.quantity), 0)
             FROM borrow_request_items bri
             WHERE bri.borrow_request_id = br.id) AS total_copies
     FROM borrow_requests br
     WHERE br.student_id = :sid
       AND br.status = 'claimed'
     ORDER BY br.requested_at DESC"
);
$stmt->execute([':sid' => $student_id]);
$active_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

/** @noinspection PhpUndefinedFunctionInspection */
student_render_header('My Borrow Books');
?>

<style>
    .sl-myborrow-hero {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, rgba(128, 0, 0, 0.98), rgba(74, 0, 0, 0.95));
        border-radius: 14px;
        color: #fff;
        padding: 1.2rem 1.25rem;
        box-shadow: 0 12px 30px rgba(74, 0, 0, 0.2);
    }
    .sl-myborrow-hero::after {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(255, 200, 92, 0.22), transparent 45%);
        pointer-events: none;
    }
    .sl-myborrow-hero h2,
    .sl-myborrow-hero p {
        position: relative;
        z-index: 1;
    }
    .sl-borrow-state-panel {
        border: 1px solid rgba(128, 0, 0, 0.12);
        background: linear-gradient(145deg, #ffffff, #fafafa);
        border-radius: 12px;
        padding: 0.9rem 1rem;
        box-shadow: 0 10px 24px rgba(128, 0, 0, 0.06);
    }
    .sl-borrow-state-panel strong {
        color: #212529;
    }
    .sl-borrow-state-panel .btn {
        border-radius: 999px;
        font-weight: 600;
    }
    .sl-myborrow-card {
        border: 1px solid rgba(128, 0, 0, 0.12);
        background: linear-gradient(145deg, #ffffff, #fafafa);
        box-shadow: 0 12px 28px rgba(128, 0, 0, 0.08);
    }
    .sl-myborrow-title {
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }
    .sl-myborrow-title .dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #ffc85c;
        box-shadow: 0 0 0 4px rgba(255, 200, 92, 0.22);
    }
    .sl-myborrow-table thead th {
        font-size: 0.79rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #6c757d;
        border-bottom-width: 1px;
    }
    .sl-myborrow-table tbody tr:hover {
        background: rgba(128, 0, 0, 0.03);
    }
    .sl-status-badge {
        font-weight: 600;
        letter-spacing: 0.01em;
        border-radius: 999px;
        padding: 0.35rem 0.55rem;
    }
    .sl-bottom-actions .btn {
        border-radius: 999px;
        font-weight: 600;
    }
</style>

<div class="row mb-4">
    <div class="col-12">
        <div class="sl-page-header sl-myborrow-hero">
            <h2 class="mb-1">My Borrow Books</h2>
            <p class="mb-0" style="color: rgba(255, 255, 255, 0.9);">
                Books you have currently claimed. Borrowing is limited to <strong><?php echo (int)$max_books_at_a_time; ?></strong> copies at the same time.
            </p>
        </div>
    </div>
</div>

<div class="sl-borrow-state-panel mb-3">
    <strong>Currently borrowed:</strong> <?php echo (int)$active_borrowed_count; ?> / <?php echo (int)$max_books_at_a_time; ?>
    <?php if ($remaining_to_borrow <= 0): ?>
        <span class="badge bg-danger ms-2">Limit reached</span>
    <?php endif; ?>
    <div class="mt-2">
        <?php if ($remaining_to_borrow > 0): ?>
            <a href="<?php echo BASE_URL; ?>/student/choose_books.php" class="btn btn-sm btn-sl-primary">
                Borrow More Books
            </a>
        <?php else: ?>
            <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                Borrow More Books
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="card sl-card sl-myborrow-card">
    <div class="card-body">
        <h5 class="card-title fw-semibold mb-3 sl-myborrow-title"><span class="dot"></span>Currently Borrowed Books</h5>
        <div class="table-responsive">
            <table class="table table-sm align-middle sl-myborrow-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Requested</th>
                        <th>Books</th>
                        <th class="text-center">Copies</th>
                        <th class="text-end">QR</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($active_requests): ?>
                        <?php foreach ($active_requests as $r): ?>
                            <tr>
                                <td>
                                    <span class="badge sl-status-badge bg-<?php
                                        echo $r['status'] === 'pending'
                                            ? 'warning'
                                            : ($r['status'] === 'approved' ? 'info' : 'success');
                                    ?>">
                                        <?php echo htmlspecialchars($r['status'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($r['requested_at'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-break"><?php echo htmlspecialchars($r['book_titles'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-center"><?php echo (int)($r['total_copies'] ?? 0); ?></td>
                                <td class="text-end">
                                    <?php if (!empty($r['qr_token'])): ?>
                                        <a href="<?php echo BASE_URL; ?>/student/qr_display.php?token=<?php echo urlencode($r['qr_token']); ?>" class="btn btn-sm btn-outline-primary">
                                            View QR
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">N/A</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                You have no currently borrowed books right now.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4 sl-bottom-actions">
    <a href="<?php echo BASE_URL; ?>/student/dashboard.php" class="btn btn-sl-primary">Back to Dashboard</a>
    <a href="<?php echo BASE_URL; ?>/student/returns.php" class="btn btn-outline-secondary ms-2">Return Books</a>
</div>

<?php
/** @noinspection PhpUndefinedFunctionInspection */
student_render_footer();
?>

