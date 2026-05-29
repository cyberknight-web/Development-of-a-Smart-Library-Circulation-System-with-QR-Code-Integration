<?php
// admin/approved.php
// Admin page to see list of approved borrow requests.

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/admin_layout.php';

require_admin_login();

$pdo = db_connect();

$stmt = $pdo->prepare(
    "SELECT br.*, s.name AS student_name, s.student_id AS student_code, s.course, s.section, s.email,
            (SELECT GROUP_CONCAT(b.title ORDER BY b.title SEPARATOR ', ')
             FROM borrow_request_items bri
             JOIN books b ON b.id = bri.book_id
             WHERE bri.borrow_request_id = br.id) AS book_titles,
            (SELECT GROUP_CONCAT(
                        DISTINCT COALESCE(NULLIF(TRIM(b.category), ''), 'Uncategorized')
                        ORDER BY COALESCE(NULLIF(TRIM(b.category), ''), 'Uncategorized') SEPARATOR ', '
                    )
             FROM borrow_request_items bri
             JOIN books b ON b.id = bri.book_id
             WHERE bri.borrow_request_id = br.id) AS book_categories,
            (SELECT COALESCE(SUM(bri.quantity), 0)
             FROM borrow_request_items bri
             WHERE bri.borrow_request_id = br.id) AS total_copies
     FROM borrow_requests br
     JOIN students s ON s.id = br.student_id
     WHERE br.status = 'approved'
     ORDER BY br.approved_at DESC"
);
$stmt->execute();
$approved = $stmt->fetchAll();

function format_approved_on(?string $approved_at): string
{
    if (!$approved_at) {
        return 'Approved on: Not recorded';
    }

    try {
        $approved_date = new DateTimeImmutable($approved_at, new DateTimeZone(APP_TIMEZONE));
        return 'Approved on: ' . $approved_date->format('F j, Y g:i A');
    } catch (Throwable $e) {
        error_log('Approved date formatting failed: ' . $e->getMessage());
        return 'Approved on: ' . $approved_at;
    }
}

admin_render_header('Approved Borrowers');
?>

<style>
    .sl-approved-hero {
        position: relative;
        overflow: hidden;
        background: linear-gradient(90deg, #FF6A00, #FF8C00, #FFA500);
        border-radius: 14px;
        color: #fff;
        padding: 1.2rem 1.25rem;
        box-shadow: 0 12px 30px rgba(255, 106, 0, 0.2);
    }
    .sl-approved-hero::after {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(255, 165, 0, 0.2), transparent 45%);
        pointer-events: none;
    }
    .sl-approved-hero h2,
    .sl-approved-hero p {
        position: relative;
        z-index: 1;
    }
    .sl-approved-card {
        border: 1px solid rgba(255, 106, 0, 0.12);
        background: linear-gradient(145deg, #ffffff, #fff8f3);
        box-shadow: 0 12px 28px rgba(255, 106, 0, 0.08);
    }
    .sl-approved-card .card-header {
        background: linear-gradient(160deg, #ffffff, #fff3ed);
        border-bottom: 1px solid rgba(255, 106, 0, 0.1) !important;
    }
    .sl-approved-count {
        font-size: 0.76rem;
        border: 1px solid rgba(255, 165, 0, 0.45);
        background: rgba(255, 165, 0, 0.18);
        color: #A05000;
        border-radius: 999px;
        padding: 0.25rem 0.55rem;
        display: inline-flex;
        align-items: center;
    }
    .sl-approved-table thead th {
        font-size: 0.79rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #6c757d;
        border-bottom-width: 1px;
    }
    .sl-approved-table tbody tr:hover {
        background: rgba(128, 0, 0, 0.03);
    }
    .sl-approved-token {
        border: 1px solid rgba(255, 106, 0, 0.12);
        background: #fff5eb;
        color: #FF8C00;
    }
    .sl-approved-tag {
        border-radius: 999px;
        font-weight: 600;
    }
</style>

<div class="row mb-4">
    <div class="col-12">
        <div class="sl-page-header sl-approved-hero">
            <h2 class="mb-1">
                <i class="bi bi-check-circle me-2"></i>Approved Borrowers
            </h2>
            <p class="mb-0" style="color: rgba(255, 255, 255, 0.9);">
                Students whose borrow requests have been approved and are ready to claim their books via QR code.
            </p>
        </div>
    </div>
</div>

<div class="card sl-card sl-approved-card shadow-sm">
    <div class="card-header border-bottom-0 pt-4 pb-3">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="card-title fw-semibold mb-0">
                    <i class="bi bi-list-check text-success me-2"></i>Approved Requests
                </h5>
            </div>
            <div class="col-auto">
                <span class="sl-approved-count">
                    <i class="bi bi-people me-1"></i><?php echo count($approved); ?> Total
                </span>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0 sl-approved-table">
                <thead>
                <tr>
                    <th class="fw-semibold"><i class="bi bi-person-badge text-primary me-2"></i>Student</th>
                    <th class="fw-semibold"><i class="bi bi-hash text-primary me-2"></i>ID</th>
                    <th class="fw-semibold"><i class="bi bi-book-half text-primary me-2"></i>Course</th>
                    <th class="fw-semibold"><i class="bi bi-diagram-3 text-primary me-2"></i>Year / Section</th>
                    <th class="fw-semibold"><i class="bi bi-envelope text-primary me-2"></i>Email</th>
                    <th class="fw-semibold"><i class="bi bi-book text-primary me-2"></i>Books</th>
                    <th class="fw-semibold"><i class="bi bi-tag text-primary me-2"></i>Category</th>
                    <th class="text-center fw-semibold"><i class="bi bi-stack text-primary me-2"></i>Qty</th>
                    <th class="fw-semibold"><i class="bi bi-chat-left-text text-primary me-2"></i>Notes</th>
                    <th class="fw-semibold"><i class="bi bi-qr-code text-primary me-2"></i>QR Token</th>
                    <th class="fw-semibold"><i class="bi bi-calendar-check text-primary me-2"></i>Approved</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($approved): ?>
                    <?php foreach ($approved as $r): ?>
                        <tr>
                            <td>
                                <i class="bi bi-person-circle text-primary me-2" style="opacity: 0.6;"></i>
                                <strong><?php echo htmlspecialchars($r['student_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            </td>
                            <td><code class="text-primary"><?php echo htmlspecialchars($r['student_code'], ENT_QUOTES, 'UTF-8'); ?></code></td>
                            <td>
                                <span class="badge bg-light text-dark sl-approved-tag">
                                    <?php echo htmlspecialchars($r['course'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark sl-approved-tag">
                                    <?php echo htmlspecialchars($r['section'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td>
                                <i class="bi bi-envelope me-1 text-muted" style="opacity: 0.5;"></i>
                                <span class="text-break"><?php echo htmlspecialchars($r['email'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </td>
                            <td class="text-break">
                                <small><?php echo htmlspecialchars($r['book_titles'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></small>
                            </td>
                            <td class="text-break">
                                <?php if ($r['book_categories']): ?>
                                    <small><span class="badge bg-warning text-dark sl-approved-tag"><?php echo htmlspecialchars($r['book_categories'], ENT_QUOTES, 'UTF-8'); ?></span></small>
                                <?php else: ?>
                                    <small class="text-muted">Uncategorized</small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info text-white sl-approved-tag"><?php echo (int)($r['total_copies'] ?? 0); ?></span>
                            </td>
                            <td>
                                <?php if ($r['notes']): ?>
                                    <div class="d-inline-flex align-items-center gap-2">
                                        <span class="badge bg-info sl-approved-tag" title="<?php echo htmlspecialchars($r['notes'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="bi bi-chat-left-text-fill me-1"></i>Has Note
                                        </span>
                                    </div>
                                    <div class="small text-muted mt-2" style="max-width: 180px; word-wrap: break-word;">
                                        <em><?php echo htmlspecialchars(substr($r['notes'], 0, 80), ENT_QUOTES, 'UTF-8'); ?><?php if (strlen($r['notes']) > 80) echo '...'; ?></em>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <code class="p-2 rounded d-inline-block sl-approved-token" style="font-size: 0.75rem;">
                                    <?php echo htmlspecialchars(substr($r['qr_token'], 0, 8), ENT_QUOTES, 'UTF-8'); ?>...
                                </code>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i>
                                    <?php echo htmlspecialchars(format_approved_on($r['approved_at'] ?? null), ENT_QUOTES, 'UTF-8'); ?>
                                </small>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.2;"></i>
                            <div class="mt-3 text-muted">
                                <p class="mb-0"><strong>No approved borrowers yet.</strong></p>
                                <small>Approved requests will appear here and be ready for claiming.</small>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
admin_render_footer();

