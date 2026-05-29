<?php
// student/qr_display.php
// Show QR code (token), student info, and borrowed books. Student can download/screenshot.

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/student_auth.php';
require_once __DIR__ . '/../includes/student_layout.php';

require_student_login();

$token = trim($_GET['token'] ?? '');
if ($token === '') {
    header('Location: ' . BASE_URL . '/student/dashboard.php');
    exit;
}

$pdo = db_connect();
$student_id = (int)$_SESSION['student_id'];

$stmt = $pdo->prepare(
    "SELECT br.id, br.qr_token, br.status, br.notes, br.requested_at,
            s.name, s.student_id AS student_code, s.course, s.section, s.email
     FROM borrow_requests br
     JOIN students s ON s.id = br.student_id
     WHERE br.qr_token = :token AND br.student_id = :sid
     LIMIT 1"
);
$stmt->execute([
    ':token' => $token,
    ':sid' => $student_id
]);

$record = $stmt->fetch();

if (!$record) {
    header('Location: ' . BASE_URL . '/student/dashboard.php');
    exit;
}

// Ensure course and section are present for this borrow record. If missing,
// redirect the student to their profile to complete the details.
if (trim((string)$record['course']) === '' || trim((string)$record['section']) === '') {
    header('Location: ' . BASE_URL . '/student/profile.php?status=missing_profile');
    exit;
}

$stmt_items = $pdo->prepare(
    "SELECT bri.*, b.title
     FROM borrow_request_items bri
     JOIN books b ON b.id = bri.book_id
     WHERE bri.borrow_request_id = :id"
);
$stmt_items->execute([
    ':id' => $record['id']
]);

$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

$qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($token);
$qr_download_url = BASE_URL . '/student/qr_download.php?token=' . urlencode($token);
$qr_claim_url = BASE_URL . '/admin/qr_scan.php?qr=' . urlencode($token);

$generated_at_display = '';
$return_due_display = '';

if (!empty($record['requested_at'])) {
    try {
        $generated_at = new DateTimeImmutable((string)$record['requested_at'], new DateTimeZone(APP_TIMEZONE));
        $generated_at_display = $generated_at->format('F j, Y g:i A');

        // The return due date is exactly 3 days after the QR code was generated.
        $return_due = $generated_at->modify('+3 days');
        $return_due_display = $return_due->format('F j, Y g:i A');
    } catch (Throwable $e) {
        error_log('QR generated date formatting failed: ' . $e->getMessage());
    }
}

student_render_header('Your QR Code');
?>

<style>
    .sl-qr-date {
        color: #343a40;
        font-size: 1.08rem;
        font-weight: 700;
        line-height: 1.35;
    }
</style>

<div class="row mb-4">
    <div class="col-12">
        <div class="sl-page-header">
            <h2 class="mb-1">Your Borrow QR Code</h2>
            <p class="text-muted mb-0">Show this QR code at the library to claim your books. Return within 3 days.</p>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card sl-card">
            <div class="card-body text-center">
                <h5 class="card-title mb-3">QR Code</h5>

                <img
                    src="<?php echo htmlspecialchars($qr_url, ENT_QUOTES, 'UTF-8'); ?>"
                    alt="QR Code"
                    class="img-fluid"
                    id="qrImage"
                    style="max-width: 220px;"
                >

                <?php if ($generated_at_display !== ''): ?>
                    <p class="sl-qr-date mt-3 mb-1">
                        Generated on: <?php echo htmlspecialchars($generated_at_display, ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                <?php endif; ?>

                <?php if ($return_due_display !== ''): ?>
                    <p class="sl-qr-date mb-2">
                        Return due: <?php echo htmlspecialchars($return_due_display, ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                <?php endif; ?>

                <p class="small text-muted mt-2 mb-2">
                    Token:
                    <code id="qrToken">
                        <?php echo htmlspecialchars($record['qr_token'], ENT_QUOTES, 'UTF-8'); ?>
                    </code>
                </p>

                <p class="small mb-2">Download or screenshot this page to present at the library.</p>

                <a
                    href="<?php echo htmlspecialchars($qr_download_url, ENT_QUOTES, 'UTF-8'); ?>"
                    class="btn btn-sl-primary btn-sm me-2"
                    id="qrDownloadLink"
                >
                    Download QR Image (PNG)
                </a>

                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print();">
                    Print / Save as PDF
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card sl-card">
            <div class="card-body">
                <h5 class="card-title fw-semibold mb-3">Borrow Details</h5>

                <dl class="row small mb-0">
                    <dt class="col-sm-4">Name</dt>
                    <dd class="col-sm-8">
                        <?php echo htmlspecialchars($record['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </dd>

                    <dt class="col-sm-4">Student ID</dt>
                    <dd class="col-sm-8">
                        <?php echo htmlspecialchars($record['student_code'], ENT_QUOTES, 'UTF-8'); ?>
                    </dd>

                    <dt class="col-sm-4">Course</dt>
                    <dd class="col-sm-8">
                        <?php echo htmlspecialchars($record['course'], ENT_QUOTES, 'UTF-8'); ?>
                    </dd>

                    <dt class="col-sm-4">Year / Section</dt>
                    <dd class="col-sm-8">
                        <?php echo htmlspecialchars($record['section'], ENT_QUOTES, 'UTF-8'); ?>
                    </dd>

                    <dt class="col-sm-4">Email</dt>
                    <dd class="col-sm-8">
                        <?php echo htmlspecialchars($record['email'], ENT_QUOTES, 'UTF-8'); ?>
                    </dd>

                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">
                        <span class="badge bg-warning">
                            <?php echo htmlspecialchars($record['status'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </dd>

                    <?php if ($record['notes']): ?>
                        <dt class="col-sm-4">Notes</dt>
                        <dd class="col-sm-8">
                            <span class="text-break">
                                <?php echo htmlspecialchars($record['notes'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </dd>
                    <?php endif; ?>
                </dl>

                <h6 class="mt-3">Books</h6>

                <ul class="list-group list-group-flush">
                    <?php foreach ($items as $item): ?>
                        <li class="list-group-item px-0">
                            <?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <p class="text-muted small mt-3 mb-0">
                    Return within 3 days. Present this QR at the library for the admin to scan when returning.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="mt-4 sl-bottom-actions">
    <a href="<?php echo BASE_URL; ?>/student/dashboard.php" class="btn btn-sl-primary">
        Back to Dashboard
    </a>

    <a href="<?php echo BASE_URL; ?>/student/choose_books.php" class="btn btn-outline-secondary ms-2">
        Borrow More Books
    </a>
</div>

<script>
(function () {
    // Auto-download QR as PNG when page loads once per session for this page.
    var key = 'qr_auto_download_' + <?php echo json_encode($token); ?>;

    if (!sessionStorage.getItem(key)) {
        sessionStorage.setItem(key, '1');

        var iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.src = <?php echo json_encode($qr_download_url); ?>;

        document.body.appendChild(iframe);
    }
})();
</script>

<?php student_render_footer(); ?>
