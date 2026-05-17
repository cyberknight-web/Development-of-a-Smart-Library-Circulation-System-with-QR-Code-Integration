<?php
// student/returns.php
// Student page to view borrowed/returned books with dates and access QR codes.

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/student_auth.php';
require_once __DIR__ . '/../includes/student_layout.php';

require_student_login();

$pdo = db_connect();
$student_id = (int)$_SESSION['student_id'];

// Load this student's non-pending borrow requests (approved, claimed, returned)
$stmt = $pdo->prepare(
    "SELECT id, qr_token, status, requested_at, approved_at, claimed_at, returned_at
     FROM borrow_requests
     WHERE student_id = :sid AND status IN ('approved', 'claimed', 'returned')
     ORDER BY requested_at DESC"
);
$stmt->execute([':sid' => $student_id]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

function format_datetime(?string $value): string {
    if (!$value) {
        return '-';
    }
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function calculate_due_date(array $row): string {
    // Use claimed_at if available, otherwise approved_at, otherwise requested_at as the borrow baseline.
    $base = $row['claimed_at'] ?? $row['approved_at'] ?? $row['requested_at'] ?? null;
    if (!$base) {
        return '-';
    }
    try {
        $dt = new DateTimeImmutable($base);
        $due = $dt->modify('+3 days');
        return htmlspecialchars($due->format('Y-m-d H:i:s'), ENT_QUOTES, 'UTF-8');
    } catch (Throwable $e) {
        return '-';
    }
}

student_render_header('Return Books');
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="sl-page-header">
            <h2 class="mb-1">Return Books</h2>
            <p class="text-muted mb-0">
                View your borrowed books, see return deadlines, and open your QR code for returning.
            </p>
        </div>
    </div>
</div>

<div class="card sl-card">
    <div class="card-body">
        <h5 class="card-title fw-semibold mb-3">My Borrow History</h5>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Borrowed At</th>
                        <th>Return Deadline</th>
                        <th>Returned At</th>
                        <th class="text-center">QR</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($requests): ?>
                    <?php foreach ($requests as $r): ?>
                        <?php
                        $borrowed_at = $r['claimed_at'] ?? $r['approved_at'] ?? $r['requested_at'];
                        ?>
                        <tr>
                            <td>
                                <span class="badge bg-<?php
                                    echo $r['status'] === 'approved'
                                        ? 'info'
                                        : ($r['status'] === 'claimed' ? 'success' : 'secondary');
                                ?>">
                                    <?php echo htmlspecialchars($r['status'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td><?php echo format_datetime($borrowed_at); ?></td>
                            <td><?php echo calculate_due_date($r); ?></td>
                            <td><?php echo format_datetime($r['returned_at'] ?? null); ?></td>
                            <td class="text-center">
                                <?php if (!empty($r['qr_token'])): ?>
                                    <a
                                        href="<?php echo BASE_URL; ?>/student/qr_display.php?token=<?php echo urlencode($r['qr_token']); ?>"
                                        class="btn btn-outline-secondary btn-sm"
                                    >
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
                        <td colspan="5" class="text-center text-muted">
                            You have no approved, claimed, or returned borrow requests yet.
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
    <a href="<?php echo BASE_URL; ?>/student/choose_books.php" class="btn btn-outline-secondary ms-2">Borrow More Books</a>
</div>

<?php student_render_footer(); ?>

