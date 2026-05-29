<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_once __DIR__ . '/../includes/borrow_requests_helper.php';

require_admin_login();

$pdo = db_connect();

$requests = sl_get_pending_borrow_requests($pdo);
$request_book_availability = sl_get_request_book_availability($pdo, $requests);

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

    .sl-borrow-requests-table {
        table-layout: fixed;
        width: 100%;
        font-size: 0.84rem;
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

    .sl-book-availability-list {
        display: grid;
        gap: 0.45rem;
    }

    .sl-book-availability-item {
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        padding-bottom: 0.4rem;
    }

    .sl-book-availability-item:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .sl-book-availability-meta {
        color: #6c757d;
        font-size: 0.76rem;
        line-height: 1.35;
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
            font-size: 0.8rem;
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

<div class="card sl-card sl-borrow-card">
    <div class="card-body">
        <div class="sl-borrow-head">
            <h5 class="fw-semibold sl-borrow-title">
                <span class="dot"></span>
                Pending Borrow Requests
            </h5>

            <span class="sl-borrow-chip" id="pendingBorrowChip">
                <?php echo count($requests); ?> Pending
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-sm align-middle sl-borrow-requests-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Student ID</th>
                        <th>Course</th>
                        <th>Year / Section</th>
                        <th>Active Borrows</th>
                        <th>Email</th>
                        <th>Requested Books</th>
                        <th>Category</th>
                        <th class="text-center">Copies</th>
                        <th>Notes</th>
                        <th>QR Token</th>
                        <th>Requested At</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>

                <tbody id="borrowRequestsTableBody">
                    <?php if ($requests): ?>
                        <?php foreach ($requests as $r): ?>
                            <tr>
                                <td data-label="Name">
                                    <?php echo htmlspecialchars($r['student_name'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td data-label="Student ID">
                                    <?php echo htmlspecialchars($r['student_code'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td data-label="Course">
                                    <?php echo htmlspecialchars($r['course'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td data-label="Year / Section">
                                    <?php echo htmlspecialchars($r['section'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td data-label="Active Borrows">
                                    <?php
                                    $active = (int)($r['active_borrows'] ?? 0);
                                    echo $active . ' / 3';
                                    ?>
                                </td>

                                <td data-label="Email">
                                    <?php echo htmlspecialchars($r['email'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td data-label="Requested Books" class="text-break">
                                    <?php $availability_books = $request_book_availability[(int)$r['id']] ?? []; ?>

                                    <?php if ($availability_books): ?>
                                        <div class="sl-book-availability-list">
                                            <?php foreach ($availability_books as $book_info): ?>
                                                <?php
                                                $available_copies = (int)($book_info['copies_available'] ?? 0);
                                                $availability_status = $available_copies > 0 ? 'Available' : 'Not Available';
                                                ?>

                                                <div class="sl-book-availability-item">
                                                    <div>
                                                        <?php echo htmlspecialchars($book_info['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>

                                                    <div class="sl-book-availability-meta">
                                                        Available Copies: <?php echo $available_copies; ?>
                                                    </div>

                                                    <div class="sl-book-availability-meta">
                                                        Status: <?php echo htmlspecialchars($availability_status, ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($r['book_titles'] ?? 'No books listed', ENT_QUOTES, 'UTF-8'); ?>
                                    <?php endif; ?>
                                </td>

                                <td data-label="Category" class="text-break">
                                    <?php echo htmlspecialchars($r['book_categories'] ?? 'Uncategorized', ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td data-label="Copies" class="text-center">
                                    <?php echo (int)($r['total_copies'] ?? 0); ?>
                                </td>

                                <td data-label="Notes">
                                    <?php if ($r['notes']): ?>
                                        <span class="badge bg-info" title="<?php echo htmlspecialchars($r['notes'], ENT_QUOTES, 'UTF-8'); ?>">
                                            Has note
                                        </span>

                                        <div class="small text-muted mt-1" style="max-width: 150px; word-wrap: break-word;">
                                            <?php echo htmlspecialchars(substr($r['notes'], 0, 100), ENT_QUOTES, 'UTF-8'); ?>
                                            <?php if (strlen($r['notes']) > 100): ?>
                                                ...
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>

                                <td data-label="QR Token">
                                    <?php $qr_token = $r['qr_token'] ?? ''; ?>

                                    <div class="sl-qr-token-cell">
                                        <code
                                            class="js-qr-token sl-qr-token-value"
                                            data-token="<?php echo htmlspecialchars($qr_token, ENT_QUOTES, 'UTF-8'); ?>"
                                        >
                                            <?php echo htmlspecialchars($qr_token, ENT_QUOTES, 'UTF-8'); ?>
                                        </code>

                                        <button type="button" class="btn btn-outline-secondary btn-sm js-qr-copy" aria-label="Copy QR token">
                                            Copy
                                        </button>
                                    </div>

                                    <span class="sl-copy-message js-copy-message" aria-live="polite"></span>
                                </td>

                                <td data-label="Requested At">
                                    <?php echo htmlspecialchars($r['requested_at'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>

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
    var tableBody = document.getElementById('borrowRequestsTableBody');
    var pendingChip = document.getElementById('pendingBorrowChip');
    var endpoint = '<?php echo BASE_URL; ?>/admin/borrow_requests_live.php';

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

    document.addEventListener('click', function (event) {
        var button = event.target.closest('.js-qr-copy');

        if (!button) {
            return;
        }

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

    function refreshBorrowRequests() {
        if (!tableBody) {
            return;
        }

        fetch(endpoint, {
            headers: { 'Accept': 'application/json' },
            cache: 'no-store'
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Request failed');
                }

                return response.json();
            })
            .then(function (data) {
                var count = Number(data.pending_count || 0);

                if (typeof data.rows_html === 'string') {
                    tableBody.innerHTML = data.rows_html;
                }

                if (pendingChip) {
                    pendingChip.textContent = count + ' Pending';
                }
            })
            .catch(function () {
                // Leave the current table visible if the connection briefly drops.
            });
    }

    refreshBorrowRequests();
    window.setInterval(refreshBorrowRequests, 5000);
})();
</script>

<?php
admin_render_footer();
?>
