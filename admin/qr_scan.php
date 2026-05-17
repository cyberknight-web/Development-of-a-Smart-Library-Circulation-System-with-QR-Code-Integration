<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/admin_layout.php';

require_admin_login();

$pdo = db_connect();

$qr_token = trim($_GET['qr'] ?? '');
$record = null;
$items = [];
$error = null;

if ($qr_token !== '') {
    $stmt = $pdo->prepare(
        "SELECT br.*, s.name AS student_name, s.student_id AS student_code, s.course, s.section, s.email
         FROM borrow_requests br
         JOIN students s ON s.id = br.student_id
         WHERE br.qr_token = :qr_token
         LIMIT 1"
    );
    $stmt->execute([':qr_token' => $qr_token]);
    $record = $stmt->fetch();

    if ($record) {
        $stmt_items = $pdo->prepare(
            "SELECT bri.*, b.title
             FROM borrow_request_items bri
             JOIN books b ON b.id = bri.book_id
             WHERE bri.borrow_request_id = :req_id"
        );
        $stmt_items->execute([':req_id' => $record['id']]);
        $items = $stmt_items->fetchAll();
    } else {
        $error = 'No record found for this QR token.';
    }
}

function format_qr_returned_on(?string $returned_at): string
{
    if (!$returned_at) {
        return '';
    }

    try {
        $returned_date = new DateTimeImmutable($returned_at, new DateTimeZone(APP_TIMEZONE));
        return 'Returned on: ' . $returned_date->format('F j, Y g:i A');
    } catch (Throwable $e) {
        error_log('QR scan returned date formatting failed: ' . $e->getMessage());
        return 'Returned on: ' . $returned_at;
    }
}

admin_render_header('QR Scan');
?>

<style>
    .sl-qr-hero {
        background: linear-gradient(90deg, #FF6A00, #FF8C00, #FFA500);
        border-radius: 14px;
        padding: 2rem 1.5rem;
        color: #fff;
        margin-bottom: 2rem;
        box-shadow: 0 8px 20px rgba(255, 106, 0, 0.15);
    }
    .sl-qr-hero h2 {
        font-weight: 700;
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }
    .sl-qr-hero p {
        font-size: 1rem;
        opacity: 0.95;
        margin: 0;
    }
    .sl-qr-scanner-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: box-shadow 0.3s ease;
    }
    .sl-qr-scanner-card:hover {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }
    .sl-qr-scanner-card .card-title {
        color: #212529;
        font-size: 1.1rem;
        border-bottom: 2px solid rgba(255, 106, 0, 0.1);
        padding-bottom: 1rem;
        margin-bottom: 1rem !important;
    }
    #qr-reader {
        border-radius: 10px !important;
        box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.15);
        min-height: 300px;
    }
    .btn-group-sm > .btn {
        border-radius: 6px;
    }
    .sl-student-info {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .sl-student-info dt {
        font-weight: 600;
        color: #495057;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .sl-student-info dd {
        color: #212529;
        font-size: 1rem;
        margin-bottom: 0.75rem;
    }
    .sl-student-info dd:last-child {
        margin-bottom: 0;
    }
    .sl-books-table {
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }
    .sl-books-table thead {
        background: linear-gradient(to right, rgba(255, 106, 0, 0.08), rgba(255, 106, 0, 0.04));
        border-bottom: 2px solid rgba(255, 106, 0, 0.1);
    }
    .sl-books-table thead th {
        color: #495057;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 1rem;
    }
    .sl-books-table tbody tr {
        border-bottom: 1px solid #e9ecef;
        transition: background-color 0.2s ease;
    }
    .sl-books-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    .sl-books-table tbody td {
        padding: 0.75rem 1rem;
        vertical-align: middle;
    }
    .sl-action-buttons {
        display: flex;
        gap: 0.75rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e9ecef;
    }
    .sl-action-buttons .btn {
        flex: 1;
        padding: 0.75rem 1rem;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    .sl-status-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .sl-status-approved {
        background: #d1ecf1;
        color: #0c5460;
    }
    .sl-status-claimed {
        background: #fff3cd;
        color: #856404;
    }
    .sl-status-returned {
        background: #d4edda;
        color: #155724;
    }
    .sl-status-pending {
        background: #e2e3e5;
        color: #383d41;
    }
    .form-label {
        font-weight: 600;
        color: #495057;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }
    .form-control {
        border-radius: 8px;
        border: 1px solid #dee2e6;
        padding: 0.75rem 1rem;
        font-size: 1rem;
    }
    .form-control:focus {
        border-color: #800000;
        box-shadow: 0 0 0 0.2rem rgba(128, 0, 0, 0.1);
    }
    .sl-qr-loading {
        display: none;
        align-items: center;
        gap: 0.5rem;
        margin-top: 1rem;
    }
    .sl-qr-loading.is-visible {
        display: flex;
    }
</style>

<div class="sl-qr-hero">
    <h2>🔍 QR Code Scanner</h2>
    <p>Scan or enter student QR codes to review details, mark requests as claimed, or process returns.</p>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card sl-qr-scanner-card">
            <div class="card-body">
                <h5 class="card-title">📷 Scan QR Code</h5>
                <p class="text-muted small mb-4">
                    Position the student's QR code in front of your camera, or manually enter the token below.
                </p>

                <div class="mb-4">
                    <div id="qr-reader" class="border rounded overflow-hidden" style="max-width: 100%; display: none; background: #1a1a1a;"></div>
                    <div id="qr-reader-status" class="small text-muted mt-2 text-center" style="min-height: 20px;"></div>
                    <div class="btn-group btn-group-sm mt-3 w-100" role="group">
                        <button type="button" class="btn btn-outline-danger" id="btnStartCamera" style="flex: 1;">▶ Start Camera</button>
                        <button type="button" class="btn btn-outline-secondary" id="btnStopCamera" style="flex: 1; display: none;">⏹ Stop Camera</button>
                    </div>
                </div>

                <form method="get" id="qrForm">
                    <div class="mb-3">
                        <label for="qr" class="form-label">QR Token</label>
                        <input
                            type="text"
                            class="form-control"
                            id="qr"
                            name="qr"
                            value="<?php echo htmlspecialchars($qr_token, ENT_QUOTES, 'UTF-8'); ?>"
                            placeholder="Scan QR or paste token here"
                            required
                        >
                    </div>
                    <button type="submit" class="btn btn-sl-primary w-100">
                        🔍 Search Record
                    </button>
                </form>
                <div class="alert alert-info sl-qr-loading" id="qrLoadingMessage" role="status" aria-live="polite">
                    <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                    <span>Fetching borrowing request...</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7" id="review-actions">
        <div class="card sl-qr-scanner-card">
            <div class="card-body">
                <h5 class="card-title">📋 Request Details & Actions</h5>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>⚠ Error:</strong> <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php elseif ($record): ?>
                    <?php
                    $status = (string)($record['status'] ?? '');
                    $can_mark_claimed = ($status === 'approved');
                    $can_process_return = ($status === 'claimed');
                    
                    // Map status to badge class
                    $statusClass = 'sl-status-pending';
                    if ($status === 'approved') $statusClass = 'sl-status-approved';
                    elseif ($status === 'claimed') $statusClass = 'sl-status-claimed';
                    elseif ($status === 'returned') $statusClass = 'sl-status-returned';
                    ?>
                    <div class="sl-student-info">
                        <dl class="row mb-0">
                            <div class="col-12">
                                <h6 class="mb-3" style="color: #212529; font-weight: 700;">Student Information</h6>
                            </div>
                            <dt class="col-md-4">📛 Name</dt>
                            <dd class="col-md-8"><?php echo htmlspecialchars($record['student_name'], ENT_QUOTES, 'UTF-8'); ?></dd>

                            <dt class="col-md-4">🆔 Student ID</dt>
                            <dd class="col-md-8"><?php echo htmlspecialchars($record['student_code'], ENT_QUOTES, 'UTF-8'); ?></dd>

                            <dt class="col-md-4">📚 Course / Section</dt>
                            <dd class="col-md-8">
                                <?php echo htmlspecialchars($record['course'], ENT_QUOTES, 'UTF-8'); ?>
                                /
                                <?php echo htmlspecialchars($record['section'], ENT_QUOTES, 'UTF-8'); ?>
                            </dd>

                            <dt class="col-md-4">✉️ Email</dt>
                            <dd class="col-md-8">
                                <a href="mailto:<?php echo htmlspecialchars($record['email'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($record['email'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </dd>

                            <dt class="col-md-4">📊 Status</dt>
                            <dd class="col-md-8">
                                <span class="sl-status-badge <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </dd>

                            <?php if ($status === 'returned' && !empty($record['returned_at'])): ?>
                            <dt class="col-md-4">Returned Date/Time</dt>
                            <dd class="col-md-8"><?php echo htmlspecialchars(format_qr_returned_on($record['returned_at'] ?? null), ENT_QUOTES, 'UTF-8'); ?></dd>
                            <?php endif; ?>
                            
                            <?php if ($record['notes']): ?>
                            <dt class="col-md-4">📝 Notes</dt>
                            <dd class="col-md-8">
                                <div class="alert alert-info mb-0" style="font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($record['notes'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </dd>
                            <?php endif; ?>
                        </dl>
                    </div>

                    <h6 class="mb-3" style="color: #212529; font-weight: 700;">📚 Books in Request</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-hover align-middle mb-0 sl-books-table">
                            <thead>
                            <tr>
                                <th>Book Title</th>
                                <th class="text-center">Qty</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if ($items): ?>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary"><?php echo (int)$item['quantity']; ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">
                                        No books found in this request.
                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="sl-action-buttons">
                        <?php if ($can_mark_claimed): ?>
                            <form action="<?php echo BASE_URL; ?>/admin/qr_scan_action.php" method="post" class="js-qr-action-form w-100" data-confirm-title="Mark as Claimed" data-confirm-msg="Are you sure you want to mark this request as claimed?">
                                <input type="hidden" name="request_id" value="<?php echo (int)$record['id']; ?>">
                                <input type="hidden" name="action" value="claimed">
                                <input type="hidden" name="qr" value="<?php echo htmlspecialchars($qr_token, ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="btn btn-sl-primary w-100">
                                    ✓ Mark as Claimed
                                </button>
                            </form>
                        <?php else: ?>
                            <button type="button" class="btn btn-sl-primary w-100" disabled>
                                ✓ Mark as Claimed
                            </button>
                        <?php endif; ?>

                        <?php if ($can_process_return): ?>
                            <form action="<?php echo BASE_URL; ?>/admin/qr_scan_action.php" method="post" class="js-qr-action-form w-100" data-confirm-title="Process Return" data-confirm-msg="Are you sure you want to process this return?">
                                <input type="hidden" name="request_id" value="<?php echo (int)$record['id']; ?>">
                                <input type="hidden" name="action" value="returned">
                                <input type="hidden" name="qr" value="<?php echo htmlspecialchars($qr_token, ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="btn btn-sl-accent w-100">
                                    ↩ Process Return
                                </button>
                            </form>
                        <?php else: ?>
                            <button type="button" class="btn btn-sl-accent w-100" disabled>
                                ↩ Process Return
                            </button>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p style="font-size: 1.1rem; color: #6c757d;">
                            👀 Scan a QR code or enter a token to view student details and actions.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmActionModal" tabindex="-1" aria-labelledby="confirmActionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);">
            <div class="modal-header" style="border-bottom: 1px solid #e9ecef; background: #f8f9fa; border-radius: 12px 12px 0 0;">
                <h5 class="modal-title" id="confirmActionModalLabel" style="font-weight: 700; color: #212529;"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="confirmActionModalBody" style="padding: 1.5rem; font-size: 1rem;"></div>
            <div class="modal-footer" style="border-top: 1px solid #e9ecef; background: #f8f9fa;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sl-primary" id="confirmActionYes">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var qrInput = document.getElementById('qr');
    var qrForm = document.getElementById('qrForm');
    var readerDiv = document.getElementById('qr-reader');
    var statusEl = document.getElementById('qr-reader-status');
    var btnStart = document.getElementById('btnStartCamera');
    var btnStop = document.getElementById('btnStopCamera');
    var loadingMessage = document.getElementById('qrLoadingMessage');

    var scanner = null;

    function showLoadingMessage() {
        if (loadingMessage) {
            loadingMessage.classList.add('is-visible');
        }
    }

    function stopCamera() {
        if (scanner && scanner.isScanning()) {
            scanner.stop().then(function() {
                readerDiv.style.display = 'none';
                btnStart.style.display = 'block';
                btnStop.style.display = 'none';
                statusEl.innerHTML = '';
                statusEl.textContent = '';
            }).catch(function(err) {
                console.error(err);
            });
        }
    }

    function startCamera() {
        readerDiv.style.display = 'block';
        if (!scanner) {
            scanner = new Html5Qrcode('qr-reader');
        }
        scanner.start(
            { facingMode: 'environment' },
            {
                fps: 10,
                qrbox: function(viewfinderWidth, viewfinderHeight) {
                    var size = Math.floor(Math.min(viewfinderWidth, viewfinderHeight, 250) * 0.85);
                    return { width: Math.max(size, 140), height: Math.max(size, 140) };
                }
            },
            function(decodedText) {
                qrInput.value = decodedText;
                showLoadingMessage();
                statusEl.innerHTML = '<span style="color: #198754; font-weight: 600;">✓ QR scanned! Loading record...</span>';
                setTimeout(function() {
                    if (qrForm) {
                        qrForm.submit();
                    } else {
                        var targetUrl = window.location.pathname + '?qr=' + encodeURIComponent(decodedText) + '#review-actions';
                        window.location.href = targetUrl;
                    }
                }, 500);
            },
            function() {}
        ).then(function() {
            btnStart.style.display = 'none';
            btnStop.style.display = 'block';
            statusEl.innerHTML = '<span style="color: #0d6efd;">📹 Position the QR code in front of the camera...</span>';
        }).catch(function(err) {
            readerDiv.style.display = 'none';
            statusEl.innerHTML = '<span style="color: #dc3545; font-weight: 600;">⚠ Camera error: ' + (err.message || 'Could not access camera. Please allow camera permission.') + '</span>';
            console.error(err);
        });
    }

    btnStart.addEventListener('click', startCamera);
    btnStop.addEventListener('click', stopCamera);

    if (qrForm) {
        qrForm.addEventListener('submit', function() {
            showLoadingMessage();
        });
    }

    window.addEventListener('beforeunload', function() {
        if (scanner && scanner.isScanning()) {
            scanner.stop().catch(function() {});
        }
    });

    // After redirect from scan, scroll Review & Actions into view
    if (window.location.hash === '#review-actions') {
        var el = document.getElementById('review-actions');
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    // Yes/No confirmation modal before Mark as Claimed or Process Return
    var confirmModalEl = document.getElementById('confirmActionModal');
    var confirmTitleEl = document.getElementById('confirmActionModalLabel');
    var confirmBodyEl = document.getElementById('confirmActionModalBody');
    var confirmYesBtn = document.getElementById('confirmActionYes');
    var pendingForm = null;

    if (confirmModalEl && confirmTitleEl && confirmBodyEl && confirmYesBtn) {
        var confirmModal = typeof bootstrap !== 'undefined' ? new bootstrap.Modal(confirmModalEl) : null;

        document.querySelectorAll('.js-qr-action-form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                var title = form.getAttribute('data-confirm-title') || 'Confirm';
                var msg = form.getAttribute('data-confirm-msg') || 'Are you sure?';
                confirmTitleEl.textContent = title;
                confirmBodyEl.textContent = msg;
                pendingForm = form;
                if (confirmModal) {
                    confirmModal.show();
                }
            });
        });

        confirmYesBtn.addEventListener('click', function() {
            if (pendingForm) {
                pendingForm.removeAttribute('data-confirm-title');
                pendingForm.removeAttribute('data-confirm-msg');
                pendingForm.submit();
                pendingForm = null;
            }
            if (confirmModal) {
                confirmModal.hide();
            }
        });
    }
});
</script>

<?php
admin_render_footer();
?>

