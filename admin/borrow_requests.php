<?php

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
             WHERE bri.borrow_request_id = br.id) AS total_copies,
            (SELECT COALESCE(SUM(bri.quantity), 0)
             FROM borrow_requests br2
             JOIN borrow_request_items bri ON bri.borrow_request_id = br2.id
             WHERE br2.student_id = s.id AND br2.status IN ('pending', 'approved', 'claimed')) AS active_borrows
     FROM borrow_requests br
     JOIN students s ON s.id = br.student_id
     WHERE br.status = 'pending'
     ORDER BY br.requested_at ASC"
);
$stmt->execute();
$requests = $stmt->fetchAll();

$status = $_GET['status'] ?? null;
$error = $_GET['error'] ?? null;
$status_message = null;
$error_message = null;

if ($status === 'approved') {
    $status_message = 'Borrow request approved.';
} elseif ($status === 'rejected') {
    $status_message = 'Borrow request marked as not available.';
}

if ($error === 'borrow_limit') {
    $error_message = 'Cannot approve: Student has reached the maximum borrowing limit of 3 books. Request student to return books first.';
}

admin_render_header('Borrow Requests');
?>

<style>
    .sl-borrow-hero {
        position: relative;
        overflow: hidden;
        background: linear-gradient(90deg, #FF6A00, #FF8C00, #FFA500);
        border-radius: 14px;
        color: #fff;
        padding: 1.2rem 1.25rem;
        box-shadow: 0 12px 30px rgba(255, 106, 0, 0.2);
    }
    .sl-borrow-hero::after {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(255, 165, 0, 0.2), transparent 45%);
        pointer-events: none;
    }
    .sl-borrow-hero h2,
    .sl-borrow-hero p {
        position: relative;
        z-index: 1;
    }
    .sl-borrow-alert {
        border-width: 1px;
        box-shadow: 0 10px 24px rgba(255, 106, 0, 0.06);
    }
    .sl-borrow-card {
        border: 1px solid rgba(255, 106, 0, 0.12);
        background: linear-gradient(145deg, #ffffff, #fff8f3);
        box-shadow: 0 12px 28px rgba(255, 106, 0, 0.08);
    }
    .sl-borrow-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.85rem;
    }
    .sl-borrow-title {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        margin: 0;
    }
    .sl-borrow-title .dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #FFA500;
        box-shadow: 0 0 0 4px rgba(255, 165, 0, 0.22);
    }
    .sl-borrow-chip {
        font-size: 0.76rem;
        border: 1px solid rgba(255, 165, 0, 0.45);
        background: rgba(255, 165, 0, 0.18);
        color: #A05000;
        border-radius: 999px;
        padding: 0.2rem 0.55rem;
        display: inline-flex;
        align-items: center;
    }
</style>

<div class="row mb-4">
    <div class="col-12">
        <div class="sl-page-header sl-borrow-hero">
            <h2 class="mb-1">Borrow Requests</h2>
            <p class="mb-0" style="color: rgba(255, 255, 255, 0.9);">
                Review student requests and mark as Available (approve) or Not Available (reject).
            </p>
        </div>
    </div>
</div>

<?php if ($status_message): ?>
    <div class="alert alert-success sl-borrow-alert">
        <?php echo htmlspecialchars($status_message, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-danger sl-borrow-alert">
        <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<style>
.sl-borrow-requests-table {
    table-layout: fixed;
    width: 100%;
    font-size: clamp(0.74rem, 0.2vw + 0.7rem, 0.86rem);
}

.sl-borrow-requests-table th,
.sl-borrow-requests-table td {
    padding: 0.42rem 0.45rem;
    vertical-align: middle;
    overflow-wrap: anywhere;
    word-break: break-word;
}

.sl-borrow-requests-table th {
    white-space: nowrap;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #6c757d;
    border-bottom-width: 1px;
}

.sl-borrow-requests-table td {
    white-space: normal;
}

.sl-borrow-requests-table tbody tr:hover {
    background: rgba(128, 0, 0, 0.03);
}

.sl-borrow-requests-table .btn {
    border-radius: 999px;
    font-weight: 600;
}

.sl-qr-token-cell {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.35rem;
}

.sl-qr-token-value {
    max-width: 100%;
    font-size: 0.74rem;
    white-space: normal;
    overflow-wrap: anywhere;
}

.sl-copy-message {
    display: block;
    min-height: 1rem;
    margin-top: 0.2rem;
    color: #198754;
    font-size: 0.75rem;
    font-weight: 600;
}

@media (max-width: 1199.98px) {
    .sl-borrow-requests-table {
        font-size: clamp(0.72rem, 0.25vw + 0.66rem, 0.82rem);
    }

    .sl-borrow-requests-table th,
    .sl-borrow-requests-table td {
        padding: 0.36rem 0.4rem;
    }
}

@media (max-width: 991.98px) {
    .sl-borrow-requests-table thead {
        display: none;
    }

    .sl-borrow-requests-table,
    .sl-borrow-requests-table tbody,
    .sl-borrow-requests-table tr,
    .sl-borrow-requests-table td {
        display: block;
        width: 100%;
    }

    .sl-borrow-requests-table tr {
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
        margin-bottom: 0.75rem;
        background-color: #fff;
    }

    .sl-borrow-requests-table td {
        border: 0;
        padding: 0.4rem 0;
        text-align: left !important;
    }

    .sl-borrow-requests-table td::before {
        content: attr(data-label);
        display: block;
        font-size: 0.75rem;
        color: #6c757d;
        margin-bottom: 0.15rem;
        font-weight: 600;
    }

    .sl-borrow-actions form {
        display: inline-block;
        margin-right: 0.35rem;
        margin-bottom: 0.35rem;
    }
}
</style>

<div class="card sl-card sl-borrow-card">
    <div class="card-body">
        <div class="sl-borrow-head">
            <h5 class="fw-semibold sl-borrow-title"><span class="dot"></span>Pending Borrow Requests</h5>
            <span class="sl-borrow-chip"><?php echo count($requests); ?> Pending</span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle sl-borrow-requests-table">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Student ID</th>
                    <th>Course / Section</th>
                    <th>Active Borrows</th>
                    <th>Email</th>
                    <th>Requested Books</th>
                    <th> </th>
                    <th>Category</th>
                    <th class="text-center">Copies</th>
                    <th>Notes</th>
                    <th>QR Token</th>
                    <th>Requested At</th>
                    <th class="text-center">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($requests): ?>
                    <?php foreach ($requests as $r): ?>
                        <tr>
                            <td data-label="Name"><?php echo htmlspecialchars($r['student_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td data-label="Student ID"><?php echo htmlspecialchars($r['student_code'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td data-label="Course / Section">
                                <?php echo htmlspecialchars($r['course'], ENT_QUOTES, 'UTF-8'); ?>
                                /
                                <?php echo htmlspecialchars($r['section'], ENT_QUOTES, 'UTF-8'); ?>
                            </td>
                            <td data-label="Active Borrows">
                                <?php 
                                $active = (int)($r['active_borrows'] ?? 0);
                                echo $active . ' / 3';
                                ?>
                            </td>
                            <td data-label="Email"><?php echo htmlspecialchars($r['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td data-label="Requested Books" class="text-break"><?php echo htmlspecialchars($r['book_titles'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td></td>
                            <td data-label="Category" class="text-break"><?php echo htmlspecialchars($r['book_categories'] ?? 'Uncategorized', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td data-label="Copies" class="text-center"><?php echo (int)($r['total_copies'] ?? 0); ?></td>
                            <td data-label="Notes">
                                <?php if ($r['notes']): ?>
                                    <span class="badge bg-info" title="<?php echo htmlspecialchars($r['notes'], ENT_QUOTES, 'UTF-8'); ?>">Has note</span>
                                    <div class="small text-muted mt-1" style="max-width: 150px; word-wrap: break-word;"><?php echo htmlspecialchars(substr($r['notes'], 0, 100), ENT_QUOTES, 'UTF-8'); ?><?php if (strlen($r['notes']) > 100) echo '...'; ?></div>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="QR Token">
                                <div class="sl-qr-token-cell">
                                    <code class="js-qr-token sl-qr-token-value" data-token="<?php echo htmlspecialchars($r['qr_token'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($r['qr_token'], ENT_QUOTES, 'UTF-8'); ?></code>
                                    <button type="button" class="btn btn-outline-secondary btn-sm js-qr-copy" aria-label="Copy QR token">Copy</button>
                                </div>
                                <span class="sl-copy-message js-copy-message" aria-live="polite"></span>
                            </td>
                            <td data-label="Requested At"><?php echo htmlspecialchars($r['requested_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td data-label="Actions" class="text-center sl-borrow-actions">
                                <form action="<?php echo BASE_URL; ?>/admin/borrow_request_action.php" method="post" class="d-inline">
                                    <input type="hidden" name="request_id" value="<?php echo (int)$r['id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        Available
                                    </button>
                                </form>
                                <form action="<?php echo BASE_URL; ?>/admin/borrow_request_action.php" method="post" class="d-inline ms-1">
                                    <input type="hidden" name="request_id" value="<?php echo (int)$r['id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-secondary btn-sm">
                                        Not Available
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="13" class="text-center text-muted">
                            No pending borrow requests.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function () {
    var copyButtons = document.querySelectorAll('.js-qr-copy');

    function copyTextToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text);
        }

        return new Promise(function (resolve, reject) {
            var tempInput = document.createElement('input');
            tempInput.type = 'text';
            tempInput.value = text;
            document.body.appendChild(tempInput);
            tempInput.select();
            tempInput.setSelectionRange(0, tempInput.value.length);

            try {
                var successful = document.execCommand('copy');
                document.body.removeChild(tempInput);
                if (successful) {
                    resolve();
                    return;
                }
                reject(new Error('Copy command was not successful.'));
            } catch (err) {
                document.body.removeChild(tempInput);
                reject(err);
            }
        });
    }

    copyButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            var tokenCell = button.closest('td');
            var tokenEl = tokenCell ? tokenCell.querySelector('.js-qr-token') : null;
            var messageEl = tokenCell ? tokenCell.querySelector('.js-copy-message') : null;
            if (!tokenEl) {
                return;
            }

            var rawToken = tokenEl.getAttribute('data-token') || '';
            if (rawToken === '') {
                return;
            }

            copyTextToClipboard(rawToken).then(function () {
                if (messageEl) {
                    messageEl.textContent = 'QR Token copied.';
                }
                button.classList.remove('btn-outline-secondary');
                button.classList.add('btn-success');
                setTimeout(function () {
                    if (messageEl) {
                        messageEl.textContent = '';
                    }
                    button.classList.remove('btn-success');
                    button.classList.add('btn-outline-secondary');
                }, 1600);
            }).catch(function () {
                if (messageEl) {
                    messageEl.textContent = 'Copy failed.';
                }
                button.classList.remove('btn-outline-secondary');
                button.classList.add('btn-danger');
                setTimeout(function () {
                    if (messageEl) {
                        messageEl.textContent = '';
                    }
                    button.classList.remove('btn-danger');
                    button.classList.add('btn-outline-secondary');
                }, 1500);
            });
        });
    });
})();
</script>
<?php
admin_render_footer();
?>
