<?php
// student/shelves.php
// Selected books (cart). Review, fill borrow form, generate QR code.

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/student_auth.php';
require_once __DIR__ . '/../includes/student_layout.php';

require_student_login();

$pdo = db_connect();
$cart = student_sync_cart_with_books($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $book_id = isset($_POST['book_id']) ? (int)$_POST['book_id'] : 0;
    if ($action === 'remove' && $book_id > 0) {
        student_remove_from_cart($book_id);
        header('Location: ' . BASE_URL . '/student/shelves.php');
        exit;
    }
}

$cart_books = [];
if (!empty($cart)) {
    $placeholders = implode(',', array_fill(0, count($cart), '?'));
    $stmt = $pdo->prepare("SELECT id, title, author, category FROM books WHERE id IN ($placeholders)");
    $stmt->execute($cart);
    $cart_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$errors = [];

/** @noinspection PhpUndefinedFunctionInspection */
student_render_header('My Shelves');
?>

<style>
    .sl-shelves-hero {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, rgba(128, 0, 0, 0.98), rgba(74, 0, 0, 0.95));
        border-radius: 14px;
        color: #fff;
        padding: 1.2rem 1.25rem;
        box-shadow: 0 12px 30px rgba(74, 0, 0, 0.2);
    }
    .sl-shelves-hero::after {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(255, 200, 92, 0.22), transparent 45%);
        pointer-events: none;
    }
    .sl-shelves-hero h2,
    .sl-shelves-hero p {
        position: relative;
        z-index: 1;
    }
    .sl-shelves-alert {
        border-width: 1px;
        box-shadow: 0 10px 24px rgba(128, 0, 0, 0.06);
    }
    .sl-shelves-alert.alert-warning {
        border-color: rgba(255, 200, 92, 0.45);
    }
    .sl-shelves-shell {
        border: 1px solid rgba(128, 0, 0, 0.12);
        background: linear-gradient(145deg, #ffffff, #fafafa);
        box-shadow: 0 12px 28px rgba(128, 0, 0, 0.08);
    }
    .sl-shelves-title {
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }
    .sl-shelves-title .dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #ffc85c;
        box-shadow: 0 0 0 4px rgba(255, 200, 92, 0.22);
    }
    .sl-selected-list .list-group-item {
        border-color: rgba(128, 0, 0, 0.1);
        background: transparent;
    }
    .sl-selected-list .list-group-item:hover {
        background: rgba(128, 0, 0, 0.03);
    }
    .sl-borrow-form .form-label {
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #6c757d;
        margin-bottom: 0.35rem;
    }
    .sl-borrow-form .form-control {
        border-color: rgba(128, 0, 0, 0.18);
    }
    .sl-borrow-form .form-control:focus {
        border-color: #800000;
        box-shadow: 0 0 0 0.2rem rgba(128, 0, 0, 0.15);
    }
    .sl-borrow-form .form-control[readonly] {
        background-color: #fffaf2;
    }
</style>

<div class="row mb-4">
    <div class="col-12">
        <div class="sl-page-header sl-shelves-hero">
            <h2 class="mb-1">My Shelves</h2>
            <p class="mb-0" style="color: rgba(255, 255, 255, 0.9);">Review your selected books, fill the form, and generate your QR code to claim at the library. Max 3 books. Return within 3 days.</p>
        </div>
    </div>
</div>

<?php
$shelves_error = $_GET['error'] ?? '';
if ($shelves_error === 'cart'): ?>
    <div class="alert alert-warning sl-shelves-alert">Please add 1 to 3 books from Choose Books first.</div>
<?php elseif ($shelves_error === 'unavailable'): ?>
    <div class="alert alert-warning sl-shelves-alert">One or more selected books are no longer available. Please choose again.</div>
<?php elseif ($shelves_error === 'submit'): ?>
    <div class="alert alert-danger sl-shelves-alert">Could not create request. Please try again.</div>
<?php elseif ($shelves_error === 'borrow_limit'): ?>
    <div class="alert alert-warning sl-shelves-alert">
        Borrowing limit reached. You can borrow only the remaining number of books not yet returned.
        Return books first, then try again.
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card sl-card sl-shelves-shell">
            <div class="card-body">
                <h5 class="card-title fw-semibold mb-3 sl-shelves-title"><span class="dot"></span>Selected Books</h5>
                <?php if ($cart_books): ?>
                    <ul class="list-group list-group-flush sl-selected-list">
                        <?php foreach ($cart_books as $b): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo htmlspecialchars($b['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <?php if ($b['author'] ?? '') echo ' <span class="text-muted">– ' . htmlspecialchars($b['author'], ENT_QUOTES, 'UTF-8') . '</span>'; ?>
                                </div>
                                <form method="post">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="book_id" value="<?php echo (int)$b['id']; ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Remove</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <p class="text-muted small mt-2 mb-0"><?php echo count($cart_books); ?> / 3 books selected.</p>
                <?php else: ?>
                    <p class="text-muted mb-0">No books selected. <a href="<?php echo BASE_URL; ?>/student/choose_books.php">Choose Books</a> to add up to 3.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card sl-card sl-shelves-shell">
            <div class="card-body">
                <h5 class="card-title fw-semibold mb-3 sl-shelves-title"><span class="dot"></span>Borrow Form</h5>
                <p class="text-muted small">Your details (from your account). Click Generate QR Code to create your borrow request.</p>
                <form method="post" action="<?php echo BASE_URL; ?>/student/borrow_submit.php" class="sl-borrow-form" id="borrowRequestForm">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['student_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Student ID</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['student_code'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Course</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['student_course'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Year / Section</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['student_section'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['student_email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Additional Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Add any special instructions or notes for your borrow request..." maxlength="500"></textarea>
                        <small class="form-text text-muted">Max 500 characters</small>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-sl-primary" <?php if (empty($cart)) echo ' disabled'; ?> name="generate_qr" value="1">
                            Generate QR Code
                        </button>
                    </div>
                </form>
                <?php if (empty($cart)): ?>
                    <p class="text-muted small mt-2 mb-0">Add books from Choose Books first.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="borrowConfirmModal" tabindex="-1" aria-labelledby="borrowConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="borrowConfirmModalLabel">Confirm Borrowing Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to submit this borrowing request?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sl-primary" id="confirmBorrowRequest">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const borrowRequestForm = document.getElementById('borrowRequestForm');
        const confirmButton = document.getElementById('confirmBorrowRequest');
        const modalElement = document.getElementById('borrowConfirmModal');
        let confirmed = false;

        if (!borrowRequestForm || !confirmButton || !modalElement || typeof bootstrap === 'undefined') {
            return;
        }

        const confirmModal = new bootstrap.Modal(modalElement);

        borrowRequestForm.addEventListener('submit', function (event) {
            if (!confirmed) {
                event.preventDefault();
                confirmModal.show();
            }
        });

        confirmButton.addEventListener('click', function () {
            confirmed = true;
            confirmModal.hide();
            borrowRequestForm.submit();
        });
    });
</script>

<?php
/** @noinspection PhpUndefinedFunctionInspection */
student_render_footer();
?>
