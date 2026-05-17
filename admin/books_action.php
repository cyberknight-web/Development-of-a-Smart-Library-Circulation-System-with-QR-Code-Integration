<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/admin_auth.php';

require_admin_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/books.php');
    exit;
}

$action = trim($_POST['action'] ?? '');
$book_id = isset($_POST['book_id']) ? (int)$_POST['book_id'] : 0;

if (!in_array($action, ['update', 'delete', 'toggle_status', 'delete_all', 'restore_all'], true)) {
    header('Location: ' . BASE_URL . '/admin/books.php?status=action_error');
    exit;
}

$pdo = db_connect();

function ensure_books_backup_table(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS books_restore_bin (
            id int(10) unsigned NOT NULL,
            accession_no varchar(100) DEFAULT NULL,
            isbn varchar(50) DEFAULT NULL,
            title varchar(255) NOT NULL,
            author varchar(255) DEFAULT NULL,
            publisher varchar(255) DEFAULT NULL,
            publication_year varchar(10) DEFAULT NULL,
            category varchar(100) DEFAULT NULL,
            location varchar(100) DEFAULT NULL,
            copies_total int(10) unsigned NOT NULL DEFAULT 1,
            copies_available int(10) unsigned NOT NULL DEFAULT 1,
            status enum(\'available\',\'not_available\') NOT NULL DEFAULT \'available\',
            imported_from_excel tinyint(1) NOT NULL DEFAULT 0,
            created_at timestamp NOT NULL DEFAULT current_timestamp(),
            updated_at timestamp NULL DEFAULT NULL,
            deleted_at timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
}

if (in_array($action, ['delete_all', 'restore_all'], true)) {
    if ($action === 'delete_all') {
        try {
            ensure_books_backup_table($pdo);
        } catch (Throwable $e) {
            error_log('Delete all books backup table create failed: ' . $e->getMessage());
            header('Location: ' . BASE_URL . '/admin/books.php?status=action_error');
            exit;
        }

        $pdo->beginTransaction();
        try {
            // Avoid TRUNCATE here because it triggers implicit commit in MySQL.
            $pdo->exec('DELETE FROM books_restore_bin');
            $pdo->exec(
                'INSERT INTO books_restore_bin (
                    id, accession_no, isbn, title, author, publisher, publication_year,
                    category, location, copies_total, copies_available, status,
                    imported_from_excel, created_at, updated_at
                )
                SELECT
                    id, accession_no, isbn, title, author, publisher, publication_year,
                    category, location, copies_total, copies_available, status,
                    imported_from_excel, created_at, updated_at
                FROM books'
            );
            $pdo->exec('DELETE FROM borrow_request_items');
            $pdo->exec('DELETE FROM books');
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Delete all books failed: ' . $e->getMessage());
            header('Location: ' . BASE_URL . '/admin/books.php?status=action_error');
            exit;
        }
        header('Location: ' . BASE_URL . '/admin/books.php?status=all_deleted');
        exit;
    }

    $pdo->beginTransaction();
    try {
        $table_check = $pdo->query("SHOW TABLES LIKE 'books_restore_bin'");
        if (!$table_check || !$table_check->fetchColumn()) {
            $pdo->rollBack();
            header('Location: ' . BASE_URL . '/admin/books.php?status=restore_empty');
            exit;
        }

        $count_stmt = $pdo->query('SELECT COUNT(*) FROM books_restore_bin');
        $backup_count = (int)$count_stmt->fetchColumn();
        if ($backup_count <= 0) {
            $pdo->rollBack();
            header('Location: ' . BASE_URL . '/admin/books.php?status=restore_empty');
            exit;
        }

        $pdo->exec('DELETE FROM books');
        $pdo->exec(
            'INSERT INTO books (
                id, accession_no, isbn, title, author, publisher, publication_year,
                category, location, copies_total, copies_available, status,
                imported_from_excel, created_at, updated_at
            )
            SELECT
                id, accession_no, isbn, title, author, publisher, publication_year,
                category, location, copies_total, copies_available, status,
                imported_from_excel, created_at, updated_at
            FROM books_restore_bin
            ORDER BY id ASC'
        );
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Restore all books failed: ' . $e->getMessage());
        header('Location: ' . BASE_URL . '/admin/books.php?status=action_error');
        exit;
    }
    header('Location: ' . BASE_URL . '/admin/books.php?status=all_restored');
    exit;
}

if ($book_id <= 0) {
    header('Location: ' . BASE_URL . '/admin/books.php?status=action_error');
    exit;
}


$stmt = $pdo->prepare('SELECT id FROM books WHERE id = :id');
$stmt->execute([':id' => $book_id]);
if (!$stmt->fetch()) {
    header('Location: ' . BASE_URL . '/admin/books.php?status=action_error');
    exit;
}

if ($action === 'delete') {
    try {
        ensure_books_backup_table($pdo);
    } catch (Throwable $e) {
        error_log('Book backup table create failed: ' . $e->getMessage());
        header('Location: ' . BASE_URL . '/admin/books.php?status=action_error');
        exit;
    }

    $pdo->beginTransaction();
    try {
        $backup = $pdo->prepare(
            'REPLACE INTO books_restore_bin (
                id, accession_no, isbn, title, author, publisher, publication_year,
                category, location, copies_total, copies_available, status,
                imported_from_excel, created_at, updated_at, deleted_at
            )
            SELECT
                id, accession_no, isbn, title, author, publisher, publication_year,
                category, location, copies_total, copies_available, status,
                imported_from_excel, created_at, updated_at, CURRENT_TIMESTAMP
            FROM books
            WHERE id = :id'
        );
        $backup->execute([':id' => $book_id]);

        $del_items = $pdo->prepare('DELETE FROM borrow_request_items WHERE book_id = :id');
        $del_items->execute([':id' => $book_id]);
        $del = $pdo->prepare('DELETE FROM books WHERE id = :id');
        $del->execute([':id' => $book_id]);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Book delete failed: ' . $e->getMessage());
        header('Location: ' . BASE_URL . '/admin/books.php?status=action_error');
        exit;
    }
    header('Location: ' . BASE_URL . '/admin/books.php?status=deleted');
    exit;
}

if ($action === 'toggle_status') {
    $current = $pdo->prepare('SELECT status FROM books WHERE id = :id');
    $current->execute([':id' => $book_id]);
    $row = $current->fetch();
    $new_status = ($row && $row['status'] === 'available') ? 'not_available' : 'available';
    $upd = $pdo->prepare('UPDATE books SET status = :status WHERE id = :id');
    $upd->execute([':status' => $new_status, ':id' => $book_id]);
    header('Location: ' . BASE_URL . '/admin/books.php?status=status_updated');
    exit;
}

if ($action === 'update') {
    $title = trim($_POST['title'] ?? '');
    if ($title === '') {
        header('Location: ' . BASE_URL . '/admin/books.php?status=update_error');
        exit;
    }
    $accession_no = trim($_POST['accession_no'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $publication_year = trim($_POST['publication_year'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $copies_total = (int)($_POST['copies_total'] ?? 0);
    $copies_available = (int)($_POST['copies_available'] ?? 0);
    $status = trim($_POST['status'] ?? 'available');
    if (!in_array($status, ['available', 'not_available'], true)) {
        $status = 'available';
    }
    $copies_total = max(0, $copies_total);
    $copies_available = min(max(0, $copies_available), $copies_total);

    try {
        $upd = $pdo->prepare(
            'UPDATE books SET
                accession_no = :accession_no,
                isbn = :isbn,
                title = :title,
                author = :author,
                publisher = :publisher,
                publication_year = :publication_year,
                category = :category,
                location = :location,
                copies_total = :copies_total,
                copies_available = :copies_available,
                status = :status
             WHERE id = :id'
        );
        $upd->execute([
            ':accession_no' => $accession_no,
            ':isbn' => $isbn,
            ':title' => $title,
            ':author' => $author,
            ':publisher' => $publisher,
            ':publication_year' => $publication_year,
            ':category' => $category,
            ':location' => $location,
            ':copies_total' => $copies_total,
            ':copies_available' => $copies_available,
            ':status' => $status,
            ':id' => $book_id,
        ]);
    } catch (Throwable $e) {
        error_log('Book update failed: ' . $e->getMessage());
        header('Location: ' . BASE_URL . '/admin/books.php?status=update_error');
        exit;
    }
    header('Location: ' . BASE_URL . '/admin/books.php?status=updated');
    exit;
}

header('Location: ' . BASE_URL . '/admin/books.php');
exit;
