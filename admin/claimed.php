<?php
// admin/claimed.php
// Admin page to see borrow requests that have been marked as claimed.

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
             WHERE bri.borrow_request_id = br.id) AS book_titles
     FROM borrow_requests br
     JOIN students s ON s.id = br.student_id
     WHERE br.status = 'claimed'
     ORDER BY br.claimed_at DESC"
);
$stmt->execute();
$claimed = $stmt->fetchAll();

function format_claimed_on(?string $claimed_at): string
{
    if (!$claimed_at) {
        return 'Claimed on: Not recorded';
    }

    try {
        $claimed_date = new DateTimeImmutable($claimed_at, new DateTimeZone(APP_TIMEZONE));
        return 'Claimed on: ' . $claimed_date->format('F j, Y g:i A');
    } catch (Throwable $e) {
        error_log('Claimed date formatting failed: ' . $e->getMessage());
        return 'Claimed on: ' . $claimed_at;
    }
}

admin_render_header('Claimed Borrowers');
?>

<style>
    .sl-claimed-hero {
        position: relative;
        overflow: hidden;
        background: linear-gradient(90deg, #FF6A00, #FF8C00, #FFA500);
        border-radius: 14px;
        color: #fff;
        padding: 1.2rem 1.25rem;
        box-shadow: 0 12px 30px rgba(255, 106, 0, 0.2);
    }
    .sl-claimed-hero::after {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(255, 165, 0, 0.2), transparent 45%);
        pointer-events: none;
    }
    .sl-claimed-hero h2,
    .sl-claimed-hero p {
        position: relative;
        z-index: 1;
    }
    .sl-claimed-card {
        border: 1px solid rgba(255, 106, 0, 0.12);
        background: linear-gradient(145deg, #ffffff, #fff8f3);
        box-shadow: 0 12px 28px rgba(255, 106, 0, 0.08);
    }
    .sl-claimed-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.85rem;
    }
    .sl-claimed-title {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        margin: 0;
    }
    .sl-claimed-title .dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #FFA500;
        box-shadow: 0 0 0 4px rgba(255, 165, 0, 0.22);
    }
    .sl-claimed-chip {
        font-size: 0.76rem;
        border: 1px solid rgba(255, 165, 0, 0.45);
        background: rgba(255, 165, 0, 0.18);
        color: #A05000;
        border-radius: 999px;
        padding: 0.2rem 0.55rem;
        display: inline-flex;
        align-items: center;
    }
    .sl-claimed-table thead th {
        font-size: 0.79rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #6c757d;
        border-bottom-width: 1px;
    }
    .sl-claimed-table tbody tr:hover {
        background: rgba(128, 0, 0, 0.03);
    }
    .sl-claimed-token {
        font-size: 0.75rem;
        overflow-wrap: anywhere;
    }
</style>

<div class="row mb-4">
    <div class="col-12">
        <div class="sl-page-header sl-claimed-hero">
            <h2 class="mb-1">Claimed Borrowers</h2>
            <p class="mb-0" style="color: rgba(255, 255, 255, 0.9);">
                Borrow requests that have been claimed by students and are awaiting return.
            </p>
        </div>
    </div>
</div>

<div class="card sl-card sl-claimed-card">
    <div class="card-body">
        <div class="sl-claimed-head">
            <h5 class="fw-semibold sl-claimed-title"><span class="dot"></span>Claimed Transactions</h5>
            <span class="sl-claimed-chip"><?php echo count($claimed); ?> Total</span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle sl-claimed-table">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Student ID</th>
                        <th>Course</th>
                        <th>Year / Section</th>
                        <th>Email</th>
                        <th>Claimed Books</th>
                        <th>QR Token</th>
                        <th>Claimed Date/Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($claimed): ?>
                        <?php foreach ($claimed as $r): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r['student_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($r['student_code'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($r['course'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($r['section'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-break"><?php echo htmlspecialchars($r['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-break"><?php echo htmlspecialchars($r['book_titles'] ?? 'No books listed', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-break">
                                    <code class="sl-claimed-token"><?php echo htmlspecialchars($r['qr_token'], ENT_QUOTES, 'UTF-8'); ?></code>
                                </td>
                                <td><?php echo htmlspecialchars(format_claimed_on($r['claimed_at'] ?? null), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><span class="badge bg-warning text-dark"><?php echo htmlspecialchars(ucfirst((string)$r['status']), ENT_QUOTES, 'UTF-8'); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No claimed borrow requests yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
admin_render_footer();
?>
