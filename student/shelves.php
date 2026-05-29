<?php
// student/shelves.php
// Selected books (cart). Review, fill borrow form, generate QR code.

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/student_auth.php';
require_once __DIR__ . '/../includes/student_layout.php';
require_once __DIR__ . '/../includes/book_covers.php';

require_student_login();

$pdo = db_connect();
smartlibrary_ensure_book_cover_columns($pdo);
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
    $stmt = $pdo->prepare("SELECT id, title, author, category, cover_image FROM books WHERE id IN ($placeholders)");
    $stmt->execute($cart);
    $cart_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$errors = [];
$borrow_form_errors = $_SESSION['borrow_form_errors'] ?? [];
$borrow_form_values = array_merge([
    'selected_student_id' => $_SESSION['student_id'] ?? '',
    'name' => $_SESSION['student_name'] ?? '',
    'student_code' => $_SESSION['student_code'] ?? '',
    'course' => $_SESSION['student_course'] ?? '',
    'section' => $_SESSION['student_section'] ?? '',
    'email' => $_SESSION['student_email'] ?? '',
    'notes' => '',
], $_SESSION['borrow_form_values'] ?? []);
unset($_SESSION['borrow_form_errors'], $_SESSION['borrow_form_values']);

$student_options_stmt = $pdo->query(
    "SELECT id, name, student_id, course, section, email
     FROM students
     WHERE name <> ''
     ORDER BY name ASC"
);
$student_options = $student_options_stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['student_options_json'])) {
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store');
    echo json_encode(['students' => $student_options], JSON_THROW_ON_ERROR);
    exit;
}

$selected_student_id = (int)($borrow_form_values['selected_student_id'] ?: ($_SESSION['student_id'] ?? 0));
$selected_student = null;
foreach ($student_options as $student_option) {
    if ((int)$student_option['id'] === $selected_student_id) {
        $selected_student = $student_option;
        break;
    }
}
if ($selected_student) {
    $borrow_form_values['name'] = (string)$selected_student['name'];
    $borrow_form_values['student_code'] = (string)$selected_student['student_id'];
    $borrow_form_values['course'] = (string)$selected_student['course'];
    $borrow_form_values['section'] = (string)$selected_student['section'];
    $borrow_form_values['email'] = (string)$selected_student['email'];
}

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
    .sl-selected-book {
        gap: 0.85rem;
    }
    .sl-selected-book-main {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        min-width: 0;
    }
    .sl-book-cover-preview {
        width: 54px;
        height: 74px;
        object-fit: cover;
        flex: 0 0 auto;
        border: 1px solid rgba(128, 0, 0, 0.18);
        border-radius: 8px;
        background: #faf7f7;
        box-shadow: 0 6px 14px rgba(33, 37, 41, 0.08);
    }
    .sl-book-cover-placeholder {
        width: 54px;
        height: 74px;
        flex: 0 0 auto;
        border: 1px dashed rgba(128, 0, 0, 0.32);
        border-radius: 8px;
        color: #6c757d;
        background: #faf7f7;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.66rem;
        line-height: 1.1;
        text-align: center;
        padding: 0.3rem;
    }
    .sl-selected-book-text {
        min-width: 0;
    }
    .sl-borrow-form .form-label {
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #6c757d;
        margin-bottom: 0.35rem;
    }
    .sl-borrow-form .form-control,
    .sl-borrow-form .form-select {
        border-color: rgba(128, 0, 0, 0.18);
    }
    .sl-borrow-form .form-control:focus,
    .sl-borrow-form .form-select:focus {
        border-color: #800000;
        box-shadow: 0 0 0 0.2rem rgba(128, 0, 0, 0.15);
    }
    .sl-student-native-select {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
    }
    .sl-student-combobox {
        position: relative;
    }
    .sl-student-combobox-toggle {
        width: 100%;
        min-height: 42px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        border: 1px solid rgba(128, 0, 0, 0.18);
        border-radius: 8px;
        background: #fff;
        color: #212529;
        padding: 0.55rem 0.7rem;
        text-align: left;
    }
    .sl-student-combobox-toggle:focus {
        border-color: #800000;
        box-shadow: 0 0 0 0.2rem rgba(128, 0, 0, 0.15);
        outline: 0;
    }
    .sl-student-combobox-value {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .sl-student-combobox-arrow {
        flex: 0 0 auto;
        font-size: 0.75rem;
        color: #6c757d;
    }
    .sl-student-combobox-menu {
        position: absolute;
        z-index: 20;
        top: calc(100% + 0.35rem);
        left: 0;
        right: 0;
        display: none;
        border: 1px solid rgba(128, 0, 0, 0.2);
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 14px 30px rgba(33, 37, 41, 0.16);
        padding: 0.65rem;
    }
    .sl-student-combobox.is-open .sl-student-combobox-menu {
        display: block;
    }
    .sl-student-search-wrap {
        position: relative;
        margin-bottom: 0.45rem;
    }
    .sl-student-search-wrap::before {
        content: "Search";
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
    }
    .sl-student-search-icon {
        position: absolute;
        top: 50%;
        left: 0.75rem;
        transform: translateY(-50%);
        color: #6c757d;
        font-size: 0.9rem;
        pointer-events: none;
    }
    .sl-student-search {
        width: 100%;
        border: 1px solid rgba(33, 37, 41, 0.25);
        border-radius: 999px;
        padding: 0.42rem 0.75rem 0.42rem 2rem;
        font-size: 0.92rem;
    }
    .sl-student-options {
        max-height: 230px;
        overflow-y: auto;
        display: grid;
        gap: 0.12rem;
        padding: 0.15rem 0;
    }
    .sl-student-option {
        width: 100%;
        min-height: 38px;
        border: 0;
        border-radius: 6px;
        background: transparent;
        color: #212529;
        padding: 0.45rem 0.55rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        text-align: left;
    }
    .sl-student-option:hover,
    .sl-student-option:focus {
        background: rgba(128, 0, 0, 0.06);
        outline: 0;
    }
    .sl-student-option.is-selected {
        font-weight: 700;
    }
    .sl-student-option.is-disabled {
        color: #adb5bd;
        cursor: not-allowed;
    }
    .sl-student-check {
        color: #800000;
        font-weight: 800;
        flex: 0 0 auto;
    }
    .sl-student-empty {
        color: #6c757d;
        padding: 0.45rem 0.55rem;
        font-size: 0.9rem;
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

<?php if ($borrow_form_errors): ?>
    <div class="alert alert-danger sl-shelves-alert">
        <strong>Validation error.</strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($borrow_form_errors as $error): ?>
                <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
        </ul>
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
                            <?php $cover_url = smartlibrary_book_cover_url($b['cover_image'] ?? null); ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center sl-selected-book">
                                <div class="sl-selected-book-main">
                                    <?php if ($cover_url): ?>
                                        <img src="<?php echo htmlspecialchars($cover_url, ENT_QUOTES, 'UTF-8'); ?>" alt="Cover for <?php echo htmlspecialchars($b['title'], ENT_QUOTES, 'UTF-8'); ?>" class="sl-book-cover-preview">
                                    <?php else: ?>
                                        <div class="sl-book-cover-placeholder">No cover</div>
                                    <?php endif; ?>
                                    <div class="sl-selected-book-text">
                                        <strong><?php echo htmlspecialchars($b['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                        <?php if ($b['author'] ?? '') echo ' <span class="text-muted">– ' . htmlspecialchars($b['author'], ENT_QUOTES, 'UTF-8') . '</span>'; ?>
                                    </div>
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
                        <label for="selected_student_id" class="form-label">Fullname - Student ID</label>
                        <div class="sl-student-combobox" id="studentCombobox">
                            <button type="button" class="sl-student-combobox-toggle" id="studentComboboxToggle" aria-haspopup="listbox" aria-expanded="false">
                                <span class="sl-student-combobox-value" id="studentComboboxValue">Select Student</span>
                                <span class="sl-student-combobox-arrow" aria-hidden="true">▼</span>
                            </button>
                            <div class="sl-student-combobox-menu" id="studentComboboxMenu">
                                <div class="sl-student-search-wrap">
                                    <span class="sl-student-search-icon" aria-hidden="true">⌕</span>
                                    <input
                                        type="search"
                                        class="sl-student-search"
                                        id="studentSearch"
                                        placeholder="search"
                                        autocomplete="off"
                                    >
                                </div>
                                <div class="sl-student-options" id="studentOptionsList" role="listbox"></div>
                            </div>
                        </div>
                        <select class="form-select sl-student-native-select" id="selected_student_id" name="selected_student_id" required tabindex="-1" aria-hidden="true">
                            <option value="">Select Student</option>
                            <?php foreach ($student_options as $student_option): ?>
                                <?php $yearSection = trim((string)$student_option['section']);
                                $missingYearSection = ($yearSection === ''); ?>
                                <option
                                    value="<?php echo (int)$student_option['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($student_option['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-student-code="<?php echo htmlspecialchars($student_option['student_id'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-course="<?php echo htmlspecialchars($student_option['course'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-year-section="<?php echo htmlspecialchars($student_option['section'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-email="<?php echo htmlspecialchars($student_option['email'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-has-year-section="<?php echo $missingYearSection ? '0' : '1'; ?>"
                                    <?php echo $selected_student_id === (int)$student_option['id'] ? 'selected' : ''; ?>
                                    <?php echo $missingYearSection ? 'disabled title="Complete Year / Section in profile"' : ''; ?>
                                >
                                    <?php echo htmlspecialchars($student_option['name'] . ' - ' . $student_option['student_id'] . ($missingYearSection ? ' (No Year/Section)' : ''), ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="sectionWarning" class="alert alert-warning mt-2 d-none" role="alert">
                            Your account is missing Year / Section. Please update it in your <a href="<?php echo BASE_URL; ?>/student/profile.php">profile</a> before borrowing.
                        </div>
                        <input type="hidden" id="name" name="name" value="<?php echo htmlspecialchars($borrow_form_values['name'], ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" id="student_code" name="student_code" value="<?php echo htmlspecialchars($borrow_form_values['student_code'], ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" id="course" name="course" value="<?php echo htmlspecialchars($borrow_form_values['course'], ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" id="section" name="section" value="<?php echo htmlspecialchars($borrow_form_values['section'], ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" id="email" name="email" value="<?php echo htmlspecialchars($borrow_form_values['email'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Additional Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Add any special instructions or notes for your borrow request..." maxlength="500"><?php echo htmlspecialchars($borrow_form_values['notes'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                        <small class="form-text text-muted">Max 500 characters</small>
                    </div>
                    <div class="d-grid">
                        <button type="submit" id="generateQrBtn" class="btn btn-sl-primary" <?php if (empty($cart)) echo ' disabled'; ?> name="generate_qr" value="1">
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
        const studentSelect = document.getElementById('selected_student_id');
        const studentSearch = document.getElementById('studentSearch');
        const studentCombobox = document.getElementById('studentCombobox');
        const studentComboboxToggle = document.getElementById('studentComboboxToggle');
        const studentComboboxValue = document.getElementById('studentComboboxValue');
        const studentOptionsList = document.getElementById('studentOptionsList');
        const nameInput = document.getElementById('name');
        const studentCodeInput = document.getElementById('student_code');
        const courseInput = document.getElementById('course');
        const sectionInput = document.getElementById('section');
        const emailInput = document.getElementById('email');
        const borrowRequestForm = document.getElementById('borrowRequestForm');
        const confirmButton = document.getElementById('confirmBorrowRequest');
        const modalElement = document.getElementById('borrowConfirmModal');
        const modalTitle = document.getElementById('borrowConfirmModalLabel');
        const modalBody = modalElement ? modalElement.querySelector('.modal-body') : null;
        let confirmed = false;
        const currentStudentId = '<?php echo (int)($_SESSION['student_id'] ?? 0); ?>';

        function getSelectedStudentOption() {
            return studentSelect ? studentSelect.options[studentSelect.selectedIndex] : null;
        }

        function updateComboboxValue() {
            const selectedOption = getSelectedStudentOption();
            const label = selectedOption && selectedOption.value !== '' ? selectedOption.textContent.trim() : 'Select Student';

            if (studentComboboxValue) {
                studentComboboxValue.textContent = label;
            }
        }

        function closeStudentDropdown() {
            if (studentCombobox) {
                studentCombobox.classList.remove('is-open');
            }
            if (studentComboboxToggle) {
                studentComboboxToggle.setAttribute('aria-expanded', 'false');
            }
        }

        function openStudentDropdown() {
            if (studentCombobox) {
                studentCombobox.classList.add('is-open');
            }
            if (studentComboboxToggle) {
                studentComboboxToggle.setAttribute('aria-expanded', 'true');
            }
            if (studentSearch) {
                window.setTimeout(function () {
                    studentSearch.focus();
                }, 0);
            }
        }

        function updateStudentDetails() {
            const selectedOption = studentSelect ? studentSelect.options[studentSelect.selectedIndex] : null;
            const name = selectedOption ? selectedOption.getAttribute('data-name') || '' : '';
            const studentCode = selectedOption ? selectedOption.getAttribute('data-student-code') || '' : '';
            const course = selectedOption ? selectedOption.getAttribute('data-course') || '' : '';
            const section = selectedOption ? selectedOption.getAttribute('data-year-section') || selectedOption.getAttribute('data-section') || '' : '';
            const email = selectedOption ? selectedOption.getAttribute('data-email') || '' : '';
            const hasYearSection = selectedOption ? selectedOption.getAttribute('data-has-year-section') !== '0' : false;

            if (nameInput) nameInput.value = name;
            if (studentCodeInput) studentCodeInput.value = studentCode;
            if (courseInput) courseInput.value = course;
            if (sectionInput) sectionInput.value = section;
            if (emailInput) emailInput.value = email;

            const sectionWarn = document.getElementById('sectionWarning');
            const generateBtn = document.getElementById('generateQrBtn');
            if (!hasYearSection) {
                if (sectionWarn) sectionWarn.classList.remove('d-none');
                if (generateBtn) generateBtn.disabled = true;
            } else {
                if (sectionWarn) sectionWarn.classList.add('d-none');
                if (generateBtn) generateBtn.disabled = <?php echo empty($cart) ? 'true' : 'false'; ?>;
            }

            updateComboboxValue();
            renderCustomStudentOptions();
        }

        if (studentSelect) {
            studentSelect.addEventListener('change', updateStudentDetails);
            updateStudentDetails();
        }

        function renderCustomStudentOptions() {
            if (!studentSelect || !studentOptionsList) {
                return;
            }

            const searchValue = studentSearch ? studentSearch.value.trim().toLowerCase() : '';
            const options = Array.from(studentSelect.options).filter(function (option) {
                return option.value !== '';
            });
            let visibleCount = 0;

            studentOptionsList.innerHTML = '';

            options.forEach(function (option) {
                const name = option.getAttribute('data-name') || '';
                const studentCode = option.getAttribute('data-student-code') || '';
                const label = option.textContent.trim();
                const isMatch = searchValue === '' || (name + ' ' + studentCode).toLowerCase().indexOf(searchValue) !== -1;

                if (!isMatch) {
                    return;
                }

                visibleCount += 1;

                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'sl-student-option';
                item.setAttribute('role', 'option');
                item.dataset.value = option.value;

                if (option.disabled) {
                    item.classList.add('is-disabled');
                    item.disabled = true;
                }

                if (option.value === studentSelect.value) {
                    item.classList.add('is-selected');
                    item.setAttribute('aria-selected', 'true');
                } else {
                    item.setAttribute('aria-selected', 'false');
                }

                const text = document.createElement('span');
                text.textContent = label;
                item.appendChild(text);

                if (option.value === studentSelect.value) {
                    const check = document.createElement('span');
                    check.className = 'sl-student-check';
                    check.setAttribute('aria-hidden', 'true');
                    check.textContent = '✓';
                    item.appendChild(check);
                }

                item.addEventListener('click', function () {
                    studentSelect.value = option.value;
                    studentSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    closeStudentDropdown();
                });

                studentOptionsList.appendChild(item);
            });

            if (visibleCount === 0) {
                const empty = document.createElement('div');
                empty.className = 'sl-student-empty';
                empty.textContent = 'No students found.';
                studentOptionsList.appendChild(empty);
            }
        }

        function renderStudentOptions(students) {
            if (!studentSelect || !Array.isArray(students)) {
                return;
            }

            const previousValue = studentSelect.value || currentStudentId;
            const fragment = document.createDocumentFragment();
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Select Student';
            fragment.appendChild(placeholder);

            let hasPreviousValue = false;

            students.forEach(function (student) {
                const option = document.createElement('option');
                const id = String(student.id || '');
                const name = String(student.name || '');
                const studentId = String(student.student_id || '');
                const section = String(student.section || '');
                const missingYearSection = section.trim() === '';

                option.value = id;
                option.dataset.name = name;
                option.dataset.studentCode = studentId;
                option.dataset.course = String(student.course || '');
                option.dataset.yearSection = section;
                option.dataset.email = String(student.email || '');
                option.dataset.hasYearSection = missingYearSection ? '0' : '1';
                option.textContent = name + ' - ' + studentId + (missingYearSection ? ' (No Year/Section)' : '');

                if (missingYearSection) {
                    option.disabled = true;
                    option.title = 'Complete Year / Section in profile';
                }

                if (id === previousValue && !missingYearSection) {
                    option.selected = true;
                    hasPreviousValue = true;
                }

                fragment.appendChild(option);
            });

            studentSelect.replaceChildren(fragment);

            if (!hasPreviousValue) {
                studentSelect.value = '';
            }

            updateStudentDetails();
        }

        function filterStudentDropdown() {
            renderCustomStudentOptions();
        }

        function refreshStudentDropdown() {
            fetch('<?php echo BASE_URL; ?>/student/shelves.php?student_options_json=1', {
                headers: { 'Accept': 'application/json' },
                cache: 'no-store'
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Student dropdown refresh failed.');
                    }
                    return response.json();
                })
                .then(function (data) {
                    renderStudentOptions(data.students || []);
                })
                .catch(function (error) {
                    console.error(error);
                });
        }

        if (studentSelect) {
            window.setInterval(refreshStudentDropdown, 30000);
        }

        if (studentSearch) {
            studentSearch.addEventListener('input', filterStudentDropdown);
        }

        if (studentComboboxToggle) {
            studentComboboxToggle.addEventListener('click', function () {
                if (studentCombobox && studentCombobox.classList.contains('is-open')) {
                    closeStudentDropdown();
                } else {
                    openStudentDropdown();
                }
            });
        }

        document.addEventListener('click', function (event) {
            if (studentCombobox && !studentCombobox.contains(event.target)) {
                closeStudentDropdown();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeStudentDropdown();
            }
        });

        if (!borrowRequestForm || !confirmButton || !modalElement || typeof bootstrap === 'undefined') {
            return;
        }

        const confirmModal = new bootstrap.Modal(modalElement);

        function showBorrowModal(title, message, showConfirmButton) {
            if (modalTitle) {
                modalTitle.textContent = title;
            }
            if (modalBody) {
                modalBody.textContent = message;
            }
            if (confirmButton) {
                confirmButton.style.display = showConfirmButton ? '' : 'none';
            }
            confirmModal.show();
        }

        borrowRequestForm.addEventListener('submit', function (event) {
            if (studentSelect && studentSelect.value !== currentStudentId) {
                event.preventDefault();
                confirmed = false;
                showBorrowModal('Invalid Student', 'You select other student', false);
                return;
            }

            if (!confirmed) {
                event.preventDefault();
                showBorrowModal('Confirm Borrowing Request', 'Are you sure you want to submit this borrowing request?', true);
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
