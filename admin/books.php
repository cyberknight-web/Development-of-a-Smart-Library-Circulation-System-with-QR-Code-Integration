<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_once __DIR__ . '/../includes/book_covers.php';

require_admin_login();

$pdo = db_connect();
smartlibrary_ensure_book_cover_columns($pdo);

$search_books = trim($_GET['q'] ?? '');
$like_books = $search_books !== '' ? '%' . $search_books . '%' : null;

if ($like_books !== null) {
    $stmt_all = $pdo->prepare(
        "SELECT * FROM books WHERE title LIKE :q OR author LIKE :q OR category LIKE :q OR accession_no LIKE :q OR isbn LIKE :q ORDER BY created_at DESC"
    );
    $stmt_all->execute([':q' => $like_books]);
    $stmt_available = $pdo->prepare(
        "SELECT * FROM books WHERE status = 'available' AND (title LIKE :q OR author LIKE :q OR category LIKE :q OR accession_no LIKE :q OR isbn LIKE :q) ORDER BY title ASC"
    );
    $stmt_available->execute([':q' => $like_books]);
} else {
    $stmt_all = $pdo->query('SELECT * FROM books ORDER BY created_at DESC');
    $stmt_available = $pdo->prepare("SELECT * FROM books WHERE status = 'available' ORDER BY title ASC");
    $stmt_available->execute();
}
$books_all = $stmt_all->fetchAll();
$books_available = $stmt_available->fetchAll();
$books_backup = [];
$backup_available = false;

function sl_book_data_attribute(array $book): string
{
    $json = json_encode(
        $book,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE
    );

    return htmlspecialchars($json !== false ? $json : '{}', ENT_QUOTES, 'UTF-8');
}

try {
    $table_check = $pdo->query("SHOW TABLES LIKE 'books_restore_bin'");
    $backup_available = $table_check && (bool)$table_check->fetchColumn();
    if ($backup_available) {
        $stmt_backup = $pdo->query('SELECT * FROM books_restore_bin ORDER BY deleted_at DESC, id DESC');
        $books_backup = $stmt_backup ? $stmt_backup->fetchAll() : [];
    }
} catch (Throwable $e) {
    $backup_available = false;
    $books_backup = [];
}

// Read status message from query string (import and book actions)
$status = $_GET['status'] ?? null;
$status_message = null;
$status_alert = 'danger';
if ($status === 'import_success') {
    $status_message = 'Import Successfully.';
    $status_alert = 'success';
} elseif ($status === 'import_error') {
    $status_message = 'Invalid file format.';
} elseif ($status === 'invalid_format') {
    $status_message = 'Invalid file format.';
} elseif ($status === 'updated') {
    $status_message = 'Book updated successfully.';
    $status_alert = 'success';
} elseif ($status === 'invalid_cover') {
    $status_message = 'Book cover photo must be a JPG, PNG, WEBP, or GIF image up to 5 MB.';
} elseif ($status === 'deleted') {
    $status_message = 'Book deleted successfully.';
    $status_alert = 'success';
} elseif ($status === 'status_updated') {
    $status_message = 'Book status updated.';
    $status_alert = 'success';
} elseif ($status === 'all_deleted') {
    $status_message = 'Deleted successfully.';
    $status_alert = 'success';
} elseif ($status === 'all_restored') {
    $status_message = 'All books have been restored successfully.';
    $status_alert = 'success';
} elseif ($status === 'restore_empty') {
    $status_message = 'No backup found to restore.';
} elseif ($status === 'update_error' || $status === 'action_error') {
    $status_message = 'Action failed. Please try again.';
}

admin_render_header('Books Management');
?>

<style>
    .sl-books-hero {
        position: relative;
        overflow: hidden;
        background: linear-gradient(90deg, #FF6A00, #FF8C00, #FFA500);
        border-radius: 14px;
        color: #fff;
        padding: 1.2rem 1.25rem;
        box-shadow: 0 12px 30px rgba(255, 106, 0, 0.2);
    }
    .sl-books-hero::after {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(255, 165, 0, 0.2), transparent 45%);
        pointer-events: none;
    }
    .sl-books-hero h2,
    .sl-books-hero p {
        position: relative;
        z-index: 1;
    }
    .sl-books-alert {
        border-width: 1px;
        box-shadow: 0 10px 24px rgba(255, 106, 0, 0.06);
    }
    .sl-future-card {
        border: 1px solid rgba(255, 106, 0, 0.12);
        background: linear-gradient(145deg, #ffffff, #fff8f3);
        box-shadow: 0 12px 28px rgba(255, 106, 0, 0.08);
    }
    .sl-future-title {
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }
    .sl-future-title .dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #FFA500;
        box-shadow: 0 0 0 4px rgba(255, 165, 0, 0.22);
    }
    .sl-search-shell {
        border: 1px solid rgba(255, 106, 0, 0.12);
        background: linear-gradient(145deg, #ffffff, #fff8f3);
        border-radius: 12px;
        padding: 0.8rem;
        box-shadow: 0 10px 24px rgba(255, 106, 0, 0.06);
    }
    .sl-search-shell .form-control,
    .sl-future-card .form-control,
    .sl-future-card .form-select {
        border-color: rgba(255, 106, 0, 0.18);
    }
    .sl-search-shell .form-control:focus,
    .sl-future-card .form-control:focus,
    .sl-future-card .form-select:focus {
        border-color: #FF8C00;
        box-shadow: 0 0 0 0.2rem rgba(255, 106, 0, 0.14);
    }
    .sl-books-tabs {
        border-bottom: 1px solid rgba(128, 0, 0, 0.14);
    }
    .sl-books-tabs .nav-link {
        border: 0;
        color: #6c757d;
        font-weight: 600;
        padding: 0.45rem 0.85rem;
        border-radius: 999px;
        margin-right: 0.35rem;
    }
    .sl-books-tabs .nav-link.active {
        background: rgba(128, 0, 0, 0.12);
        color: #800000;
        box-shadow: inset 0 0 0 1px rgba(128, 0, 0, 0.18);
    }
    .sl-books-table thead th {
        font-size: 0.79rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #6c757d;
        border-bottom-width: 1px;
    }
    .sl-books-table tbody tr:hover {
        background: rgba(128, 0, 0, 0.03);
    }
    .sl-book-cover-preview {
        width: 76px;
        height: 104px;
        object-fit: cover;
        border: 1px solid rgba(255, 106, 0, 0.18);
        border-radius: 8px;
        background: #fff8f3;
    }
    .sl-book-cover-placeholder {
        width: 76px;
        height: 104px;
        border: 1px dashed rgba(255, 106, 0, 0.35);
        border-radius: 8px;
        color: #6c757d;
        background: #fff8f3;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.72rem;
        text-align: center;
        padding: 0.35rem;
    }
    .sl-badge-soft {
        font-size: 0.76rem;
        border: 1px solid rgba(255, 200, 92, 0.45);
        background: rgba(255, 200, 92, 0.18);
        color: #805200;
        border-radius: 999px;
        padding: 0.2rem 0.55rem;
    }
    .sl-modal-future {
        border: 1px solid rgba(128, 0, 0, 0.14);
        box-shadow: 0 18px 36px rgba(128, 0, 0, 0.15);
    }
</style>

<div class="row mb-4">
    <div class="col-12">
        <div class="sl-page-header sl-books-hero">
            <h2 class="mb-1">Books Management</h2>
            <p class="mb-0" style="color: rgba(255, 255, 255, 0.9);">
                Import book records from Excel and view availability.
            </p>
        </div>
    </div>
</div>

<?php if ($status_message): ?>
    <div class="alert alert-<?php echo $status_alert; ?> sl-books-alert">
        <?php echo htmlspecialchars($status_message, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card sl-card sl-future-card">
            <div class="card-body">
                <h5 class="card-title fw-semibold mb-3 sl-future-title"><span class="dot"></span>Import Books from Excel/CSV</h5>
                <p class="text-muted small">
                    Download the template, edit it in Excel, save as CSV, then upload the CSV file. The system will add or update books accordingly.
                </p>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <a
                        href="<?php echo BASE_URL; ?>/admin/books_import.php?download_template=1"
                        class="btn btn-outline-secondary btn-sm"
                    >
                        Download Import Template (CSV)
                    </a>
                </div>
                <form action="<?php echo BASE_URL; ?>/admin/books_import.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="books_excel" class="form-label">Import file (.csv)</label>
                        <input
                            type="file"
                            class="form-control"
                            id="books_excel"
                            name="books_excel"
                            accept=".csv"
                            required
                        >
                    </div>
                    <button type="submit" class="btn btn-sl-primary">
                        Import Books
                    </button>
                </form>
                <p class="text-muted small mt-3 mb-0">
                    Required columns (header row, in order): <strong>accession_no</strong>, <strong>isbn</strong>, <strong>title</strong>, <strong>author</strong>, <strong>publisher</strong>, <strong>publication_year</strong>, <strong>category</strong>, <strong>location</strong>, <strong>copies</strong>. Status is auto-set: copies &gt; 0 = Available, copies = 0 = Not Available.
                </p>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="sl-search-shell mb-3">
            <form method="get" action="<?php echo BASE_URL; ?>/admin/books.php">
                <?php if (isset($_GET['status'])): ?>
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($_GET['status'], ENT_QUOTES, 'UTF-8'); ?>">
                <?php endif; ?>
                <div class="input-group">
                    <input
                        type="search"
                        class="form-control"
                        name="q"
                        placeholder="Search books (title, author, category, accession, ISBN)..."
                        value="<?php echo htmlspecialchars($search_books, ENT_QUOTES, 'UTF-8'); ?>"
                        aria-label="Search books"
                    >
                    <button type="submit" class="btn btn-sl-primary">Search</button>
                    <?php if ($search_books !== ''): ?>
                        <a href="<?php echo BASE_URL; ?>/admin/books.php" class="btn btn-outline-secondary">Clear</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <div class="d-flex gap-2 justify-content-end mb-3">
            <button type="button" class="btn btn-outline-danger btn-sm" id="btnDeleteAllBooks">
                Delete All
            </button>
            <button type="button" class="btn btn-outline-success btn-sm" id="btnRestoreAllBooks">
                Restore All
            </button>
        </div>
        <ul class="nav nav-tabs mb-3 sl-books-tabs" id="booksTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="imported-tab" data-bs-toggle="tab" data-bs-target="#imported" type="button" role="tab">
                    Imported Books
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="available-tab" data-bs-toggle="tab" data-bs-target="#available" type="button" role="tab">
                    Available Books
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="backup-tab" data-bs-toggle="tab" data-bs-target="#backup" type="button" role="tab">
                    Deleted/Backup Books
                </button>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="imported" role="tabpanel">
                <div class="card sl-card sl-future-card">
                    <div class="card-body">
                        <h5 class="card-title fw-semibold mb-3 sl-future-title"><span class="dot"></span>All Imported Books</h5>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle sl-books-table" id="tableImportedBooks">
                                <thead>
                                <tr>
                                    <th>Cover</th>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Category</th>
                                    <th class="text-center">Copies</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if ($books_all): ?>
                                    <?php foreach ($books_all as $book): ?>
                                        <tr data-book="<?php echo sl_book_data_attribute($book); ?>">
                                            <td>
                                                <?php $cover_url = smartlibrary_book_cover_url($book['cover_image'] ?? null); ?>
                                                <?php if ($cover_url): ?>
                                                    <img src="<?php echo htmlspecialchars($cover_url, ENT_QUOTES, 'UTF-8'); ?>" alt="Cover for <?php echo htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8'); ?>" class="sl-book-cover-preview">
                                                <?php else: ?>
                                                    <div class="sl-book-cover-placeholder">No cover</div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($book['author'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($book['category'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-center">
                                                <?php echo (int)$book['copies_available']; ?> / <?php echo (int)$book['copies_total']; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($book['status'] === 'available'): ?>
                                                    <span class="badge bg-success">Available</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Not Available</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary btn-edit-book" title="Update">Update</button>
                                                    <button type="button" class="btn btn-outline-warning btn-status-book" title="Change status">
                                                        <?php echo ($book['status'] === 'available') ? 'Not Available' : 'Available'; ?>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-delete-book" title="Delete">Delete</button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            <?php echo $search_books !== '' ? 'No books match your search. Try a different term or clear search.' : 'No books found. Please import a file.'; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="available" role="tabpanel">
                <div class="card sl-card sl-future-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-3">
                            <h5 class="card-title fw-semibold mb-0 sl-future-title"><span class="dot"></span>Available Books</h5>
                            <div class="d-flex gap-2">
                                <a
                                    class="btn btn-outline-secondary btn-sm"
                                    href="<?php echo BASE_URL; ?>/admin/books_export.php?scope=available&format=csv<?php echo $search_books !== '' ? '&q=' . urlencode($search_books) : ''; ?>"
                                >
                                    Download CSV (Excel)
                                </a>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle sl-books-table">
                                <thead>
                                <tr>
                                    <th>Cover</th>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Category</th>
                                    <th class="text-center">Available Copies</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if ($books_available): ?>
                                    <?php foreach ($books_available as $book): ?>
                                        <tr data-book="<?php echo sl_book_data_attribute($book); ?>">
                                            <td>
                                                <?php $cover_url = smartlibrary_book_cover_url($book['cover_image'] ?? null); ?>
                                                <?php if ($cover_url): ?>
                                                    <img src="<?php echo htmlspecialchars($cover_url, ENT_QUOTES, 'UTF-8'); ?>" alt="Cover for <?php echo htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8'); ?>" class="sl-book-cover-preview">
                                                <?php else: ?>
                                                    <div class="sl-book-cover-placeholder">No cover</div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($book['author'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($book['category'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-center">
                                                <?php echo (int)$book['copies_available']; ?>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary btn-edit-book" title="Update">
                                                        Update
                                                    </button>
                                                    <button type="button" class="btn btn-outline-warning btn-status-book" title="Set Not Available">
                                                        Not Available
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-delete-book" title="Delete">
                                                        Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            <?php echo $search_books !== '' ? 'No available books match your search.' : 'No available books at the moment.'; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="backup" role="tabpanel">
                <div class="card sl-card sl-future-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-3">
                            <h5 class="card-title fw-semibold mb-0 sl-future-title"><span class="dot"></span>Deleted / Backup Books</h5>
                            <span class="sl-badge-soft">
                                <?php echo (int)count($books_backup); ?> saved
                            </span>
                        </div>
                        <p class="text-muted small mb-3">
                            Books stored here are used by the Restore All button.
                        </p>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle sl-books-table">
                                <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Category</th>
                                    <th class="text-center">Copies</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Saved At</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if ($books_backup): ?>
                                    <?php foreach ($books_backup as $book): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($book['author'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($book['category'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-center">
                                                <?php echo (int)$book['copies_available']; ?> / <?php echo (int)$book['copies_total']; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if (($book['status'] ?? '') === 'available'): ?>
                                                    <span class="badge bg-success">Available</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Not Available</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php echo htmlspecialchars($book['deleted_at'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            <?php echo $backup_available ? 'No deleted/backup books saved yet.' : 'Backup table not created yet. It will appear after your first delete action.'; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Edit Book -->
<div class="modal fade" id="editBookModal" tabindex="-1" aria-labelledby="editBookModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content sl-modal-future">
            <div class="modal-header">
                <h5 class="modal-title" id="editBookModalLabel">Update Book</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="<?php echo BASE_URL; ?>/admin/books_action.php" id="formEditBook" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="book_id" id="editBookId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="edit_cover_image" class="form-label">Book Cover Photo</label>
                            <div id="editCoverPreviewWrap" class="mb-2">
                                <div class="sl-book-cover-placeholder">No cover photo</div>
                            </div>
                            <input type="file" class="form-control" id="edit_cover_image" name="cover_image" accept="image/jpeg,image/png,image/webp,image/gif">
                            <div class="form-text">Upload a new cover anytime to replace the current cover. Supported formats: JPG, PNG, WEBP, GIF, max 5 MB.</div>
                        </div>
                        <div class="col-md-8">
                            <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_accession_no" class="form-label">Accession No</label>
                            <input type="text" class="form-control" id="edit_accession_no" name="accession_no">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_isbn" class="form-label">ISBN</label>
                            <input type="text" class="form-control" id="edit_isbn" name="isbn">
                        </div>
                        <div class="col-12">
                            <label for="edit_title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_title" name="title" required>
                        </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_author" class="form-label">Author</label>
                            <input type="text" class="form-control" id="edit_author" name="author">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_publisher" class="form-label">Publisher</label>
                            <input type="text" class="form-control" id="edit_publisher" name="publisher">
                        </div>
                        <div class="col-md-4">
                            <label for="edit_publication_year" class="form-label">Publication Year</label>
                            <input type="text" class="form-control" id="edit_publication_year" name="publication_year">
                        </div>
                        <div class="col-md-4">
                            <label for="edit_category" class="form-label">Category</label>
                            <input type="text" class="form-control" id="edit_category" name="category">
                        </div>
                        <div class="col-md-4">
                            <label for="edit_location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="edit_location" name="location">
                        </div>
                        <div class="col-md-4">
                            <label for="edit_copies_total" class="form-label">Copies Total</label>
                            <input type="number" class="form-control" id="edit_copies_total" name="copies_total" min="0" value="1">
                        </div>
                        <div class="col-md-4">
                            <label for="edit_copies_available" class="form-label">Copies Available</label>
                            <input type="number" class="form-control" id="edit_copies_available" name="copies_available" min="0" value="0">
                        </div>
                        <div class="col-md-4">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-select" id="edit_status" name="status">
                                <option value="available">Available</option>
                                <option value="not_available">Not Available</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sl-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden form for delete (submitted via JS after confirm) -->
<form id="formDeleteBook" method="post" action="<?php echo BASE_URL; ?>/admin/books_action.php" style="display:none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="book_id" id="deleteBookId">
</form>
<!-- Hidden form for toggle status -->
<form id="formStatusBook" method="post" action="<?php echo BASE_URL; ?>/admin/books_action.php" style="display:none;">
    <input type="hidden" name="action" value="toggle_status">
    <input type="hidden" name="book_id" id="statusBookId">
</form>
<form id="formDeleteAllBooks" method="post" action="<?php echo BASE_URL; ?>/admin/books_action.php" style="display:none;">
    <input type="hidden" name="action" value="delete_all">
</form>
<form id="formRestoreAllBooks" method="post" action="<?php echo BASE_URL; ?>/admin/books_action.php" style="display:none;">
    <input type="hidden" name="action" value="restore_all">
</form>

<script>
(function () {
    var editModal = document.getElementById('editBookModal');
    var formEdit = document.getElementById('formEditBook');
    var formDelete = document.getElementById('formDeleteBook');
    var formStatus = document.getElementById('formStatusBook');
    var formDeleteAll = document.getElementById('formDeleteAllBooks');
    var formRestoreAll = document.getElementById('formRestoreAllBooks');
    var btnDeleteAll = document.getElementById('btnDeleteAllBooks');
    var btnRestoreAll = document.getElementById('btnRestoreAllBooks');
    var deleteBookId = document.getElementById('deleteBookId');
    var statusBookId = document.getElementById('statusBookId');
    var coverInput = document.getElementById('edit_cover_image');
    var coverPreviewWrap = document.getElementById('editCoverPreviewWrap');
    var selectedCoverPreviewUrl = null;

    function getBookFromRow(btn) {
        var tr = btn.closest('tr');
        if (!tr || !tr.dataset.book) return null;
        try { return JSON.parse(tr.dataset.book); } catch (e) { return null; }
    }

    function renderCoverPreview(filename) {
        if (!coverPreviewWrap) return;
        if (selectedCoverPreviewUrl) {
            URL.revokeObjectURL(selectedCoverPreviewUrl);
            selectedCoverPreviewUrl = null;
        }
        if (!filename) {
            coverPreviewWrap.innerHTML = '<div class="sl-book-cover-placeholder">No cover photo</div>';
            return;
        }
        var baseUrl = '<?php echo BASE_URL; ?>/uploads/book_covers/';
        coverPreviewWrap.innerHTML = '<img class="sl-book-cover-preview" src="' + baseUrl + encodeURIComponent(filename) + '" alt="Current book cover">';
    }

    if (coverInput) {
        coverInput.addEventListener('change', function () {
            var file = coverInput.files && coverInput.files[0] ? coverInput.files[0] : null;
            if (!file || !coverPreviewWrap) return;
            if (selectedCoverPreviewUrl) {
                URL.revokeObjectURL(selectedCoverPreviewUrl);
            }
            selectedCoverPreviewUrl = URL.createObjectURL(file);
            coverPreviewWrap.innerHTML = '<img class="sl-book-cover-preview" src="' + selectedCoverPreviewUrl + '" alt="Selected book cover">';
        });
    }

    document.querySelectorAll('.btn-edit-book').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var book = getBookFromRow(btn);
            if (!book || !book.id) {
                alert('This book record could not be loaded for editing. Please refresh the page and try again.');
                return;
            }
            document.getElementById('editBookId').value = book.id;
            document.getElementById('edit_accession_no').value = book.accession_no || '';
            document.getElementById('edit_isbn').value = book.isbn || '';
            document.getElementById('edit_title').value = book.title || '';
            document.getElementById('edit_author').value = book.author || '';
            document.getElementById('edit_publisher').value = book.publisher || '';
            document.getElementById('edit_publication_year').value = book.publication_year || '';
            document.getElementById('edit_category').value = book.category || '';
            document.getElementById('edit_location').value = book.location || '';
            document.getElementById('edit_copies_total').value = book.copies_total != null ? book.copies_total : 1;
            document.getElementById('edit_copies_available').value = book.copies_available != null ? book.copies_available : 0;
            document.getElementById('edit_status').value = (book.status === 'not_available') ? 'not_available' : 'available';
            if (coverInput) {
                coverInput.value = '';
            }
            renderCoverPreview(book.cover_image || '');
            if (!editModal || !window.bootstrap) {
                alert('The update form could not be opened. Please refresh the page and try again.');
                return;
            }

            bootstrap.Modal.getOrCreateInstance(editModal).show();
        });
    });

    document.querySelectorAll('.btn-delete-book').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var book = getBookFromRow(btn);
            if (!book) return;
            if (!confirm('Delete this book?\n"' + (book.title || '') + '"')) return;
            deleteBookId.value = book.id;
            formDelete.submit();
        });
    });

    document.querySelectorAll('.btn-status-book').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var book = getBookFromRow(btn);
            if (!book) return;
            var toStatus = (book.status === 'available') ? 'Not Available' : 'Available';
            if (!confirm('Change status to ' + toStatus + '?')) return;
            statusBookId.value = book.id;
            formStatus.submit();
        });
    });

    if (btnDeleteAll && formDeleteAll) {
        btnDeleteAll.addEventListener('click', function () {
            if (!confirm('Delete ALL books?\n\nThis will clear every book record. Use Restore All to bring them back.')) return;
            formDeleteAll.submit();
        });
    }

    if (btnRestoreAll && formRestoreAll) {
        btnRestoreAll.addEventListener('click', function () {
            if (!confirm('Restore all previously deleted books?')) return;
            formRestoreAll.submit();
        });
    }
})();
</script>

<?php
admin_render_footer();

