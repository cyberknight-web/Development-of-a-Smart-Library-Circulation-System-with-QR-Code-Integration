<?php
// student/choose_books.php
// Search and list books. Add to shelves (cart). Max 3 books.

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/student_auth.php';
require_once __DIR__ . '/../includes/student_layout.php';

require_student_login();

$pdo = db_connect();
$search = trim($_GET['q'] ?? '');
$cart = student_get_cart();
$student_id = (int)($_SESSION['student_id'] ?? 0);

// Borrowing limit is based on books that are currently not yet returned (pending, approved, or claimed).
try {
    $stmt = $pdo->prepare(
        "SELECT COALESCE(SUM(bri.quantity), 0) AS total_active_copies
         FROM borrow_requests br
         JOIN borrow_request_items bri ON bri.borrow_request_id = br.id
         WHERE br.student_id = :sid
           AND br.status IN ('pending', 'approved', 'claimed')"
    );
    $stmt->execute([':sid' => $student_id]);
    $active_borrowed_count = (int)($stmt->fetchColumn() ?? 0);
} catch (Throwable $e) {
    // If the query fails, fail safe by blocking new borrows.
    error_log('Active borrow count failed: ' . $e->getMessage());
    $active_borrowed_count = 3;
}

$max_books_at_a_time = 3;
$remaining_to_borrow = max(0, $max_books_at_a_time - $active_borrowed_count);
$cart_count = count($cart);
$_choose_books_error = $_GET['error'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $book_id = isset($_POST['book_id']) ? (int)$_POST['book_id'] : 0;
    if ($action === 'remove' && $book_id > 0) {
        student_remove_from_cart($book_id);
        header('Location: ' . BASE_URL . '/student/choose_books.php' . ($search !== '' ? '?q=' . urlencode($search) : ''));
        exit;
    }
    if ($action === 'add' && $book_id > 0) {
        // Enforce: (active borrows) + (books selected in cart) must be <= 3.
        if ($remaining_to_borrow <= 0 || $cart_count >= $remaining_to_borrow) {
            $qs = $search !== '' ? ('?q=' . urlencode($search) . '&error=borrow_limit') : '?error=borrow_limit';
            header('Location: ' . BASE_URL . '/student/choose_books.php' . $qs);
            exit;
        }

        $stmt = $pdo->prepare('SELECT id, copies_available, status FROM books WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $book_id]);
        $book = $stmt->fetch();
        if ($book && $book['status'] === 'available' && (int)$book['copies_available'] > 0) {
            if (student_add_to_cart($book_id)) {
                header('Location: ' . BASE_URL . '/student/choose_books.php' . ($search !== '' ? '?q=' . urlencode($search) : ''));
                exit;
            }
        }
    }
}

$like = $search !== '' ? '%' . $search . '%' : null;
if ($like !== null) {
    $stmt = $pdo->prepare(
        "SELECT * FROM books
         WHERE (title LIKE :q OR author LIKE :q OR category LIKE :q OR accession_no LIKE :q OR isbn LIKE :q)
         ORDER BY title ASC"
    );
    $stmt->execute([':q' => $like]);
} else {
    $stmt = $pdo->query(
        "SELECT * FROM books ORDER BY title ASC"
    );
}
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

/** @noinspection PhpUndefinedFunctionInspection */
student_render_header('Choose Books');
?>

<style>
    .sl-choose-hero {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, rgba(128, 0, 0, 0.98), rgba(74, 0, 0, 0.95));
        border-radius: 14px;
        color: #fff;
        padding: 1.2rem 1.25rem;
        box-shadow: 0 12px 30px rgba(74, 0, 0, 0.2);
    }
    .sl-choose-hero::after {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(255, 200, 92, 0.22), transparent 45%);
        pointer-events: none;
    }
    .sl-choose-hero h2,
    .sl-choose-hero p {
        position: relative;
        z-index: 1;
    }
    .sl-choose-alert {
        border: 1px solid rgba(255, 200, 92, 0.45);
        box-shadow: 0 10px 24px rgba(128, 0, 0, 0.06);
    }
    .sl-search-shell {
        border: 1px solid rgba(128, 0, 0, 0.12);
        background: linear-gradient(145deg, #ffffff, #faf7f7);
        border-radius: 12px;
        padding: 0.8rem;
        box-shadow: 0 10px 24px rgba(128, 0, 0, 0.06);
    }
    .sl-search-shell .form-control {
        border-color: rgba(128, 0, 0, 0.18);
    }
    .sl-search-shell .form-control:focus {
        border-color: #800000;
        box-shadow: 0 0 0 0.2rem rgba(128, 0, 0, 0.15);
    }
    .sl-stats-panel {
        border: 1px solid rgba(128, 0, 0, 0.12);
        background: linear-gradient(145deg, #ffffff, #fafafa);
        border-radius: 12px;
        padding: 0.85rem 1rem;
        box-shadow: 0 10px 24px rgba(128, 0, 0, 0.06);
    }
    .sl-stats-panel strong {
        color: #212529;
    }
    .sl-books-card {
        border: 1px solid rgba(128, 0, 0, 0.12);
        background: linear-gradient(145deg, #ffffff, #fafafa);
        box-shadow: 0 12px 28px rgba(128, 0, 0, 0.08);
    }
    .sl-books-title {
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }
    .sl-books-title .dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #ffc85c;
        box-shadow: 0 0 0 4px rgba(255, 200, 92, 0.22);
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
</style>

<div class="row mb-4">
    <div class="col-12">
        <div class="sl-page-header sl-choose-hero">
            <h2 class="mb-1">Choose Books</h2>
            <p class="mb-0" style="color: rgba(255, 255, 255, 0.9);">Search and add books to your shelves. Your total borrow limit is capped at 3 books at a time (based on not-yet-returned borrows).</p>
        </div>
    </div>
</div>

<?php if ($_choose_books_error === 'borrow_limit'): ?>
    <div class="alert alert-warning sl-choose-alert mb-4">
        You can borrow up to <strong><?php echo (int) $remaining_to_borrow; ?></strong> more book(s) at the same time.
        Return or clear existing borrows to borrow again.
    </div>
<?php endif; ?>

<div class="sl-search-shell mb-4">
    <form method="get">
        <div class="input-group">
            <input type="search" class="form-control" name="q" placeholder="Search books (title, author, category)..." value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
            <button type="submit" class="btn btn-sl-primary">Search</button>
        </div>
    </form>
</div>

<div class="sl-stats-panel mb-3">
    <strong>Active borrows:</strong> <?php echo $active_borrowed_count; ?> / <?php echo $max_books_at_a_time; ?> books
    <?php if ($remaining_to_borrow <= 0): ?>
        <span class="badge bg-danger ms-2">Limit reached</span>
    <?php endif; ?>
    <br>
    <strong>In your shelves:</strong> <?php echo $cart_count; ?> / <?php echo $remaining_to_borrow; ?> book(s) you can still borrow
    <?php if (!empty($cart)): ?>
        <a href="<?php echo BASE_URL; ?>/student/shelves.php" class="btn btn-sm btn-sl-primary ms-2">View Shelves &rarr;</a>
    <?php endif; ?>
</div>

<div class="card sl-card sl-books-card">
    <div class="card-body">
        <h5 class="card-title fw-semibold mb-3 sl-books-title"><span class="dot"></span>Books</h5>
        <div class="table-responsive">
            <table class="table table-sm align-middle sl-books-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th class="text-center">Available</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($books): ?>
                        <?php foreach ($books as $b): ?>
                            <?php
                                $copies_available = (int)($b['copies_available'] ?? 0);
                                $is_available_status = ($b['status'] ?? '') === 'available';
                                $can_add = $is_available_status && $copies_available > 0;
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($b['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($b['author'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($b['category'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-center">
                                    <?php echo $copies_available; ?>
                                    <?php if (!$can_add): ?>
                                        <span class="badge bg-secondary ms-1">Not Available</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if (in_array((int)$b['id'], $cart, true)): ?>
                                        <span class="text-success">In shelves</span>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="book_id" value="<?php echo (int)$b['id']; ?>">
                                            <button type="submit" class="btn btn-link btn-sm text-danger p-0 ms-1">Remove</button>
                                        </form>
                                    <?php elseif (!$can_add): ?>
                                        <span class="text-muted">Not Available</span>
                                    <?php elseif ($remaining_to_borrow <= 0): ?>
                                        <span class="text-danger">Borrowing limit reached</span>
                                    <?php elseif ($cart_count >= $remaining_to_borrow): ?>
                                        <span class="text-muted">Max <?php echo (int)$remaining_to_borrow; ?> books</span>
                                    <?php else: ?>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="action" value="add">
                                            <input type="hidden" name="book_id" value="<?php echo (int)$b['id']; ?>">
                                            <button type="submit" class="btn btn-sl-primary btn-sm">Add to Shelves</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                <?php echo $search !== '' ? 'No available books match your search.' : 'No available books at the moment.'; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
/** @noinspection PhpUndefinedFunctionInspection */
student_render_footer();
?>
