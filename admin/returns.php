<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/admin_layout.php';

require_admin_login();

$pdo = db_connect();

// All returned borrow requests with student info and book titles (comma-separated per request).
$stmt = $pdo->query(
    "SELECT br.*, s.name AS student_name, s.student_id AS student_code, s.course, s.section, s.email,
            (SELECT GROUP_CONCAT(b.title ORDER BY b.title SEPARATOR ', ')
             FROM borrow_request_items bri
             JOIN books b ON b.id = bri.book_id
             WHERE bri.borrow_request_id = br.id) AS book_titles,
            (SELECT COALESCE(SUM(bri.quantity), 0)
             FROM borrow_request_items bri
             WHERE bri.borrow_request_id = br.id) AS total_copies
     FROM borrow_requests br
     JOIN students s ON s.id = br.student_id
     WHERE br.status = 'returned'
     ORDER BY br.returned_at DESC"
);
$returns = $stmt->fetchAll();

function format_returned_on(?string $returned_at): string
{
    if (!$returned_at) {
        return 'Returned on: Not recorded';
    }

    try {
        $returned_date = new DateTimeImmutable($returned_at, new DateTimeZone(APP_TIMEZONE));
        return 'Returned on: ' . $returned_date->format('F j, Y g:i A');
    } catch (Throwable $e) {
        error_log('Returned date formatting failed: ' . $e->getMessage());
        return 'Returned on: ' . $returned_at;
    }
}

admin_render_header('Returned Books');
?>

<style>
    .sl-returns-hero {
        position: relative;
        overflow: hidden;
        background: linear-gradient(90deg, #FF6A00, #FF8C00, #FFA500);
        border-radius: 14px;
        color: #fff;
        padding: 1.2rem 1.25rem;
        box-shadow: 0 12px 30px rgba(255, 106, 0, 0.2);
    }
    .sl-returns-hero::after {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(255, 165, 0, 0.2), transparent 45%);
        pointer-events: none;
    }
    .sl-returns-hero h2,
    .sl-returns-hero p {
        position: relative;
        z-index: 1;
    }
    .sl-returns-card {
        border: 1px solid rgba(255, 106, 0, 0.12);
        background: linear-gradient(145deg, #ffffff, #fff8f3);
        box-shadow: 0 12px 28px rgba(255, 106, 0, 0.08);
    }
    .sl-returns-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.85rem;
    }
    .sl-returns-title {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        margin: 0;
    }
    .sl-returns-title .dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #FFA500;
        box-shadow: 0 0 0 4px rgba(255, 165, 0, 0.22);
    }
    .sl-returns-chip {
        font-size: 0.76rem;
        border: 1px solid rgba(255, 165, 0, 0.45);
        background: rgba(255, 165, 0, 0.18);
        color: #A05000;
        border-radius: 999px;
        padding: 0.2rem 0.55rem;
        display: inline-flex;
        align-items: center;
    }
    .sl-returns-table thead th {
        font-size: 0.79rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #6c757d;
        border-bottom-width: 1px;
    }
    .sl-returns-table tbody tr:hover {
        background: rgba(128, 0, 0, 0.03);
    }
    .sl-returns-note {
        border-radius: 999px;
        font-weight: 600;
    }
</style>

<div class="row mb-4">
    <div class="col-12">
        <div class="sl-page-header sl-returns-hero">
            <h2 class="mb-1">Returned Books</h2>
            <p class="mb-0" style="color: rgba(255, 255, 255, 0.9);">
                History of all borrow transactions that have been returned to the library.
            </p>
        </div>
    </div>
</div>

<div class="card sl-card sl-returns-card">
    <div class="card-body">
        <div class="sl-returns-head">
            <h5 class="fw-semibold sl-returns-title"><span class="dot"></span>Returned Transactions</h5>
            <span class="sl-returns-chip"><?php echo count($returns); ?> Total</span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle sl-returns-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Student ID</th>
                        <th>Course / Section</th>
                        <th>Books Returned</th>
                        <th>Copies</th>
                        <th>Notes</th>
                        <th>Returned At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($returns): ?>
                        <?php foreach ($returns as $r): ?>
                            <tr>
                                <td>
                                    <span class="fw-semibold"><?php echo htmlspecialchars($r['student_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php if (!empty($r['email'])): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($r['email'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($r['student_code'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($r['course'], ENT_QUOTES, 'UTF-8'); ?>
                                    / <?php echo htmlspecialchars($r['section'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td class="text-break"><?php echo htmlspecialchars($r['book_titles'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-center"><?php echo (int) ($r['total_copies'] ?? 0); ?></td>
                                <td>
                                    <?php if ($r['notes']): ?>
                                        <span class="badge bg-info small sl-returns-note" title="<?php echo htmlspecialchars($r['notes'], ENT_QUOTES, 'UTF-8'); ?>">Has note</span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars(format_returned_on($r['returned_at'] ?? null), ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No returned borrows yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
admin_render_footer();
