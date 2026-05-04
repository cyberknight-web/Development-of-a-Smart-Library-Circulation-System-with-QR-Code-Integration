<?php
// student/dashboard.php
// Student dashboard: welcome, quick link to borrow books, and current borrow status.

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/student_auth.php';
require_once __DIR__ . '/../includes/student_layout.php';

require_student_login();

$pdo = db_connect();
$student_id = (int)$_SESSION['student_id'];

// Pending/approved/claimed requests (not returned) for this student, with book titles per request
$stmt = $pdo->prepare(
    "SELECT br.id, br.qr_token, br.status, br.requested_at,
            (SELECT GROUP_CONCAT(b.title ORDER BY b.title SEPARATOR ', ')
             FROM borrow_request_items bri
             JOIN books b ON b.id = bri.book_id
             WHERE bri.borrow_request_id = br.id) AS book_titles
     FROM borrow_requests br
     WHERE br.student_id = :sid AND br.status IN ('pending', 'approved', 'claimed')
     ORDER BY br.requested_at DESC
     LIMIT 10"
);
$stmt->execute([':sid' => $student_id]);
$my_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

/** @noinspection PhpUndefinedFunctionInspection */
student_render_header('Dashboard');
?>

<style>
    .sl-student-hero {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, rgba(128, 0, 0, 0.98), rgba(74, 0, 0, 0.95));
        border-radius: 14px;
        color: #fff;
        padding: 1.2rem 1.25rem;
        box-shadow: 0 12px 30px rgba(74, 0, 0, 0.2);
    }
    .sl-student-hero::after {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(255, 200, 92, 0.22), transparent 45%);
        pointer-events: none;
    }
    .sl-student-hero h2,
    .sl-student-hero p {
        position: relative;
        z-index: 1;
    }
    .sl-action-card {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(128, 0, 0, 0.12);
        background: linear-gradient(145deg, #ffffff, #faf7f7);
        box-shadow: 0 12px 28px rgba(128, 0, 0, 0.08);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .sl-action-card::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(140deg, rgba(255, 200, 92, 0.12), transparent 42%);
        pointer-events: none;
    }
    .sl-action-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 16px 30px rgba(128, 0, 0, 0.12);
    }
    .sl-action-card .card-body {
        position: relative;
        z-index: 1;
    }
    .sl-action-title {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        font-weight: 700;
        color: #212529;
    }
    .sl-action-title .dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #ffc85c;
        box-shadow: 0 0 0 4px rgba(255, 200, 92, 0.22);
    }
    .sl-action-card .btn {
        border-radius: 999px;
        font-weight: 600;
        letter-spacing: 0.01em;
    }
    .sl-requests-card {
        border: 1px solid rgba(128, 0, 0, 0.12);
        background: linear-gradient(145deg, #ffffff, #fafafa);
        box-shadow: 0 12px 28px rgba(128, 0, 0, 0.08);
    }
    .sl-requests-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
    }
    .sl-requests-chip {
        font-size: 0.76rem;
        border: 1px solid rgba(255, 200, 92, 0.45);
        background: rgba(255, 200, 92, 0.18);
        color: #805200;
        border-radius: 999px;
        padding: 0.2rem 0.55rem;
    }
    .sl-requests-table thead th {
        font-size: 0.79rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #6c757d;
        border-bottom-width: 1px;
    }
    .sl-requests-table tbody tr:hover {
        background: rgba(128, 0, 0, 0.03);
    }
    .sl-status-badge {
        font-weight: 600;
        letter-spacing: 0.01em;
        border-radius: 999px;
        padding: 0.35rem 0.55rem;
    }
</style>

<div class="row mb-4">
    <div class="col-12">
        <div class="sl-page-header sl-student-hero">
            <h2 class="mb-1">Dashboard</h2>
            <p class="mb-0" style="color: rgba(255,255,255,0.9);">
                Welcome, <?php echo htmlspecialchars($_SESSION['student_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>.
                Borrow books, view your requests, and return within 3 days.
            </p>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6 col-lg-4">
        <div class="card sl-card sl-action-card h-100">
            <div class="card-body text-center">
                <h5 class="card-title sl-action-title"><span class="dot"></span>Borrow Books</h5>
                <p class="text-muted small">Choose up to 3 books and get a QR code to claim them at the library.</p>
                <a href="<?php echo BASE_URL; ?>/student/choose_books.php" class="btn btn-sl-primary">Borrow Books</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card sl-card sl-action-card h-100">
            <div class="card-body text-center">
                <h5 class="card-title sl-action-title"><span class="dot"></span>My Shelves</h5>
                <p class="text-muted small">Review selected books and fill the borrow form to generate your QR code.</p>
                <a href="<?php echo BASE_URL; ?>/student/shelves.php" class="btn btn-outline-secondary">View Shelves</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card sl-card sl-action-card h-100">
            <div class="card-body text-center">
                <h5 class="card-title sl-action-title"><span class="dot"></span>Return Books</h5>
                <p class="text-muted small">Return within 3 days. Present your QR code at the library for the admin to scan.</p>
                <a href="<?php echo BASE_URL; ?>/student/returns.php" class="btn btn-outline-secondary">Return Books</a>
            </div>
        </div>
    </div>
        <div class="col-md-6 col-lg-4">
            <div class="card sl-card sl-action-card h-100">
                <div class="card-body text-center">
                    <h5 class="card-title sl-action-title"><span class="dot"></span>My Borrow Books</h5>
                    <p class="text-muted small">See your active borrow requests (not yet returned).</p>
                    <a href="<?php echo BASE_URL; ?>/student/my_borrow_books.php" class="btn btn-outline-secondary">View My Borrow Books</a>
                </div>
            </div>
        </div>
</div>

<?php if (!empty($my_requests)): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card sl-card sl-requests-card">
            <div class="card-body">
                <div class="sl-requests-head mb-3">
                    <h5 class="card-title fw-semibold mb-0">Your Active Borrow Requests</h5>
                    <span class="sl-requests-chip"><?php echo count($my_requests); ?> Active</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle sl-requests-table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Requested</th>
                                <th>Books Borrowed</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($my_requests as $r): ?>
                                <tr>
                                    <td>
                                        <span class="badge sl-status-badge bg-<?php
                                            echo $r['status'] === 'pending' ? 'warning' : ($r['status'] === 'approved' ? 'info' : 'success');
                                        ?>"><?php echo htmlspecialchars($r['status'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($r['requested_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="text-break"><?php echo htmlspecialchars($r['book_titles'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="text-end">
                                        <a href="<?php echo BASE_URL; ?>/student/qr_display.php?token=<?php echo urlencode($r['qr_token']); ?>" class="btn btn-sm btn-outline-primary">View QR</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
/** @noinspection PhpUndefinedFunctionInspection */
student_render_footer();
?>
